<?php
/**
 * Alert Detail Page - Shows detailed view of BIPAD alerts, Earthquakes, etc.
 * Similar to news-detail.php with source attribution
 */
@ini_set('default_socket_timeout', 8);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Get alert ID and source from URL
$alertId = isset($_GET['id']) ? trim($_GET['id']) : '';
$source = isset($_GET['src']) ? trim($_GET['src']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';

// Decode the alert ID if it's base64 encoded
$alertData = null;
if ($alertId) {
    $decoded = @base64_decode($alertId, true);
    if ($decoded) {
        $alertData = @json_decode($decoded, true);
    }
}

// Fallback to fetching from API if no data passed
if (!$alertData) {
    // Fetch from alerts API
    $apiUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/api/alerts.php';
    $ctx = stream_context_create([
        'http' => ['timeout' => 5, 'header' => "User-Agent: Aakashvani/1.0\r\n"],
    ]);
    $apiResponse = @file_get_contents($apiUrl, false, $ctx);
    if ($apiResponse) {
        $apiData = json_decode($apiResponse, true);
        if ($apiData && $apiData['ok'] && !empty($apiData['items'])) {
            // Find matching alert by constructing ID from data
            foreach ($apiData['items'] as $item) {
                $itemId = md5($item['title'] . $item['source'] . $item['time']);
                if ($itemId === $alertId || 
                    ($source && stripos($item['source'], $source) !== false)) {
                    $alertData = $item;
                    break;
                }
            }
        }
    }
}

// If still no data, show error
if (!$alertData) {
    http_response_code(404);
    $pageTitle = 'चेतावनी फेला परेन · आकाशवाणी';
    require_once __DIR__ . '/header.php';
    echo '<div class="art-wrap">';
    echo '<a href="/" class="art-back ne"><i data-lucide="arrow-left" class="w-4 h-4"></i> होममा फर्किनुहोस्</a>';
    echo '<div style="padding:40px 20px;text-align:center;">';
    echo '<h2 class="ne">चेतावनी फेला परेन</h2>';
    echo '<p class="ne" style="color:#64748b;margin-top:16px;">यो चेतावनी उपलब्ध छैन वा समय सकिएको हुन सक्छ।</p>';
    echo '</div>';
    echo '</div>';
    require_once __DIR__ . '/footer.php';
    exit;
}

// Extract alert data
$title = $alertData['title'] ?? 'सरकारी सूचना';
$desc = $alertData['description'] ?? $alertData['desc'] ?? '';
$time = $alertData['time'] ?? $alertData['date'] ?? '';
$sourceName = $alertData['source'] ?? 'BIPAD';
$sourceUrl = $alertData['url'] ?? $alertData['link'] ?? '#';
$severity = $alertData['severity'] ?? $alertData['level'] ?? 'info';
$category = $alertData['category'] ?? $alertData['type'] ?? 'Alert';

// Source metadata mapping
$sourceMeta = [
    'BIPAD' => ['name' => 'BIPAD', 'full' => 'BIPAD - राष्ट्रिय विपद् जोखिम न्यूनीकरण तथा व्यवस्थापन प्राधिकरण', 'color' => '#dc2626', 'home' => 'https://bipad.gov.np'],
    'USGS' => ['name' => 'USGS', 'full' => 'USGS Earthquake Hazards Program', 'color' => '#ea580c', 'home' => 'https://earthquake.usgs.gov'],
    'NDRRMA' => ['name' => 'NDRRMA', 'full' => 'राष्ट्रिय विपद् जोखिम न्यूनीकरण तथा व्यवस्थापन प्राधिकरण', 'color' => '#dc2626', 'home' => 'https://ndrrma.gov.np'],
    'DHM' => ['name' => 'DHM', 'full' => 'Department of Hydrology and Meteorology', 'color' => '#0891b2', 'home' => 'https://hydrology.gov.np'],
];

// Determine source info
$srcKey = 'BIPAD';
foreach ($sourceMeta as $key => $meta) {
    if (stripos($sourceName, $key) !== false) {
        $srcKey = $key;
        break;
    }
}
$srcInfo = $sourceMeta[$srcKey] ?? ['name' => $sourceName, 'full' => $sourceName, 'color' => '#64748b', 'home' => '#'];

// Format time
$timeStr = '';
if ($time) {
    $ts = is_numeric($time) ? $time : strtotime($time);
    if ($ts) {
        $timeStr = date('Y-m-d g:i A', $ts);
    }
}

// Severity colors
$severityColors = [
    'high' => '#dc2626',
    'severe' => '#dc2626',
    'critical' => '#991b1b',
    'warning' => '#ea580c',
    'medium' => '#f59e0b',
    'low' => '#16a34a',
    'info' => '#0891b2',
];
$severityColor = $severityColors[strtolower($severity)] ?? '#64748b';

// Nepali severity labels
$severityLabels = [
    'high' => 'उच्च जोखिम',
    'severe' => 'गम्भीर',
    'critical' => 'अत्यन्त गम्भीर',
    'warning' => 'चेतावनी',
    'medium' => 'मध्यम जोखिम',
    'low' => 'सामान्य',
    'info' => 'सूचना',
];
$severityLabel = $severityLabels[strtolower($severity)] ?? 'सूचना';

$pageTitle = $title . ' · आकाशवाणी';
$pageDesc = $desc ? mb_substr($desc, 0, 150) . '...' : $title;
$pageCanonical = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $_SERVER['REQUEST_URI'];

require_once __DIR__ . '/header.php';
?>

<div class="art-wrap">
  <a href="/" class="art-back ne"><i data-lucide="arrow-left" class="w-4 h-4"></i> होममा फर्किनुहोस्</a>

  <!-- ══ SOURCE ATTRIBUTION ══════════════ -->
  <div class="art-srcbar" style="--src-color:<?= htmlspecialchars($srcInfo['color']) ?>">
    <div class="src-left" style="display:flex;align-items:center;gap:12px;flex:1;">
      <div class="logo" style="background:<?= htmlspecialchars($severityColor) ?>;">
        <i data-lucide="bell" class="w-5 h-5"></i>
      </div>
      <div class="meta">
        <div class="name ne"><?= htmlspecialchars($srcInfo['full']) ?></div>
        <div class="lic ne">© <?= date('Y') ?> <?= htmlspecialchars($srcInfo['name']) ?> — आधिकारिक स्रोत</div>
      </div>
    </div>
    <a href="<?= htmlspecialchars($sourceUrl) ?>" target="_blank" rel="noopener" class="read-source-btn ne">
      <i data-lucide="external-link" class="w-4 h-4"></i>
      आधिकारिक स्रोतमा हेर्नुहोस्
    </a>
  </div>

  <!-- ══ ALERT HEADER ══════════════ -->
  <div style="margin-bottom:20px;padding:16px;background:linear-gradient(135deg, <?= $severityColor ?>15, <?= $severityColor ?>08);border-radius:12px;border-left:4px solid <?= $severityColor ?>;">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
      <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;background:<?= $severityColor ?>;color:#fff;border-radius:20px;font-size:12px;font-weight:600;">
        <i data-lucide="alert-triangle" class="w-3 h-3"></i>
        <?= htmlspecialchars($severityLabel) ?>
      </span>
      <span style="font-size:12px;color:#64748b;"><?= htmlspecialchars($category) ?></span>
    </div>
    <h1 class="art-title ne" style="margin-bottom:0;"><?= htmlspecialchars($title) ?></h1>
    <?php if ($timeStr): ?>
    <div class="art-meta ne" style="margin-top:8px;margin-bottom:0;">
      <i data-lucide="clock" class="w-3.5 h-3.5"></i>
      <?= htmlspecialchars($timeStr) ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- ══ ALERT CONTENT ══════════════ -->
  <div class="art-ai-wrap">
    <div class="art-ai-head">
      <span class="label">
        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
        <?= htmlspecialchars($srcInfo['name']) ?> बाट प्राप्त सूचना
      </span>
      <span class="src-badge" style="background:<?= htmlspecialchars($srcInfo['color']) ?>">
        <i data-lucide="shield" class="w-3 h-3"></i>
        <?= htmlspecialchars($srcInfo['name']) ?>
      </span>
    </div>
    <div class="art-ai-body ne">
      <?php if ($desc): ?>
        <div style="font-size:16px;line-height:1.8;color:#1e293b;">
          <?= nl2br(htmlspecialchars($desc)) ?>
        </div>
      <?php else: ?>
        <p style="color:#64748b;">थप विवरण उपलब्ध छैन। आधिकारिक स्रोतमा जानुहोस्।</p>
      <?php endif; ?>
    </div>
    <div class="art-ai-footer">
      <span class="src-note ne">
        <i data-lucide="info" class="w-3.5 h-3.5"></i>
        यो सामग्री <?= htmlspecialchars($srcInfo['name']) ?> स्रोतबाट प्राप्त सूचनामा आधारित छ। समयसीमा र जोखिमको स्तर आधिकारिक स्रोतमा पुष्टि गर्नुहोस्।
      </span>
    </div>
  </div>

  <!-- ══ LEGAL NOTICE ══════════════ -->
  <div class="art-legal ne">
    <strong>📜 कानूनी सूचना:</strong>
    यो चेतावनी <?= htmlspecialchars($srcInfo['name']) ?> द्वारा जारी गरिएको हो। यो सूचना आकाशवाणीमा पढ्न मिल्ने गरी तयार पारिएको हो।
    तत्काल कारबाही वा थप जानकारीको लागि 
    <a href="<?= htmlspecialchars($sourceUrl) ?>" target="_blank" rel="noopener" style="margin-left:4px;">आधिकारिक स्रोतमा सम्पर्क गर्नुहोस्</a>।
  </div>

  <!-- ══ SHARE BUTTONS ══════════════ -->
  <div style="display:flex;gap:8px;margin-top:20px;">
    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($pageCanonical) ?>" 
       target="_blank" rel="noopener"
       style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:#1877f2;color:#fff;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
      <i data-lucide="facebook" class="w-4 h-4"></i>
      Share
    </a>
    <a href="https://twitter.com/intent/tweet?text=<?= urlencode($title) ?>&url=<?= urlencode($pageCanonical) ?>" 
       target="_blank" rel="noopener"
       style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:#1da1f2;color:#fff;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
      <i data-lucide="twitter" class="w-4 h-4"></i>
      Tweet
    </a>
    <a href="https://wa.me/?text=<?= urlencode($title . ' - ' . $pageCanonical) ?>" 
       target="_blank" rel="noopener"
       style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:#25d366;color:#fff;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
      <i data-lucide="message-circle" class="w-4 h-4"></i>
      WhatsApp
    </a>
  </div>
</div>

<script>
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
