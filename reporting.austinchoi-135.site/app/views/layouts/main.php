<?php
$appName = $config['app_name'] ?? 'App';
$isAuthenticated = !empty($_SESSION['authenticated']);
$displayName = $_SESSION['display_name'] ?? ($_SESSION['username'] ?? 'User');
$role = $_SESSION['role'] ?? null;

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

function navIsActive(string $href, string $currentPath): bool
{
    if ($href === '/') {
        return $currentPath === '/';
    }

    return $currentPath === $href || str_starts_with($currentPath, rtrim($href, '/') . '/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - <?= htmlspecialchars($appName) ?></title>
    <link rel="stylesheet" href="/public/styles.css">
</head>
<body>
    <noscript>
        <div class="noscript-banner">
            JavaScript is turned off. Charts and some interactive elements may be limited, but reports, tables, and exports should still remain available.
        </div>
    </noscript>

    <header class="site-header">
        <div class="container site-header-inner">
            <div class="brand-block">
                <h1 class="site-title"><?= htmlspecialchars($appName) ?></h1>
                <p class="site-subtitle">
                    Analytics dashboard, curated reports, role-based access, and exportable report outputs.
                </p>
            </div>

            <div class="header-right">
                <?php if ($isAuthenticated): ?>
                    <nav class="top-nav nav-primary" aria-label="Primary navigation">
                        <a href="/reports" <?= navIsActive('/reports', $currentPath) ? 'aria-current="page"' : '' ?>>
                            Overview
                        </a>
                        <a href="/saved-reports" <?= navIsActive('/saved-reports', $currentPath) ? 'aria-current="page"' : '' ?>>
                            Saved Reports
                        </a>
                        <?php if ($role === 'super_admin'): ?>
                            <a href="/admin/users" <?= navIsActive('/admin/users', $currentPath) ? 'aria-current="page"' : '' ?>>
                                Manage Users
                            </a>
                        <?php endif; ?>
                    </nav>

                    <div class="header-account">
                        <a href="/logout" class="nav-text-link">Logout</a>

                        <div class="user-badge" aria-label="Signed in user">
                            <span class="user-badge-label">Signed in as</span>
                            <strong class="user-badge-name"><?= htmlspecialchars($displayName) ?></strong>
                            <span class="user-badge-role">(<?= htmlspecialchars($role ?? 'unknown') ?>)</span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="header-account">
                        <a href="/login" class="nav-text-link" <?= navIsActive('/login', $currentPath) ? 'aria-current="page"' : '' ?>>
                            Login
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="container page-shell">
        <?php require $viewFile; ?>
    </main>
</body>
</html>