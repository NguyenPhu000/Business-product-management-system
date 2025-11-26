# Phase 10: Inventory Over Time Page Redesign

**Status**: ✅ COMPLETED  
**Date**: 2025  
**Module**: Report / Inventory Over Time  
**Changes**: Complete UI redesign - from transaction-detail view to chart + daily-summary view

## Overview

The `inventory-over-time` page has been completely redesigned to emphasize visual trends and daily summary data instead of detailed transaction history.

### Before (Phase 9)
- Filter form (date range, product/SKU, transaction type)
- Summary cards (opening, import, export, closing)
- Table 1: "Tổng Nhập / Xuất theo ngày" (daily transactions by type)
- Table 2: "Chi Tiết Giao Dịch (Running Balance)" (transaction-level detail with running balance)

### After (Phase 10)
- Filter form (date range, product/SKU, transaction type) - KEPT
- Summary cards (opening, import, export, closing) - KEPT
- **NEW**: Line Chart (Chart.js) showing daily closing stock trend
- **NEW**: "Tồn Kho Hàng Ngày" table showing daily opening/import/export/closing balance
- **NEW**: "Truy Vấn Tồn Tại Ngày" picker to lookup stock at a specific past date
- **REMOVED**: Daily transaction summary table
- **REMOVED**: Detailed transaction table

## Implementation Details

### 1. Backend Changes

#### ReportController (`src/modules/report/controllers/ReportController.php`)

**Method: `inventoryOverTime()`**
- Updated data array passed to view
- **OLD keys removed**: `daily_summary`, `transactions`
- **NEW keys added**: `daily_balances` (array of daily records), `chart_data` (Chart.js format)
- Maintains: `opening_stock`, `total_import`, `total_export`, `closing_stock`, filters

**NEW Method: `stockAtDate()`**
- API endpoint for date picker (POST `/admin/reports/stock-at-date`)
- Accepts: `date` (Y-m-d), `product_sku`
- Returns: JSON response with stock at EOD for that date/product
- Error handling for missing parameters

#### ReportService (`src/modules/report/services/ReportService.php`)

**Modified Method: `getInventoryOverTimeReport()`**
- Old logic: Returned transaction details with running balance
- **New logic**: Computes daily balances from transaction aggregates
  - For each date in range, calculates: opening_balance, total_import, total_export, closing_balance
  - Calls new helper `formatChartData()` to produce Chart.js format
  - Returns: `daily_balances` array + `chart_data` object + summary totals

**NEW Helper Method: `formatChartData(dailyBalances)`**
- Transforms daily_balances into Chart.js-compatible structure
- Produces: `{labels: [...dates...], datasets: [{label, data: [...closing_balances...], ...}]}`
- Used for Line Chart rendering in view

**NEW Method: `getStockAtDate(date, productSku)`**
- Wrapper around model method; returns int (stock quantity at EOD for given date/product)

#### InventoryReportModel (`src/modules/report/models/InventoryReportModel.php`)

**NEW Method: `getStockAtDate(date, productSku): int`**
- Queries SUM of quantity_change for all transactions up to EOD of given date
- Filters by product (matches variant_sku OR product_sku)
- Returns: Integer closing stock at that date
- Logic: 
  ```sql
  SELECT SUM(quantity_change) FROM inventory_transactions 
  WHERE DATE(created_at) <= :date 
  AND (product variant matches :productSku)
  ```

### 2. Frontend Changes

#### View: `inventory_over_time.php` (Refactored)

**Section 1: Filter Form**
- Date range (start_date, end_date)
- Product/SKU search
- Transaction type dropdown (All, Import, Export, Adjust)
- Filter button (GET request)
- **No changes from original**

**Section 2: Summary Cards**
- Opening stock, Total import, Total export, Closing stock
- **No changes from original**

**Section 3: Line Chart (NEW)**
- Canvas element: `<canvas id="inventoryChart"></canvas>`
- Height: 400px, responsive
- Chart.js 3.9.1 library included
- Data source: `$data['chart_data']` (Chart.js format)
- Displays: Daily closing stock trend line
- Color: Blue line with light blue fill

**Section 4: Daily Closing Balance Table (NEW)**
- Replaces old "Tổng Nhập / Xuất theo ngày" table
- Data source: `$data['daily_balances']` array
- Columns: Ngày (date) | Tồn đầu ngày (opening) | Tổng nhập (import) | Tổng xuất (export) | Tồn cuối ngày (closing)
- One row per day with calculated balances
- Visual: import numbers green (+), export numbers red (-)

**Section 5: Stock at Date Picker (NEW)**
- Form: Date input + Product SKU input + Search button
- Submission: AJAX POST to `/admin/reports/stock-at-date`
- Response handling: Displays result in alert box (success/error)
- User can query stock at any past date for any product

**Section 6: JavaScript**
- Chart.js initialization (only if data exists)
- AJAX submit handler for stock-at-date form
- HTML escape helper to prevent XSS
- Error handling and user feedback

