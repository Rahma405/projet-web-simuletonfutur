<?php

abstract class Controller {

    protected function view(string $viewPath, array $data = []): void {
        // Extract data array into variables for the view
        extract($data);

        $fullPath = __DIR__ . '/../views/' . $viewPath . '.php';

        if (!file_exists($fullPath)) {
            die("View not found: {$fullPath}");
        }

        require $fullPath;
    }

    protected function redirect(string $url): void {
        header("Location: {$url}");
        exit();
    }

    protected function isPost(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isGet(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    protected function post(string $key, mixed $default = null): mixed {
        return $_POST[$key] ?? $default;
    }

    protected function get(string $key, mixed $default = null): mixed {
        return $_GET[$key] ?? $default;
    }
}
