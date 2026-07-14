<?php
namespace App\Core;

class Auth {

    public static function login(array $usuario): void {
        $_SESSION['usuario_id']   = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_tipo'] = $usuario['tipo']; // 'admin' | 'empresa'
        $_SESSION['empresa_id']   = $usuario['empresa_id'] ?? null;
        session_regenerate_id(true);
    }

    public static function logout(): void {
        $_SESSION = [];
        session_destroy();
    }

    public static function check(): bool {
        return isset($_SESSION['usuario_id']);
    }

    public static function isAdmin(): bool {
        return ($_SESSION['usuario_tipo'] ?? '') === 'admin';
    }

    public static function isEmpresa(): bool {
        return ($_SESSION['usuario_tipo'] ?? '') === 'empresa';
    }

    public static function id(): ?int {
        return $_SESSION['usuario_id'] ?? null;
    }

    public static function nome(): string {
        return $_SESSION['usuario_nome'] ?? '';
    }

    public static function empresaId(): ?int {
        return $_SESSION['empresa_id'] ?? null;
    }

    public static function requireAdmin(): void {
        if (!self::check() || !self::isAdmin()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }
    }

    public static function requireEmpresa(): void {
        if (!self::check()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }
    }
}
