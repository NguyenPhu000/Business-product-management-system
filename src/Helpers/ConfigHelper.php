<?php

namespace Helpers;

use Modules\System\Models\SystemConfigModel;

/**
 * ConfigHelper - Helper để lấy và set config từ database
 */
class ConfigHelper
{
    private static ?SystemConfigModel $model = null;
    private static array $cache = [];

    /**
     * Khởi tạo model
     */
    private static function getModel(): SystemConfigModel
    {
        if (self::$model === null) {
            self::$model = new SystemConfigModel();
        }
        return self::$model;
    }

    /**
     * Lấy giá trị config theo key
     * 
     * @param string $key Key của config
     * @param mixed $default Giá trị mặc định nếu không tìm thấy
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        // Check cache trước
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        try {
            $value = self::getModel()->getValue($key);

            if ($value === null) {
                return $default;
            }

            // Lưu vào cache
            self::$cache[$key] = $value;
            return $value;
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Lấy giá trị config dạng integer
     */
    public static function getInt(string $key, int $default = 0): int
    {
        return (int) self::get($key, $default);
    }

    /**
     * Lấy giá trị config dạng boolean
     */
    public static function getBool(string $key, bool $default = false): bool
    {
        $value = self::get($key, $default);
        if (is_bool($value)) {
            return $value;
        }
        return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * Lấy giá trị config dạng float
     */
    public static function getFloat(string $key, float $default = 0.0): float
    {
        return (float) self::get($key, $default);
    }

    /**
     * Set giá trị config
     * 
     * @param string $key
     * @param mixed $value
     * @param int|null $userId
     * @return bool
     */
    public static function set(string $key, $value, ?int $userId = null): bool
    {
        try {
            $result = self::getModel()->setValue($key, $value, $userId);

            // Xóa cache
            if (isset(self::$cache[$key])) {
                unset(self::$cache[$key]);
            }

            return $result;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Xóa cache
     */
    public static function clearCache(?string $key = null): void
    {
        if ($key !== null) {
            unset(self::$cache[$key]);
        } else {
            self::$cache = [];
        }
    }

    /**
     * Lấy tất cả config
     */
    public static function all(): array
    {
        try {
            return self::getModel()->getAllConfigs();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Các helper method cho config thường dùng
     */
    public static function getCompanyName(): string
    {
        return self::get('company_name', 'Cửa hàng ABC');
    }

    public static function getCompanyAddress(): string
    {
        return self::get('company_address', '');
    }

    public static function getCompanyPhone(): string
    {
        return self::get('company_phone', '');
    }

    public static function getCompanyEmail(): string
    {
        return self::get('company_email', '');
    }

    public static function getCurrency(): string
    {
        return self::get('currency', 'VND');
    }

    public static function getDateFormat(): string
    {
        return self::get('date_format', 'd/m/Y');
    }

    public static function getRecordsPerPage(): int
    {
        return self::getInt('records_per_page', 20);
    }

    public static function getDefaultVatRate(): float
    {
        return self::getFloat('default_vat_rate', 10);
    }

    public static function getLowStockThreshold(): int
    {
        return self::getInt('low_stock_threshold', 10);
    }

    public static function allowNegativeStock(): bool
    {
        return self::getBool('allow_negative_stock', false);
    }
}
