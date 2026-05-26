<?php
/** 404.php v13 — App-style not found
 *  FIX: Load config.php + functions.php FIRST so getFlash() and all
 *  helper functions are available when header.php calls them.
 *  Previously: fatal "Call to undefined function getFlash()" on every 404.
 */
http_response_code(404);

/* ── Clean URL redirect (fallback when rewrite is unavailable) ── */
$__path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$__clean = rtrim($__path, '/') ?: '/';
$__routes = [
    '/news'=>'/news.php','/loksewa'=>'/loksewa.php','/rashifal'=>'/rashifal.php',
    '/tools'=>'/tools.php','/alerts'=>'/alerts.php','/gov-services'=>'/gov-services.php',
    '/utilities'=>'/utilities.php','/nepali-patro'=>'/nepali-patro.php','/patro'=>'/nepali-patro.php',
    '/contact'=>'/contact.php','/search'=>'/search.php','/install'=>'/install.php',
    '/emergency'=>'/emergency.php','/ipo-tracker'=>'/ipo-tracker.php','/tax-calculator'=>'/tax-calculator.php',
    '/downloads'=>'/downloads.php','/dashboard'=>'/dashboard.php','/login'=>'/login.php',
    '/register'=>'/register.php','/about'=>'/about.php','/bookmarks'=>'/bookmarks.php',
    '/morning-brief'=>'/morning-brief.php','/notices'=>'/notices.php','/offline'=>'/offline.php',
];
if ($__clean !== '/' && isset($__routes[$__clean])) {
    $__target = $__routes[$__clean];
    if (!empty($_SERVER['QUERY_STRING'])) $__target .= '?' . $_SERVER['QUERY_STRING'];
    header('Location: ' . $__target, true, 302);
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$pageTitle = '404 — Page Not Found';
require_once __DIR__ . '/header.php';
?>
<main class="app-main">
  <section class="px-6 pt-10 pb-6 text-center">
    <div class="w-24 h-24 mx-auto rounded-3xl bg-gradient-to-br from-rose-500 to-orange-500 flex items-center justify-center shadow-app mb-4">
      <i data-lucide="map-pinned-off" class="w-12 h-12 text-white"></i>
    </div>
    <div class="text-[64px] font-extrabold text-slate-900 leading-none">404</div>
    <h1 class="text-[18px] font-bold text-slate-800 mt-2"><?= $tH('पृष्ठ भेटिएन','Page not found') ?></h1>
    <p class="text-[13px] text-slate-500 mt-1"><?= $tH('तपाईंले खोज्नुभएको पृष्ठ छैन वा हटाइएको छ।','The page you were looking for doesn\'t exist.') ?></p>
    <a href="/" class="inline-flex items-center gap-1.5 mt-5 bg-teal-600 text-white font-bold px-5 py-2.5 rounded-xl shadow-app">
      <i data-lucide="home" class="w-4 h-4"></i> <?= $tH('गृहमा फर्क','Go Home') ?>
    </a>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