### 3. Route Changes

**File**: `config/routes.php`

**New Route Added**:
```php
$router->post('/admin/reports/stock-at-date', 
    'Modules\Report\Controllers\ReportController@stockAtDate', 
    [AuthMiddleware::class, RoleMiddleware::class]);
```

- Method: POST
- Returns: JSON response
- Requires: Authentication & role-based access

## Data Flow

### Display Page (GET /admin/reports/inventory-over-time)
```
ReportController::inventoryOverTime()
  ↓ Applies filters
ReportService::getInventoryOverTimeReport($startDate, $endDate, $productSku, $transactionType)
  ↓ Fetches transactions, computes daily balances
  ├─ Calls model::getTransactionHistory()
  ├─ Aggregates by date to compute: opening, import, export, closing
  ├─ Calls formatChartData() → Chart.js format
  └─ Returns: daily_balances, chart_data, opening_stock, total_import, total_export, closing_stock
  ↓
View::inventory_over_time.php
  ├─ Renders filters (unchanged)
  ├─ Renders summary cards (unchanged)
  ├─ Renders Chart.js line chart from chart_data
  ├─ Renders daily balance table from daily_balances
  └─ Renders date picker form (no data needed)
```

### Query Stock at Date (POST /admin/reports/stock-at-date)
```
JavaScript AJAX
  ↓ POST {date, product_sku}
ReportController::stockAtDate()
  ↓
ReportService::getStockAtDate(date, productSku)
  ↓
InventoryReportModel::getStockAtDate(date, productSku)
  ↓ Query SUM quantity_change WHERE DATE(created_at) <= date
Response: JSON {success, stock_at_date, message} or {error}
  ↓
View: Display result in alert/message box
```

## Database Schema Notes

**Table**: `inventory_transactions`
- Columns used: id, product_variant_id, created_at, quantity_change, type
- Related: product_variants (for SKU lookup), products (for product name)
- No schema changes required

**Removed from queries**:
- `quantity_after` (doesn't exist in schema; was mistakenly attempted in Phase 9)

## Testing Checklist

- [ ] Load `/admin/reports/inventory-over-time` without filters → chart shows overall trend
- [ ] Apply date range filter → chart updates, table updates, summary cards update
- [ ] Apply product/SKU filter → chart shows only that product, table reflects filter
- [ ] Apply transaction type filter (Import/Export) → affects daily totals displayed
- [ ] Verify line chart renders smoothly with multiple data points
- [ ] Verify daily balance table shows correct opening/closing balance per day
- [ ] Use date picker to query stock at past date → displays result with message
- [ ] Test with invalid/missing parameters → shows error message
- [ ] Verify responsive design on mobile (Bootstrap grid should work)
- [ ] Check Chart.js library loads from CDN (no browser console errors)

## Files Modified

| File | Type | Changes |
|------|------|---------|
| `src/modules/report/controllers/ReportController.php` | PHP | Updated `inventoryOverTime()` data keys; Added new `stockAtDate()` action |
| `src/modules/report/services/ReportService.php` | PHP | Refactored `getInventoryOverTimeReport()` logic; Added `formatChartData()` helper; Added `getStockAtDate()` wrapper |
| `src/modules/report/models/InventoryReportModel.php` | PHP | Added new `getStockAtDate()` method |
| `src/views/admin/reports/inventory_over_time.php` | PHP/HTML | Complete UI redesign: Line chart, daily balance table, date picker |
| `config/routes.php` | PHP | Added new route for `/admin/reports/stock-at-date` |

## Design Rationale

1. **Chart Focus**: Line chart provides immediate visual insight into stock trends (inventory manager concern)
2. **Daily Aggregation**: Summary by date (not by transaction) reduces noise, improves readability
3. **Date Picker**: Allows historical queries without needing to adjust date range repeatedly
4. **Consistent Design**: Uses same Bootstrap 5 + FontAwesome styling as rest of application
5. **No Data Loss**: All aggregate information still available; just presented differently

## Performance Considerations

- **Query Performance**: `getTransactionHistory()` already supports limits; charts with large date ranges may load slower
- **Frontend**: Chart.js rendering is fast even with 100+ data points
- **AJAX**: Stock-at-date queries are lightweight (single SUM query per request)
- **Caching**: No caching implemented; real-time data (acceptable for current data volume)

## Future Enhancements

1. Export daily balance table to CSV/Excel
2. Add comparison between two date ranges
3. Drill down from chart (click date → show daily transactions)
4. Add inventory forecast trend
5. Multi-product comparison on single chart
6. Date range presets (Today, This Week, This Month, etc.)

## Rollback Instructions

If needed to revert to previous design:
1. Restore old `getInventoryOverTimeReport()` method in ReportService
2. Restore old `inventoryOverTime()` data keys in ReportController
3. Restore old view from Git history
4. Remove `stockAtDate()` action and route

---
**Phase 10 Status**: All syntax checks passed ✅ | Ready for end-to-end testing
