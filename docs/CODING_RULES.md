# 📋 QUY ĐỊNH CODE - BUSINESS PRODUCT MANAGEMENT SYSTEM

## 🎯 Nguyên tắc chung

### 1. Giao diện (UI/UX)
- ✅ Luôn làm giao diện **đơn giản**, dễ sử dụng
- ✅ Tập trung vào tính năng, không phức tạp hóa
- ✅ Responsive design cho mobile và desktop
- ✅ Sử dụng Bootstrap cho consistency

### 2. Kiến trúc code
- ✅ **MVC Pattern** bắt buộc
  ```
  Model: Xử lý database, business logic
  View: Hiển thị giao diện
  Controller: Điều phối giữa Model và View
  ```
- ✅ Tuân thủ cấu trúc thư mục sẵn có:
  ```
  src/
  ├── core/           # Core framework
  ├── modules/        # Feature modules
  └── views/          # Shared views
  ```

### 3. Cấu trúc module
Mỗi module phải có đầy đủ:
```
modules/
└── [module_name]/
    ├── controllers/    # Controllers
    ├── models/        # Models
    ├── services/      # Business logic
    └── views/         # View files
```

---

## 🛠️ Quy tắc kỹ thuật

### 1. Dependencies
- ✅ Tất cả thư viện phải quản lý qua **Composer**
- ✅ Không include thư viện bên ngoài trực tiếp
- ✅ File `composer.json` phải được cập nhật

### 2. Frontend Framework
- ✅ Sử dụng **Bootstrap 5.x** cho UI
- ✅ Có thể dùng CDN hoặc local
- ✅ Icons: Font Awesome hoặc Bootstrap Icons
- ✅ JavaScript: jQuery (nếu cần)

### 3. Database
- ✅ **Bắt buộc** tham chiếu `database.md` cho tên bảng và trường
- ✅ Không tự ý đổi tên bảng/cột
- ✅ Sử dụng prepared statements (PDO)
- ✅ Không viết raw SQL trong Controller

#### Ví dụ đúng:
```php
// ✅ Đúng - Lấy tên từ database.md
$users = $this->db->query("SELECT * FROM users WHERE role_id = ?", [$roleId]);

// ❌ Sai - Tên bảng không khớp
$users = $this->db->query("SELECT * FROM user WHERE role = ?", [$role]);
```

---

## 📝 Quy tắc viết code

### 1. Ngôn ngữ
- ✅ **Code**: Tiếng Anh (biến, hàm, class)
- ✅ **Giao diện**: Tiếng Việt (labels, buttons, messages)
- ✅ **Comment**: Tiếng Việt

#### Ví dụ:
```php
// ✅ Đúng
public function showLoginForm()
{
    return $this->render('auth.login', [
        'title' => 'Đăng nhập',  // Tiếng Việt
        'error' => $this->getFlash('error')
    ]);
}

// ❌ Sai
public function hienThiFormDangNhap()  // Không dùng tiếng Việt cho tên hàm
{
    // ...
}
```

### 2. Code Style
- ✅ **Đơn giản, dễ hiểu** - Ưu tiên readability
- ✅ **Không tối ưu hóa quá mức** - Avoid over-engineering
- ✅ **Clean code** - Tránh duplicate code
- ✅ **Không hard code** - Sử dụng constants/config

#### Ví dụ:
```php
// ✅ Đúng - Đơn giản, dễ hiểu
if ($user->status == 1) {
    return 'Hoạt động';
}
return 'Không hoạt động';

// ❌ Sai - Quá phức tạp, hard code
return ($user->status === 1) ? 'Hoạt động' : (($user->status === 0) ? 'Không hoạt động' : 'Unknown');
```

### 3. Comment và Documentation
- ✅ **Bắt buộc** comment cho:
  - Chức năng chính của class/method
  - Logic phức tạp
  - Business rules quan trọng
- ✅ Comment bằng **Tiếng Việt**
- ✅ Format: PHPDoc style

#### Ví dụ:
```php
/**
 * Xác thực thông tin đăng nhập
 * 
 * @param string $email Email hoặc username
 * @param string $password Mật khẩu
 * @return array Thông tin user nếu đăng nhập thành công
 * @throws Exception Nếu thông tin không hợp lệ
 */
public function login($email, $password)
{
    // Tìm user theo email hoặc username
    $user = $this->userModel->findByEmailOrUsername($email);
    
    // Kiểm tra mật khẩu
    if (!password_verify($password, $user['password_hash'])) {
        throw new Exception('Mật khẩu không đúng');
    }
    
    return $user;
}
```

