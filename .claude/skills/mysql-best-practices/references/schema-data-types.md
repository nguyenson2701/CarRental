---
title: Chọn đúng kiểu dữ liệu — DECIMAL cho tiền, ENUM/VARCHAR nhất quán cho trạng thái
impact: CAO
impactDescription: tránh sai số tiền tệ và giá trị trạng thái rác không kiểm soát được
tags: schema, data-types, decimal, enum
---

## DECIMAL cho tiền, không dùng FLOAT/DOUBLE

`FLOAT`/`DOUBLE` là số thực dấu phẩy động, có sai số làm tròn — **không được dùng cho tiền**. `schema.sql`
của CarRental đã làm đúng: mọi cột tiền (`PricePerDay`, `DepositAmount`, `TotalPrice`, `Amount`...) đều là
`DECIMAL(12,2)`. Khi thêm cột tiền mới, luôn giữ `DECIMAL(12,2)`, không đổi sang `FLOAT` dù chỉ để "thử nhanh".

## Giá trị trạng thái (Status) — nhất quán NULL vs chuỗi rỗng vs giá trị mặc định

Một vấn đề có thật trong schema hiện tại: `bookings.ReturnStatus` có `DEFAULT 'NotReturned'`, nhưng code ở
một số chỗ coi giá trị "chưa yêu cầu trả xe" là `NULL` hoặc chuỗi rỗng thay vì check đúng chuỗi
`'NotReturned'`. Khi viết query mới liên quan tới `ReturnStatus`, kiểm tra kỹ:

```sql
-- Sai neu gia dinh "chua xu ly" nghia la NULL:
SELECT * FROM bookings WHERE ReturnStatus IS NULL;

-- Dung theo dung default cua schema.sql:
SELECT * FROM bookings WHERE ReturnStatus = 'NotReturned';
```

Nguyên tắc chung khi thêm cột trạng thái mới: đặt `DEFAULT` rõ ràng, dùng đúng một quy ước (hoặc luôn `NULL`
khi "chưa có", hoặc luôn có giá trị mặc định — không trộn lẫn hai kiểu cho cùng một cột), và liệt kê đầy đủ
các giá trị hợp lệ trong comment hoặc `CHECK` constraint để người sau không đoán mò.

## Comment liệt kê giá trị hợp lệ cho cột kiểu VARCHAR dùng như enum

`schema.sql` dùng `VARCHAR` (không phải `ENUM`) cho các cột trạng thái (`Status`, `ReturnStatus`,
`PaymentType`...) để khớp với schema thật đang chạy. Vì MySQL không ép kiểm tra giá trị hợp lệ như `ENUM`
hay `CHECK ... IN (...)`, việc kiểm tra giá trị hợp lệ **phải làm ở tầng PHP** bằng `in_array(..., true)`
trước khi ghi xuống DB — đây cũng là pattern đã dùng đúng ở nhiều file (`admin/blogs/store.php` kiểm tra
`Status` chỉ nhận `'Published'`/`'Draft'`, `admin/cars/delete_image.php` kiểm tra `ImageType`). Khi thêm cột
trạng thái mới, luôn thêm bước validate tương tự trước khi insert/update, đừng tin giá trị POST lên là hợp lệ.
