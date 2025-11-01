# Hướng dẫn đồng bộ Database

## Vấn đề

Khi bạn thêm ảnh/dữ liệu mới trên local, người khác pull code về sẽ **KHÔNG** có dữ liệu đó trong database của họ.

## Giải pháp

### Option 1: Export SQL mới nhất (Recommended)

1. **Sau khi thêm dữ liệu mới**, export database:

```bash
# Vào thư mục project
cd d:\xampp\htdocs\Business-product-management-system

# Export toàn bộ database (thay YOUR_DB_NAME)
mysqldump -u root -p business_product_management_system > business_product_management_system_latest.sql
```

2. **Commit file SQL mới**:

```bash
git add business_product_management_system_latest.sql
git commit -m "Update database with new product images"
git push
```

3. **Người khác pull về và import**:

```bash
git pull
mysql -u root -p business_product_management_system < business_product_management_system_latest.sql
```

### Option 2: Tạo Migration File (Professional)

1. Tạo file SQL trong `database_updates/` với tên có ngày:

```
database_updates/2025-10-30_add_product_images.sql
```

2. Viết SQL INSERT cho dữ liệu mới:

```sql
INSERT INTO `product_images` (`product_id`, `url`, `is_primary`, `created_at`) VALUES
(5, '/assets/images/products/image1.jpg', 0, NOW()),
(5, '/assets/images/products/image2.jpg', 0, NOW());
```

3. Commit và người khác chạy file SQL đó

### Option 3: Seed Data (Advanced)

Tạo file `seeds/product_images_seed.sql` với dữ liệu mẫu cho dev environment.

## Lưu ý quan trọng

⚠️ **Files ảnh** được sync qua Git
⚠️ **Database records** KHÔNG được sync qua Git tự động
✅ Phải export SQL hoặc tạo migration file

## Workflow đề xuất

1. Thêm ảnh/dữ liệu mới
2. Export database → commit file `.sql`
3. Push code + SQL file
4. Người khác pull về → import SQL file
