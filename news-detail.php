<?php
/**
 * news-detail.php v2 — News reader with auto AI-expand + clear source attribution
 *
 * STRATEGY (Nepal Copyright Act 2059 + fair-use):
 *   • Show AI-summarised full article (based on public RSS excerpt/og:description)
 *   • Prominent source attribution: logo + full name + canonical URL — always visible
 *   • Source is shown ABOVE content, WITHIN content footer, and in legal notice
 *   • "मूल स्रोतमा पूरा पढ्नुहोस्" CTA always visible
 *   • We do NOT republish scraped full body. AI summary is from RSS-derived public data.
 */
@ini_set('default_socket_timeout', 8);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$slug   = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$url    = isset($_GET['url']) ? trim($_GET['url']) : '';
$srcLbl = isset($_GET['src']) ? trim($_GET['src']) : '';
$view   = isset($_GET['view']) ? trim($_GET['view']) : 'reader';
$storedArticle = null;

if ($slug !== '') {
    try {
        ensureNewsTable();
        $stmt = db()->prepare("SELECT * FROM tech_news WHERE slug=? AND is_published=1 LIMIT 1");
        $stmt->execute([$slug]);
        $storedArticle = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        if ($storedArticle) {
            $url = trim($storedArticle['original_url'] ?? $storedArticle['source_url'] ?? '');
            $srcLbl = trim($storedArticle['source_name'] ?? $storedArticle['source'] ?? $srcLbl);
        }
    } catch (Throwable $e) {
    }
}

if (!$storedArticle && (!$url || !filter_var($url, FILTER_VALIDATE_URL))) {
    http_response_code(400);
    $pageTitle = 'त्रुटि · आकाशवाणी';
    @include __DIR__ . '/header.php';
    echo '<div style="padding:40px 20px;text-align:center"><h2 class="ne">समाचार फेला परेन</h2><a href="/news.php" style="color:#0d9488;font-weight:600">← समाचारमा फर्किनुहोस्</a></div>';
    @include __DIR__ . '/footer.php';
    exit;
}

/* Whitelist trusted Nepali news domains */
$allowedHosts = [
    'onlinekhabar.com','www.onlinekhabar.com','english.onlinekhabar.com',
    'setopati.com','www.setopati.com','en.setopati.com',
    'ratopati.com','www.ratopati.com','english.ratopati.com',
    'nepalkhabar.com','www.nepalkhabar.com',
    'ekantipur.com','www.ekantipur.com',
    'nagariknews.nagariknetwork.com',
    'annapurnapost.com','www.annapurnapost.com',
    'hamrakura.com','www.hamrakura.com',
    'techpana.com','www.techpana.com',
    'techlekha.com','www.techlekha.com',
    'techsansar.com','www.techsansar.com',
    'nepalitelecom.com','www.nepalitelecom.com',
    'sharesansar.com','www.sharesansar.com',
    'merolagani.com','www.merolagani.com',
    'arthikpati.com','www.arthikpati.com',
    'goalnepal.com','www.goalnepal.com',
    'kathmandupost.com','www.kathmandupost.com',
    'thehimalayantimes.com','www.thehimalayantimes.com',
    'myrepublica.nagariknetwork.com',
    'risingnepaldaily.com','www.risingnepaldaily.com',
    'bbc.com','www.bbc.com','feeds.bbci.co.uk',
    'aljazeera.com','www.aljazeera.com',
];
$host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
if (!$storedArticle && !in_array($host, $allowedHosts, true)) {
    header('Location: ' . $url);
    exit;
}

