-- Sim Thăng Long Clone Database Schema
CREATE DATABASE IF NOT EXISTS simthanglong;
USE simthanglong;

-- Bảng SIM
CREATE TABLE sims (
    id INT PRIMARY KEY AUTO_INCREMENT,
    phone_number VARCHAR(15) NOT NULL UNIQUE,
    network VARCHAR(20) NOT NULL,
    price DECIMAL(15,2) NOT NULL,
    sim_type VARCHAR(50),
    status ENUM('available', 'sold', 'reserved') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_phone (phone_number),
    INDEX idx_network (network),
    INDEX idx_price (price),
    INDEX idx_status (status)
);

-- Bảng khách hàng
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    email VARCHAR(100),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone (phone),
    INDEX idx_email (email)
);

-- Bảng đơn hàng
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT,
    sim_id INT,
    total_amount DECIMAL(15,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    shipping_address TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (sim_id) REFERENCES sims(id),
    INDEX idx_customer (customer_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
);

-- Bảng admin/nhân viên
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
);

-- Bảng loại SIM
CREATE TABLE sim_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng mạng di động
CREATE TABLE networks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    color VARCHAR(7),
    logo_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert dữ liệu mẫu
INSERT INTO networks (name, color, logo_url) VALUES
('Viettel', '#E31E24', 'assets/images/viettel-logo.png'),
('Vinaphone', '#0066CC', 'assets/images/vinaphone-logo.png'),
('Mobifone', '#00A651', 'assets/images/mobifone-logo.png'),
('Vietnamobile', '#FF6600', 'assets/images/vietnamobile-logo.png'),
('iTelecom', '#8B4513', 'assets/images/itelecom-logo.png');

INSERT INTO sim_types (name, description) VALUES
('Sim Tứ quý', 'Sim có 4 số giống nhau liên tiếp'),
('Sim Ngũ quý', 'Sim có 5 số giống nhau liên tiếp'),
('Sim Lục quý', 'Sim có 6 số giống nhau liên tiếp'),
('Sim Tam hoa', 'Sim có 3 số giống nhau'),
('Sim Tam hoa kép', 'Sim có 2 bộ 3 số giống nhau'),
('Sim Năm sinh', 'Sim có số năm sinh'),
('Sim Lộc phát', 'Sim có số 68, 86'),
('Sim Thần Tài', 'Sim có số 38, 83'),
('Sim Dễ nhớ', 'Sim có số dễ nhớ'),
('Sim trả góp', 'Sim có thể trả góp'),
('Sim giá rẻ', 'Sim có giá dưới 1 triệu'),
('Sim VIP', 'Sim cao cấp'),
('Sim đầu số cổ', 'Sim có đầu số cũ'),
('Sim khuyến mãi', 'Sim đang khuyến mãi'),
('Sim Tiến lên', 'Sim có số tăng dần'),
('Sim Lặp kép', 'Sim có số lặp lại'),
('Sim Gánh đảo', 'Sim có số đối xứng'),
('Sim Số độc', 'Sim có số đặc biệt'),
('Sim Ông Địa', 'Sim có số 38');

-- Insert admin mặc định
INSERT INTO admins (username, email, password, full_name, role) VALUES
('admin', 'admin@simthanglong.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');

-- Bảng đầu số
CREATE TABLE phone_prefixes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    prefix VARCHAR(3) NOT NULL UNIQUE,
    network_id INT,
    description VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (network_id) REFERENCES networks(id),
    INDEX idx_prefix (prefix)
);

-- Bảng khoảng giá
CREATE TABLE price_ranges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    min_price DECIMAL(15,2),
    max_price DECIMAL(15,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng phong thủy
CREATE TABLE feng_shui (
    id INT PRIMARY KEY AUTO_INCREMENT,
    element VARCHAR(20) NOT NULL, -- Mộc, Hỏa, Thổ, Kim, Thủy
    lucky_numbers VARCHAR(50), -- Các số hợp mệnh
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng tin tức
CREATE TABLE news (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    excerpt TEXT,
    image_url VARCHAR(255),
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created (created_at)
);

-- Bảng đánh giá SIM
CREATE TABLE sim_evaluations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sim_id INT,
    customer_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sim_id) REFERENCES sims(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    INDEX idx_sim (sim_id),
    INDEX idx_rating (rating)
);

-- Bảng liên hệ/góp ý
CREATE TABLE contacts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    email VARCHAR(100),
    subject VARCHAR(255),
    message TEXT,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created (created_at)
);

