<?php
/**
 * आकाशवाणी — Desktop Header (Professional News Portal)
 * Full-width desktop-first layout with sidebar
 * 
 * Usage: Replace header.php include with this file for desktop experience
 */

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$isHome = in_array($currentPath, ['/', '/index.php', '/home.php']);

// BS Date
$nepal = new DateTimeZone('Asia/Kathmandu');
$now = new DateTime('now', $nepal);
[$adY,$adM,$adD,$adDow] = [(int)$now->format('Y'),(int)$now->format('n'),(int)$now->format('j'),(int)$now->format('w')];
$_bsData=[2080=>[0,31,32,31,32,31,30,30,29,30,29,30,30],2081=>[0,31,31,32,31,31,31,30,29,30,29,30,30],
          2082=>[0,31,32,31,32,31,30,30,30,29,30,29,31],2083=>[0,31,32,31,32,31,30,30,30,29,30,30,30],
          2084=>[0,31,31,32,31,31,30,30,30,29,30,30,30],2085=>[0,31,32,31,32,31,30,30,30,29,30,29,31],
          2086=>[0,31,32,31,32,31,30,30,30,29,30,29,31],2087=>[0,31,31,32,31,31,31,30,29,30,29,30,30]];
$refJd=gregoriantojd(4,14,2026); $jdNow=gregoriantojd($adM,$adD,$adY); $diff=$jdNow-$refJd;
$bsY=2083;$bsM=1;$bsD=1;
if($diff>=0){$rem=$diff;while($rem>0){$dim=$_bsData[$bsY][$bsM]??30;$left=$dim-$bsD;if($rem<=$left){$bsD+=$rem;$rem=0;}else{$rem-=($left+1);$bsD=1;$bsM++;if($bsM>12){$bsM=1;$bsY++;}}}}
else{$rem=-$diff;while($rem>0){if($bsD>1){$s=min($rem,$bsD-1);$bsD-=$s;$rem-=$s;}else{$bsM--;if($bsM<1){$bsM=12;$bsY--;}$bsD=$_bsData[$bsY][$bsM]??30;$rem-=1;}}}
$_bsMonths=['','बैशाख','जेठ','असार','श्रावण','भाद्र','आश्विन','कार्तिक','मंसिर','पौष','माघ','फाल्गुन','चैत्र'];
$_bsDays=['आइतबार','सोमबार','मंगलबार','बुधबार','बिहिबार','शुक्रबार','शनिबार'];
$bsDateStr = $_bsDays[$adDow].', '.$bsD.' '.$_bsMonths[$bsM].' '.$bsY;
$bsShort = $bsD.' '.$_bsMonths[$bsM].' '.$bsY;

// Greeting
$hr = (int)$now->format('G');
$greetNe = $hr < 11 ? 'शुभ प्रभात' : ($hr < 16 ? 'नमस्कार' : ($hr < 19 ? 'शुभ साँझ' : 'शुभ रात्री'));

// Language
$lang = $_COOKIE['site_lang'] ?? 'ne';
$isNepali = ($lang !== 'en');

// Navigation
$mainNav = [
    '/' => ['ne'=>'गृहपृष्ठ', 'en'=>'Home', 'icon'=>'home'],
    '/news.php' => ['ne'=>'समाचार', 'en'=>'News', 'icon'=>'newspaper'],
    '/info-hub.php' => ['ne'=>'जानकारी', 'en'=>'Info Hub', 'icon'=>'layout-grid'],
    '/ipo-tracker.php' => ['ne'=>'NEPSE', 'en'=>'NEPSE', 'icon'=>'trending-up'],
    '/nepali-patro.php' => ['ne'=>'पात्रो', 'en'=>'Calendar', 'icon'=>'calendar'],
    '/rashifal.php' => ['ne'=>'राशिफल', 'en'=>'Rashifal', 'icon'=>'sparkles'],
    '/tools.php' => ['ne'=>'टूल', 'en'=>'Tools', 'icon'=>'wrench'],
    '/gov-services.php' => ['ne'=>'सरकारी', 'en'=>'Government', 'icon'=>'landmark'],
];

