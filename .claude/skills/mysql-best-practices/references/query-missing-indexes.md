---
title: Thêm index cho cột dùng trong WHERE/JOIN
impact: CAO
impactDescription: nhanh hơn 10-1000 lần khi bảng lớn dần (bookings, payments)
tags: index, performance, mysql
---

## Thêm index cho cột dùng trong WHERE/JOIN

Query lọc/join trên cột không có index sẽ quét toàn bảng (full table scan), càng chậm khi bảng càng lớn.
Với InnoDB (engine mặc định của MySQL/MariaDB), mỗi index phụ (secondary index) trỏ về khoá chính, nên đừng
ngại thêm index cho cột hay dùng để lọc.

**Sai (không có index trên cột lọc):**

```sql
-- Neu ReturnStatus khong co index, MySQL phai quet toan bo bang bookings
SELECT * FROM bookings WHERE ReturnStatus = 'Pending';
```

**Đúng (thêm index rồi kiểm tra bằng EXPLAIN):**

```sql
ALTER TABLE bookings ADD INDEX idx_bookings_return_status (ReturnStatus);

EXPLAIN SELECT * FROM bookings WHERE ReturnStatus = 'Pending';
-- cot `key` phai hien idx_bookings_return_status, khong phai NULL
```

## Tình trạng thực tế trong CarRental

Đã làm đúng: `schema.sql` đã có `KEY` trên mọi cột khoá ngoại chính — `bookings(UserID)`, `bookings(CarID)`,
`payments(BookingID)`, `cars(BrandID)`, `cars(TypeID)`, `carimages(CarID)`. Khi thêm bảng/cột khoá ngoại mới,
luôn thêm `KEY` cho cột đó ngay trong cùng câu `ALTER TABLE`/`CREATE TABLE`, đừng để "thêm sau".

Cần chú ý: `CarRental_Admin/bookings/list.php` và các trang lọc theo `Status`/`ReturnStatus` hiện dựa vào
`KEY CarID`/`KEY UserID` sẵn có, không có index riêng trên `Status`/`ReturnStatus`. Với vài chục booking như
hiện tại không sao, nhưng nếu dữ liệu tăng lên hàng nghìn dòng, nên thêm index như ví dụ trên.
