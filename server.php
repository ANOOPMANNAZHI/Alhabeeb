<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a Laravel
// application without having installed a "real" web server software here.
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

// Handle asset paths that include the /public prefix (used when project is
// deployed from the project root rather than the public directory).
if ($uri !== '/' && strpos($uri, '/public/') === 0) {
    $filePath = realpath(__DIR__.$uri);
    $publicDir = realpath(__DIR__.'/public');
    if ($filePath && $publicDir && strpos($filePath, $publicDir) === 0 && is_file($filePath)) {
        $mimeTypes = [
            'css'   => 'text/css',
            'js'    => 'application/javascript',
            'json'  => 'application/json',
            'png'   => 'image/png',
            'jpg'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'gif'   => 'image/gif',
            'svg'   => 'image/svg+xml',
            'ico'   => 'image/x-icon',
            'woff'  => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf'   => 'font/ttf',
            'eot'   => 'application/vnd.ms-fontobject',
            'map'   => 'application/json',
        ];
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        if (isset($mimeTypes[$ext])) {
            header('Content-Type: '.$mimeTypes[$ext]);
        }
        readfile($filePath);
        exit;
    }
}

require_once __DIR__.'/public/index.php';
