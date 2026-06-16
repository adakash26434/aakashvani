<?php
/**
 * आकाशवाणी — Desktop Header (Professional News Portal)
 * Full-width layout with sidebar navigation
 */

if (!defined('SITE_ROOT')) {
    define('SITE_ROOT', dirname(__DIR__));
}

// Get current path for active state
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$isHome = in_array($currentPath, ['/', '/index.php', '/home.php']);

// Language helper
$lang = $_COOKIE['site_lang'] ?? 'ne';
$isNepali = ($lang !== 'en');

// Navigation Items
$mainNav = [
    ['href' => '/', 'ne' => 'गृहपृष्ठ', 'en' => 'Home', 'icon' => 'home'],
    ['href' => '/news.php', 'ne' => 'समाचार', 'en' => 'News', 'icon' => 'newspaper'],
    ['href' => '/info-hub.php', 'ne' => 'जानकारी', 'en' => 'Info Hub', 'icon' => 'layout-grid'],
    ['href' => '/ipo-tracker.php', 'ne' => 'NEPSE/IPO', 'en' => 'NEPSE/IPO', 'icon' => 'trending-up'],
    ['href' => '/nepali-patro.php', 'ne' => 'पात्रो', 'en' => 'Calendar', 'icon' => 'calendar'],
    ['href' => '/rashifal.php', 'ne' => 'राशिफल', 'en' => 'Rashifal', 'icon' => 'star'],
    ['href' => '/tools.php', 'ne' => 'टुलहरू', 'en' => 'Tools', 'icon' => 'wrench'],
    ['href' => '/gov-services.php', 'ne' => 'सरकारी', 'en' => 'Gov', 'icon' => 'landmark'],
];

// More Navigation
$moreNav = [
    ['href' => '/weather.php', 'ne' => 'मौसम', 'en' => 'Weather', 'icon' => 'cloud'],
    ['href' => '/gold-price.php', 'ne' => 'सुनको मूल्य', 'en' => 'Gold Price', 'icon' => 'gem'],
    ['href' => '/currency-converter.php', 'ne' => 'मुद्रा', 'en' => 'Currency', 'icon' => 'coins'],
    ['href' => '/nokari.php', 'ne' => 'नोकरी', 'en' => 'Jobs', 'icon' => 'briefcase'],
    ['href' => '/loksewa.php', 'ne' => 'लोकसेवा', 'en' => 'Loksewa', 'icon' => 'building'],
    ['href' => '/cricket.php', 'ne' => 'क्रिकेट', 'en' => 'Cricket', 'icon' => 'trophy'],
    ['href' => '/emergency.php', 'ne' => 'आपतकालीन', 'en' => 'Emergency', 'icon' => 'phone'],
    ['href' => '/morning-brief.php', 'ne' => 'बिहानी ब्रिफ', 'en' => 'Morning Brief', 'icon' => 'sunrise'],
    ['href' => '/market.php', 'ne' => 'बजार', 'en' => 'Market', 'icon' => 'bar-chart'],
    ['href' => '/ai-guides.php', 'ne' => 'AI गाइड', 'en' => 'AI Guides', 'icon' => 'bot'],
];

// Sidebar Widgets
$sidebarWidgets = [
    ['title' => 'Latest News', 'icon' => 'clock', 'href' => '/news.php'],
    ['title' => 'Trending', 'icon' => 'flame', 'href' => '/news.php?sort=trending'],
    ['title' => 'Popular', 'icon' => 'star', 'href' => '/news.php?sort=popular'],
];

// Function to check active nav
function isActiveNavDesktop(string $href, string $path): bool {
    if ($href === '/') {
        return in_array($path, ['/', '/index.php', '/home.php']);
    }
    return str_starts_with($path, rtrim($href, '/'));
}

