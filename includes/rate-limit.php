<?php
/**
 * आकाशवाणी — Simple IP-based Rate Limiter
 * Include in any API file: require_once __DIR__ . '/../includes/rate-limit.php';
 * Then call: rateLimit('market-data', 30, 60); // max 30 requests per 60 seconds
 */

function rateLimit(string $key, int $maxRequests = 30, int $windowSeconds = 60): void {
    $ip  = $_SERVER['HTTP_CF_CONNECTING_IP']
        ?? $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['REMOTE_ADDR']
        ?? 'unknown';
    $ip = preg_replace('/[^0-9a-fA-F.:,]/', '', $ip);
    $ip = explode(',', $ip)[0]; // take first IP if forwarded list

    $cacheDir = defined('CACHE_DIR') ? CACHE_DIR : (__DIR__ . '/../data/cache/');
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0755, true);
    }

    $hash     = substr(md5($ip . $key), 0, 16);
    $file     = $cacheDir . 'rl_' . $hash . '.json';
    $now      = time();
    $windowStart = $now - $windowSeconds;

    // Read existing timestamps
    $data = [];
    if (file_exists($file)) {
        $raw = @file_get_contents($file);
        if ($raw) {
            $data = json_decode($raw, true) ?? [];
        }
    }

    // Prune old timestamps outside the window
    $data = array_filter($data, fn($ts) => $ts > $windowStart);
    $data = array_values($data);

    if (count($data) >= $maxRequests) {
        $retryAfter = (min($data) + $windowSeconds) - $now;
        header('Content-Type: application/json; charset=utf-8');
        header('Retry-After: ' . max(1, $retryAfter));
        http_response_code(429);
        echo json_encode([
            'ok'    => false,
            'error' => 'Too many requests. Please slow down.',
            'retry_after' => max(1, $retryAfter),
        ]);
        exit;
    }

    // Record this request
    $data[] = $now;
    @file_put_contents($file, json_encode($data), LOCK_EX);
}
