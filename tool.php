<?php
/**
 * आकाशवाणी — Tool Detail Page
 */
require_once __DIR__ . '/config.php';
$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;

$toolId = $_GET['id'] ?? 'tax-calculator';
$tools = [
    'tax-calculator' => ['name' => 'कर क्यालकुलेटर', 'en' => 'Tax Calculator', 'desc' => 'आयकर गणना'],
    'unit-converter' => ['name' => 'इकाई रूपान्तरक', 'en' => 'Unit Converter', 'desc' => 'इकाई रूपान्तरण'],
    'currency-converter' => ['name' => 'मुद्रा रूपान्तरक', 'en' => 'Currency Converter', 'desc' => 'मुद्रा रूपान्तरण'],
    'bmi-calculator' => ['name' => 'BMI क्यालकुलेटर', 'en' => 'BMI Calculator', 'desc' => 'BMI गणना'],
];
$tool = $tools[$toolId] ?? ['name' => 'टूल', 'en' => 'Tool', 'desc' => ''];
?>
<!DOCTYPE html>
<html lang="<?= $isNepali ? 'ne' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isNepali ? $tool['name'] : $tool['en'] ?> | <?= $t('आकाशवाणी', 'Aakashvani') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .tool-page { padding: var(--space-8) 0; }
        .tool-card { background: #fff; border-radius: var(--radius-xl); padding: var(--space-8); box-shadow: var(--shadow); }
        .tool-result { background: var(--primary-50); border-radius: var(--radius-lg); padding: var(--space-6); text-align: center; margin-top: var(--space-6); }
        .tool-result-value { font-size: 2rem; font-weight: 800; color: var(--primary); }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="header-main">
            <div class="container">
                <div class="header-grid">
                    <a href="/" class="header-brand">
                        <div class="brand-logo">आ</div>
                        <div class="brand-text">
                            <h1><?= $t('आकाशवाणी', 'Aakashvani') ?></h1>
                            <span><?= $t('सूचनाको खुला आकाश', 'Your Gateway to Information') ?></span>
                        </div>
                    </a>
                    <nav class="main-nav">
                        <div class="nav-list">
                            <a href="/" class="nav-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1rem;height:1rem"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg><?= $t('गृह', 'Home') ?></a>
                            <a href="/tools.php" class="nav-link active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1rem;height:1rem"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg><?= $t('टूलहरू', 'Tools') ?></a>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <section class="page-header">
        <div class="container">
            <h1 class="page-title"><?= $isNepali ? $tool['name'] : $tool['en'] ?></h1>
            <p class="page-subtitle"><?= $isNepali ? $tool['desc'] : $tool['desc'] ?></p>
        </div>
    </section>

    <section class="tool-page">
        <div class="container" style="max-width: 600px;">
            <div class="tool-card">
                <?php if ($toolId === 'tax-calculator'): ?>
                <div class="form-group">
                    <label class="form-label"><?= $t('वार्षिक आय (रु.)', 'Annual Income (Rs.)') ?></label>
                    <input type="number" class="input input-lg" id="income" placeholder="500000" oninput="calculateTax()">
                </div>
                <div class="tool-result">
                    <p style="color: var(--dark-500); margin-bottom: var(--space-2);"><?= $t('अनुमानित कर', 'Estimated Tax') ?></p>
                    <div class="tool-result-value" id="tax-result">रु. 0</div>
                </div>
                <script>
                function calculateTax() {
                    const income = parseFloat(document.getElementById('income').value) || 0;
                    let tax = 0;
                    if (income > 500000) tax = (income - 500000) * 0.01;
                    if (income > 700000) tax = 2000 + (income - 700000) * 0.1;
                    if (income > 1000000) tax = 32000 + (income - 1000000) * 0.2;
                    if (income > 2000000) tax = 92000 + (income - 2000000) * 0.3;
                    if (income > 5000000) tax = 192000 + (income - 5000000) * 0.36;
                    document.getElementById('tax-result').textContent = 'रु. ' + Math.round(tax).toLocaleString();
                }
                </script>
                <?php elseif ($toolId === 'bmi-calculator'): ?>
                <div class="form-group">
                    <label class="form-label"><?= $t('तौल (kg)', 'Weight (kg)') ?></label>
                    <input type="number" class="input input-lg" id="weight" placeholder="70" oninput="calculateBMI()">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= $t('उचाई (cm)', 'Height (cm)') ?></label>
                    <input type="number" class="input input-lg" id="height" placeholder="170" oninput="calculateBMI()">
                </div>
                <div class="tool-result">
                    <p style="color: var(--dark-500); margin-bottom: var(--space-2);"><?= $t('तपाईंको BMI', 'Your BMI') ?></p>
                    <div class="tool-result-value" id="bmi-result">0</div>
                </div>
                <script>
                function calculateBMI() {
                    const weight = parseFloat(document.getElementById('weight').value) || 0;
                    const height = parseFloat(document.getElementById('height').value) || 0;
                    if (weight > 0 && height > 0) {
                        const bmi = weight / Math.pow(height / 100, 2);
                        document.getElementById('bmi-result').textContent = bmi.toFixed(1);
                    }
                }
                </script>
                <?php else: ?>
                <div class="alert alert-info">
                    <?= $t('यो टूल छिट्टै आउँदैछ।', 'This tool is coming soon.') ?>
                </div>
                <?php endif; ?>
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
</body>
</html>