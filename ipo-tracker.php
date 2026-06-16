<?php
/**
 * आकाशवाणी — ipo-tracker.php (World-Class IPO Tracker)
 * Premium IPO tracking with clean, professional design
 */

$pageTitle = 'IPO ट्र्याकर | आकाशवाणी';
include __DIR__ . '/header.php';

$lang = siteLang();
$isNepali = ($lang !== 'en');

// Sample IPO data
$ipos = [
    ['id' => 1, 'company' => 'NMB Bank', 'symbol' => 'NMB', 'price' => 235, 'units' => 5000000, 'status' => 'open', 'close_date' => '2026-06-20', 'min_units' => 10],
    ['id' => 2, 'company' => 'Global IME Bank', 'symbol' => 'GIME', 'price' => 280, 'units' => 3000000, 'status' => 'upcoming', 'close_date' => '2026-06-25', 'min_units' => 10],
    ['id' => 3, 'company' => 'NIC Asia Bank', 'symbol' => 'NIC', 'price' => 310, 'units' => 4000000, 'status' => 'closed', 'close_date' => '2026-06-10', 'min_units' => 10],
];
?>

<section class="ipo-header">
    <div class="container">
        <div class="header-content">
            <div class="header-title">
                <i data-lucide="trending-up" class="icon-lg"></i>
                <h1><?= $isNepali ? 'IPO ट्र्याकर' : 'IPO Tracker' ?></h1>
            </div>
        </div>
    </div>
</section>

<main class="ipo-main">
    <div class="container">
        <div class="ipo-tabs">
            <button class="ipo-tab active" data-status="open"><?= $isNepali ? 'खुला' : 'Open' ?></button>
            <button class="ipo-tab" data-status="upcoming"><?= $isNepali ? 'आगामी' : 'Upcoming' ?></button>
            <button class="ipo-tab" data-status="closed"><?= $isNepali ? 'बन्द' : 'Closed' ?></button>
        </div>

        <div class="ipo-grid">
            <?php foreach ($ipos as $ipo): ?>
            <article class="ipo-card" data-status="<?= $ipo['status'] ?>">
                <div class="ipo-header-card">
                    <div class="ipo-symbol"><?= $ipo['symbol'] ?></div>
                    <span class="ipo-status <?= $ipo['status'] ?>">
                        <?= $ipo['status'] === 'open' ? 'खुला' : ($ipo['status'] === 'upcoming' ? 'आगामी' : 'बन्द') ?>
                    </span>
                </div>
                <h3 class="ipo-company"><?= $ipo['company'] ?></h3>
                <div class="ipo-details">
                    <div class="detail-row">
                        <span class="detail-label"><?= $isNepali ? 'मूल्य' : 'Price' ?></span>
                        <span class="detail-value">रु <?= number_format($ipo['price']) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label"><?= $isNepali ? 'कुल युनिट' : 'Total Units' ?></span>
                        <span class="detail-value"><?= number_format($ipo['units']) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label"><?= $isNepali ? 'बन्द हुने मिति' : 'Close Date' ?></span>
                        <span class="detail-value"><?= $ipo['close_date'] ?></span>
                    </div>
                </div>
                <a href="/ipo-detail.php?id=<?= $ipo['id'] ?>" class="ipo-btn">
                    <?= $isNepali ? 'विवरण हेर्नुहोस्' : 'View Details' ?>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<style>
.ipo-header { background: linear-gradient(135deg, #0f172a, #1e293b); padding: 32px 0; color: #fff; }
.header-content { display: flex; align-items: center; justify-content: space-between; }
.header-title { display: flex; align-items: center; gap: 12px; }
.header-title i { color: #10b981; }
.header-title h1 { font-size: 28px; font-weight: 800; color: #fff; }
.icon-lg { width: 28px; height: 28px; }

.ipo-main { padding: 32px 0; background: #f8fafc; }

.ipo-tabs { display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap; }
.ipo-tab { padding: 10px 20px; background: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; color: #64748b; cursor: pointer; transition: all 0.2s; }
.ipo-tab.active { background: #10b981; color: #fff; }
.ipo-tab:hover:not(.active) { background: #f1f5f9; }

.ipo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
.ipo-card { background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.ipo-header-card { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.ipo-symbol { font-size: 24px; font-weight: 800; color: #10b981; }
.ipo-status { padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
.ipo-status.open { background: #dcfce7; color: #166534; }
.ipo-status.upcoming { background: #fef3c7; color: #92400e; }
.ipo-status.closed { background: #f1f5f9; color: #64748b; }

.ipo-company { font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 16px; }
.ipo-details { display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px; }
.detail-row { display: flex; justify-content: space-between; padding: 8px 12px; background: #f8fafc; border-radius: 8px; }
.detail-label { font-size: 13px; color: #64748b; }
.detail-value { font-size: 13px; font-weight: 600; color: #0f172a; }
.ipo-btn { display: block; width: 100%; padding: 12px; background: #10b981; color: #fff; text-align: center; border-radius: 10px; font-size: 14px; font-weight: 600; text-decoration: none; transition: all 0.2s; }
.ipo-btn:hover { background: #059669; }
</style>

<?php include __DIR__ . '/footer.php'; ?>
