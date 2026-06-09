<?php

/**
 * PHP built-in server router for Railway deployment.
 *
 * When a router script is given, PHP passes EVERY request through
 * it -- including requests for existing static files. "return false"
 * is supposed to let PHP serve them natively, but in practice it
 * uses the document root which may not match public/.
 *
 * This router manually serves static files via readfile() with
 * explicit MIME types, bypassing all that ambiguity.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Resolve the file path inside public/
$publicPath = __DIR__ . '/public';
$file = $publicPath . $uri;

// Serve existing static files directly with correct MIME types.
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    static $mimes = [
        'css'  => 'text/css; charset=utf-8',
        'js'   => 'application/javascript; charset=utf-8',
        'mjs'  => 'application/javascript; charset=utf-8',
        'json' => 'application/json; charset=utf-8',
        'map'  => 'application/json; charset=utf-8',
        'svg'  => 'image/svg+xml; charset=utf-8',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'ico'  => 'image/x-icon',
        'webp' => 'image/webp',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
        'eot'  => 'application/vnd.ms-fontobject',
        'pdf'  => 'application/pdf',
        'txt'  => 'text/plain; charset=utf-8',
        'xml'  => 'application/xml; charset=utf-8',
        'mp4'  => 'video/mp4',
        'webm' => 'video/webm',
    ];

    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $contentType = $mimes[$ext] ?? 'application/octet-stream';

    header('Content-Type: ' . $contentType);
    header('Content-Length: ' . filesize($file));
    // Vite assets are content-hashed: cache them forever.
    if (str_starts_with($uri, '/build/')) {
        header('Cache-Control: public, max-age=31536000, immutable');
    }
    readfile($file);
    exit;
}

// All other requests go through Laravel.
require_once $publicPath . '/index.php';
