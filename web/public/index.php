<?php
// Front controller: every non-file request lands here (.htaccess, dev-router).
// Routes come from PAGES in web/lib/event.php; templates live in web/pages/.
declare(strict_types=1);
require __DIR__ . '/../lib/render.php';

$route = route($_SERVER['REQUEST_URI'] ?? '/');
if (isset($route['redirect'])) {
    header('Location: ' . $route['redirect'], true, 301);
    exit;
}
serve($route);
