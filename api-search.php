<?php
/**
 * /api-search.php?q=keyword
 * Global autocomplete: menus + success stories + visit places + radio + notices.
 * Returns JSON: [{type,label,url,icon,sub}]
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/includes/menu.php';
@require_once __DIR__ . '/includes/functions.entertainment.php';
@require_once __DIR__ . '/includes/functions.notices.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=30');

$q = trim($_GET['q'] ?? '');
if (mb_strlen($q, 'UTF-8') < 1) { echo json_encode([]); exit; }

$results = [];

// 1) Menu items (fastest, no DB)
foreach (aakashvani_menu_search($q, 6) as $m) {
    $results[] = [
        'type'  => 'menu',
        'icon'  => $m['icon'],
        'label' => $m['label'],
        'sub'   => $m['group'] ?? '',
        'url'   => $m['url'],
    ];
}

// 2) DB lookups (best-effort; ignore if tables missing)
try {
    $like = '%' . $q . '%';

    if (function_exists('db')) {
        $pdo = db();

        // Success stories
        $s = $pdo->prepare("SELECT slug,title FROM success_stories WHERE status='published' AND title LIKE ? ORDER BY featured DESC, published_at DESC LIMIT 5");
        $s->execute([$like]);
        foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $results[] = ['type'=>'story','icon'=>'🏆','label'=>$r['title'],'sub'=>'सफलताका कथा','url'=>'/story.php?slug='.urlencode($r['slug'])];
        }

        // Visit places
        $s = $pdo->prepare("SELECT slug,title,district FROM visit_places WHERE status='published' AND (title LIKE ? OR title_en LIKE ? OR district LIKE ?) ORDER BY featured DESC LIMIT 5");
        $s->execute([$like,$like,$like]);
        foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $results[] = ['type'=>'place','icon'=>'🏔️','label'=>$r['title'],'sub'=>'घुम्ने ठाउँ · '.($r['district'] ?? ''),'url'=>'/visit-place.php?slug='.urlencode($r['slug'])];
        }

        // Radio stations
        $s = $pdo->prepare("SELECT id,name,frequency FROM radio_stations WHERE status='active' AND name LIKE ? LIMIT 4");
        $s->execute([$like]);
        foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $results[] = ['type'=>'radio','icon'=>'📻','label'=>$r['name'],'sub'=>'रेडियो'.($r['frequency'] ? ' · '.$r['frequency'] : ''),'url'=>'/radio.php#st-'.$r['id']];
        }

        // Active notices
        $s = $pdo->prepare("SELECT id,title FROM app_notices WHERE active=1 AND title LIKE ? ORDER BY priority DESC LIMIT 3");
        $s->execute([$like]);
        foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $results[] = ['type'=>'notice','icon'=>'radio','label'=>$r['title'],'sub'=>'सूचना','url'=>'/notices.php#n-'.$r['id']];
        }
    }
} catch (Throwable $e) {
    // silent — search still returns menu matches
}

echo json_encode(array_slice($results, 0, 15), JSON_UNESCAPED_UNICODE);
