<?php
/**
 * आकाशवाणी — Tax Calculator
 * Income Tax Calculator for Nepal
 */
require_once __DIR__ . '/config.php';
$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;
?>
<!DOCTYPE html>
<html lang="<?= $isNepali ? 'ne' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t('कर क्याल्कुलेटर', 'Tax Calculator') ?> | <?= $t('आकाशवाणी', 'Aakashvani') ?></title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/premium.css">
    <style>
        body { background: var(--dark-950); color: var(--text-primary); font-family: 'Inter', 'Noto Sans Devanagari', sans-serif; }
        .calc-container { max-width: 700px; margin: 0 auto; padding: 2rem; }
        .calc-card { background: var(--dark-900); border-radius: 16px; padding: 2rem; border: 1px solid var(--dark-700); }
        .calc-title { font-size: 1.75rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--primary); }
        .form-group { margin-bottom: 1.25rem; }
        .form-label { display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--text-secondary); }
        .form-input { width: 100%; padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid var(--dark-600); background: var(--dark-800); color: var(--text-primary); font-size: 1rem; }
        .calc-btn { width: 100%; padding: 1rem; background: var(--primary); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 1rem; }
        .calc-btn:hover { background: var(--primary-dark); }
        .result-box { margin-top: 1.5rem; padding: 1.5rem; background: var(--dark-800); border-radius: 12px; border-left: 4px solid var(--primary); display: none; }
        .result-box.show { display: block; }
        .result-row { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--dark-700); }
        .result-row:last-child { border-bottom: none; font-weight: 700; color: var(--primary); font-size: 1.2rem; }
        .back-link { display: inline-flex; align-items: center; gap: 0.5rem; color: var(--text-secondary); text-decoration: none; margin-bottom: 1.5rem; }
        .back-link:hover { color: var(--primary); }
        .tax-brackets { margin-top: 2rem; }
        .bracket-table { width: 100%; border-collapse: collapse; }
        .bracket-table th, .bracket-table td { padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--dark-700); }
        .bracket-table th { color: var(--primary); font-weight: 600; }
        .bracket-table td { color: var(--text-secondary); }
    </style>
</head>
<body>
    <div class="calc-container">
        <a href="/tools.php" class="back-link">← <?= $t('← Tools मा फर्कनुस्', '← Back to Tools') ?></a>
        
        <div class="calc-card">
            <h1 class="calc-title"><?= $t('आयकर क्याल्कुलेटर', 'Income Tax Calculator') ?></h1>
            
            <div class="form-group">
                <label class="form-label"><?= $t('मासिक तलब (रु.)', 'Monthly Salary (Rs.)') ?></label>
                <input type="number" id="monthlySalary" class="form-input" placeholder="50000" min="0">
            </div>
            
            <div class="form-group">
                <label class="form-label"><?= $t('उपदान उपदान (भविष्य निवृत्ति कोष)', 'Gratuity/Pension Fund (%)') ?></label>
                <select id="pensionRate" class="form-input">
                    <option value="0"><?= $t('छैन', 'None') ?></option>
                    <option value="10"><?= $t('10% (छुट्टी बापत)', '10% (Leave Encashment)') ?></option>
                    <option value="15"><?= $t('15% (निलम्बित रकम)', '15% (Suspense Amount)') ?></option>
                </select>
            </div>
            
            <button onclick="calculateTax()" class="calc-btn"><?= $t('कर गणना गर्नुस्', 'Calculate Tax') ?></button>
            
            <div id="result" class="result-box">
                <div class="result-row"><span><?= $t('मासिक तलब', 'Monthly Salary') ?></span><span id="r-salary">-</span></div>
                <div class="result-row"><span><?= $t('वार्षिक तलब', 'Annual Salary') ?></span><span id="r-annual">-</span></div>
                <div class="result-row"><span><?= $t('घटाउने (10% छुट)', 'Deduction (10% Exemption)') ?></span><span id="r-deduct">-</span></div>
                <div class="result-row"><span><?= $t('कर योग्य आय', 'Taxable Income') ?></span><span id="r-taxable">-</span></div>
                <div class="result-row"><span><?= $t('वार्षिक कर', 'Annual Tax') ?></span><span id="r-tax">-</span></div>
                <div class="result-row"><span><?= $t('मासिक कर', 'Monthly Tax') ?></span><span id="r-monthly-tax">-</span></div>
            </div>
        </div>
        
        <div class="calc-card tax-brackets">
            <h2 style="color: var(--primary); margin-bottom: 1rem;"><?= $t('कर दर (नयाँ शिर्षक)', 'Tax Brackets (New Structure)') ?></h2>
            <table class="bracket-table">
                <tr><th><?= $t('आय श्रेणी (रु.)', 'Income Range (Rs.)') ?></th><th><?= $t('दर', 'Rate') ?></th></tr>
                <tr><td>0 - 500,000</td><td><?= $t('निःशुल्क', 'NIL') ?></td></tr>
                <tr><td>500,001 - 700,000</td><td>10%</td></tr>
                <tr><td>700,001 - 1,000,000</td><td>20%</td></tr>
                <tr><td>1,000,001 - 2,000,000</td><td>30%</td></tr>
                <tr><td>2,000,001+</td><td>36%</td></tr>
            </table>
        </div>
    </div>
    
    <script>
    function calculateTax() {
        const salary = parseFloat(document.getElementById('monthlySalary').value) || 0;
        const annual = salary * 12;
        const deduction = Math.min(annual * 0.1, 500000);
        const taxable = Math.max(0, annual - deduction);
        
        let tax = 0;
        if (taxable > 2000000) {
            tax = (taxable - 2000000) * 0.36 + 450000;
        } else if (taxable > 1000000) {
            tax = (taxable - 1000000) * 0.30 + 150000;
        } else if (taxable > 700000) {
            tax = (taxable - 700000) * 0.20 + 20000;
        } else if (taxable > 500000) {
            tax = (taxable - 500000) * 0.10;
        }
        
        document.getElementById('r-salary').textContent = 'रु. ' + salary.toLocaleString();
        document.getElementById('r-annual').textContent = 'रु. ' + annual.toLocaleString();
        document.getElementById('r-deduct').textContent = 'रु. ' + deduction.toLocaleString();
        document.getElementById('r-taxable').textContent = 'रु. ' + taxable.toLocaleString();
        document.getElementById('r-tax').textContent = 'रु. ' + Math.round(tax).toLocaleString();
        document.getElementById('r-monthly-tax').textContent = 'रु. ' + Math.round(tax / 12).toLocaleString();
        document.getElementById('result').classList.add('show');
    }
    </script>
</body>
</html>
