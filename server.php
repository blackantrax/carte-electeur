<?php

/**
 * Router script for PHP built-in server (Railway deployment).
 *
 * PHP's built-in server routes ALL requests through this script
 * when a router file is specified. This script returns false for
 * existing static files so PHP serves them directly with the
 * correct MIME type. Dynamic routes are passed to Laravel's
 * public/index.php.
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// If the requested file exists in public/, return false so
// PHP serves it directly (correct Content-Type, no Laravel overhead).
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

require_once __DIR__ . '/public/index.php';
