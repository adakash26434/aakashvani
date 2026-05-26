<?php
/**
 * includes/header.php — shim for legacy pages (news.php, news-detail.php, sources.php).
 * v2: load config + functions FIRST so db()/auth helpers are defined before notices_render
 *     calls getActiveAppNotices(). Previously caused fatal "Call to undefined function db()".
 */

// Bootstrap core (idempotent — guards via require_once)
$__rootDir = dirname(__DIR__);
require_once $__rootDir . '/config.php';
require_once $__rootDir . '/functions.php';

// Now safe to render notices (uses db())
require_once __DIR__ . '/notices_render.php';
require_once __DIR__ . '/search_widget.php';

// Delegate to the unified app header (design system, nav, etc.)
require_once $__rootDir . '/header.php';
