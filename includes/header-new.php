<?php
/**
 * आकाशवाणी - Modern Header Component (v2)
 * Professional News Portal Header
 * Clean, Fast, Responsive Design
 */

// Prevent direct access
if (!defined('SITE_ROOT')) {
    define('SITE_ROOT', dirname(__DIR__));
}

// Get current path for active state
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$isHome = in_array($currentPath, ['/', '/index.php', '/home.php']);

// Language helper
$lang = $_COOKIE['site_lang'] ?? 'ne';
$isNepali = ($lang !== 'en');

/**
 * Check if a nav item is active
 */
function isActiveNav(string $href, string $path): bool {
    if ($href === '/') {
        return in_array($path, ['/', '/index.php', '/home.php']);
    }
    return str_starts_with($path, rtrim($href, '/'));
}

// Navigation Items
$navItems = [
    ['href' => '/', 'ne' => 'गृहपृष्ठ', 'en' => 'Home', 'icon' => 'home'],
    ['href' => '/news.php', 'ne' => 'समाचार', 'en' => 'News', 'icon' => 'newspaper'],
    ['href' => '/info-hub.php', 'ne' => 'जानकारी', 'en' => 'Info Hub', 'icon' => 'layout-grid'],
    ['href' => '/ipo-tracker.php', 'ne' => 'NEPSE/IPO', 'en' => 'NEPSE/IPO', 'icon' => 'trending-up'],
    ['href' => '/nepali-patro.php', 'ne' => 'पात्रो', 'en' => 'Calendar', 'icon' => 'calendar'],
    ['href' => '/rashifal.php', 'ne' => 'राशिफल', 'en' => 'Rashifal', 'icon' => 'star'],
    ['href' => '/tools.php', 'ne' => 'टुलहरू', 'en' => 'Tools', 'icon' => 'wrench'],
];

