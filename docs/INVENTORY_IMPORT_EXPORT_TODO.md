# TODO: Tính năng Xuất - Nhập hàng (Inventory import/export)

Phiên bản: 2025-11-20

Mục tiêu: Thay đổi UI/UX trang `inventory` để chuyển nút + thành Tạo Phiếu Nhập, thêm nút - để Tạo Phiếu Xuất (dùng `suppliers` làm `customers`), loại bỏ nút 'Điều chỉnh' hiển thị, và đảm bảo backend cập nhật `inventory` + `inventory_transactions` + `user_logs`.

## Tổng quan công việc (tóm tắt từ todo list manager)

1. Phân tích & contract
2. UI: Xóa nút Điều chỉnh
3. UI: Thay nút + mở Phiếu nhập
4. UI: Thêm nút - mở Phiếu xuất
5. Backend: Inventory transaction & logs
6. Migrations & DB docs
7. Tests & QA
8. Docs & TODO finalize
9. Gợi ý hoàn thiện (optional)

---

## Chi tiết công việc (mỗi bước)

### 1) Phân tích & contract
- Inputs: hành động click (+ / -) trên trang `views/admin/inventory`.
- Outputs: tạo `purchase_orders` / `sales_orders`, cập nhật `inventory`, thêm `inventory_transactions`, ghi `user_logs`.
- Acceptance criteria: có tài liệu thiết kế nhỏ, danh sách file cần sửa, SQL migration (nếu cần), và test cases.
- Assumptions: Dùng `suppliers` làm `customers` cho phiếu xuất theo yêu cầu bài tập.

### 2) UI: Xóa nút "Điều chỉnh"
- Files cần check: `views/admin/inventory/index.php`, `views/admin/partials/*`.
- Yêu cầu: không hiển thị cột hoặc button "Điều chỉnh". Không remove code ghi log/back-end (chỉ ẩn UI).
- Acceptance: giao diện không còn nút, responsive ok, không ảnh hưởng permissions.

### 3) UI: Thay nút + thành mở Phiếu nhập
- Behavior: click + -> mở modal hoặc điều hướng tới `purchase/create`.
- Form phiếu nhập fields: product_variant (id), quantity, import_price, supplier_id (optional), purchase_date, note.
- On submit: tạo `purchase_orders` + `purchase_details`, cập nhật `inventory` + `inventory_transactions(type='import')`, ghi `user_logs`.
- Files front-end: `views/admin/inventory/index.php`, `views/admin/purchase/create.php`.
- Files back-end: `modules/purchase/controllers/PurchaseController.php`, `modules/purchase/models/*`.

### 4) UI: Thêm nút - mở Phiếu xuất
- Behavior: click - -> modal/route `sales/create`.
- Use `suppliers` as `customers`.
- Form phiếu xuất fields: supplier_id (as customer), product_variant, quantity, sale_price, sale_date, note.
- On submit: validate stock, create `sales_orders` + `sales_details`, decrement `inventory`, insert `inventory_transactions(type='export')`, log action.
- Files: `modules/sales/*`, `views/admin/sales/create.php`.

### 5) Backend: Inventory transactions & logs
- Implement service methods to apply imports/exports atomically.
- Ensure DB transaction wrapping (atomicity).
- Use `inventory_transactions` table (schema exists in `docs/Database.md`).
- Log user actions in `user_logs`.
- Edge cases: concurrent updates, insufficient stock, missing variant.

### 6) Migrations & DB docs
- Verify existence of `purchase_orders`, `purchase_details`, `sales_orders`, `sales_details` in DB schema; if missing, add migration SQL in `scripts/` and update `docs/Database.md`.

### 7) Tests & QA
- Unit tests for `InventoryService`.
- Feature tests for purchase and sale flows.
- Manual test cases list included.

### 8) Docs & finalize
- Add short README `docs/INVENTORY_IMPORT_EXPORT_README.md` với cách dùng tính năng mới và migration steps.

### 9) Gợi ý hoàn thiện (optional)
- Partial receipts, drafts, supplier auto-create, audit UI, background jobs, typeahead search, API endpoints.

---

## Checklist nhanh (acceptance)
- [ ] Xóa UI "Điều chỉnh" hoàn tất
- [ ] Nút + mở tạo Phiếu nhập + DB update + inventory_transactions + logs
- [ ] Nút - mở tạo Phiếu xuất + DB update + inventory_transactions + logs
- [ ] Không cho xuất vượt tồn (mặc định)
- [ ] Tất cả thao tác DB chạy trong transaction
- [ ] Viết tests cơ bản (unit + feature)
- [ ] Cập nhật `docs/Database.md` nếu migration được thêm
- [ ] Tạo file docs hướng dẫn

---

## Các file gợi ý sẽ chỉnh sửa/ thêm
- `views/admin/inventory/index.php` (ẩn 'Điều chỉnh', thay +, thêm -)
- `views/admin/purchase/create.php`, `views/admin/sales/create.php`
- `modules/purchase/controllers/PurchaseController.php`, `modules/purchase/models/*`
- `modules/sales/controllers/SalesController.php`, `modules/sales/models/*`
- `src/core/InventoryService.php` (new/updated service)
- `config/routes.php` (thêm route POST/GET)
- `scripts/migrations/` (nếu cần SQL)
- `tests/Unit/InventoryServiceTest.php`, `tests/Feature/PurchaseFlowTest.php`, `tests/Feature/SalesFlowTest.php`.

---

## Hẹn bước tiếp theo
Nếu bạn duyệt todo list này, tôi sẽ:
1) Triển khai UI nhỏ trước (modal + route) và backend minimal để tạo `purchase_orders` (phiếu nhập) — hoặc
2) Nếu bạn muốn, tôi có thể code cả hai flows (nhập + xuất) và tests.

Bạn muốn tôi bắt đầu với bước nào? (gợi ý: bắt đầu với `purchase` flow vì ít rủi ro.)