/* Source metadata */
$sources = [
    'onlinekhabar.com'              => ['name'=>'OnlineKhabar',       'home'=>'https://www.onlinekhabar.com',                'color'=>'#dc2626'],
    'setopati.com'                  => ['name'=>'Setopati',            'home'=>'https://www.setopati.com',                   'color'=>'#0ea5e9'],
    'ratopati.com'                  => ['name'=>'Ratopati',            'home'=>'https://www.ratopati.com',                   'color'=>'#e11d48'],
    'nepalkhabar.com'               => ['name'=>'NepalKhabar',         'home'=>'https://www.nepalkhabar.com',                'color'=>'#7c3aed'],
    'ekantipur.com'                 => ['name'=>'Kantipur / eKantipur','home'=>'https://ekantipur.com',                      'color'=>'#b91c1c'],
    'nagariknews.nagariknetwork.com'=> ['name'=>'Nagarik News',        'home'=>'https://nagariknews.nagariknetwork.com',     'color'=>'#0369a1'],
    'annapurnapost.com'             => ['name'=>'Annapurna Post',      'home'=>'https://www.annapurnapost.com',              'color'=>'#0f766e'],
    'techpana.com'                  => ['name'=>'TechPana',            'home'=>'https://techpana.com',                       'color'=>'#4f46e5'],
    'techlekha.com'                 => ['name'=>'TechLekha',           'home'=>'https://techlekha.com',                      'color'=>'#0891b2'],
    'techsansar.com'                => ['name'=>'TechSansar',          'home'=>'https://techsansar.com',                     'color'=>'#059669'],
    'sharesansar.com'               => ['name'=>'ShareSansar',         'home'=>'https://sharesansar.com',                   'color'=>'#0369a1'],
    'merolagani.com'                => ['name'=>'MeroLagani',          'home'=>'https://merolagani.com',                    'color'=>'#7c3aed'],
    'kathmandupost.com'             => ['name'=>'Kathmandu Post',      'home'=>'https://kathmandupost.com',                 'color'=>'#1d4ed8'],
    'thehimalayantimes.com'         => ['name'=>'Himalayan Times',     'home'=>'https://thehimalayantimes.com',             'color'=>'#92400e'],
    'bbc.com'                       => ['name'=>'BBC नेपाली',          'home'=>'https://www.bbc.com/nepali',                'color'=>'#000000'],
    'aljazeera.com'                 => ['name'=>'Al Jazeera',          'home'=>'https://www.aljazeera.com',                 'color'=>'#c2410c'],
];
$rootHost = preg_replace('/^(www|english|en|feeds|nepalitelecom|goalnepal|arthikpati|hamrakura|risingnepaldaily|myrepublica\.nagariknetwork)\./', '', $host);
$srcMeta  = $sources[$rootHost] ?? ['name'=>$srcLbl?:$host, 'home'=>'https://'.$host, 'color'=>'#475569'];

/* Fetch og metadata only — NOT full body */
$ctx = stream_context_create([
    'http' => ['timeout'=>6, 'user_agent'=>'Mozilla/5.0 Aakashvani/1.0', 'follow_location'=>1, 'ignore_errors'=>true],
    'ssl'  => ['verify_peer'=>true, 'verify_peer_name'=>true],
]);
$html = ($url && filter_var($url, FILTER_VALIDATE_URL)) ? @file_get_contents($url, false, $ctx) : '';

$title = $storedArticle['title'] ?? '';
$img = $storedArticle['image_url'] ?? '';
$excerpt = $storedArticle['excerpt'] ?? '';
$dbContent = $storedArticle['content'] ?? ''; // Full content from DB
$pub = $storedArticle['created_at'] ?? '';
$hasFullContent = (mb_strlen(trim($dbContent)) > 200); // Check if we have substantial content

// FIX: If DB content is short, do a LIVE scrape NOW so first paint has full article
if (!$hasFullContent && $url && filter_var($url, FILTER_VALIDATE_URL)) {
    @require_once __DIR__ . '/includes/article-fetch.php';
    if (function_exists('aakFetchArticle')) {
        try {
            $fetched = aakFetchArticle($url);
            $scraped = trim($fetched['plain'] ?? implode("\n\n", $fetched['paragraphs'] ?? []));
            if (mb_strlen($scraped) > 300) {
                $dbContent = $scraped;
                $hasFullContent = true;
                // Backfill DB for future requests
                if ($slug && $storedArticle) {
                    try {
                        $up = db()->prepare("UPDATE tech_news SET content=?, ai_processed=1 WHERE slug=?");
                        $up->execute([$scraped, $slug]);
                    } catch(\Throwable $e) {}
                }
            }
        } catch(\Throwable $e) {}
    }
}
if ($html) {
    if (preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)/i', $html, $m))    $title   = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
    elseif (preg_match('/<title>([^<]+)<\/title>/i', $html, $m))                                          $title   = html_entity_decode(trim($m[1]), ENT_QUOTES, 'UTF-8');
    if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)/i', $html, $m))    $img     = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
    if (preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)/i', $html, $m)) $excerpt = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
    if (!$excerpt && preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)/i', $html, $m)) $excerpt = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
    if (preg_match('/<meta[^>]+property=["\']article:published_time["\'][^>]+content=["\']([^"\']+)/i', $html, $m)) $pub = $m[1];
}
if (!$title) $title = $srcLbl ? $srcLbl . ' — समाचार' : 'समाचार';
if (mb_strlen($excerpt) > 1000) $excerpt = mb_substr($excerpt, 0, 950, 'UTF-8') . '…';

