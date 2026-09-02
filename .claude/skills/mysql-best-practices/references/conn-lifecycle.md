---
title: Vòng đời kết nối mysqli trong mô hình PHP request-response
impact: TRUNG BÌNH (thấp trên XAMPP dev, quan trọng hơn khi triển khai thật)
impactDescription: tránh rò rỉ kết nối, hết connection slot khi nhiều người dùng cùng lúc
tags: connection, mysqli, php, pooling
---

## Vòng đời kết nối mysqli

Khác với Postgres/Supabase (thường có connection pooler như PgBouncer đứng giữa app và DB), stack của
CarRental là PHP thuần + mysqli: **mỗi request HTTP mở một kết nối mới** qua
`CarRental_Backend/config/database.php`:

```php
$conn = new mysqli($host, $username, $password, $database);
```

Kết nối này tự đóng khi script PHP kết thúc (không cần gọi `$conn->close()` thủ công, dù gọi cũng không sai).
Không có pool để tái sử dụng kết nối giữa các request như Node/Postgres.

**Sai (dễ mắc khi mới quen mysqli) — mở kết nối riêng nhiều lần trong cùng 1 request:**

```php
// File A
$conn = new mysqli(...);
// ... include file B, file B lại tự mở mysqli(...) lần nữa
```

Mỗi request chỉ nên có **một** `require_once '.../config/database.php'` ở đầu chuỗi include — đây cũng chính
là pattern mọi file trong `CarRental_Backend/api/**/*.php` đang dùng (require `config/database.php` một lần,
dùng chung biến `$conn` cho mọi câu query trong file đó).

## Giới hạn kết nối (`max_connections`)

MySQL/MariaDB có giới hạn số kết nối đồng thời (`max_connections`, mặc định thường 151 trên XAMPP). Vì mỗi
request PHP = một kết nối ngắn hạn rồi đóng ngay, project ở quy mô đồ án gần như không bao giờ chạm giới hạn
này. Nếu triển khai thật với nhiều người dùng đồng thời và thấy lỗi `Too many connections`, hướng xử lý là:
tăng `max_connections` trong `my.ini`, hoặc chuyển sang PHP-FPM với connection pool ở tầng ứng dụng — không
phải việc cần làm bây giờ, chỉ cần biết để không hoảng khi gặp lỗi này lúc demo với đông người dùng thật.
