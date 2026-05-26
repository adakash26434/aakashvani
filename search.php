<?php
/**
 * /search.php — Global search across news + services + sources
 * Aggregates internal nav items and live news headlines from api/news-rss.php
 */
$pageTitle = 'खोज्नुहोस् — आकाशवाणी';
$pageDesc  = 'समाचार, सरकारी सेवा, र स्रोतहरू एकै ठाउँमा खोज्नुहोस्।';
require_once __DIR__.'/header.php';

$q = trim((string)($_GET['q'] ?? ''));
$qLow = mb_strtolower($q, 'UTF-8');

/* Internal index — pages & services */
$index = [
  ['t'=>'समाचार','d'=>'ताजा नेपाली समाचार सबै स्रोत','u'=>'/news.php','c'=>'news'],
  ['t'=>'नेपाली पात्रो','d'=>'BS मिति, तिथि, चाडपर्व','u'=>'/nepali-patro.php','c'=>'tool'],
  ['t'=>'राशिफल','d'=>'दैनिक राशिफल १२ राशि','u'=>'/rashifal.php','c'=>'tool'],
  ['t'=>'IPO Tracker','d'=>'खुला/आगामी IPO, allotment','u'=>'/ipo-tracker.php','c'=>'finance'],
  ['t'=>'कर क्याल्कुलेटर','d'=>'Income tax, VAT, TDS','u'=>'/tax-calculator.php','c'=>'finance'],
  ['t'=>'Morning Brief','d'=>'AI-curated बिहानको रिपोर्ट','u'=>'/morning-brief.php','c'=>'news'],
  ['t'=>'Emergency','d'=>'आपतकालीन नम्बर र हटलाइन','u'=>'/emergency.php','c'=>'gov'],
  ['t'=>'सरकारी सेवा','d'=>'Nagarik App, Lok Sewa, PAN, Driving License','u'=>'/gov-services.php','c'=>'gov'],
  ['t'=>'Alerts','d'=>'भूकम्प, बाढी, मौसम चेतावनी','u'=>'/alerts.php','c'=>'gov'],
  ['t'=>'Utilities','d'=>'NEA, खानेपानी, इन्टरनेट bills','u'=>'/utilities.php','c'=>'tool'],
  ['t'=>'Downloads','d'=>'सरकारी फारम, PDF','u'=>'/downloads.php','c'=>'gov'],
  ['t'=>'Tools','d'=>'Unit converter, EMI, etc.','u'=>'/tools.php','c'=>'tool'],
  ['t'=>'Sources & Attribution','d'=>'सबै data sources र licenses','u'=>'/sources.php','c'=>'info'],
  ['t'=>'Admin: Manual Prices','d'=>'Gold, fuel, forex override','u'=>'/admin/prices.php','c'=>'admin'],
];

$matches = [];
if ($q !== '') {
  foreach ($index as $row) {
    $hay = mb_strtolower($row['t'].' '.$row['d'], 'UTF-8');
    if (mb_strpos($hay, $qLow) !== false) $matches[] = $row;
  }
}

