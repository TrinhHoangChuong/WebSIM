# Sim Thăng Long Clone

Dự án clone hoàn chỉnh website [Sim Thăng Long](https://simthanglong.vn/) với đầy đủ chức năng tìm kiếm, lọc và đặt hàng SIM.

## 🚀 Tính năng

- **Tìm kiếm SIM**: Tìm theo số, đầu số, đuôi số
- **Lọc SIM**: Theo giá, mạng, loại SIM
- **Hiển thị SIM**: Grid layout với pagination
- **Đặt hàng**: Form đặt hàng trực tiếp
- **Responsive**: Tối ưu cho mobile và desktop
- **API RESTful**: Backend PHP với MySQL

## 📁 Cấu trúc thư mục

```
SimThangLongClone/
├── api/
│   └── index.php          # API endpoints chính
├── config/
│   └── database.php       # Cấu hình database
├── database/
│   └── schema.sql         # Database schema và sample data
├── public/
│   ├── index.html         # Trang chủ
│   └── search.html        # Trang tìm kiếm
├── assets/
│   ├── css/
│   │   └── style.css      # CSS chính
│   └── js/
│       └── app.js         # JavaScript chính
└── README.md
```

## 🛠️ Cài đặt

### 1. Yêu cầu hệ thống
- XAMPP (Apache + MySQL + PHP 7.4+)
- MySQL 5.7+
- PHP 7.4+

### 2. Cài đặt Database
1. Mở phpMyAdmin: `http://localhost/phpmyadmin`
2. Import file `database/schema.sql`
3. Database sẽ được tạo với tên `simthanglong_clone`

### 3. Cấu hình
1. Copy thư mục `SimThangLongClone` vào `htdocs`
2. Truy cập: `http://localhost/SimThangLongClone/public/`

### 4. Admin mặc định
- Username: `admin`
- Password: `password`
- Email: `admin@simthanglong.com`

## 🔧 API Endpoints

### GET /api/sims
Lấy danh sách SIM với filter và pagination

**Parameters:**
- `page` (int): Trang hiện tại (default: 1)
- `limit` (int): Số SIM mỗi trang (default: 20)
- `network` (string): Lọc theo mạng (Mobifone, Viettel, Vinaphone, Vietnamobile)
- `sim_type` (string): Lọc theo loại SIM
- `min_price` (float): Giá tối thiểu
- `max_price` (float): Giá tối đa

**Response:**
```json
{
  "success": true,
  "data": {
    "sims": [...],
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total": 100,
      "total_pages": 5
    }
  }
}
```

### GET /api/search
Tìm kiếm SIM theo từ khóa

**Parameters:**
- `q` (string): Từ khóa tìm kiếm

**Response:**
```json
{
  "success": true,
  "data": {
    "sims": [...]
  }
}
```

### POST /api/orders
Tạo đơn hàng mới

**Body:**
```json
{
  "sim_id": 1,
  "customer_name": "Nguyễn Văn A",
  "customer_phone": "0901234567",
  "customer_email": "email@example.com",
  "payment_method": "cash",
  "shipping_address": "123 Đường ABC"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "order_id": 123
  }
}
```

## 🎨 Design Features

- **Color Scheme**: Xanh lá chủ đạo (#00A651), vàng kim (#FFD700)
- **Typography**: Segoe UI font family
- **Layout**: Bootstrap 5 grid system
- **Icons**: Font Awesome 6
- **Animations**: CSS transitions và hover effects

## 📱 Responsive Design

- **Desktop**: 3 cột SIM cards
- **Tablet**: 2 cột SIM cards  
- **Mobile**: 1 cột SIM cards
- **Sidebar**: Collapse trên mobile

## 🔍 Tìm kiếm nâng cao

Hỗ trợ các pattern tìm kiếm:
- `6789`: Tìm SIM có số 6789
- `090*8888`: Tìm SIM đầu 090 đuôi 8888
- `0914*`: Tìm SIM bắt đầu 0914

## 🛒 Quy trình đặt hàng

1. Chọn SIM từ danh sách
2. Click "Mua ngay"
3. Nhập thông tin khách hàng
4. Xác nhận đơn hàng
5. Nhận mã đơn hàng

## 📊 Database Schema

### Bảng `sims`
- `id`: Primary key
- `phone_number`: Số điện thoại
- `network`: Mạng (Mobifone, Viettel, Vinaphone, Vietnamobile)
- `price`: Giá SIM
- `sim_type`: Loại SIM (Tứ quý, Ngũ quý, etc.)
- `status`: Trạng thái (available, sold, reserved)

### Bảng `customers`
- `id`: Primary key
- `full_name`: Họ tên
- `phone`: Số điện thoại
- `email`: Email
- `address`: Địa chỉ

### Bảng `orders`
- `id`: Primary key
- `customer_id`: Foreign key to customers
- `sim_id`: Foreign key to sims
- `total_amount`: Tổng tiền
- `status`: Trạng thái đơn hàng
- `payment_method`: Phương thức thanh toán

## 🚀 Deployment

### XAMPP Local
1. Copy vào `htdocs`
2. Import database
3. Truy cập `http://localhost/SimThangLongClone/public/`

### Production
1. Upload files lên server
2. Cấu hình database connection
3. Import database schema
4. Cấu hình web server (Apache/Nginx)

## 📝 Changelog

### v1.0.0
- ✅ Clone hoàn chỉnh Sim Thăng Long
- ✅ API RESTful với PHP
- ✅ Database MySQL với sample data
- ✅ Frontend responsive với Bootstrap 5
- ✅ Tìm kiếm và lọc SIM
- ✅ Đặt hàng trực tiếp
- ✅ Pagination và sorting

## 🤝 Contributing

1. Fork repository
2. Tạo feature branch
3. Commit changes
4. Push to branch
5. Tạo Pull Request

## 📄 License

MIT License - Xem file LICENSE để biết thêm chi tiết.

## 📞 Support

- Email: support@simthanglong.com
- Hotline: 024.6666.6666
- Website: https://simthanglong.vn/

---

**© Sim Thăng Long Clone - Demo Project**
