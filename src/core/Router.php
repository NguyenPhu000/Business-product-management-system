<?php

namespace Core;

/**
 * Router - Xử lý định tuyến URL
 */
class Router
{
    private array $routes = [];
    private array $middlewares = [];

    /**
     * Đăng ký route GET
     */
    public function get(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    /**
     * Đăng ký route POST
     */
    public function post(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    /**
     * Thêm route vào danh sách
     */
    private function addRoute(string $method, string $path, $handler, array $middlewares = []): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    /**
     * Xử lý request
     */
    public function dispatch(string $method, string $uri): void
    {
        // Loại bỏ query string
        $uri = parse_url($uri, PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            // Chuyển path pattern thành regex
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                // Lọc chỉ lấy named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Chạy middlewares
                foreach ($route['middlewares'] as $middleware) {
                    $middlewareInstance = new $middleware();
                    if (!$middlewareInstance->handle()) {
                        return;
                    }
                }

                // Gọi controller
                $this->callHandler($route['handler'], $params);
                return;
            }
        }

        // Route không tìm thấy
        http_response_code(404);
        View::render('errors/404', null, []);
    }

    /**
     * Gọi controller handler
     */
    private function callHandler($handler, array $params = []): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        if (is_string($handler)) {
            [$controller, $method] = explode('@', $handler);

            // Nếu controller đã có namespace đầy đủ (chứa \), giữ nguyên
            // Ngược lại, thêm Controllers\ prefix cho backward compatibility
            if (strpos($controller, '\\') === false) {
                $controller = "Controllers\\{$controller}";
            }

            if (class_exists($controller)) {
                $instance = new $controller();
                if (method_exists($instance, $method)) {
                    call_user_func_array([$instance, $method], $params);
                    return;
                } else {
                    throw new \Exception("Method '{$method}' không tồn tại trong class '{$controller}'");
                }
            } else {
                throw new \Exception("Class '{$controller}' không tồn tại. Handler: {$handler}");
            }
        }

        throw new \Exception("Handler không hợp lệ: " . print_r($handler, true));
    }
}