---

## 🔄 Quy trình làm việc

### 1. Trước khi code
✅ **Bắt buộc** tạo TODO List để xác nhận:
```markdown
## TODO: [Tên tính năng]

- [ ] Task 1: Mô tả
- [ ] Task 2: Mô tả
- [ ] Task 3: Mô tả
```

### 2. Trong quá trình code
- ✅ Tuân thủ MVC pattern
- ✅ Tham chiếu `database.md` cho tên bảng/cột
- ✅ Comment code quan trọng
- ✅ Test từng chức năng nhỏ

### 3. Sau khi code
- ✅ Kiểm tra lại code style
- ✅ Test tất cả use cases
- ✅ Update documentation nếu cần
- ✅ Commit với message rõ ràng

---

## 📁 Cấu trúc file chuẩn

### Controller
```php
<?php

namespace Modules\[ModuleName]\Controllers;

use Core\Controller;
use Modules\[ModuleName]\Services\[ServiceName];

class [ClassName]Controller extends Controller
{
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new [ServiceName]();
    }

    /**
     * Mô tả chức năng
     */
    public function index()
    {
        // Logic here
    }
}
```

### Model
```php
<?php

namespace Modules\[ModuleName]\Models;

use Core\Model;

class [ClassName]Model extends Model
{
    protected $table = '[table_name]'; // Lấy từ database.md

    /**
     * Mô tả chức năng
     */
    public function findById($id)
    {
        // Logic here
    }
}
```

### Service
```php
<?php

namespace Modules\[ModuleName]\Services;

use Modules\[ModuleName]\Models\[ModelName];

class [ClassName]Service
{
    private $model;

    public function __construct()
    {
        $this->model = new [ModelName]();
    }

    /**
     * Business logic mô tả
     */
    public function processData($data)
    {
        // Business logic here
    }
}
```

### View
```php
<!-- Mô tả trang -->
<div class="container">
    <h1><?= $title ?></h1>
    
    <!-- Nội dung tiếng Việt -->
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?= $this->e($error) ?>
        </div>
    <?php endif; ?>
    
    <!-- Form/content here -->
</div>
```

---

## 🚫 Những điều KHÔNG NÊN làm

❌ **Không** tối ưu hóa sớm (premature optimization)
❌ **Không** hard code giá trị (magic numbers/strings)
❌ **Không** viết SQL trong Controller
❌ **Không** bỏ qua validation
❌ **Không** dùng tiếng Việt cho tên biến/hàm
❌ **Không** copy-paste code nhiều lần
❌ **Không** commit code chưa test
❌ **Không** tự ý thay đổi database schema

---

## ✅ Best Practices

### Security
```php
// ✅ Escape output
<?= $this->e($userInput) ?>

// ✅ Validate input
if (empty($data['email'])) {
    throw new Exception('Email is required');
}

// ✅ Use prepared statements
$result = $this->db->query("SELECT * FROM users WHERE id = ?", [$id]);
```

### Error Handling
```php
try {
    $result = $this->service->process($data);
    $this->setFlash('success', 'Thao tác thành công');
} catch (Exception $e) {
    $this->setFlash('error', $e->getMessage());
    return $this->redirect('/error-page');
}
```

### Configuration
```php
// ✅ Dùng config file
$dbHost = $this->config['database']['host'];

// ❌ Không hard code
$dbHost = 'localhost';
```

---

## 📚 Tài liệu tham khảo

- **Database Schema**: `database.md`
- **Sample Data**: `config/sample_data.sql`
- **Bootstrap Docs**: https://getbootstrap.com/docs/5.1/
- **PHP PSR Standards**: https://www.php-fig.org/psr/

---

## 📞 Quy trình review code

Trước khi merge code, kiểm tra:
- [ ] Tuân thủ MVC pattern
- [ ] Tên bảng/cột đúng với `database.md`
- [ ] Có comment đầy đủ
- [ ] Code đơn giản, dễ hiểu
- [ ] Không có hard code
- [ ] UI dùng tiếng Việt
- [ ] Đã test các chức năng
- [ ] Bootstrap được sử dụng đúng cách

---

**Cập nhật lần cuối**: 27/10/2025
**Version**: 1.0
