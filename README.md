# Exam - Hệ thống quản lý thi

## Chức năng
- Dashboard trung tâm + thanh menu liên kết các phân hệ theo quyền.
- Đăng nhập + phân vai trò (`admin`, `exam_manager`, `score_input`) + phân quyền chi tiết.
- `admin` có toàn quyền và có thể gán/thu hồi quyền riêng cho từng tài khoản.
- `exam_manager` mặc định có quyền lấy DS học sinh trong DB, phân phòng thi, in danh sách thí sinh, xem/in báo cáo.
- `score_input` được phân công nhập điểm theo **môn** + **thành phần điểm** (dữ liệu môn đồng bộ từ `MON.xml`).
- CRUD + tìm kiếm cho Học sinh, Môn học, Điểm thi, Phân phòng thi, Phân công nhập điểm.
- Xoá 2 giai đoạn: xoá tạm (thùng rác) và xoá thật.
- Import học sinh từ Excel, mapping cột và lưu trực tiếp vào CSDL.
- CSDL SQLite binary tự khởi tạo.

## Vị trí file CSDL
Hệ thống sẽ tự tìm vị trí ghi được theo thứ tự:
1. `data/exam.db` (ưu tiên)
2. `exam.db` (thư mục gốc project)
3. thư mục tạm hệ điều hành (`/tmp/exam.db` trên Linux)

Bạn có thể kiểm tra nhanh bằng endpoint:
- `api/auth.php?action=health`

## Chạy dự án
```bash
php -S 0.0.0.0:8000
```
Mở: `http://localhost:8000/index.php`

## Tài khoản mẫu
- `admin / admin123`
- `qlthi / manager123`
- `nhapdiem / input123`

## Nếu đăng nhập báo lỗi API
- Mở `api/auth.php?action=health` để xem chi tiết `db_file`, `db_exists`, `sqlite_driver`.
- Nếu `sqlite_driver=false`, cần bật extension `pdo_sqlite`/`sqlite3` trong PHP rồi restart web server.
