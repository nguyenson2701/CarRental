---
title: Tránh N+1 query — dùng JOIN thay vì query trong vòng lặp
impact: TRUNG BÌNH-CAO khi danh sách dài ra
impactDescription: giảm số round-trip tới MySQL từ N+1 xuống còn 1
tags: n-plus-one, join, performance
---

## Tránh N+1 query

N+1 nghĩa là: 1 query lấy danh sách, rồi N query nữa (một query cho mỗi dòng) để lấy thông tin liên quan.
Với PHP, lỗi này thường xuất hiện dạng: query lấy danh sách xe, rồi trong vòng `foreach`, query thêm lần nữa
để lấy tên hãng xe cho từng chiếc.

**Sai (N+1) — KHÔNG viết theo kiểu này:**

```php
$cars = $conn->query("SELECT CarID, CarName, BrandID FROM cars");
while ($car = $cars->fetch_assoc()) {
    // Moi vong lap la 1 query rieng toi DB!
    $stmt = $conn->prepare("SELECT BrandName FROM brands WHERE BrandID = ?");
    $stmt->bind_param("i", $car['BrandID']);
    $stmt->execute();
    $brand = $stmt->get_result()->fetch_assoc();
    echo $car['CarName'] . ' - ' . $brand['BrandName'];
}
```

**Đúng — dùng JOIN, lấy hết trong 1 query:**

```php
$sql = "
    SELECT c.CarID, c.CarName, b.BrandName
    FROM cars c
    JOIN brands b ON c.BrandID = b.BrandID
";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    echo $row['CarName'] . ' - ' . $row['BrandName'];
}
```

## Tình trạng thực tế trong CarRental

Phần lớn code đã làm đúng: `CarRental_Admin/cars/list.php`, `CarRental_Frontend/vehicle.php` dùng JOIN với
`brands`/`cartypes` trong một câu SELECT thay vì query riêng cho từng xe. `CarRental_Admin/cars/edit.php`
lấy toàn bộ ảnh của một xe bằng **một** câu `SELECT * FROM CarImages WHERE CarID = ?` rồi lặp trong PHP để
render — đây không phải N+1 (chỉ 1 query cho N ảnh), là cách làm đúng, không phải "mỗi ảnh một query".

Khi thêm tính năng hiển thị dữ liệu liên quan tới nhiều bảng (vd. thêm cột "Tên hãng xe" vào một danh sách
mới), luôn ưu tiên JOIN ngay trong câu SQL đầu tiên, đừng query thêm bên trong vòng lặp PHP dù chỉ để "tiện
code trước, tối ưu sau" — thói quen đó rất dễ quên tối ưu lại.
