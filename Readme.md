Xay dung phan mem mua ban simcard cua hang SimMobifone

## Cay thu muc
```
WebSim/
  frontend/
    index.html
    login.html
    admin.html
    customer.html
    public.html
    deal.html
    assets/
      css/
        style.css
      js/
        utils.js
        api.js
        auth.js
        admin.js
        customer.js
  backend/
    api/
      _common.php
      auth.php
      sims.php
      orders.php
    storage/
      sims.json
      users.json
      tokens.json
  Readme.md
```

## Vai tro va tai khoan demo
- admin: `admin` / `123456`
- customer: `khach` / `123456`

Dang nhap tai `frontend/login.html`:
- admin → chuyen toi `frontend/admin.html`
- customer → chuyen toi `frontend/customer.html`
- khach chua dang nhap co the xem cong khai tai `frontend/public.html`

## Chay nhanh (PHP built-in)
```powershell
php -S localhost:8000 -t .
```
Mo: `http://localhost:8000/frontend/`

## Chay tren XAMPP (Windows)
1. Mo XAMPP Control Panel → Start Apache (va MySQL neu can, nhung du an nay khong dung DB).
2. Copy ca thu muc `WebSim` vao `C:\xampp\htdocs\` (duong dan thuong gap).
3. Truy cap tren trinh duyet:
   - Trang chinh: `http://localhost:9999/WebSim/frontend/`
   - Dang nhap: `http://localhost:9999/WebSim/frontend/login.html`
   - Admin: `http://localhost:9999/WebSim/frontend/admin.html`
   - Khach hang: `http://localhost:9999/WebSim/frontend/customer.html`
   - Cong khai: `http://localhost:9999/WebSim/frontend/public.html`
   - Deal: `http://localhost:9999/WebSim/frontend/deal.html`

Ghi chu: Frontend da su dung base URL dong, tu dong goi API tai `http://localhost/WebSim/backend/api/...` khi chay trong thu muc con cua XAMPP.

## API chinh
- Auth: POST `/backend/api/auth.php?action=login` body `{ username, password }`
- Logout: POST `/backend/api/auth.php?action=logout` (header Authorization)
- Sims:
  - GET `/backend/api/sims.php` (cong khai)
  - POST `/backend/api/sims.php` (admin) body `{ so, nha_mang, gia, trang_thai }`
  - DELETE `/backend/api/sims.php` (admin) body `{ id }`
- Orders:
  - GET `/backend/api/orders.php` (customer: chi don cua minh, admin: tat ca)
  - POST `/backend/api/orders.php` (customer) body `{ sim_id }`

## Ghi chu
- Du an dung PHP (khong dung DB) voi file JSON trong `backend/storage/`.
- Token dang nhap luu tren localStorage, gui qua header `Authorization: Bearer <token>`.