<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) { header('Location: /news.php'); exit; }

$news = getNewsBySlug($slug);
if (!$news) { http_response_code(404); header('Location: /news.php'); exit; }

$related = getRelatedNews($news['id'], $news['category'], 6);
$latest  = getPublishedNews(null, null, 8, 0);
$latest  = array_slice(array_values(array_filter($latest, fn($n) => $n['id'] !== $news['id'])), 0, 6);

$siteLang  = siteLang();
$t         = fn(string $ne, string $en) => $siteLang === 'ne' ? $ne : $en;

$pageTitle = h($news['title']) . ' | ' . SITE_NAME;
$pageDesc  = h($news['excerpt'] ?: mb_substr(strip_tags($news['content'] ?? ''), 0, 160));
$pageUrl   = SITE_URL . '/news-post.php?slug=' . urlencode($slug);
$pageImg   = $news['image_url'] ?: OG_IMAGE;

// Read-time estimate
$wordCount = str_word_count(strip_tags(($news['content'] ?? '') . ' ' . ($news['excerpt'] ?? '')));
$readMins  = max(1, (int)ceil($wordCount / 200));

// Source favicon helper
function newsSourceFavicon(?string $sourceName, ?string $originalUrl = null): string {
    $domain = '';
    if ($originalUrl) {
        $host = parse_url($originalUrl, PHP_URL_HOST);
        if ($host) $domain = preg_replace('/^www\./i', '', (string)$host);
    }
    if (!$domain && $sourceName) {
        static $map = [
            'Onlinekhabar'  => 'onlinekhabar.com',
            'Setopati'      => 'setopati.com',
            'Ratopati'      => 'ratopati.com',
            'Gorkhapatra'   => 'gorkhapatraonline.com',
            'Nagarik'       => 'nagariknews.nagariknetwork.com',
            'My Republica'  => 'myrepublica.nagariknetwork.com',
            'Nepali Times'  => 'nepalitimes.com',
        ];
        $domain = $map[trim($sourceName)] ?? '';
    }
    if (!$domain) return '';
    return 'https://www.google.com/s2/favicons?domain=' . urlencode($domain) . '&sz=32';
}

include __DIR__ . '/header.php';
?>

