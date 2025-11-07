# 📋 CHECKLIST TRƯỚC KHI SỬA CODE

> **Quy định bắt buộc**: Đọc checklist này TRƯỚC KHI bắt đầu sửa bất kỳ file nào trong dự án.

---

## ✅ BƯỚC 1: ĐỌC TÀI LIỆU

Trước khi sửa code, **BẮT BUỘC** đọc các file sau (nếu liên quan):

- [ ] **`docs/CODING_RULES.md`** - Quy tắc code tổng quan (bắt buộc đọc)
- [ ] **`README.md`** - Tổng quan dự án và cấu trúc
- [ ] **Database schema** (nếu sửa Model/DB):
  - [ ] `business_product_management_system.sql` - Schema hiện tại
  - [ ] `docs/DATABASE_SYNC_GUIDE.md` - Hướng dẫn đồng bộ DB
  - [ ] `docs/CHANGELOG_DATABASE_RESTRUCTURE.md` - Thay đổi DB gần đây
- [ ] **Module-specific docs** (nếu sửa module cụ thể):
  - [ ] `docs/PRODUCT_MODULE_ADD_NEW.md` - Module sản phẩm
  - [ ] `docs/CATEGORY_MANAGEMENT_MODULE.md` - Module danh mục
  - [ ] `docs/PASSWORD_RESET_INSTALLATION.md` - Tính năng reset password
  - [ ] `docs/SYSTEM_ADMIN_MODULE_README.md` - Module admin
- [ ] **Git workflow**:
  - [ ] `docs/GIT_WORKFLOW_AND_PROJECT_GUIDE.md` (nếu làm việc với Git)
  - [ ] `docs/huongdandungbranch.md` - Hướng dẫn branch

---

## ✅ BƯỚC 2: KIỂM TRA TRẠNG THÁI

### Git Status
```bash
git status                    # Xem file đang thay đổi
git branch                    # Xác nhận đang ở đúng branch
git log --oneline -5          # Xem commit gần đây
```

### Database Status
```bash
# Kiểm tra database có đúng version không
mysql -u root -p business_product_management_system -e "SHOW TABLES;"
```

### Application Status
```bash
# Test app có chạy được không
php -S localhost:8000 -t public
# Mở browser: http://localhost:8000
```

---

## ✅ BƯỚC 3: PHÂN TÍCH YÊU CẦU

- [ ] **Hiểu rõ yêu cầu**: Tính năng cần thêm/sửa là gì?
- [ ] **Xác định file cần sửa**: Model? Controller? View?
- [ ] **Kiểm tra file hiện có**:
  ```bash
  # Tìm file liên quan
  find src -name "*Product*"
  grep -r "function calculateTotal" src/
  ```
- [ ] **Đọc code hiện tại**: Hiểu logic đang làm gì trước khi sửa

---

## ✅ BƯỚC 4: TUÂN THỦ QUY TẮC

### Code Style (theo CODING_RULES.md)

- [ ] ✅ Code bằng **Tiếng Anh** (tên biến, hàm, class)
- [ ] ✅ UI/Label bằng **Tiếng Việt** (nút, thông báo, form)
- [ ] ✅ Comment bằng **Tiếng Việt** (PHPDoc format)
- [ ] ✅ Tuân thủ **MVC Pattern**:
  - Model: Chỉ xử lý database, business logic
  - View: Chỉ hiển thị, không có logic
  - Controller: Điều phối, không viết SQL trực tiếp
- [ ] ✅ Không **hard-code** giá trị (dùng constants hoặc config)
- [ ] ✅ Không **copy-paste** code nhiều lần

### Database (nếu sửa Model)

- [ ] ✅ Tên bảng/cột **khớp với** `business_product_management_system.sql`
- [ ] ✅ Dùng **prepared statements** (PDO với `?` placeholder)
- [ ] ✅ **KHÔNG viết raw SQL** trong Controller
- [ ] ✅ Kiểm tra **foreign keys** và **constraints**

### Security

- [ ] ✅ **Escape output** trong view: `<?= \Core\View::e($data) ?>`
- [ ] ✅ **Validate input** trước khi lưu DB
- [ ] ✅ **Hash password** với `password_hash()`, không dùng md5/sha1
- [ ] ✅ **Prepared statements** để tránh SQL injection

### Frontend

- [ ] ✅ Dùng **Bootstrap 5** cho UI
- [ ] ✅ Icons: **Font Awesome** hoặc Bootstrap Icons
- [ ] ✅ **Responsive design** (mobile-friendly)

---

## ✅ BƯỚC 5: VIẾT CODE

### Quy trình

1. **Tạo TODO list** (nếu task phức tạp):
   ```markdown
   ## TODO: [Tên tính năng]
   - [ ] Task 1: Tạo Model
   - [ ] Task 2: Tạo Controller
   - [ ] Task 3: Tạo View
   - [ ] Task 4: Test chức năng
   ```

