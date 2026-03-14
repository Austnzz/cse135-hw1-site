<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';

class ReportsController
{
    public function index(): void
    {
        Auth::requireLogin();

        $config = require __DIR__ . '/../../config/app.php';
        $pageTitle = 'Reports';
        $username = $_SESSION['display_name'] ?? ($_SESSION['username'] ?? 'user');
        $role = $_SESSION['role'] ?? 'viewer';

        $pdo = Database::connect();

        $stmt = $pdo->prepare(
            'SELECT id, received_at, event_type, page, session_id
             FROM events
             WHERE event_type <> :excluded_event
             ORDER BY id DESC
             LIMIT 20'
        );
        $stmt->execute([
            ':excluded_event' => 'mousemove',
        ]);
        $events = $stmt->fetchAll();

        $chartStmt = $pdo->prepare(
            'SELECT event_type, COUNT(*) AS total
             FROM events
             GROUP BY event_type
             ORDER BY total DESC'
        );
        $chartStmt->execute();
        $eventCounts = $chartStmt->fetchAll();

        $viewFile = __DIR__ . '/../views/reports/index.php';
        require __DIR__ . '/../views/layouts/main.php';
    }
}