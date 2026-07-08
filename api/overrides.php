<?php
/**
 * api/overrides.php — Admin manual price overrides
 *
 *  GET  → returns currently-active overrides (public, used by dashboard)
 *  POST → save overrides (admin only, requires PIN in session or CRON_KEY)
 *
 * Storage: /data/cache/overrides.json (auto-created)
 * Auth   : Admin session OR CRON_KEY
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
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json; charset=UTF-8');
sendSecurityHeaders();
// GET is public (dashboard reads overrides), POST requires auth
$cronKey = defined('CRON_KEY') ? CRON_KEY : '';
$reqKey  = trim($_GET['key'] ?? $_SERVER['HTTP_X_CRON_KEY'] ?? '');
$hasKey  = $cronKey && hash_equals($cronKey, $reqKey);

session_start();

$cacheDir  = __DIR__ . '/../data/cache';
$file      = $cacheDir . '/overrides.json';
$pinFile   = $cacheDir . '/admin-pin.txt';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);

function read_overrides(string $file): array {
  if (!is_file($file)) return [];
  $j = json_decode(@file_get_contents($file) ?: '{}', true);
  return is_array($j) ? $j : [];
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $data = read_overrides($file);
  echo json_encode(['ok'=>true, 'overrides'=>$data], JSON_UNESCAPED_UNICODE);
  return;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $hasAdmin = !empty($_SESSION['nh_admin']) || !empty($_SESSION['admin_logged_in']);
  if (!$hasKey && !$hasAdmin) {
    http_response_code(401);
    echo json_encode(['ok'=>false, 'error'=>'Unauthorized — admin session or CRON_KEY required']);
    return;
  }
  $raw = file_get_contents('php://input');
  $in  = json_decode($raw, true);
  if (!is_array($in)) {
    http_response_code(400);
    echo json_encode(['ok'=>false, 'error'=>'Invalid JSON']);
    return;
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
  return;
}

http_response_code(405);
echo json_encode(['ok'=>false, 'error'=>'Method not allowed']);
return;
