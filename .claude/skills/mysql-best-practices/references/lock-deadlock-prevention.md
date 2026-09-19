---
title: Khoá các bảng theo cùng một thứ tự để tránh deadlock
impact: TRUNG BÌNH (tăng khi nhiều thao tác admin chạy đồng thời)
impactDescription: tránh lỗi "Deadlock found when trying to get lock"
tags: transaction, deadlock, innodb
---

## Luôn khoá các bảng/dòng theo cùng một thứ tự

Deadlock xảy ra khi transaction A khoá dòng 1 rồi chờ khoá dòng 2, trong khi transaction B khoá dòng 2 rồi
chờ khoá dòng 1 — cả hai chờ nhau mãi mãi cho tới khi InnoDB tự phát hiện và huỷ một trong hai (lỗi
`Deadlock found when trying to get lock; try restarting transaction`).

**Nguyên tắc:** nếu một luồng nghiệp vụ cần khoá nhiều bảng (vd. vừa cập nhật `bookings` vừa cập nhật `cars`
vừa cập nhật `payments`), **luôn khoá theo cùng một thứ tự ở mọi nơi trong code** — ví dụ quy ước:
`bookings` → `cars` → `payments`, không đảo ngược ở endpoint khác.

**Ví dụ đang tuân thủ đúng thứ tự trong CarRental** — `CarRental_Backend/api/admin/payments/confirm.php`:

```php
$conn->begin_transaction();
try {
    // 1. payments truoc
    $stmtUpdate = $conn->prepare("UPDATE payments SET ... WHERE PaymentID = ? ...");
    $stmtUpdate->execute();

    // 2. bookings sau
    $stmtBooking = $conn->prepare("UPDATE bookings SET Status = ? WHERE BookingID = ?");
    $stmtBooking->execute();

    // 3. cars sau cung
    $stmtCar = $conn->prepare("UPDATE cars SET Status = 'Booked' WHERE CarID = ?");
    $stmtCar->execute();

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    die($e->getMessage());
}
```

Khi viết endpoint mới cần cập nhật nhiều bảng trong cùng transaction, đi theo đúng thứ tự
`payments → bookings → cars` như các file admin hiện có (`confirm.php`, `confirm_final.php`,
`return_approve.php`, `bookings/update.php`) đang làm, để không tạo ra thứ tự khoá ngược chiều gây deadlock.

## Nếu gặp deadlock khi test

```sql
SHOW ENGINE INNODB STATUS;
-- Xem muc "LATEST DETECTED DEADLOCK" de biet 2 transaction nao xung dot va theo thu tu nao
```

`mysqli` sẽ trả lỗi qua `$stmt->execute()` trả về `false` và `$conn->error` chứa thông báo deadlock — code
hiện tại đã bắt lỗi này bằng `try/catch (Throwable $e)` quanh transaction rồi `rollback()`, đúng cách xử lý
cần có (không nên tự động retry ngầm mà không báo lỗi rõ ràng cho người dùng).
