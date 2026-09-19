---
title: Tài khoản MySQL riêng, quyền tối thiểu khi triển khai thật
impact: CAO khi deploy production, THẤP trên máy dev cá nhân
impactDescription: giới hạn thiệt hại nếu code có lỗ hổng (vd. SQL injection sót lại)
tags: security, privileges, mysql-user, production
---

## Dùng tài khoản MySQL riêng, quyền tối thiểu — không dùng `root` khi triển khai thật

`CarRental_Backend/config/database.php` hiện dùng:

```php
$username = "root";
$password = "";
```

Đây là chấp nhận được **trên máy dev cá nhân với XAMPP** (MySQL chỉ nghe `localhost`, không expose ra
mạng). **Không được mang nguyên cấu hình này lên môi trường triển khai thật** (VPS, hosting) — nếu có, kẻ
tấn công khai thác được một lỗ hổng bất kỳ trong code PHP (vd. include file lạ, SQL injection sót lại) sẽ có
toàn quyền trên toàn bộ MySQL server, không chỉ riêng `carrentaldb`.

**Khi triển khai thật, tạo tài khoản riêng chỉ có quyền trên đúng 1 database:**

```sql
CREATE USER 'carrental_app'@'localhost' IDENTIFIED BY 'mot-mat-khau-manh-ngau-nhien';

GRANT SELECT, INSERT, UPDATE, DELETE ON carrentaldb.* TO 'carrental_app'@'localhost';
-- Khong GRANT: FILE, SUPER, GRANT OPTION, hay quyen tren database khac

FLUSH PRIVILEGES;
```

Sau đó cập nhật `config/database.php` dùng tài khoản này thay vì `root`, và **không commit mật khẩu thật vào
git** — nên đọc từ biến môi trường hoặc file config không nằm trong repo (`.gitignore`).

## Tình trạng thực tế trong CarRental

Dùng `root`/không mật khẩu là bình thường cho đồ án chạy trên XAMPP local. Chỉ cần lưu ý điểm này khi có kế
hoạch đưa project lên hosting/VPS thật để demo hoặc nộp bài — đừng copy nguyên `config/database.php` sang môi
trường public.