-- Bảng định giá SIM
CREATE TABLE sim_pricing (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sim_id INT,
    estimated_price DECIMAL(15,2),
    pricing_factors JSON, -- Lưu các yếu tố định giá
    pricing_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sim_id) REFERENCES sims(id),
    INDEX idx_sim (sim_id)
);

-- Bảng cầm SIM - thu mua SIM
CREATE TABLE sim_pawn (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT,
    sim_number VARCHAR(15) NOT NULL,
    network VARCHAR(20),
    pawn_amount DECIMAL(15,2),
    interest_rate DECIMAL(5,2) DEFAULT 0,
    duration_months INT DEFAULT 6,
    status ENUM('active', 'redeemed', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    INDEX idx_customer (customer_id),
    INDEX idx_status (status)
);

-- Bảng thanh toán
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    payment_method ENUM('cash', 'bank_transfer', 'credit_card', 'momo', 'zalopay', 'vnpay') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    transaction_id VARCHAR(100),
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_date TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    INDEX idx_order (order_id),
    INDEX idx_status (payment_status)
);

-- Bảng cửa hàng/chi nhánh
CREATE TABLE stores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(50) NOT NULL,
    phone VARCHAR(15),
    email VARCHAR(100),
    manager_name VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_city (city),
    INDEX idx_status (status)
);

-- Bảng năm sinh SIM
CREATE TABLE birth_years (
    id INT PRIMARY KEY AUTO_INCREMENT,
    year INT NOT NULL UNIQUE,
    description VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng loại trừ số (49, 53)
CREATE TABLE excluded_numbers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    number VARCHAR(10) NOT NULL UNIQUE,
    reason VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng khuyến mãi
CREATE TABLE promotions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    discount_type ENUM('percentage', 'fixed_amount') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    min_order_amount DECIMAL(15,2),
    max_discount DECIMAL(15,2),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date)
);

-- Bảng áp dụng khuyến mãi cho SIM
CREATE TABLE sim_promotions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sim_id INT,
    promotion_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sim_id) REFERENCES sims(id),
    FOREIGN KEY (promotion_id) REFERENCES promotions(id),
    UNIQUE KEY unique_sim_promotion (sim_id, promotion_id)
);

-- Bảng đánh giá SIM chi tiết
CREATE TABLE sim_ratings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sim_id INT,
    customer_id INT,
    overall_rating INT CHECK (overall_rating >= 1 AND overall_rating <= 5),
    price_rating INT CHECK (price_rating >= 1 AND price_rating <= 5),
    quality_rating INT CHECK (quality_rating >= 1 AND quality_rating <= 5),
    service_rating INT CHECK (service_rating >= 1 AND service_rating <= 5),
    comment TEXT,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sim_id) REFERENCES sims(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    INDEX idx_sim (sim_id),
    INDEX idx_overall_rating (overall_rating)
);

-- Bảng cấu hình hệ thống
CREATE TABLE system_config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT,
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert dữ liệu cho các bảng mới
INSERT INTO phone_prefixes (prefix, network_id, description) VALUES
('090', 3, 'Mobifone'),
('091', 2, 'Vinaphone'),
('092', 4, 'Vietnamobile'),
('096', 1, 'Viettel'),
('097', 1, 'Viettel'),
('098', 1, 'Viettel'),
('081', 4, 'Vietnamobile'),
('082', 4, 'Vietnamobile'),
('083', 4, 'Vietnamobile'),
('084', 4, 'Vietnamobile'),
('085', 4, 'Vietnamobile'),
('086', 4, 'Vietnamobile'),
('087', 4, 'Vietnamobile'),
('088', 4, 'Vietnamobile'),
('089', 4, 'Vietnamobile'),
('070', 5, 'iTelecom'),
('071', 5, 'iTelecom'),
('072', 5, 'iTelecom'),
('073', 5, 'iTelecom'),
('074', 5, 'iTelecom'),
('075', 5, 'iTelecom'),
('076', 5, 'iTelecom'),
('077', 5, 'iTelecom'),
('078', 5, 'iTelecom'),
('079', 5, 'iTelecom'),
('050', 1, 'Viettel'),
('051', 1, 'Viettel'),
('052', 1, 'Viettel'),
('053', 1, 'Viettel'),
('054', 1, 'Viettel'),
('055', 1, 'Viettel'),
('056', 1, 'Viettel'),
('057', 1, 'Viettel'),
('058', 1, 'Viettel'),
('059', 1, 'Viettel'),
('030', 1, 'Viettel'),
('031', 1, 'Viettel'),
('032', 1, 'Viettel'),
('033', 1, 'Viettel'),
('034', 1, 'Viettel'),
('035', 1, 'Viettel'),
('036', 1, 'Viettel'),
('037', 1, 'Viettel'),
('038', 1, 'Viettel'),
('039', 1, 'Viettel');

