<?php
/**
 * Copy this file to config.php and fill real production values.
 */

$_useMySQL = !getenv('REPL_ID') && !getenv('REPLIT_DB_URL');
define('DB_DRIVER',  $_useMySQL ? 'mysql' : 'sqlite');
define('DB_PATH',    __DIR__ . '/data/nsh_dev.sqlite');
define('DB_HOST',    getenv('DB_HOST') ?: 'localhost');
define('DB_NAME',    getenv('DB_NAME') ?: 'your_database_name');
define('DB_USER',    getenv('DB_USER') ?: 'your_database_user');
define('DB_PASS',    getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME',    'आकाशवाणी');
define('SITE_TAGLINE', 'सूचनाको खुला आकाश');
define('SITE_URL',     getenv('SITE_URL') ?: 'https://example.com');
define('SITE_EMAIL',   getenv('SITE_EMAIL') ?: 'admin@example.com');
define('WHATSAPP_NO',  getenv('WHATSAPP_NO') ?: '');
define('ADMIN_PASS',   getenv('ADMIN_PASS') ?: 'CHANGE_ME');
define('SESSION_NAME', 'nsh_session');
define('SITE_LOGO',    '/assets/images/logo.png');

define('PWA_NAME',        defined('SITE_NAME') ? SITE_NAME : 'आकाशवाणी');
define('PWA_SHORT_NAME',  getenv('PWA_SHORT_NAME') ?: 'आकाशवाणी');
define('PWA_THEME_COLOR', '#0f766e');

define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: '');
define('AI_MODEL',       getenv('AI_MODEL') ?: 'gpt-4o-mini');
define('CRON_KEY',       getenv('CRON_KEY') ?: 'CHANGE_ME_CRON_KEY');

define('CACHE_DIR',            __DIR__ . '/data/cache/');
define('GOLD_CACHE_TTL',       3600);
define('FOREX_CACHE_TTL',      3600);
define('NEPSE_CACHE_TTL',      900);
define('PETROL_CACHE_TTL',     86400);
define('NEWS_SYNC_INTERVAL',   1800);
define('NEWS_MAX_PER_SYNC',    20);
define('ALERTS_SYNC_INTERVAL', 1800);
define('MARKET_SYNC_INTERVAL', 3600);

define('OG_IMAGE', SITE_URL . '/assets/images/og-image.jpg');

if (!headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
}
mb_internal_encoding('UTF-8');
ini_set('default_charset', 'utf-8');
date_default_timezone_set('Asia/Kathmandu');

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure',   '1');
    ini_set('session.use_strict_mode', '1');
    session_name(SESSION_NAME);
    session_start();
}

if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}