/* Live news search */
$newsHits = [];
if ($q !== '') {
  $base = (defined('SITE_URL') ? SITE_URL : '');
  $ctx = stream_context_create(['http'=>['timeout'=>5,'header'=>"Accept: application/json\r\n"]]);
  $raw = @file_get_contents($base.'/api/news-rss.php?limit=80', false, $ctx);
  $j = $raw ? json_decode($raw, true) : null;
  if (is_array($j) && !empty($j['items'])) {
    foreach ($j['items'] as $it) {
      $hay = mb_strtolower(($it['title'] ?? '').' '.($it['source'] ?? ''), 'UTF-8');
      if (mb_strpos($hay, $qLow) !== false) {
        $newsHits[] = $it;
        if (count($newsHits) >= 30) break;
      }
    }
  }
}
?>
<style>
.search-wrap{max-width:760px;margin:0 auto;padding:14px}
.search-box{display:flex;gap:8px;background:var(--card,#fff);border:1px solid var(--border,#e5e7eb);border-radius:14px;padding:10px 12px;box-shadow:0 2px 10px rgba(0,0,0,.04)}
.search-box input{flex:1;border:0;outline:0;background:transparent;font-size:15px;font-family:inherit}
.search-box button{background:var(--primary,#0f766e);color:#fff;border:0;border-radius:10px;padding:8px 14px;font-weight:600;cursor:pointer}
.sec-title{font-size:13px;font-weight:700;color:var(--muted-fg,#64748b);text-transform:uppercase;letter-spacing:.05em;margin:18px 4px 8px}
.hit{display:flex;gap:10px;padding:10px 12px;border-radius:12px;background:var(--card,#fff);border:1px solid var(--border,#eef0f3);margin-bottom:8px;text-decoration:none;color:inherit}
.hit:hover{border-color:var(--primary,#0f766e);background:rgba(15,118,110,.04)}
.hit-ico{width:34px;height:34px;border-radius:9px;background:rgba(15,118,110,.1);display:grid;place-items:center;color:var(--primary,#0f766e);flex-shrink:0}
.hit-t{font-weight:600;font-size:14px;line-height:1.35}
.hit-d{font-size:12px;color:var(--muted-fg,#64748b);margin-top:2px}
.hit-meta{font-size:11px;color:var(--muted-fg,#94a3b8);margin-top:3px}
.empty{text-align:center;padding:30px 14px;color:var(--muted-fg,#64748b);font-size:14px}
.chip{display:inline-block;background:rgba(15,118,110,.08);color:var(--primary,#0f766e);padding:2px 8px;border-radius:99px;font-size:10.5px;font-weight:600;margin-right:4px}

<div class="search-wrap">
  <form method="get" class="search-box" role="search">
    <i data-lucide="search" style="width:20px;height:20px;color:#64748b;align-self:center"></i>
    <input type="search" name="q" value="<?=htmlspecialchars($q)?>" placeholder="समाचार, सेवा, स्रोत खोज्नुहोस्…" autofocus>
    <button type="submit">खोज्नुहोस्</button>
  </form>

  <?php if ($q === ''): ?>
    <div class="sec-title">लोकप्रिय खोजी</div>
    <?php foreach (['सुन','पेट्रोल','मौसम','IPO','Lok Sewa','भूकम्प','राशिफल','कर'] as $sug): ?>
        <a class="chip" href="?q=<?=urlencode($sug)?>"><?=$sug?></a>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="sec-title">सेवा र पृष्ठहरू (<?=count($matches)?>)</div>
      <?php if (!$matches): ?>
        <div class="empty">कुनै सेवा मेल खाएन।</div>
      <?php else: foreach ($matches as $m): ?>
        <a class="hit" href="<?=htmlspecialchars($m['u'])?>">
          <div class="hit-ico"><i data-lucide="layout-grid"></i></div>
        <div>
          <div class="hit-t"><?=htmlspecialchars($m['t'])?></div>
          <div class="hit-d"><?=htmlspecialchars($m['d'])?></div>
          <div class="hit-meta"><span class="chip"><?=$m['c']?></span><?=htmlspecialchars($m['u'])?></div>
        </div>
      </a>
    <?php endforeach; endif; ?>

    <div class="sec-title">समाचार (<?=count($newsHits)?>)</div>
    <?php if (!$newsHits): ?>
      <div class="empty">कुनै समाचार मेल खाएन। फेरि अर्को शब्दले प्रयास गर्नुहोस्।</div>
    <?php else: foreach ($newsHits as $n): ?>
      <a class="hit" href="/news-detail.php?u=<?=urlencode($n['link'] ?? '#')?>&s=<?=urlencode($n['source'] ?? '')?>">
        <div class="hit-ico" style="background:rgba(239,68,68,.1);color:#ef4444"><i data-lucide="newspaper" style="width:18px;height:18px"></i></div>
        <div style="flex:1;min-width:0">
          <div class="hit-t"><?=htmlspecialchars($n['title'] ?? '')?></div>
          <div class="hit-meta"><span class="chip"><?=htmlspecialchars($n['source'] ?? '')?></span><?=htmlspecialchars(substr($n['pubDate'] ?? '', 0, 16))?></div>
        </div>
      </a>
    <?php endforeach; endif; ?>
  <?php endif; ?>
</div>

<?php require_once __DIR__.'/footer.php'; ?>