INSERT INTO price_ranges (name, min_price, max_price) VALUES
('Dưới 500 nghìn', 0, 500000),
('500K - 1 triệu', 500000, 1000000),
('1 - 3 triệu', 1000000, 3000000),
('3 - 5 triệu', 3000000, 5000000),
('5 - 10 triệu', 5000000, 10000000),
('10 - 50 triệu', 10000000, 50000000),
('50 - 100 triệu', 50000000, 100000000),
('100 - 200 triệu', 100000000, 200000000),
('200 - 500 triệu', 200000000, 500000000),
('Trên 500 triệu', 500000000, NULL);

INSERT INTO feng_shui (element, lucky_numbers, description) VALUES
('Mộc', '0,1,3', 'Người mệnh Mộc hợp với sim tam hoa 0, 1, 3'),
('Hỏa', '3,4,9', 'Người mệnh Hỏa hợp với tam hoa 3, 4, 9'),
('Thổ', '2,5,8,9', 'Người mệnh Thổ hợp tam hoa 2, 5, 8, 9'),
('Kim', '2,5,6,7,8', 'Người mệnh Kim hợp tam hoa 2, 5, 6, 7, 8'),
('Thủy', '0,1,6,7', 'Người mệnh Thủy hợp với tam hoa 0,1, 6, 7');

-- Insert dữ liệu cửa hàng
INSERT INTO stores (name, address, city, phone, email, manager_name) VALUES
('Chi nhánh Hà Nội', '263 Xã Đàn, Đống Đa, Hà Nội', 'Hà Nội', '024.6666.6666', 'hanoi@simthanglong.com', 'Nguyễn Văn A'),
('Chi nhánh TP.HCM', '730 Lũy Bán Bích, Phường Tân Thành, Quận Tân Phú', 'TP. Hồ Chí Minh', '028.6666.6666', 'hcm@simthanglong.com', 'Trần Thị B'),
('Chi nhánh Tuyên Quang', 'Số nhà 54 Lý Nam Đế, tổ 7 phường Tân Quang', 'Tuyên Quang', '0207.6666.6666', 'tuyenquang@simthanglong.com', 'Lê Văn C'),
('Chi nhánh Hưng Yên', 'Số nhà 71 Thủy Nguyên, KĐT Ecopark, Văn Giang', 'Hưng Yên', '0221.6666.6666', 'hungyen@simthanglong.com', 'Phạm Thị D'),
('Chi nhánh Đà Nẵng', 'Số nhà 56/1 Phạm Văn Nghị, Thạch Gián, Thanh Khê', 'Đà Nẵng', '0236.6666.6666', 'danang@simthanglong.com', 'Hoàng Văn E');

-- Insert năm sinh
INSERT INTO birth_years (year, description) VALUES
(1985, 'Năm sinh 1985'),
(1986, 'Năm sinh 1986'),
(1987, 'Năm sinh 1987'),
(1988, 'Năm sinh 1988'),
(1989, 'Năm sinh 1989'),
(1990, 'Năm sinh 1990'),
(1991, 'Năm sinh 1991'),
(1992, 'Năm sinh 1992'),
(1993, 'Năm sinh 1993'),
(1994, 'Năm sinh 1994'),
(1995, 'Năm sinh 1995'),
(1996, 'Năm sinh 1996');

