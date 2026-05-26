<?php
/**
 * आकाशवाणी — config.php v9 (SECURITY IMPROVED)
 * 
 * !! SECURITY NOTICE !!
 * Replace DB_PASS and ADMIN_PASS values below with your own
 * credentials when deploying to cPanel. Never commit plain passwords
 * to version control. Consider moving sensitive values to a .env file
 * or a file outside public_html.
 */

// ─── Database ─────────────────────────────────────────────────────────────────
// Auto-detect: use SQLite in Replit dev, MySQL in production (cPanel)
$_useMySQL = !getenv('REPL_ID') && !getenv('REPLIT_DB_URL');
define('DB_DRIVER',  $_useMySQL ? 'mysql' : 'sqlite');
define('DB_PATH',    __DIR__ . '/data/nsh_dev.sqlite');
define('DB_HOST',    'localhost');
define('DB_NAME',    'tankaadh_admin');   // replace with your cPanel DB name
define('DB_USER',    'tankaadh_admin');   // replace with your cPanel DB user
define('DB_PASS',    getenv('DB_PASS') ?: 'CHANGE_ME_DB_PASS'); // Set DB_PASS in cPanel environment variables — NEVER hardcode here!
define('DB_CHARSET', 'utf8mb4');

// ─── Site ─────────────────────────────────────────────────────────────────────
define('SITE_NAME',    'आकाशवाणी');
define('SITE_TAGLINE', 'सूचनाको खुला आकाश');
define('SITE_URL',     'https://www.tankaadhikari.com.np');
define('SITE_EMAIL',   'aakashpame@gmail.com');
define('WHATSAPP_NO',  '9827157000');
define('ADMIN_PASS',   'CHANGE_ME_ADMIN_PASS'); // CHANGE THIS before deploying!
define('SESSION_NAME', 'nsh_session');
define('SITE_LOGO',    '/assets/images/logo.png');

// ─── PWA (Progressive Web App) ─────────────────────────────────────────────
define('PWA_NAME',        defined('SITE_NAME') ? SITE_NAME : 'आकाशवाणी');
define('PWA_SHORT_NAME',  getenv('PWA_SHORT_NAME') ?: 'आकाशवाणी');
define('PWA_THEME_COLOR', '#0f766e');

// ─── AI (optional — set OPENAI_API_KEY in cPanel env vars or here) ───────────
define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: '');
define('AI_MODEL',       'gpt-4o-mini');

// ─── Cron key (protects /api/auto-sync.php, /api/sync-all.php, /api/morning-brief.php?gen=1) ──
// Change this string to anything random. Use in cPanel Cron:
//   curl -s "https://www.tankaadhikari.com.np/api/auto-sync.php?key=YOUR_CRON_KEY"
define('CRON_KEY', getenv('CRON_KEY') ?: 'CHANGE_ME_CRON_KEY');

// ─── Cache ───────────────────────────────────────────────────────────────────
define('CACHE_DIR',            __DIR__ . '/data/cache/');
define('GOLD_CACHE_TTL',       3600);
define('FOREX_CACHE_TTL',      3600);
define('NEPSE_CACHE_TTL',      900);
define('PETROL_CACHE_TTL',     86400);
define('NEWS_SYNC_INTERVAL',   1800);
define('NEWS_MAX_PER_SYNC',    20);
define('ALERTS_SYNC_INTERVAL', 1800);
define('MARKET_SYNC_INTERVAL', 3600);

// ─── SEO ─────────────────────────────────────────────────────────────────────
define('OG_IMAGE', SITE_URL . '/assets/images/og-image.jpg');

// ─── Output Compression (PHP-level gzip fallback) ────────────────────────────
// Kicks in when mod_deflate is not available on the server.
// Must be called before any output — config.php is included first everywhere.
if (!ob_get_level() && !headers_sent()) {
    if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
        @ob_start('ob_gzhandler');
    } else {
        ob_start();
    }
}

// ─── UTF-8 Encoding (CRITICAL for Nepali text) ────────────────────────────────
if (!headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
}
mb_internal_encoding('UTF-8');
ini_set('default_charset', 'utf-8');

// ─── Timezone ─────────────────────────────────────────────────────────────────
date_default_timezone_set('Asia/Kathmandu');

// ─── Session ─────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure',   '1'); // only over HTTPS
    ini_set('session.use_strict_mode', '1');
    session_name(SESSION_NAME);
    session_start();
}

// ─── Security headers ─────────────────────────────────────────────────────────
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}