2. **Viết code từng bước nhỏ**:
   - Sửa 1 file → Test ngay
   - Tránh sửa quá nhiều file cùng lúc

3. **Comment đầy đủ**:
   ```php
   /**
    * Tính tổng giá trị đơn hàng
    * 
    * @param int $orderId ID đơn hàng
    * @return float Tổng tiền
    */
   public function calculateTotal($orderId): float
   {
       // Logic here
   }
   ```

---

## ✅ BƯỚC 6: TEST

### Test thủ công

- [ ] **Chạy ứng dụng**: `php -S localhost:8000 -t public`
- [ ] **Test tính năng mới**: Click qua tất cả use cases
- [ ] **Test tính năng cũ**: Đảm bảo không bị break
- [ ] **Test edge cases**: Input rỗng, số âm, SQL injection, XSS

### Test với nhiều role

- [ ] Test với **Admin** (role_id = 1)
- [ ] Test với **Sales Staff** (role_id = 2)
- [ ] Test với **Warehouse Manager** (role_id = 3)

### Kiểm tra errors

```bash
# Xem PHP errors
tail -f storage/logs/error.log

# Hoặc check browser console (F12)
```

---

## ✅ BƯỚC 7: COMMIT

### Trước khi commit

- [ ] **Đọc lại code** mình vừa viết
- [ ] **Xóa debug code**: `var_dump()`, `die()`, `console.log()`, etc.
- [ ] **Format code**: Indentation đúng (4 spaces)
- [ ] **Xóa commented code** không cần thiết

### Git commit

```bash
# Kiểm tra những gì đã thay đổi
git diff

# Add file cần commit
git add src/Models/ProductModel.php
git add src/Controllers/Admin/ProductController.php

# Commit với message rõ ràng
git commit -m "feat: thêm tính năng tính tổng giá đơn hàng

- Thêm method calculateTotal() trong ProductModel
- Cập nhật ProductController để gọi calculateTotal()
- Test với 3 use cases: đơn hàng trống, 1 sản phẩm, nhiều sản phẩm"

# Push (nếu đã test xong)
git push origin feature/calculate-order-total
```

---

## ✅ BƯỚC 8: UPDATE DOCS (NẾU CẦN)

Nếu thay đổi quan trọng, cập nhật docs:

- [ ] **README.md** - Nếu thay đổi cách chạy project
- [ ] **CODING_RULES.md** - Nếu thêm quy tắc mới
- [ ] **Module docs** - Nếu thay đổi tính năng module
- [ ] **Database schema** - Nếu thay đổi bảng/cột

---

## 🚫 NHỮNG ĐIỀU CẤM LÀM

❌ **KHÔNG BAO GIỜ**:

- [ ] ❌ Sửa code mà **không đọc CODING_RULES.md**
- [ ] ❌ Viết SQL trong **Controller** (phải để trong Model)
- [ ] ❌ Dùng `md5()` hoặc `sha1()` để hash password
- [ ] ❌ Hard-code giá trị (VD: `if ($roleId == 1)` thay vì `ROLE_ADMIN`)
- [ ] ❌ Copy-paste code mà không hiểu
- [ ] ❌ Commit code **chưa test**
- [ ] ❌ Push trực tiếp lên `main` hoặc `master` (phải qua branch)
- [ ] ❌ Tự ý **đổi tên bảng/cột** trong database
- [ ] ❌ Xóa code của người khác mà không hỏi
- [ ] ❌ Merge branch mà **không resolve conflicts**

---

## 📚 TÀI LIỆU THAM KHẢO

| Docs | Mục đích |
|------|----------|
| `docs/CODING_RULES.md` | ⭐ **BẮT BUỘC ĐỌC** - Quy tắc code |
| `docs/GIT_WORKFLOW_AND_PROJECT_GUIDE.md` | Git workflow chi tiết |
| `docs/DATABASE_SYNC_GUIDE.md` | Đồng bộ database |
| `docs/PRODUCT_MODULE_ADD_NEW.md` | Module sản phẩm |
| `docs/CATEGORY_MANAGEMENT_MODULE.md` | Module danh mục |
| `docs/PASSWORD_RESET_INSTALLATION.md` | Reset password |
| `business_product_management_system.sql` | Database schema |

---

## 🎯 TÓM TẮT NHANH

```bash
# 1. ĐỌC docs/CODING_RULES.md
# 2. Kiểm tra git status
git status

# 3. Đọc code hiện tại
grep -r "function productDetail" src/

# 4. Viết code tuân thủ MVC
# 5. Test thủ công
php -S localhost:8000 -t public

# 6. Commit
git add .
git commit -m "feat: mô tả ngắn gọn"
git push origin branch-name
```

---

**Ghi nhớ**: 
- ✅ **Đơn giản, dễ hiểu** > Tối ưu hóa phức tạp
- ✅ **Test trước khi commit** > Fix sau khi push
- ✅ **Đọc docs** > Đoán mò

---

**Version**: 1.0  
**Last updated**: November 7, 2025
