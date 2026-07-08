<?php
/**
 * /api/user-data.php — Per-user JSON preferences blob.
 *  GET  → { ok:true, data:{...} }   (returns {} if guest or none)
 *  POST → { key: "...", value: any } merges into the user's blob.
 *
 * This is how a logged-in user's saved data (favourites, theme, last viewed,
 * rashifal sign, IPO watchlist, etc.) persists across devices & logins.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
csrfRequire();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
sendSecurityHeaders();

function ensureUserDataTable(): void {
    static $done = false; if ($done) return;
    db()->exec("CREATE TABLE IF NOT EXISTS user_data (
        user_id    INTEGER PRIMARY KEY,
        data_json  LONGTEXT NOT NULL DEFAULT '{}',
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
    $done = true;
}

startAuthSession();
$user = getCurrentUser();
if (!$user) { echo json_encode(['ok' => true, 'guest' => true, 'data' => new stdClass()]); return; }

ensureUserDataTable();
$uid = (int)$user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = db()->prepare('SELECT data_json FROM user_data WHERE user_id = ?');
    $stmt->execute([$uid]);
    $row = $stmt->fetch();
    $data = $row ? (json_decode($row['data_json'], true) ?: []) : [];
    echo json_encode(['ok' => true, 'data' => (object)$data]);
    return;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $in  = json_decode($raw, true);
    if (!is_array($in) || !isset($in['key'])) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'bad request']); return; }

    $key = substr(preg_replace('/[^a-zA-Z0-9_\-\.]/', '', (string)$in['key']), 0, 80);
    if ($key === '') { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'bad key']); return; }

    $stmt = db()->prepare('SELECT data_json FROM user_data WHERE user_id = ?');
    $stmt->execute([$uid]);
    $row = $stmt->fetch();
    $data = $row ? (json_decode($row['data_json'], true) ?: []) : [];

    if (array_key_exists('delete', $in) && $in['delete']) {
        unset($data[$key]);
    } else {
        $data[$key] = $in['value'] ?? null;
    }

    $json = json_encode($data, JSON_UNESCAPED_UNICODE);
    if (strlen($json) > 200000) { http_response_code(413); echo json_encode(['ok'=>false,'error'=>'too large']); return; }

    if ($row) {
        $u = db()->prepare('UPDATE user_data SET data_json = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?');
        $u->execute([$json, $uid]);
    } else {
        $i = db()->prepare('INSERT INTO user_data (user_id, data_json) VALUES (?, ?)');
        $i->execute([$uid, $json]);
    }
    echo json_encode(['ok' => true]);
    return;
}

http_response_code(405);
echo json_encode(['ok'=>false,'error'=>'method not allowed']);
