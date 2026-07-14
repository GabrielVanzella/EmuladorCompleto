<?php
namespace App\Core;

class Controller {

    protected function view(string $view, array $data = [], string $layout = 'main'): void {
        extract($data);
        $viewFile   = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';
        $layoutFile = __DIR__ . '/../Views/layouts/' . $layout . '.php';

        if (!file_exists($viewFile)) {
            die("View não encontrada: $view");
        }

        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        if ($layout && file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }

    protected function json(mixed $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect(string $url): void {
        header('Location: ' . APP_URL . $url);
        exit;
    }

    protected function redirectTo(string $url): void {
        header('Location: ' . $url);
        exit;
    }

    protected function isPost(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function input(string $key, mixed $default = null): mixed {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function sanitize(string $value): string {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    protected function flash(string $type, string $message): void {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    protected function getFlash(): ?array {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}
