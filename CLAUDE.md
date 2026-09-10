# CLAUDE.md — Cửa vào chung cho AI

> Claude Code và mọi AI agent phải đọc file này trước khi làm việc trong repo CarRental (đồ án thuê xe PHP + MySQL).

## 1. Xác định chế độ trước khi hành động

| Người dùng nói | Chế độ | Được làm gì |
|---|---|---|
| "trao đổi trước", "chưa làm", "kiểm tra", "tìm nguyên nhân", "xem giúp" | 🔵 **TRAO ĐỔI** | Chỉ đọc, phân tích và đề xuất. Không sửa file, không chạy migration, không đụng dữ liệu thật trong `carrentaldb`. |
| "đúng rồi", "ok làm đi", "sửa đi", "tiếp tục", hoặc yêu cầu code cụ thể | 🟢 **THỰC HIỆN** | Sửa → tự kiểm tra (`php -l`, thử import SQL vào DB tạm nếu đụng schema) → tóm tắt đã đổi gì. |

**Mặc định việc mới, mơ hồ là TRAO ĐỔI.** Không rõ chế độ thì hỏi một câu ngắn, không tự đoán.

## 2. Trước khi sửa

1. Xác định đúng phạm vi — dự án đã chuyển hẳn sang kiến trúc MVC (không còn 3 module trang riêng biệt cũ):
   - [index.php](index.php) — front controller duy nhất, mọi URL `/Carrental/...` đi qua đây (xem
     [.htaccess](.htaccess) ở gốc dự án).
   - [app/Core/](app/Core/) — Router, Controller/Model cơ sở, `Database` (kết nối mysqli), `Auth` (session/phân
     quyền/CSRF — thay cho `CarRental_Backend/config/auth.php` cũ, file đó **không còn được dùng**).
   - [app/Controllers/](app/Controllers/), [app/Models/](app/Models/), [app/Views/](app/Views/) — chia theo
     `Admin/` (quản trị) và `Frontend/` (khách hàng), dùng chung 1 database.
   - [public/admin/](public/admin/), [public/frontend/](public/frontend/) — **chỉ chứa asset tĩnh** (CSS/JS/ảnh
     giao diện) và **ảnh khách hàng đã upload thật** (avatar, GPLX, ảnh xe, ảnh trả xe) — không còn file `.php`
     logic nào ở đây, chỉ là nơi lưu trữ được Router/View trỏ tới. Xoá nhầm ảnh trong này là xoá dữ liệu thật.
   - [CarRental_Backend/config/](CarRental_Backend/config/), [CarRental_Backend/helpers/](CarRental_Backend/helpers/) —
     config kết nối DB + helper dùng chung (upload, bookings, blogs), được `app/` gọi tới. **Bị chặn truy cập web
     trực tiếp bằng `.htaccess`** (`Require all denied`) — chỉ gọi được từ PHP nội bộ (`require`), không phải "trang".
   - [CarRental_Backend/api/](CarRental_Backend/api/) — chỉ còn vài endpoint AJAX/JSON thật sự còn dùng
     (`bookings/{calendar,check_availability}.php` cho form đặt xe, `chatbot/chat.php`) — vẫn được gọi trực tiếp
     qua URL nên không bị `.htaccess` chặn.
2. Đọc [schema.sql](schema.sql) trước khi đụng tới bất kỳ bảng nào — đây là nguồn sự thật duy nhất về cấu trúc
   database (`carrentaldb`), được đối chiếu trực tiếp với DB thật, không phải suy đoán từ code.
3. Đọc [app/Core/Auth.php](app/Core/Auth.php) trước khi đụng tới đăng nhập, phân quyền (RoleID: 1=Admin, 2=Staff,
   3=Customer) hoặc CSRF (`Auth::csrfField()`, `Auth::requireCsrf()`) — đây là nơi logic auth thật sự nằm.
4. Đọc [CarRental_Backend/helpers/upload.php](CarRental_Backend/helpers/upload.php) trước khi thêm bất kỳ tính
   năng upload file nào — mọi upload ảnh phải đi qua `safeUploadImage()` / `isRealImageUpload()`, không tự viết
   `move_uploaded_file()` trần.
