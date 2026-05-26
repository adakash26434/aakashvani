<?php
/**
 * आकाशवाणी — Offline Fallback Page
 * Shown by the Service Worker when the user is offline and no cached page exists.
 */
require_once __DIR__ . '/config.php';

$pageTitle = 'अफलाइन | ' . SITE_NAME;
$pageDesc  = 'इन्टरनेट जडान छैन। कृपया जडान जाँच गर्नुहोस्।';

// We can't use the full header (it loads CDN resources that won't work offline)
// So this is a self-contained minimal page.
?>
<!DOCTYPE html>
<html lang="ne" class="bg-stone-50">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="theme-color" content="#0f766e"/>
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Hind Siliguri', 'Noto Sans Devanagari', system-ui, sans-serif; background: #fafaf9; color: #0f172a; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px; text-align: center; }
    .icon { font-size: 72px; margin-bottom: 24px; animation: pulse 2s infinite; }
    @keyframes pulse { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.05); opacity: .8; } }
    h1 { font-size: 1.75rem; font-weight: 700; color: #0f172a; margin-bottom: 12px; line-height: 1.3; }
    p { font-size: 1rem; color: #64748b; line-height: 1.7; max-width: 380px; margin: 0 auto 28px; }
    .btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; background: #0f766e; color: #fff; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background .2s; text-decoration: none; margin: 6px; }
    .btn:hover { background: #0d9488; }
    .btn-ghost { background: transparent; color: #0f766e; border: 1.5px solid #0f766e; }
    .btn-ghost:hover { background: rgba(15,118,110,.06); }
    .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; margin-top: 32px; max-width: 420px; width: 100%; text-align: left; }
    .card h2 { font-size: 1rem; font-weight: 700; margin-bottom: 12px; color: #0f172a; }
    .quick-link { display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: 8px; text-decoration: none; color: #334155; font-size: .9375rem; transition: background .15s; margin-bottom: 2px; }
    .quick-link:hover { background: #f1f5f9; color: #0f766e; }
    .quick-link .icon-sm { font-size: 1.25rem; width: 28px; text-align: center; }
    .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 99px; font-size: .8125rem; font-weight: 600; margin-bottom: 20px; }
    .badge-offline { background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; }
    .badge-online { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
    .dot { width: 8px; height: 8px; border-radius: 50%; background: currentColor; }
  </style>
</head>
<body>

  <div class="icon">📡</div>

  <div class="status-badge badge-offline">
    <span class="dot"></span>
    अफलाइन
  </div>

  <h1>इन्टरनेट जडान छैन</h1>
  <p>तपाईंको डिभाइस अहिले अफलाइन छ। कृपया इन्टरनेट कनेक्सन जाँच गर्नुहोस् र पुनः प्रयास गर्नुहोस्।</p>

  <div>
    <button class="btn" onclick="location.reload()">🔄 पुनः लोड गर्नुस्</button>
    <a href="/" class="btn btn-ghost">🏠 गृहपृष्ठ</a>
  </div>

  <div class="card">
    <h2>⚡ ऑफलाइन परिचर्या</h2>
    <p style="margin:0 0 12px;font-size:.9375rem;color:#475569">यहाँ केही पृष्ठहरू र विषयवस्तु उपलब्ध हो।</p>
    <nav>
      <a href="/" class="quick-link">
        <span class="icon-sm">🏠</span>
        <span>गृहपृष्ठ</span>
      </a>
      <a href="/news.php" class="quick-link">
        <span class="icon-sm">📰</span>
        <span>समाचार</span>
      </a>
      <a href="/about.php" class="quick-link">
        <span class="icon-sm">ℹ️</span>
        <span>हाम्रो बारेमा</span>
      </a>
      <a href="/contact.php" class="quick-link">
        <span class="icon-sm">📧</span>
        <span>संपर्क</span>
      </a>
    </nav>
  </div>

  <script>
    setInterval(function(){
      fetch('/api/sync-status.php?brief=1', {cache:'no-store'})
        .then(r => r.ok && location.reload())
        .catch(()=> {});
    }, 2000);
  </script>
</body>
</html>