$pageTitle   = $title . ' · आकाशवाणी';
$pageDesc    = $excerpt ?: $title;
$pageImg     = $img ?: '/assets/images/og-image.jpg';
$pageCanonical = $url;

require_once __DIR__ . '/header.php';
?>

<style>
.art-wrap{padding:14px 14px 100px;max-width:760px;margin:0 auto}
.art-back{display:inline-flex;align-items:center;gap:5px;color:#0d9488;font-weight:600;font-size:13px;text-decoration:none;margin-bottom:14px}

/* ── Source attribution bar — always visible, always prominent ── */
.art-srcbar{
  display:flex;align-items:center;gap:12px;
  background:#fff;border:2px solid var(--src-color,#0d9488);
  border-radius:16px;padding:12px 14px;margin-bottom:16px;
  box-shadow:0 2px 10px -4px rgba(0,0,0,.1);
}
.art-srcbar .logo{
  width:40px;height:40px;border-radius:11px;color:#fff;
  display:flex;align-items:center;justify-content:center;
  font-weight:800;font-size:18px;flex-shrink:0;
  background:var(--src-color,#0d9488);
}
.art-srcbar .meta{flex:1;min-width:0}
.art-srcbar .name{font-size:14px;font-weight:800;color:#0b1220}
.art-srcbar .name a{color:inherit;text-decoration:underline;text-underline-offset:2px}
.art-srcbar .lic{font-size:11px;color:#64748b;margin-top:2px}

.art-title{font-size:21px;font-weight:800;line-height:1.35;color:#0b1220;margin:0 0 10px}
.art-meta{font-size:12px;color:#64748b;margin-bottom:14px;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.art-hero{width:100%;border-radius:14px;overflow:hidden;margin-bottom:16px;background:linear-gradient(135deg,#f0fdfa,#cffafe)}
.art-hero img{width:100%;height:auto;display:block}

/* ── AI Content Area ── */
.art-ai-wrap{background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;margin-bottom:16px}
.art-ai-head{
  display:flex;align-items:center;justify-content:space-between;gap:8px;
  padding:10px 14px;background:linear-gradient(135deg,#f0fdfa,#e0f9f6);
  border-bottom:1px solid #d1fae5;
}
.art-ai-head .label{font-size:11.5px;font-weight:700;color:#065f46;display:flex;align-items:center;gap:5px}
.art-ai-head .src-badge{
  font-size:10.5px;font-weight:700;
  padding:3px 9px;border-radius:999px;color:#fff;
  background:var(--src-color,#0d9488);
  display:inline-flex;align-items:center;gap:4px;
}
.art-ai-body{padding:14px 16px;font-size:15px;line-height:1.85;color:#1e293b;font-family:'Hind Siliguri','Mukta',sans-serif;min-height:200px}
.art-ai-body p{margin-bottom:1.1em}
.art-ai-body h3{font-size:16px;font-weight:700;color:#0f172a;margin:1.4em 0 .5em}
.art-ai-body .db-content{width:100%}
.art-ai-body .excerpt-fallback{font-size:14px;color:#475569;font-style:italic;padding:12px;background:#f8fafc;border-radius:8px;border-left:3px solid #0d9488}
.art-ai-body strong,.art-ai-body b{font-weight:700;color:#0f172a}
.art-ai-footer{
  display:flex;align-items:center;justify-content:space-between;gap:8px;
  padding:10px 14px;background:#f8fafc;border-top:1px solid #e2e8f0;flex-wrap:wrap;
}
.art-ai-footer .src-note{font-size:11px;color:#475569;display:none;}
.art-ai-footer .src-note a{color:var(--src-color,#0d9488);font-weight:700;text-decoration:underline}
.art-ai-footer .read-full{
  font-size:12px;font-weight:700;color:#fff;
  background:var(--src-color,#0d9488);
  padding:6px 12px;border-radius:9px;text-decoration:none;
  display:inline-flex;align-items:center;gap:4px;
}
.art-ai-footer .read-full:hover{opacity:.88}

/* ── Spinner ── */
.art-spinner{
  display:flex;align-items:center;justify-content:center;gap:10px;
  padding:28px 16px;color:#475569;font-size:13px;
}
.art-spinner .spin{width:20px;height:20px;border:2px solid #d1fae5;border-top-color:#0d9488;border-radius:50%;animation:artSpin .7s linear infinite}
@keyframes artSpin{to{transform:rotate(360deg)}}

/* ── Lang toggle ── */
.art-lang{display:flex;gap:6px;margin-bottom:12px}
.art-lang button{padding:6px 14px;border-radius:999px;font-size:12px;font-weight:600;border:1px solid #e2e8f0;background:#fff;color:#475569;cursor:pointer;transition:all .15s}
.art-lang button.active{background:#0d9488;color:#fff;border-color:#0d9488}

/* ── CTA strip ── */
.art-cta-strip{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px}
.art-cta{display:inline-flex;align-items:center;justify-content:center;gap:5px;padding:11px 14px;border-radius:12px;font-weight:700;font-size:13px;text-decoration:none;border:1px solid transparent}
.art-cta.primary{background:#0d9488;color:#fff}
.art-cta.primary:hover{background:#0f766e}
.art-cta.secondary{background:#fff;color:#0b1220;border-color:#e2e8f0}
.art-cta.secondary:hover{background:#f8fafc}

/* ── Legal ── */
.art-legal{margin-top:16px;padding:12px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;font-size:11px;color:#64748b;line-height:1.6}
.art-legal a{color:#0d9488;font-weight:600}
</style>

<div class="art-wrap">
  <a href="/news.php" class="art-back ne"><i data-lucide="arrow-left" class="w-4 h-4"></i> समाचारमा फर्किनुहोस्</a>

  <!-- ══ SOURCE ATTRIBUTION — always first, always prominent ══════════════ -->
  <div class="art-srcbar" style="--src-color:<?= htmlspecialchars($srcMeta['color']) ?>">
    <div class="logo"><?= htmlspecialchars(mb_substr($srcMeta['name'], 0, 1, 'UTF-8')) ?></div>
    <div class="meta">
      <div class="name ne"><?= htmlspecialchars($srcMeta['name']) ?></div>
      <div class="lic ne">© <?= date('Y') ?> <?= htmlspecialchars($srcMeta['name']) ?> — मूल प्रकाशक</div>
    </div>
  </div>

  <h1 class="art-title ne"><?= htmlspecialchars($title) ?></h1>

  <?php if ($pub): ?>
    <div class="art-meta ne">
      <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
      <?= htmlspecialchars(date('Y-m-d g:i A', strtotime($pub))) ?>
      · <span style="color:var(--src-color,#0d9488);font-weight:600"><?= htmlspecialchars($srcMeta['name']) ?></span>
    </div>
  <?php endif; ?>

  <?php if ($img): ?>
    <div class="art-hero"><img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($title) ?>" onerror="this.parentNode.style.display='none'"></div>
  <?php endif; ?>

  <!-- ══ LANG TOGGLE ══════════════════════════════════════════════════════ -->
  <div class="art-lang">
    <button id="btn-ne" class="active" onclick="loadContent('ne')">🇳🇵 नेपाली</button>
    <button id="btn-en" onclick="loadContent('en')">🇬🇧 English</button>
  </div>

  <!-- ══ FULL CONTENT — Server-side loaded or AI-expanded ══════════════════════════ -->
  <div class="art-ai-wrap" id="art-ai-wrap">
    <div class="art-ai-head">
      <span class="label">
        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
        <?= htmlspecialchars($srcMeta['name']) ?> बाट समाचार
      </span>
      <span class="src-badge" style="background:<?= htmlspecialchars($srcMeta['color']) ?>">
        <i data-lucide="link" class="w-3 h-3"></i>
        <?= htmlspecialchars($srcMeta['name']) ?>
      </span>
    </div>
    <div class="art-ai-body ne" id="art-ai-body" data-loaded="<?= $hasFullContent ? '1' : '0' ?>">
      <?php if ($hasFullContent): ?>
        <!-- Full content available from database -->
        <div class="db-content">
          <?= formatNewsContent($dbContent) ?>
        </div>
      <?php else: ?>
        <!-- Need to load via AI API -->
        <div class="art-spinner">
          <div class="spin"></div>
          <span>समाचार लोड गर्दैछ…</span>
        </div>
      <?php endif; ?>
    </div>
    <div class="art-ai-footer">
      <span class="src-note ne">
        <i data-lucide="info" class="w-3.5 h-3.5"></i>
        मूल स्रोत: <?= htmlspecialchars($srcMeta['name']) ?> — यो सामग्री आकाशवाणीमा पढ्न मिल्ने गरी तयार पारिएको
      </span>
      <a href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener" class="read-full ne" style="text-decoration:none;color:inherit;">
        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
        मूल स्रोतमा पढ्नुहोस्
      </a>
    </div>
  </div>

  <!-- ══ LEGAL NOTICE ═════════════════════════════════════════════════ -->
  <div class="art-legal ne">
    <strong>📜 कानूनी सूचना:</strong>
    यो सामग्री <?= htmlspecialchars($srcMeta['name']) ?> स्रोतबाट प्राप्त समाचारमा आधारित छ। स्रोत स्पष्ट रूपमा <strong><?= htmlspecialchars($srcMeta['name']) ?></strong> मार्फत आकाशवाणीमा पढ्न मिल्ने गरी तयार पारिएको।
    <a href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener" style="margin-left:12px;">पूर्ण सामग्रीको लागि मूल स्रोतमा जानुहोस्</a>
  </div>
</div>

<script>
(function(){
  var _url    = <?= json_encode($url) ?>;
  var _slug   = <?= json_encode($slug) ?>;
  var _title  = <?= json_encode($title) ?>;
  var _excerpt= <?= json_encode($excerpt) ?>;
  var _srcName= <?= json_encode($srcMeta['name']) ?>;
  var _srcUrl = <?= json_encode($url) ?>;
  var _srcColor = <?= json_encode($srcMeta['color']) ?>;
  var _currentLang = 'ne';

  function setLangButtons(lang) {
    document.getElementById('btn-ne').className = lang==='ne' ? 'active' : '';
    document.getElementById('btn-en').className = lang==='en' ? 'active' : '';
  }

  window.loadContent = function(lang) {
    var body = document.getElementById('art-ai-body');
    // If content already loaded from server-side (PHP), only update on language switch
    if (body.dataset.loaded === '1' && body.querySelector('.db-content')) {
      // Content loaded from database, no need to fetch again
      // Just update the UI language if needed
      _currentLang = lang;
      setLangButtons(lang);
      return;
    }
    // Skip if same language and already loaded
    if (_currentLang === lang && body.dataset.loaded === '1') return;
    _currentLang = lang;
    setLangButtons(lang);

    body.dataset.loaded = '0';
    body.innerHTML = '<div class="art-spinner"><div class="spin"></div><span>'+(lang==='ne'?'समाचार लोड गर्दैछ…':'Loading article…')+'</span></div>';

    fetch('/api/news-expand.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({title: _title, slug: _slug, excerpt: _excerpt, lang: lang})
    })
    .then(function(r){ return r.json(); })
    .then(function(d) {
      if (d && d.content) {
        body.innerHTML = d.content;
        body.dataset.loaded = '1';
        // Update footer source label dynamically
        var srcNote = document.querySelector('.art-ai-footer .src-note');
        if (srcNote) {
          var badge = document.querySelector('.art-ai-head .src-badge');
          if (badge) badge.style.background = _srcColor;
        }
      } else {
        body.innerHTML = showExcerpt(lang);
        body.dataset.loaded = '1';
      }
      if (window.lucide && lucide.createIcons) lucide.createIcons();
    })
    .catch(function() {
      body.innerHTML = showExcerpt(lang);
      body.dataset.loaded = '1';
    });
  };

  function showExcerpt(lang) {
    if (_excerpt && _excerpt.length > 20) {
      var note = lang==='ne'
        ? '<div class="excerpt-fallback">⚠ विस्तृत विवरण अहिले उपलब्ध छैन। यो '+esc(_srcName)+' बाट sync गरिएको सारांश हो। पूर्ण समाचार मूल स्रोतमा पढ्नुहोस्।</div>'
        : '<div class="excerpt-fallback">⚠ Detailed content is currently unavailable. This is a summary from '+esc(_srcName)+'. Please read the full article from the original source.</div>';
      return '<p class="leading-relaxed mb-4">' + esc(_excerpt) + '</p>' + note;
    }
    return '<div class="excerpt-fallback">'+(lang==='ne'?'समाचारको विस्तृत विवरण अहिले उपलब्ध छैन। मूल स्रोतमा पढ्नुहोस्।':'Detailed content is unavailable. Please read from the original source.')+'</div>';
  }

  function esc(s){ return String(s||'').replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }

  // Auto-load on page open (only if not already loaded from server-side)
  var _initialBody = document.getElementById('art-ai-body');
  if (_initialBody.dataset.loaded !== '1') {
    loadContent('ne');
  } else {
    // Content already loaded from PHP, just set button state
    setLangButtons('ne');
  }

  if (window.lucide && lucide.createIcons) lucide.createIcons();
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
