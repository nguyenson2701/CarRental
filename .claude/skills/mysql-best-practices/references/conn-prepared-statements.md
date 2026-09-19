---
title: Luôn dùng prepared statement, không nối chuỗi SQL
impact: NGHIÊM TRỌNG nếu vi phạm (SQL Injection)
impactDescription: chống SQL injection — quan trọng hơn mọi tối ưu hiệu năng khác
tags: security, sql-injection, mysqli, prepared-statements
---

## Luôn dùng prepared statement (`mysqli::prepare` + `bind_param`)

Không bao giờ chèn trực tiếp giá trị từ `$_POST`/`$_GET`/`$_SESSION` vào chuỗi SQL. Luôn dùng
`$conn->prepare()` + `bind_param()`.

**Sai (SQL injection) — KHÔNG được làm:**

```php
$email = $_POST['email'];
$sql = "SELECT * FROM users WHERE Email = '$email'"; // Nguy hiem!
$result = $conn->query($sql);
```

Attacker chỉ cần nhập `' OR '1'='1` vào ô email là bypass được điều kiện lọc.

**Đúng — pattern đang dùng xuyên suốt CarRental:**

```php
$stmt = $conn->prepare("SELECT * FROM users WHERE Email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
```

## Tình trạng thực tế trong CarRental

Đã làm đúng gần như 100%: mọi file trong `CarRental_Backend/api/**/*.php` dùng `prepare()`/`bind_param()`.
Khi thêm tính năng mới có nhận input từ người dùng (form, query string), **luôn** theo đúng pattern này —
không có ngoại lệ, kể cả với input tưởng chừng "an toàn" như ID dạng số (ép kiểu `(int)` là đủ cho số, nhưng
với chuỗi vẫn phải bind_param, không nối chuỗi trực tiếp).

Lưu ý ký tự bind_param: `"i"` = int, `"s"` = string, `"d"` = double/decimal, `"b"` = blob — số ký tự trong
chuỗi type phải khớp đúng số lượng biến truyền vào, sai là lỗi runtime ngay lập tức (dễ phát hiện khi test).
