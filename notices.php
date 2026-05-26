<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

try { seedDefaultNotices(); } catch(Exception $e) {}
try { maybeRefreshNotices(); } catch(Exception $e) { error_log('[NSH] Notice sync: '.$e->getMessage()); }

$category   = trim($_GET['cat'] ?? '');
$importance = trim($_GET['imp'] ?? '');

$notices    = getPublishedNotices($category ?: null, $importance ?: null);
$allNotices = getPublishedNotices();
$cats       = getNoticeCategories();
$sync       = getSyncStatus();

// Count by importance
$urgentCount    = count(array_filter($allNotices, fn($n) => $n['importance'] === 'urgent'));
$importantCount = count(array_filter($allNotices, fn($n) => $n['importance'] === 'important'));
$normalCount    = count(array_filter($allNotices, fn($n) => $n['importance'] === 'normal'));

// Source mapping: label => website
$sourceLinks = [
    'OnlineKhabar'    => 'https://english.onlinekhabar.com',
    'NepalNews'       => 'https://www.nepalnews.com',
    'MyRepublica'     => 'https://myrepublica.nagariknetwork.com',
    'The Rising Nepal'=> 'https://risingnepaldaily.com',
    'Kathmandu Post'  => 'https://kathmandupost.com',
    'ShareSansar'     => 'https://sharesansar.com',
    'MeroLagani'      => 'https://merolagani.com',
    'TechSansar'      => 'https://techsansar.com',
    'TechLekha'       => 'https://techlekha.com',
    'TechPana'        => 'https://techpana.com',
    'NRB'             => 'https://www.nrb.org.np',
    'PSC'             => 'https://www.psc.gov.np',
    'DoFP'            => 'https://www.dofp.gov.np',
    'eSewa'           => 'https://esewa.com.np',
    'Khalti'          => 'https://khalti.com',
    'NTC'             => 'https://www.ntc.net.np',
    'GitHub'          => 'https://github.com',
    'OpenAI'          => 'https://openai.com',
];

$pageTitle = t('सूचनाहरू','Notices') . ' | ' . SITE_NAME;
$pageDesc  = 'Nepal सरकार, वित्त, प्रविधि र अन्य क्षेत्रका महत्त्वपूर्ण सूचनाहरू। OnlineKhabar, NepalNews, Kathmandu Post, ShareSansar बाट auto-sync।';
$pageUrl   = SITE_URL . '/notices.php';
include __DIR__ . '/header.php';
?>

