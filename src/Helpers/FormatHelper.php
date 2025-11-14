<?php

namespace Helpers;

/**
 * FormatHelper - Định dạng dữ liệu
 */
class FormatHelper
{
    /**
     * Định dạng ngày tháng
     */
    public static function date(?string $date, string $format = 'd/m/Y'): string
    {
        if (!$date) {
            return '';
        }
        
        try {
            $timestamp = strtotime($date);
            return date($format, $timestamp);
        } catch (\Exception $e) {
            return $date;
        }
    }
    
    /**
     * Định dạng ngày giờ
     */
    public static function datetime(?string $datetime, string $format = 'd/m/Y H:i:s'): string
    {
        return self::date($datetime, $format);
    }
    
    /**
     * Định dạng tiền tệ VND
     */
    public static function money($amount): string
    {
        if (!is_numeric($amount)) {
            return '0 ₫';
        }
        
        return number_format($amount, 0, ',', '.') . ' ₫';
    }
    
    /**
     * Định dạng số
     */
    public static function number($number, int $decimals = 0): string
    {
        if (!is_numeric($number)) {
            return '0';
        }
        
        return number_format($number, $decimals, ',', '.');
    }
    
    /**
     * Tạo slug từ tiếng Việt
     */
    public static function slug(string $string): string
    {
        // Chuyển về chữ thường
        $string = mb_strtolower($string, 'UTF-8');
        
        // Mảng chuyển đổi ký tự có dấu
        $replacements = [
            'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ' => 'a',
            'đ' => 'd',
            'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ' => 'e',
            'í|ì|ỉ|ĩ|ị' => 'i',
            'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ' => 'o',
            'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự' => 'u',
            'ý|ỳ|ỷ|ỹ|ỵ' => 'y',
        ];
        
        foreach ($replacements as $pattern => $replacement) {
            $string = preg_replace('/[' . $pattern . ']/u', $replacement, $string);
        }
        
        // Thay thế ký tự đặc biệt bằng dấu gạch ngang
        $string = preg_replace('/[^a-z0-9\-]/', '-', $string);
        $string = preg_replace('/-+/', '-', $string);
        $string = trim($string, '-');
        
        return $string;
    }
    
    /**
     * Cắt ngắn chuỗi
     */
    public static function truncate(string $string, int $length = 100, string $suffix = '...'): string
    {
        if (mb_strlen($string) <= $length) {
            return $string;
        }
        
        return mb_substr($string, 0, $length) . $suffix;
    }
    
    /**
     * Escape HTML
     */
    public static function escape(?string $string): string
    {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Định dạng file size
     */
    public static function fileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Tạo badge status
     */
    public static function statusBadge(int $status): string
    {
        if ($status == STATUS_ACTIVE) {
            return '<span class="badge bg-success">Hoạt động</span>';
        } else {
            return '<span class="badge bg-secondary">Không hoạt động</span>';
        }
    }
}
