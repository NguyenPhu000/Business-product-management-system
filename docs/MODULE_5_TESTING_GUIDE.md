# 🧪 Hướng Dẫn Test Module 5 - Báo Cáo & Thống Kê

## 📋 Chuẩn Bị Test

### 1. Yêu Cầu Tiên Quyết
- Đã đăng nhập admin account
- Database có dữ liệu sản phẩm, tồn kho, đơn bán
- Tối thiểu:
  - 5+ sản phẩm
  - 3+ danh mục
  - 5+ giao dịch nhập/xuất
  - 3+ đơn bán hàng

### 2. Dữ Liệu Test Cơ Bản

```sql
-- Kiểm tra có dữ liệu không
SELECT COUNT(*) FROM products;
SELECT COUNT(*) FROM inventory_transactions;
SELECT COUNT(*) FROM sales_orders;
```

---

## 🚀 Test Scenarios

### Test 1️⃣: Dashboard Báo Cáo
**URL:** `http://localhost/admin/reports`

✅ **Test Cases:**
1. [ ] Trang tải đúng, không lỗi
2. [ ] Hiển thị 7 danh mục báo cáo
3. [ ] Hiển thị 4 summary cards
4. [ ] Các nút bấm hoạt động
5. [ ] Responsive trên mobile

**Kết quả mong đợi:**
- Giao diện sạch, sắp xếp hợp lý
- Tất cả nút dẫn đến báo cáo đúng

---

### Test 2️⃣: Báo Cáo Tồn Kho - Danh Sách Sản Phẩm
**URL:** `http://localhost/admin/reports/inventory`

✅ **Test Cases:**
1. [ ] Trang tải đúng
2. [ ] Hiển thị 4 summary cards thống kê
3. [ ] **Lọc "Tất Cả"** - hiển thị tất cả sản phẩm
4. [ ] **Lọc "Còn Hàng"** - chỉ hiển thị quantity > 0
5. [ ] **Lọc "Sắp Hết Hàng"** - quantity < min_stock_level và > 0
6. [ ] **Lọc "Hết Hàng"** - quantity = 0
7. [ ] **Phân trang** - click page 2, 3...
8. [ ] **Xem Chi Tiết** - click icon mắt đi đến sản phẩm

**Kết quả mong đợi:**
- Các status badge đúng màu (xanh/vàng/đỏ)
- Số lượng thống kê match với dữ liệu

---

### Test 3️⃣: Lịch Sử Nhập - Xuất - Tồn
**URL:** `http://localhost/admin/reports/transaction-history`

✅ **Test Cases:**
1. [ ] Trang tải đúng
2. [ ] Hiển thị 4 summary cards
3. [ ] **Lọc "Tất Cả"** - hiển thị tất cả giao dịch
4. [ ] **Lọc "Nhập Hàng"** - chỉ import transactions
5. [ ] **Lọc "Xuất Hàng"** - chỉ export transactions
6. [ ] **Lọc "Điều Chỉnh"** - chỉ adjustment transactions
7. [ ] **Lọc ngày** - chọn từ ngày, đến ngày
8. [ ] **Tooltip notes** - hover vào ghi chú

**Kết quả mong đợi:**
- Badge màu đúng (xanh/đỏ/vàng)
- Chỉ hiển thị giao dịch trong khoảng ngày chọn

---

### Test 4️⃣: Báo Cáo Doanh Thu
**URL:** `http://localhost/admin/reports/sales`

✅ **Test Cases:**
1. [ ] Trang tải đúng
2. [ ] Tab "Doanh Thu Theo Sản Phẩm"
   - [ ] Hiển thị danh sách sản phẩm
   - [ ] Tính toán doanh thu đúng (giá × số lượng)
3. [ ] Tab "Doanh Thu Theo Danh Mục"
   - [ ] Hiển thị danh mục
   - [ ] Progress bar % doanh thu
4. [ ] Tab "Xu Hướng Hàng Ngày"
   - [ ] Hiển thị theo ngày
   - [ ] Progress bar so sánh
5. [ ] **Lọc ngày** - chọn khoảng thời gian
6. [ ] **Định dạng tiền** - hiển thị ₫ đúng

