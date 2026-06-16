<?php
/**
 * आकाशवाणी — rashifal.php (World-Class Horoscope)
 * Premium rashifal with clean, modern design
 */

$pageTitle = 'राशिफल | आकाशवाणी';

include __DIR__ . '/header.php';

$lang = siteLang();
$isNepali = ($lang !== 'en');

// Rashis
$rashis = [
    ['id' => 'mesha', 'name' => 'मेष', 'symbol' => '♈', 'element' => 'अग्नि', 'color' => '#ef4444'],
    ['id' => 'vrishabha', 'name' => 'वृष', 'symbol' => '♉', 'element' => 'पृथ्वी', 'color' => '#10b981'],
    ['id' => 'mithuna', 'name' => 'मिथुन', 'symbol' => '♊', 'element' => 'वायु', 'color' => '#f59e0b'],
    ['id' => 'karkata', 'name' => 'कर्कट', 'symbol' => '♋', 'element' => 'जल', 'color' => '#3b82f6'],
    ['id' => 'simha', 'name' => 'सिंह', 'symbol' => '♌', 'element' => 'अग्नि', 'color' => '#ef4444'],
    ['id' => 'kanya', 'name' => 'कन्या', 'symbol' => '♍', 'element' => 'पृथ्वी', 'color' => '#10b981'],
    ['id' => 'tula', 'name' => 'तुला', 'symbol' => '♎', 'element' => 'वायु', 'color' => '#f59e0b'],
    ['id' => 'vrishchika', 'name' => 'वृश्चिक', 'symbol' => '♏', 'element' => 'जल', 'color' => '#3b82f6'],
    ['id' => 'dhanu', 'name' => 'धनु', 'symbol' => '♐', 'element' => 'अग्नि', 'color' => '#ef4444'],
    ['id' => 'makara', 'name' => 'मकर', 'symbol' => '♑', 'element' => 'पृथ्वी', 'color' => '#10b981'],
    ['id' => 'kumbha', 'name' => 'कुम्भ', 'symbol' => '♒', 'element' => 'वायु', 'color' => '#f59e0b'],
    ['id' => 'meena', 'name' => 'मीन', 'symbol' => '♓', 'element' => 'जल', 'color' => '#3b82f6'],
];

// Sample predictions
$predictions = [
    'mesha' => [
        'luck' => 85,
        'love' => 78,
        'career' => 72,
        'health' => 90,
        'text' => $isNepali 
            ? 'आज तपाईंको मेहनतले सफलता ल्याउनेछ। नयाँ अवसरहरू आउनेछन्।'
            : 'Today your hard work will bring success. New opportunities will arise.'
    ],
    'vrishabha' => [
        'luck' => 78,
        'love' => 85,
        'career' => 80,
        'health' => 75,
        'text' => $isNepali
            ? 'आज प्रेम र सम्बन्धमा सुधार हुनेछ। आर्थिक स्थिति बलियो हुनेछ।'
            : 'Today there will be improvement in love and relationships. Financial situation will strengthen.'
    ],
];

// Get selected rashi
$selectedRashi = isset($_GET['rashi']) ? $_GET['rashi'] : 'mesha';
$currentRashi = null;
foreach ($rashis as $r) {
    if ($r['id'] === $selectedRashi) {
        $currentRashi = $r;
        break;
    }
}
if (!$currentRashi) $currentRashi = $rashis[0];

$prediction = $predictions[$selectedRashi] ?? [
    'luck' => 75, 'love' => 70, 'career' => 72, 'health' => 80,
    'text' => $isNepali ? 'आज तपाईंले राम्रो दिन बिताउनेछौं।' : 'Today you will have a good day.'
];
?>

<!-- Page Header -->
<section class="rashifal-header">
    <div class="container">
        <div class="header-content">
            <div class="header-title">
                <i data-lucide="sparkles" class="icon-lg"></i>
                <h1><?= $isNepali ? 'दैनिक राशिफल' : 'Daily Horoscope' ?></h1>
            </div>
            <p class="header-subtitle">
                <?= $isNepali ? 'तपाईंको आजको राशिफल हेर्नुहोस्' : 'Check your today\'s horoscope' ?>
            </p>
        </div>
    </div>
</section>

