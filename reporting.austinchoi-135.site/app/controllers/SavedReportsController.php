<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class SavedReportsController
{
    private function shortenPageLabel(?string $page): string
    {
        if (!$page) {
            return 'Unknown';
        }

        return str_replace('https://test.austinchoi-135.site/', '', $page);
    }

    private function renderErrorPage(int $statusCode, string $viewName, string $pageTitle): void
    {
        http_response_code($statusCode);

        $config = require __DIR__ . '/../../config/app.php';
        $viewFile = __DIR__ . '/../views/errors/' . $viewName . '.php';

        require __DIR__ . '/../views/layouts/main.php';
    }

    private function renderNotFound(): void
    {
        $this->renderErrorPage(404, '404', '404 Not Found');
    }

    private function renderExportFailed(): void
    {
        $this->renderErrorPage(500, 'export-failed', 'Export Failed');
    }

    private function buildReportData(string $slug, string $role): ?array
    {
        $pdo = Database::connect();

        if ($role === 'viewer') {
            $reportStmt = $pdo->prepare(
                'SELECT sr.id, sr.title, sr.slug, sr.category, sr.section, sr.chart_type,
                        sr.commentary, sr.is_published, sr.created_at, sr.updated_at,
                        u.display_name AS author_name
                 FROM saved_reports sr
                 LEFT JOIN users u ON sr.created_by = u.id
                 WHERE sr.slug = :slug
                   AND sr.is_published = TRUE
                 LIMIT 1'
            );
        } else {
            $reportStmt = $pdo->prepare(
                'SELECT sr.id, sr.title, sr.slug, sr.category, sr.section, sr.chart_type,
                        sr.commentary, sr.is_published, sr.created_at, sr.updated_at,
                        u.display_name AS author_name
                 FROM saved_reports sr
                 LEFT JOIN users u ON sr.created_by = u.id
                 WHERE sr.slug = :slug
                 LIMIT 1'
            );
        }

        $reportStmt->execute([':slug' => $slug]);
        $report = $reportStmt->fetch();

        if (!$report) {
            return null;
        }

        $chartLabels = [];
        $chartValues = [];
        $tableRows = [];
        $summaryText = '';
        $chartDatasetLabel = 'Report Data';
        $tableIntro = 'Supporting event data for this report.';

        $trendLabels = [];
        $trendValues = [];
        $trendTitle = '';
        $trendIntro = '';

        $recentSessions = [];

        if ($report['category'] === 'behavior') {
            $chartStmt = $pdo->prepare(
                'SELECT event_type, COUNT(*) AS total
                FROM events
                WHERE event_type NOT IN (\'mousemove\')
                GROUP BY event_type
                ORDER BY total DESC'
            );
            $chartStmt->execute();
            $chartData = $chartStmt->fetchAll();

            foreach ($chartData as $row) {
                $chartLabels[] = ucfirst($row['event_type']);
                $chartValues[] = (int) $row['total'];
            }

            $tableStmt = $pdo->prepare(
                'SELECT id, received_at, event_type, page, session_id
                FROM events
                WHERE event_type NOT IN (\'mousemove\')
                ORDER BY id DESC
                LIMIT 20'
            );
            $tableStmt->execute();
            $tableRows = $tableStmt->fetchAll();

            $trendStmt = $pdo->prepare(
                "SELECT TO_CHAR(date_trunc('minute', received_at), 'YYYY-MM-DD HH24:MI') AS bucket,
                        COUNT(*) AS total
                FROM events
                WHERE event_type NOT IN ('mousemove')
                GROUP BY date_trunc('minute', received_at)
                ORDER BY date_trunc('minute', received_at) ASC
                LIMIT 12"
            );
            $trendStmt->execute();
            $trendData = $trendStmt->fetchAll();

            foreach ($trendData as $row) {
                $trendLabels[] = $row['bucket'];
                $trendValues[] = (int) $row['total'];
            }

            $sessionStmt = $pdo->prepare(
                "SELECT
                    session_id,
                    COUNT(*) AS event_count,
                    MIN(received_at) AS first_seen,
                    MAX(received_at) AS last_seen,
                    COUNT(DISTINCT page) AS distinct_pages,
                    COUNT(*) FILTER (WHERE event_type = 'error') AS error_count,
                    COUNT(*) FILTER (WHERE event_type = 'performance') AS performance_count,
                    MIN(page) AS sample_page
                FROM events
                WHERE event_type NOT IN ('mousemove')
                AND session_id IS NOT NULL
                AND session_id <> ''
                GROUP BY session_id
                ORDER BY MAX(received_at) DESC
                LIMIT 6"
            );
            $sessionStmt->execute();
            $recentSessions = $sessionStmt->fetchAll();

            $summaryText = 'This report focuses on how users interact with the site by comparing major event types, showing how meaningful activity changes over time, and summarizing recent sessions.';
            $chartDatasetLabel = 'Behavior Event Count';
            $tableIntro = 'Recent interaction events excluding high-volume mousemove noise.';

            $trendTitle = 'Behavior Over Time';
            $trendIntro = 'A lightweight trend view of recent meaningful behavior activity grouped into recent time buckets.';
        } elseif ($report['category'] === 'errors') {
            $chartStmt = $pdo->prepare(
                'SELECT page, COUNT(*) AS total
                 FROM events
                 WHERE event_type = :event_type
                 GROUP BY page
                 ORDER BY total DESC'
            );
            $chartStmt->execute([':event_type' => 'error']);
            $chartData = $chartStmt->fetchAll();

            foreach ($chartData as $row) {
                $chartLabels[] = $this->shortenPageLabel($row['page']);
                $chartValues[] = (int) $row['total'];
            }

            $tableStmt = $pdo->prepare(
                'SELECT id, received_at, event_type, page, session_id
                 FROM events
                 WHERE event_type = :event_type
                 ORDER BY id DESC
                 LIMIT 20'
            );
            $tableStmt->execute([':event_type' => 'error']);
            $tableRows = $tableStmt->fetchAll();

            $summaryText = 'This report highlights where client-side errors were observed so unstable pages or flows can be prioritized for debugging.';
            $chartDatasetLabel = 'Error Count by Page';
            $tableIntro = 'Recent captured error events with page context.';
        } elseif ($report['category'] === 'performance') {
            $chartStmt = $pdo->prepare(
                'SELECT page, COUNT(*) AS total
                 FROM events
                 WHERE event_type = :event_type
                 GROUP BY page
                 ORDER BY total DESC'
            );
            $chartStmt->execute([':event_type' => 'performance']);
            $chartData = $chartStmt->fetchAll();

            foreach ($chartData as $row) {
                $chartLabels[] = $this->shortenPageLabel($row['page']);
                $chartValues[] = (int) $row['total'];
            }

            $tableStmt = $pdo->prepare(
                'SELECT id, received_at, event_type, page, session_id
                 FROM events
                 WHERE event_type = :event_type
                 ORDER BY id DESC
                 LIMIT 20'
            );
            $tableStmt->execute([':event_type' => 'performance']);
            $tableRows = $tableStmt->fetchAll();

            $summaryText = 'This report highlights where performance-related events were recorded so responsiveness patterns can be reviewed by page.';
            $chartDatasetLabel = 'Performance Events by Page';
            $tableIntro = 'Recent performance-related events captured from the site.';
        }

        return [
            'report' => $report,
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,
            'tableRows' => $tableRows,
            'summaryText' => $summaryText,
            'chartDatasetLabel' => $chartDatasetLabel,
            'tableIntro' => $tableIntro,
            'trendLabels' => $trendLabels,
            'trendValues' => $trendValues,
            'trendTitle' => $trendTitle,
            'trendIntro' => $trendIntro,
            'recentSessions' => $recentSessions,
        ];
    }

    public function index(): void
    {
        Auth::requireLogin();

        $config = require __DIR__ . '/../../config/app.php';
        $pageTitle = 'Saved Reports';
        $role = $_SESSION['role'] ?? 'viewer';

        $pdo = Database::connect();

        if ($role === 'viewer') {
            $stmt = $pdo->prepare(
                'SELECT sr.id, sr.title, sr.slug, sr.category, sr.section, sr.chart_type,
                        sr.commentary, sr.is_published, sr.created_at, sr.updated_at,
                        u.display_name AS author_name
                 FROM saved_reports sr
                 LEFT JOIN users u ON sr.created_by = u.id
                 WHERE sr.is_published = TRUE
                 ORDER BY sr.id ASC'
            );
            $stmt->execute();
        } else {
            $stmt = $pdo->prepare(
                'SELECT sr.id, sr.title, sr.slug, sr.category, sr.section, sr.chart_type,
                        sr.commentary, sr.is_published, sr.created_at, sr.updated_at,
                        u.display_name AS author_name
                 FROM saved_reports sr
                 LEFT JOIN users u ON sr.created_by = u.id
                 ORDER BY sr.id ASC'
            );
            $stmt->execute();
        }

        $savedReports = $stmt->fetchAll();

        $viewFile = __DIR__ . '/../views/reports/saved-index.php';
        require __DIR__ . '/../views/layouts/main.php';
    }

    public function show(string $slug): void
    {
        Auth::requireLogin();

        $config = require __DIR__ . '/../../config/app.php';
        $role = $_SESSION['role'] ?? 'viewer';

        $data = $this->buildReportData($slug, $role);

        if (!$data) {
            $this->renderNotFound();
            return;
        }

        extract($data);

        $pageTitle = $report['title'];
        $viewFile = __DIR__ . '/../views/reports/saved-show.php';
        require __DIR__ . '/../views/layouts/main.php';
    }

    public function export(string $slug): void
    {
        Auth::requireLogin();

        $config = require __DIR__ . '/../../config/app.php';
        $role = $_SESSION['role'] ?? 'viewer';

        $data = $this->buildReportData($slug, $role);

        if (!$data) {
            $this->renderNotFound();
            return;
        }

        extract($data);

        ob_start();
        require __DIR__ . '/../views/reports/export-pdf.php';
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $safeSlug = preg_replace('/[^a-zA-Z0-9\-]/', '-', $report['slug']);
        $timestamp = date('Ymd_His');
        $exportFilename = $safeSlug . '_' . $timestamp . '.pdf';
        $exportPath = __DIR__ . '/../../storage/exports/' . $exportFilename;

        $pdfBytes = $dompdf->output();
        $writeResult = file_put_contents($exportPath, $pdfBytes);

        if ($writeResult === false) {
            $this->renderExportFailed();
            return;
        }

        $pageTitle = 'Export Complete';
        $downloadUrl = '/download-export/' . rawurlencode($exportFilename);
        $viewFile = __DIR__ . '/../views/reports/export-result.php';
        require __DIR__ . '/../views/layouts/main.php';
    }

    public function download(string $filename): void
    {
        Auth::requireLogin();

        $safeFilename = basename($filename);
        $filePath = __DIR__ . '/../../storage/exports/' . $safeFilename;

        if (!is_file($filePath)) {
            $this->renderNotFound();
            return;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $safeFilename . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}