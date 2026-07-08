<?php
/**
 * Save a Web Push subscription. Called from app.js after user grants permission.
 * Stores: endpoint, p256dh, auth, optional user_id, lang.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/csrf.php';
// Rate-limit: 10 subscribes/min/IP
$rlKey = 'push:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
if (!checkRateLimit($rlKey, 10, 60)) {
    http_response_code(429); header('Content-Type: application/json');
    exit(json_encode(['ok'=>false, 'error'=>'Rate limit exceeded']));
}
csrfRequire();
header('Content-Type: application/json; charset=utf-8');
sendSecurityHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error'=>'POST only']); return;
}

$body = json_decode(file_get_contents('php://input'), true);
$sub  = $body['subscription'] ?? null;
$userId = $body['user_id'] ?? null;
$lang   = ($body['lang'] ?? 'ne') === 'en' ? 'en' : 'ne';

if (!$sub || empty($sub['endpoint']) || empty($sub['keys']['p256dh']) || empty($sub['keys']['auth'])) {
    http_response_code(400); echo json_encode(['error'=>'invalid subscription']); return;
}

try {
    $isMysql = defined('DB_DRIVER') && DB_DRIVER === 'mysql';
    if (!$isMysql) {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } else {
        $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
        if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
            $opts[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci";
        }
        $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET, DB_USER, DB_PASS, $opts);
        try { $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"); } catch (\Throwable $e) {}
    }
    $ai      = $isMysql ? 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $charset = $isMysql ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : '';
    $endpointDef = $isMysql ? 'endpoint VARCHAR(500) UNIQUE' : 'endpoint TEXT UNIQUE';
    $pdo->exec("CREATE TABLE IF NOT EXISTS push_subscriptions (
        id $ai,
        $endpointDef, p256dh TEXT, auth TEXT,
        user_id VARCHAR(200), lang VARCHAR(10), created_at VARCHAR(50)
    )$charset");
    // upsert by endpoint
    $sql = DB_DRIVER === 'sqlite'
        ? "INSERT OR REPLACE INTO push_subscriptions(endpoint,p256dh,auth,user_id,lang,created_at) VALUES(?,?,?,?,?,?)"
        : "REPLACE INTO push_subscriptions(endpoint,p256dh,auth,user_id,lang,created_at) VALUES(?,?,?,?,?,?)";
    $st = $pdo->prepare($sql);
    $st->execute([$sub['endpoint'], $sub['keys']['p256dh'], $sub['keys']['auth'], $userId, $lang, date('c')]);
    echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
    http_response_code(500); echo json_encode(['error'=>'db error', 'detail'=>$e->getMessage()]);
}
