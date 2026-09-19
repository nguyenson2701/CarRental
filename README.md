# VinaDrive — Hệ thống cho thuê xe (CarRental)

Đồ án thuê xe viết bằng PHP thuần (kiến trúc MVC tự dựng) + MySQL, chạy trên XAMPP. Gồm 2 phần: trang khách hàng (đặt xe, thanh toán, quản lý tài khoản) và trang quản trị (quản lý xe, đơn đặt, thanh toán, người dùng, blog, menu).

## Công nghệ sử dụng

- **PHP 8.2**, không dùng framework — tự viết Router/Controller/Model theo mô hình MVC (`app/Core/`).
- **MySQL** (mysqli, prepared statement), schema tham chiếu tại [`schema.sql`](schema.sql).
- **Apache** qua XAMPP, không có bước build — không dùng Composer/npm để chạy app (`app/autoload.php` là autoloader PSR-4 tự viết).
- Giao diện: Bootstrap — theme **SB Admin 2** cho trang quản trị, theme riêng cho trang khách hàng (`public/admin/`, `public/frontend/`).

## Cấu trúc thư mục

```
index.php                     Front controller duy nhất — mọi URL /Carrental/... đi qua đây
.htaccess                     Điều hướng URL sạch về index.php, chặn truy cập .git/dotfile
app/
├── Core/                     Router, Controller/Model cơ sở, Database (mysqli), Auth (session/phân quyền/CSRF)
├── Controllers/
│   ├── Admin/                Brand, Car, User, Blog, Menu, Dashboard, Booking, Payment
│   └── Frontend/             Auth, Page, Blog, Car, Booking, Account
├── Models/                   1 model / 1 bảng, kế thừa Core/Model (prepared statement sẵn)
├── Views/                    admin/ và frontend/, mỗi view lồng trong 1 layout dùng chung
└── autoload.php              Autoloader PSR-4 tự viết (App\Foo\Bar → app/Foo/Bar.php)
CarRental_Backend/
├── config/                   Kết nối DB (config cũ) + auth helper cũ — bị chặn truy cập web trực tiếp
├── helpers/                  Upload ảnh, xử lý booking/blog dùng chung — bị chặn truy cập web trực tiếp
└── api/                      Vài endpoint AJAX/JSON còn dùng (check lịch trống, chatbot...)
public/
├── admin/                    Asset tĩnh (CSS/JS) cho trang quản trị
└── frontend/                 Asset tĩnh + ảnh khách hàng upload (avatar, GPLX, ảnh xe, ảnh trả xe)
schema.sql                    Nguồn sự thật duy nhất về cấu trúc database `carrentaldb`
```

## Phân quyền

Cột `RoleID` trong bảng `users`:

| RoleID | Vai trò | Quyền |
|---|---|---|
| 1 | Admin | Toàn quyền quản trị |
| 2 | Staff | Quản trị, trừ vài thao tác dành riêng cho Admin (gán quyền, xoá tài khoản) |
| 3 | Customer | Đặt xe, thanh toán, quản lý tài khoản/lịch sử của chính mình |

## Tính năng chính

**Trang khách hàng**
- Đăng ký / đăng nhập / đăng xuất
- Xem danh sách xe, chi tiết xe, đặt xe theo khoảng ngày
- Thanh toán tiền cọc / tiền thuê, theo dõi lịch sử đặt xe & thanh toán
- Trả xe, xem blog, trang tĩnh (giới thiệu, liên hệ)
- Chatbot hỏi đáp (`CarRental_Backend/api/chatbot/chat.php`)

**Trang quản trị** (`/admin/...`)
- Dashboard tổng quan (xe, đơn đặt, doanh thu)
- Quản lý xe, hãng xe, đơn đặt xe (xác nhận, huỷ, xử lý trả xe), thanh toán
- Quản lý người dùng, bài viết blog, menu điều hướng trang khách hàng

## Cài đặt & chạy thử (XAMPP)

1. Clone repo vào `htdocs`, đúng tên thư mục `Carrental` (nếu đổi tên khác, sửa hằng `BASE_PATH` trong [`index.php`](index.php)).
2. Tạo database `carrentaldb` rồi import [`schema.sql`](schema.sql).
3. Cấu hình kết nối DB đang để mặc định XAMPP local (`root` / không mật khẩu) tại [`app/Core/Database.php`](app/Core/Database.php) — đổi lại nếu môi trường của bạn khác.
4. Khởi động Apache + MySQL trong XAMPP.
5. Truy cập `http://localhost/Carrental/`.

Không có bộ test tự động trong repo. Sau khi sửa code, kiểm cú pháp từng file đã đổi bằng:

```bash
/c/xampp/php/php.exe -l duong/dan/file.php
```

## Ghi chú

- Không đưa thông tin kết nối/API key thật vào code hay commit — file `CarRental_Backend/config/database.php` và `app/Core/Database.php` dùng `root`/không mật khẩu chỉ vì đây là môi trường XAMPP local.
- Xem [`CLAUDE.md`](CLAUDE.md) để biết quy ước code chi tiết khi thêm/sửa module (route, controller, model, view).
