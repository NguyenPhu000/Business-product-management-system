<?php

namespace Core;

/**
 * Bootstrap - Khởi tạo ứng dụng
 */
class Bootstrap
{
    private Router $router;
    
    public function __construct()
    {
        $this->router = new Router();
        $this->loadEnv();
        $this->loadConstants();
        $this->setTimezone();
        $this->startSession();
        $this->registerRoutes();
    }
    
    /**
     * Load system constants
     */
    private function loadConstants(): void
    {
        require_once __DIR__ . '/../../config/constants.php';
    }
    
    /**
     * Load .env file
     */
    private function loadEnv(): void
    {
        $envFile = __DIR__ . '/../../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                if (!array_key_exists($name, $_ENV)) {
                    $_ENV[$name] = $value;
                    putenv("{$name}={$value}");
                }
            }
        }
    }
    
    /**
     * Set timezone
     */
    private function setTimezone(): void
    {
        $config = require __DIR__ . '/../../config/app.php';
        date_default_timezone_set($config['timezone']);
    }
    
    /**
     * Start session
     */
    private function startSession(): void
    {
        \Helpers\AuthHelper::startSession();
    }
    
    /**
     * Đăng ký routes
     */
    private function registerRoutes(): void
    {
        $router = $this->router;
        require __DIR__ . '/../../config/routes.php';
    }
    
    /**
     * Chạy ứng dụng
     */
    public function run(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Xử lý POST _method override
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }
        
        try {
            $this->router->dispatch($method, $uri);
        } catch (\Exception $e) {
            if ($_ENV['APP_DEBUG'] ?? false) {
                echo "<h1>Error</h1>";
                echo "<p>" . $e->getMessage() . "</p>";
                echo "<pre>" . $e->getTraceAsString() . "</pre>";
            } else {
                http_response_code(500);
                View::render('errors/500', [], null);
            }
        }
    }
    
    /**
     * Lấy router instance
     */
    public function getRouter(): Router
    {
        return $this->router;
    }
}
