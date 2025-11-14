<div class="page-header">
    <h2 class="page-title">Dashboard</h2>
</div>

<div class="row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <i class="fas fa-users" style="font-size: 36px; margin-bottom: 10px;"></i>
        <h3><?= $stats['totalUsers'] ?? 0 ?></h3>
        <p>Tổng số người dùng</p>
    </div>

    <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
        <i class="fas fa-user-check" style="font-size: 36px; margin-bottom: 10px;"></i>
        <h3><?= $stats['activeUsers'] ?? 0 ?></h3>
        <p>Người dùng hoạt động</p>
    </div>

    <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
        <i class="fas fa-user-tag" style="font-size: 36px; margin-bottom: 10px;"></i>
        <h3><?= $stats['totalRoles'] ?? 0 ?></h3>
        <p>Vai trò</p>
    </div>

    <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
        <i class="fas fa-history" style="font-size: 36px; margin-bottom: 10px;"></i>
        <h3><?= $stats['totalLogs'] ?? 0 ?></h3>
        <p>Log hoạt động</p>
    </div>
</div>

<div class="row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <!-- Recent Logs -->
    <div class="card">
        <div class="card-header">
            <h3>Log hoạt động gần đây</h3>
            <a href="/admin/logs" class="btn btn-primary btn-sm">Xem tất cả</a>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Người dùng</th>
                        <th>Hành động</th>
                        <th>Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentLogs)): ?>
                        <?php foreach ($recentLogs as $log): ?>
                            <tr>
                                <td><?= \Core\View::e($log['username'] ?? 'Unknown') ?></td>
                                <td><?= \Core\View::e($log['action']) ?></td>
                                <td><?= \Helpers\FormatHelper::datetime($log['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center;">Chưa có log nào</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="card">
        <div class="card-header">
            <h3>Người dùng mới nhất</h3>
            <a href="/admin/users" class="btn btn-primary btn-sm">Xem tất cả</a>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentUsers)): ?>
                        <?php foreach ($recentUsers as $user): ?>
                            <tr>
                                <td><?= \Core\View::e($user['full_name']) ?></td>
                                <td><?= \Core\View::e($user['email']) ?></td>
                                <td><?= \Core\View::e($user['name'] ?? 'N/A') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center;">Chưa có người dùng nào</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>