// More Navigation
$moreItems = [
    ['href' => '/weather.php', 'ne' => 'मौसम', 'en' => 'Weather', 'icon' => 'cloud'],
    ['href' => '/gov-services.php', 'ne' => 'सरकारी सेवा', 'en' => 'Gov Services', 'icon' => 'landmark'],
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

// Function to get label based on language
$getLabel = function($item) use ($isNepali) {
    return $isNepali ? $item['ne'] : $item['en'];
};
?>
<!DOCTYPE html>
<html lang="<?= $isNepali ? 'ne' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= htmlspecialchars($pageTitle ?? 'आकाशवाणी - सूचनाको खुला आकाश') ?></title>
    
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Global Styles -->
    <link rel="stylesheet" href="<?= $baseUrl ?? '' ?>/assets/css/global.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= $baseUrl ?? '' ?>/assets/images/favicon.svg">
</head>
<body class="app-body">
    <!-- Skip Link for Accessibility -->
    <a href="#main-content" class="skip-link"><?= $isNepali ? 'मुख्य सामग्रीमा जानुहोस्' : 'Skip to main content' ?></a>
    
    <!-- ═══════════════════════════════════════════════════════════════════════════════
         HEADER - Professional News Portal Header
         ═══════════════════════════════════════════════════════════════════════════════ -->
    <header class="app-header" id="app-header">
        
        <!-- Top Bar (Breaking News / Quick Info) -->
        <div class="topbar">
            <div class="container">
                <div class="topbar-inner">
                    <!-- Date -->
                    <div class="topbar-date">
                        <i data-lucide="calendar" class="icon-sm"></i>
                        <span><?= date('l, F j, Y') ?></span>
                    </div>
                    
                    <!-- Breaking News Ticker -->
                    <div class="breaking-ticker">
                        <span class="ticker-badge">LIVE</span>
                        <div class="ticker-content">
                            <span class="ticker-text" id="breaking-text">
                                <?= $isNepali ? 'आकाशवाणीमा स्वागत छ - सबैभन्दा छिटो र भरपर्दो सूचना' : 'Welcome to Aakashbani - Fastest & Most Reliable Information' ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Language Toggle -->
                    <div class="topbar-actions">
                        <a href="?lang=<?= $isNepali ? 'en' : 'ne' ?>" class="lang-toggle">
                            <i data-lucide="globe" class="icon-sm"></i>
                            <span><?= $isNepali ? 'EN' : 'नेपाली' ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Header -->
        <div class="main-header">
            <div class="container">
                <div class="header-inner">
                    
                    <!-- Logo -->
                    <a href="/" class="logo" aria-label="आकाशवाणी Home">
                        <div class="logo-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
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
                    
                    <!-- Search Bar (Desktop) -->
                    <div class="header-search" id="header-search">
                        <form action="/search.php" method="GET" class="search-form">
                            <div class="search-input-wrapper">
                                <i data-lucide="search" class="search-icon"></i>
                                <input type="search" name="q" placeholder="<?= $isNepali ? 'खोज्नुहोस्...' : 'Search...' ?>" class="search-input" aria-label="Search">
                                <button type="submit" class="search-btn">
                                    <i data-lucide="arrow-right" class="icon-md"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Header Actions -->
                    <div class="header-actions">
                        <!-- Search Toggle (Mobile) -->
                        <button type="button" class="header-action-btn mobile-search-toggle" id="mobile-search-toggle" aria-label="Toggle search">
                            <i data-lucide="search" class="icon-lg"></i>
                        </button>
                        
                        <!-- Notifications -->
                        <button type="button" class="header-action-btn" aria-label="Notifications">
                            <i data-lucide="bell" class="icon-lg"></i>
                            <span class="notification-badge" id="notif-count"></span>
                        </button>
                        
                        <!-- Mobile Menu Toggle -->
                        <button type="button" class="header-action-btn mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Toggle menu">
                            <i data-lucide="menu" class="icon-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Navigation Bar -->
        <nav class="nav-bar" id="nav-bar" aria-label="Main navigation">
            <div class="container">
                <ul class="nav-list" role="list">
                    <?php foreach ($navItems as $item): ?>
                        <?php $active = isActiveNav($item['href'], $currentPath); ?>
                        <li class="nav-item">
                            <a href="<?= $item['href'] ?>" class="nav-link <?= $active ? 'active' : '' ?>">
                                <i data-lucide="<?= $item['icon'] ?>" class="nav-icon"></i>
                                <span><?= $getLabel($item) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    
                    <!-- More Dropdown -->
                    <li class="nav-item has-dropdown">
                        <button type="button" class="nav-link dropdown-trigger" aria-expanded="false" aria-haspopup="true">
                            <i data-lucide="more-horizontal" class="nav-icon"></i>
                            <span><?= $isNepali ? 'थप' : 'More' ?></span>
                            <i data-lucide="chevron-down" class="dropdown-icon"></i>
                        </button>
                        <div class="nav-dropdown">
                            <div class="dropdown-grid">
                                <?php foreach ($moreItems as $item): ?>
                                    <a href="<?= $item['href'] ?>" class="dropdown-item">
                                        <i data-lucide="<?= $item['icon'] ?>" class="dropdown-icon-item"></i>
                                        <span><?= $getLabel($item) ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
        
        <!-- Mobile Search Panel -->
        <div class="mobile-search-panel" id="mobile-search-panel">
            <div class="container">
                <form action="/search.php" method="GET" class="mobile-search-form">
                    <div class="mobile-search-input-wrapper">
                        <input type="search" name="q" placeholder="<?= $isNepali ? 'खोज्नुहोस्...' : 'Search...' ?>" class="mobile-search-input" aria-label="Search">
                        <button type="submit" class="mobile-search-btn">
                            <i data-lucide="search" class="icon-lg"></i>
                        </button>
                        <button type="button" class="mobile-search-close" id="mobile-search-close">
                            <i data-lucide="x" class="icon-lg"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
    </header>
    
    <!-- Mobile Navigation Drawer -->
    <div class="mobile-nav-overlay" id="mobile-nav-overlay"></div>
    <nav class="mobile-nav-drawer" id="mobile-nav-drawer" aria-label="Mobile navigation">
        <div class="mobile-nav-header">
            <div class="mobile-nav-user">
                <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
                    <div class="user-avatar"><?= $userInitial ?? 'U' ?></div>
                    <div class="user-info">
                        <span class="user-name"><?= htmlspecialchars($userName ?? 'User') ?></span>
                        <a href="/profile.php" class="user-link"><?= $isNepali ? 'प्रोफाइल' : 'Profile' ?></a>
                    </div>
                <?php else: ?>
                    <div class="user-avatar guest">?</div>
                    <div class="user-info">
                        <span class="user-name"><?= $isNepali ? 'अतिथि' : 'Guest' ?></span>
                        <a href="/login.php" class="user-link"><?= $isNepali ? 'लगइन गर्नुहोस्' : 'Login' ?></a>
                    </div>
                <?php endif; ?>
            </div>
            <button type="button" class="mobile-nav-close" id="mobile-nav-close" aria-label="Close menu">
                <i data-lucide="x" class="icon-lg"></i>
            </button>
        </div>
        
        <div class="mobile-nav-content">
            <div class="mobile-nav-section">
                <h3 class="mobile-nav-section-title"><?= $isNepali ? 'मुख्य मेनु' : 'Main Menu' ?></h3>
                <ul class="mobile-nav-list" role="list">
                    <?php foreach ($navItems as $item): ?>
                        <?php $active = isActiveNav($item['href'], $currentPath); ?>
                        <li>
                            <a href="<?= $item['href'] ?>" class="mobile-nav-link <?= $active ? 'active' : '' ?>">
                                <i data-lucide="<?= $item['icon'] ?>" class="mobile-nav-icon"></i>
                                <span><?= $getLabel($item) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="mobile-nav-section">
                <h3 class="mobile-nav-section-title"><?= $isNepali ? 'थप सेवाहरू' : 'More Services' ?></h3>
                <ul class="mobile-nav-list" role="list">
                    <?php foreach ($moreItems as $item): ?>
                        <li>
                            <a href="<?= $item['href'] ?>" class="mobile-nav-link">
                                <i data-lucide="<?= $item['icon'] ?>" class="mobile-nav-icon"></i>
                                <span><?= $getLabel($item) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="mobile-nav-section">
                <div class="mobile-nav-auth">
                    <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
                        <a href="/logout.php" class="btn btn-outline w-full">
                            <i data-lucide="log-out" class="icon-md"></i>
                            <span><?= $isNepali ? 'लगआउट' : 'Logout' ?></span>
                        </a>
                    <?php else: ?>
                        <a href="/login.php" class="btn btn-primary w-full">
                            <i data-lucide="log-in" class="icon-md"></i>
                            <span><?= $isNepali ? 'लगइन' : 'Login' ?></span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Area -->
    <main id="main-content" class="main-content">
        <div class="container">

<script>
    // Initialize Lucide Icons
    lucide.createIcons();
    
    // Mobile Menu Toggle
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mobileNavDrawer = document.getElementById('mobile-nav-drawer');
    const mobileNavOverlay = document.getElementById('mobile-nav-overlay');
    const mobileNavClose = document.getElementById('mobile-nav-close');
    
    function openMobileMenu() {
        mobileNavDrawer.classList.add('open');
        mobileNavOverlay.classList.add('visible');
        document.body.style.overflow = 'hidden';
    }
    
    function closeMobileMenu() {
        mobileNavDrawer.classList.remove('open');
        mobileNavOverlay.classList.remove('visible');
        document.body.style.overflow = '';
    }
    
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', openMobileMenu);
    }
    if (mobileNavClose) {
        mobileNavClose.addEventListener('click', closeMobileMenu);
    }
    if (mobileNavOverlay) {
        mobileNavOverlay.addEventListener('click', closeMobileMenu);
    }
    
    // Mobile Search Toggle
    const mobileSearchToggle = document.getElementById('mobile-search-toggle');
    const mobileSearchPanel = document.getElementById('mobile-search-panel');
    const mobileSearchClose = document.getElementById('mobile-search-close');
    
    if (mobileSearchToggle && mobileSearchPanel) {
        mobileSearchToggle.addEventListener('click', () => {
            mobileSearchPanel.classList.toggle('open');
            if (mobileSearchPanel.classList.contains('open')) {
                mobileSearchPanel.querySelector('input').focus();
            }
        });
    }
    if (mobileSearchClose && mobileSearchPanel) {
        mobileSearchClose.addEventListener('click', () => {
            mobileSearchPanel.classList.remove('open');
        });
    }
    
    // Dropdown Toggle
    const dropdownTrigger = document.querySelector('.dropdown-trigger');
    if (dropdownTrigger) {
        dropdownTrigger.addEventListener('click', () => {
            const isOpen = dropdownTrigger.getAttribute('aria-expanded') === 'true';
            dropdownTrigger.setAttribute('aria-expanded', !isOpen);
            dropdownTrigger.closest('.has-dropdown').classList.toggle('open');
        });
        
        // Close on click outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.has-dropdown')) {
                dropdownTrigger.setAttribute('aria-expanded', 'false');
                dropdownTrigger.closest('.has-dropdown').classList.remove('open');
            }
        });
    }
    
    // Sticky Header on Scroll
    let lastScroll = 0;
    const header = document.getElementById('app-header');
    
    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        lastScroll = currentScroll;
    });
    
    // Escape key closes panels
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMobileMenu();
            if (mobileSearchPanel) mobileSearchPanel.classList.remove('open');
        }
    });
