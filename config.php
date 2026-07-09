<?php
/**
 * आकाशवाणी - Configuration
 * सूचनाको खुला आकाश
 */

// Database Configuration - UPDATE THESE WITH YOUR CREDENTIALS
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'your_database');
define('DB_USER', getenv('DB_USER') ?: 'your_username');
define('DB_PASS', getenv('DB_PASS') ?: 'your_password');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_NAME', getenv('SITE_NAME') ?: 'आकाशवाणी');
define('SITE_TAGLINE', getenv('SITE_TAGLINE') ?: 'सूचनाको खुला आकाश');
define('SITE_URL', getenv('SITE_URL') ?: 'https://news.bandanasigdel.com.np');
define('SITE_EMAIL', getenv('SITE_EMAIL') ?: 'admin@example.com');

// Admin Password - SET THIS via ADMIN_PASS environment variable
// For security, use: define('ADMIN_PASS', getenv('ADMIN_PASS'));
// Default fallback for initial setup only - CHANGE IN PRODUCTION
define('ADMIN_PASS', getenv('ADMIN_PASS') ?: 'CHANGE_ME_admin_password_123');

// Social Media Links - SET THESE via environment variables
// Leave empty to hide social icons
define('SOCIAL_FACEBOOK', getenv('SOCIAL_FACEBOOK') ?: '');
define('SOCIAL_TWITTER', getenv('SOCIAL_TWITTER') ?: '');
define('SOCIAL_YOUTUBE', getenv('SOCIAL_YOUTUBE') ?: '');
define('SOCIAL_INSTAGRAM', getenv('SOCIAL_INSTAGRAM') ?: '');

// Auto-sync: set a strong random key, then add to cron:
//   0,30 * * * * /usr/bin/php /home/USER/public_html/cron/ai-sync.php
// Use sync-trigger via: POST /api/sync-trigger.php?key=YOUR_CRON_KEY
define('CRON_KEY', getenv('CRON_KEY') ?: 'CHANGE_ME_to_a_strong_random_string');

// Timezone
date_default_timezone_set('Asia/Kathmandu');

// Session
if (session_status() === PHP_SESSION_NONE) {
    @session_start([
        'cookie_lifetime' => 0,
        'cookie_httponly' => 1,
        'cookie_secure'   => isset($_SERVER['HTTPS']),
        'use_strict_mode' => 1,
        'use_only_cookies' => 1,
    ]);
}
// Session timeout: destroy admin sessions idle > 2 hours
if (!defined('SESSION_TIMEOUT')) define('SESSION_TIMEOUT', 7200); // 2 hours
if (!empty($_SESSION['admin_logged_in']) || !empty($_SESSION['is_admin']) || !empty($_SESSION['nh_admin'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        session_start();
    } else {
        $_SESSION['last_activity'] = time();
    }
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
