<?php
/**
 * आकाशवाणी — Autoloader & Bootstrap
 *
 * This is the SINGLE entry point for all shared functions and classes.
 * Every page should require this file instead of picking individual includes.
 *
 * Load order matters — config must be first (defines constants),
 * then functions (core helpers), then data-manager (uses both).
 */

$__root = dirname(__DIR__);

// 1. Configuration — MUST be first
require_once $__root . '/config.php';

// 2. Core functions — second (sanitize, timeAgo, slugify, security helpers)
require_once $__root . '/functions.php';

// 3. Data layer — depends on config
require_once $__root . '/includes/data-schema.php';
require_once $__root . '/includes/data-manager.php';

// 4. Auth helpers — depends on config, functions
require_once $__root . '/includes/auth.php';

// 5. API response wrapper — used by all API endpoints
require_once $__root . '/includes/api-response.php';

// 6. Error handling — global error handler + logger
require_once $__root . '/includes/error-handler.php';
require_once $__root . '/includes/error-logger.php';

// 7. SEO helper
require_once $__root . '/includes/seo-helper.php';

// 8. CSRF helper
require_once $__root . '/includes/csrf.php';
?>
