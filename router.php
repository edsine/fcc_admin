<?php
// router.php

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Path to your "web" directory
$webPath = __DIR__ . '/web';

// Serve the file directly if it exists (e.g., CSS, JS, images)
if ($uri !== '/' && file_exists($webPath . $uri)) {
    return false;
}

// Fallback to front controller
require_once $webPath . '/app_dev.php'; // or app.php

//php -S localhost:8001 router.php
