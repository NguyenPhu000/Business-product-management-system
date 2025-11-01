# Chuyển đổi lưu ảnh sang Base64

## Tại sao cần Base64?

**Trước đây:**

- Ảnh lưu dưới dạng file trong `/public/assets/images/products/`
- Database chỉ lưu đường dẫn (URL)
- ❌ Khi push code, người khác phải pull cả code VÀ file ảnh
- ❌ Nếu chỉ có database, không có ảnh

**Bây giờ:**

- Ảnh được encode thành Base64 và lưu TRỰC TIẾP trong database
- ✅ Khi kết nối database, có ngay ảnh (không cần pull code)
- ✅ Database độc lập hoàn toàn với file system

## Cấu trúc mới

### Bảng `product_images`

| Cột          | Kiểu              | Mô tả                              |
| ------------ | ----------------- | ---------------------------------- |
| `id`         | INT               | Primary key                        |
| `product_id` | INT               | ID sản phẩm                        |
| `url`        | VARCHAR(255) NULL | Đường dẫn (cũ, giữ để tương thích) |
| `image_data` | LONGTEXT NULL     | **Base64 encoded image**           |
| `mime_type`  | VARCHAR(50) NULL  | image/jpeg, image/png, etc.        |
| `is_primary` | TINYINT           | Ảnh chính (1) hay phụ (0)          |
| `sort_order` | INT               | Thứ tự hiển thị                    |

## Cách hoạt động

### 1. Upload ảnh (ProductController)

```php
// Đọc file và convert sang base64
$imageData = file_get_contents($fileTmpName);
$base64Data = base64_encode($imageData);

// Lưu vào database
$this->productImageModel->create([
    'product_id' => $productId,
    'url' => null,  // Không cần lưu file
    'image_data' => $base64Data,
    'mime_type' => $fileType,
    'is_primary' => $isPrimary
]);
```

### 2. Hiển thị ảnh (ProductImageModel)

```php
// Tự động convert base64 thành data URL
if (!empty($image['image_data'])) {
    $image['display_url'] = "data:{$mimeType};base64,{$base64Data}";
} else {
    $image['display_url'] = $image['url']; // Fallback cho ảnh cũ
}
```

### 3. Render trong HTML

```php
<img src="<?= $image['display_url'] ?>" alt="Product">
```

Browser tự động hiển thị ảnh từ base64!

## Ưu điểm

✅ **Database self-contained**: Database chứa toàn bộ dữ liệu, kể cả ảnh
✅ **Không cần sync files**: Người khác chỉ cần kết nối database là có ngay ảnh
✅ **Backup dễ dàng**: Export database = có luôn ảnh
✅ **Cloud-ready**: Dễ deploy lên cloud database

## Nhược điểm

⚠️ **Kích thước database lớn hơn**: Base64 tăng ~33% so với file gốc
⚠️ **Truy vấn chậm hơn**: Khi SELECT nhiều sản phẩm với ảnh lớn
⚠️ **Không cache được**: Browser không cache base64 như cache file

## Giải pháp lai (Hybrid)

Có thể giữ cả 2 cách:

- **Development/Shared DB**: Dùng base64
- **Production**: Convert sang file và lưu URL

## Migration

Chạy file: `run_migration.php` để thêm cột mới

```bash
php run_migration.php
```

## Chuyển đổi ảnh cũ sang Base64

```php
// Script convert ảnh cũ (nếu cần)
$images = $pdo->query("SELECT * FROM product_images WHERE url IS NOT NULL AND image_data IS NULL");

foreach ($images as $img) {
    $filePath = __DIR__ . '/public' . $img['url'];
    if (file_exists($filePath)) {
        $data = file_get_contents($filePath);
        $base64 = base64_encode($data);
        $mime = mime_content_type($filePath);

        $pdo->prepare("UPDATE product_images SET image_data = ?, mime_type = ? WHERE id = ?")
            ->execute([$base64, $mime, $img['id']]);
    }
}
```

## Kết luận

Giải pháp này phù hợp cho:

- ✅ Team nhỏ, share database qua VPN/Tailscale
- ✅ Demo/Testing environment
- ✅ Ảnh có kích thước nhỏ (< 1MB)

KHÔNG phù hợp cho:

- ❌ Production với hàng ngàn sản phẩm
- ❌ Ảnh có kích thước lớn (> 2MB)
- ❌ Cần performance cao
