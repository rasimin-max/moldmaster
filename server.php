<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server.
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    // PHP's built-in server refuses to serve symlinks that point outside the docroot.
    // So we manually serve /storage/ files.
    if (strpos($uri, '/storage/') === 0) {
        $path = __DIR__.'/storage/app/public/' . substr($uri, 9);
        if (file_exists($path)) {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $mimes = [
                'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
                'gif' => 'image/gif', 'svg' => 'image/svg+xml', 'webp' => 'image/webp',
                'pdf' => 'application/pdf', 'txt' => 'text/plain', 'csv' => 'text/csv'
            ];
            $mime = $mimes[strtolower($ext)] ?? mime_content_type($path);
            header("Content-Type: $mime");
            readfile($path);
            exit;
        }
    }
    return false;
}

require_once __DIR__.'/public/index.php';
