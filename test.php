<?php
/**
 * आकाशवाणी - Design System Test Page
 * Test all CSS components
 */
require_once __DIR__ . '/config.php';
$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;
?>
<!DOCTYPE html>
<html lang="<?=$isNepali?'ne':'en'?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Design System Test | आकाशवाणी</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="stylesheet" href="/assets/css/premium.css">
    <style>
        .test-container { max-width: 1000px; margin: 40px auto; padding: 20px; }
        .test-section { background: #fff; border: 1px solid var(--dark-200); border-radius: var(--radius-xl); padding: 24px; margin-bottom: 24px; }
        .test-section h2 { margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid var(--primary); display: flex; align-items: center; gap: 8px; }
        .test-row { display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 16px; }
        .test-item { padding: 16px; background: var(--dark-50); border-radius: var(--radius-lg); min-width: 150px; text-align: center; }
        .colors-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 12px; }
        .color-box { padding: 20px; border-radius: var(--radius-lg); text-align: center; color: #fff; font-weight: 600; }
        .dark-text { color: var(--dark-900); }
    </style>
</head>
<body style="background: var(--dark-50);">

    <!-- TOP BAR -->
    <div class="tp-topbar">
        <div class="tp-container">
            <div class="tp-topbar-inner">
                <div class="tp-topbar-left">
                    <span class="tp-date"><?= date('l, j F Y') ?></span>
                    <span class="tp-topbar-links"><a href="?">नेपाली</a><a href="?lang=en">English</a></span>
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

    
    <div class="test-container">
        <h1 style="margin-bottom: 24px;">🎨 Design System Test</h1>
        
        <!-- Colors -->
        <div class="test-section">
            <h2>🎨 Colors</h2>
            <div class="colors-grid">
                <div class="color-box" style="background: var(--primary)">Primary<br><small>#10b981</small></div>
                <div class="color-box" style="background: var(--primary-600)">Primary 600<br><small>#059669</small></div>
                <div class="color-box" style="background: var(--accent)">Accent<br><small>#f59e0b</small></div>
                <div class="color-box" style="background: var(--dark-900)">Dark 900<br><small>#0f172a</small></div>
                <div class="color-box" style="background: var(--dark-500); color: #fff">Dark 500<br><small>#64748b</small></div>
                <div class="color-box" style="background: var(--dark-100); color: var(--dark-900)">Dark 100<br><small>#f1f5f9</small></div>
                <div class="color-box" style="background: var(--success)">Success<br><small>#22c55e</small></div>
                <div class="color-box" style="background: var(--error)">Error<br><small>#ef4444</small></div>
            </div>
        </div>
        
        <!-- Typography -->
        <div class="test-section">
            <h2>📝 Typography</h2>
            <div class="test-row">
                <div class="test-item"><h1>H1 Title</h1><small>3rem/48px</small></div>
                <div class="test-item"><h2>H2 Title</h2><small>2.25rem</small></div>
                <div class="test-item"><h3>H3 Title</h3><small>1.5rem</small></div>
            </div>
            <div class="test-row">
                <div class="test-item">
                    <p class="text-lg font-semibold">Large Text</p>
                    <small>1.125rem</small>
                </div>
                <div class="test-item">
                    <p class="text-primary">Primary Color</p>
                    <small>.text-primary</small>
                </div>
                <div class="test-item">
                    <p class="text-secondary">Secondary Color</p>
                    <small>.text-secondary</small>
                </div>
                <div class="test-item">
                    <p class="text-muted">Muted Text</p>
                    <small>.text-muted</small>
                </div>
            </div>
        </div>
        
        <!-- Buttons -->
        <div class="test-section">
            <h2>🔘 Buttons</h2>
            <div class="test-row">
                <button class="btn btn-primary">Primary Button</button>
                <button class="btn btn-secondary">Secondary</button>
                <button class="btn btn-outline">Outline</button>
                <button class="btn btn-ghost">Ghost</button>
                <button class="btn btn-sm btn-primary">Small</button>
                <button class="btn btn-lg btn-primary">Large</button>
            </div>
            <div class="test-row">
                <button class="btn btn-icon btn-primary">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                </button>
                <button class="btn btn-icon btn-secondary">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 0-2-2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/></svg>
                </button>
            </div>
        </div>
        
        <!-- Cards -->
        <div class="test-section">
            <h2>📦 Cards</h2>
            <div class="test-row">
                <div class="card" style="width: 250px;">
                    <div class="card-body">
                        <h3 class="card-title">Card Title</h3>
                        <p class="text-secondary">Card content goes here with some sample text.</p>
                    </div>
                </div>
                <div class="card" style="width: 250px;">
                    <div class="card-header">Card Header</div>
                    <div class="card-body">
                        <p>Card body content</p>
                    </div>
                    <div class="card-footer">Card Footer</div>
                </div>
            </div>
        </div>
        
        <!-- Badges -->
        <div class="test-section">
            <h2>🏷️ Badges</h2>
            <div class="test-row">
                <span class="badge badge-primary">Primary</span>
                <span class="badge badge-dark">Dark</span>
                <span class="badge badge-success">Success</span>
                <span class="badge badge-warning">Warning</span>
                <span class="badge badge-error">Error</span>
                <span class="badge badge-info">Info</span>
                <span class="live-badge"><span class="live-dot"></span>LIVE</span>
            </div>
        </div>
        
        <!-- Forms -->
        <div class="test-section">
            <h2>📝 Forms</h2>
            <div class="test-row" style="flex-direction: column;">
                <div class="form-group" style="max-width: 300px;">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="input" placeholder="info@example.com">
                </div>
                <div class="form-group" style="max-width: 300px;">
                    <label class="form-label">Large Input</label>
                    <input type="text" class="input input-lg" placeholder="Large input...">
                </div>
            </div>
        </div>
        
        <!-- Table -->
        <div class="test-section">
            <h2>📊 Table</h2>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Sample News 1</td>
                            <td>Politics</td>
                            <td><span class="badge badge-success">Active</span></td>
                        </tr>
                        <tr>
                            <td>Sample News 2</td>
                            <td>Economy</td>
                            <td><span class="badge badge-warning">Pending</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Alerts -->
        <div class="test-section">
            <h2>⚠️ Alerts</h2>
            <div class="test-row" style="flex-direction: column;">
                <div class="alert alert-success">✓ Success Alert - Your action was completed.</div>
                <div class="alert alert-warning">⚠ Warning Alert - Please check this.</div>
                <div class="alert alert-error">✗ Error Alert - Something went wrong.</div>
                <div class="alert alert-info">ℹ Info Alert - Here's some information.</div>
            </div>
        </div>
        
        <!-- Page Header -->
        <div class="test-section">
            <h2>📄 Page Header</h2>
            <section class="page-header">
                <div class="container">
                    <h1 class="page-title">Sample Page Title</h1>
                    <p class="page-subtitle">This is a sample page subtitle</p>
                </div>
            </section>
        </div>
        
        <!-- Quick Links -->
        <div class="test-section">
            <h2>🔗 All Pages Test</h2>
            <div class="test-row">
                <a href="/" class="btn btn-primary">🏠 Homepage</a>
                <a href="/news.php" class="btn btn-outline">📰 News</a>
                <a href="/about.php" class="btn btn-outline">ℹ️ About</a>
                <a href="/contact.php" class="btn btn-outline">📧 Contact</a>
                <a href="/rashifal.php" class="btn btn-outline">⭐ Rashifal</a>
                <a href="/nepali-patro.php" class="btn btn-outline">📅 Calendar</a>
                <a href="/ipo-tracker.php" class="btn btn-outline">📈 IPO</a>
                <a href="/emergency.php" class="btn btn-outline">🚨 Emergency</a>
                <a href="/gov-services.php" class="btn btn-outline">🏛️ Gov Services</a>
                <a href="/tools.php" class="btn btn-outline">🔧 Tools</a>
            </div>
        </div>
    </div>

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
                    <p class="tp-footer-desc"><?= $t('नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म।', 'Nepal\'s most trusted information platform.') ?></p>
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
                <span><?= $t('हामी नेपालको सूचना खुला राख्छौं', 'We keep Nepal\'s information open') ?></span>
            </div>
        </div>
    </footer>

    
</body>
</html>