<?php
/**
 * आकाशवाणी — IPO Tracker (LIVE API DATA)
 */
require_once __DIR__ . '/config.php';

$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;

// Try to fetch from IPO API
$ipos = [];
$cacheFile = __DIR__ . '/data/cache/ipo-list.json';
$forceRefresh = isset($_GET['refresh']);

// Try cache first
if (!$forceRefresh && file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 600) {
    $data = json_decode(file_get_contents($cacheFile), true);
    $ipos = $data['ipos'] ?? $data ?? [];
}

// Fetch fresh data if no cache or empty
if (empty($ipos)) {
    $ipos = fetchIPOsFromAPI();
}

// Save to cache
if (!empty($ipos)) {
    $cacheData = ['ipos' => $ipos, 'fetched_at' => date('Y-m-d H:i:s')];
    file_put_contents($cacheFile, json_encode($cacheData, JSON_UNESCAPED_UNICODE));
}

function fetchIPOsFromAPI(): array {
    $ipos = [];
    
    // Try ShareSansar API
    $ch = curl_init('https://www.sharesansar.com/existing-issues?type=1&draw=1&start=0&length=20&_=' . time());
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-Requested-With: XMLHttpRequest',
        'Accept: application/json',
        'Referer: https://www.sharesansar.com/existing-issues'
    ]);
    
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($resp && $code === 200) {
        $data = json_decode($resp, true);
        if (!empty($data['data']) && is_array($data['data'])) {
            foreach ($data['data'] as $item) {
                $co = $item['company'] ?? [];
                $symbol = strip_tags($co['symbol'] ?? '');
                $name = strip_tags($co['companyname'] ?? '');
                
                if (empty($symbol) && empty($name)) continue;
                
                // Determine status
                $closeDate = trim($item['closing_date'] ?? '');
                $status = 'upcoming';
                if (!empty($closeDate)) {
                    $closeTs = strtotime($closeDate);
                    if ($closeTs < time()) {
                        $status = 'closed';
                    } else {
                        $status = 'open';
                    }
                }
                
                $ipos[] = [
                    'symbol' => $symbol ?: $name,
                    'company' => $name ?: $symbol,
                    'price' => (int)($item['price'] ?? 0),
                    'units' => number_format((int)($item['total_units'] ?? 0)),
                    'status' => $status,
                    'close' => $closeDate,
                    'open' => trim($item['opening_date'] ?? ''),
                ];
            }
        }
    }
    
    // Fallback to MeroLagani if empty
    if (empty($ipos)) {
        $ipos = fetchFromMeroLagani();
    }
    
    return $ipos;
}