</script>

<style>
/* ═══════════════════════════════════════════════════════════════════════════════
   HEADER STYLES - Modern News Portal Header
   ═══════════════════════════════════════════════════════════════════════════════ */

/* Base Header */
.app-header {
    position: sticky;
    top: 0;
    z-index: var(--z-sticky);
    background: var(--bg-primary);
    transition: all var(--transition-base);
}

.app-header.scrolled {
    box-shadow: var(--shadow-md);
}

/* Top Bar */
.topbar {
    background: linear-gradient(135deg, var(--slate-800), var(--slate-900));
    color: white;
    padding: var(--space-2) 0;
    font-size: var(--text-xs);
}

.topbar-inner {
    display: flex;
    align-items: center;
    gap: var(--space-4);
}

.topbar-date {
    display: none;
    align-items: center;
    gap: var(--space-1-5);
    color: var(--slate-300);
    white-space: nowrap;
}

@media (min-width: 768px) {
    .topbar-date { display: flex; }
}

.topbar-date i {
    opacity: 0.7;
}

/* Breaking News Ticker */
.breaking-ticker {
    flex: 1;
    display: flex;
    align-items: center;
    gap: var(--space-2);
    min-width: 0;
}

.ticker-badge {
    flex-shrink: 0;
    padding: var(--space-0-5) var(--space-2);
    background: var(--error-500);
    color: white;
    font-size: 10px;
    font-weight: var(--font-bold);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-radius: var(--radius-sm);
    animation: pulse-soft 2s ease-in-out infinite;
}