<!-- Hero Banner -->
<section class="border-b border-[#e2e8f0] py-10" style="background:linear-gradient(135deg,#fafaf9 0%,#ffffff 60%,#1a2332 100%)">
  <div class="max-w-7xl mx-auto px-4">
    <!-- Live badge row -->
    <div class="flex flex-wrap items-center gap-2 mb-4">
      <div class="inline-flex items-center gap-2 bg-[#f5f5f4] border border-[#ea580c]/60 px-3 py-1.5 rounded-full text-xs font-mono">
        <span class="w-2 h-2 rounded-full bg-[#ea580c] animate-pulse"></span>
        <span class="text-[#ea580c] font-semibold"><?= t('Live Auto-Sync','Live Auto-Sync') ?></span>
      </div>
      <span class="text-[10px] font-mono text-[#64748b] bg-[#f5f5f4] border border-[#e2e8f0] px-2.5 py-1 rounded-full">
        🔄 <?= t('हर २ घण्टामा नया सूचना','Refreshes every 2 hrs') ?>
      </span>
      <?php if ($sync['notices'] !== 'Never'): ?>
      <span class="text-[10px] font-mono text-[#64748b]">
        ⏱ <?= t('अन्तिम sync','Last sync') ?>: <span class="text-[#14b8a6]"><?= $sync['notices'] ?></span>
      </span>
      <?php endif; ?>
    </div>

    <h1 class="text-3xl md:text-4xl font-black text-[#0f172a] tracking-tight mb-2">
      🔔 <?= t('सूचनाहरू', 'Notices & Announcements') ?>
    </h1>
    <p class="text-[#64748b] text-sm max-w-2xl leading-relaxed mb-5">
      <?= t('OnlineKhabar, Kathmandu Post, NepalNews, ShareSansar, TechSansar र अन्य प्रमाणित स्रोतहरूबाट नेपालका सरकारी, वित्त र प्रविधि सूचनाहरू स्वचालित रूपमा sync।',
           'Government, finance & tech notices auto-synced from OnlineKhabar, Kathmandu Post, NepalNews, ShareSansar, TechSansar & more.') ?>
    </p>

    <!-- Stats row -->
    <div class="flex flex-wrap gap-3">
      <a href="/notices.php" class="group flex items-center gap-2 bg-[#f5f5f4] border border-[#e2e8f0] rounded-lg px-3 py-2 hover:border-[#0f766e] transition-colors">
        <span class="text-lg font-black text-[#0f172a] group-hover:text-[#14b8a6]"><?= count($allNotices) ?></span>
        <span class="text-xs text-[#64748b] font-mono"><?= t('जम्मा सूचना','Total') ?></span>
      </a>
      <a href="/notices.php?imp=urgent" class="group flex items-center gap-2 bg-[#f5f5f4] border border-[#dc2626]/30 rounded-lg px-3 py-2 hover:border-[#dc2626] transition-colors">
        <span class="text-lg font-black text-[#dc2626]"><?= $urgentCount ?></span>
        <span class="text-xs text-[#64748b] font-mono">🚨 <?= t('अत्यावश्यक','Urgent') ?></span>
      </a>
      <a href="/notices.php?imp=important" class="group flex items-center gap-2 bg-[#f5f5f4] border border-[#ea580c]/30 rounded-lg px-3 py-2 hover:border-[#ea580c] transition-colors">
        <span class="text-lg font-black text-[#ea580c]"><?= $importantCount ?></span>
        <span class="text-xs text-[#64748b] font-mono">⚠️ <?= t('महत्त्वपूर्ण','Important') ?></span>
      </a>
      <a href="/notices.php?imp=normal" class="group flex items-center gap-2 bg-[#f5f5f4] border border-[#0284c7]/30 rounded-lg px-3 py-2 hover:border-[#0284c7] transition-colors">
        <span class="text-lg font-black text-[#0284c7]"><?= $normalCount ?></span>
        <span class="text-xs text-[#64748b] font-mono">ℹ️ <?= t('सामान्य','Normal') ?></span>
      </a>
    </div>
  </div>
</section>

