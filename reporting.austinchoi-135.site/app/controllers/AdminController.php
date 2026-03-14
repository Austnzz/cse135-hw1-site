<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';

class AdminController
{
    public function users(): void
    {
        Auth::requireRole(['super_admin']);

        $config = require __DIR__ . '/../../config/app.php';
        $pageTitle = 'User Management';

        $pdo = Database::connect();

        $stmt = $pdo->query(
            'SELECT id, username, display_name, role, allowed_sections, created_at, last_login
             FROM users
             ORDER BY id ASC'
        );
        $users = $stmt->fetchAll();

        $viewFile = __DIR__ . '/../views/admin/users.php';
        require __DIR__ . '/../views/layouts/main.php';
    }
}