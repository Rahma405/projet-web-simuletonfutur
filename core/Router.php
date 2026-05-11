<?php

class Router {
    private array $routes = [];

    public function get(string $path, string $controller, string $method): void {
        $this->routes['GET'][$path] = ['controller' => $controller, 'method' => $method];
    }

    public function post(string $path, string $controller, string $method): void {
        $this->routes['POST'][$path] = ['controller' => $controller, 'method' => $method];
    }

    public function dispatch(string $requestUri, string $requestMethod): void {
        // Use ?route= param if present (XAMPP without rewrite)
        if (isset($_GET['route'])) {
            $path = '/' . ltrim($_GET['route'], '/');
        } else {
            $path      = strtok($requestUri, '?');
            $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
            if ($scriptDir !== '/' && str_starts_with($path, $scriptDir)) {
                $path = substr($path, strlen($scriptDir));
            }
            $path = '/' . ltrim($path, '/');
        }

        $path   = $path ?: '/';
        $routes = $this->routes[$requestMethod] ?? [];

        if (isset($routes[$path])) {
            $route      = $routes[$path];
            $controller = new $route['controller']();
            $controller->{$route['method']}();
        } else {
            // Try GET route as fallback for POST-heavy user pages
            $fallback = $this->routes['GET'][$path] ?? null;
            if ($fallback && $requestMethod === 'POST') {
                $controller = new $fallback['controller']();
                $controller->{$fallback['method']}();
            } else {
                http_response_code(404);
                echo "<h1>404 – Page Not Found</h1><p>No route: <code>" . htmlspecialchars($path) . "</code></p>";
            }
        }
    }
}
