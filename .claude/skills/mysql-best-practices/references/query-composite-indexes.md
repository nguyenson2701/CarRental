---
title: Index gộp nhiều cột (composite index) cho query nhiều điều kiện
impact: CAO cho các query kiểm tra trùng lịch
impactDescription: tránh quét toàn bộ booking của một xe khi kiểm tra trùng lịch
tags: index, composite, mysql, booking-overlap
---

## Index gộp nhiều cột cho query nhiều điều kiện

Khi một câu query luôn lọc theo nhiều cột cùng lúc, một index gộp (composite index) đúng thứ tự cột sẽ hiệu
quả hơn nhiều index đơn lẻ cộng lại. Thứ tự cột trong index phải khớp với thứ tự dùng trong `WHERE`
(cột lọc bằng `=` đứng trước, cột lọc theo khoảng `<`/`>`/`BETWEEN` đứng sau).

**Ví dụ thật trong CarRental — kiểm tra trùng lịch đặt xe** (`CarRental_Backend/api/bookings/helpers.php`,
hàm `bookingHasOverlap()`):

```sql
SELECT BookingID
FROM bookings
WHERE CarID = ?
  AND Status IN ('Pending', 'Confirmed', 'Paid')
  AND (? < EndDate)
  AND (? > StartDate)
LIMIT 1;
```

**Hiện tại** `bookings` chỉ có `KEY CarID` (index đơn cột) — MySQL dùng index này để tìm các dòng cùng `CarID`,
sau đó phải kiểm tra `Status`/`StartDate`/`EndDate` bằng cách quét thêm (không tệ vì mỗi xe thường chỉ có ít
booking, nhưng sẽ chậm dần nếu một xe có hàng trăm booking).

**Tốt hơn** — thêm index gộp đúng thứ tự cột hay lọc:

```sql
ALTER TABLE bookings
    ADD INDEX idx_bookings_car_dates (CarID, StartDate, EndDate);
```

Đặt `CarID` trước vì luôn lọc bằng `=`, `StartDate`/`EndDate` sau vì lọc theo khoảng — giúp MySQL thu hẹp
phạm vi quét ngay trong index thay vì phải đọc thêm dữ liệu ở bảng chính.

Kiểm tra hiệu quả bằng `EXPLAIN`: cột `rows` (số dòng ước tính phải đọc) nên giảm rõ rệt so với khi chỉ có
`KEY CarID`.
