<?php
/**
 * logout.php - Unified logout handler
 * Redirects to home after session destroy
 */
require_once __DIR__ . '/config.php';

// Destroy session safely
if (session_status() === PHP_SESSION_ACTIVE) {
    session_regenerate_id(true);
    session_destroy();
}

// Clear remember-me cookie
setcookie('nsh_remember', '', time() - 3600, '/', '', true, true);

flash('सफलतापूर्वक लग-आउट भयो। धन्यवाद!', 'success');
header('Location: /');
exit;