$moreNav = [
    '/weather.php' => ['ne'=>'मौसम', 'en'=>'Weather', 'icon'=>'cloud-sun'],
    '/gold-price.php' => ['ne'=>'सुनको मूल्य', 'en'=>'Gold Price', 'icon'=>'gem'],
    '/currency-converter.php' => ['ne'=>'मुद्रा', 'en'=>'Currency', 'icon'=>'coins'],
    '/nokari.php' => ['ne'=>'नोकरी', 'en'=>'Jobs', 'icon'=>'briefcase'],
    '/loksewa.php' => ['ne'=>'लोकसेवा', 'en'=>'Loksewa', 'icon'=>'building'],
    '/cricket.php' => ['ne'=>'क्रिकेट', 'en'=>'Cricket', 'icon'=>'trophy'],
    '/emergency.php' => ['ne'=>'आपतकालीन', 'en'=>'Emergency', 'icon'=>'phone'],
    '/morning-brief.php' => ['ne'=>'बिहानी ब्रिफ', 'en'=>'Morning Brief', 'icon'=>'sunrise'],
    '/market.php' => ['ne'=>'बजार', 'en'=>'Market', 'icon'=>'bar-chart-2'],
];

function navActDesktop(string $href, string $path): bool {
    if($href==='/') return in_array($path,['/','/index.php','/home.php']);
    return str_starts_with($path, rtrim($href,'/'));
}

$isLoggedIn = function_exists('isLoggedIn') && isLoggedIn();
$cu = ($isLoggedIn && function_exists('getCurrentUser')) ? getCurrentUser() : null;
$userInitial = $cu && !empty($cu['name']) ? mb_substr($cu['name'],0,1) : 'U';
?>
<!DOCTYPE html>
<html lang="<?= $isNepali ? 'ne' : 'en' ?>" class="scroll-smooth">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<meta name="description" content="<?= htmlspecialchars($pageDesc ?? 'नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म') ?>"/>
<meta property="og:site_name" content="आकाशवाणी"/>
<meta property="og:title" content="<?= htmlspecialchars($pageTitle ?? 'आकाशवाणी') ?>"/>
<meta name="twitter:card" content="summary_large_image"/>

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet"/>

<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

<!-- Design System CSS -->
<link rel="stylesheet" href="/assets/css/global.css"/>

<!-- Favicon -->
<link rel="icon" type="image/svg+xml" href="/assets/favicon.svg"/>
<title><?= htmlspecialchars($pageTitle ?? 'आकाशवाणी') ?></title>
</head>
<body class="desktop-body">

<!-- ═══════════════════════════════════════════════════════════════
     TOP BAR - Date, Language, Quick Links
     ═══════════════════════════════════════════════════════════════ -->
<div class="desktop-topbar">
    <div class="desktop-container">
        <div class="topbar-left">
            <span class="topbar-date">
                <i data-lucide="calendar" class="icon-sm"></i>
                <?= $bsDateStr ?>
            </span>
            <span class="topbar-sep">|</span>
            <span class="topbar-greet"><?= $greetNe ?></span>
        </div>
        <div class="topbar-right">
            <a href="?lang=<?= $isNepali ? 'en' : 'ne' ?>" class="topbar-link">
                <i data-lucide="languages" class="icon-sm"></i>
                <?= $isNepali ? 'English' : 'नेपाली' ?>
            </a>
            <?php if ($isLoggedIn): ?>
                <a href="/profile.php" class="topbar-link">
                    <i data-lucide="user" class="icon-sm"></i>
                    Profile
                </a>
            <?php else: ?>
                <a href="/login.php" class="topbar-link topbar-login">
                    <i data-lucide="log-in" class="icon-sm"></i>
                    Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     MAIN HEADER - Logo, Search, Actions
     ═══════════════════════════════════════════════════════════════ -->
<header class="desktop-header">
    <div class="desktop-container">
        
        <!-- Logo -->
        <a href="/" class="desktop-logo">
            <div class="logo-icon">
                <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                    <rect width="48" height="48" rx="12" fill="url(#lg)"/>
                    <path d="M12 33V15L24 9L36 15V33L24 39L12 33Z" stroke="white" stroke-width="2.5" stroke-linejoin="round"/>
                    <path d="M24 21V27M24 33V36" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                    <circle cx="24" cy="16" r="3" fill="white"/>
                    <defs>
                        <linearGradient id="lg" x1="0" y1="0" x2="48" y2="48">
                            <stop stop-color="#10B981"/>
                            <stop offset="1" stop-color="#059669"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
            <div class="logo-text">
                <span class="logo-title">आकाशवाणी</span>
                <span class="logo-tagline">सूचनाको खुला आकाश</span>
            </div>
        </a>
        
        <!-- Search -->
        <form action="/search.php" method="GET" class="desktop-search">
            <div class="search-wrapper">
                <i data-lucide="search" class="search-icon"></i>
                <input type="search" name="q" placeholder="Search news, info..." class="search-input" autocomplete="off"/>
                <button type="submit" class="search-btn">
                    <i data-lucide="arrow-right" class="icon-md"></i>
                </button>
            </div>
        </form>
        
        <!-- Actions -->
        <div class="desktop-actions">
            <button class="action-btn" title="Notifications">
                <i data-lucide="bell" class="icon-lg"></i>
            </button>
            <button class="action-btn" title="Bookmarks">
                <i data-lucide="bookmark" class="icon-lg"></i>
            </button>
            <?php if ($isLoggedIn): ?>
                <a href="/profile.php" class="user-avatar"><?= $userInitial ?></a>
            <?php else: ?>
                <a href="/login.php" class="btn-login">
                    <i data-lucide="user" class="icon-md"></i>
                    Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- ═══════════════════════════════════════════════════════════════
     NAVIGATION BAR - Sticky
     ═══════════════════════════════════════════════════════════════ -->