$getLabel = fn($item) => $isNepali ? $item['ne'] : $item['en'];
?>
<!DOCTYPE html>
<html lang="<?= $isNepali ? 'ne' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'आकाशवाणी - सूचनाको खुला आकाश') ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDesc ?? 'नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म') ?>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Global Styles -->
    <link rel="stylesheet" href="<?= $baseUrl ?? '' ?>/assets/css/global.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= $baseUrl ?? '' ?>/assets/images/favicon.svg">
</head>
<body class="desktop-body">
    
    <!-- Skip Link -->
    <a href="#main-content" class="skip-link"><?= $isNepali ? 'मुख्य सामग्री' : 'Skip to content' ?></a>
    
    <!-- ═══════════════════════════════════════════════════════════════════════
         TOP BAR - Date, Weather, Quick Links
         ═══════════════════════════════════════════════════════════════════════ -->
    <div class="topbar-desktop">
        <div class="topbar-container">
            <div class="topbar-left">
                <span class="topbar-date">
                    <i data-lucide="calendar" class="icon-sm"></i>
                    <?= date('l, F j, Y') ?>
                </span>
                <span class="topbar-separator">|</span>
                <span class="topbar-date-ne">
                    <?= $isNepali ? 'आज : ' : 'Today: ' ?><?= $bsShort ?? date('j F Y') ?>
                </span>
            </div>
            <div class="topbar-right">
                <a href="?lang=<?= $isNepali ? 'en' : 'ne' ?>" class="topbar-link">
                    <i data-lucide="globe" class="icon-sm"></i>
                    <?= $isNepali ? 'English' : 'नेपाली' ?>
                </a>
                <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
                    <a href="/profile.php" class="topbar-link"><?= $isNepali ? 'प्रोफाइल' : 'Profile' ?></a>
                    <a href="/logout.php" class="topbar-link"><?= $isNepali ? 'लगआउट' : 'Logout' ?></a>
                <?php else: ?>
                    <a href="/login.php" class="topbar-link"><?= $isNepali ? 'लगइन' : 'Login' ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- ═══════════════════════════════════════════════════════════════════════
         MAIN HEADER - Logo, Search, Actions
         ═══════════════════════════════════════════════════════════════════════ -->
    <header class="main-header-desktop">
        <div class="header-container">
            
            <!-- Logo -->
            <a href="/" class="header-logo">
                <div class="logo-icon">
                    <svg width="44" height="44" viewBox="0 0 32 32" fill="none">
                        <rect width="32" height="32" rx="8" fill="url(#logoGrad)"/>
                        <path d="M8 22V10L16 6L24 10V22L16 26L8 22Z" stroke="white" stroke-width="2" stroke-linejoin="round"/>
                        <path d="M16 14V18M16 21V23" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        <circle cx="16" cy="11" r="2" fill="white"/>
                        <defs>
                            <linearGradient id="logoGrad" x1="0" y1="0" x2="32" y2="32" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#10B981"/>
                                <stop offset="1" stop-color="#059669"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <div class="logo-text">
                    <span class="logo-title"><?= $isNepali ? 'आकाशवाणी' : 'Aakashbani' ?></span>
                    <span class="logo-tagline"><?= $isNepali ? 'सूचनाको खुला आकाश' : 'Open Sky of Information' ?></span>
                </div>
            </a>
            
            <!-- Search Bar -->
            <div class="header-search-desktop">
                <form action="/search.php" method="GET" class="search-form-desktop">
                    <div class="search-input-wrapper-desktop">
                        <i data-lucide="search" class="search-icon-desktop"></i>
                        <input type="search" name="q" placeholder="<?= $isNepali ? 'खोज्नुहोस्...' : 'Search news, info...' ?>" class="search-input-desktop">
                        <button type="submit" class="search-btn-desktop">
                            <i data-lucide="arrow-right" class="icon-md"></i>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Header Actions -->
            <div class="header-actions-desktop">
                <button type="button" class="action-btn-desktop" title="<?= $isNepali ? 'सूचना' : 'Notifications' ?>">
                    <i data-lucide="bell" class="icon-lg"></i>
                </button>
                <button type="button" class="action-btn-desktop" title="<?= $isNepali ? 'बुकमार्क' : 'Bookmarks' ?>">
                    <i data-lucide="bookmark" class="icon-lg"></i>
                </button>
                <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
                    <a href="/profile.php" class="user-avatar-desktop">
                        <?= $userInitial ?? 'U' ?>
                    </a>
                <?php else: ?>
                    <a href="/login.php" class="login-btn-desktop">
                        <i data-lucide="user" class="icon-md"></i>
                        <span><?= $isNepali ? 'लगइन' : 'Login' ?></span>
                    </a>
                <?php endif; ?>
            </div>
            
        </div>
    </header>
    
    <!-- ═══════════════════════════════════════════════════════════════════════
         NAVIGATION BAR - Main Navigation
         ═══════════════════════════════════════════════════════════════════════ -->
    <nav class="nav-bar-desktop" aria-label="Main navigation">
        <div class="nav-container">
            <ul class="nav-list-desktop" role="list">
                <?php foreach ($mainNav as $item): ?>
                    <?php $active = isActiveNavDesktop($item['href'], $currentPath); ?>
                    <li class="nav-item-desktop">
                        <a href="<?= $item['href'] ?>" class="nav-link-desktop <?= $active ? 'active' : '' ?>">
                            <i data-lucide="<?= $item['icon'] ?>" class="nav-icon-desktop"></i>
                            <span><?= $getLabel($item) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
                
                <!-- More Dropdown -->
                <li class="nav-item-desktop has-dropdown-desktop">
                    <button type="button" class="nav-link-desktop dropdown-trigger-desktop" aria-expanded="false">
                        <span><?= $isNepali ? 'थप' : 'More' ?></span>
                        <i data-lucide="chevron-down" class="dropdown-icon-desktop"></i>
                    </button>
                    <div class="nav-dropdown-desktop">
                        <div class="dropdown-content-desktop">
                            <?php foreach ($moreNav as $item): ?>
                                <a href="<?= $item['href'] ?>" class="dropdown-item-desktop">
                                    <i data-lucide="<?= $item['icon'] ?>" class="dropdown-icon-item-desktop"></i>
                                    <span><?= $getLabel($item) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </li>
            </ul>
            
            <!-- Live Ticker -->
            <div class="live-ticker">
                <span class="ticker-badge-desktop">LIVE</span>
                <div class="ticker-content-desktop">
                    <span class="ticker-text-desktop">
                        <?= $isNepali ? 'आकाशवाणीमा स्वागत छ - नेपालको सबैभन्दा छिटो सूचना प्लेटफर्म' : 'Welcome to Aakashbani - Nepal\'s fastest information platform' ?>
                    </span>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- ═══════════════════════════════════════════════════════════════════════
         MAIN CONTENT AREA - Two Column Layout
         ═══════════════════════════════════════════════════════════════════════ -->
    <div class="content-wrapper-desktop" id="main-content">
        
        <!-- Sidebar -->
        <aside class="sidebar-desktop">
            
            <!-- Quick Links -->
            <div class="sidebar-widget">
                <h3 class="sidebar-widget-title">
                    <i data-lucide="compass" class="icon-md"></i>
                    <?= $isNepali ? 'छिटो लिंक' : 'Quick Links' ?>
                </h3>
                <ul class="sidebar-nav">
                    <?php foreach ($sidebarWidgets as $widget): ?>
                        <li>
                            <a href="<?= $widget['href'] ?>" class="sidebar-nav-link">
                                <i data-lucide="<?= $widget['icon'] ?>" class="sidebar-nav-icon"></i>
                                <span><?= $widget['title'] ?></span>
                                <i data-lucide="chevron-right" class="sidebar-nav-arrow"></i>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Categories -->
            <div class="sidebar-widget">
                <h3 class="sidebar-widget-title">
                    <i data-lucide="grid-3x3" class="icon-md"></i>
                    <?= $isNepali ? 'श्रेणीहरू' : 'Categories' ?>
                </h3>
                <div class="sidebar-categories">
                    <a href="/news.php?category=politics" class="category-chip"><?= $isNepali ? 'राजनीति' : 'Politics' ?></a>
                    <a href="/news.php?category=business" class="category-chip"><?= $isNepali ? 'अर्थ' : 'Business' ?></a>
                    <a href="/news.php?category=sports" class="category-chip"><?= $isNepali ? 'खेलकुद' : 'Sports' ?></a>
                    <a href="/news.php?category=technology" class="category-chip"><?= $isNepali ? 'प्रविधि' : 'Tech' ?></a>
                    <a href="/news.php?category=entertainment" class="category-chip"><?= $isNepali ? 'मनोरंजन' : 'Entertainment' ?></a>
                    <a href="/news.php?category=international" class="category-chip"><?= $isNepali ? 'अन्तर्राष्ट्रिय' : 'International' ?></a>
                </div>
            </div>
            
            <!-- Market Summary -->
            <div class="sidebar-widget sidebar-market">
                <h3 class="sidebar-widget-title">
                    <i data-lucide="trending-up" class="icon-md"></i>
                    <?= $isNepali ? 'बजार सारांश' : 'Market Summary' ?>
                </h3>
                <div class="market-items">
                    <div class="market-item">
                        <span class="market-label">NEPSE</span>
                        <span class="market-value" id="mkt-nepse">--</span>
                    </div>
                    <div class="market-item">
                        <span class="market-label">Gold (10g)</span>
                        <span class="market-value" id="mkt-gold">--</span>
                    </div>
                    <div class="market-item">
                        <span class="market-label">USD</span>
                        <span class="market-value" id="mkt-usd">--</span>
                    </div>
                    <div class="market-item">
                        <span class="market-label">Petrol</span>
                        <span class="market-value" id="mkt-petrol">--</span>
                    </div>
                </div>
            </div>
            
        </aside>
        
        <!-- Main Content -->
        <main class="main-content-desktop">

