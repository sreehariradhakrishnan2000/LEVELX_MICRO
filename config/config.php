<?php
/**
 * Application Configuration
 */

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

// Timezone
date_default_timezone_set('Asia/Kolkata');

// App Info
define('APP_NAME', 'EventSphere');
define('APP_TAGLINE', 'Discover, Register & Experience Premier Events');

// Database Configuration
define('DB_DRIVER', getenv('DB_DRIVER') ?: 'auto'); // Options: 'mysql', 'sqlite', 'auto' (tries MySQL, fallbacks to SQLite if MySQL is offline)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'event_management');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

// Base URL helper
function getBaseUrl(): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = dirname($script);
    $dir = str_replace('\\', '/', $dir);
    // Strip admin or subfolder if in getBaseUrl
    $dir = preg_replace('/(\/admin|\/database|\/tests)$/', '', $dir);
    return rtrim($protocol . $host . $dir, '/');
}
