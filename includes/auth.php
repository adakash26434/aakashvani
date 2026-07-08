<?php
/**
 * auth.php - PDO-based authentication for आकाशवाणी
 * Rewritten to use PDO (SQLite/MySQL) matching functions.php db() system
 */

if (!function_exists('startAuthSession')) {
    function startAuthSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}

// ── Ensure full auth users table ────────────────────────────────────────────
if (!function_exists('ensureAuthTable')) {
    function ensureAuthTable(): void {
        static $done = false;
        if ($done) return;
        try {
            // Dual-driver: MySQL uses AUTO_INCREMENT, SQLite uses AUTOINCREMENT.
            $idCol = function_exists('isMysql') && isMysql()
                ? 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY'
                : 'INTEGER PRIMARY KEY AUTOINCREMENT';
            $engine = function_exists('isMysql') && isMysql()
                ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
                : '';
            db()->exec("CREATE TABLE IF NOT EXISTS auth_users (
                id            $idCol,
                email         VARCHAR(200) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                full_name     VARCHAR(200) NOT NULL,
                phone         VARCHAR(20),
                language      VARCHAR(5) DEFAULT 'ne',
                is_active     TINYINT NOT NULL DEFAULT 1,
                created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            )$engine");
        } catch (Throwable $e) {
            error_log('[auth] ensureAuthTable failed: ' . $e->getMessage());
        }
        $done = true;
    }
}

if (!function_exists('isLoggedIn')) {
    function isLoggedIn(): bool {
        startAuthSession();
        return !empty($_SESSION['auth_user_id']);
    }
}

if (!function_exists('getCurrentUser')) {
    function getCurrentUser(): ?array {
        if (!isLoggedIn()) return null;
        ensureAuthTable();
        $stmt = db()->prepare("SELECT id, email, full_name, phone, language, is_active FROM auth_users WHERE id = ?");
        $stmt->execute([$_SESSION['auth_user_id']]);
        return $stmt->fetch() ?: null;
    }
}

if (!function_exists('logoutUser')) {
    function logoutUser(): void {
        startAuthSession();
        unset($_SESSION['auth_user_id']);
        session_destroy();
    }
}

if (!function_exists('hashPassword')) {
    function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}

if (!function_exists('verifyPassword')) {
    function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
}

if (!function_exists('registerUser')) {
    function registerUser(string $email, string $password, string $fullName, ?string $phone = null): array {
        startAuthSession();
        ensureAuthTable();

        $email = strtolower(trim(filter_var($email, FILTER_VALIDATE_EMAIL) ?: ''));
        if (!$email) return ['error' => 'Invalid email address'];

        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            return ['error' => 'Password must be 8+ chars with at least one uppercase letter and one number'];
        }

        // Check existing
        $stmt = db()->prepare("SELECT id FROM auth_users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) return ['error' => 'Email already registered'];

        $hash = hashPassword($password);
        $lang = $_SESSION['lang'] ?? 'ne';

        try {
            $stmt = db()->prepare("INSERT INTO auth_users (email, password_hash, full_name, phone, language) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$email, $hash, trim($fullName), $phone ?: null, $lang]);
            return ['success' => true, 'user_id' => db()->lastInsertId()];
        } catch (PDOException $e) {
            return ['error' => 'Registration failed. Please try again.'];
        }
    }
}

if (!function_exists('loginUser')) {
    // आकाशवाणी लगइन फङ्सन सुरक्षा कभर
    function loginUser(string $email, string $password): array {
        startAuthSession();
        ensureAuthTable();

        $email = strtolower(trim(filter_var($email, FILTER_VALIDATE_EMAIL) ?: ''));
        if (!$email) return ['error' => 'Invalid email'];

        $stmt = db()->prepare("SELECT id, password_hash, is_active, full_name FROM auth_users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) return ['error' => 'Invalid email or password'];
        if (!$user['is_active']) return ['error' => 'Account is disabled'];
        if (!verifyPassword($password, $user['password_hash'])) return ['error' => 'Invalid email or password'];

        session_regenerate_id(true);
        $_SESSION['auth_user_id'] = $user['id'];
        return ['success' => true, 'user' => $user];
    }
}

if (!function_exists('validateSession')) {
    function validateSession(): bool {
        return isLoggedIn();
    }
}

// ── Admin session helpers (separate from user auth) ───────────────────────────
if (!function_exists('isAdmin')) {
    function isAdmin(): bool {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return !empty($_SESSION['nh_admin']) || !empty($_SESSION['admin_logged_in']) || !empty($_SESSION['is_admin']);
    }
}

if (!function_exists('requireAdmin')) {
    function requireAdmin(): void {
        if (!isAdmin()) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok'=>false, 'error'=>'Admin session required']);
            exit;
        }
    }
}

if (!function_exists('isCron')) {
    function isCron(): bool {
        $key = defined('CRON_KEY') ? CRON_KEY : '';
        $req = trim($_GET['key'] ?? $_SERVER['HTTP_X_CRON_KEY'] ?? '');
        return $key !== '' && hash_equals($key, $req);
    }
}

if (!function_exists('requireCron')) {
    function requireCron(): void {
        if (!isCron()) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok'=>false, 'error'=>'Valid CRON_KEY required']);
            exit;
        }
    }
}