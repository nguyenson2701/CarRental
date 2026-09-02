---
name: mysql-best-practices
description: "MySQL/MariaDB best practices for the CarRental project (PHP + mysqli, no ORM, no Supabase). Adapted from Supabase's Postgres best-practices skill for this project's actual stack. Load this skill BEFORE writing or changing anything that touches carrentaldb: creating or altering tables/columns, editing schema.sql, writing new SELECT/INSERT/UPDATE/DELETE queries, adding indexes, wrapping queries in transactions, adding pagination to an admin list page, or diagnosing a slow admin page / booking overlap check. This is not just a performance guide — schema design and security-by-query-scoping matter too, even for a one-column change."
metadata:
  author: adapted-for-carrental
  source: supabase/supabase-postgres-best-practices (MIT)
  version: "1.0.0"
---

# MySQL Best Practices (CarRental)

Đây là bản rút gọn, chuyển thể từ skill `supabase-postgres-best-practices` cho đúng công nghệ thật của
CarRental: **MySQL/MariaDB (XAMPP) + PHP mysqli, không ORM, không Supabase, không RLS**. Một số quy tắc gốc
(RLS của Postgres, `pg_cron`, `pgvector`, JSONB indexing...) không áp dụng được và đã được lược bỏ hoặc thay
bằng khái niệm tương đương trong MySQL.

Nguồn sự thật về cấu trúc bảng hiện tại: [schema.sql](../../../schema.sql) — luôn đối chiếu file đó, đừng đoán
tên cột từ trí nhớ.

## Khi nào dùng skill này

- Viết/sửa câu SELECT, INSERT, UPDATE, DELETE trong `CarRental_Backend/api/**/*.php`
- Thêm/sửa cột, bảng, index trong `schema.sql`
- Thêm phân trang cho một trang danh sách trong `CarRental_Admin/**/list.php`
- Bọc nhiều câu query trong `$conn->begin_transaction()` / `commit()` / `rollback()`
- Chẩn đoán trang admin chậm, hoặc kiểm tra trùng lịch đặt xe chậm

## Nhóm quy tắc theo mức ưu tiên

| Ưu tiên | Nhóm | Mức ảnh hưởng | File |
|---|---|---|---|
| 1 | Index & truy vấn | CAO | `references/query-*.md` |
| 2 | Kết nối (mysqli) | TRUNG BÌNH-CAO trên production | `references/conn-*.md` |
| 3 | Bảo mật (thay cho RLS) | CAO | `references/security-*.md` |
| 4 | Thiết kế schema | CAO | `references/schema-*.md` |
| 5 | Transaction & khoá | TRUNG BÌNH-CAO | `references/lock-*.md` |
| 6 | Cách truy cập dữ liệu | TRUNG BÌNH | `references/data-*.md` |
| 7 | Theo dõi/chẩn đoán | THẤP-TRUNG BÌNH | `references/monitor-*.md` |

## Khác biệt quan trọng so với bản gốc (Postgres/Supabase)

- **Không có RLS.** MySQL không có Row-Level Security built-in. Mọi việc "chỉ cho user thấy dữ liệu của họ"
  phải làm bằng tay trong từng câu query (`WHERE UserID = ?`) — xem `references/security-app-level-scoping.md`.
  Đây là điểm dễ quên nhất khi thêm tính năng mới, vì không có lưới an toàn ở tầng DB như Postgres RLS.
- **`EXPLAIN` khác cú pháp/output** so với Postgres (`EXPLAIN ANALYZE` chỉ có từ MySQL 8.0.18+/MariaDB 10.4+
  dạng JSON, cú pháp không giống Postgres). Xem `references/monitor-explain-analyze.md`.
- **Không có tầng connection pooler riêng** (kiểu PgBouncer) trong stack này — mỗi request PHP mở một kết nối
  mysqli mới qua `config/database.php` rồi đóng khi script kết thúc. Xem `references/conn-lifecycle.md`.
- Project đã làm đúng nhiều thứ sẵn: dùng `mysqli::prepare`/`bind_param` (chống SQL injection) ở mọi nơi,
  dùng `DECIMAL(12,2)` cho tiền thay vì FLOAT, có `SELECT ... FOR UPDATE` khi đặt xe. Các file tham khảo dưới
  đây trích dẫn thẳng các ví dụ đó làm mẫu, và cũng chỉ ra vài chỗ **hiện đang thiếu** (vd. trang danh sách
  admin chưa phân trang) để làm khi có dịp.

## Cách dùng

Đọc file tham khảo theo tình huống, ví dụ:

```
references/query-missing-indexes.md
references/data-pagination.md
references/security-app-level-scoping.md
```

Mỗi file gồm: vì sao quan trọng, ví dụ sai, ví dụ đúng, và tình trạng thực tế trong CarRental (đã làm đúng hay
đang thiếu).

## Tham khảo

- https://dev.mysql.com/doc/refman/8.0/en/
- https://mariadb.com/kb/en/documentation/
- Skill gốc: `supabase-postgres-best-practices` (MIT, tác giả Supabase)
