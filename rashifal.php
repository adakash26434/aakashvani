<?php
require_once __DIR__ . '/config.php';
$lang=siteLang();
$isNepali=($lang!=='en');
$t=fn($ne,$en)=>$isNepali?$ne:$en;
$rashis=[
    ['id'=>'mesha','name'=>'मेष','symbol'=>'♈','color'=>'#ef4444'],
    ['id'=>'vrishabha','name'=>'वृष','symbol'=>'♉','color'=>'#10b981'],
    ['id'=>'mithuna','name'=>'मिथुन','symbol'=>'♊','color'=>'#f59e0b'],
    ['id'=>'karkata','name'=>'कर्कट','symbol'=>'♋','color'=>'#3b82f6'],
    ['id'=>'simha','name'=>'सिंह','symbol'=>'♌','color'=>'#ef4444'],
    ['id'=>'kanya','name'=>'कन्या','symbol'=>'♍','color'=>'#10b981'],
    ['id'=>'tula','name'=>'तुला','symbol'=>'♎','color'=>'#f59e0b'],
    ['id'=>'vrishchika','name'=>'वृश्चिक','symbol'=>'♏','color'=>'#3b82f6'],
    ['id'=>'dhanu','name'=>'धनु','symbol'=>'♐','color'=>'#ef4444'],
    ['id'=>'makara','name'=>'मकर','symbol'=>'♑','color'=>'#10b981'],
    ['id'=>'kumbha','name'=>'कुम्भ','symbol'=>'♒','color'=>'#f59e0b'],
    ['id'=>'meena','name'=>'मीन','symbol'=>'♓','color'=>'#3b82f6'],
];
?>
<!DOCTYPE html>
<html lang="<?=$isNepali?'ne':'en'?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$t('राशिफल','Horoscope')?> | आकाशवाणी</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .page-header{background:linear-gradient(135deg,var(--dark-900),var(--dark-800));padding:var(--space-12) 0;color:#fff}
        .rashi-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:var(--space-4)}
        @media(max-width:768px){.rashi-grid{grid-template-columns:repeat(4,1fr)}}
        @media(max-width:480px){.rashi-grid{grid-template-columns:repeat(3,1fr)}}
        .rashi-card{display:flex;flex-direction:column;align-items:center;gap:var(--space-2);padding:var(--space-4);background:#fff;border-radius:var(--radius-xl);border:1px solid var(--dark-100);text-decoration:none;transition:all var(--transition)}
        .rashi-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-lg);border-color:var(--rashi-color)}
        .rashi-symbol{font-size:2rem;color:var(--rashi-color)}
        .rashi-name{font-size:0.875rem;font-weight:600;color:var(--dark-900)}
        .rashi-element{font-size:0.75rem;color:var(--dark-400)}
        .section{padding:var(--space-12) 0}
    </style>
</head>
<body>
    <header class="site-header">
        <div class="header-main">
            <div class="container">
                <div class="flex items-center justify-between gap-4">
                    <a href="/" class="header-brand"><div class="brand-logo">आ</div><span class="brand-name"><?=$t('आकाशवाणी','Aakashvani')?></span></a>
                    <nav class="header-nav">
                        <a href="/" class="nav-link"><?=$t('गृह','Home')?></a>
                        <a href="/news.php" class="nav-link"><?=$t('समाचार','News')?></a>
                        <a href="/rashifal.php" class="nav-link active"><?=$t('राशिफल','Horoscope')?></a>
                    </nav>
                </div>
            </div>
        </div>
    </header>
    <section class="page-header">
        <div class="container">
            <h1 class="page-title" style="display:flex;align-items:center;gap:12px">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--primary)"><path d="M12 3l1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3z"/></svg>
                <?=$t('दैनिक राशिफल','Daily Horoscope')?>
            </h1>
            <p class="page-subtitle"><?=$t('आफ्नो राशिको आजको भाग्य हेर्नुहोस्','Check your today\'s fortune')?></p>
        </div>
    </section>
    <section class="section">
        <div class="container">
            <div class="rashi-grid">
                <?php foreach($rashis as $r):?>
                <a href="?rashi=<?=$r['id']?>" class="rashi-card" style="--rashi-color:<?=$r['color']?>">
                    <span class="rashi-symbol"><?=$r['symbol']?></span>
                    <span class="rashi-name"><?=$r['name']?></span>
                    <span class="rashi-element"><?=$t('राशि','Sign')?></span>
                </a>
                <?php endforeach;?>
            </div>
        </div>
    </section>
    <footer class="site-footer">
        <div class="container">
            <div class="footer-bottom" style="border:none;padding:0"><p class="footer-copyright">&copy; <?=date('Y')?> <?=$t('आकाशवाणी','Aakashvani')?></p></div>
        </div>
    </footer>
    <script src="/assets/js/app.js"></script>
</body>
</html>
