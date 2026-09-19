---
title: Giữ transaction ngắn, SELECT ... FOR UPDATE đúng chỗ
impact: CAO cho luồng đặt xe (nhiều người có thể đặt cùng lúc)
impactDescription: tránh 2 khách đặt trùng cùng 1 xe cùng khung giờ (race condition)
tags: transaction, locking, for-update, mysql
---

## Giữ transaction ngắn, chỉ khoá đúng phần cần khoá

Transaction càng dài, càng giữ khoá lâu, càng block các request khác — đặc biệt nguy hiểm nếu bên trong
transaction có gọi API ngoài, đọc/ghi file, hoặc chờ input người dùng.

**Mẫu đang dùng đúng trong CarRental** — `CarRental_Backend/api/bookings/store.php`:

```php
$conn->begin_transaction();

try {
    $stmtCar = $conn->prepare("
        SELECT CarID, CarName, PricePerDay, DepositAmount, Status
        FROM cars
        WHERE CarID = ?
        FOR UPDATE
    ");
    $stmtCar->bind_param('i', $carID);
    $stmtCar->execute();
    $car = $stmtCar->get_result()->fetch_assoc();

    // ... kiem tra trung lich (bookingHasOverlap) ...
    // ... INSERT booking, INSERT payment ...

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    die($e->getMessage());
}
```

`SELECT ... FOR UPDATE` khoá đúng dòng xe đang xét cho tới khi `commit()`/`rollback()`, nên nếu hai khách bấm
đặt cùng một xe cùng lúc, người thứ hai phải đợi người thứ nhất commit xong rồi mới đọc được trạng thái mới
nhất (`bookingHasOverlap` sẽ thấy booking vừa tạo và từ chối nếu trùng lịch) — đây chính là cách chống race
condition khi đặt xe trùng giờ.

**Sai — sẽ phá vỡ tác dụng chống race condition:**

```php
// KHONG lam upload anh, goi API thanh toan, hay bat nguoi dung nhap them
// o giua begin_transaction() va commit() — giu khoa qua lau, block nguoi khac
$conn->begin_transaction();
$car = ...; // SELECT ... FOR UPDATE
sleep(3); // hoac goi payment gateway that, hoac move_uploaded_file(...)
$conn->commit();
```

## Tình trạng thực tế trong CarRental

Các file có transaction (`bookings/store.php`, `admin/cars/delete_image.php`,
`admin/cars/set_main_image.php`, `admin/bookings/update.php`, `admin/bookings/return_approve.php`,
`admin/payments/confirm.php`, `admin/payments/confirm_final.php`) đều tuân thủ đúng nguyên tắc này: chỉ có
các câu `SELECT`/`UPDATE`/`INSERT` bên trong transaction, việc ghi file (`move_uploaded_file`, `unlink`) luôn
nằm **ngoài** transaction (thường là sau `commit()`). Khi thêm transaction mới, giữ đúng cấu trúc này — không
đưa `safeUploadImage()` hay bất kỳ thao tác I/O/network nào vào giữa `begin_transaction()` và `commit()`.
