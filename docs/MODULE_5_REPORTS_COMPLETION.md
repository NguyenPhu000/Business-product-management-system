# Module 5: Báo Cáo & Thống Kê - Hoàn Thành ✅

## 📋 Tổng Quan
Module báo cáo và thống kê đã hoàn thành toàn bộ, bao gồm:
- 3 Report Models (Tồn kho, Doanh thu, Top sản phẩm)
- 1 ReportService với 30+ methods
- 1 ReportController với 10 action methods
- **10 View files** với giao diện đẹp mắt
- **10 Routes** được đăng ký
- **Menu sidebar** được cập nhật

## 🎯 Các Báo Cáo Được Tạo

### 1️⃣ Dashboard Báo Cáo (`/admin/reports`)
**File:** `src/views/admin/reports/dashboard.php`
- Dropdown menu chọn loại báo cáo
- 7 danh mục báo cáo với icon
- Hướng dẫn sử dụng chi tiết

### 2️⃣ Báo Cáo Tồn Kho - Danh Sách Sản Phẩm (`/admin/reports/inventory`)
**File:** `src/views/admin/reports/inventory_report.php`
- Lọc theo trạng thái tồn kho (Còn hàng / Sắp hết / Hết hàng)
- Thống kê số lượng theo từng trạng thái
- Bảng chi tiết sản phẩm với pagination
- Liên kết xem chi tiết sản phẩm

### 3️⃣ Lịch Sử Nhập - Xuất - Tồn (`/admin/reports/transaction-history`)
**File:** `src/views/admin/reports/transaction_history.php`
- Lọc theo loại giao dịch (Nhập / Xuất / Điều chỉnh)
- Lọc theo khoảng thời gian (Từ - Đến ngày)
- Thống kê số lượng giao dịch theo loại
- Bảng chi tiết với mô tả và ghi chú

### 4️⃣ Báo Cáo Doanh Thu (`/admin/reports/sales`)
**File:** `src/views/admin/reports/sales_report.php`
- 3 Tab: Theo sản phẩm / Theo danh mục / Xu hướng hàng ngày
- Tổng doanh thu, số đơn, giá trị trung bình, tổng SKU
- Lọc theo khoảng thời gian
- Biểu đồ tiến độ cho tỷ lệ doanh thu theo danh mục

### 5️⃣ Báo Cáo Lợi Nhuận Gộp (`/admin/reports/profit`)
**File:** `src/views/admin/reports/profit_report.php`
- Tổng doanh thu, giá vốn, lợi nhuận, margin
- Lọc theo khoảng thời gian
- Chi tiết theo sản phẩm: Doanh thu - Giá vốn - Lợi nhuận - Margin%
- Biểu đồ màu cho margin (Danger < 10% / Warning 10-20% / Info 20-30% / Success > 30%)
- Hướng dẫn cách tính lợi nhuận gộp

### 6️⃣ Sản Phẩm Bán Chạy Nhất (`/admin/reports/top-selling`)
**File:** `src/views/admin/reports/top_selling_products.php`
- Top 5 / 10 / 15 / 20 / 50 sản phẩm bán chạy
- Lọc theo khoảng thời gian
- Huy chương 🥇🥈🥉 cho 3 sản phẩm đầu
- Hiển thị % doanh thu của mỗi sản phẩm
- Gợi ý chiến lược: Quảng cáo, Bundle products, Tối ưu giá

### 7️⃣ Sản Phẩm Tồn Kho Lâu, Ít Bán (`/admin/reports/slow-moving`)
**File:** `src/views/admin/reports/slow_moving_inventory.php`
- Lọc Top N (10 / 20 / 30 / 50)
- Lọc theo số ngày không bán (14 / 30 / 60 / 90 / 180 ngày)
- Cảnh báo vàng về chi phí lưu kho
- Hiển thị giá trị tồn kho = Số lượng × Giá vốn
- Hành động đề xuất: Khuyến mãi, Bundle, Stop kinh doanh

### 8️⃣ Dead Stock - Sản Phẩm Chưa Bao Giờ Bán (`/admin/reports/dead-stock`)
**File:** `src/views/admin/reports/dead_stock.php`
- **Cảnh báo đỏ** - Rất quan trọng
- Danh sách sản phẩm có tồn kho nhưng KHÔNG Bao GIỜ xuất hiện trong đơn bán
- Thống kê: Tổng sản phẩm, tổng số lượng, tổng giá trị tồn
- Kế hoạch hành động: Ngắn hạn (1-2 tuần) và Dài hạn (1-3 tháng)

### 9️⃣ Sản Phẩm Giá Trị Cao (`/admin/reports/high-value`)
**File:** `src/views/admin/reports/high_value_products.php`
- Top N sản phẩm có giá trị tồn kho cao nhất
- Giá trị tồn = Số lượng × Giá vốn/cái
- Biểu đồ % tổng vốn buộc
- Thống kê: Tổng sản phẩm, tổng tồn kho, tổng giá trị
- Quản lý rủi ro: Giám sát doanh số, Tối ưu dòng tiền

### 🔟 Sản Phẩm Lợi Nhuận Cao (`/admin/reports/top-profit`)
**File:** `src/views/admin/reports/top_profit_products.php`
- Top N sản phẩm lợi nhuận cao nhất
- Xếp hạng với huy chương 🥇🥈🥉
- Hiển thị: Số lượng bán, Doanh thu, Giá vốn, Lợi nhuận, Margin%
- Biểu đồ margin color-coded
- Thống kê tổng lợi nhuận và average margin
- Chiến lược: Tập trung bán, Tối ưu hóa

---

## 📁 Cấu Trúc File