-- Insert số loại trừ
INSERT INTO excluded_numbers (number, reason) VALUES
('49', 'Số không may mắn'),
('53', 'Số không may mắn');

-- Insert khuyến mãi mẫu
INSERT INTO promotions (title, description, discount_type, discount_value, min_order_amount, max_discount, start_date, end_date) VALUES
('Khuyến mãi đầu năm', 'Giảm giá 10% cho đơn hàng trên 5 triệu', 'percentage', 10.00, 5000000, 1000000, '2024-01-01', '2024-12-31'),
('Sim VIP giảm giá', 'Giảm 500k cho sim VIP', 'fixed_amount', 500000, 0, 500000, '2024-01-01', '2024-12-31'),
('Mua combo ưu đãi', 'Mua 2 sim giảm 15%', 'percentage', 15.00, 10000000, 2000000, '2024-01-01', '2024-12-31');

-- Insert cấu hình hệ thống
INSERT INTO system_config (config_key, config_value, description) VALUES
('site_name', 'Sim Thăng Long', 'Tên website'),
('site_phone', '024.6666.6666', 'Số điện thoại hotline'),
('site_email', 'info@simthanglong.com', 'Email liên hệ'),
('free_shipping_min', '1000000', 'Đơn hàng tối thiểu để miễn phí ship'),
('default_currency', 'VND', 'Đơn vị tiền tệ mặc định'),
('pagination_limit', '20', 'Số lượng SIM hiển thị mỗi trang'),
('enable_rating', '1', 'Bật/tắt tính năng đánh giá'),
('enable_pawn', '1', 'Bật/tắt tính năng cầm SIM');

-- Insert SIM mẫu
INSERT INTO sims (phone_number, network, price, sim_type, status) VALUES
-- Mobifone SIMs
('0901234567', 'Mobifone', 2000000, 'Sim Tứ quý', 'available'),
('0901234568', 'Mobifone', 1500000, 'Sim Tam hoa', 'available'),
('0901555555', 'Mobifone', 5000000, 'Sim Ngũ quý', 'available'),
('0901666666', 'Mobifone', 3000000, 'Sim Tứ quý', 'available'),
('0901777777', 'Mobifone', 2500000, 'Sim Tứ quý', 'available'),
('0901888888', 'Mobifone', 4000000, 'Sim Tứ quý', 'available'),
('0901999999', 'Mobifone', 3500000, 'Sim Tứ quý', 'available'),
('0901111111', 'Mobifone', 6000000, 'Sim Ngũ quý', 'available'),
('0901222222', 'Mobifone', 1800000, 'Sim Tứ quý', 'available'),
('0901333333', 'Mobifone', 2200000, 'Sim Tứ quý', 'available'),

-- Viettel SIMs (demo)
('0961234567', 'Viettel', 1800000, 'Sim Tứ quý', 'available'),
('0961234568', 'Viettel', 1200000, 'Sim Tam hoa', 'available'),
('0961555555', 'Viettel', 4500000, 'Sim Ngũ quý', 'available'),
('0961666666', 'Viettel', 2800000, 'Sim Tứ quý', 'available'),
('0961777777', 'Viettel', 2300000, 'Sim Tứ quý', 'available'),

-- Vinaphone SIMs (demo)
('0911234567', 'Vinaphone', 1900000, 'Sim Tứ quý', 'available'),
('0911234568', 'Vinaphone', 1300000, 'Sim Tam hoa', 'available'),
('0911555555', 'Vinaphone', 4800000, 'Sim Ngũ quý', 'available'),
('0911666666', 'Vinaphone', 2900000, 'Sim Tứ quý', 'available'),
('0911777777', 'Vinaphone', 2400000, 'Sim Tứ quý', 'available'),

-- Vietnamobile SIMs (demo)
('0921234567', 'Vietnamobile', 1700000, 'Sim Tứ quý', 'available'),
('0921234568', 'Vietnamobile', 1100000, 'Sim Tam hoa', 'available'),
('0921555555', 'Vietnamobile', 4200000, 'Sim Ngũ quý', 'available'),
('0921666666', 'Vietnamobile', 2700000, 'Sim Tứ quý', 'available'),
('0921777777', 'Vietnamobile', 2200000, 'Sim Tứ quý', 'available');
