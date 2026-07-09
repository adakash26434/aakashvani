<?php
/**
 * /api/newsletter.php — Newsletter subscription endpoint
 * 
 * POST: { email: "user@example.com" }
 * Returns: { ok: true, message: "Subscribed!" } or { ok: false, error: "..." }
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json; charset=utf-8');
sendSecurityHeaders();

// Rate limit: 5 subscriptions per minute per IP
$rlKey = 'newsletter:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
if (!checkRateLimit($rlKey, 5, 60)) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Too many requests. Please wait a minute.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
$email = trim($input['email'] ?? '');

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid email address']);
    exit;
}

try {
    $pdo = db();
    if (!$pdo) {
        // If no DB, just return success to not expose internal issues
        echo json_encode(['ok' => true, 'message' => 'Subscribed!']);
        exit;
    }
    
    // Create newsletter table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS newsletter_subscribers (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        subscribed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        unsubscribed_at DATETIME DEFAULT NULL,
        INDEX idx_email (email),
        INDEX idx_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Check if already subscribed
    $stmt = $pdo->prepare("SELECT id, unsubscribed_at FROM newsletter_subscribers WHERE email = ?");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        if ($existing['unsubscribed_at']) {
            // Re-subscribe
            $stmt = $pdo->prepare("UPDATE newsletter_subscribers SET is_active = 1, unsubscribed_at = NULL, subscribed_at = CURRENT_TIMESTAMP WHERE email = ?");
            $stmt->execute([$email]);
            echo json_encode(['ok' => true, 'message' => 'Welcome back! You\'re subscribed again.']);
        } else {
            echo json_encode(['ok' => true, 'message' => 'You\'re already subscribed!']);
        }
    } else {
        // New subscription
        $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
        $stmt->execute([$email]);
        echo json_encode(['ok' => true, 'message' => 'Subscribed! Welcome to आकाशवाणी newsletter.']);
    }
    
} catch (Throwable $e) {
    error_log('[newsletter] Error: ' . $e->getMessage());
    // Don't expose internal errors to client
    echo json_encode(['ok' => false, 'error' => 'Subscription failed. Please try again.']);
}