**Kết quả mong đợi:**
- Doanh thu = SUM(price × quantity) cho mỗi sản phẩm
- Tổng doanh thu match khi cộng tất cả

---

### Test 5️⃣: Báo Cáo Lợi Nhuận Gộp
**URL:** `http://localhost/admin/reports/profit`

✅ **Test Cases:**
1. [ ] Trang tải đúng
2. [ ] Hiển thị 4 summary cards
3. [ ] **Lọc ngày** - chọn khoảng thời gian
4. [ ] Bảng chi tiết sản phẩm
   - [ ] Doanh Thu = price × quantity
   - [ ] Giá Vốn = unit_cost × quantity
   - [ ] Lợi Nhuận = Doanh Thu - Giá Vốn
   - [ ] Margin = (Lợi Nhuận / Doanh Thu) × 100
5. [ ] Progress bar margin color-coded
   - [ ] Đỏ < 10%
   - [ ] Vàng 10-20%
   - [ ] Xanh dương 20-30%
   - [ ] Xanh lá > 30%
6. [ ] Phân trang

**Kết quả mong đợi:**
- Tính toán lợi nhuận chính xác
- Margin % từ 0-100%

---

### Test 6️⃣: Sản Phẩm Bán Chạy Nhất
**URL:** `http://localhost/admin/reports/top-selling`

✅ **Test Cases:**
1. [ ] Trang tải đúng
2. [ ] **Chọn Top** - 5, 10, 15, 20, 50
3. [ ] **Lọc ngày** - chọn khoảng thời gian
4. [ ] Hiển thị xếp hạng
   - [ ] #1 huy chương vàng 🥇
   - [ ] #2 huy chương bạc 🥈
   - [ ] #3 huy chương đồng 🥉
5. [ ] Progress bar % doanh thu
6. [ ] Sắp xếp giảm dần theo số lượng bán

**Kết quả mong đợi:**
- Top sản phẩm có số lượng bán cao nhất
- Tổng % = 100%

---

### Test 7️⃣: Sản Phẩm Tồn Kho Lâu, Ít Bán
**URL:** `http://localhost/admin/reports/slow-moving`

✅ **Test Cases:**
1. [ ] Trang tải đúng
2. [ ] **Chọn Top** - 10, 20, 30, 50
3. [ ] **Chọn ngày không bán** - 14, 30, 60, 90, 180 ngày
4. [ ] Hiển thị sản phẩm
   - [ ] Có tồn kho > 0
   - [ ] Chưa bao giờ bán HOẶC không bán từ N ngày
5. [ ] Giá Trị Tồn = Số Lượng × Giá Vốn/Cái
6. [ ] Ngày chưa bán >= số ngày đã chọn

**Kết quả mong đợi:**
- Chỉ hiển thị sản phẩm chậm chân
- Alert vàng cảnh báo

---

### Test 8️⃣: Dead Stock - Chưa Bao Giờ Bán
**URL:** `http://localhost/admin/reports/dead-stock`

✅ **Test Cases:**
1. [ ] Trang tải đúng
2. [ ] **Chọn hiển thị** - 10, 20, 50, All
3. [ ] Hiển thị sản phẩm
   - [ ] Có tồn kho > 0
   - [ ] **KHÔNG BAO GIỜ** xuất hiện trong sales_details
4. [ ] Alert đỏ cảnh báo
5. [ ] Thống kê:
   - [ ] Tổng sản phẩm dead stock
   - [ ] Tổng số lượng tồn
   - [ ] Tổng giá trị tồn

**Kết quả mong đợi:**
- Chỉ hiển thị sản phẩm never sold
- Giá trị tồn = số lượng × unit_cost

---

### Test 9️⃣: Sản Phẩm Giá Trị Cao
**URL:** `http://localhost/admin/reports/high-value`

✅ **Test Cases:**
1. [ ] Trang tải đúng
2. [ ] **Chọn Top** - 10, 20, 30, 50
3. [ ] Hiển thị sản phẩm sắp xếp theo giá trị tồn giảm dần
4. [ ] Giá Trị = Số Lượng Tồn × Giá Vốn/Cái
5. [ ] Progress bar % tổng vốn
6. [ ] Thống kê 3 cards
   - [ ] Số sản phẩm
   - [ ] Tổng tồn kho
   - [ ] Tổng giá trị tồn

