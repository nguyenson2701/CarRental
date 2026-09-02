<?php

namespace App\Core;

/**
 * Router don gian: khai bao "phuong thuc HTTP + duong dan" -> [Controller, action].
 * Ho tro tham so dong kieu {id} trong duong dan (vi du /admin/brands/{id}/edit).
 *
 * Vi du dang ky:
 *   $router->get('/admin/brands', [BrandController::class, 'index']);
 *   $router->get('/admin/brands/{id}/edit', [BrandController::class, 'edit']);
 *   $router->post('/admin/brands', [BrandController::class, 'store']);
 */
class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][] = [$path, $handler];
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][] = [$path, $handler];
    }

    /**
     * Chay router: tim route khop voi REQUEST_METHOD + duong dan hien tai
     * roi goi Controller->action(...thamSo).
     */
    public function dispatch(string $basePath = ''): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Bo phan base path (vi du /Carrental) de con lai duong dan "logic"
        if ($basePath !== '' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }
        $uri = '/' . trim($uri, '/');

        foreach ($this->routes[$method] ?? [] as [$routePath, $handler]) {
            $params = $this->match($routePath, $uri);
            if ($params !== null) {
                [$controllerClass, $action] = $handler;
                $controller = new $controllerClass();
                call_user_func_array([$controller, $action], $params);
                return;
            }
        }

        http_response_code(404);
        echo '404 - Khong tim thay trang: ' . htmlspecialchars($uri);
    }

    /**
     * So khop 1 duong dan dang ky (co the chua {id}) voi URI thuc te.
     * Tra ve mang tham so theo dung thu tu neu khop, null neu khong khop.
     */
    private function match(string $routePath, string $uri): ?array
    {
        $paramNames = [];
        $pattern = preg_replace_callback('/\{(\w+)\}/', function ($m) use (&$paramNames) {
            $paramNames[] = $m[1];
            return '([^/]+)';
        }, $routePath);

        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            array_shift($matches);
            return array_values($matches);
        }

        return null;
    }
}