<style>
/* ── Article body ── */
.article-body { color:#1e293b; font-size:1.0625rem; line-height:1.9; font-family:'Hind Siliguri',sans-serif; }
.article-body p { margin-bottom:1.3em; }
.article-body h2,.article-body h3 { color:#0f172a; font-weight:700; margin:1.8em 0 .6em; }
.article-body h2 { font-size:1.35rem; border-bottom:1px solid #e2e8f0; padding-bottom:.4em; }
.article-body h3 { font-size:1.15rem; }
.article-body strong,
.article-body b    { color:#0f172a; font-weight:600; }
.article-body a    { color:#0f766e; text-decoration:underline; text-underline-offset:3px; }
.article-body blockquote {
  border-left:3px solid #0f766e; padding:.9em 1.2em;
  background:rgba(15,118,110,.07); margin:1.6em 0;
  border-radius:0 8px 8px 0; color:#0f172a; font-style:italic;
}
.article-body ul,.article-body ol { padding-left:1.5em; margin-bottom:1.1em; }
.article-body li { margin-bottom:.45em; }
.article-body img { border-radius:8px; margin:1em 0; width:100%; }
@media (min-width:768px){ .article-body { font-size:1.125rem; line-height:1.95; } }

/* No-content fallback */
.no-content-box {
  background:#f8fafc; border:1px dashed #cbd5e1;
  border-radius:12px; padding:2rem; text-align:center;
  color:#64748b; margin-bottom:1.5rem;
}

/* Share buttons */
.share-btn {
  display:inline-flex; align-items:center; justify-content:center;
  width:34px; height:34px; border-radius:8px; font-size:.8rem;
  transition:all .15s; border:1px solid transparent; position:relative;
}
.share-btn:hover { opacity:.85; transform:translateY(-2px); }
.share-btn::after {
  content:attr(data-tooltip);
  position:absolute; bottom:calc(100% + 7px); left:50%;
  transform:translateX(-50%); padding:5px 9px;
  background:#0f172a; color:#fff; font-size:11px; font-weight:500;
  white-space:nowrap; border-radius:6px;
  opacity:0; visibility:hidden; transition:all .15s; pointer-events:none;
}
.share-btn:hover::after { opacity:1; visibility:visible; }

/* Sidebar card */
.side-card { display:flex; gap:10px; padding:10px 0; border-bottom:1px solid #e2e8f0; }
.side-card:last-child { border-bottom:none; }
.side-card-img { width:68px; height:50px; object-fit:cover; border-radius:6px; flex-shrink:0; }

/* AI expand */
#ai-expanded { animation:fadeIn .35s ease; }
@keyframes fadeIn { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:none} }
</style>

<!-- Breadcrumb -->
<nav class="border-b border-slate-200 bg-slate-50">
  <div class="max-w-7xl mx-auto px-4 py-2.5 flex items-center gap-1.5 text-xs text-slate-500 flex-wrap">
    <a href="/" class="hover:text-teal-600 transition-colors">🏠 <?= $t('गृहपृष्ठ','Home') ?></a>
    <span class="text-slate-300">›</span>
    <a href="/news.php" class="hover:text-teal-600 transition-colors"><?= $t('समाचार','News') ?></a>
    <span class="text-slate-300">›</span>
    <span class="text-slate-700 truncate max-w-[260px]"><?= h(mb_substr($news['title'], 0, 55)) ?>…</span>
  </div>
</nav>

<div class="max-w-7xl mx-auto px-4 py-6">
  <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_300px] gap-8">

    <!-- ── Main Article ──────────────────────────────────────────── -->
    <main class="max-w-3xl mx-auto w-full">

      <!-- Article Header -->
      <header class="mb-5">
        <div class="flex flex-wrap items-center gap-2 mb-3">
          <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wide"
                style="background:rgba(15,118,110,.1);color:#0f766e;border:1px solid rgba(15,118,110,.3);">
            <?= h($news['category']) ?>
          </span>
          <?php if (!empty($news['is_breaking'])): ?>
          <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wide"
                style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;">
            🔴 <?= $t('ब्रेकिङ','BREAKING') ?>
          </span>
          <?php endif; ?>
          <?php
            $srcName = $news['source_name'] ?? ($news['source'] ?? '');
            $srcUrl  = $news['original_url'] ?? '';
            $srcIcon = newsSourceFavicon($srcName, $srcUrl);
            if ($srcName || $srcIcon):
          ?>
          <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs text-slate-600 bg-slate-100 border border-slate-200">
            <?php if ($srcIcon): ?>
              <img src="<?= h($srcIcon) ?>" alt="" class="w-3.5 h-3.5 rounded" onerror="this.remove()" loading="lazy">
            <?php endif; ?>
            <?php if ($srcUrl): ?>
              <a href="<?= h($srcUrl) ?>" target="_blank" rel="noopener noreferrer" class="hover:text-teal-600 transition-colors"><?= h($srcName) ?></a>
            <?php else: ?>
              <span><?= h($srcName) ?></span>
            <?php endif; ?>
          </span>
          <?php endif; ?>
          <span class="text-xs text-slate-500">
            📅 <?= function_exists('bsDate') ? bsDate($news['created_at']) : date('Y-m-d', strtotime($news['created_at'])) ?>
          </span>
          <span class="text-xs text-slate-500">
            ⏱ <?= $readMins ?> <?= $t('मिनेट पढाइ','min read') ?>
          </span>
          <span class="text-xs text-slate-500">
            👁 <?= number_format((int)($news['views'] ?? 0)) ?>
          </span>
        </div>

        <!-- Title -->
        <h1 class="text-xl md:text-2xl lg:text-3xl font-bold text-slate-900 leading-tight mb-4"
            style="font-family:'Mukta',sans-serif;">
          <?= h($news['title']) ?>
        </h1>

        <!-- Lead paragraph (excerpt) -->
        <?php $lead = trim($news['excerpt'] ?? ''); if ($lead): ?>
        <div class="border-l-4 border-[#0f766e] pl-4 mb-5 bg-teal-50/50 rounded-r-xl py-3 pr-4">
          <p class="text-slate-700 text-sm leading-relaxed font-medium m-0"><?= h($lead) ?></p>
        </div>
        <?php endif; ?>
      </header>

      <!-- Featured Image (no source caption) -->
      <?php if (!empty($news['image_url'])): ?>
      <figure class="mb-6 rounded-xl overflow-hidden border border-slate-200">
        <img src="<?= h($news['image_url']) ?>"
             alt="<?= h($news['title']) ?>"
             class="w-full object-cover max-h-[440px]"
             loading="lazy"
             onerror="this.closest('figure').style.display='none';" />
      </figure>
      <?php endif; ?>

      <!-- ── Article Body ──────────────────────────────────────── -->
      <article class="article-body mb-6">
        <?php
        $content = trim($news['content'] ?? '');
        if ($content) {
            // Has HTML tags → sanitise and render
            if (preg_match('/<[a-z][\s\S]*>/i', $content)) {
                $allowed = '<p><br><b><strong><i><em><u><h2><h3><h4><ul><ol><li><blockquote><span>';
                echo strip_tags($content, $allowed);
            } else {
                // Plain text → smart paragraph splitting
                $text = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                // Try double-newline split (stored as "\n\n" by sync)
                $paras = preg_split('/\n\n+/u', $text);
                if (count($paras) === 1) {
                    // Sentence-level grouping (2 sentences per paragraph)
                    $sentences = preg_split('/(?<=[।॥!?.])\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
                    $chunks = array_chunk($sentences, 2);
                    $paras = array_map(fn($c) => implode(' ', $c), $chunks);
                }
                foreach ($paras as $p) {
                    $p = trim($p);
                    if (mb_strlen($p) > 10) echo '<p>' . h($p) . '</p>' . "\n";
                }
            }
        } else {
            // No full content — show excerpt gracefully
            $fallback = trim($news['excerpt'] ?? '');
            if ($fallback) {
                $sentences = preg_split('/(?<=[।॥!?.])\s+/u', $fallback, -1, PREG_SPLIT_NO_EMPTY);
                $chunks = array_chunk($sentences, 2);
                foreach ($chunks as $chunk) {
                    $p = trim(implode(' ', $chunk));
                    if ($p) echo '<p>' . h($p) . '</p>' . "\n";
                }
            } else {
                echo '<div class="no-content-box">
                    <p class="text-2xl mb-2">📰</p>
                    <p>' . $t('यो समाचारको विस्तृत विवरण उपलब्ध छैन।','Full article content is not available.') . '</p>
                </div>';
            }
        }
        ?>
      </article>

      <!-- AI Expand (only interactive feature — no source link) -->
      <div class="mb-6 flex flex-wrap items-center gap-2">
        <button type="button" onclick="toggleAi()" id="ai-toggle"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition"
                style="background:rgba(15,118,110,.1);color:#0f766e;border:1px solid rgba(15,118,110,.3);">
          🤖 <?= $t('AI ले विस्तार गर','Expand with AI') ?>
        </button>
      </div>

      <!-- AI Expand panel -->
      <div id="ai-expand-wrap" class="hidden mb-6 bg-slate-50 border border-slate-200 rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-4 py-2.5 border-b border-slate-200">
          <span class="text-xs font-semibold text-slate-600">🤖 AI विस्तृत विवरण</span>
          <div class="flex items-center gap-1.5">
            <button onclick="expandNews('ne')" id="btn-ne"
                    class="px-2.5 py-1 rounded text-[11px] font-semibold"
                    style="background:rgba(15,118,110,.1);color:#0f766e;border:1px solid rgba(15,118,110,.25);">नेपाली</button>
            <button onclick="expandNews('en')" id="btn-en"
                    class="px-2.5 py-1 rounded text-[11px] font-semibold"
                    style="background:rgba(59,130,246,.1);color:#3b82f6;border:1px solid rgba(59,130,246,.25);">English</button>
          </div>
        </div>
        <div id="ai-expand-body" class="px-4 py-4 text-sm text-slate-600">
          <span class="text-slate-400 text-xs"><?= $t('माथिको भाषा छान्नुस् — AI ले विस्तृत विवरण तयार गर्नेछ।','Choose language above to get AI analysis.') ?></span>
        </div>
      </div>

      <!-- Share Bar -->
      <div class="mb-8 pt-4 border-t border-slate-100">
        <div class="flex items-center gap-2 flex-wrap">
          <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mr-1"><?= $t('सेयर गर्नुस्','Share') ?></span>
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($pageUrl) ?>" target="_blank" rel="noopener"
             class="share-btn" style="background:#1877f2;color:#fff;" data-tooltip="Facebook">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
          </a>
          <a href="https://wa.me/?text=<?= rawurlencode($news['title'] . "\n" . $pageUrl) ?>" target="_blank" rel="noopener"
             class="share-btn" style="background:#25d366;color:#fff;" data-tooltip="WhatsApp">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
          </a>
          <a href="https://twitter.com/intent/tweet?text=<?= rawurlencode($news['title']) ?>&url=<?= rawurlencode($pageUrl) ?>" target="_blank" rel="noopener"
             class="share-btn" style="background:#000;color:#fff;" data-tooltip="X / Twitter">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.748l7.738-8.835L1.254 2.25H8.08l4.264 5.633 5.9-5.633zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
          </a>
          <a href="viber://forward?text=<?= rawurlencode($news['title'] . ' ' . $pageUrl) ?>"
             class="share-btn" style="background:#7360f2;color:#fff;" data-tooltip="Viber">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11.398.002C9.473.028 5.141.344 3.068 2.303 1.685 3.687.862 5.785.717 8.623.571 11.46.512 16.64 5.21 18.08v2.417s-.032.984.611 1.186c.776.244 1.234-.499 1.976-1.293.407-.436.969-1.076 1.394-1.566 3.844.324 6.803-.417 7.141-.528.779-.256 5.188-.818 5.907-6.678.741-6.027-.356-9.834-2.332-11.526C18.062.488 15.04-.051 11.398.002z"/></svg>
          </a>
          <button onclick="copyLink()" id="copyBtn"
                  class="share-btn" style="background:#f1f5f9;color:#64748b;border-color:#e2e8f0;" data-tooltip="<?= $t('लिंक कपी','Copy link') ?>">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
          </button>
        </div>
      </div>

      <!-- Related News -->
      <?php if (!empty($related)): ?>
      <section>
        <h2 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-200 flex items-center gap-2">
          <span class="text-teal-600">■</span> <?= $t('सम्बन्धित समाचार','Related News') ?>
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <?php foreach ($related as $r): ?>
          <a href="/news-post.php?slug=<?= urlencode($r['slug']) ?>"
             class="group flex gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 hover:border-[#0f766e]/40 transition-all">
            <?php if ($r['image_url']): ?>
            <div class="w-20 h-[58px] rounded-lg overflow-hidden flex-shrink-0">
              <img src="<?= h($r['image_url']) ?>" alt="" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity" loading="lazy" onerror="this.parentElement.remove()" />
            </div>
            <?php else: ?>
            <div class="w-20 h-[58px] rounded-lg bg-slate-100 flex-shrink-0 flex items-center justify-center text-xl">📰</div>
            <?php endif; ?>
            <div class="flex-1 min-w-0">
              <div class="text-[10px] text-slate-400 mb-0.5"><?= function_exists('bsDate') ? bsDate($r['created_at']) : date('M j, Y', strtotime($r['created_at'])) ?></div>
              <h4 class="text-sm font-semibold text-slate-900 group-hover:text-teal-600 transition-colors line-clamp-2 leading-snug"><?= h($r['title']) ?></h4>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

      <div class="mt-8 pt-4 border-t border-slate-200">
        <a href="/news.php" class="inline-flex items-center gap-1.5 text-sm text-teal-600 hover:underline font-semibold">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
          <?= $t('सबै समाचार हेर्नुस्','All news') ?>
        </a>
      </div>
    </main>

    <!-- ── Sidebar ────────────────────────────────────────────────── -->
    <aside class="space-y-5 lg:sticky lg:top-20 self-start">

      <!-- Latest News -->
      <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3 pb-2 border-b border-slate-200">
          🔴 <?= $t('ताजा समाचार','Latest News') ?>
        </h3>
        <div>
          <?php foreach ($latest as $l): ?>
          <a href="/news-post.php?slug=<?= urlencode($l['slug']) ?>" class="side-card group">
            <?php if ($l['image_url']): ?>
            <img src="<?= h($l['image_url']) ?>" alt="" class="side-card-img" loading="lazy" onerror="this.remove()" />
            <?php else: ?>
            <div class="w-[68px] h-[50px] rounded-lg bg-slate-100 flex-shrink-0 flex items-center justify-center">📰</div>
            <?php endif; ?>
            <div class="flex-1 min-w-0">
              <h5 class="text-xs font-semibold text-slate-900 group-hover:text-teal-600 transition-colors line-clamp-3 leading-snug"><?= h($l['title']) ?></h5>
              <span class="text-[10px] text-slate-400 mt-0.5 block"><?= function_exists('bsDate') ? bsDate($l['created_at']) : date('M j', strtotime($l['created_at'])) ?></span>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
        <a href="/news.php" class="block mt-3 text-center text-xs font-semibold text-teal-600 py-1.5 rounded border border-[#0f766e]/30 hover:bg-teal-600/10 transition-all"><?= $t('सबै समाचार →','All news →') ?></a>
      </div>

      <!-- Categories -->
      <?php $cats = getNewsCategories(); if (!empty($cats)): ?>
      <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">📂 <?= $t('श्रेणीहरू','Categories') ?></h3>
        <div class="space-y-1">
          <?php foreach ($cats as $c): ?>
          <a href="/news.php?cat=<?= urlencode($c['category']) ?>"
             class="flex items-center justify-between py-1.5 px-2 rounded-lg text-sm transition-all hover:bg-slate-100 <?= $c['category'] === $news['category'] ? 'text-teal-600 font-semibold bg-slate-100' : 'text-slate-500' ?>">
            <span><?= h($c['category']) ?></span>
            <span class="text-xs bg-slate-200 px-1.5 py-0.5 rounded"><?= (int)$c['c'] ?></span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

    </aside>
  </div>
</div>

<script>
const pageUrl = <?= json_encode($pageUrl) ?>;
const newsData = {
  title:   <?= json_encode($news['title']) ?>,
  excerpt: <?= json_encode($news['excerpt'] ?? '') ?>,
};
let expandedLang = null;

function toggleAi() {
  const w = document.getElementById('ai-expand-wrap');
  w.classList.toggle('hidden');
  if (!w.classList.contains('hidden') && !expandedLang) expandNews('ne');
}

async function expandNews(lang) {
  if (expandedLang === lang) return;
  const body = document.getElementById('ai-expand-body');
  body.innerHTML = '<div class="flex items-center gap-3 text-slate-500 text-sm"><span class="animate-spin inline-block w-4 h-4 border-2 border-[#0f766e] border-t-transparent rounded-full"></span> <?= $t('AI विश्लेषण गर्दैछ…','AI is analysing…') ?></div>';
  try {
    const res = await fetch('/api/news-expand.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({title: newsData.title, excerpt: newsData.excerpt, lang}),
    });
    const data = await res.json();
    if (data.content) {
      expandedLang = lang;
      body.innerHTML = `<div id="ai-expanded" class="article-body text-sm">${data.content}</div>`;
      ['btn-ne','btn-en'].forEach(id => document.getElementById(id).style.opacity = '0.5');
      document.getElementById('btn-' + lang).style.opacity = '1';
    } else {
      body.innerHTML = '<p class="text-red-500 text-sm">⚠ <?= $t('AI service अहिले उपलब्ध छैन।','AI service unavailable.') ?></p>';
    }
  } catch(e) {
    body.innerHTML = '<p class="text-red-500 text-sm">⚠ ' + e.message + '</p>';
  }
}

function copyLink() {
  navigator.clipboard?.writeText(pageUrl).then(() => {
    const btn = document.getElementById('copyBtn');
    btn.setAttribute('data-tooltip', '✓ Copied!');
    btn.style.color = '#0f766e';
    setTimeout(() => { btn.setAttribute('data-tooltip','<?= $t('लिंक कपी','Copy link') ?>'); btn.style.color = '#64748b'; }, 2000);
  });
}
</script>

<?php include __DIR__ . '/footer.php'; ?>
