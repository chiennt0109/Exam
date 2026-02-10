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
- CSDL SQLite binary (`exam.db`) khởi tạo tự động từ schema trong `config.php` + dữ liệu mẫu.

## Chạy dự án
```bash
php -S 0.0.0.0:8000
```
Mở: `http://localhost:8000/index.php`

## Tài khoản mẫu
- `admin / admin123`
- `qlthi / manager123`
- `nhapdiem / input123`
