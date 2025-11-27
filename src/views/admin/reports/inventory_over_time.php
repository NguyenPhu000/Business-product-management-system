<?php
/**
 * Tồn Theo Thời Gian - Biểu đồ đường (Line Chart) + Bảng tồn hàng hàng ngày + Truy vấn tồn tại ngày cụ thể
 * 
 * Dữ liệu:
 * - daily_balances: Mảng các ngày với {date, opening_balance, total_import, total_export, closing_balance}
 * - chart_data: {labels: [...], datasets: [...]} cho Chart.js
 * - opening_stock, total_import, total_export, closing_stock: Tóm tắt toàn kỳ
 * - product_sku, transaction_type, start_date, end_date: Tham số lọc
 */
$start_date = $data['start_date'] ?? null;
$end_date = $data['end_date'] ?? null;
$daily_balances = $data['daily_balances'] ?? [];
$chart_data = $data['chart_data'] ?? null;
$product_sku = $data['product_sku'] ?? '';
$transaction_type = $data['transaction_type'] ?? 'all';
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">
                <i class="fas fa-chart-line"></i> Báo Cáo Lịch Sử Tồn Kho
            </h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="/admin/reports" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Quay Lại Dashboard
            </a>
        </div>
    </div>

    <!-- PHẦN 1: BỘNG LỌC -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label for="startDate" class="form-label">Từ ngày</label>
                            <input type="date" id="startDate" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="endDate" class="form-label">Đến ngày</label>
                            <input type="date" id="endDate" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="productSku" class="form-label">Sản phẩm / SKU</label>
                            <input type="text" id="productSku" name="product_sku" class="form-control" placeholder="Nhập tên hoặc SKU" value="<?= htmlspecialchars($product_sku) ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="transactionType" class="form-label">Loại giao dịch</label>
                            <select id="transactionType" name="transaction_type" class="form-select">
                                <option value="all" <?= ($transaction_type === 'all') ? 'selected' : '' ?>>Tất cả</option>
                                <option value="import" <?= ($transaction_type === 'import') ? 'selected' : '' ?>>Nhập kho</option>
                                <option value="export" <?= ($transaction_type === 'export') ? 'selected' : '' ?>>Xuất kho</option>
                                <option value="adjust" <?= ($transaction_type === 'adjust') ? 'selected' : '' ?>>Điều chỉnh</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Lọc
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- PHẦN 2: SUMMARY CARDS -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-bg-light shadow-sm">
                <div class="card-body text-center">
                    <div class="fw-bold text-secondary">Tồn đầu kỳ</div>
                    <div class="fs-4 text-primary" id="openingStock">
                        <?= isset($data['opening_stock']) ? number_format($data['opening_stock']) : '--' ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-light shadow-sm">
                <div class="card-body text-center">
                    <div class="fw-bold text-secondary">Tổng nhập</div>
                    <div class="fs-4 text-success" id="totalImport">
                        <?= isset($data['total_import']) ? number_format($data['total_import']) : '--' ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-light shadow-sm">
                <div class="card-body text-center">
                    <div class="fw-bold text-secondary">Tổng xuất</div>
                    <div class="fs-4 text-danger" id="totalExport">
                        <?= isset($data['total_export']) ? number_format($data['total_export']) : '--' ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-light shadow-sm">
                <div class="card-body text-center">
                    <div class="fw-bold text-secondary">Tồn cuối kỳ</div>
                    <div class="fs-4 text-primary" id="closingStock">
                        <?= isset($data['closing_stock']) ? number_format($data['closing_stock']) : '--' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PHẦN 3: LINE CHART - Đường xu hướng tồn kho theo ngày -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-line"></i> Lịch Sử Số Lượng Tồn Kho</h5>
                </div>
                <div class="card-body" style="position: relative; height: 400px;">
                    <?php if ($chart_data && !empty($chart_data['labels'])): ?>
                        <canvas id="inventoryChart"></canvas>
                    <?php else: ?>
                        <div class="alert alert-info mt-3">Không có dữ liệu để hiển thị biểu đồ.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- PHẦN 4: BẢNG TỒNG HẾP HÀNG NGÀY (Daily Closing Balance) -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-table"></i> Tồn Kho Hàng Ngày</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($data['product_daily_balances'])): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 10%; text-align: left; padding-left: 24px;" class="ps-4">Ngày</th>
                                        <th style="width: 38%; text-align: left;">Tên Sản Phẩm</th>
                                        <th style="width: 15%; text-align: left;">SKU</th>
                                        <th style="width: 8%; text-align: right;">Nhập</th>
                                        <th style="width: 8%; text-align: right;">Xuất</th>
                                        <th style="width: 12%; text-align: right; padding-right: 24px;">Tồn Cuối</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['product_daily_balances'] as $product): ?>
                                        <tr style="height: 50px; border-bottom: 1px solid #f0f0f0;">
                                            <td style="text-align: left; vertical-align: middle; padding-left: 24px;">
                                                <strong><?= date('d/m/Y', strtotime($product['date'])) ?></strong>
                                            </td>
                                            <td style="vertical-align: middle; text-align: left;">
                                                <span title="<?= htmlspecialchars($product['product_name']) ?>" style="font-weight: 500; color: #333; line-height: 1.4; display: block;">
                                                    <?= htmlspecialchars($product['product_name']) ?>
                                                </span>
                                            </td>
                                            <td style="vertical-align: middle; text-align: left;">
                                                <code style="font-size: 11px; color: #666; background: #f8f9fa; padding: 2px 4px; border-radius: 3px; display: inline-block; word-break: break-all; line-height: 1.2;">
                                                    <?= htmlspecialchars($product['variant_sku'] ?? $product['product_sku'] ?? '-') ?>
                                                </code>
                                            </td>
                                            <td style="text-align: right; vertical-align: middle; color: #198754; font-weight: 500;">
                                                <?= $product['total_import'] > 0 ? '+'.number_format($product['total_import']) : '<span style="color:#ccc">-</span>' ?>
                                            </td>
                                            <td style="text-align: right; vertical-align: middle; color: #dc3545; font-weight: 500;">
                                                <?= $product['total_export'] > 0 ? '-'.number_format($product['total_export']) : '<span style="color:#ccc">-</span>' ?>
                                            </td>
                                            <td style="text-align: right; vertical-align: middle; padding-right: 24px;">
                                                <strong style="color: #2c3e50; font-size: 1.1em;"><?= number_format($product['closing_balance']) ?></strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info m-3">Không có dữ liệu tồn kho chi tiết trong khoảng thời gian này.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- PHẦN 5: TÌM TỒNG TẠI MỘT NGÀY CỤ THỂ (Stock at Date Picker) -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-search"></i> Truy Vấn Tồn Kho</h5>
                </div>
                <div class="card-body">
                    <form id="stockAtDateForm" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="queryDate" class="form-label">Chọn Ngày <span class="text-muted">(tùy chọn)</span></label>
                            <input type="date" id="queryDate" name="date" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label for="querySku" class="form-label">Sản phẩm / SKU <span class="text-muted">(tùy chọn)</span></label>
                            <input type="text" id="querySku" name="product_sku" class="form-control" placeholder="Tên sản phẩm hoặc SKU">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Tìm
                            </button>
                        </div>
                    </form>
                    <div id="stockAtDateResult" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Thêm Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ========== INITIALIZE LINE CHART ==========
    <?php if ($chart_data && !empty($chart_data['labels'])): ?>
    const ctx = document.getElementById('inventoryChart').getContext('2d');
    const chartData = <?= json_encode($chart_data) ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                title: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#0d6efd',
                    borderWidth: 1,
                    displayColors: true,
                    callbacks: {
                        title: function(context) {
                            if (context.length > 0) {
                                const dateStr = context[0].label;
                                const dateParts = dateStr.split('-');
                                if (dateParts.length === 3) {
                                    const date = new Date(dateStr);
                                    return 'Ngày ' + date.toLocaleDateString('vi-VN', {day: '2-digit', month: '2-digit', year: 'numeric'});
                                }
                            }
                            return context[0].label;
                        },
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += context.parsed.y.toLocaleString('vi-VN') + ' cái';
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Số lượng (cái)'
                    },
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('vi-VN');
                        }
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Ngày'
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 0,
                        callback: function(value, index, values) {
                            const dateStr = chartData.labels[index];
                            if (!dateStr) return '';
                            
                            const date = new Date(dateStr);
                            const day = date.getDate();
                            const month = date.getMonth() + 1;
                            const year = date.getFullYear();
                            
                            // Hiển thị mốc: đầu tháng (ngày 1), hoặc cứ 7 ngày một lần
                            if (day === 1 || day % 7 === 0 || index === 0 || index === values.length - 1) {
                                return day + '/' + month;
                            }
                            return '';
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>

    // ========== STOCK QUERY - AJAX SUBMIT ==========
    // Hỗ trợ 3 loại truy vấn:
    // 1. Chỉ ngày: lấy tồn kho tất cả sản phẩm vào ngày đó
    // 2. Chỉ sản phẩm: lấy lịch sử tồn kho sản phẩm đó (tất cả ngày)
    // 3. Cả ngày + sản phẩm: lấy tồn kho sản phẩm đó vào ngày đó
    document.getElementById('stockAtDateForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const date = document.getElementById('queryDate').value;
        const productSku = document.getElementById('querySku').value;
        
        // Phải nhập ít nhất một trong hai
        if (!date && !productSku) {
            alert('Vui lòng nhập ngày hoặc tên sản phẩm/SKU');
            return;
        }

        // Xác định loại truy vấn và gọi endpoint phù hợp
        let endpoint = '/admin/reports/stock-at-date';
        let body = '';
        
        if (date && productSku) {
            // Truy vấn 3: Ngày + Sản phẩm → tồn kho sản phẩm đó tại ngày đó
            body = 'date=' + encodeURIComponent(date) + '&product_sku=' + encodeURIComponent(productSku);
        } else if (date && !productSku) {
            // Truy vấn 1: Chỉ ngày → tồn kho tất cả sản phẩm tại ngày đó
            body = 'date=' + encodeURIComponent(date);
        } else if (!date && productSku) {
            // Truy vấn 2: Chỉ sản phẩm → lịch sử tồn kho sản phẩm đó
            endpoint = '/admin/reports/product-stock-history';
            body = 'product_sku=' + encodeURIComponent(productSku);
        }

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: body
        })
        .then(response => response.json())
        .then(data => {
            const resultDiv = document.getElementById('stockAtDateResult');
            if (data.error) {
                resultDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' + htmlEscape(data.error) + '</div>';
            } else if (data.success) {
                // Trường hợp 1: Truy vấn theo ngày với chi tiết sản phẩm
                if (data.product_details && Array.isArray(data.product_details)) {
                    let tableHtml = '<div class="alert alert-success mb-3"><i class="fas fa-check-circle"></i> ' + htmlEscape(data.message) + '</div>';
                    tableHtml += '<p class="text-muted"><strong>Chi tiết các sản phẩm tồn tại thời điểm đó:</strong></p>';
                    tableHtml += '<div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr>';
                    tableHtml += '<th>Sản phẩm</th>';
                    tableHtml += '<th style="width: 120px;">SKU</th>';
                    tableHtml += '<th style="width: 120px;">Tồn kho lúc đó</th>';
                    tableHtml += '</tr></thead><tbody>';
                    
                    data.product_details.forEach(product => {
                        tableHtml += '<tr>';
                        tableHtml += '<td><strong>' + htmlEscape(product.product_name) + '</strong></td>';
                        tableHtml += '<td><small>' + htmlEscape(product.variant_sku || product.product_sku) + '</small></td>';
                        tableHtml += '<td><strong>' + product.stock_at_date.toLocaleString('vi-VN') + '</strong></td>';
                        tableHtml += '</tr>';
                    });
                    
                    tableHtml += '</tbody></table></div>';
                    resultDiv.innerHTML = tableHtml;
                }
                // Trường hợp 2: Truy vấn theo ngày + sản phẩm (chỉ một sản phẩm)
                else if (data.stock_at_date !== undefined && !data.product_details) {
                    resultDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' + htmlEscape(data.message) + '</div>';
                }
                // Trường hợp 3: Truy vấn lịch sử sản phẩm (product-stock-history) - hiển thị bảng
                else if (data.history && Array.isArray(data.history)) {
                    let tableHtml = '<div class="alert alert-success mb-3"><i class="fas fa-check-circle"></i> ' + htmlEscape(data.message) + '</div>';
                    tableHtml += '<div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr>';
                    tableHtml += '<th style="width: 100px;">Ngày</th>';
                    tableHtml += '<th>Tên Sản Phẩm</th>';
                    tableHtml += '<th style="width: 100px;">Tồn đầu ngày</th>';
                    tableHtml += '<th style="width: 100px;">Tổng nhập</th>';
                    tableHtml += '<th style="width: 100px;">Tổng xuất</th>';
                    tableHtml += '<th style="width: 100px;">Tồn cuối ngày</th>';
                    tableHtml += '</tr></thead><tbody>';
                    
                    data.history.forEach(row => {
                        const date = new Date(row.date);
                        const dateStr = date.toLocaleDateString('vi-VN', {day: '2-digit', month: '2-digit', year: 'numeric'});
                        tableHtml += '<tr>';
                        tableHtml += '<td><strong>' + htmlEscape(dateStr) + '</strong></td>';
                        tableHtml += '<td><strong>' + htmlEscape(data.product_name || '-') + '</strong></td>';
                        tableHtml += '<td>' + row.opening_balance.toLocaleString('vi-VN') + '</td>';
                        tableHtml += '<td class="text-success"><strong>+' + row.total_import.toLocaleString('vi-VN') + '</strong></td>';
                        tableHtml += '<td class="text-danger"><strong>-' + row.total_export.toLocaleString('vi-VN') + '</strong></td>';
                        tableHtml += '<td><strong>' + row.closing_balance.toLocaleString('vi-VN') + '</strong></td>';
                        tableHtml += '</tr>';
                    });
                    
                    tableHtml += '</tbody></table></div>';
                    resultDiv.innerHTML = tableHtml;
                }
            }
        })
        .catch(error => {
            document.getElementById('stockAtDateResult').innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Lỗi: ' + htmlEscape(error.message) + '</div>';
        });
    });

    // Helper function to escape HTML
    function htmlEscape(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
});
</script>

<style>
/* Fix scrollbar causing layout shift */
.table-responsive {
    scrollbar-gutter: stable;
}
</style>

<?php
// End file
?>
