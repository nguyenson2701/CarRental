# CLAUDE.md — Cửa vào chung cho AI

> Claude Code và mọi AI agent phải đọc file này trước khi làm việc trong repo CarRental (đồ án thuê xe PHP + MySQL).

## 1. Xác định chế độ trước khi hành động

| Người dùng nói | Chế độ | Được làm gì |
|---|---|---|
| "trao đổi trước", "chưa làm", "kiểm tra", "tìm nguyên nhân", "xem giúp" | 🔵 **TRAO ĐỔI** | Chỉ đọc, phân tích và đề xuất. Không sửa file, không chạy migration, không đụng dữ liệu thật trong `carrentaldb`. |
| "đúng rồi", "ok làm đi", "sửa đi", "tiếp tục", hoặc yêu cầu code cụ thể | 🟢 **THỰC HIỆN** | Sửa → tự kiểm tra (`php -l`, thử import SQL vào DB tạm nếu đụng schema) → tóm tắt đã đổi gì. |

**Mặc định việc mới, mơ hồ là TRAO ĐỔI.** Không rõ chế độ thì hỏi một câu ngắn, không tự đoán.

## 2. Trước khi sửa

1. Xác định đúng phạm vi: đây là 3 module độc lập nhưng dùng chung DB —
   [CarRental_Frontend/](CarRental_Frontend/) (khách hàng), [CarRental_Admin/](CarRental_Admin/) (quản trị),
   [CarRental_Backend/](CarRental_Backend/) (API xử lý, dùng chung cho cả hai module trên).
2. Đọc [schema.sql](schema.sql) trước khi đụng tới bất kỳ bảng nào — đây là nguồn sự thật duy nhất về cấu trúc
   database (`carrentaldb`), được đối chiếu trực tiếp với DB thật, không phải suy đoán từ code.
3. Đọc [CarRental_Backend/config/auth.php](CarRental_Backend/config/auth.php) trước khi đụng tới đăng nhập,
   phân quyền (RoleID: 1=Admin, 2=Staff, 3=Customer) hoặc CSRF (`csrf_field()`, `requireCsrf()`).
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