<nav class="desktop-nav" id="desktop-nav">
    <div class="desktop-container">
        <ul class="nav-list">
            <?php foreach ($mainNav as $href => $item): ?>
                <?php $active = navActDesktop($href, $currentPath); ?>
                <li class="nav-item">
                    <a href="<?= $href ?>" class="nav-link <?= $active ? 'active' : '' ?>">
                        <i data-lucide="<?= $item['icon'] ?>" class="nav-icon"></i>
                        <span><?= $isNepali ? $item['ne'] : $item['en'] ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
            
            <!-- More Dropdown -->
            <li class="nav-item has-dropdown">
                <button class="nav-link dropdown-trigger" aria-expanded="false">
                    <span>More</span>
                    <i data-lucide="chevron-down" class="dropdown-icon"></i>
                </button>
                <div class="nav-dropdown">
                    <div class="dropdown-grid">
                        <?php foreach ($moreNav as $href => $item): ?>
                            <a href="<?= $href ?>" class="dropdown-item">
                                <i data-lucide="<?= $item['icon'] ?>" class="dropdown-icon-item"></i>
                                <span><?= $isNepali ? $item['ne'] : $item['en'] ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </li>
        </ul>
        
        <!-- Live Ticker -->
        <div class="live-ticker">
            <span class="ticker-badge">LIVE</span>
            <div class="ticker-text">
                <span>Welcome to Aakashbani - Nepal's fastest information platform</span>
            </div>
        </div>
    </div>
</nav>

<!-- ═══════════════════════════════════════════════════════════════
     MAIN CONTENT AREA
     ═══════════════════════════════════════════════════════════════ -->
<main class="desktop-main" id="main-content">
    <div class="desktop-content-grid">
        
        <!-- Sidebar -->
        <aside class="desktop-sidebar">
            
            <!-- Quick Links -->
            <div class="sidebar-card">
                <h3 class="sidebar-title">
                    <i data-lucide="compass" class="icon-md"></i>
                    Quick Links
                </h3>
                <ul class="sidebar-nav">
                    <li><a href="/news.php?sort=latest" class="sidebar-link">
                        <i data-lucide="clock" class="sidebar-icon"></i>
                        <span>Latest News</span>
                        <i data-lucide="chevron-right" class="sidebar-arrow"></i>
                    </a></li>
                    <li><a href="/news.php?sort=trending" class="sidebar-link">
                        <i data-lucide="flame" class="sidebar-icon"></i>
                        <span>Trending</span>
                        <i data-lucide="chevron-right" class="sidebar-arrow"></i>
                    </a></li>
                    <li><a href="/news.php?sort=popular" class="sidebar-link">
                        <i data-lucide="star" class="sidebar-icon"></i>
                        <span>Popular</span>
                        <i data-lucide="chevron-right" class="sidebar-arrow"></i>
                    </a></li>
                </ul>
            </div>
            
            <!-- Categories -->
            <div class="sidebar-card">
                <h3 class="sidebar-title">
                    <i data-lucide="grid-3x3" class="icon-md"></i>
                    Categories
                </h3>
                <div class="category-grid">
                    <a href="/news.php?category=politics" class="category-chip">Politics</a>
                    <a href="/news.php?category=economy" class="category-chip">Economy</a>
                    <a href="/news.php?category=sports" class="category-chip">Sports</a>
                    <a href="/news.php?category=technology" class="category-chip">Technology</a>
                    <a href="/news.php?category=entertainment" class="category-chip">Entertainment</a>
                    <a href="/news.php?category=international" class="category-chip">International</a>
                </div>
            </div>
            
            <!-- Market Widget -->
            <div class="sidebar-card sidebar-dark">
                <h3 class="sidebar-title sidebar-title-light">
                    <i data-lucide="trending-up" class="icon-md"></i>
                    Market Summary
                </h3>
                <div class="market-list">
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
        <article class="desktop-content">

