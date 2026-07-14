<?php
namespace App\Core;

class Router {
    private array $routes = [];

    public function get(string $path, string $controller, string $method): void {
        $this->routes[] = ['GET', $path, $controller, $method];
    }

    public function post(string $path, string $controller, string $method): void {
        $this->routes[] = ['POST', $path, $controller, $method];
    }

    public function dispatch(string $uri, string $httpMethod): void {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        // Remove base path se existir
        $base = parse_url(APP_URL, PHP_URL_PATH);
        if ($base && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base)) ?: '/';
        }

        foreach ($this->routes as [$method, $path, $controller, $action]) {
            if ($method !== $httpMethod) continue;

            $pattern = preg_replace('#\{[^}]+\}#', '([^/]+)', $path);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                $class = 'App\\Controllers\\' . str_replace('/', '\\', $controller);
                $ctrl  = new $class();
                call_user_func_array([$ctrl, $action], $matches);
                return;
            }
        }

        // 404
        http_response_code(404);
        include __DIR__ . '/../Views/errors/404.php';
    }
}
