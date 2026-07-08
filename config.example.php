<?php
/**
 * आकाशवाणी — Configuration Example
 * Copy this file to config.php and fill in your values
 * config.php is gitignored — do NOT commit real credentials
 */

// ═══════════════════════════════════════════════════════════════
// DATABASE CONFIGURATION
// ═══════════════════════════════════════════════════════════════
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');
define('DB_CHARSET', 'utf8mb4');

// ═══════════════════════════════════════════════════════════════
// SITE CONFIGURATION
// ═══════════════════════════════════════════════════════════════
define('SITE_NAME', 'आकाशवाणी');
define('SITE_TAGLINE', 'सूचनाको खुला आकाश');
define('SITE_URL', 'https://your-domain.com');

// ═══════════════════════════════════════════════════════════════
// SECURITY
// ═══════════════════════════════════════════════════════════════
define('SESSION_TIMEOUT', 7200);
define('CSRF_TOKEN_NAME', 'aakashvani_csrf_token');
define('HASH_COST', 12);

// ═══════════════════════════════════════════════════════════════
// API KEYS (fill in as needed)
// ═══════════════════════════════════════════════════════════════
define('NEPAL_INTEGRATION_KEY', '');   // Nepal data API
define('NEWS_API_KEY', '');             // News aggregator API

// ═══════════════════════════════════════════════════════════════
// CACHE SETTINGS
// ═══════════════════════════════════════════════════════════════
define('CACHE_ENABLED', true);
define('CACHE_TTL', 300);               // 5 minutes default