<script>
// Initialize Lucide Icons
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});

// Dropdown Toggle
document.querySelectorAll('.dropdown-trigger').forEach(function(trigger) {
    trigger.addEventListener('click', function() {
        var isOpen = this.getAttribute('aria-expanded') === 'true';
        this.setAttribute('aria-expanded', !isOpen);
        this.closest('.has-dropdown').classList.toggle('open');
    });
});

// Close dropdown on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('.has-dropdown')) {
        document.querySelectorAll('.dropdown-trigger').forEach(function(t) {
            t.setAttribute('aria-expanded', 'false');
        });
        document.querySelectorAll('.has-dropdown').forEach(function(d) {
            d.classList.remove('open');
        });
    }
});

// Escape key closes dropdowns
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.has-dropdown').forEach(function(d) {
            d.classList.remove('open');
        });
    }
});

// Sticky nav shadow on scroll
var nav = document.getElementById('desktop-nav');
if (nav) {
    window.addEventListener('scroll', function() {
        if (window.scrollY > 10) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
    });
}
</script>

<style>
/* ═══════════════════════════════════════════════════════════════
   DESKTOP STYLES - Professional News Portal
   ═══════════════════════════════════════════════════════════════ */

.desktop-body {
    font-family: 'Inter', 'Hind Siliguri', system-ui, -apple-system, sans-serif;
    background: #f8fafc;
    color: #0f172a;
    line-height: 1.6;
    min-height: 100vh;
}

/* Container */
.desktop-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 24px;
}

/* ═══════════════════════════════════════════════════════════════
   TOP BAR
   ═══════════════════════════════════════════════════════════════ */
.desktop-topbar {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    color: #e2e8f0;
    padding: 10px 0;
    font-size: 13px;
}

.topbar-left,
.topbar-right {
    display: flex;
    align-items: center;
    gap: 16px;
}

.topbar-date,
.topbar-greet {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #94a3b8;
}

.topbar-sep {
    color: #475569;
}

.topbar-link {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #94a3b8;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
}

.topbar-link:hover {
    color: #fff;
}

.topbar-login {
    background: #10b981;
    color: #fff !important;
    padding: 6px 14px;
    border-radius: 8px;
}

.topbar-login:hover {
    background: #059669;
}

/* ═══════════════════════════════════════════════════════════════
   MAIN HEADER
   ═══════════════════════════════════════════════════════════════ */
.desktop-header {
    background: #fff;
    padding: 20px 0;
    border-bottom: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.desktop-header .desktop-container {
    display: flex;
    align-items: center;
    gap: 40px;
}

/* Logo */
.desktop-logo {
    display: flex;
    align-items: center;
    gap: 14px;
    text-decoration: none;
    flex-shrink: 0;
}

.logo-icon {
    width: 48px;
    height: 48px;
}

.logo-text {
    display: flex;
    flex-direction: column;
}

.logo-title {
    font-size: 26px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.02em;
    line-height: 1.1;
}

.logo-tagline {
    font-size: 11px;
    color: #64748b;
    margin-top: 2px;
}

/* Search */
.desktop-search {
    flex: 1;
    max-width: 600px;
}

.search-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: 16px;
    color: #94a3b8;
    pointer-events: none;
}

.search-input {
    width: 100%;
    padding: 14px 56px 14px 48px;
    background: #f1f5f9;
    border: 2px solid transparent;
    border-radius: 14px;
    font-size: 15px;
    transition: all 0.2s;
}

.search-input:focus {
    outline: none;
    background: #fff;
    border-color: #10b981;
    box-shadow: 0 0 0 4px rgba(16,185,129,0.1);
}

.search-btn {
    position: absolute;
    right: 8px;
    width: 40px;
    height: 40px;
    background: #10b981;
    color: #fff;
    border: none;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s;
}

.search-btn:hover {
    background: #059669;
}

/* Actions */
.desktop-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.action-btn {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    background: transparent;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.action-btn:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.user-avatar {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 18px;
    text-decoration: none;
    transition: transform 0.2s;
}

.user-avatar:hover {
    transform: scale(1.05);
}

.btn-login {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    border-radius: 12px;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16,185,129,0.3);
}

/* ═══════════════════════════════════════════════════════════════
   NAVIGATION
   ═══════════════════════════════════════════════════════════════ */
