<?php

namespace Models;

use core\Database;
use PDO;

class PasswordResetRequestModel extends BaseModel
{
    protected string $table = 'password_reset_requests';

    /**
     * Tạo yêu cầu reset password mới
     */
    public function createRequest(int $userId, string $email): ?int
    {
        $sql = "INSERT INTO {$this->table} (user_id, email, status, requested_at) 
                VALUES (:user_id, :email, 'pending', NOW())";

        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'email' => $email
        ]);

        return $pdo->lastInsertId();
    }

    /**
     * Lấy tất cả yêu cầu đang chờ
     */
    public function getPendingRequests(): array
    {
        $sql = "SELECT r.*, u.username, u.email 
                FROM {$this->table} r
                INNER JOIN users u ON r.user_id = u.id
                WHERE r.status = 'pending'
                ORDER BY r.requested_at DESC";

        $pdo = self::getConnection();
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy tất cả yêu cầu (có phân trang) - hiển thị TẤT CẢ để audit
     */
    public function getAllRequests(int $page = 1, int $perPage = 12): array
    {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT r.*, 
                u.username, u.email,
                approver.username as approver_username,
                approver.full_name as approver_full_name
                FROM {$this->table} r
                INNER JOIN users u ON r.user_id = u.id
                LEFT JOIN users approver ON r.approved_by = approver.id
                ORDER BY r.requested_at DESC
                LIMIT :limit OFFSET :offset";

        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * Đếm tổng số yêu cầu - tất cả requests
     */
    public function countRequests(): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $pdo = self::getConnection();
        $stmt = $pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Đếm số yêu cầu đang chờ
     */
    public function countPendingRequests(): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = 'pending'";
        $pdo = self::getConnection();
        $stmt = $pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Lấy yêu cầu theo ID
     */
    public function getRequestById(int $id): ?array
    {
        $sql = "SELECT r.*, u.username, u.email 
                FROM {$this->table} r
                INNER JOIN users u ON r.user_id = u.id
                WHERE r.id = :id";

        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Phê duyệt yêu cầu
     */
    public function approveRequest(int $id, int $approvedBy): bool
    {
        $sql = "UPDATE {$this->table} 
                SET status = 'approved', 
                    approved_by = :approved_by, 
                    approved_at = NOW() 
                WHERE id = :id AND status = 'pending'";

        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'approved_by' => $approvedBy
        ]);
    }

    /**
     * Từ chối yêu cầu
     */
    public function rejectRequest(int $id, int $rejectedBy): bool
    {
        $sql = "UPDATE {$this->table} 
                SET status = 'rejected', 
                    approved_by = :rejected_by, 
                    approved_at = NOW() 
                WHERE id = :id AND status = 'pending'";

        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'rejected_by' => $rejectedBy
        ]);
    }

    /**
     * Kiểm tra xem user có yêu cầu pending không
     */
    public function hasPendingRequest(int $userId): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE user_id = :user_id AND status = 'pending'";

        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result['total'] ?? 0) > 0;
    }

    /**
     * Lấy yêu cầu approved của user (chưa đổi mật khẩu)
     * QUAN TRỌNG: Chỉ lấy request chưa hoàn tất (new_password IS NULL hoặc != 'changed')
     * VÀ chưa hết hạn (approved trong vòng 10 GIÂY)
     */
    public function getApprovedRequestByUserId(int $userId): ?array
    {
        // Timeout: 10 GIÂY kể từ khi approved
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                AND status = 'approved' 
                AND (new_password IS NULL OR new_password != 'changed')
                AND approved_at >= DATE_SUB(NOW(), INTERVAL 10 SECOND)
                ORDER BY approved_at DESC 
                LIMIT 1";

        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Lấy yêu cầu approved theo ID (vẫn còn hiệu lực: chưa đổi mật khẩu và trong 10 giây)
     */
    public function getApprovedRequestById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE id = :id 
                AND status = 'approved'
                AND (new_password IS NULL OR new_password != 'changed')
                AND approved_at >= DATE_SUB(NOW(), INTERVAL 10 SECOND)
                LIMIT 1";

        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Xóa các request approved đã hết hạn (quá 10 GIÂY chưa đổi MK)
     */
    public function deleteExpiredApprovedRequests(int $userId): bool
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE user_id = :user_id 
                AND status = 'approved' 
                AND (new_password IS NULL OR new_password != 'changed')
                AND approved_at < DATE_SUB(NOW(), INTERVAL 10 SECOND)";

        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(['user_id' => $userId]);
    }

    /**
     * Đánh dấu là đã đổi mật khẩu
     */
    public function markPasswordChanged(int $id): bool
    {
        $sql = "UPDATE {$this->table} 
                SET new_password = 'changed' 
                WHERE id = :id";

        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Lấy yêu cầu bị rejected của user
     */
    public function getRejectedRequestByUserId(int $userId): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                AND status = 'rejected' 
                ORDER BY approved_at DESC 
                LIMIT 1";

        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Xóa chỉ yêu cầu rejected (GIỮ LẠI approved/changed để audit)
     */
    public function deleteRejectedRequests(int $userId): bool
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE user_id = :user_id 
                AND status = 'rejected'";

        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(['user_id' => $userId]);
    }

    /**
     * Xóa 1 request cụ thể (dùng cho admin)
     */
    public function deleteRequest(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";

        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Lấy tất cả các yêu cầu đã bị cancelled (người dùng hủy)
     */
    public function getCancelledRequests(): array
    {
        $sql = "SELECT r.*, 
                u.username, 
                u.full_name,
                approver.username as approver_username,
                approver.full_name as approver_name
                FROM {$this->table} r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN users approver ON r.approved_by = approver.id
                WHERE r.status = 'cancelled' 
                ORDER BY r.requested_at DESC";

        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Hủy yêu cầu pending của user (người dùng tự hủy)
     */
    public function cancelPendingRequest(int $userId): bool
    {
        $sql = "UPDATE {$this->table} 
                SET status = 'cancelled'
                WHERE user_id = :user_id 
                AND status = 'pending'";

        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        // Kiểm tra số row bị affected
        return $stmt->rowCount() > 0;
    }

    /**
     * Xóa các request pending của user (dùng cho thao tác silent cancel)
     */
    public function deletePendingRequestByUser(int $userId): bool
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE user_id = :user_id 
                AND status = 'pending'";

        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->rowCount() > 0;
    }
}