<div class="max-w-7xl mx-auto px-4 py-8 grid grid-cols-1 lg:grid-cols-[1fr_260px] gap-8">

  <!-- Main Content -->
  <div>

    <!-- LIVE Traffic Notices strip (Nepal Police / MTPD) — v12 -->
    <div class="bg-white border border-[#fed7aa] rounded-xl mb-5 overflow-hidden">
      <div class="bg-gradient-to-r from-[#fff7ed] to-[#ffedd5] px-4 py-2.5 flex items-center gap-2 border-b border-[#fed7aa]">
        <span class="inline-flex items-center gap-1.5 bg-[#ea580c] text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
          <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> LIVE
        </span>
        <span class="text-[13px] font-bold text-[#9a3412]">🚦 ट्राफिक अपडेट</span>
        <a href="https://traffic.nepalpolice.gov.np/" target="_blank" rel="noopener"
           class="ml-auto text-[10px] font-mono text-[#9a3412] bg-white border border-[#fed7aa] px-2 py-0.5 rounded">
          स्रोत: MTPD ↗
        </a>
      </div>
      <div id="trf-list" class="divide-y divide-[#fef3c7] text-[12.5px]">
        <div class="p-3 text-slate-400">ट्राफिक सूचना लोड हुँदै…</div>
      </div>
    </div>

    <!-- Filter tabs -->
    <div class="flex flex-wrap gap-2 mb-6">
      <?php
      $filters = [
        ''          => ['label' => t('सबै','All notices'),           'active' => 'bg-[#0f766e] text-white border-[#0f766e]'],
        'urgent'    => ['label' => '🚨 '.t('अत्यावश्यक','Urgent'),  'active' => 'bg-[#dc2626] text-white border-[#dc2626]'],
        'important' => ['label' => '⚠️ '.t('महत्त्वपूर्ण','Important'), 'active' => 'bg-[#ea580c] text-white border-[#ea580c]'],
        'normal'    => ['label' => 'ℹ️ '.t('सामान्य','Normal'),     'active' => 'bg-[#0284c7] text-white border-[#0284c7]'],
      ];
      foreach ($filters as $val => $meta):
        $isActive = $importance === $val;
        $href = $val
            ? '/notices.php?imp='.$val.($category ? '&cat='.urlencode($category) : '')
            : '/notices.php'.($category ? '?cat='.urlencode($category) : '');
      ?>
      <a href="<?= $href ?>"
         class="px-3 py-1.5 rounded-lg text-xs font-mono font-semibold border transition-colors
           <?= $isActive ? $meta['active'] : 'bg-[#f5f5f4] border-[#e2e8f0] text-[#64748b] hover:text-[#0f172a] hover:border-[#64748b]' ?>">
        <?= $meta['label'] ?>
      </a>
      <?php endforeach; ?>
      <?php if ($category): ?>
      <span class="px-3 py-1.5 rounded-lg text-xs font-mono bg-[#f5f5f4] border border-[#e2e8f0] text-[#64748b] flex items-center gap-1">
        📂 <?= h($category) ?>
        <a href="/notices.php<?= $importance?'?imp='.$importance:'' ?>" class="ml-1 text-[#dc2626] hover:text-[#dc2626] font-bold">✕</a>
      </span>
      <?php endif; ?>
      <span class="ml-auto text-xs font-mono text-[#64748b] self-center"><?= count($notices) ?> <?= t('सूचना','notices') ?></span>
    </div>

    <!-- Notices List -->
    <?php if (empty($notices)): ?>
    <div class="text-center py-20 bg-[#ffffff] border border-dashed border-[#e2e8f0] rounded-xl text-[#64748b] font-mono">
      <p class="text-5xl mb-4">📋</p>
      <p class="font-semibold text-[#0f172a] mb-2"><?= t('कुनै सूचना भेटिएन','No notices found') ?></p>
      <p class="text-sm"><?= t('अर्को sync मा नया सूचनाहरू आउनेछन्।','New notices will appear on the next sync.') ?></p>
      <?php if ($category || $importance): ?>
      <a href="/notices.php" class="mt-4 inline-block text-[#14b8a6] hover:underline text-sm">← <?= t('सबै सूचना हेर्नुस्','View all notices') ?></a>
      <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="space-y-3">
      <?php foreach ($notices as $n):
        $imp = $n['importance'];
        [$leftBar, $badgeCls, $badgeLabel, $borderCls, $headerBg] = match($imp) {
          'urgent'    => ['bg-[#dc2626]',  'bg-[#dc2626]/20 text-[#dc2626] border border-[#dc2626]/40',   '🚨 '.t('अत्यावश्यक','Urgent'),    'border-[#dc2626]/30 hover:border-[#dc2626]/60', 'bg-[#dc2626]/5'],
          'important' => ['bg-[#ea580c]',  'bg-[#ea580c]/20 text-[#fb923c] border border-[#ea580c]/40',   '⚠️ '.t('महत्त्वपूर्ण','Important'), 'border-[#ea580c]/30 hover:border-[#ea580c]/60', 'bg-[#ea580c]/5'],
          default     => ['bg-[#0284c7]',  'bg-[#0284c7]/20 text-[#38bdf8] border border-[#0284c7]/40',   'ℹ️ '.t('सूचना','Notice'),           'border-[#e2e8f0] hover:border-[#64748b]',         'bg-[#f5f5f4]/40'],
        };
        // Source URL: prefer stored source_url, then lookup by source name
        $srcUrl = $n['source_url'] ?? '';
        if (!$srcUrl && $n['source'] && isset($sourceLinks[$n['source']])) {
            $srcUrl = $sourceLinks[$n['source']];
        }
        $srcDomain = $srcUrl ? parse_url($srcUrl, PHP_URL_HOST) : '';
        $faviconUrl = $srcDomain ? 'https://www.google.com/s2/favicons?domain='.$srcDomain.'&sz=16' : '';
        // Format date in Nepali
        $dateTs = strtotime($n['created_at']);
        $dateAD = date('Y M j', $dateTs);
        // Time ago
        $diff = time() - $dateTs;
        if ($diff < 3600)      $ago = floor($diff/60).' '.t('मिनेट पहिले','min ago');
        elseif ($diff < 86400) $ago = floor($diff/3600).' '.t('घण्टा पहिले','hrs ago');
        elseif ($diff < 604800)$ago = floor($diff/86400).' '.t('दिन पहिले','days ago');
        else                   $ago = $dateAD;
      ?>
      <article class="bg-[#ffffff] border <?= $borderCls ?> rounded-xl overflow-hidden transition-all hover:shadow-lg hover:shadow-black/30 flex">
        <!-- Left accent bar -->
        <div class="w-1 shrink-0 <?= $leftBar ?>"></div>

        <div class="flex-1 min-w-0">
          <!-- Card header -->
          <div class="<?= $headerBg ?> border-b border-[#e2e8f0] px-4 py-2.5 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="<?= $badgeCls ?> px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold"><?= $badgeLabel ?></span>
              <a href="/notices.php?cat=<?= urlencode($n['category']) ?><?= $importance?'&imp='.$importance:'' ?>"
                 class="text-[11px] font-mono text-[#64748b] bg-[#f5f5f4] border border-[#e2e8f0] px-2 py-0.5 rounded-full hover:text-[#0f172a] hover:border-[#64748b] transition-colors">
                📂 <?= h($n['category']) ?>
              </a>
            </div>
            <span class="text-[11px] font-mono text-[#64748b]" title="<?= $dateAD ?>"><?= $ago ?></span>
          </div>

          <!-- Card body -->
          <div class="px-4 py-4">
            <h2 class="font-bold text-[#0f172a] text-[15px] leading-snug mb-2"><?= h($n['title']) ?></h2>
            <?php if ($n['content'] && $n['content'] !== $n['title']): ?>
            <p class="text-sm text-[#64748b] leading-relaxed line-clamp-3"><?= h(mb_substr($n['content'], 0, 280)) ?><?= mb_strlen($n['content']) > 280 ? '…' : '' ?></p>
            <?php endif; ?>
          </div>

          <!-- Card footer: Source + actions -->
          <div class="px-4 pb-4 flex flex-wrap items-center justify-between gap-2">
            <!-- Source attribution -->
            <div class="flex items-center gap-2">
              <?php if ($n['source']): ?>
              <?php if ($srcUrl): ?>
              <a href="<?= h($srcUrl) ?>" target="_blank" rel="noopener"
                 class="inline-flex items-center gap-1.5 text-[11px] font-mono text-[#64748b] bg-[#f5f5f4] border border-[#e2e8f0] px-2.5 py-1 rounded-full hover:text-[#14b8a6] hover:border-[#0f766e] transition-colors"
                 title="<?= t('मूल स्रोत','Original source') ?>: <?= h($n['source']) ?>">
                <?php if ($faviconUrl): ?><img src="<?= h($faviconUrl) ?>" alt="" class="w-3.5 h-3.5" loading="lazy"><?php else: ?>📡<?php endif; ?>
                <span><?= h($n['source']) ?></span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 12 12" width="10" height="10" fill="currentColor" class="opacity-60"><path d="M3.5 3a.5.5 0 0 0 0 1H7.3L2.1 9.2a.5.5 0 1 0 .7.7L8 4.7V8.5a.5.5 0 1 0 1 0v-5a.5.5 0 0 0-.5-.5H3.5Z"/></svg>
              </a>
              <?php else: ?>
              <span class="inline-flex items-center gap-1.5 text-[11px] font-mono text-[#64748b] bg-[#f5f5f4] border border-[#e2e8f0] px-2.5 py-1 rounded-full">
                📡 <?= h($n['source']) ?>
              </span>
              <?php endif; ?>
              <?php endif; ?>
            </div>
            <!-- Share -->
            <a href="<?= waShare('📋 '.h($n['title']).' — '.SITE_URL.'/notices.php') ?>" target="_blank"
               class="inline-flex items-center gap-1 text-[11px] font-mono text-[#64748b] hover:text-[#14b8a6] transition-colors">
              💬 <?= t('Share','Share') ?>
            </a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- Sidebar -->
  <aside class="space-y-5 lg:sticky lg:top-20 self-start">

    <!-- Auto-Sync Status Card -->
    <div class="bg-[#ffffff] border border-[#0f766e]/40 rounded-xl p-4">
      <h3 class="text-xs font-bold uppercase tracking-widest text-[#14b8a6] mb-3 flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-[#14b8a6] animate-pulse"></span>
        <?= t('Auto-Sync स्थिति','Auto-Sync Status') ?>
      </h3>
      <div class="space-y-2.5 text-xs font-mono">
        <div class="flex items-center justify-between py-1.5 border-b border-[#e2e8f0]">
          <span class="text-[#64748b]">📰 <?= t('समाचार','News') ?></span>
          <div class="flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-[#14b8a6]"></span>
            <span class="text-[#14b8a6]"><?= t('हर १ घण्टा','Every 1 hr') ?></span>
          </div>
        </div>
        <div class="flex items-center justify-between py-1.5 border-b border-[#e2e8f0]">
          <span class="text-[#64748b]">🔔 <?= t('सूचना','Notices') ?></span>
          <div class="flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-[#14b8a6]"></span>
            <span class="text-[#14b8a6]"><?= t('हर २ घण्टा','Every 2 hrs') ?></span>
          </div>
        </div>
        <div class="flex items-center justify-between py-1.5">
          <span class="text-[#64748b]">🌍 <?= t('भूकम्प','Earthquake') ?></span>
          <div class="flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-[#14b8a6]"></span>
            <span class="text-[#14b8a6]"><?= t('हर ३० मिनेट','Every 30 min') ?></span>
          </div>
        </div>
      </div>
      <?php if ($sync['notices'] !== 'Never'): ?>
      <div class="mt-3 pt-3 border-t border-[#e2e8f0] text-[10px] font-mono text-[#64748b]">
        ⏱ <?= t('अन्तिम sync','Last sync') ?>: <span class="text-[#0f172a]"><?= $sync['notices'] ?></span>
      </div>
      <?php endif; ?>
    </div>

    <!-- Data Sources -->
    <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-xl p-4">
      <h3 class="text-xs font-bold uppercase tracking-widest text-[#64748b] mb-3">
        📡 <?= t('डेटा स्रोतहरू','Data Sources') ?>
      </h3>
      <ul class="space-y-1.5">
        <?php foreach ([
          ['OnlineKhabar',     'https://english.onlinekhabar.com', '🇳🇵'],
          ['Kathmandu Post',   'https://kathmandupost.com',         '🇳🇵'],
          ['NepalNews',        'https://www.nepalnews.com',         '🇳🇵'],
          ['MyRepublica',      'https://myrepublica.nagariknetwork.com','🇳🇵'],
          ['The Rising Nepal', 'https://risingnepaldaily.com',      '🇳🇵'],
          ['ShareSansar',      'https://sharesansar.com',           '📈'],
          ['TechSansar',       'https://techsansar.com',            '💻'],
          ['TechLekha',        'https://techlekha.com',             '💻'],
        ] as [$name, $url, $flag]): ?>
        <li>
          <a href="<?= $url ?>" target="_blank" rel="noopener"
             class="flex items-center gap-2 text-xs font-mono text-[#64748b] hover:text-[#14b8a6] transition-colors py-1">
            <span><?= $flag ?></span>
            <span><?= $name ?></span>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 12 12" width="9" height="9" fill="currentColor" class="ml-auto opacity-40"><path d="M3.5 3a.5.5 0 0 0 0 1H7.3L2.1 9.2a.5.5 0 1 0 .7.7L8 4.7V8.5a.5.5 0 1 0 1 0v-5a.5.5 0 0 0-.5-.5H3.5Z"/></svg>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <!-- Categories -->
    <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-xl p-4">
      <h3 class="text-xs font-bold uppercase tracking-widest text-[#64748b] mb-3">📂 <?= t('श्रेणी','Categories') ?></h3>
      <ul class="space-y-1">
        <li>
          <a href="/notices.php<?= $importance?'?imp='.$importance:'' ?>"
             class="flex justify-between items-center text-sm font-mono py-1.5 px-2 rounded transition-colors <?= !$category?'text-[#14b8a6] bg-[#f5f5f4]':'text-[#64748b] hover:text-[#0f172a] hover:bg-[#f5f5f4]' ?>">
            <span><?= t('सबै','All') ?></span>
            <span class="text-[10px] bg-[#f5f5f4] border border-[#e2e8f0] px-1.5 py-0.5 rounded-full"><?= count($allNotices) ?></span>
          </a>
        </li>
        <?php foreach ($cats as $c): ?>
        <li>
          <a href="/notices.php?cat=<?= urlencode($c['category']) ?><?= $importance?'&imp='.$importance:'' ?>"
             class="flex justify-between items-center text-sm font-mono py-1.5 px-2 rounded transition-colors <?= $category===$c['category']?'text-[#14b8a6] bg-[#f5f5f4]':'text-[#64748b] hover:text-[#0f172a] hover:bg-[#f5f5f4]' ?>">
            <span><?= h($c['category']) ?></span>
            <span class="text-[10px] bg-[#f5f5f4] border border-[#e2e8f0] px-1.5 py-0.5 rounded-full"><?= (int)$c['c'] ?></span>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <!-- Related links -->
    <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-xl p-4">
      <h3 class="text-xs font-bold uppercase tracking-widest text-[#64748b] mb-3"><?= t('थप हेर्नुस्','More') ?></h3>
      <ul class="space-y-2">
        <li><a href="/alerts.php"  class="flex items-center gap-2 text-sm font-mono text-[#64748b] hover:text-[#dc2626] transition-colors">🚨 <?= t('अलर्टहरू','Alerts') ?></a></li>
        <li><a href="/news.php"    class="flex items-center gap-2 text-sm font-mono text-[#64748b] hover:text-[#0284c7] transition-colors">📰 <?= t('AI समाचार','AI News') ?></a></li>
        <li><a href="/tools.php"   class="flex items-center gap-2 text-sm font-mono text-[#64748b] hover:text-[#ea580c] transition-colors">⚡ <?= t('टुलहरू','Tools') ?></a></li>
      </ul>
    </div>

    <!-- Share Card -->
    <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-xl p-4">
      <h3 class="text-xs font-bold uppercase tracking-widest text-[#64748b] mb-3"><?= t('Share गर्नुस्','Share') ?></h3>
      <a href="<?= fbShare(SITE_URL.'/notices.php') ?>" target="_blank"
         class="block w-full text-center py-2 btn-primary rounded-lg text-xs font-semibold mb-2">📘 Facebook</a>
      <a href="<?= waShare('🇳🇵 Nepal Notices: '.SITE_URL.'/notices.php') ?>" target="_blank"
         class="block w-full text-center py-2 btn-outline rounded-lg text-xs font-semibold">💬 WhatsApp</a>
    </div>
  </aside>
