<?php

session_start();

require __DIR__ . '/../app/core/Router.php';

$route = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router = new Router();
$router->dispatch($route);