<script>
// Initialize Lucide Icons
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}

// Dropdown Toggle
document.querySelectorAll('.dropdown-trigger-desktop').forEach(trigger => {
    trigger.addEventListener('click', function() {
        const isOpen = this.getAttribute('aria-expanded') === 'true';
        this.setAttribute('aria-expanded', !isOpen);
        this.closest('.has-dropdown-desktop').classList.toggle('open');
    });
});

// Close dropdown on click outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.has-dropdown-desktop')) {
        document.querySelectorAll('.dropdown-trigger-desktop').forEach(t => {
            t.setAttribute('aria-expanded', 'false');
        });
        document.querySelectorAll('.has-dropdown-desktop').forEach(d => {
            d.classList.remove('open');
        });
    }
});

// Keyboard support
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.dropdown-trigger-desktop').forEach(t => {
            t.setAttribute('aria-expanded', 'false');
        });
        document.querySelectorAll('.has-dropdown-desktop').forEach(d => {
            d.classList.remove('open');
        });
    }
});
</script>

<style>
/* ═══════════════════════════════════════════════════════════════════════════════
   DESKTOP LAYOUT STYLES - Professional News Portal
   ═══════════════════════════════════════════════════════════════════════════════ */

/* Base */
.desktop-body {
    font-family: 'Inter', 'Hind Siliguri', 'Noto Sans Devanagari', system-ui, sans-serif;
    background: #f8fafc;
    color: #0f172a;
    line-height: 1.6;
}

