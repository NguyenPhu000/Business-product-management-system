<?php

namespace Core;

/**
 * View - Xử lý render template
 */
class View
{
    private static string $viewPath = __DIR__ . '/../Views/';
    private static ?string $layout = null;
    private static array $sections = [];
    private static ?string $currentSection = null;
    
    /**
     * Render view
     */
    public static function render(string $view, array $data = [], ?string $layout = 'admin/layout/main'): void
    {
        self::$layout = $layout;
        
        // Extract data to variables
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include view file
        $viewFile = self::$viewPath . $view . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new \Exception("View không tồn tại: {$view}");
        }
        
        $content = ob_get_clean();
        
        // Render with layout
        if (self::$layout) {
            self::renderLayout($content, $data);
        } else {
            echo $content;
        }
    }
    
    /**
     * Render layout
     */
    private static function renderLayout(string $content, array $data = []): void
    {
        extract($data);
        
        $layoutFile = self::$viewPath . self::$layout . '.php';
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }
    
    /**
     * Bắt đầu section
     */
    public static function section(string $name): void
    {
        self::$currentSection = $name;
        ob_start();
    }
    
    /**
     * Kết thúc section
     */
    public static function endSection(): void
    {
        if (self::$currentSection) {
            self::$sections[self::$currentSection] = ob_get_clean();
            self::$currentSection = null;
        }
    }
    
    /**
     * Hiển thị section
     */
    public static function yield(string $name, string $default = ''): void
    {
        echo self::$sections[$name] ?? $default;
    }
    
    /**
     * Include partial view
     */
    public static function include(string $partial, array $data = []): void
    {
        extract($data);
        $partialFile = self::$viewPath . $partial . '.php';
        if (file_exists($partialFile)) {
            include $partialFile;
        }
    }
    
    /**
     * Escape HTML
     */
    public static function e($value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}
