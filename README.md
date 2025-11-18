# Sim Thăng Long - Website Bán SIM Số Đẹp

## 🚀 Cách chạy với XAMPP

### Bước 1: Khởi động XAMPP
1. Mở **XAMPP Control Panel**
2. Start **Apache** và **MySQL**
3. Đảm bảo cả 2 đều chạy (màu xanh)

### Bước 2: Setup Database
1. Mở **phpMyAdmin**: `http://localhost:9999/phpmyadmin`
2. Tạo database `simthanglong` (nếu chưa có)
3. Import file `database/schema.sql` hoặc chạy `setup.php`

### Bước 3: Truy cập Website
Mở trình duyệt và truy cập:
```
http://localhost:9999/WebSIM-main/WebSIM-main/public/index.html
```

Hoặc nếu dùng Live Server (VS Code):
- Mở file `public/index.html` bằng Live Server
- Code sẽ tự động gọi API từ `http://localhost:9999/WebSIM-main/WebSIM-main/api/`

## 📁 Cấu trúc thư mục

```
WebSIM-main/
├── api/                 # API endpoints
│   ├── db.php          # Database connection
│   ├── get_sim_khuyenmai.php
│   ├── get_sim_noibat.php
│   ├── search.php
│   └── test.php        # Test API
├── public/             # Frontend files
│   ├── index.html     # Trang chủ
│   ├── search.html   # Trang tìm kiếm
│   └── ...
├── assets/            # CSS, JS
├── config/           # Config files
└── database/         # SQL schema
```

## 🔧 Cấu hình

- **API Base URL**: `http://localhost:9999/WebSIM-main/WebSIM-main/api/`
- **Database**: `simthanglong`
- **Port**: 9999 (XAMPP)

## ✨ Tính năng

- ✅ Tìm kiếm SIM theo số, đầu số, đuôi số
- ✅ Lọc theo giá, mạng, loại SIM
- ✅ Hiển thị SIM khuyến mãi và nổi bật
- ✅ Responsive design
- ✅ Auto-detect API path

## 🐛 Troubleshooting

### Lỗi "Database connection failed"
→ Kiểm tra MySQL đang chạy và database `simthanglong` đã tạo

### Lỗi "Query failed"
→ Kiểm tra bảng `sim` có tồn tại và có dữ liệu không

### Không thấy SIM
→ Kiểm tra database có dữ liệu trong bảng `sim`
