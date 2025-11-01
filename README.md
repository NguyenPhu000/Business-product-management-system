# Business Product Management System

Hệ thống quản lý sản phẩm kinh doanh xây dựng bằng PHP thuần (không framework).

## 📋 Tính năng

- **Quản lý người dùng**: Đăng nhập, đăng ký, phân quyền
- **Quản lý danh mục**: Cây danh mục phân cấp
- **Quản lý sản phẩm**: CRUD sản phẩm, biến thể, hình ảnh
- **Quản lý tồn kho**: Xem tồn kho, điều chỉnh số lượng, lịch sử
- **Quản lý mua hàng**: Tạo đơn mua hàng, nhập kho
- **Quản lý bán hàng**: Tạo đơn bán hàng, xuất kho, in hóa đơn
- **Báo cáo thống kê**: Dashboard, báo cáo tồn kho, doanh thu, lãi lỗ

## 🛠️ Công nghệ

- PHP 8.0+
- MySQL 5.7+
- Apache with mod_rewrite
- Composer

## 📦 Cài đặt

### 1. Clone repository

```bash
git clone https://github.com/NguyenPhu000/Business-product-management-system.git
cd Business-product-management-system
```

### 2. Cài đặt dependencies

```bash
composer install
```

### 3. Cấu hình môi trường

```bash
# Copy file .env.example thành .env
cp .env.example .env

# Sửa thông tin database trong file .env
```

### 4. Tạo database

```sql
CREATE DATABASE business_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Import database schema

```bash
# TODO: Import file database/schema.sql
```

### 6. Chạy ứng dụng

```bash
# Chạy PHP built-in server
php -S localhost:8000 -t public

# Hoặc cấu hình Apache DocumentRoot trỏ đến thư mục public/
```

### 7. Truy cập

Mở trình duyệt: `http://localhost:8000`

## 📁 Cấu trúc thư mục

```
├── config/          # Cấu hình hệ thống
├── public/          # Document root (index.php, assets)
├── src/
│   ├── core/        # Các thành phần lõi
│   ├── modules/     # Modules chức năng
│   └── views/       # Views dùng chung
├── storage/         # Logs, cache, sessions
├── tests/           # Unit & Feature tests
└── vendor/          # Composer packages
```

## 🔐 Tài khoản mặc định

- **Admin**: admin@example.com / password
- **Manager**: manager@example.com / password

## 🤝 Đóng góp

1. Fork repository
2. Tạo branch mới: `git checkout -b feature/your-feature`
3. Commit changes: `git commit -m 'Add some feature'`
4. Push to branch: `git push origin feature/your-feature`
5. Tạo Pull Request

## 📝 License

MIT License - xem file [LICENSE](LICENSE) để biết thêm chi tiết.

## 👥 Nhóm phát triển

- Nguyễn Phú ([@NguyenPhu000](https://github.com/NguyenPhu000))

## 📞 Liên hệ

- Email: your-email@example.com
- GitHub: https://github.com/NguyenPhu000/Business-product-management-system
