<?php
/**
 * CSRF Protection Helper
 * Include this in any page that has forms.
 * Usage: csrfField() in forms, csrfVerify() before processing POST.
 */

if (!function_exists('csrfToken')) {
    function csrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrfField')) {
    function csrfField(): string {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken(), ENT_QUOTES) . '">';
    }
}

if (!function_exists('csrfVerify')) {
    function csrfVerify(): bool {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$token || !isset($_SESSION['csrf_token'])) return false;
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('csrfRequire')) {
    function csrfRequire(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrfVerify()) {
            http_response_code(403);
            die(json_encode(['error' => 'CSRF token mismatch']));
        }
    }
}
