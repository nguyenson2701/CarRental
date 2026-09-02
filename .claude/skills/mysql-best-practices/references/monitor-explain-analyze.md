---
title: Dùng EXPLAIN để chẩn đoán query chậm trên MySQL/MariaDB
impact: THẤP-TRUNG BÌNH (công cụ chẩn đoán, không tự tối ưu gì)
impactDescription: biết chính xác query có dùng index hay đang full table scan
tags: monitor, explain, mysql, mariadb
---

## Dùng EXPLAIN trước khi đoán mò tại sao query chậm

**Cú pháp cơ bản (MySQL 5.7+/MariaDB, không cần quyền đặc biệt):**

```sql
EXPLAIN SELECT * FROM bookings WHERE CarID = 3 AND Status = 'Pending';
```

Đọc kết quả, chú ý các cột:
- `type`: `ALL` = full table scan (xấu, cần thêm index); `ref`/`range`/`const` = đang dùng index (tốt).
- `key`: tên index thực sự được MySQL chọn dùng, `NULL` nghĩa là không dùng index nào.
- `rows`: số dòng MySQL ước tính phải đọc — càng gần số dòng thật sự cần thì càng tốt.

**`EXPLAIN ANALYZE`** (chạy query thật, cho số liệu chính xác thay vì ước tính — cần MySQL ≥ 8.0.18 hoặc
MariaDB ≥ 10.4, XAMPP hiện tại thường đi kèm MariaDB nên kiểm tra phiên bản trước bằng `SELECT VERSION();`):

```sql
EXPLAIN ANALYZE
SELECT * FROM bookings WHERE CarID = 3 AND Status = 'Pending';
```

Lưu ý: cú pháp/định dạng output của `EXPLAIN ANALYZE` trên MySQL/MariaDB **khác** Postgres — không phải bảng
chi phí (cost) như Postgres mà là dạng cây lồng nhau kèm thời gian thực đo được.

## Bật slow query log khi cần tìm query nào đang chậm (XAMPP)

Mở `C:\xampp\mysql\bin\my.ini`, thêm vào phần `[mysqld]`:

```ini
slow_query_log = 1
slow_query_log_file = "C:/xampp/mysql/data/slow.log"
long_query_time = 1
```

Khởi động lại MySQL (qua XAMPP Control Panel), sau đó mọi query chạy lâu hơn 1 giây sẽ được ghi vào
`slow.log` — dùng để tìm ra query nào thực sự cần tối ưu thay vì đoán.

## Tình trạng thực tế trong CarRental

Project chưa cấu hình slow query log (bình thường cho môi trường dev). Khi nghi ngờ một trang admin chậm
(thường là các trang chưa phân trang, xem `data-pagination.md`), dùng `EXPLAIN` trực tiếp trên câu SQL của
trang đó trong phpMyAdmin hoặc `mysql` CLI trước khi sửa code — tránh tối ưu sai chỗ.
