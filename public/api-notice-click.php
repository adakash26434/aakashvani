<?php
/** /api/notice-click.php — track CTA clicks (lightweight) */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/functions.notices.php';
$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) trackNoticeClick($id);
header('Content-Type: application/json');
echo json_encode(['ok' => true]);
