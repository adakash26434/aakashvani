<?php
require_once __DIR__ . '/config.php';
$lang=siteLang();
$isNepali=($lang!=='en');
$t=fn($ne,$en)=>$isNepali?$ne:$en;
?>
<!DOCTYPE html>
<html lang="<?=$isNepali?'ne':'en'?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$t('सेवा सर्त','Terms of Service')?> | <?=$t('आकाशवाणी','Aakashvani')?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <header class="site-header">
        <div class="header-main">
            <div class="container">
                <div class="flex items-center justify-between">
                    <a href="/" class="brand" style="text-decoration:none">
                        <div class="brand-logo">आ</div>
                        <span class="brand-name"><?=$t('आकाशवाणी','Aakashvani')?></span>
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <section style="background:linear-gradient(135deg,var(--dark-900),var(--dark-800));padding:var(--space-12) 0;color:#fff">
        <div class="container">
            <h1 class="text-3xl font-bold"><?=$t('सेवा सर्त','Terms of Service')?></h1>
        </div>
    </section>
    
    <section style="padding:var(--space-12) 0">
        <div class="container" style="max-width:800px">
            <div class="card card-body">
                <h2 style="margin-bottom:var(--space-4)"><?=$t('सेवाको प्रयोग','Use of Service')?></h2>
                <p style="color:var(--dark-600);line-height:1.8">
                    <?=$isNepali?'आकाशवाणी सेवा प्रयोग गर्दा तपाईं यी सर्तहरूमा सहमत हुनुहुन्छ।':'By using Aakashbani service, you agree to these terms.'?>
                </p>
                <h2 style="margin-top:var(--space-8);margin-bottom:var(--space-4)"><?=$t('सामग्री','Content')?></h2>
                <p style="color:var(--dark-600);line-height:1.8">
                    <?=$isNepali?'हाम्रो सामग्री सूचनाको उद्देश्यको लागि हो। कुनै पनि निर्णय लिनु अघि अन्य स्रोतहरू पनि जाँच्नुहोस्।':'Our content is for informational purposes. Please verify from other sources before making any decisions.'?>
                </p>
            </div>
        </div>
    </section>
    
    <footer class="site-footer">
        <div class="container">
            <div class="footer-bottom" style="border:none;padding:0">
                <p class="footer-copyright">&copy; <?=date('Y')?> <?=$t('आकाशवाणी','Aakashvani')?></p>
            </div>
        </div>
    </footer>
    <script src="/assets/js/app.js"></script>
</body>
</html>
