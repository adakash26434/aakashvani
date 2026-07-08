<?php
/**
 * आकाशवाणी - Configuration
 * सूचनाको खुला आकाश
 */

// Database Configuration - UPDATE THESE WITH YOUR CREDENTIALS
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_NAME', 'आकाशवाणी');
define('SITE_TAGLINE', 'सूचनाको खुला आकाश');
define('SITE_URL', 'https://news.bandanasigdel.com.np');

// Timezone
date_default_timezone_set('Asia/Kathmandu');

// Session
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Language
function siteLang() {
    if (isset($_GET['lang']) && $_GET['lang'] === 'en') {
        $_SESSION['lang'] = 'en';
    }
    if (isset($_SESSION['lang'])) {
        return $_SESSION['lang'];
    }
    return 'ne';
}

// Helper translation
if (!function_exists('t')) {
    function t($ne, $en = '') {
        $lang = siteLang();
        return $lang === 'en' ? $en : $ne;
    }
}

// Database Connection
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            return null;
        }
    }
    return $pdo;
}

// Alias for getDB() — maintains compatibility with codebase that uses db()
if (!function_exists('db')) {
    function db() {
        return getDB();
    }
}

// Time Ago
if (!function_exists('timeAgo')) {
    function timeAgo($datetime) {
        if (!$datetime) return '';
        $time = strtotime($datetime);
        $diff = time() - $time;
        if ($diff < 60) return $diff . 's ago';
        if ($diff < 3600) return floor($diff / 60) . 'm ago';
        if ($diff < 86400) return floor($diff / 3600) . 'h ago';
        if ($diff < 604800) return floor($diff / 86400) . 'd ago';
        return date('j M', $time);
    }
}

// Sanitize
if (!function_exists('sanitize')) {
    function sanitize($str) {
        return htmlspecialchars(trim($str ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

// Truncate text to a given length
if (!function_exists('truncateText')) {
    function truncateText(string $str, int $length = 200): string {
        $str = strip_tags($str ?? '');
        if (mb_strlen($str) <= $length) return $str;
        return mb_substr($str, 0, $length) . '…';
    }
}
