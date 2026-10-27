<?php
// Front controller: every non-file request lands here (.htaccess, dev-router).
// Routes come from PAGES in lib/event.php; templates live in pages/.
// Layouts: repo/production  web/public/ + web/lib, web/pages  (app = ..)
//          upload bundle    <docroot>/acro/ + acro/_app/lib, _app/pages (tools/bundle.sh)
declare(strict_types=1);
define('PUBLIC_DIR', __DIR__);
require (is_dir(__DIR__ . '/_app') ? __DIR__ . '/_app' : __DIR__ . '/..') . '/lib/render.php';

$route = route($_SERVER['REQUEST_URI'] ?? '/');
if (isset($route['redirect'])) {
    header('Location: ' . $route['redirect'], true, 301);
    exit;
}
serve($route);
