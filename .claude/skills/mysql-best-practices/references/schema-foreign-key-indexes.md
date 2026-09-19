---
title: Luôn index cột khoá ngoại, đặt FK constraint tường minh
impact: CAO
impactDescription: JOIN nhanh, và MySQL/InnoDB từ chối dữ liệu mồ côi (orphan rows)
tags: schema, foreign-key, index
---

## Luôn có index + constraint FK tường minh trên cột khoá ngoại

InnoDB **yêu cầu** một index trên cột dùng làm khoá ngoại (tự tạo ngầm nếu bạn quên, nhưng nên khai báo rõ
ràng để dễ đọc và kiểm soát tên index). Constraint FK còn giúp MySQL tự chặn việc chèn `CarID` không tồn tại
vào `bookings`, hoặc quyết định điều gì xảy ra khi xoá bản ghi cha (`ON DELETE CASCADE`/`RESTRICT`/`SET NULL`).

**Mẫu đang dùng đúng trong `schema.sql`:**

```sql
CREATE TABLE `carimages` (
    `ImageID`  INT(11) NOT NULL AUTO_INCREMENT,
    `CarID`    INT(11) NOT NULL,
    ...
    PRIMARY KEY (`ImageID`),
    KEY `idx_carimages_carid` (`CarID`),
    CONSTRAINT `fk_carimages_cars` FOREIGN KEY (`CarID`) REFERENCES `cars` (`CarID`)
        ON DELETE CASCADE ON UPDATE CASCADE
);
```

`ON DELETE CASCADE` ở đây là lựa chọn đúng: xoá một xe (`cars`) thì ảnh của xe đó (`carimages`) cũng nên bị
xoá theo — khớp với logic PHP trong `CarRental_Backend/api/admin/cars/delete.php` (xoá cả folder ảnh vật lý
lẫn record DB).

Khi thêm bảng mới có cột trỏ tới bảng khác (bất kể có được PHP code join tới hay không), luôn:
1. Đặt kiểu dữ liệu khớp chính xác với cột PK được tham chiếu (`INT(11)` tham chiếu `INT(11)`, không lệch
   `INT` với `BIGINT`).
2. Thêm `KEY` trên cột đó.
3. Thêm `CONSTRAINT ... FOREIGN KEY` với `ON DELETE`/`ON UPDATE` phù hợp nghiệp vụ (`CASCADE` cho dữ liệu phụ
   thuộc hoàn toàn như ảnh, `RESTRICT`/mặc định cho dữ liệu quan trọng như `bookings.UserID` — không nên xoá
   nhầm hết lịch sử đặt xe của một khách chỉ vì xoá tài khoản của họ).
4. Cập nhật lại `schema.sql` ngay, không chỉ chạy `ALTER TABLE` rời rạc rồi quên đồng bộ.
