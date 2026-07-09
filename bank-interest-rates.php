<?php
/**
 * आकाशवाणी — Bank Interest Rates
 * Nepal Bank Interest Rates from NRB
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/data-manager.php';

$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;

// Fetch bank interest rates data
$ratesData = null;
$cacheKey = 'bank_rates_' . $lang;
$cacheFile = __DIR__ . '/data/cache/bank_rates.json';
$cacheTime = 3600; // 1 hour

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    $ratesData = json_decode(file_get_contents($cacheFile), true);
}

if (!$ratesData) {
    $apiUrl = (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '') . '/api/bank-interest-rates.php';
    $ctx = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
    $response = @file_get_contents($apiUrl, false, $ctx);
    if ($response) {
        $ratesData = json_decode($response, true);
        @file_put_contents($cacheFile, $response);
    }
}

if (!$ratesData) {
    $ratesData = ['error' => 'Unable to fetch rates', 'deposit_rates' => [], 'lending_rates' => []];
}
?>
<!DOCTYPE html>
<html lang="<?= $isNepali ? 'ne' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t('बैंक ब्याज दर', 'Bank Interest Rates') ?> | <?= $t('आकाशवाणी', 'Aakashvani') ?></title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/premium.css">
    <style>
        body { background: var(--dark-950); color: var(--text-primary); font-family: 'Inter', 'Noto Sans Devanagari', sans-serif; }
        .page-container { max-width: 1000px; margin: 0 auto; padding: 2rem 1rem; }
        .page-header { text-align: center; margin-bottom: 2rem; }
        .page-title { font-size: 2rem; font-weight: 700; color: var(--primary); margin-bottom: 0.5rem; }
        .page-subtitle { color: var(--text-secondary); }
        .section-title { font-size: 1.25rem; font-weight: 600; color: var(--primary); margin: 2rem 0 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--dark-700); }
        .rates-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem; }
        .rate-card { background: var(--dark-900); border-radius: 12px; padding: 1.25rem; border: 1px solid var(--dark-700); }
        .rate-name { font-weight: 600; color: var(--text-primary); margin-bottom: 0.5rem; font-size: 0.95rem; }
        .rate-value { font-size: 1.5rem; font-weight: 700; color: var(--primary); }
        .rate-value small { font-size: 0.8rem; font-weight: 400; color: var(--text-secondary); }
        .rate-desc { color: var(--text-secondary); font-size: 0.8rem; margin-top: 0.5rem; }
        .policy-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .policy-card { background: linear-gradient(135deg, var(--dark-800), var(--dark-900)); border-radius: 12px; padding: 1.25rem; text-align: center; border: 1px solid var(--dark-600); }
        .policy-label { color: var(--text-secondary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .policy-rate { font-size: 1.75rem; font-weight: 700; color: var(--primary); margin: 0.5rem 0; }
        .policy-desc { color: var(--text-secondary); font-size: 0.75rem; }
        .updated { text-align: center; color: var(--text-secondary); font-size: 0.85rem; margin-top: 2rem; padding: 1rem; background: var(--dark-800); border-radius: 8px; }
        .error-box { background: var(--dark-800); border-radius: 12px; padding: 2rem; text-align: center; color: var(--text-secondary); }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <div class="page-container">
        <div class="page-header">
            <h1 class="page-title">🏦 <?= $t('बैंक ब्याज दर', 'Bank Interest Rates') ?></h1>
            <p class="page-subtitle"><?= $t('नेपाल राष्ट्र बैंक अनुसार', 'As per Nepal Rastra Bank') ?></p>
        </div>
        
        <?php if (isset($ratesData['error'])): ?>
            <div class="error-box">
                <p>⚠️ <?= htmlspecialchars($ratesData['error']) ?></p>
            </div>
        <?php else: ?>
            
            <?php if (!empty($ratesData['policy_rates'])): ?>
                <h2 class="section-title"><?= $t('नीतिगत दर', 'Policy Rates') ?></h2>
                <div class="policy-grid">
                    <?php foreach ($ratesData['policy_rates'] as $key => $rate): ?>
                        <div class="policy-card">
                            <div class="policy-label"><?= htmlspecialchars($rate['description']) ?></div>
                            <div class="policy-rate"><?= $rate['rate'] ?>%</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($ratesData['deposit_rates'])): ?>
                <h2 class="section-title"><?= $t('निक्षेप दर (Deposit Rates)', 'Deposit Rates') ?></h2>
                <div class="rates-grid">
                    <?php foreach ($ratesData['deposit_rates'] as $key => $rate): ?>
                        <div class="rate-card">
                            <div class="rate-name"><?= htmlspecialchars($rate['description'] ?? $key) ?></div>
                            <div class="rate-value">
                                <?= $rate['avg'] ?? $rate['min'] ?? 'N/A' ?>%
                                <small>(<?= ($rate['min'] ?? 'N/A') ?> - <?= ($rate['max'] ?? 'N/A') ?>%)</small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($ratesData['lending_rates'])): ?>
                <h2 class="section-title"><?= $t('ऋण दर (Lending Rates)', 'Lending Rates') ?></h2>
                <div class="rates-grid">
                    <?php foreach ($ratesData['lending_rates'] as $key => $rate): ?>
                        <div class="rate-card">
                            <div class="rate-name"><?= htmlspecialchars($rate['description'] ?? $key) ?></div>
                            <div class="rate-value">
                                <?= is_array($rate) ? ($rate['avg'] ?? $rate['range'] ?? 'N/A') : $rate ?>
                                <?php if (is_array($rate) && isset($rate['range'])): ?>
                                    <small>(<?= $rate['range'] ?>)</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="updated">
                <?= $t('अपडेट: ', 'Updated: ') ?><?= htmlspecialchars($ratesData['updated'] ?? date('Y-m-d H:i:s')) ?>
                | <?= $t('स्रोत: ', 'Source: ') ?><?= htmlspecialchars($ratesData['source'] ?? 'NRB') ?>
                <br><small><?= $t('* यी दरहरू अनुमानित हुन्। वास्तविक दरहरू बैंक अनुसार फरक हुन सक्छन्।', '* These are indicative rates. Actual rates may vary by bank.') ?></small>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
