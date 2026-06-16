<?php
/**
 * आकाशवाणी — emergency.php (World-Class Emergency Numbers)
 * Premium emergency services directory with clean design
 */

$pageTitle = 'आपतकालीन नम्बर | आकाशवाणी';
include __DIR__ . '/header.php';

$lang = siteLang();
$isNepali = ($lang !== 'en');

// Emergency contacts
$emergencyContacts = [
    ['name' => 'प्रहरी', 'number' => '100', 'icon' => 'shield', 'color' => '#3b82f6'],
    ['name' => 'एम्बुलेन्स', 'number' => '102', 'icon' => 'heart-pulse', 'color' => '#ef4444'],
    ['name' => 'दमकल', 'number' => '101', 'icon' => 'flame', 'color' => '#f59e0b'],
    ['name' => 'गृह मन्त्रालय', 'number' => '01-4200100', 'icon' => 'landmark', 'color' => '#10b981'],
];

$hospitals = [
    ['name' => 'टिचिङ हस्पिटल', 'phone' => '01-4412300', 'address' => 'महाराजगञ्ज, काठमाडौं'],
    ['name' => 'स्टेटसर्जरी हस्पिटल', 'phone' => '01-4412555', 'address' => 'थापाथली, काठमाडौं'],
    ['name' => 'नर्विक हस्पिटल', 'phone' => '01-4412999', 'address' => 'लाजिम्पाट, काठमाडौं'],
];

$police = [
    ['name' => 'केन्द्रीय प्रहरी', 'number' => '01-4414212'],
    ['name' => 'महानगरीय प्रहरी', 'number' => '01-4261799'],
];
?>

<section class="emergency-header">
    <div class="container">
        <div class="header-content">
            <div class="header-icon">
                <i data-lucide="phone-call" class="icon-xl"></i>
            </div>
            <div>
                <h1><?= $isNepali ? 'आपतकालीन नम्बरहरू' : 'Emergency Numbers' ?></h1>
                <p class="header-subtitle"><?= $isNepali ? 'नेपालका महत्वपूर्ण आपतकालीन सम्पर्क नम्बरहरू' : 'Important emergency contact numbers of Nepal' ?></p>
            </div>
        </div>
    </div>
</section>

<main class="emergency-main">
    <div class="container">
        
        <!-- Quick Dial -->
        <section class="quick-dial">
            <h2 class="section-title">
                <i data-lucide="zap" class="section-icon"></i>
                <?= $isNepali ? 'छिटो डायल' : 'Quick Dial' ?>
            </h2>
            <div class="quick-grid">
                <?php foreach ($emergencyContacts as $contact): ?>
                <a href="tel:<?= $contact['number'] ?>" class="quick-card" style="--card-color: <?= $contact['color'] ?>">
                    <div class="quick-icon">
                        <i data-lucide="<?= $contact['icon'] ?>" class="icon-lg"></i>
                    </div>
                    <div class="quick-info">
                        <span class="quick-name"><?= $contact['name'] ?></span>
                        <span class="quick-number"><?= $contact['number'] ?></span>
                    </div>
                    <i data-lucide="phone" class="call-icon"></i>
                </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Hospitals -->
        <section class="contacts-section">
            <h2 class="section-title">
                <i data-lucide="building-2" class="section-icon"></i>
                <?= $isNepali ? 'अस्पतालहरू' : 'Hospitals' ?>
            </h2>
            <div class="contacts-list">
                <?php foreach ($hospitals as $hospital): ?>
                <div class="contact-card">
                    <div class="contact-info">
                        <h3 class="contact-name"><?= $hospital['name'] ?></h3>
                        <p class="contact-address"><?= $hospital['address'] ?></p>
                    </div>
                    <div class="contact-actions">
                        <a href="tel:<?= $hospital['phone'] ?>" class="action-btn call">
                            <i data-lucide="phone" class="action-icon"></i>
                            <?= $hospital['phone'] ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Police -->
        <section class="contacts-section">
            <h2 class="section-title">
                <i data-lucide="shield" class="section-icon"></i>
                <?= $isNepali ? 'प्रहरी' : 'Police' ?>
            </h2>
            <div class="contacts-list">
                <?php foreach ($police as $station): ?>
                <div class="contact-card">
                    <div class="contact-info">
                        <h3 class="contact-name"><?= $station['name'] ?></h3>
                    </div>
                    <div class="contact-actions">
                        <a href="tel:<?= $station['number'] ?>" class="action-btn call">
                            <i data-lucide="phone" class="action-icon"></i>
                            <?= $station['number'] ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

    </div>
</main>

<style>
.emergency-header { background: linear-gradient(135deg, #dc2626, #991b1b); padding: 48px 0; color: #fff; text-align: center; }
.header-content { display: flex; flex-direction: column; align-items: center; gap: 16px; }
.header-icon { width: 80px; height: 80px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.icon-xl { width: 40px; height: 40px; color: #fff; }
.header-content h1 { font-size: 32px; font-weight: 800; }
.header-subtitle { font-size: 16px; opacity: 0.9; }

.emergency-main { padding: 40px 0; background: #f8fafc; }

.section-title { display: flex; align-items: center; gap: 8px; font-size: 20px; font-weight: 700; color: #0f172a; margin-bottom: 20px; }
.section-icon { color: #10b981; width: 22px; height: 22px; }

.quick-dial { margin-bottom: 40px; }
.quick-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; }

.quick-card { display: flex; align-items: center; gap: 16px; padding: 20px; background: #fff; border-radius: 16px; text-decoration: none; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.quick-card:hover { transform: translateY(-4px); box-shadow: 0 10px 30px rgba(0,0,0,0.15); }
.quick-icon { width: 56px; height: 56px; background: var(--card-color); border-radius: 12px; display: flex; align-items: center; justify-content: center; }
.quick-icon .icon-lg { width: 28px; height: 28px; color: #fff; }
.quick-info { flex: 1; }
.quick-name { display: block; font-size: 14px; font-weight: 600; color: #0f172a; margin-bottom: 4px; }
.quick-number { display: block; font-size: 20px; font-weight: 800; color: var(--card-color); }
.call-icon { width: 24px; height: 24px; color: #cbd5e1; }

.contacts-section { margin-bottom: 40px; }
.contacts-list { display: flex; flex-direction: column; gap: 12px; }

.contact-card { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); flex-wrap: wrap; gap: 12px; }
.contact-name { font-size: 15px; font-weight: 600; color: #0f172a; margin-bottom: 4px; }
.contact-address { font-size: 13px; color: #64748b; }

.action-btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; transition: all 0.2s; }
.action-btn.call { background: #10b981; color: #fff; }
.action-btn.call:hover { background: #059669; }
.action-icon { width: 16px; height: 16px; }

@media (max-width: 640px) {
    .header-content h1 { font-size: 24px; }
    .contact-card { flex-direction: column; align-items: flex-start; }
}
</style>

<?php include __DIR__ . '/footer.php'; ?>