function fetchFromMeroLagani(): array {
    $ipos = [];
    $html = @file_get_contents('https://www.merolagani.com/IPOList');
    
    if ($html && preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $html, $matches)) {
        foreach ($matches[1] as $row) {
            if (preg_match('/IPO/i', $row)) {
                preg_match('/<td[^>]*>(.*?)<\/td>/s', $row, $cols);
                preg_match('/<a[^>]*>(.*?)<\/a>/s', $row, $link);
                
                if (!empty($cols[1])) {
                    $ipos[] = [
                        'symbol' => trim(strip_tags($cols[1])),
                        'company' => trim(strip_tags($link[1] ?? $cols[1])),
                        'price' => 0,
                        'units' => '-',
                        'status' => 'open',
                        'close' => date('Y-m-d', strtotime('+7 days')),
                    ];
                }
            }
        }
    }
    
    return array_slice($ipos, 0, 20);
}
?>
<!DOCTYPE html>
<html lang="<?= $isNepali ? 'ne' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta property="og:title" content="<?= $t('आकाशवाणी - Nepal Information Portal', 'Aakashvani - Nepal Information Portal') ?>">
    <meta property="og:description" content="<?= $t('नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म।', 'Nepal\'s most trusted information platform.') ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t('IPO ट्र्याकर', 'IPO Tracker') ?> | आकाशवाणी</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .page-header { background: linear-gradient(135deg, var(--dark-900), var(--dark-800)); padding: var(--space-12) 0; color: #fff; }
        .ipo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: var(--space-6); }
        .ipo-card { background: #fff; border-radius: var(--radius-xl); border: 1px solid var(--dark-100); padding: var(--space-6); transition: all var(--transition); }
        .ipo-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
        .ipo-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4); }
        .ipo-symbol { font-size: 1.5rem; font-weight: 800; color: var(--primary); }
        .ipo-status { padding: var(--space-1) var(--space-3); border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 700; }
        .ipo-status.open { background: var(--primary-50); color: var(--primary-700); }
        .ipo-status.upcoming { background: #fef3c7; color: #92400e; }
        .ipo-status.closed { background: var(--dark-100); color: var(--dark-500); }
        .ipo-company { font-size: 1rem; font-weight: 600; color: var(--dark-900); margin-bottom: var(--space-4); }
        .ipo-details { display: flex; flex-direction: column; gap: var(--space-2); margin-bottom: var(--space-4); }
        .ipo-detail { display: flex; justify-content: space-between; padding: var(--space-2) var(--space-3); background: var(--dark-50); border-radius: var(--radius); }
        .ipo-detail-label { font-size: 0.875rem; color: var(--dark-500); }
        .ipo-detail-value { font-size: 0.875rem; font-weight: 600; color: var(--dark-900); }
        .section { padding: var(--space-12) 0; }
        .ipo-section:nth-child(even) { background: var(--dark-50); }
        
        /* Responsive */
        @media (max-width: 640px) {
            .page-title { font-size: 1.5rem; }
            .ipo-card { padding: var(--space-4); }
            .ipo-symbol { font-size: 1.25rem; }
            .ipo-grid { grid-template-columns: 1fr; gap: var(--space-4); }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="header-main">
            <div class="container">
                <div class="flex items-center justify-between gap-4">
                    <a href="/" class="header-brand">
                        <div class="brand-logo">आ</div>
                        <span class="brand-name"><?= $t('आकाशवाणी', 'Aakashvani') ?></span>
                    </a>
                    <nav class="main-nav">
                        <div class="container">
                            <div class="nav-list">
                                <a href="/" class="nav-link"><?= $t('गृह', 'Home') ?></a>
                                <a href="/news.php" class="nav-link"><?= $t('समाचार', 'News') ?></a>
                                <a href="/nepali-patro.php" class="nav-link"><?= $t('पात्रो', 'Calendar') ?></a>
                                <a href="/rashifal.php" class="nav-link"><?= $t('राशिफल', 'Horoscope') ?></a>
                                <a href="/ipo-tracker.php" class="nav-link active"><?= $t('NEPSE/IPO', 'NEPSE/IPO') ?></a>
                                <a href="/weather.php" class="nav-link"><?= $t('मौसम', 'Weather') ?></a>
                                <a href="/cricket.php" class="nav-link"><?= $t('क्रिकेट', 'Cricket') ?></a>
                                <a href="/tenders.php" class="nav-link"><?= $t('टेन्डर', 'Tenders') ?></a>
                                <a href="/emergency.php" class="nav-link"><?= $t('आपतकालीन', 'Emergency') ?></a>
                            </div>
                        </div>
                    </nav>
                    <div class="header-actions">
                        <button class="btn btn-ghost btn-icon" aria-label="Search">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <section class="page-header">
        <div class="container">
            <h1 class="page-title" style="display:flex;align-items:center;gap:12px">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--primary)"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                <?= $t('IPO ट्र्याकर', 'IPO Tracker') ?>
            </h1>
            <p class="page-subtitle"><?= $t('नेपालका IPO र मर्जर आइपिओको विवरण', 'Details of IPOs and merger IPOs in Nepal') ?></p>
        </div>
    </section>
    
    <section class="ipo-section">
        <div class="container">
            <!-- Open IPOs -->
            <h2 class="text-xl font-bold mb-6" style="padding-top:var(--space-6)"><?= $t('खुला IPO', 'Open IPOs') ?></h2>
            <div class="ipo-grid">
                <?php foreach (array_filter($ipos, fn($i) => $i['status'] === 'open') as $ipo): ?>
                <div class="ipo-card">
                    <div class="ipo-header">
                        <span class="ipo-symbol"><?= $ipo['symbol'] ?></span>
                        <span class="ipo-status open"><?= $t('खुला', 'OPEN') ?></span>
                    </div>
                    <h3 class="ipo-company"><?= $ipo['company'] ?></h3>
                    <div class="ipo-details">
                        <div class="ipo-detail">
                            <span class="ipo-detail-label"><?= $t('मूल्य', 'Price') ?></span>
                            <span class="ipo-detail-value">रु <?= number_format($ipo['price']) ?></span>
                        </div>
                        <div class="ipo-detail">
                            <span class="ipo-detail-label"><?= $t('कुल युनिट', 'Total Units') ?></span>
                            <span class="ipo-detail-value"><?= $ipo['units'] ?></span>
                        </div>
                        <div class="ipo-detail">
                            <span class="ipo-detail-label"><?= $t('बन्द हुने', 'Close Date') ?></span>
                            <span class="ipo-detail-value"><?= $ipo['close'] ?></span>
                        </div>
                    </div>
                    <button class="btn btn-primary w-full"><?= $t('थप विवरण', 'More Details') ?></button>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Upcoming IPOs -->
            <h2 class="text-xl font-bold mb-6" style="padding-top:var(--space-8)"><?= $t('आगामी IPO', 'Upcoming IPOs') ?></h2>
            <div class="ipo-grid">
                <?php foreach (array_filter($ipos, fn($i) => $i['status'] === 'upcoming') as $ipo): ?>
                <div class="ipo-card">
                    <div class="ipo-header">
                        <span class="ipo-symbol"><?= $ipo['symbol'] ?></span>
                        <span class="ipo-status upcoming"><?= $t('आगामी', 'UPCOMING') ?></span>
                    </div>
                    <h3 class="ipo-company"><?= $ipo['company'] ?></h3>
                    <div class="ipo-details">
                        <div class="ipo-detail">
                            <span class="ipo-detail-label"><?= $t('मूल्य', 'Price') ?></span>
                            <span class="ipo-detail-value">रु <?= number_format($ipo['price']) ?></span>
                        </div>
                        <div class="ipo-detail">
                            <span class="ipo-detail-label"><?= $t('कुल युनिट', 'Total Units') ?></span>
                            <span class="ipo-detail-value"><?= $ipo['units'] ?></span>
                        </div>
                        <div class="ipo-detail">
                            <span class="ipo-detail-label"><?= $t('खुला हुने', 'Open Date') ?></span>
                            <span class="ipo-detail-value"><?= $ipo['close'] ?></span>
                        </div>
                    </div>
                    <button class="btn btn-secondary w-full"><?= $t('अलर्ट सेट गर्नुहोस्', 'Set Alert') ?></button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <footer class="site-footer">
        <div class="container">
            <div class="footer-bottom" style="border:none;padding:0">
                <p class="footer-copyright">&copy; <?= date('Y') ?> <?= $t('आकाशवाणी', 'Aakashvani') ?></p>
            </div>
        </div>
    </footer>
    
    <script src="/assets/js/app.js"></script>
</body>
</html>