5. Nếu đụng dữ liệu, schema hoặc cấu trúc bảng: nêu rõ trước và chờ xác nhận riêng — xem mục 4.

## 3. Rào chắn bắt buộc

1. **Không tự commit, push** trừ khi được yêu cầu rõ ràng trong lượt trò chuyện đó (một lần đồng ý không có nghĩa
   là đồng ý mãi mãi). Repo chỉ có nhánh `main`, không có quy trình PR/preview — cẩn thận hơn vì không có bước
   review trung gian.
2. **Không sửa cấu trúc bảng bằng tay qua phpMyAdmin/lệnh rời rạc rồi bỏ quên** — mọi thay đổi schema phải được
   phản ánh lại vào [schema.sql](schema.sql) để file này luôn khớp với DB thật.
3. **Không chạy DDL (`DROP`/`ALTER`/`TRUNCATE`) trực tiếp lên `carrentaldb`** khi đang có dữ liệu thật trong đó.
   Muốn thử `schema.sql` hay migration, tạo database tạm (`carrentaldb_test` chẳng hạn), import và kiểm tra ở đó
   trước, không đụng DB đang chạy.
4. **Không đưa mật khẩu, API key, hay thông tin kết nối thật vào code/commit.**
   [CarRental_Backend/config/database.php](CarRental_Backend/config/database.php) dùng `root`/không mật khẩu vì
   đây là môi trường XAMPP local — không đổi sang thông tin nhạy cảm mà không hỏi trước.
5. **Không hạ cấp bảo mật đã có**: giữ nguyên `password_hash()`/`password_verify()`, CSRF token trên form
   add/edit/delete, và validate MIME thật (`finfo` + `getimagesize()`) khi upload ảnh — không quay lại so sánh
   mật khẩu thường hay chỉ check đuôi file.
6. **Không tự khởi động/dừng dịch vụ (MySQL, Apache) trừ khi cần kiểm thử và phải trả lại đúng trạng thái ban đầu**
   sau khi xong (nếu dịch vụ đang tắt trước khi bắt đầu, phải tắt lại).
7. Không tạo bản sao của một tính năng đã có (vd. thêm một hàm upload ảnh mới thay vì dùng lại `upload.php`).

## 4. Khi đụng vào dữ liệu/schema

1. Mô tả trước: bảng nào, cột nào, thay đổi gì, có phá vỡ dữ liệu cũ không.
2. Nếu cần kiểm thử thật: khởi động MySQL (nếu đang tắt), tạo DB tạm, import/thử ở đó, xoá DB tạm khi xong,
   trả MySQL về trạng thái ban đầu.
3. Cập nhật [schema.sql](schema.sql) để phản ánh đúng thay đổi — không để file này lạc hậu so với DB thật.

## 5. Cách giao tiếp

- Trả lời bằng tiếng Việt trừ khi được yêu cầu khác.
- Đây là đồ án học tập (một người làm), không cần phong cách "báo cáo giám đốc" — nhưng vẫn nên nói rõ ràng,
  tránh thuật ngữ khó hiểu khi không cần thiết.
- Phân biệt rõ: điều đã kiểm tra thật (chạy `php -l`, import SQL thử), điều đọc code suy ra, và điều mới là
  phỏng đoán chưa kiểm chứng.
- Không báo "xong" khi chưa kiểm tra được (vd. không chạy được UI thật trong môi trường hiện tại) — nói rõ phần
  nào chưa kiểm được và vì sao.

## 6. Kiểm tra trước khi bàn giao

Không có bộ test tự động trong repo này. Trước khi báo hoàn tất, tối thiểu:

```bash
# Kiểm tra cú pháp PHP cho từng file đã sửa
/c/xampp/php/php.exe -l duong/dan/file.php
```

- Nếu đổi schema: thử import `schema.sql` vào DB tạm (xem mục 4).
- Nếu đổi giao diện: nói rõ là **chưa** kiểm tra được bằng trình duyệt thật (nếu đúng vậy), không mặc định là
  đã hoạt động.

Bàn giao ngắn gọn: đã đổi gì, đã kiểm tra được gì, chưa kiểm được gì.

