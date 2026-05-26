<?php
/**
 * api/directory.php v2 — DB-backed Nepal Contact Directory
 * ?q=keyword  ?cat=emergency|government|bank|hospital|education|telecom|utility|airport|media
 * ?district=Kathmandu  ?limit=80
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=300');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../functions.php';

$q        = trim($_GET['q']        ?? '');
$cat      = strtolower(trim($_GET['cat']      ?? ''));
$district = trim($_GET['district'] ?? '');
$limit    = max(1, min(200, (int)($_GET['limit'] ?? 80)));

$items    = searchDirectory($q, $cat, $district, $limit);
$catCounts= getDirectoryCatCounts();
$total    = (int) db()->query('SELECT COUNT(*) FROM directory')->fetchColumn();

echo json_encode([
    'ok'    => true,
    'q'     => $q,
    'cat'   => $cat,
    'count' => count($items),
    'total' => $total,
    'cats'  => $catCounts,
    'items' => $items,
], JSON_UNESCAPED_UNICODE);