.desktop-nav {
    background: #fff;
    border-bottom: 3px solid #10b981;
    position: sticky;
    top: 0;
    z-index: 100;
    transition: box-shadow 0.2s;
}

.desktop-nav.scrolled {
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.desktop-nav .desktop-container {
    display: flex;
    align-items: center;
    gap: 32px;
}

.nav-list {
    display: flex;
    align-items: center;
    gap: 4px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav-item {
    position: relative;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 16px 18px;
    color: #475569;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    border-radius: 10px;
    transition: all 0.2s;
}

.nav-link:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.nav-link.active {
    background: #10b981;
    color: #fff;
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
    background: none;
    border: none;
    cursor: pointer;
    font: inherit;
}

.dropdown-icon {
    width: 14px;
    height: 14px;
    transition: transform 0.2s;
}

.has-dropdown.open .dropdown-icon {
    transform: rotate(180deg);
}

.nav-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    min-width: 320px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    border: 1px solid #e2e8f0;
    opacity: 0;
    visibility: hidden;
    transform: translateY(8px);
    transition: all 0.2s;
    z-index: 100;
}

.has-dropdown.open .nav-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(4px);
}

.dropdown-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 4px;
    padding: 12px;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    color: #475569;
    font-size: 13px;
    font-weight: 500;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.15s;
}

.dropdown-item:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.dropdown-icon-item {
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
}

.ticker-badge {
    padding: 4px 12px;
    background: #ef4444;
    color: #fff;
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

.ticker-text {
    font-size: 13px;
    color: #64748b;
    max-width: 280px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ═══════════════════════════════════════════════════════════════
   CONTENT GRID
   ═══════════════════════════════════════════════════════════════ */
.desktop-main {
    padding: 32px 0;
}

.desktop-content-grid {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 24px;
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 32px;
}

/* ═══════════════════════════════════════════════════════════════
   SIDEBAR
   ═══════════════════════════════════════════════════════════════ */
.desktop-sidebar {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.sidebar-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    border: 1px solid #e2e8f0;
}

.sidebar-title {
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
    margin: 0;
    padding: 0;
}

.sidebar-link {
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

.sidebar-link:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.sidebar-icon {
    width: 18px;
    height: 18px;
    color: #10b981;
}

.sidebar-arrow {
    width: 14px;
    height: 14px;
    color: #cbd5e1;
    margin-left: auto;
}

/* Categories */
.category-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.category-chip {
    padding: 8px 16px;
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
    color: #fff;
}

/* Market Widget */
.sidebar-dark {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    color: #fff;
}

.sidebar-title-light {
    color: #fff;
    border-bottom-color: #10b981;
}

.market-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.market-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255,255,255,0.1);
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
    color: #fff;
}

/* ═══════════════════════════════════════════════════════════════
   MAIN CONTENT
   ═══════════════════════════════════════════════════════════════ */
.desktop-content {
    background: #fff;
    border-radius: 16px;
    padding: 28px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    border: 1px solid #e2e8f0;
    min-height: 600px;
}

/* ═══════════════════════════════════════════════════════════════
   RESPONSIVE
   ═══════════════════════════════════════════════════════════════ */
@media (max-width: 1200px) {
    .desktop-content-grid {
        grid-template-columns: 240px 1fr;
        gap: 24px;
    }
    
    .live-ticker {
        display: none;
    }
}

@media (max-width: 1024px) {
    .desktop-content-grid {
        grid-template-columns: 1fr;
    }
    
    .desktop-sidebar {
        display: none;
    }
    
    .logo-tagline {
        display: none;
    }
    
    .desktop-header .desktop-container {
        gap: 20px;
    }
    
    .desktop-search {
        max-width: 400px;
    }
}

@media (max-width: 768px) {
    .desktop-container {
        padding: 0 16px;
    }
    
    .desktop-header {
        padding: 16px 0;
    }
    
    .desktop-header .desktop-container {
        flex-wrap: wrap;
    }
    
    .desktop-search {
        order: 3;
        max-width: 100%;
        width: 100%;
    }
    
    .nav-link span {
        display: none;
    }
    
    .nav-link {
        padding: 14px;
    }
    
    .logo-title {
        font-size: 20px;
    }
    
    .topbar-greet {
        display: none;
    }
}

/* Icon Sizes */
.icon-sm { width: 14px; height: 14px; }
.icon-md { width: 18px; height: 18px; }
.icon-lg { width: 22px; height: 22px; }
.icon-xl { width: 28px; height: 28px; }
</style>
