<?php

class Router
{
    public function dispatch(string $route): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($route === '/' || $route === '/login') {
            require __DIR__ . '/../controllers/AuthController.php';
            $controller = new AuthController();

            if ($method === 'POST') {
                $controller->login();
                return;
            }

            $controller->showLogin();
            return;
        }

        if ($route === '/logout') {
            require __DIR__ . '/../controllers/AuthController.php';
            $controller = new AuthController();
            $controller->logout();
            return;
        }

        if ($route === '/reports') {
            require __DIR__ . '/../controllers/ReportsController.php';
            $controller = new ReportsController();
            $controller->index();
            return;
        }

        if ($route === '/saved-reports') {
            require __DIR__ . '/../controllers/SavedReportsController.php';
            $controller = new SavedReportsController();
            $controller->index();
            return;
        }

        if (preg_match('#^/saved-reports/([a-zA-Z0-9\-]+)$#', $route, $matches)) {
            require __DIR__ . '/../controllers/SavedReportsController.php';
            $controller = new SavedReportsController();
            $controller->show($matches[1]);
            return;
        }

        if (preg_match('#^/export/report/([a-zA-Z0-9\-]+)$#', $route, $matches)) {
            require __DIR__ . '/../controllers/SavedReportsController.php';
            $controller = new SavedReportsController();
            $controller->export($matches[1]);
            return;
        }

        if (preg_match('#^/download-export/([a-zA-Z0-9._\-]+)$#', $route, $matches)) {
            require __DIR__ . '/../controllers/SavedReportsController.php';
            $controller = new SavedReportsController();
            $controller->download($matches[1]);
            return;
        }

        if ($route === '/admin/users') {
            require __DIR__ . '/../controllers/AdminController.php';
            $controller = new AdminController();
            $controller->users();
            return;
        }

        $this->renderErrorPage(404, '404');
    }

    private function renderErrorPage(int $statusCode, string $viewName): void
    {
        http_response_code($statusCode);

        $config = require __DIR__ . '/../../config/app.php';
        $pageTitle = $statusCode . ' ' . ($viewName === '403' ? 'Forbidden' : 'Not Found');
        $viewFile = __DIR__ . '/../views/errors/' . $viewName . '.php';

        require __DIR__ . '/../views/layouts/main.php';
    }
}