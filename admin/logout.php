<?php
/**
 * admin/logout.php - Admin logout (unified with main logout.php)
 * Same as /logout.php but redirects to admin
 */
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_ACTIVE) {
    session_regenerate_id(true);
    session_destroy();
}

setcookie('nsh_remember', '', time() - 3600, '/', '', true, true);

header('Location: /admin/');
exit;