**Kết quả mong đợi:**
- Top sản phẩm có giá trị buộc vốn cao nhất
- Tổng % = 100%

---

### Test 🔟: Sản Phẩm Lợi Nhuận Cao
**URL:** `http://localhost/admin/reports/top-profit`

✅ **Test Cases:**
1. [ ] Trang tải đúng
2. [ ] **Chọn Top** - 10, 20, 30, 50
3. [ ] **Lọc ngày** - chọn khoảng thời gian
4. [ ] Hiển thị sản phẩm sắp xếp theo lợi nhuận giảm dần
5. [ ] Huy chương cho 3 đầu
6. [ ] Progress bar margin color-coded
7. [ ] Thống kê
   - [ ] Số sản phẩm
   - [ ] Tổng lợi nhuận
   - [ ] Average margin

**Kết quả mong đợi:**
- Top sản phẩm có lợi nhuận cao nhất
- Margin% chính xác

---

## 🔍 Các Trường Hợp Đặc Biệt

### Khi Không Có Dữ Liệu
- [ ] Hiển thị "Không có dữ liệu..." message
- [ ] Không lỗi 500
- [ ] Giao diện vẫn đẹp

### Khi Có Dữ Liệu Nhiều
- [ ] Phân trang hoạt động tốt
- [ ] Không lag/treo
- [ ] Load time < 3 giây

### Responsive Design
- [ ] Desktop (1920px): Bình thường
- [ ] Tablet (768px): Bảng scroll ngang
- [ ] Mobile (480px): Hiển thị đúng

### Định Dạng Tiền
- [ ] 0-999: 0 ₫
- [ ] 1,000+: 1.000 ₫ (dấu phẩy)
- [ ] 1,000,000+: 1.000.000 ₫

---

## 📊 Test Data Preparation

Nếu muốn test với dữ liệu thực:

```sql
-- Kiểm tra dữ liệu hiện tại
SELECT p.id, p.name, COUNT(sd.id) as sales_count
FROM products p
LEFT JOIN product_variants pv ON p.id = pv.product_id
LEFT JOIN sales_details sd ON pv.id = sd.variant_id
GROUP BY p.id
ORDER BY sales_count DESC;

-- Kiểm tra tồn kho
SELECT pv.id, p.name, i.current_quantity, i.min_stock_level
FROM product_variants pv
JOIN products p ON pv.product_id = p.id
JOIN inventory i ON pv.id = i.variant_id
ORDER BY i.current_quantity DESC;

-- Kiểm tra lịch sử giao dịch
SELECT DATE(transaction_date), transaction_type, COUNT(*) 
FROM inventory_transactions
GROUP BY DATE(transaction_date), transaction_type
ORDER BY transaction_date DESC;
```

---

## ✅ Checklist Hoàn Thành Test

- [ ] Test 1: Dashboard OK
- [ ] Test 2: Inventory Report OK
- [ ] Test 3: Transaction History OK
- [ ] Test 4: Sales Report OK
- [ ] Test 5: Profit Report OK
- [ ] Test 6: Top Selling OK
- [ ] Test 7: Slow Moving OK
- [ ] Test 8: Dead Stock OK
- [ ] Test 9: High Value OK
- [ ] Test 10: Top Profit OK
- [ ] Responsive Design OK
- [ ] Không có lỗi Console
- [ ] Không có lỗi 404/500

---

## 🐛 Nếu Gặp Lỗi

### Lỗi 404
```
Kiểm tra: config/routes.php có 10 routes không?
```

### Lỗi 500
```
Kiểm tra: Error log trong storage/logs/
```

### Dữ Liệu Trống
```
INSERT test data vào database trước khi test
```

### CSS/JS Không Load
```
Clear cache browser (Ctrl+Shift+Delete)
Hoặc hard refresh (Ctrl+F5)
```

---

## 📞 Support

Nếu gặp vấn đề:
1. Kiểm tra error log: `storage/logs/`
2. Kiểm tra database connection
3. Kiểm tra có dữ liệu test không
4. Kiểm tra routes config

---

**Test Date:** 24/11/2025
**Module:** 5 - Báo Cáo & Thống Kê
**Status:** Ready for Testing ✅
