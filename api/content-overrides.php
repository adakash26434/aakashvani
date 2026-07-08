<?php
/**
 * api/content-overrides.php — Admin manual content fallback
 *
 *  GET                → returns all current overrides (public read, restricted CORS)
 *  GET ?key=traffic   → returns one block
 *  POST  (admin)      → save one block {key, items, note}
 *
 * Sections supported:
 *   traffic        — Traffic notices (closures, jams, restrictions)
 *   loadshedding   — Power cut schedule
 *   water          — Water supply schedule
 *   loksewa        — Lok Sewa notices (admin can pin)
 *   transport      — Bus/flight schedule notes
 *   alert          — General urgent broadcast
 *
 * Storage: /data/cache/admin/content-overrides.json
 * Auth   : Admin session OR CRON_KEY
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/csrf.php';
csrfRequire();

header('Content-Type: application/json; charset=UTF-8');

// ── CORS: Restrict to same-origin ───────────────────────────────────────────────
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = [
    'https://tankaadhikari.com.np',
    'https://www.tankaadhikari.com.np',
    'http://localhost',
    'http://localhost:8080',
    'http://127.0.0.1',
];
if (in_array($origin, $allowed, true)) {
    header("Access-Control-Allow-Origin: $origin");
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); return; }

$cacheDir = __DIR__ . '/../data/cache/admin';
$file     = $cacheDir . '/content-overrides.json';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
session_start();

// ── Auth: Admin session OR CRON_KEY ─────────────────────────────────────────────
$cronKey = defined('CRON_KEY') ? CRON_KEY : '';
$reqKey  = trim($_GET['key'] ?? $_SERVER['HTTP_X_CRON_KEY'] ?? '');
$hasKey   = $cronKey && $reqKey === $cronKey;
$hasAdmin = !empty($_SESSION['nh_admin']) || !empty($_SESSION['admin_logged_in']);

$ALLOWED = ['traffic','loadshedding','water','loksewa','transport','alert'];

function read_all(string $f): array {
  if (!is_file($f)) return [];
  $j = json_decode((string)@file_get_contents($f), true);
  return is_array($j) ? $j : [];
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
  $all = read_all($file);
  if (!empty($_GET['key'])) {
    $k = (string)$_GET['key'];
    echo json_encode(['ok'=>true, 'key'=>$k, 'data'=>$all[$k] ?? null, 'source'=>'आकाशवाणी Admin'], JSON_UNESCAPED_UNICODE);
  } else {
    echo json_encode(['ok'=>true, 'overrides'=>$all, 'source'=>'आकाशवाणी Admin'], JSON_UNESCAPED_UNICODE);
  }
  return;
}

if ($method === 'POST') {
  if (!$hasKey && !$hasAdmin) {
    http_response_code(401);
    echo json_encode(['ok'=>false, 'error'=>'Unauthorized — admin session or CRON_KEY required']);
    return;
  }
  $in = json_decode((string)file_get_contents('php://input'), true);
  if (!is_array($in) || empty($in['key']) || !in_array($in['key'], $ALLOWED, true)) {
    http_response_code(400);
    echo json_encode(['ok'=>false, 'error'=>'Invalid key. Allowed: '.implode(',', $ALLOWED)]);
    return;
  }
  $key   = $in['key'];
  $items = is_array($in['items'] ?? null) ? $in['items'] : [];
  $note  = isset($in['note']) ? substr(strip_tags((string)$in['note']), 0, 300) : '';
  $src   = isset($in['source']) ? substr(strip_tags((string)$in['source']), 0, 120) : 'Admin';
  $url   = isset($in['source_url']) ? filter_var($in['source_url'], FILTER_VALIDATE_URL) : '';

  // sanitize items: each must be assoc array with text fields
  $clean = [];
  foreach ($items as $it) {
    if (!is_array($it)) continue;
    $row = [];
    foreach ($it as $k=>$v) {
      $k = preg_replace('/[^a-z0-9_]/i','', (string)$k);
      if ($k==='') continue;
      $row[$k] = is_scalar($v) ? mb_substr(strip_tags((string)$v), 0, 500) : '';
    }
    if ($row) $clean[] = $row;
  }

  $all = read_all($file);
  $all[$key] = [
    'items'     => $clean,
    'note'      => $note,
    'source'    => $src ?: 'Admin',
    'source_url'=> $url ?: '',
    'updatedAt' => date('c'),
    'enabled'   => !empty($in['enabled']),
  ];
  @file_put_contents($file, json_encode($all, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
  echo json_encode(['ok'=>true, 'saved'=>$all[$key]], JSON_UNESCAPED_UNICODE);
  return;
}

http_response_code(405);
echo json_encode(['ok'=>false, 'error'=>'Method not allowed']);
return;
