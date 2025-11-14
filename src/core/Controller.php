<?php

namespace Core;

/**
 * Controller - Base controller
 */
abstract class Controller
{
    /**
     * Render view
     */
    protected function view(string $view, array $data = [], ?string $layout = 'admin/layout/main'): void
    {
        View::render($view, $data, $layout);
    }
    
    /**
     * Redirect
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        header("Location: {$url}", true, $statusCode);
        exit;
    }
    
    /**
     * JSON response
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json', true, $statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Success JSON response
     */
    protected function success($data = null, string $message = 'Thành công'): void
    {
        $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }
    
    /**
     * Error JSON response
     */
    protected function error(string $message = 'Có lỗi xảy ra', int $statusCode = 400): void
    {
        $this->json([
            'success' => false,
            'message' => $message
        ], $statusCode);
    }
    
    /**
     * Get request input
     */
    protected function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
    
    /**
     * Get all request input
     */
    protected function all(): array
    {
        return array_merge($_GET, $_POST);
    }
    
    /**
     * Validate request
     */
    protected function validate(array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $ruleString) {
            $rulesArray = explode('|', $ruleString);
            $value = $this->input($field);
            
            foreach ($rulesArray as $rule) {
                $error = $this->validateField($field, $value, $rule);
                if ($error) {
                    $errors[$field] = $error;
                    break;
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate từng field
     */
    private function validateField(string $field, $value, string $rule): ?string
    {
        if ($rule === 'required' && empty($value)) {
            return "Trường {$field} là bắt buộc";
        }
        
        if (str_starts_with($rule, 'min:')) {
            $min = (int) substr($rule, 4);
            if (strlen($value) < $min) {
                return "Trường {$field} phải có ít nhất {$min} ký tự";
            }
        }
        
        if (str_starts_with($rule, 'max:')) {
            $max = (int) substr($rule, 4);
            if (strlen($value) > $max) {
                return "Trường {$field} không được vượt quá {$max} ký tự";
            }
        }
        
        if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return "Trường {$field} phải là email hợp lệ";
        }
        
        if ($rule === 'numeric' && !is_numeric($value)) {
            return "Trường {$field} phải là số";
        }
        
        return null;
    }
}