/* Container */
.topbar-container,
.header-container,
.nav-container,
.content-wrapper-desktop {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 24px;
}

/* ═══════════════════════════════════════════════════════════════════════════
   TOP BAR
   ═══════════════════════════════════════════════════════════════════════════ */
.topbar-desktop {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    color: #e2e8f0;
    padding: 8px 0;
    font-size: 13px;
}

.topbar-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.topbar-left,
.topbar-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.topbar-date,
.topbar-date-ne {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #94a3b8;
}

.topbar-separator {
    color: #475569;
}

.topbar-link {
    display: flex;
    align-items: center;
    gap: 4px;
    color: #94a3b8;
    text-decoration: none;
    transition: color 0.2s;
    font-weight: 500;
}

.topbar-link:hover {
    color: #ffffff;
}

/* ═══════════════════════════════════════════════════════════════════════════
   MAIN HEADER
   ═══════════════════════════════════════════════════════════════════════════ */
.main-header-desktop {
    background: #ffffff;
    padding: 16px 0;
    border-bottom: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.header-container {
    display: flex;
    align-items: center;
    gap: 32px;
}

/* Logo */
.header-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    flex-shrink: 0;
}

.logo-icon {
    width: 44px;
    height: 44px;
}

.logo-text {
    display: flex;
    flex-direction: column;
}

