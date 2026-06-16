<?php
/**
 * आकाशवाणी — gov-services.php (World-Class Government Services)
 * Premium government services directory
 */

$pageTitle = 'सरकारी सेवा | आकाशवाणी';
include __DIR__ . '/header.php';

$lang = siteLang();
$isNepali = ($lang !== 'en');

$services = [
    ['name' => 'नागरिकता', 'icon' => 'id-card', 'desc' => 'नागरिकता प्रमाणपत्र', 'link' => '#'],
    ['name' => 'राहदानी', 'icon' => 'book-open', 'desc' => 'राहदानी (Passport)', 'link' => '#'],
    ['name' => 'स्थानीय तह', 'icon' => 'map-pin', 'desc' => 'नगरपालिका/गाउँपालिका', 'link' => '#'],
    ['name' => 'कर', 'icon' => 'calculator', 'desc' => 'आयकर र मूल्याङ्कन', 'link' => '#'],
    ['name' => 'जग्गा', 'icon' => 'home', 'desc' => 'जग्गा रजिष्ट्रेशन', 'link' => '#'],
    ['name' => 'शिक्षा', 'icon' => 'graduation-cap', 'desc' => 'शैक्षिक प्रमाणपत्र', 'link' => '#'],
];
?>

<section class="gov-header">
    <div class="container">
        <div class="header-title">
            <i data-lucide="landmark" class="icon-lg"></i>
            <h1><?= $isNepali ? 'सरकारी सेवाहरू' : 'Government Services' ?></h1>
        </div>
    </div>
</section>

<main class="gov-main">
    <div class="container">
        <div class="services-grid">
            <?php foreach ($services as $svc): ?>
            <a href="<?= $svc['link'] ?>" class="service-card">
                <div class="service-icon">
                    <i data-lucide="<?= $svc['icon'] ?>" class="icon-xl"></i>
                </div>
                <h3 class="service-name"><?= $svc['name'] ?></h3>
                <p class="service-desc"><?= $svc['desc'] ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<style>
.gov-header { background: linear-gradient(135deg, #0f172a, #1e293b); padding: 32px 0; color: #fff; }
.header-title { display: flex; align-items: center; gap: 12px; }
.header-title i { color: #10b981; }
.header-title h1 { font-size: 28px; font-weight: 800; color: #fff; }
.icon-lg { width: 28px; height: 28px; }
.gov-main { padding: 40px 0; background: #f8fafc; }
.services-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
.service-card { display: flex; flex-direction: column; align-items: center; text-align: center; padding: 32px 24px; background: #fff; border-radius: 16px; text-decoration: none; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.service-card:hover { transform: translateY(-4px); box-shadow: 0 10px 30px rgba(0,0,0,0.15); }
.service-icon { width: 72px; height: 72px; background: #10b981; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; }
.icon-xl { width: 32px; height: 32px; color: #fff; }
.service-name { font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
.service-desc { font-size: 14px; color: #64748b; }
</style>

<?php include __DIR__ . '/footer.php'; ?>
