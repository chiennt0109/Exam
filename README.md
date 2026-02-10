# Exam - Hệ thống quản lý thi

## Chức năng mới
- Dashboard trung tâm + thanh menu liên kết các phân hệ.
- Đăng nhập + phân vai trò (`admin`, `exam_manager`, `score_input`) + phân quyền chi tiết.
- CRUD + tìm kiếm cho Học sinh, Môn học, Điểm thi.
- Xoá 2 giai đoạn: xoá tạm (thùng rác) và xoá thật.
- Import học sinh từ Excel, mapping cột và lưu trực tiếp vào CSDL.
- CSDL SQLite (`exam.db`) khởi tạo tự động từ schema nhúng trong `config.php` với dữ liệu mẫu.

## Chạy dự án
```bash
php -S 0.0.0.0:8000
```
Mở: `http://localhost:8000/index.php`

## Tài khoản mẫu
- `admin / admin123`
- `qlthi / manager123`
- `nhapdiem / input123`
