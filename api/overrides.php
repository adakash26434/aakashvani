<?php
/**
 * api/overrides.php — Admin manual price overrides
 *
 *  GET  → returns currently-active overrides (public, used by dashboard)
 *  POST → save overrides (admin only, requires PIN in session)
 *
 * Storage: /cache/overrides.json (auto-created)
 * Auth   : PIN stored in /cache/admin-pin.txt (set on first run, see admin/prices.php)
 *
 * Override format (only fields with "use:true" are applied; rest fall back to live API):
 * {
 *   "gold":   { "use":true, "hallmarkPerTola":300000, "tajbiPerTola":295000, "silverPerTola":1800 },
 *   "petrol": { "use":true, "petrol":214, "diesel":222, "kerosene":222, "lpg_cylinder":1900, "aviation_fuel":145 },
 *   "forex":  { "use":true, "usdNpr":135.5 },
 *   "note":   "Manually set by admin on YYYY-MM-DD",
 *   "updatedBy":"admin", "updatedAt":"2026-..."
 * }
 */
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

$cacheDir  = __DIR__ . '/../cache';
$file      = $cacheDir . '/overrides.json';
$pinFile   = $cacheDir . '/admin-pin.txt';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);

session_start();

function read_overrides(string $file): array {
  if (!is_file($file)) return [];
  $j = json_decode(@file_get_contents($file) ?: '{}', true);
  return is_array($j) ? $j : [];
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $data = read_overrides($file);
  // strip metadata fields not needed publicly
  echo json_encode(['ok'=>true, 'overrides'=>$data], JSON_UNESCAPED_UNICODE);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Must be authenticated admin (set by admin/prices.php login)
  if (empty($_SESSION['nh_admin'])) {
    http_response_code(401);
    echo json_encode(['ok'=>false, 'error'=>'Unauthorized — login at /admin/prices.php']);
    exit;
  }
  $raw = file_get_contents('php://input');
  $in  = json_decode($raw, true);
  if (!is_array($in)) {
    http_response_code(400);
    echo json_encode(['ok'=>false, 'error'=>'Invalid JSON']);
    exit;
  }
  // sanitize: only allow known sections
  $clean = [];
  foreach (['gold','petrol','forex'] as $sec) {
    if (!empty($in[$sec]) && is_array($in[$sec])) {
      $row = ['use' => !empty($in[$sec]['use'])];
      foreach ($in[$sec] as $k=>$v) {
        if ($k==='use') continue;
        if (is_numeric($v)) $row[$k] = (float)$v;
      }
      $clean[$sec] = $row;
    }
  }
  if (isset($in['note'])) $clean['note'] = substr(strip_tags((string)$in['note']), 0, 200);
  $clean['updatedBy'] = 'admin';
  $clean['updatedAt'] = date('c');
  @file_put_contents($file, json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
  echo json_encode(['ok'=>true, 'saved'=>$clean], JSON_UNESCAPED_UNICODE);
  exit;
}

http_response_code(405);
echo json_encode(['ok'=>false, 'error'=>'Method not allowed']);