## 7. Môi trường & lệnh

- **PHP 8.2** chạy qua XAMPP: `C:\xampp\php\php.exe` (trong Git Bash: `/c/xampp/php/php.exe`).
- App chạy dưới Apache tại **`http://localhost/Carrental/`**. Không có bước build: `app/` không dùng Composer/npm
  — [app/autoload.php](app/autoload.php) là autoloader PSR-4 tự viết (`App\Foo\Bar` → `app/Foo/Bar.php`).
- `public/admin/` có `package.json` nhưng chỉ là bộ theme SB Admin 2 tải sẵn — không cần `npm install` để chạy app.
- Không có test tự động. Kiểm tra tối thiểu: `/c/xampp/php/php.exe -l <file>` cho mọi file PHP đã sửa (xem mục 6).
- Đổi tên thư mục dự án trên XAMPP thì phải sửa hằng `BASE_PATH` trong [index.php](index.php) cho khớp
  (hiện là `/Carrental`).

## 8. Vòng đời request & quy ước khi thêm/sửa module MVC

**Luồng chạy:** Apache → [.htaccess](.htaccess) (URL không trỏ tới file/thư mục thật → `index.php`) →
[index.php](index.php) đăng ký route → [app/Core/Router.php](app/Core/Router.php) cắt `BASE_PATH`, so khớp
`METHOD + path`, tách tham số `{id}` → `new Controller()` rồi gọi `action(...$params)` → Controller gọi Model
lấy dữ liệu → `$this->view()` render View lồng trong Layout.

Khi thêm một module (ví dụ "Khuyến mãi"):

1. **Route** — khai báo trong [index.php](index.php). Router **chỉ hỗ trợ `get()` và `post()`** (không PUT/DELETE);
   sửa/xoá đi qua `POST .../update`, `POST .../delete`. Đường dá́n route **không** kèm `/Carrental` (đã bị cắt).
2. **Controller** — [app/Controllers/Admin/](app/Controllers/Admin/) hoặc `Frontend/`, kế thừa
   [app/Core/Controller.php](app/Core/Controller.php). Trong `__construct()` gọi `Auth::start()` rồi
   `Auth::requireAdminOrStaff('/Carrental/login')` (hoặc `requireCustomer` / `requireLogin`) — hàm này **tự
   kiểm CSRF cho mọi request POST**. Tham số route vào dưới dạng **chuỗi**, tự ép `(int)`. Lấy input qua
   `$this->input()` / `inputInt()` / `inputFloat()`. Chuyển hướng bằng `$this->redirect('/Carrental/...')`
   (đường dẫn ở đây **có** `/Carrental`).
3. **Model** — [app/Models/](app/Models/), kế thừa [app/Core/Model.php](app/Core/Model.php). Chỉ cần khai báo
   `$table` (tên bảng thật, PascalCase — vd `Brands`) và `$primaryKey` (khoá thật — vd `BrandID`, không phải
   `id`). Kế thừa sẵn `find/all/create/update/delete` chạy prepared statement. Query tự viết thêm **cũng phải**
   dùng `$this->db->prepare()` + `bind_param()` — không nối chuỗi vào SQL.
4. **View** — [app/Views/](app/Views/), chỉ chứa HTML + biểu thức PHP hiển thị, **không query DB**. Render qua
   `$this->view('admin/khuyenmai/list', [...], 'admin/layout/main')`. Layout mặc định là `admin/layout/main`;
   trang Frontend truyền `'frontend/layout/main'`; truyền `null` nếu không cần layout. Form add/edit/delete phải
   có `<?= Auth::csrfField() ?>`.
5. **Asset** — CSS/JS/ảnh giao diện đặt dưới `public/admin/` hoặc `public/frontend/`, tham chiếu bằng đường dẫn
   tuyệt đối `/Carrental/public/...` (xem [app/Views/admin/layout/main.php](app/Views/admin/layout/main.php)).
6. **Helper dùng chung** — upload ảnh, xử lý booking/blog nằm ở [CarRental_Backend/helpers/](CarRental_Backend/helpers/),
   nạp bằng `require_once` từ Controller (xem [PageController.php](app/Controllers/Frontend/PageController.php)).
