<?php
/**
 * आकाशवाणी — Admin SEO Manager
 * Google first ranking ko lagi sabai SEO settings yahan bata manage garnus.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/seo-helper.php';
requireAdmin();

$msg   = '';
$error = '';
$tab   = $_GET['tab'] ?? 'global';

// ── POST handlers ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Save global SEO settings
    if ($action === 'save_global') {
        saveSeoSettings([
            'meta_title_template'  => trim($_POST['meta_title_template'] ?? ''),
            'meta_description'     => trim($_POST['meta_description'] ?? ''),
            'meta_keywords'        => trim($_POST['meta_keywords'] ?? ''),
            'og_image_default'     => trim($_POST['og_image_default'] ?? ''),
            'twitter_handle'       => ltrim(trim($_POST['twitter_handle'] ?? ''), '@'),
            'facebook_app_id'      => trim($_POST['facebook_app_id'] ?? ''),
        ]);
        $msg = '✅ Global SEO settings saved!';
        $tab = 'global';
    }

    // Save Google / Analytics settings
    if ($action === 'save_google') {
        saveSeoSettings([
            'ga4_id'              => trim($_POST['ga4_id'] ?? ''),
            'gsc_meta'            => trim($_POST['gsc_meta'] ?? ''),
            'bing_meta'           => trim($_POST['bing_meta'] ?? ''),
            'gtm_id'              => trim($_POST['gtm_id'] ?? ''),
        ]);
        $msg = '✅ Google / Analytics settings saved!';
        $tab = 'google';
    }

    // Save Schema.org settings
    if ($action === 'save_schema') {
        saveSeoSettings([
            'schema_enabled'      => isset($_POST['schema_enabled']) ? '1' : '0',
            'schema_org_name'     => trim($_POST['schema_org_name'] ?? ''),
            'schema_org_url'      => trim($_POST['schema_org_url'] ?? ''),
            'schema_org_logo'     => trim($_POST['schema_org_logo'] ?? ''),
            'schema_org_desc'     => trim($_POST['schema_org_desc'] ?? ''),
            'schema_org_fb'       => trim($_POST['schema_org_fb'] ?? ''),
            'schema_org_twitter'  => trim($_POST['schema_org_twitter'] ?? ''),
            'schema_search'       => isset($_POST['schema_search']) ? '1' : '0',
        ]);
        $msg = '✅ Schema.org settings saved!';
        $tab = 'schema';
    }

    // Save per-page SEO
    if ($action === 'save_page') {
        $path = trim($_POST['page_path'] ?? '');
        if ($path) {
            savePageSeo($path, [
                'meta_title'       => trim($_POST['meta_title'] ?? ''),
                'meta_description' => trim($_POST['meta_description'] ?? ''),
                'meta_keywords'    => trim($_POST['meta_keywords'] ?? ''),
                'og_image'         => trim($_POST['og_image'] ?? ''),
                'is_noindex'       => isset($_POST['is_noindex']) ? 1 : 0,
            ]);
            $msg = "✅ SEO saved for: $path";
        }
        $tab = 'pages';
    }

    // Delete per-page SEO
    if ($action === 'delete_page') {
        $path = trim($_POST['page_path'] ?? '');
        if ($path) {
            try {
                ensureSeoTables();
                db()->prepare("DELETE FROM seo_pages WHERE page_path=?")->execute([$path]);
                $msg = "🗑 Removed SEO override for: $path";
            } catch (Exception $e) { $error = $e->getMessage(); }
        }
        $tab = 'pages';
    }

    // Save robots.txt
    if ($action === 'save_robots') {
        $content = $_POST['robots_content'] ?? '';
        $content = str_replace("\r\n", "\n", $content);
        $robotsPath = __DIR__ . '/../robots.txt';
        if (file_put_contents($robotsPath, $content) !== false) {
            $msg = '✅ robots.txt saved!';
        } else {
            $error = 'Could not write robots.txt — check file permissions (chmod 644).';
        }
        $tab = 'robots';
    }

    // Ping Google & Bing sitemap
    if ($action === 'ping_sitemap') {
        $sitemapUrl = rtrim(defined('SITE_URL') ? SITE_URL : '', '/') . '/sitemap.xml';
        $results = [];
        $targets = [
            'Google' => 'https://www.google.com/ping?sitemap=' . urlencode($sitemapUrl),
            'Bing'   => 'https://www.bing.com/ping?sitemap=' . urlencode($sitemapUrl),
        ];
        foreach ($targets as $name => $url) {
            $ctx = stream_context_create(['http'=>['timeout'=>8,'ignore_errors'=>true]]);
            $r   = @file_get_contents($url, false, $ctx);
            $hdr = $http_response_header[0] ?? '';
            $results[] = "$name: " . (strpos($hdr,'200') !== false ? '✅ OK' : '⚠️ '.$hdr);
        }
        $msg = 'Ping results — ' . implode(' | ', $results);
        $tab = 'sitemap';
    }
}

// ── Load current settings ─────────────────────────────────────────────────────
$robotsTxt  = @file_get_contents(__DIR__ . '/../robots.txt') ?: '';
$allPages   = getAllPageSeo();

// Pages for the per-page SEO tab
$knownPages = [
    '/'                  => 'Home (मुख्य पृष्ठ)',
    '/news.php'          => 'News (समाचार)',
    '/nepali-patro.php'  => 'Nepali Patro (पात्रो)',
    '/rashifal.php'      => 'Rashifal (राशिफल)',
    '/ipo-tracker.php'   => 'IPO Tracker',
    '/utilities.php'     => 'Utilities (बजार)',
    '/tools.php'         => 'Tools (टूलहरू)',
    '/gov-services.php'  => 'Gov Services (सरकारी सेवा)',
    '/cricket.php'       => 'Cricket (क्रिकेट)',
    '/nokari.php'        => 'Nokari (नोकरी)',
    '/loksewa.php'       => 'Loksewa (लोकसेवा)',
    '/morning-brief.php' => 'Morning Brief (बिहानी ब्रिफ)',
    '/alerts.php'        => 'Alerts (अलर्टहरू)',
    '/emergency.php'     => 'Emergency',
    '/about.php'         => 'About',
    '/contact.php'       => 'Contact',
];

// Index existing overrides by path
$pageIndex = [];
foreach ($allPages as $p) $pageIndex[$p['page_path']] = $p;

// Edit mode
$editPage = null;
if ($tab === 'pages' && !empty($_GET['edit'])) {
    $ep = '/' . ltrim($_GET['edit'], '/');
    $editPage = $pageIndex[$ep] ?? ['page_path' => $ep];
}

$siteUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';

// SEO Health checks
function seoHealthChecks(): array {
    $checks = [];
    $ga4 = getSeoSetting('ga4_id');
    $checks[] = ['label'=>'Google Analytics 4 (GA4)', 'ok'=>$ga4 !== '', 'detail'=>$ga4 ? "ID: $ga4" : 'Add GA4 Measurement ID'];
    $gsc = getSeoSetting('gsc_meta');
    $checks[] = ['label'=>'Google Search Console Verification', 'ok'=>$gsc !== '', 'detail'=>$gsc ? 'Meta tag set' : 'Add GSC verification meta'];
    $schema = getSeoSetting('schema_enabled');
    $checks[] = ['label'=>'Schema.org Markup (JSON-LD)', 'ok'=>$schema === '1', 'detail'=>$schema === '1' ? 'Enabled' : 'Enable Organization schema'];
    $desc = getSeoSetting('meta_description');
    $checks[] = ['label'=>'Global Meta Description', 'ok'=>$desc !== '', 'detail'=>$desc ? mb_substr($desc,0,60).'…' : 'Add site-wide description'];
    $kw = getSeoSetting('meta_keywords');
    $checks[] = ['label'=>'Meta Keywords', 'ok'=>$kw !== '', 'detail'=>$kw ? 'Set' : 'Add target keywords'];
    $ogImg = getSeoSetting('og_image_default');
    $checks[] = ['label'=>'OG Image (Social Share Image)', 'ok'=>$ogImg !== '', 'detail'=>$ogImg ? 'Set' : 'Upload /assets/images/og-image.jpg'];
    $tw = getSeoSetting('twitter_handle');
    $checks[] = ['label'=>'Twitter / X Handle', 'ok'=>$tw !== '', 'detail'=>$tw ? "@$tw" : 'Add @handle'];
    $bing = getSeoSetting('bing_meta');
    $checks[] = ['label'=>'Bing Webmaster Verification', 'ok'=>$bing !== '', 'detail'=>$bing ? 'Set' : 'Add Bing verification meta'];
    $surl = defined('SITE_URL') ? SITE_URL : '';
    $checks[] = ['label'=>'Site URL configured', 'ok'=>$surl !== '' && $surl !== 'https://example.com', 'detail'=>$surl ?: 'Set in Settings'];
    $robots = @file_get_contents(__DIR__ . '/../robots.txt');
    $checks[] = ['label'=>'robots.txt exists', 'ok'=>$robots !== false, 'detail'=>'File present and editable'];
    $sitemap = file_exists(__DIR__ . '/../sitemap.xml') || file_exists(__DIR__ . '/../sitemap.php');
    $checks[] = ['label'=>'Sitemap file', 'ok'=>$sitemap, 'detail'=>'sitemap.xml / sitemap.php exists'];
    return $checks;
}
$healthChecks = seoHealthChecks();
$healthScore  = count(array_filter($healthChecks, fn($c)=>$c['ok']));
$healthTotal  = count($healthChecks);
?>
<!DOCTYPE html>
<html lang="ne">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>SEO Manager | आकाशवाणी Admin</title>
<meta name="robots" content="noindex,nofollow"/>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  body{background:#f8fafc;color:#0f172a;font-family:'Inter',system-ui,sans-serif;font-size:14px}
  input,select,textarea{background:#fff;border:1px solid #e2e8f0;padding:8px 12px;width:100%;color:#0f172a;outline:none;border-radius:6px;font-size:13px;font-family:inherit}
  input:focus,select:focus,textarea:focus{border-color:#0ea5e9;box-shadow:0 0 0 3px rgba(14,165,233,.12)}
  label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#64748b;margin-bottom:4px}
  .hint{font-size:11px;color:#94a3b8;margin-top:4px;font-family:monospace}
  .card{background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;margin-bottom:20px}
  .card-title{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#0ea5e9;margin-bottom:18px;padding-bottom:10px;border-bottom:1px solid #e2e8f0}
  .btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;border:none;transition:.15s}
  .btn-primary{background:#0ea5e9;color:#fff}.btn-primary:hover{background:#0284c7}
  .btn-danger{background:#ef4444;color:#fff}.btn-danger:hover{background:#dc2626}
  .btn-ghost{background:transparent;border:1px solid #e2e8f0;color:#475569}.btn-ghost:hover{background:#f1f5f9}
  .tab{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px 8px 0 0;font-size:12px;font-weight:600;text-decoration:none;color:#64748b;border:1px solid transparent;border-bottom:none;transition:.15s}
  .tab:hover{color:#0ea5e9;background:#f0f9ff}
  .tab.active{background:#fff;color:#0ea5e9;border-color:#e2e8f0;border-bottom-color:#fff;margin-bottom:-1px}
  .badge{display:inline-flex;align-items:center;padding:2px 8px;border-radius:999px;font-size:10px;font-weight:700}
  .char-counter{font-size:10px;font-family:monospace;color:#94a3b8;text-align:right;margin-top:2px}
</style>
</head>
<body class="min-h-screen">

<!-- Header -->
<header style="background:#fff;border-bottom:1px solid #e2e8f0;position:sticky;top:0;z-index:50">
  <div style="max-width:900px;margin:0 auto;padding:0 16px;height:52px;display:flex;align-items:center;justify-content:space-between">
    <div style="display:flex;align-items:center;gap:10px">
      <span style="font-size:20px">🔍</span>
      <span style="font-weight:800;font-size:13px;letter-spacing:.08em;text-transform:uppercase">SEO <span style="color:#0ea5e9">Manager</span></span>
      <span style="font-size:11px;color:#94a3b8;font-family:monospace">Search Engine Optimization</span>
    </div>
    <div style="display:flex;align-items:center;gap:12px">
      <a href="<?= htmlspecialchars($siteUrl ?: '/') ?>" target="_blank" style="font-size:11px;color:#0ea5e9;font-weight:600">🌐 Site हेर्नुस् ↗</a>
      <a href="/admin/settings.php" style="font-size:12px;color:#64748b">⚙ Settings</a>
      <a href="/admin/dashboard.php" style="font-size:12px;color:#64748b">← Dashboard</a>
    </div>
  </div>
</header>

<div style="max-width:900px;margin:0 auto;padding:24px 16px">

  <!-- Flash messages -->
  <?php if ($msg): ?>
    <div style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:10px 16px;border-radius:8px;margin-bottom:16px;font-size:13px"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div style="background:#fef2f2;border:1px solid #fca5a5;color:#b91c1c;padding:10px 16px;border-radius:8px;margin-bottom:16px;font-size:13px"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- Health Score Banner -->
  <?php $pct = round($healthScore/$healthTotal*100); $clr = $pct>=80?'#16a34a':($pct>=50?'#d97706':'#dc2626'); ?>
  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;gap:20px">
    <div style="font-size:36px;font-weight:900;color:<?= $clr ?>"><?= $pct ?>%</div>
    <div style="flex:1">
      <div style="font-size:13px;font-weight:700;margin-bottom:6px">SEO Health Score — <span style="color:<?= $clr ?>"><?= $healthScore ?>/<?= $healthTotal ?> checks passed</span></div>
      <div style="height:8px;background:#e2e8f0;border-radius:4px;overflow:hidden">
        <div style="height:100%;width:<?= $pct ?>%;background:<?= $clr ?>;border-radius:4px;transition:.4s"></div>
      </div>
    </div>
    <a href="?tab=health" style="font-size:12px;font-weight:600;color:#0ea5e9;text-decoration:none">View Checklist →</a>
  </div>

  <!-- Tabs -->
  <div style="display:flex;flex-wrap:wrap;gap:4px;border-bottom:1px solid #e2e8f0;margin-bottom:0">
    <?php
    $tabs = [
      'global'  => ['🌐','Global Meta'],
      'pages'   => ['📄','Per-Page SEO'],
      'google'  => ['📊','Google & Analytics'],
      'schema'  => ['🏛','Schema.org'],
      'sitemap' => ['🗺','Sitemap'],
      'robots'  => ['🤖','Robots.txt'],
      'health'  => ['✅','Health Check'],
    ];
    foreach ($tabs as $key => [$ico,$label]):
    ?>
      <a href="?tab=<?= $key ?>" class="tab <?= $tab===$key?'active':'' ?>"><?= $ico ?> <?= $label ?></a>
    <?php endforeach; ?>
  </div>
  <div style="background:#fff;border:1px solid #e2e8f0;border-top:none;border-radius:0 0 10px 10px;padding:24px">

  <!-- ═══ TAB: GLOBAL META ═══ -->
  <?php if ($tab === 'global'): ?>
    <div style="margin-bottom:20px">
      <h2 style="font-size:16px;font-weight:700;margin-bottom:4px">🌐 Global Meta Tags</h2>
      <p style="color:#64748b;font-size:13px">हरेक page मा लाग्ने default SEO settings। Individual page ले यसलाई override गर्न सक्छ।</p>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="save_global"/>
      <div style="display:grid;gap:18px">
        <div>
          <label>Meta Title Template</label>
          <input type="text" name="meta_title_template" maxlength="70"
            value="<?= htmlspecialchars(getSeoSetting('meta_title_template','%page% | आकाशवाणी — सूचनाको खुला आकाश')) ?>"
            oninput="document.getElementById('title-count').textContent=this.value.length+'/70 chars'"/>
          <div class="char-counter" id="title-count"><?= mb_strlen(getSeoSetting('meta_title_template','')) ?>/70 chars</div>
          <p class="hint">%page% = page को आफ्नै नाम। उदाहरण: "राशिफल | आकाशवाणी"</p>
        </div>
        <div>
          <label>Default Meta Description</label>
          <textarea name="meta_description" rows="3" maxlength="160"
            oninput="document.getElementById('desc-count').textContent=this.value.length+'/160 chars'"><?= htmlspecialchars(getSeoSetting('meta_description','आकाशवाणी — AI News, पात्रो, राशिफल, NEPSE, IPO, सरकारी सेवा र धेरै कुरा एकै app मा। नेपालको सबैभन्दा comprehensive information portal।')) ?></textarea>
          <div class="char-counter" id="desc-count"><?= mb_strlen(getSeoSetting('meta_description','')) ?>/160 chars</div>
          <p class="hint">Google ले search result मा यही देखाउँछ। 120-160 characters राम्रो।</p>
        </div>
        <div>
          <label>Meta Keywords</label>
          <input type="text" name="meta_keywords"
            value="<?= htmlspecialchars(getSeoSetting('meta_keywords','नेपाली पात्रो, राशिफल, NEPSE, IPO, नेपाल समाचार, सरकारी सेवा, nepali patro, rashifal, nepal news, nepse, ipo')) ?>"/>
          <p class="hint">Comma separated। Google ले कम importance दिन्छ, Bing ले हेर्छ।</p>
        </div>
        <div>
          <label>Default OG Image (Social Share Image)</label>
          <input type="url" name="og_image_default"
            value="<?= htmlspecialchars(getSeoSetting('og_image_default', $siteUrl.'/assets/images/og-image.jpg')) ?>"/>
          <p class="hint">Facebook, WhatsApp, Twitter मा share गर्दा देखिने image। 1200×630px JPG राम्रो। Full URL राख्नुस्।</p>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <div>
            <label>Twitter / X Handle</label>
            <input type="text" name="twitter_handle" placeholder="aakashvani_np"
              value="<?= htmlspecialchars(getSeoSetting('twitter_handle','')) ?>"/>
            <p class="hint">@ बिना। Twitter card मा twitter:site हाल्छ।</p>
          </div>
          <div>
            <label>Facebook App ID</label>
            <input type="text" name="facebook_app_id" placeholder="123456789"
              value="<?= htmlspecialchars(getSeoSetting('facebook_app_id','')) ?>"/>
            <p class="hint">Facebook OG debugger लागि। Optional।</p>
          </div>
        </div>
        <div style="padding-top:8px">
          <button type="submit" class="btn btn-primary">💾 Save Global SEO Settings</button>
        </div>
      </div>
    </form>

    <div style="margin-top:28px;padding:16px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px">
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#0369a1;margin-bottom:10px">📋 Google Search Result Preview</div>
      <div style="font-size:18px;color:#1a0dab;font-family:arial,sans-serif;line-height:1.3" id="preview-title"><?= htmlspecialchars(getSeoSetting('meta_title_template','आकाशवाणी — सूचनाको खुला आकाश')) ?></div>
      <div style="font-size:13px;color:#006621;font-family:arial,sans-serif;margin:2px 0"><?= htmlspecialchars($siteUrl ?: 'https://www.tankaadhikari.com.np') ?></div>
      <div style="font-size:13px;color:#545454;font-family:arial,sans-serif;line-height:1.5" id="preview-desc"><?= htmlspecialchars(mb_substr(getSeoSetting('meta_description',''),0,160)) ?></div>
    </div>


  <!-- ═══ TAB: PER-PAGE SEO ═══ -->
  <?php elseif ($tab === 'pages'): ?>
    <div style="margin-bottom:20px">
      <h2 style="font-size:16px;font-weight:700;margin-bottom:4px">📄 Per-Page SEO</h2>
      <p style="color:#64748b;font-size:13px">प्रत्येक page को title, description, keywords अलग-अलग set गर्नुस्। Global setting लाई override गर्छ।</p>
    </div>

    <?php if ($editPage !== null): ?>
      <!-- Edit form -->
      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;margin-bottom:20px">
        <div style="font-weight:700;margin-bottom:16px;font-size:13px">✏️ Edit: <span style="color:#0ea5e9"><?= htmlspecialchars($editPage['page_path']) ?></span></div>
        <form method="POST">
          <input type="hidden" name="action" value="save_page"/>
          <input type="hidden" name="page_path" value="<?= htmlspecialchars($editPage['page_path']) ?>"/>
          <div style="display:grid;gap:14px">
            <div>
              <label>Meta Title <span style="color:#94a3b8">(50-60 chars ideal)</span></label>
              <input type="text" name="meta_title" maxlength="70"
                value="<?= htmlspecialchars($editPage['meta_title'] ?? '') ?>"
                placeholder="<?= htmlspecialchars($knownPages[$editPage['page_path']] ?? '') ?> | आकाशवाणी"/>
            </div>
            <div>
              <label>Meta Description <span style="color:#94a3b8">(120-160 chars ideal)</span></label>
              <textarea name="meta_description" rows="3" maxlength="160"><?= htmlspecialchars($editPage['meta_description'] ?? '') ?></textarea>
            </div>
            <div>
              <label>Keywords</label>
              <input type="text" name="meta_keywords"
                value="<?= htmlspecialchars($editPage['meta_keywords'] ?? '') ?>"
                placeholder="nepali patro 2081, bikram sambat calendar ..."/>
            </div>
            <div>
              <label>Custom OG Image URL <span style="color:#94a3b8">(optional)</span></label>
              <input type="url" name="og_image"
                value="<?= htmlspecialchars($editPage['og_image'] ?? '') ?>"
                placeholder="<?= htmlspecialchars($siteUrl) ?>/assets/images/og-news.jpg"/>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
              <input type="checkbox" name="is_noindex" id="ni" value="1" <?= !empty($editPage['is_noindex']) ? 'checked' : '' ?> style="width:auto"/>
              <label for="ni" style="margin:0;text-transform:none;font-size:13px;letter-spacing:0;color:#0f172a">noindex, nofollow — Google मा नदेखाउनुस् (admin pages को लागि)</label>
            </div>
            <div style="display:flex;gap:10px;padding-top:4px">
              <button type="submit" class="btn btn-primary">💾 Save</button>
              <a href="?tab=pages" class="btn btn-ghost">Cancel</a>
            </div>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <!-- Pages table -->
    <div style="overflow-x:auto">
      <table style="width:100%;border-collapse:collapse;font-size:12px">
        <thead>
          <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0">
            <th style="padding:10px 12px;text-align:left;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b">Page</th>
            <th style="padding:10px 12px;text-align:left;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b">Title set?</th>
            <th style="padding:10px 12px;text-align:left;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b">Desc set?</th>
            <th style="padding:10px 12px;text-align:left;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b">noindex?</th>
            <th style="padding:10px 12px;text-align:left"></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($knownPages as $path => $label): ?>
          <?php $p = $pageIndex[$path] ?? null; ?>
          <tr style="border-bottom:1px solid #f1f5f9;<?= $tab==='pages' && ($_GET['edit']??'')==ltrim($path,'/')?'background:#f0f9ff':'' ?>">
            <td style="padding:9px 12px">
              <div style="font-weight:600;color:#0f172a"><?= htmlspecialchars($label) ?></div>
              <div style="color:#94a3b8;font-family:monospace;font-size:11px"><?= htmlspecialchars($path) ?></div>
            </td>
            <td style="padding:9px 12px"><?= $p && !empty($p['meta_title']) ? '<span style="color:#16a34a;font-weight:700">✓ Yes</span>' : '<span style="color:#dc2626">✗ Default</span>' ?></td>
            <td style="padding:9px 12px"><?= $p && !empty($p['meta_description']) ? '<span style="color:#16a34a;font-weight:700">✓ Yes</span>' : '<span style="color:#dc2626">✗ Default</span>' ?></td>
            <td style="padding:9px 12px"><?= $p && !empty($p['is_noindex']) ? '<span style="color:#dc2626;font-weight:700">noindex</span>' : '<span style="color:#94a3b8">index</span>' ?></td>
            <td style="padding:9px 12px;text-align:right">
              <a href="?tab=pages&edit=<?= urlencode(ltrim($path,'/')) ?>" style="color:#0ea5e9;font-weight:600;font-size:12px;margin-right:8px">✏️ Edit</a>
              <?php if ($p): ?>
                <form method="POST" style="display:inline" onsubmit="return confirm('Remove SEO override for <?= htmlspecialchars($path) ?>?')">
                  <input type="hidden" name="action" value="delete_page"/>
                  <input type="hidden" name="page_path" value="<?= htmlspecialchars($path) ?>"/>
                  <button type="submit" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:12px;font-weight:600">🗑 Reset</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>


  <!-- ═══ TAB: GOOGLE & ANALYTICS ═══ -->
  <?php elseif ($tab === 'google'): ?>
    <div style="margin-bottom:20px">
      <h2 style="font-size:16px;font-weight:700;margin-bottom:4px">📊 Google & Analytics</h2>
      <p style="color:#64748b;font-size:13px">Google Analytics, Google Search Console, Bing Webmaster — सबै verification codes यहाँ राख्नुस्।</p>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="save_google"/>
      <div style="display:grid;gap:20px">

        <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:16px">
          <div style="font-weight:700;font-size:13px;color:#166534;margin-bottom:12px">📈 Google Analytics 4 (GA4)</div>
          <label>Measurement ID</label>
          <input type="text" name="ga4_id" placeholder="G-XXXXXXXXXX"
            value="<?= htmlspecialchars(getSeoSetting('ga4_id','')) ?>"/>
          <p class="hint">Google Analytics → Admin → Data Streams → Measurement ID हेर्नुस् (G- बाट start हुन्छ)</p>
        </div>

        <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:16px">
          <div style="font-weight:700;font-size:13px;color:#0369a1;margin-bottom:12px">🔍 Google Search Console</div>
          <label>Verification Meta Content</label>
          <input type="text" name="gsc_meta" placeholder="AbCdEfGhIjKlMnOpQrStUvWxYz123456"
            value="<?= htmlspecialchars(getSeoSetting('gsc_meta','')) ?>"/>
          <p class="hint">Search Console → Settings → Ownership Verification → HTML Tag → content="..." भित्रको value मात्र copy गर्नुस्। पूरो tag होइन।</p>
          <?php if (getSeoSetting('gsc_meta')): ?>
            <div style="margin-top:8px;font-size:11px;background:#fff;padding:8px 12px;border-radius:4px;font-family:monospace;color:#374151;border:1px solid #e2e8f0">
              &lt;meta name="google-site-verification" content="<?= htmlspecialchars(getSeoSetting('gsc_meta')) ?>"/&gt;
              <span style="color:#16a34a;font-weight:700"> ← यो header मा auto add भइरहेको छ ✓</span>
            </div>
          <?php endif; ?>
        </div>

        <div style="background:#fefce8;border:1px solid #fde68a;border-radius:8px;padding:16px">
          <div style="font-weight:700;font-size:13px;color:#854d0e;margin-bottom:12px">🔵 Bing Webmaster Tools</div>
          <label>Verification Meta Content</label>
          <input type="text" name="bing_meta" placeholder="ABC123DEF456..."
            value="<?= htmlspecialchars(getSeoSetting('bing_meta','')) ?>"/>
          <p class="hint">Bing Webmaster Tools → Verify Ownership → HTML Meta Tag → content="..." भित्रको value।</p>
        </div>

        <div style="background:#faf5ff;border:1px solid #d8b4fe;border-radius:8px;padding:16px">
          <div style="font-weight:700;font-size:13px;color:#6b21a8;margin-bottom:12px">🏷 Google Tag Manager (Optional)</div>
          <label>GTM Container ID</label>
          <input type="text" name="gtm_id" placeholder="GTM-XXXXXXX"
            value="<?= htmlspecialchars(getSeoSetting('gtm_id','')) ?>"/>
          <p class="hint">GA4 already add गर्नुभयो भने GTM छोड्नुस्। एउटा मात्र use गर्नुस्।</p>
        </div>

        <div style="padding-top:4px">
          <button type="submit" class="btn btn-primary">💾 Save Analytics Settings</button>
        </div>
      </div>
    </form>

    <div style="margin-top:24px;padding:16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px">
      <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#64748b;margin-bottom:12px">📚 Step-by-step Guide</div>
      <ol style="font-size:12px;color:#475569;line-height:2;padding-left:20px">
        <li><strong>Google Search Console</strong> → <a href="https://search.google.com/search-console" target="_blank" style="color:#0ea5e9">search.google.com/search-console</a> → Add Property → URL Prefix → tankaadhikari.com.np → HTML Tag method → content value यहाँ paste गर्नुस्</li>
        <li><strong>Sitemap submit</strong> → Search Console → Sitemaps → <code style="background:#e2e8f0;padding:1px 5px;border-radius:3px">https://tankaadhikari.com.np/sitemap.xml</code> add गर्नुस्</li>
        <li><strong>GA4</strong> → <a href="https://analytics.google.com" target="_blank" style="color:#0ea5e9">analytics.google.com</a> → Create Property → Web → domain add → Measurement ID (G-XXXXX) यहाँ paste</li>
        <li><strong>Bing</strong> → <a href="https://www.bing.com/webmasters" target="_blank" style="color:#0ea5e9">bing.com/webmasters</a> → Add Site → HTML Meta Tag method → content value यहाँ paste</li>
      </ol>
    </div>


  <!-- ═══ TAB: SCHEMA.ORG ═══ -->
  <?php elseif ($tab === 'schema'): ?>
    <div style="margin-bottom:20px">
      <h2 style="font-size:16px;font-weight:700;margin-bottom:4px">🏛 Schema.org Structured Data</h2>
      <p style="color:#64748b;font-size:13px">Google rich results को लागि JSON-LD structured data। यसले Google Knowledge Panel र Rich Snippets मा site देखाउन मद्दत गर्छ।</p>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="save_schema"/>
      <div style="display:grid;gap:18px">
        <div style="display:flex;align-items:center;gap:10px;padding:14px;background:#f0fdf4;border:1px solid #86efac;border-radius:8px">
          <input type="checkbox" name="schema_enabled" id="sch_en" value="1" <?= getSeoSetting('schema_enabled')==='1'?'checked':'' ?> style="width:18px;height:18px"/>
          <label for="sch_en" style="margin:0;text-transform:none;font-size:14px;font-weight:700;letter-spacing:0;color:#166534">✅ Schema.org JSON-LD Enable गर्नुस् (Strongly Recommended)</label>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <div>
            <label>Organization Name</label>
            <input type="text" name="schema_org_name"
              value="<?= htmlspecialchars(getSeoSetting('schema_org_name', defined('SITE_NAME')?SITE_NAME:'आकाशवाणी')) ?>"/>
          </div>
          <div>
            <label>Site URL</label>
            <input type="url" name="schema_org_url"
              value="<?= htmlspecialchars(getSeoSetting('schema_org_url', $siteUrl)) ?>"/>
          </div>
        </div>
        <div>
          <label>Logo URL (Full URL)</label>
          <input type="url" name="schema_org_logo"
            value="<?= htmlspecialchars(getSeoSetting('schema_org_logo', $siteUrl.'/assets/images/logo.png')) ?>"/>
          <p class="hint">Organization को logo — full URL। 112×112px minimum, square PNG।</p>
        </div>
        <div>
          <label>Organization Description</label>
          <textarea name="schema_org_desc" rows="2"><?= htmlspecialchars(getSeoSetting('schema_org_desc','आकाशवाणी नेपालको leading digital information portal हो — AI News, पात्रो, राशिफल, NEPSE, IPO र सरकारी सेवा एकै ठाउँमा।')) ?></textarea>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <div>
            <label>Facebook Page URL</label>
            <input type="url" name="schema_org_fb" placeholder="https://facebook.com/aakashvani"
              value="<?= htmlspecialchars(getSeoSetting('schema_org_fb','')) ?>"/>
          </div>
          <div>
            <label>Twitter/X Profile URL</label>
            <input type="url" name="schema_org_twitter" placeholder="https://twitter.com/aakashvani_np"
              value="<?= htmlspecialchars(getSeoSetting('schema_org_twitter','')) ?>"/>
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
          <input type="checkbox" name="schema_search" id="sch_srch" value="1" <?= getSeoSetting('schema_search')==='1'?'checked':'' ?> style="width:auto"/>
          <label for="sch_srch" style="margin:0;text-transform:none;font-size:13px;font-weight:600;letter-spacing:0;color:#0f172a">🔍 Sitelinks Searchbox add गर्नुस् (Google search result मा site को search box देखाउँछ)</label>
        </div>
        <div>
          <button type="submit" class="btn btn-primary">💾 Save Schema Settings</button>
        </div>
      </div>
    </form>

    <!-- Preview JSON-LD -->
    <?php if (getSeoSetting('schema_enabled')==='1'): ?>
    <div style="margin-top:24px">
      <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#64748b;margin-bottom:8px">Generated JSON-LD Preview</div>
      <?php
        $schemaData = [
          '@context' => 'https://schema.org',
          '@type'    => 'Organization',
          'name'     => getSeoSetting('schema_org_name','आकाशवाणी'),
          'url'      => getSeoSetting('schema_org_url',$siteUrl),
          'logo'     => getSeoSetting('schema_org_logo',$siteUrl.'/assets/images/logo.png'),
          'description' => getSeoSetting('schema_org_desc',''),
          'sameAs'   => array_filter([getSeoSetting('schema_org_fb',''),getSeoSetting('schema_org_twitter','')]),
        ];
      ?>
      <pre style="background:#1e293b;color:#94d2e0;padding:16px;border-radius:8px;font-size:11px;overflow-x:auto;line-height:1.6"><?= htmlspecialchars(json_encode($schemaData,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)) ?></pre>
      <p style="font-size:11px;color:#64748b;margin-top:8px">Test: <a href="https://search.google.com/test/rich-results" target="_blank" style="color:#0ea5e9">Google Rich Results Test ↗</a> | <a href="https://validator.schema.org" target="_blank" style="color:#0ea5e9">Schema Validator ↗</a></p>
    </div>
    <?php endif; ?>


  <!-- ═══ TAB: SITEMAP ═══ -->
  <?php elseif ($tab === 'sitemap'): ?>
    <div style="margin-bottom:20px">
      <h2 style="font-size:16px;font-weight:700;margin-bottom:4px">🗺 Sitemap Manager</h2>
      <p style="color:#64748b;font-size:13px">Sitemap Google र Bing लाई submit गर्नुस् र ping गर्नुस्।</p>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
      <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:16px">
        <div style="font-weight:700;font-size:13px;margin-bottom:8px">📄 Sitemap URL</div>
        <code style="font-size:12px;color:#0369a1"><?= htmlspecialchars($siteUrl) ?>/sitemap.xml</code>
        <div style="margin-top:10px;display:flex;gap:8px">
          <a href="<?= htmlspecialchars($siteUrl) ?>/sitemap.xml" target="_blank" class="btn btn-ghost" style="font-size:12px;padding:6px 12px">🔗 View</a>
        </div>
      </div>
      <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:16px">
        <div style="font-weight:700;font-size:13px;margin-bottom:8px">🔔 Search Engine Ping</div>
        <p style="font-size:12px;color:#64748b;margin-bottom:10px">Google र Bing लाई sitemap update भएको notify गर्नुस्।</p>
        <form method="POST">
          <input type="hidden" name="action" value="ping_sitemap"/>
          <button type="submit" class="btn btn-primary" style="font-size:12px;padding:7px 14px">📡 Ping Google & Bing</button>
        </form>
      </div>
    </div>

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin-bottom:16px">
      <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#64748b;margin-bottom:12px">📚 Sitemap Submit Steps</div>
      <ol style="font-size:12px;color:#475569;line-height:2.2;padding-left:20px">
        <li><strong>Google Search Console</strong> → Left menu → Sitemaps → Add new sitemap: <code style="background:#f1f5f9;padding:1px 6px;border-radius:3px"><?= htmlspecialchars($siteUrl) ?>/sitemap.xml</code> → Submit</li>
        <li><strong>Bing Webmaster</strong> → Sitemaps → Submit sitemap: <code style="background:#f1f5f9;padding:1px 6px;border-radius:3px"><?= htmlspecialchars($siteUrl) ?>/sitemap.xml</code></li>
        <li>New content add गर्दा माथि <strong>Ping</strong> button click गर्नुस् — Google लाई तुरुन्त index गर्न भन्छ।</li>
        <li>robots.txt मा <code style="background:#f1f5f9;padding:1px 6px;border-radius:3px">Sitemap: <?= htmlspecialchars($siteUrl) ?>/sitemap.xml</code> already add भइसकेको छ ✓</li>
      </ol>
    </div>

    <div style="background:#fefce8;border:1px solid #fde68a;border-radius:8px;padding:14px">
      <div style="font-size:12px;font-weight:700;color:#854d0e;margin-bottom:6px">⚡ Indexed Pages चेक गर्ने तरिका</div>
      <p style="font-size:12px;color:#713f12">Google मा search गर्नुस्: <code style="background:#fef9c3;padding:2px 6px;border-radius:3px">site:tankaadhikari.com.np</code> — कति pages index भएको छ देखिन्छ।</p>
    </div>


  <!-- ═══ TAB: ROBOTS.TXT ═══ -->
  <?php elseif ($tab === 'robots'): ?>
    <div style="margin-bottom:20px">
      <h2 style="font-size:16px;font-weight:700;margin-bottom:4px">🤖 Robots.txt Editor</h2>
      <p style="color:#64748b;font-size:13px">Google, Bing, र अन्य bots लाई कुन page crawl गर्ने/नगर्ने भन्न robots.txt edit गर्नुस्।</p>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="save_robots"/>
      <div style="margin-bottom:12px">
        <label>robots.txt Content</label>
        <textarea name="robots_content" rows="18" style="font-family:monospace;font-size:13px;line-height:1.7"><?= htmlspecialchars($robotsTxt) ?></textarea>
        <p class="hint">⚠️ गलत value राख्दा पूरो site Google बाट hide हुन सक्छ। सावधान रहनुस्।</p>
      </div>
      <div style="display:flex;gap:10px;align-items:center">
        <button type="submit" class="btn btn-primary">💾 Save robots.txt</button>
        <a href="<?= htmlspecialchars($siteUrl) ?>/robots.txt" target="_blank" class="btn btn-ghost">🔗 View Live File</a>
        <a href="https://www.google.com/webmasters/tools/robots-testing-tool" target="_blank" style="font-size:12px;color:#0ea5e9">Google Robots Tester ↗</a>
      </div>
    </form>
    <div style="margin-top:20px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px">
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#64748b;margin-bottom:8px">📋 Quick Reference</div>
      <table style="font-size:12px;font-family:monospace;width:100%">
        <tr><td style="padding:4px 8px;color:#0369a1;white-space:nowrap">Allow: /</td><td style="padding:4px 8px;color:#475569">सबै pages allow</td></tr>
        <tr><td style="padding:4px 8px;color:#dc2626;white-space:nowrap">Disallow: /admin/</td><td style="padding:4px 8px;color:#475569">Admin pages hide गर्नुस्</td></tr>
        <tr><td style="padding:4px 8px;color:#dc2626;white-space:nowrap">Disallow: /</td><td style="padding:4px 8px;color:#475569;color:#dc2626">⚠️ सबै pages block! नराख्नुस्</td></tr>
        <tr><td style="padding:4px 8px;color:#16a34a;white-space:nowrap">Sitemap: https://...</td><td style="padding:4px 8px;color:#475569">Sitemap URL declare गर्नुस्</td></tr>
        <tr><td style="padding:4px 8px;color:#0369a1;white-space:nowrap">Crawl-delay: 10</td><td style="padding:4px 8px;color:#475569">Bots बाट server protect (optional)</td></tr>
      </table>
    </div>


  <!-- ═══ TAB: HEALTH CHECK ═══ -->
  <?php elseif ($tab === 'health'): ?>
    <div style="margin-bottom:20px">
      <h2 style="font-size:16px;font-weight:700;margin-bottom:4px">✅ SEO Health Checklist</h2>
      <p style="color:#64748b;font-size:13px">Google first ranking को लागि complete गर्नुपर्ने checklist। सबै ✅ भयो भने राम्रो ranking मिल्छ।</p>
    </div>

    <div style="display:grid;gap:10px">
      <?php foreach ($healthChecks as $check): ?>
      <div style="display:flex;align-items:center;gap:14px;padding:14px 16px;background:<?= $check['ok']?'#f0fdf4':'#fef2f2' ?>;border:1px solid <?= $check['ok']?'#86efac':'#fca5a5' ?>;border-radius:8px">
        <span style="font-size:22px;flex-shrink:0"><?= $check['ok'] ? '✅' : '❌' ?></span>
        <div style="flex:1">
          <div style="font-weight:700;font-size:13px;color:<?= $check['ok']?'#166534':'#b91c1c' ?>"><?= htmlspecialchars($check['label']) ?></div>
          <div style="font-size:12px;color:#64748b;margin-top:2px"><?= htmlspecialchars($check['detail']) ?></div>
        </div>
        <?php if (!$check['ok']): ?>
          <span style="font-size:11px;font-weight:600;color:#dc2626;background:#fef2f2;border:1px solid #fca5a5;padding:3px 10px;border-radius:999px;white-space:nowrap">Fix गर्नुस्</span>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <div style="margin-top:24px;padding:16px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px">
      <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#0369a1;margin-bottom:12px">🚀 Google First Ranking को लागि अतिरिक्त Tips</div>
      <ul style="font-size:12px;color:#1e40af;line-height:2.2;padding-left:18px">
        <li><strong>Content Quality:</strong> हरेक article minimum 500+ words, unique Nepali content लेख्नुस्</li>
        <li><strong>Page Speed:</strong> <a href="https://pagespeed.web.dev" target="_blank" style="color:#0ea5e9">pagespeed.web.dev</a> मा score 90+ राख्नुस्</li>
        <li><strong>Mobile Friendly:</strong> Site mobile मा राम्रो देखिनुपर्छ — पहिले नै optimize छ ✓</li>
        <li><strong>Backlinks:</strong> अन्य Nepali sites बाट link लिनुस् — guest posts, directories</li>
        <li><strong>Regular Updates:</strong> Daily नयाँ content add गर्नुस् — Google fresh content मन पराउँछ</li>
        <li><strong>Local SEO:</strong> "Nepal", "Nepali", "नेपाल" keywords हरेक page मा naturally use गर्नुस्</li>
        <li><strong>Internal Linking:</strong> आफ्नै articles बीच links राख्नुस्</li>
        <li><strong>Image Alt Tags:</strong> सबै images मा Nepali/English alt text राख्नुस्</li>
      </ul>
    </div>

    <div style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap">
      <a href="https://search.google.com/search-console" target="_blank" class="btn btn-ghost" style="font-size:12px">Google Search Console ↗</a>
      <a href="https://pagespeed.web.dev" target="_blank" class="btn btn-ghost" style="font-size:12px">PageSpeed Test ↗</a>
      <a href="https://search.google.com/test/rich-results" target="_blank" class="btn btn-ghost" style="font-size:12px">Rich Results Test ↗</a>
      <a href="https://www.bing.com/webmasters" target="_blank" class="btn btn-ghost" style="font-size:12px">Bing Webmaster ↗</a>
    </div>

  <?php endif; ?>

  </div><!-- /tab panel -->
</div><!-- /container -->
</body>
</html>
