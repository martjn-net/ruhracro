<?php
// Local only: `php -S 127.0.0.1:8931 -t web/public web/dev-router.php`.
// Mirrors web/public/.htaccess: real files are served as-is, everything else
// goes to the front controller (not deployed: outside the docroot).
declare(strict_types=1);

$file = __DIR__ . '/public' . (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
if (is_file($file) && !str_starts_with(basename($file), '.') && basename($file) !== 'index.php') {
    return false;
}
require __DIR__ . '/public/index.php';
