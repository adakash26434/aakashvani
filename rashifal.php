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
    <header class="site-header">
        <div class="header-main">
            <div class="container">
                <div class="flex items-center justify-between gap-4">
                    <a href="/" class="header-brand"><div class="brand-logo">आ</div><span class="brand-name"><?=$t('आकाशवाणी','Aakashvani')?></span></a>
                    <nav class="main-nav">
                        <div class="container">
                            <div class="nav-list">
                                <a href="/" class="nav-link">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                                    <?= $t('गृह', 'Home') ?>
                                </a>
                                <a href="/news.php" class="nav-link">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 0-2-2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/></svg>
                                    <?= $t('समाचार', 'News') ?>
                                </a>
                                <a href="/nepali-patro.php" class="nav-link">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg>
                                    <?= $t('पात्रो', 'Calendar') ?>
                                </a>
                                <a href="/rashifal.php" class="nav-link active">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3z"/></svg>
                                    <?= $t('राशिफल', 'Horoscope') ?>
                                </a>
                                <a href="/ipo-tracker.php" class="nav-link">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                                    <?= $t('NEPSE/IPO', 'NEPSE/IPO') ?>
                                </a>
                                <a href="/weather.php" class="nav-link">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/></svg>
                                    <?= $t('मौसम', 'Weather') ?>
                                </a>
                                <a href="/cricket.php" class="nav-link">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                                    <?= $t('क्रिकेट', 'Cricket') ?>
                                </a>
                                <a href="/tenders.php" class="nav-link">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
                                    <?= $t('टेन्डर', 'Tenders') ?>
                                </a>
                                <a href="/emergency.php" class="nav-link">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    <?= $t('आपतकालीन', 'Emergency') ?>
                                </a>
                            </div>
                        </div>
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
    <footer class="site-footer">
        <div class="container">
            <div class="footer-bottom" style="border:none;padding:0"><p class="footer-copyright">&copy; <?=date('Y')?> <?=$t('आकाशवाणी','Aakashvani')?></p></div>
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
    <script src="/assets/js/app.js"></script>
</body>
</html>