.logo-title {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.02em;
    line-height: 1.2;
}

.logo-tagline {
    font-size: 11px;
    color: #64748b;
    margin-top: 2px;
}

/* Search */
.header-search-desktop {
    flex: 1;
    max-width: 560px;
}

.search-form-desktop {
    width: 100%;
}

.search-input-wrapper-desktop {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon-desktop {
    position: absolute;
    left: 16px;
    color: #94a3b8;
    pointer-events: none;
}

.search-input-desktop {
    width: 100%;
    padding: 12px 48px 12px 44px;
    background: #f1f5f9;
    border: 2px solid transparent;
    border-radius: 12px;
    font-size: 14px;
    transition: all 0.2s;
}

.search-input-desktop:focus {
    outline: none;
    background: #ffffff;
    border-color: #10b981;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
}

.search-btn-desktop {
    position: absolute;
    right: 8px;
    width: 36px;
    height: 36px;
    background: #10b981;
    color: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}

.search-btn-desktop:hover {
    background: #059669;
}

/* Header Actions */
.header-actions-desktop {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.action-btn-desktop {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    border-radius: 10px;
    transition: all 0.2s;
}

.action-btn-desktop:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.user-avatar-desktop {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    text-decoration: none;
    transition: transform 0.2s;
}

.user-avatar-desktop:hover {
    transform: scale(1.05);
}

.login-btn-desktop {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.2s;
}

.login-btn-desktop:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

/* ═══════════════════════════════════════════════════════════════════════════
   NAVIGATION BAR
   ═══════════════════════════════════════════════════════════════════════════ */
.nav-bar-desktop {
    background: #ffffff;
    border-bottom: 2px solid #10b981;
    position: sticky;
    top: 0;
    z-index: 100;
}

.nav-container {
    display: flex;
    align-items: center;
    gap: 24px;
}

.nav-list-desktop {
    display: flex;
    align-items: center;
    gap: 4px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav-item-desktop {
    position: relative;
}

.nav-link-desktop {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 14px 16px;
    color: #475569;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.2s;
}

.nav-link-desktop:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.nav-link-desktop.active {
    background: #10b981;
    color: white;
}

.nav-icon-desktop {
    width: 18px;
    height: 18px;
}

/* Dropdown */
.has-dropdown-desktop {
    position: relative;
}

.dropdown-icon-desktop {
    width: 14px;
    height: 14px;
    transition: transform 0.2s;
}

.has-dropdown-desktop.open .dropdown-icon-desktop {
    transform: rotate(180deg);
}

.nav-dropdown-desktop {
    position: absolute;
    top: 100%;
    left: 0;
    min-width: 220px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    border: 1px solid #e2e8f0;
    opacity: 0;
    visibility: hidden;
    transform: translateY(8px);
    transition: all 0.2s;
    z-index: 100;
}

.has-dropdown-desktop.open .nav-dropdown-desktop {
    opacity: 1;
    visibility: visible;
    transform: translateY(4px);
}

.dropdown-content-desktop {
    padding: 8px;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 4px;
}

.dropdown-item-desktop {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    color: #475569;
    font-size: 13px;
    font-weight: 500;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.15s;
}

.dropdown-item-desktop:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.dropdown-icon-item-desktop {
    width: 18px;
    height: 18px;
    color: #10b981;
}

/* Live Ticker */
.live-ticker {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-left: auto;
    padding: 8px 0;
}

.ticker-badge-desktop {
    padding: 4px 10px;
    background: #ef4444;
    color: white;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-radius: 4px;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.ticker-text-desktop {
    font-size: 13px;
    color: #64748b;
    max-width: 300px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ═══════════════════════════════════════════════════════════════════════════
   CONTENT WRAPPER
   ═══════════════════════════════════════════════════════════════════════════ */
.content-wrapper-desktop {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 32px;
    padding: 32px 24px;
    max-width: 1400px;
    margin: 0 auto;
    min-height: calc(100vh - 200px);
}

/* ═══════════════════════════════════════════════════════════════════════════
   SIDEBAR
   ═══════════════════════════════════════════════════════════════════════════ */
.sidebar-desktop {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.sidebar-widget {
    background: #ffffff;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.sidebar-widget-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid #10b981;
}

.sidebar-nav {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    color: #475569;
    font-size: 13px;
    font-weight: 500;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.15s;
}

.sidebar-nav-link:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.sidebar-nav-icon {
    width: 18px;
    height: 18px;
    color: #10b981;
}

.sidebar-nav-arrow {
    width: 14px;
    height: 14px;
    margin-left: auto;
    color: #cbd5e1;
}

.sidebar-categories {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.category-chip {
    padding: 8px 14px;
    background: #f1f5f9;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    text-decoration: none;
    transition: all 0.15s;
}

.category-chip:hover {
    background: #10b981;
    color: white;
}

/* Market Widget */
.sidebar-market {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    color: white;
}

.sidebar-market .sidebar-widget-title {
    color: white;
    border-bottom-color: #10b981;
}

.market-items {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.market-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.market-item:last-child {
    border-bottom: none;
}

.market-label {
    font-size: 12px;
    color: #94a3b8;
}

.market-value {
    font-size: 14px;
    font-weight: 700;
    color: white;
}

/* ═══════════════════════════════════════════════════════════════════════════
   MAIN CONTENT
   ═══════════════════════════════════════════════════════════════════════════ */
.main-content-desktop {
    background: #ffffff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    min-height: 600px;
}

/* ═══════════════════════════════════════════════════════════════════════════
   RESPONSIVE
   ═══════════════════════════════════════════════════════════════════════════ */
@media (max-width: 1200px) {
    .content-wrapper-desktop {
        grid-template-columns: 240px 1fr;
        gap: 24px;
    }
    
    .live-ticker {
        display: none;
    }
}

@media (max-width: 1024px) {
    .content-wrapper-desktop {
        grid-template-columns: 1fr;
    }
    
    .sidebar-desktop {
        display: none;
    }
    
    .logo-tagline {
        display: none;
    }
    
    .header-search-desktop {
        max-width: 400px;
    }
}

@media (max-width: 768px) {
    .topbar-container,
    .header-container,
    .nav-container,
    .content-wrapper-desktop {
        padding: 0 16px;
    }
    
    .header-container {
        flex-wrap: wrap;
        gap: 16px;
    }
    
    .header-search-desktop {
        order: 3;
        max-width: 100%;
        width: 100%;
    }
    
    .nav-list-desktop {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .nav-link-desktop span {
        display: none;
    }
    
    .nav-link-desktop {
        padding: 12px;
    }
    
    .logo-title {
        font-size: 18px;
    }
}

/* Skip Link */
.skip-link {
    position: absolute;
    top: -40px;
    left: 0;
    background: #10b981;
    color: white;
    padding: 8px 16px;
    text-decoration: none;
    z-index: 10000;
    border-radius: 0 0 8px 0;
    font-weight: 600;
}

.skip-link:focus {
    top: 0;
}
</style>