.ticker-content {
    overflow: hidden;
}

.ticker-text {
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: var(--slate-200);
}

/* Topbar Actions */
.topbar-actions {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.lang-toggle {
    display: flex;
    align-items: center;
    gap: var(--space-1);
    padding: var(--space-1) var(--space-2);
    color: var(--slate-300);
    border-radius: var(--radius-md);
    transition: all var(--transition-fast);
    font-weight: var(--font-medium);
}

.lang-toggle:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

/* Main Header */
.main-header {
    padding: var(--space-3) 0;
    border-bottom: 1px solid var(--border-primary);
}

.header-inner {
    display: flex;
    align-items: center;
    gap: var(--space-4);
}

/* Logo */
.logo {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    text-decoration: none;
    flex-shrink: 0;
}

.logo-icon {
    width: 40px;
    height: 40px;
}

.logo-text {
    display: none;
}

@media (min-width: 640px) {
    .logo-text { display: flex; flex-direction: column; }
}

.logo-title {
    font-size: var(--text-lg);
    font-weight: var(--font-bold);
    color: var(--text-primary);
    line-height: 1.2;
}

.logo-tagline {
    font-size: var(--text-xs);
    color: var(--text-tertiary);
    display: none;
}

@media (min-width: 768px) {
    .logo-tagline { display: block; }
}

/* Header Search */
.header-search {
    flex: 1;
    max-width: 480px;
    display: none;
}

@media (min-width: 768px) {
    .header-search { display: block; }
}

.search-form {
    width: 100%;
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: var(--space-3);
    color: var(--text-tertiary);
    pointer-events: none;
}

.search-input {
    width: 100%;
    padding: var(--space-2-5) var(--space-4) var(--space-2-5) var(--space-10);
    background: var(--slate-100);
    border: 1px solid transparent;
    border-radius: var(--radius-full);
    font-size: var(--text-sm);
    transition: all var(--transition-fast);
}

.search-input:focus {
    outline: none;
    background: var(--bg-card);
    border-color: var(--brand-500);
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.search-btn {
    position: absolute;
    right: var(--space-1);
    padding: var(--space-1-5);
    background: var(--brand-500);
    color: white;
    border-radius: var(--radius-full);
    transition: all var(--transition-fast);
}

.search-btn:hover {
    background: var(--brand-600);
}

/* Header Actions */
.header-actions {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    margin-left: auto;
}

.header-action-btn {
    position: relative;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-secondary);
    border-radius: var(--radius-lg);
    transition: all var(--transition-fast);
}

.header-action-btn:hover {
    background: var(--slate-100);
    color: var(--text-primary);
}

.notification-badge {
    position: absolute;
    top: 4px;
    right: 4px;
    min-width: 16px;
    height: 16px;
    padding: 0 4px;
    background: var(--error-500);
    color: white;
    font-size: 10px;
    font-weight: var(--font-bold);
    border-radius: var(--radius-full);
    display: none;
}

/* Mobile Search Toggle - Hide on desktop */
@media (min-width: 768px) {
    .mobile-search-toggle { display: none; }
}

/* Navigation Bar */
.nav-bar {
    background: var(--bg-primary);
    border-bottom: 1px solid var(--border-primary);
    display: none;
}

@media (min-width: 768px) {
    .nav-bar { display: block; }
}

.nav-list {
    display: flex;
    align-items: center;
    gap: var(--space-1);
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}

.nav-list::-webkit-scrollbar {
    display: none;
}

.nav-item {
    flex-shrink: 0;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: var(--space-1-5);
    padding: var(--space-3) var(--space-3-5);
    color: var(--text-secondary);
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
    border-radius: var(--radius-lg);
    transition: all var(--transition-fast);
    text-decoration: none;
}

.nav-link:hover {
    background: var(--slate-100);
    color: var(--text-primary);
}

.nav-link.active {
    background: var(--brand-500);
    color: white;
}

.nav-icon {
    width: 18px;
    height: 18px;
}

/* Dropdown */
.has-dropdown {
    position: relative;
}

.dropdown-trigger {
    cursor: pointer;
}

.dropdown-icon {
    width: 14px;
    height: 14px;
    transition: transform var(--transition-fast);
}

.has-dropdown.open .dropdown-icon {
    transform: rotate(180deg);
}

.nav-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    min-width: 400px;
    padding: var(--space-4);
    background: var(--bg-card);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-xl);
    opacity: 0;
    visibility: hidden;
    transform: translateY(8px);
    transition: all var(--transition-fast);
    z-index: var(--z-dropdown);
}