</div>

<script>
(function(){
  function esc(s){return String(s||'').replace(/[&<>"]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];});}
  function timeAgo(ts){
    if(!ts) return '';
    var d=(Date.now()/1000)-ts;
    if(d<60)return 'भर्खरै';
    if(d<3600)return Math.floor(d/60)+' मिनेट अघि';
    if(d<86400)return Math.floor(d/3600)+' घण्टा अघि';
    return Math.floor(d/86400)+' दिन अघि';
  }
  function paint(items){
    var box=document.getElementById('trf-list'); if(!box) return;
    if(!items.length){ box.innerHTML='<div class="p-3 text-slate-400">हाल कुनै ट्राफिक सूचना छैन।</div>'; return; }
    box.innerHTML = items.slice(0,5).map(function(it){
      var title = it.title || it.headline || it.message || '';
      var content = it.content || it.detail || it.description || it.summary || '';
      var url   = it.url || it.link || it.source_url || 'https://traffic.nepalpolice.gov.np/';
      var src   = it.source || it.source_name || 'Nepal Police MTPD';
      var ts    = it.timestamp || it.created_at_ts || (it.created_at?Math.floor(new Date(it.created_at).getTime()/1000):0);
      var loc   = it.location || it.area || '';
      return '<div class="p-3">'+
        '<div class="font-bold text-[#9a3412] mb-1">'+esc(title)+'</div>'+
        (content?'<div class="text-[#475569] leading-relaxed mb-1.5">'+esc(content.substring(0,260))+(content.length>260?'…':'')+'</div>':'')+
        '<div class="flex items-center gap-2 flex-wrap text-[10.5px] font-mono text-[#64748b]">'+
          (loc?'<span class="bg-[#fef3c7] text-[#9a3412] px-2 py-0.5 rounded-full">📍 '+esc(loc)+'</span>':'')+
          (ts?'<span>⏱ '+esc(timeAgo(ts))+'</span>':'')+
          '<a href="'+esc(url)+'" target="_blank" rel="noopener" class="ml-auto text-[#0f766e] font-bold">स्रोत: '+esc(src)+' ↗</a>'+
        '</div></div>';
    }).join('');
  }
  fetch('/api/alerts.php?type=traffic').then(function(r){return r.json();}).then(function(d){
    var items = (d && (d.alerts || d.items || d.data)) || [];
    if (Array.isArray(items) && items.length) return paint(items);
    return fetch('/api/utilities.php?type=traffic').then(function(r){return r.json();}).then(function(d2){
      var arr = (d2 && (d2.items || d2.notices || d2.data || [])) || [];
      paint(arr);
    });
  }).catch(function(){
    fetch('/api/utilities.php?type=traffic').then(function(r){return r.json();}).then(function(d2){
      var arr=(d2&&(d2.items||d2.notices||d2.data||[]))||[]; paint(arr);
    }).catch(function(){ paint([]); });
  });
})();
</script>

<?php include __DIR__ . '/footer.php'; ?>