<!-- Main Content -->
<main class="rashifal-main">
    <div class="container">
        <div class="rashifal-layout">
            
            <!-- Rashi Selection -->
            <section class="rashi-grid-section">
                <h2 class="section-title">
                    <i data-lucide="star" class="section-icon"></i>
                    <?= $isNepali ? 'आफ्नो राशि चुन्नुहोस्' : 'Select Your Rashi' ?>
                </h2>
                
                <div class="rashi-grid">
                    <?php foreach ($rashis as $r): ?>
                    <a href="?rashi=<?= $r['id'] ?>" 
                       class="rashi-card <?= $r['id'] === $selectedRashi ? 'active' : '' ?>"
                       style="--rashi-color: <?= $r['color'] ?>">
                        <span class="rashi-symbol"><?= $r['symbol'] ?></span>
                        <span class="rashi-name"><?= $r['name'] ?></span>
                        <span class="rashi-element"><?= $r['element'] ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </section>
            
            <!-- Selected Rashi Details -->
            <section class="rashi-detail">
                <div class="detail-header" style="--rashi-color: <?= $currentRashi['color'] ?>">
                    <span class="detail-symbol"><?= $currentRashi['symbol'] ?></span>
                    <div class="detail-info">
                        <h2 class="detail-name"><?= $currentRashi['name'] ?></h2>
                        <span class="detail-element"><?= $currentRashi['element'] ?> <?= $isNepali ? 'राशि' : 'Sign' ?></span>
                    </div>
                </div>
                
                <div class="detail-prediction">
                    <p><?= htmlspecialchars($prediction['text']) ?></p>
                </div>
                
                <!-- Luck Meter -->
                <div class="luck-meter">
                    <h3 class="meter-title">
                        <i data-lucide="gauge" class="meter-icon"></i>
                        <?= $isNepali ? 'आजको भाग्य' : 'Today\'s Fortune' ?>
                    </h3>
                    
                    <div class="meter-grid">
                        <div class="meter-item">
                            <div class="meter-header">
                                <span class="meter-label"><?= $isNepali ? 'भाग्य' : 'Luck' ?></span>
                                <span class="meter-value"><?= $prediction['luck'] ?>%</span>
                            </div>
                            <div class="meter-bar">
                                <div class="meter-fill" style="width: <?= $prediction['luck'] ?>%; background: #10b981"></div>
                            </div>
                        </div>
                        
                        <div class="meter-item">
                            <div class="meter-header">
                                <span class="meter-label"><?= $isNepali ? 'प्रेम' : 'Love' ?></span>
                                <span class="meter-value"><?= $prediction['love'] ?>%</span>
                            </div>
                            <div class="meter-bar">
                                <div class="meter-fill" style="width: <?= $prediction['love'] ?>%; background: #ef4444"></div>
                            </div>
                        </div>
                        
                        <div class="meter-item">
                            <div class="meter-header">
                                <span class="meter-label"><?= $isNepali ? 'कार्य' : 'Career' ?></span>
                                <span class="meter-value"><?= $prediction['career'] ?>%</span>
                            </div>
                            <div class="meter-bar">
                                <div class="meter-fill" style="width: <?= $prediction['career'] ?>%; background: #3b82f6"></div>
                            </div>
                        </div>
                        
                        <div class="meter-item">
                            <div class="meter-header">
                                <span class="meter-label"><?= $isNepali ? 'स्वास्थ्य' : 'Health' ?></span>
                                <span class="meter-value"><?= $prediction['health'] ?>%</span>
                            </div>
                            <div class="meter-bar">
                                <div class="meter-fill" style="width: <?= $prediction['health'] ?>%; background: #f59e0b"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Lucky Numbers -->
                <div class="lucky-section">
                    <h3 class="lucky-title">
                        <i data-lucide="clover" class="lucky-icon"></i>
                        <?= $isNepali ? 'शुभ अंक र रंग' : 'Lucky Numbers & Colors' ?>
                    </h3>
                    <div class="lucky-grid">
                        <div class="lucky-item">
                            <span class="lucky-label"><?= $isNepali ? 'शुभ अंक' : 'Lucky Number' ?></span>
                            <span class="lucky-value numbers">3, 7, 15</span>
                        </div>
                        <div class="lucky-item">
                            <span class="lucky-label"><?= $isNepali ? 'शुभ दिन' : 'Lucky Day' ?></span>
                            <span class="lucky-value"><?= $isNepali ? 'मंगलबार' : 'Tuesday' ?></span>
                        </div>
                        <div class="lucky-item">
                            <span class="lucky-label"><?= $isNepali ? 'शुभ रंग' : 'Lucky Color' ?></span>
                            <span class="lucky-value colors">
                                <span style="background:#ef4444"></span>
                                <span style="background:#10b981"></span>
                                <span style="background:#f59e0b"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </section>
            
        </div>
    </div>