.has-dropdown.open .nav-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(4px);
}

.dropdown-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-1);
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2-5) var(--space-3);
    color: var(--text-secondary);
    font-size: var(--text-sm);
    border-radius: var(--radius-lg);
    transition: all var(--transition-fast);
    text-decoration: none;
}

.dropdown-item:hover {
    background: var(--slate-100);
    color: var(--text-primary);
}

.dropdown-icon-item {
    width: 18px;
    height: 18px;
    color: var(--brand-500);
}

/* Mobile Search Panel */
.mobile-search-panel {
    display: none;
    padding: var(--space-3) 0;
    background: var(--bg-primary);
    border-bottom: 1px solid var(--border-primary);
}

.mobile-search-panel.open {
    display: block;
}

@media (min-width: 768px) {
    .mobile-search-panel { display: none !important; }
}

.mobile-search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.mobile-search-input {
    width: 100%;
    padding: var(--space-3) var(--space-12) var(--space-3) var(--space-4);
    background: var(--slate-100);
    border: 1px solid transparent;
    border-radius: var(--radius-xl);
    font-size: var(--text-md);
}

.mobile-search-input:focus {
    outline: none;
    border-color: var(--brand-500);
}

.mobile-search-btn {
    position: absolute;
    right: 44px;
    padding: var(--space-2);
    color: var(--text-tertiary);
}

