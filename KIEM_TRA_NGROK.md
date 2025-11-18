# Kiểm tra Ngrok đã hoạt động đúng chưa

## Bước 1: Kiểm tra Ngrok đang forward đúng port

Mở Command Prompt/PowerShell nơi bạn chạy ngrok, bạn sẽ thấy:
```
Forwarding    https://kristel-polyzoarial-votively.ngrok-free.dev -> http://localhost:9999
```

**Quan trọng:** Phải là port **9999**, không phải port 80!

Nếu ngrok đang forward port 80, bạn cần:
1. Dừng ngrok (Ctrl+C)
2. Chạy lại: `ngrok http 9999`

## Bước 2: Test Ngrok URL

### Test 1: Kiểm tra API qua Ngrok
Mở trình duyệt và truy cập:
```
https://kristel-polyzoarial-votively.ngrok-free.dev/WebSIM-main/WebSIM-main/api/test_ngrok.php
```

Bạn sẽ thấy JSON response với thông tin về cấu hình. Kiểm tra:
- ✓ `is_https` phải là `true`
- ✓ `ngrok_url_configured` phải khớp với URL hiện tại
- ✓ `access_key_set` và `secret_key_set` phải là `YES`

### Test 2: Kiểm tra CORS
Truy cập:
```
https://kristel-polyzoarial-votively.ngrok-free.dev/WebSIM-main/WebSIM-main/api/test_cors.php
```

Phải thấy JSON response với `success: true`

### Test 3: Kiểm tra MoMo Payment API
Truy cập (sẽ trả về lỗi nhưng phải có response):
```
https://kristel-polyzoarial-votively.ngrok-free.dev/WebSIM-main/WebSIM-main/api/momo_payment.php
```

Phải thấy JSON response (không phải HTML error page)

## Bước 3: Kiểm tra cấu hình trong momo_config.php

File `WebSIM-main/api/momo_config.php` phải có:
```php
define('NGROK_URL', 'https://kristel-polyzoarial-votively.ngrok-free.dev');
```

**Lưu ý:**
- Không có dấu `/` ở cuối
- Phải là `https://` (không phải `http://`)
- Không có khoảng trắng

## Bước 4: Test thanh toán MoMo

1. Đảm bảo:
   - ✓ Ngrok đang chạy
   - ✓ XAMPP Apache đang chạy trên port 9999
   - ✓ Đã cập nhật `NGROK_URL` trong `momo_config.php`

2. Thử đặt hàng và chọn thanh toán MoMo

3. Kiểm tra console logs:
   - Mở Developer Console (F12)
   - Xem Network tab
   - Kiểm tra request đến `momo_payment.php`
   - Xem response để kiểm tra URLs được gửi đến MoMo

## Bước 5: Kiểm tra URLs được gửi đến MoMo

Trong response từ `momo_payment.php`, kiểm tra debug info:
- `redirectUrl` phải bắt đầu với `https://kristel-polyzoarial-votively.ngrok-free.dev`
- `ipnUrl` phải bắt đầu với `https://kristel-polyzoarial-votively.ngrok-free.dev`
- Không được có `localhost` hoặc `127.0.0.1`

## Troubleshooting

### Vấn đề: Ngrok hiển thị directory listing thay vì website
**Nguyên nhân:** Ngrok đang forward port 80 thay vì 9999

**Giải pháp:**
1. Dừng ngrok (Ctrl+C trong cửa sổ ngrok)
2. Chạy lại: `ngrok http 9999`
3. Copy URL mới và cập nhật trong `momo_config.php`

### Vấn đề: Lỗi 404 khi truy cập qua ngrok
**Nguyên nhân:** Path không đúng

**Giải pháp:**
- Đảm bảo path đúng: `/WebSIM-main/WebSIM-main/api/...`
- Kiểm tra XAMPP có đang chạy trên port 9999 không

### Vấn đề: MoMo vẫn báo lỗi localhost
**Nguyên nhân:** 
- Ngrok URL chưa được cập nhật trong config
- Hoặc ngrok đang forward port sai

**Giải pháp:**
1. Kiểm tra lại `NGROK_URL` trong `momo_config.php`
2. Restart Apache sau khi cập nhật config
3. Kiểm tra ngrok đang forward port 9999

### Vấn đề: Ngrok URL thay đổi mỗi lần restart
**Giải pháp:**
- Đây là bình thường với tài khoản Free
- Mỗi lần restart ngrok, cần cập nhật lại `NGROK_URL`
- Hoặc nâng cấp lên tài khoản Pro để có domain tĩnh

## Checklist trước khi test MoMo:

- [ ] Ngrok đang chạy và forward port 9999
- [ ] XAMPP Apache đang chạy trên port 9999
- [ ] `NGROK_URL` đã được cập nhật trong `momo_config.php`
- [ ] Test URL `test_ngrok.php` hoạt động qua ngrok
- [ ] URLs trong response không chứa localhost
- [ ] Đang sử dụng HTTPS (không phải HTTP)

Sau khi hoàn thành checklist, thử thanh toán MoMo lại!

