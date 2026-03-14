<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';

class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            header('Location: /reports');
            exit;
        }

        $config = require __DIR__ . '/../../config/app.php';
        $pageTitle = 'Login';
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        $viewFile = __DIR__ . '/../views/auth/login.php';
        require __DIR__ . '/../views/layouts/main.php';
    }

    public function login(): void
    {
        $submittedUsername = trim($_POST['username'] ?? '');
        $submittedPassword = $_POST['password'] ?? '';

        if ($submittedUsername === '' || $submittedPassword === '') {
            $_SESSION['login_error'] = 'Username and password are required.';
            header('Location: /login');
            exit;
        }

        $pdo = Database::connect();

        $stmt = $pdo->prepare(
            'SELECT id, username, password_hash, display_name, role, allowed_sections
             FROM users
             WHERE username = :username
             LIMIT 1'
        );
        $stmt->execute([
            ':username' => $submittedUsername,
        ]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($submittedPassword, $user['password_hash'])) {
            $_SESSION['login_error'] = 'Invalid username or password.';
            header('Location: /login');
            exit;
        }

        session_regenerate_id(true);

        $_SESSION['authenticated'] = true;
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['display_name'] = $user['display_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['allowed_sections'] = $user['allowed_sections'];

        $updateStmt = $pdo->prepare(
            'UPDATE users
             SET last_login = NOW()
             WHERE id = :id'
        );
        $updateStmt->execute([
            ':id' => $user['id'],
        ]);

        header('Location: /reports');
        exit;
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        header('Location: /login');
        exit;
    }
}