.mobile-search-close {
    position: absolute;
    right: 0;
    padding: var(--space-2);
    color: var(--text-tertiary);
}

/* Mobile Navigation Drawer */
.mobile-nav-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: calc(var(--z-sticky) - 1);
    opacity: 0;
    visibility: hidden;
    transition: all var(--transition-base);
}

.mobile-nav-overlay.visible {
    opacity: 1;
    visibility: visible;
}

@media (min-width: 768px) {
    .mobile-nav-overlay,
    .mobile-nav-drawer {
        display: none !important;
    }
}

.mobile-nav-drawer {
    position: fixed;
    top: 0;
    left: 0;
    width: 300px;
    max-width: calc(100vw - 48px);
    height: 100vh;
    background: var(--bg-card);
    z-index: var(--z-modal);
    transform: translateX(-100%);
    transition: transform var(--transition-slow);
    display: flex;
    flex-direction: column;
}

.mobile-nav-drawer.open {
    transform: translateX(0);
}

.mobile-nav-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--space-4);
    border-bottom: 1px solid var(--border-primary);
}

.mobile-nav-user {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.user-avatar {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--brand-500);
    color: white;
    font-weight: var(--font-bold);
    border-radius: var(--radius-full);
}

.user-avatar.guest {
    background: var(--slate-300);
}

.user-info {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: var(--font-semibold);
    color: var(--text-primary);
    font-size: var(--text-sm);
}

.user-link {
    font-size: var(--text-xs);
    color: var(--brand-600);
}

.mobile-nav-close {
    padding: var(--space-2);
    color: var(--text-tertiary);
}

.mobile-nav-content {
    flex: 1;
    overflow-y: auto;
    padding: var(--space-4);
}

.mobile-nav-section {
    margin-bottom: var(--space-6);
}

.mobile-nav-section-title {
    font-size: var(--text-xs);
    font-weight: var(--font-semibold);
    color: var(--text-tertiary);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: var(--space-2);
}

.mobile-nav-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.mobile-nav-link {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-3);
    color: var(--text-secondary);
    font-size: var(--text-md);
    border-radius: var(--radius-lg);
    transition: all var(--transition-fast);
    text-decoration: none;
}

.mobile-nav-link:hover,
.mobile-nav-link.active {
    background: var(--slate-100);
    color: var(--text-primary);
}

.mobile-nav-link.active {
    background: var(--brand-50);
    color: var(--brand-700);
}

.mobile-nav-icon {
    width: 20px;
    height: 20px;
    color: var(--text-tertiary);
}

.mobile-nav-link.active .mobile-nav-icon {
    color: var(--brand-600);
}

.mobile-nav-auth {
    padding-top: var(--space-4);
    border-top: 1px solid var(--border-primary);
}

.btn.w-full {
    width: 100%;
    justify-content: center;
}

/* Animations */
@keyframes pulse-soft {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
</style>
