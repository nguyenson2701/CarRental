---
title: MySQL không có RLS — tự lọc theo quyền sở hữu trong từng query
impact: NGHIÊM TRỌNG nếu thiếu (lộ/sửa dữ liệu người khác)
impactDescription: đây là "RLS" thủ công — quên một chỗ là có lỗ hổng IDOR
tags: security, authorization, rls-equivalent, idor
---

## MySQL không có Row-Level Security — phải tự lọc trong từng câu query

Postgres/Supabase có RLS: khai báo policy một lần ở tầng DB, mọi query tự động chỉ thấy dữ liệu được phép.
**MySQL không có cơ chế tương đương.** Toàn bộ trách nhiệm "user A không được xem/sửa dữ liệu của user B"
nằm ở tầng code PHP — quên thêm điều kiện lọc ở một endpoint là có lỗ hổng
[IDOR](https://owasp.org/www-community/attacks/Insecure_Direct_Object_Reference) (Insecure Direct Object
Reference) ngay lập tức.

**Sai — chỉ lọc theo ID từ client, không kiểm tra chủ sở hữu:**

```php
// Nguy hiem: bat ky user dang nhap nao cung xem/huy duoc booking cua nguoi khac
// chi can doi so BookingID tren URL/form
$bookingID = (int)($_POST['BookingID'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM bookings WHERE BookingID = ?");
$stmt->bind_param("i", $bookingID);
```

**Đúng — luôn kèm điều kiện lọc theo user đang đăng nhập (trừ khi là admin/staff):**

```php
$userID = (int)($_SESSION['user_id'] ?? 0);
$bookingID = (int)($_POST['BookingID'] ?? 0);

$stmt = $conn->prepare("
    SELECT * FROM bookings
    WHERE BookingID = ? AND UserID = ?
");
$stmt->bind_param("ii", $bookingID, $userID);
```

## Tình trạng thực tế trong CarRental

Đây chính là pattern `CarRental_Backend/api/bookings/return_store.php` đang dùng đúng — query lấy booking
luôn kèm `AND b.UserID = ?` cùng với `requireCustomer()`. Khi thêm bất kỳ endpoint mới nào cho phép khách
hàng thao tác trên dữ liệu của chính họ (huỷ đơn, sửa hồ sơ, xem lịch sử...), **sao chép đúng pattern này**:
lấy `UserID` từ `$_SESSION`, không bao giờ tin ID nhận từ `$_POST`/`$_GET` để xác định "đây có phải dữ liệu
của tôi không" — ID từ client chỉ dùng để tìm dòng, còn quyền sở hữu phải được DB xác nhận lại trong cùng
câu `WHERE`.

Với các trang admin (`requireAdminOrStaff()`), không cần lọc theo `UserID` vì admin/staff được phép xem/sửa
mọi booking — nhưng vẫn phải giữ nguyên việc gọi `requireAdminOrStaff()` ở đầu file (xem
[CarRental_Backend/config/auth.php](../../../../CarRental_Backend/config/auth.php)) và `requireCsrf()` cho
mọi thao tác ghi, đây là hai lớp bảo vệ đã có sẵn trong project, không được bỏ qua khi thêm endpoint mới.
