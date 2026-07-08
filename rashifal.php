<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/bs-date.php';
$lang=siteLang();
$isNepali=($lang!=='en');
$t=fn($ne,$en)=>$isNepali?$ne:$en;
$todayBS=getTodayBS();
$selectedRashi=isset($_GET['rashi'])?sanitize($_GET['rashi']):null;
$rashis=[
    ['id'=>'mesha','name'=>'मेष','symbol'=>'♈','color'=>'#ef4444','en'=>'Aries','element'=>'अग्नि','lord'=>'मंगल'],
    ['id'=>'vrishabha','name'=>'वृष','symbol'=>'♉','color'=>'#10b981','en'=>'Taurus','element'=>'पृथ्वी','lord'=>'शुक्र'],
    ['id'=>'mithuna','name'=>'मिथुन','symbol'=>'♊','color'=>'#f59e0b','en'=>'Gemini','element'=>'वायु','lord'=>'बुध'],
    ['id'=>'karkata','name'=>'कर्कट','symbol'=>'♋','color'=>'#3b82f6','en'=>'Cancer','element'=>'जल','lord'=>'चन्द्र'],
    ['id'=>'simha','name'=>'सिंह','symbol'=>'♌','color'=>'#ef4444','en'=>'Leo','element'=>'अग्नि','lord'=>'सूर्य'],
    ['id'=>'kanya','name'=>'कन्या','symbol'=>'♍','color'=>'#10b981','en'=>'Virgo','element'=>'पृथ्वी','lord'=>'बुध'],
    ['id'=>'tula','name'=>'तुला','symbol'=>'♎','color'=>'#f59e0b','en'=>'Libra','element'=>'वायु','lord'=>'शुक्र'],
    ['id'=>'vrishchika','name'=>'वृश्चिक','symbol'=>'♏','color'=>'#3b82f6','en'=>'Scorpio','element'=>'जल','lord'=>'मंगल'],
    ['id'=>'dhanu','name'=>'धनु','symbol'=>'♐','color'=>'#ef4444','en'=>'Sagittarius','element'=>'अग्नि','lord'=>'बृहस्पति'],
    ['id'=>'makara','name'=>'मकर','symbol'=>'♑','color'=>'#10b981','en'=>'Capricorn','element'=>'पृथ्वी','lord'=>'शनि'],
    ['id'=>'kumbha','name'=>'कुम्भ','symbol'=>'♒','color'=>'#f59e0b','en'=>'Aquarius','element'=>'वायु','lord'=>'शनि'],
    ['id'=>'meena','name'=>'मीन','symbol'=>'♓','color'=>'#3b82f6','en'=>'Pisces','element'=>'जल','lord'=>'बृहस्पति'],
];
$selectedRashiInfo=null;
if($selectedRashi){foreach($rashis as$r){if($r['id']===$selectedRashi){$selectedRashiInfo=$r;break;}}}
?>
<!DOCTYPE html>
<html lang="<?=$isNepali?'ne':'en'?>">
<head>
    <meta charset="UTF-8">
    <meta property="og:title" content="<?= $t('आकाशवाणी - Nepal Information Portal', 'Aakashvani - Nepal Information Portal') ?>">
    <meta property="og:description" content="<?= $t('नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म।', 'Nepal\'s most trusted information platform.') ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$t('राशिफल','Horoscope')?> | आकाशवाणी</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/premium.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
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
        .rashi-grid{grid-template-columns:repeat(6,1fr)}
        @media(max-width:1024px){.rashi-grid{grid-template-columns:repeat(4,1fr)}}
        @media(max-width:768px){.rashi-grid{grid-template-columns:repeat(3,1fr)}}
        .rashi-card.active{background:var(--rashi-color)}
        .rashi-card.active .rashi-symbol,.rashi-card.active .rashi-name,.rashi-card.active .rashi-element{color:#fff}
        .rashifal-content{background:#fff;border-radius:var(--radius-xl);padding:var(--space-8);box-shadow:var(--shadow);margin-top:var(--space-6)}
        .rashifal-section{margin-bottom:var(--space-6);padding-bottom:var(--space-6);border-bottom:1px solid var(--dark-100)}
        .rashifal-section:last-child{border-bottom:none}
        .rashifal-title{font-size:1.125rem;font-weight:700;color:var(--dark-900);margin-bottom:var(--space-4)}
        .rashifal-text{font-size:1rem;line-height:1.8;color:var(--dark-700)}
        .rashifal-meta{display:grid;grid-template-columns:repeat(3,1fr);gap:var(--space-4);margin-top:var(--space-6)}
        .rashifal-badge{text-align:center;padding:var(--space-4);background:var(--dark-50);border-radius:var(--radius-lg)}
        .rashifal-badge-value{font-size:1.5rem;font-weight:700;color:var(--primary)}
        .rashifal-badge-label{font-size:0.75rem;color:var(--dark-500);margin-top:var(--space-1)}
        .loading-spinner{display:flex;justify-content:center;padding:var(--space-12)}
        .spinner{width:40px;height:40px;border:3px solid var(--dark-200);border-top-color:var(--primary);border-radius:50%;animation:spin 1s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}}
        .error-message{text-align:center;padding:var(--space-8);background:var(--error-50);border-radius:var(--radius-xl);color:var(--error)}
        .back-link{display:inline-flex;align-items:center;gap:var(--space-2);padding:var(--space-2) var(--space-4);background:var(--dark-100);border-radius:var(--radius-full);color:var(--dark-700);text-decoration:none;font-size:0.875rem;margin-bottom:var(--space-4)}
        .rashi-hero{text-align:center;padding:var(--space-8);background:linear-gradient(135deg,#1e1b4b,#312e81);border-radius:var(--radius-xl);margin-bottom:var(--space-6)}
        .rashi-hero-symbol{font-size:4rem;margin-bottom:var(--space-4)}
        .rashi-hero-name{font-size:2rem;font-weight:800;color:#fff}
        @media(max-width:768px){.rashifal-content{padding:var(--space-4)}.rashifal-meta{grid-template-columns:1fr}.rashi-hero-name{font-size:1.5rem}.rashi-hero-symbol{font-size:3rem}}
    </style>
</head>
<body>
    <!-- TOP BAR -->
    <div class="tp-topbar">
        <div class="tp-container">
            <div class="tp-topbar-inner">
                <div class="tp-topbar-left">
                    <span class="tp-date"><?= date('l, j F Y') ?></span>
                    <span class="tp-topbar-links"><a href="/unicode">Unicode</a><a href="?lang=en">English</a></span>
                </div>
                <div class="tp-topbar-right">
                    <a href="#" aria-label="Facebook"><i data-lucide="facebook"></i></a>
                    <a href="#" aria-label="Twitter"><i data-lucide="twitter"></i></a>
                    <a href="#" aria-label="YouTube"><i data-lucide="youtube"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- MID HEADER -->
    <div class="tp-header-mid">
        <div class="tp-container">
            <div class="tp-header-mid-inner">
                <a href="/" class="tp-logo">
                    <img src="/favicon.svg" alt="Aakashvani" width="48" height="48">
                    <div class="tp-logo-text">
                        <span class="tp-logo-name"><?= $t('आकाशवाणी', 'Aakashvani') ?></span>
                        <span class="tp-logo-tagline"><?= $t('सूचनाको खुला आकाश', 'Your Gateway to Information') ?></span>
                    </div>
                </a>
                <div class="tp-header-ads"></div>
            </div>
        </div>
    </div>

    <!-- STICKY NAV -->
    <nav class="tp-nav" id="tpNav">
        <div class="tp-container">
            <div class="tp-nav-inner">
                <button class="tp-nav-toggle" id="navToggle" aria-label="Menu"><i data-lucide="menu"></i></button>
                <ul class="tp-nav-list" id="navList">
                    <li><a href="/"><?= $t('गृह', 'Home') ?></a></li>
                    <li><a href="/news.php"><?= $t('समाचार', 'News') ?></a></li>
                    <li><a href="/nepali-patro.php"><?= $t('पात्रो', 'Calendar') ?></a></li>
                    <li><a href="/rashifal.php"><?= $t('राशिफल', 'Horoscope') ?></a></li>
                    <li><a href="/ipo-tracker.php"><?= $t('NEPSE/IPO', 'NEPSE/IPO') ?></a></li>
                    <li><a href="/tools.php"><?= $t('टूलहरू', 'Tools') ?></a></li>
                    <li><a href="/gov-services.php"><?= $t('सरकारी', 'Gov') ?></a></li>
                    <li><a href="/weather.php"><?= $t('मौसम', 'Weather') ?></a></li>
                    <li><a href="/cricket.php"><?= $t('क्रिकेट', 'Cricket') ?></a></li>
                    <li><a href="/tenders.php"><?= $t('टेन्डर', 'Tenders') ?></a></li>
                    <li><a href="/emergency.php"><?= $t('आपतकालीन', 'Emergency') ?></a></li>
                </ul>
                <div class="tp-nav-search">
                    <button class="tp-search-btn" id="searchToggle" aria-label="Search"><i data-lucide="search"></i></button>
                </div>
            </div>
            <div class="tp-search-bar" id="searchBar" style="display:none">
                <input type="search" placeholder="<?= $t('खोज्नुहोस्...', 'Search...') ?>" id="searchInput">
            </div>
        </div>
    </nav>

    <!-- MARKET TICKER -->
    <div class="tp-market-bar">
        <div class="tp-container">
            <div class="tp-market-inner">
                <span class="tp-market-item"><i data-lucide="trending-up"></i><span class="tp-mkt-label">NEPSE</span><span class="tp-mkt-value" id="nepse-value">...</span><span class="tp-mkt-change" id="nepse-change">...</span></span>
                <span class="tp-market-divider">|</span>
                <span class="tp-market-item"><i data-lucide="gem"></i><span class="tp-mkt-label"><?= $t('सुन', 'Gold') ?></span><span class="tp-mkt-value" id="gold-value">...</span></span>
                <span class="tp-market-divider">|</span>
                <span class="tp-market-item"><i data-lucide="dollar-sign"></i><span class="tp-mkt-label">USD</span><span class="tp-mkt-value" id="forex-value">...</span></span>
                <span class="tp-market-divider">|</span>
                <span class="tp-market-item"><i data-lucide="fuel"></i><span class="tp-mkt-label"><?= $t('पेट्रोल', 'Petrol') ?></span><span class="tp-mkt-value" id="petrol-value">...</span></span>
            </div>
        </div>
    </div>

    <section class="page-header">
        <div class="container">
            <h1 class="page-title" style="display:flex;align-items:center;gap:12px">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--primary)"><path d="M12 3l1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3z"/></svg>
                <?=$t('दैनिक राशिफल','Daily Horoscope')?>
            </h1>
            <p class="page-subtitle"><?=$t('आफ्नो राशिको आजको भाग्य हेर्नुहोस्','Check your today\'s fortune')?></p>
        </div>
    </section>
    <section class="section" style="background:var(--dark-50)">
        <div class="container">
            <h2 class="text-xl font-bold mb-6" style="text-align:center"><?=$t('आफ्नो राशि चयन गर्नुहोस्','Select Your Rashi')?></h2>
            <div class="rashi-grid" style="grid-template-columns:repeat(6,1fr)">
                <?php foreach($rashis as $r):?>
                <a href="?rashi=<?=$r['id']?>" class="rashi-card <?=$selectedRashi===$r['id']?'active':''?>" style="--rashi-color:<?=$r['color']?>">
                    <span class="rashi-symbol"><?=$r['symbol']?></span>
                    <span class="rashi-name"><?=$r['name']?></span>
                    <span class="rashi-element"><?=$r['en']?></span>
                </a>
                <?php endforeach;?>
            </div>
        </div>
    </section>
    <?php if($selectedRashiInfo):?>
    <section class="section">
        <div class="container" style="max-width:800px">
            <a href="/rashifal.php" class="back-link">← <?=$t('सबै राशिहरू','All Rashis')?></a>
            <div class="rashi-hero">
                <div class="rashi-hero-symbol" style="color:<?=$selectedRashiInfo['color']?>"><?=$selectedRashiInfo['symbol']?></div>
                <h1 class="rashi-hero-name"><?=$selectedRashiInfo['name']?> (<?=$selectedRashiInfo['en']?>)</h1>
                <p style="color:#fff;opacity:0.8"><?=$selectedRashiInfo['element']?> | <?=$selectedRashiInfo['lord']?></p>
            </div>
            <div id="rashifal-loading" class="loading-spinner"><div class="spinner"></div></div>
            <div id="rashifal-content" style="display:none">
                <div class="rashifal-meta" id="rashifal-meta"></div>
                <div id="rashifal-details"></div>
            </div>
            <div id="rashifal-error" class="error-message" style="display:none"><?=$t('राशिफल लोड हुन सकेन।','Failed to load horoscope.')?></div>
        </div>
    </section>
    <?php else:?>
    <section class="section">
        <div class="container" style="max-width:800px">
            <div class="rashifal-content">
                <div class="rashifal-section">
                    <h3 class="rashifal-title"><?=$t('आजको राशिफल','Todays Horoscope')?></h3>
                    <p class="rashifal-text"><?=$t('माथि राशिमा क्लिक गर्नुहोस् र आफ्नो दैनिक राशिफल हेर्नुहोस्।','Click on your rashi above to view daily horoscope.')?></p>
                </div>
                <div class="rashifal-meta">
                    <div class="rashifal-badge"><div class="rashifal-badge-value"><?=$todayBS['day']?></div><div class="rashifal-badge-label"><?=$t('आजको मिति','Todays Date')?></div></div>
                    <div class="rashifal-badge"><div class="rashifal-badge-value"><?=$todayBS['year']?></div><div class="rashifal-badge-label"><?=$t('बि.स.','B.S. Year')?></div></div>
                    <div class="rashifal-badge"><div class="rashifal-badge-value"><?=$todayBS['weekday']?></div><div class="rashifal-badge-label"><?=$t('हप्ताको दिन','Day of Week')?></div></div>
                </div>
            </div>
        </div>
    </section>
    <?php endif;?>
    <!-- FOOTER -->
    <footer class="tp-footer">
        <div class="tp-container">
            <div class="tp-footer-grid">
                <div class="tp-footer-brand">
                    <a href="/" class="tp-footer-logo">
                        <img src="/favicon.svg" alt="Aakashvani" width="40" height="40">
                        <div>
                            <span class="tp-footer-name"><?= $t('आकाशवाणी', 'Aakashvani') ?></span>
                            <span class="tp-footer-tagline"><?= $t('सूचनाको खुला आकाश', 'Your Gateway to Information') ?></span>
                        </div>
                    </a>
                    <p class="tp-footer-desc"><?= $t('नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म।', 'Nepal's most trusted information platform.') ?></p>
                    <div class="tp-footer-social">
                        <a href="#" aria-label="Facebook"><i data-lucide="facebook"></i></a>
                        <a href="#" aria-label="Twitter"><i data-lucide="twitter"></i></a>
                        <a href="#" aria-label="YouTube"><i data-lucide="youtube"></i></a>
                    </div>
                </div>
                <div>
                    <h4><?= $t('लिंकहरू', 'Links') ?></h4>
                    <ul>
                        <li><a href="/"><?= $t('गृहपृष्ठ', 'Home') ?></a></li>
                        <li><a href="/news.php"><?= $t('समाचार', 'News') ?></a></li>
                        <li><a href="/ipo-tracker.php"><?= $t('NEPSE/IPO', 'NEPSE/IPO') ?></a></li>
                    </ul>
                </div>
                <div>
                    <h4><?= $t('स्रोतहरू', 'Resources') ?></h4>
                    <ul>
                        <li><a href="/rashifal.php"><?= $t('राशिफल', 'Horoscope') ?></a></li>
                        <li><a href="/nepali-patro.php"><?= $t('नेपाली पात्रो', 'Calendar') ?></a></li>
                        <li><a href="/weather.php"><?= $t('मौसम', 'Weather') ?></a></li>
                        <li><a href="/emergency.php"><?= $t('आपतकालीन', 'Emergency') ?></a></li>
                    </ul>
                </div>
                <div>
                    <h4><?= $t('कम्पनी', 'Company') ?></h4>
                    <ul>
                        <li><a href="/about.php"><?= $t('हाम्रो बारेमा', 'About') ?></a></li>
                        <li><a href="/contact.php"><?= $t('सम्पर्क', 'Contact') ?></a></li>
                        <li><a href="/privacy.php"><?= $t('गोपनीयता', 'Privacy') ?></a></li>
                    </ul>
                </div>
            </div>
            <div class="tp-footer-bottom">
                <span>&copy; <?= date('Y') ?> <?= $t('आकाशवाणी। सर्वाधिकार सुरक्षित।', 'Aakashvani. All rights reserved.') ?></span>
                <span><?= $t('हामी नेपालको सूचना खुला राख्छौं', 'We keep Nepal's information open') ?></span>
            </div>
        </div>
    </footer>

    <script>
    async function loadRashifal(){
        const params=new URLSearchParams(window.location.search);
        const rashi=params.get('rashi');
        if(!rashi)return;
        const loading=document.getElementById('rashifal-loading');
        const content=document.getElementById('rashifal-content');
        const error=document.getElementById('rashifal-error');
        const meta=document.getElementById('rashifal-meta');
        const details=document.getElementById('rashifal-details');
        try {
            const resp=await fetch('/api/rashifal.php?rashi='+rashi+'&lang=ne');
            const data=await resp.json();
            if(data.ok&&data.rashifal){
                const r=data.rashifal;
                if(r.lucky_numbers&&r.lucky_numbers.length){
                    meta.innerHTML='<div class="rashifal-badge"><div class="rashifal-badge-value">'+r.lucky_numbers[0]+'</div><div class="rashifal-badge-label"><?=$t("लाकी नम्बर","Lucky Number")?></div></div><div class="rashifal-badge"><div class="rashifal-badge-value">'+(r.lucky_colors?r.lucky_colors[0]:'-')+'</div><div class="rashifal-badge-label"><?=$t("लाकी रंग","Lucky Color")?></div></div><div class="rashifal-badge"><div class="rashifal-badge-value">'+(r.compatibility||'-')+'</div><div class="rashifal-badge-label"><?=$t("मिल्ने राशि","Compatibility")?></div></div>';
                }
                let html='';
                if(r.overview)html+='<div class="rashifal-section"><h3 class="rashifal-title"><?=$t("आजको अवलोकन","Overview")?></h3><p class="rashifal-text">'+r.overview+'</p></div>';
                if(r.love)html+='<div class="rashifal-section"><h3 class="rashifal-title"><?=$t("प्रेम","Love")?></h3><p class="rashifal-text">'+r.love+'</p></div>';
                if(r.career)html+='<div class="rashifal-section"><h3 class="rashifal-title"><?=$t("करियर","Career")?></h3><p class="rashifal-text">'+r.career+'</p></div>';
                if(r.health)html+='<div class="rashifal-section"><h3 class="rashifal-title"><?=$t("स्वास्थ्य","Health")?></h3><p class="rashifal-text">'+r.health+'</p></div>';
                if(r.finance)html+='<div class="rashifal-section"><h3 class="rashifal-title"><?=$t("वित्त","Finance")?></h3><p class="rashifal-text">'+r.finance+'</p></div>';
                details.innerHTML=html;
                loading.style.display='none';
                content.style.display='block';
            }else{throw new Error();}
        }catch(e){loading.style.display='none';error.style.display='block';}
    }
    document.addEventListener('DOMContentLoaded',loadRashifal);
    </script>
    <script>document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
        (function() {
            var s=document.createElement('script');s.src='https://unpkg.com/lucide@latest/dist/umd/lucide.min.js';document.head.appendChild(s);
        })();
);</script>

    <!-- Mobile Bottom Nav -->
</body>
</html>