```
src/
├── modules/
│   └── report/
│       ├── models/
│       │   ├── InventoryReportModel.php        ✅
│       │   ├── SalesReportModel.php            ✅
│       │   └── TopProductsReportModel.php      ✅
│       ├── services/
│       │   └── ReportService.php               ✅ (Cập nhật)
│       └── controllers/
│           └── ReportController.php            ✅ (Cập nhật)
└── views/
    └── admin/
        └── reports/
            ├── dashboard.php                   ✅
            ├── inventory_report.php            ✅
            ├── transaction_history.php         ✅
            ├── sales_report.php                ✅
            ├── profit_report.php               ✅
            ├── top_selling_products.php        ✅
            ├── slow_moving_inventory.php       ✅
            ├── dead_stock.php                  ✅
            ├── high_value_products.php         ✅
            └── top_profit_products.php         ✅

config/
└── routes.php                                  ✅ (Cập nhật +10 routes)

views/admin/layout/
└── sidebar.php                                 ✅ (Cập nhật + Reports menu)

public/assets/css/
└── admin-style.css                            ✅ (Cập nhật CSS)
```

---

## 🛣️ Routes Đã Đăng Ký

```php
GET  /admin/reports                    → dashboard (Chọn báo cáo)
GET  /admin/reports/inventory          → inventoryReport
GET  /admin/reports/transaction-history → transactionHistory
GET  /admin/reports/sales              → salesReport
GET  /admin/reports/profit             → profitReport
GET  /admin/reports/top-selling        → topSellingProducts
GET  /admin/reports/slow-moving        → slowMovingInventory
GET  /admin/reports/dead-stock         → deadStock
GET  /admin/reports/high-value         → highValueProducts
GET  /admin/reports/top-profit         → topProfitProducts
```

---

## 🔐 Bảo Mật & Phân Quyền

Tất cả routes được bảo vệ bằng middleware:
- `AuthMiddleware` - Yêu cầu đăng nhập
- `RoleMiddleware` - Yêu cầu role phù hợp

Chỉ Admin, Owner, Sales Staff, Warehouse Manager có thể truy cập (ROLE_ID >= 2)

---

## 📊 Tính Năng Chi Tiết

### ✨ Giao Diện
- Bootstrap 5 Responsive Design
- Icons FontAwesome đẹp mắt
- Bảng dữ liệu với hover effects
- Phân trang cho dữ liệu lớn
- Progress bars và status badges
- Alert boxes với thông tin hữu ích

### 🔍 Lọc & Tìm Kiếm
- Lọc theo ngày (Từ - Đến)
- Lọc theo trạng thái
- Lọc theo loại giao dịch
- Lọc Top N (5/10/15/20/50)
- Reset bộ lọc

### 📈 Hiển Thị Dữ Liệu
- Tổng hợp thống kê
- Chi tiết theo hàng
- Percentage tỷ lệ
- Biểu đồ tiến độ
- Thứ hạng & huy chương
- Formatting tiền tệ VND

### 💡 Hướng Dẫn & Gợi Ý
- Hướng dẫn sử dụng dashboard
- Cách tính lợi nhuận gộp
- Gợi ý chiến lược bán hàng
- Kế hoạch hành động
- Quản lý rủi ro

---

## 🚀 Cách Sử Dụng

### 1. Truy cập Dashboard
```
Vào menu Sidebar → Báo cáo & Thống kê → Dashboard báo cáo
Hoặc: /admin/reports
```

### 2. Chọn Báo Cáo
Từ dashboard, click vào nút báo cáo bạn muốn xem

### 3. Lọc Dữ Liệu
- Chọn tiêu chí lọc (ngày, loại, top N)
- Click "Tìm Kiếm" để áp dụng
- Click "Đặt Lại" để xóa bộ lọc

### 4. Xem Chi Tiết
- Hover vào hàng để highlight
- Click icon xem chi tiết để vào sản phẩm
- Phân trang để duyệt dữ liệu nhiều

---

## 📝 Công Thức Tính

### Lợi Nhuận Gộp
```
Lợi Nhuận = Doanh Thu - Giá Vốn
Doanh Thu = Giá Bán × Số Lượng Bán
Giá Vốn = Unit Cost × Số Lượng Bán
Margin % = (Lợi Nhuận / Doanh Thu) × 100
```

### Giá Trị Tồn Kho
```
Giá Trị Tồn = Số Lượng Tồn × Giá Vốn/Cái
```

### Slow Moving Inventory
```
Điều kiện: Số lượng > Min Threshold 
         AND (Chưa bao giờ bán OR Last Sale Date >= 30 ngày)
```

### Dead Stock
```
Điều kiện: Số lượng > 0 
         AND Không xuất hiện trong bất kỳ Sales Details nào
Phát hiện: LEFT JOIN sales_details WHERE sales_details.id IS NULL
```

---

## ✅ Checklist Hoàn Thành

- [x] 3 Report Models tạo đầy đủ
- [x] ReportService với 30+ methods
- [x] ReportController với 10 action methods
- [x] 10 View files được tạo
- [x] 10 Routes được đăng ký
- [x] Sidebar menu được cập nhật
- [x] CSS styling được thêm
- [x] Bảo mật & phân quyền
- [x] Responsive design
- [x] Formatting tiền tệ VND
- [x] Hướng dẫn & gợi ý chiến lược

---

## 🎓 Tiếp Theo

Để kiểm tra hoạt động:
1. Truy cập `/admin/reports` 
2. Chọn một báo cáo
3. Tạo/nhập dữ liệu để test
4. Lọc & kiểm tra kết quả

---

**Module 5 Hoàn Thành Lúc:** 24/11/2025
**Tác Giả:** GitHub Copilot
**Status:** ✅ DONE
