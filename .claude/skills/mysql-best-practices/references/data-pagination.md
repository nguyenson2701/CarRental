---
title: Phân trang cho các trang danh sách admin (hiện đang thiếu)
impact: TRUNG BÌNH-CAO khi dữ liệu tăng lên
impactDescription: tránh load toàn bộ bảng mỗi lần mở trang danh sách
tags: pagination, limit, offset, admin
---

## Vấn đề có thật: các trang danh sách admin chưa phân trang

Kiểm tra thực tế (grep `LIMIT`/`OFFSET`) cho thấy các trang sau **load toàn bộ bảng, không giới hạn số dòng**:

- `CarRental_Admin/cars/list.php`
- `CarRental_Admin/users/list.php`
- `CarRental_Admin/blogs/list.php`
- `CarRental_Admin/bookings/list.php` (ngoại trừ 1 subquery `LIMIT 1` không liên quan tới phân trang)

Với vài chục dòng như hiện tại thì không sao, nhưng nếu số lượng xe/user/booking/blog tăng lên vài trăm-vài
nghìn dòng, các trang này sẽ tải chậm dần và render HTML rất nặng. Đây là việc nên làm khi có thời gian, dù
không phải lỗi bảo mật.

**Cách thêm phân trang kiểu OFFSET (đơn giản, đủ dùng cho quy mô đồ án):**

```php
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$stmt = $conn->prepare("
    SELECT * FROM cars
    ORDER BY CarID DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$cars = $stmt->get_result();

// Lay tong so dong de tinh so trang
$total = $conn->query("SELECT COUNT(*) AS total FROM cars")->fetch_assoc()['total'];
$totalPages = (int)ceil($total / $perPage);
```

**Nếu sau này dữ liệu thật sự lớn (hàng chục nghìn dòng trở lên)**, OFFSET càng về trang sau càng chậm (MySQL
vẫn phải đọc và bỏ qua hết các dòng trước offset). Khi đó chuyển sang phân trang kiểu "keyset" (dựa vào ID
cuối cùng của trang trước):

```php
// Trang dau: khong co dieu kien
$stmt = $conn->prepare("SELECT * FROM cars ORDER BY CarID DESC LIMIT 20");

// Trang ke tiep: dua vao CarID nho nhat da thay o trang truoc ($lastCarID)
$stmt = $conn->prepare("SELECT * FROM cars WHERE CarID < ? ORDER BY CarID DESC LIMIT 20");
$stmt->bind_param("i", $lastCarID);
```

Với quy mô đồ án hiện tại, cách OFFSET đơn giản ở trên là đủ — chỉ cần nhớ đây là việc **chưa làm**, không
phải đã làm và bị lỗi.
