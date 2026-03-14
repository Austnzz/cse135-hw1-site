<?php

class Auth
{
    public static function check(): bool
    {
        return !empty($_SESSION['authenticated']);
    }

    public static function user(): array
    {
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? null,
            'display_name' => $_SESSION['display_name'] ?? null,
            'role' => $_SESSION['role'] ?? null,
            'allowed_sections' => $_SESSION['allowed_sections'] ?? null,
        ];
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
    }

    public static function hasRole(array $roles): bool
    {
        $role = $_SESSION['role'] ?? null;
        return $role !== null && in_array($role, $roles, true);
    }

    public static function requireRole(array $roles): void
    {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }

        if (!self::hasRole($roles)) {
            http_response_code(403);

            $config = require __DIR__ . '/../../config/app.php';
            $pageTitle = '403 Forbidden';
            $viewFile = __DIR__ . '/../views/errors/403.php';
            require __DIR__ . '/../views/layouts/main.php';
            exit;
        }
    }
}