</main>

<style>
/* ═══════════════════════════════════════════════════════════════
   RASHIFAL PAGE STYLES
   ═══════════════════════════════════════════════════════════════ */

/* Header */
.rashifal-header {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    padding: 40px 0;
    color: #fff;
    text-align: center;
}

.header-title {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-bottom: 12px;
}

.header-title i { color: #10b981; }

.header-title h1 {
    font-size: 32px;
    font-weight: 800;
    color: #fff;
}

.header-subtitle {
    font-size: 16px;
    color: #94a3b8;
}

/* Main */
.rashifal-main {
    padding: 40px 0;
    background: #f8fafc;
}

.rashifal-layout {
    display: flex;
    flex-direction: column;
    gap: 40px;
}

/* Section Title */
.section-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 20px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 20px;
}

.section-icon { color: #10b981; width: 22px; height: 22px; }

/* Rashi Grid */
.rashi-grid-section {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.rashi-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 12px;
}

@media (max-width: 768px) {
    .rashi-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (max-width: 480px) {
    .rashi-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.rashi-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 16px 12px;
    background: #f8fafc;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.2s;
    border: 2px solid transparent;
}

.rashi-card:hover {
    background: var(--rashi-color);
    transform: translateY(-2px);
}

.rashi-card:hover .rashi-symbol,
.rashi-card:hover .rashi-name,
.rashi-card:hover .rashi-element {
    color: #fff;
}

.rashi-card.active {
    background: var(--rashi-color);
    border-color: var(--rashi-color);
}

.rashi-card.active .rashi-symbol,
.rashi-card.active .rashi-name,
.rashi-card.active .rashi-element {
    color: #fff;
}

.rashi-symbol {
    font-size: 32px;
    color: var(--rashi-color);
    transition: color 0.2s;
}

.rashi-name {
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
    transition: color 0.2s;
}

.rashi-element {
    font-size: 11px;
    color: #94a3b8;
    transition: color 0.2s;
}

/* Rashi Detail */
.rashi-detail {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.detail-header {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 24px;
    background: linear-gradient(135deg, var(--rashi-color), color-mix(in srgb, var(--rashi-color) 70%, #000));
    color: #fff;
}

.detail-symbol {
    font-size: 56px;
}

.detail-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.detail-name {
    font-size: 28px;
    font-weight: 800;
    color: #fff;
}

.detail-element {
    font-size: 14px;
    opacity: 0.9;
}

/* Prediction */
.detail-prediction {
    padding: 24px;
    border-bottom: 1px solid #e2e8f0;
}

.detail-prediction p {
    font-size: 16px;
    line-height: 1.7;
    color: #475569;
}

/* Luck Meter */
.luck-meter {
    padding: 24px;
    border-bottom: 1px solid #e2e8f0;
}

.meter-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 20px;
}

.meter-icon { color: #10b981; width: 20px; height: 20px; }

.meter-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

@media (max-width: 480px) {
    .meter-grid {
        grid-template-columns: 1fr;
    }
}

.meter-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.meter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.meter-label {
    font-size: 13px;
    font-weight: 500;
    color: #64748b;
}

.meter-value {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
}

.meter-bar {
    height: 8px;
    background: #f1f5f9;
    border-radius: 4px;
    overflow: hidden;
}

.meter-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.5s ease;
}

/* Lucky Section */
.lucky-section {
    padding: 24px;
}

.lucky-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 20px;
}

.lucky-icon { color: #10b981; width: 20px; height: 20px; }

.lucky-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

@media (max-width: 480px) {
    .lucky-grid {
        grid-template-columns: 1fr;
    }
}

.lucky-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 16px;
    background: #f8fafc;
    border-radius: 12px;
}

.lucky-label {
    font-size: 12px;
    color: #94a3b8;
}

.lucky-value {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
}

.lucky-value.numbers {
    letter-spacing: 4px;
}

.lucky-value.colors {
    display: flex;
    gap: 6px;
}

.lucky-value.colors span {
    width: 24px;
    height: 24px;
    border-radius: 6px;
}

/* Responsive */
@media (max-width: 640px) {
    .header-title h1 { font-size: 24px; }
    .detail-symbol { font-size: 40px; }
    .detail-name { font-size: 22px; }
}
</style>

<?php include __DIR__ . '/footer.php'; ?>
