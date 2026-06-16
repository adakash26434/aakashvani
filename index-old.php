<?php
/**
 * आकाशवाणी — Homepage v2
 * Premium 2026 Design
 * World-Class Live Information Platform
 */

// Bootstrap
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;

// Get news
$featuredNews = getPublishedNews(null, null, 1, 0);
$latestNews = getPublishedNews(null, null, 12, 1);
$categories = getCategories();

// SEO
$pageTitle = $t('आकाशवाणी — सूचनाको खुला आकाश', 'Aakashvani — Your Gateway to Information');
?>
<!DOCTYPE html>
<html lang="<?= $isNepali ? 'ne' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <meta name="description" content="<?= $t('नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म। समाचार, NEPSE, IPO, पात्रो, र सरकारी सेवा।', 'Nepal\'s most trusted information platform. News, NEPSE, IPO, Calendar, and Government services.') ?>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://unpkg.com/lucide-static@latest/font/lucide.css">
    
    <!-- Styles -->
    <link rel="stylesheet" href="/assets/css/app.css">
    
    <!-- Page Styles -->
    <style>
        /* Header */
        .site-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #fff;
            border-bottom: 1px solid var(--dark-100);
        }
        
        .header-top {
            background: var(--dark-900);
            color: #fff;
            padding: var(--space-2) 0;
            font-size: 0.75rem;
        }
        
        .header-main {
            padding: var(--space-4) 0;
        }
        
        .header-brand {
            display: flex;
            align-items: center;
            gap: var(--space-3);
        }
        
        .brand-logo {
            width: 40px;
            height: 40px;
            background: var(--primary);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 1.25rem;
        }
        
        .brand-name {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--dark-900);
        }
        
        .header-nav {
            display: flex;
            align-items: center;
            gap: var(--space-1);
        }
        
        .nav-link {
            padding: var(--space-2) var(--space-3);
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--dark-600);
            border-radius: var(--radius);
            transition: all var(--transition);
        }
        
        .nav-link:hover, .nav-link.active {
            background: var(--dark-100);
            color: var(--dark-900);
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }
        
        /* Market Bar */
        .market-bar {
            background: var(--dark-900);
            padding: var(--space-3) 0;
            overflow-x: auto;
        }
        
        .market-items {
            display: flex;
            gap: var(--space-6);
            white-space: nowrap;
        }
        
        .market-item {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            color: #fff;
            font-size: 0.875rem;
        }
        
        .market-label {
            color: var(--dark-400);
        }
        
        .market-value {
            font-weight: 600;
        }
        
        .market-change {
            font-size: 0.75rem;
            padding: var(--space-1) var(--space-2);
            border-radius: var(--radius-sm);
        }
        
        .market-change.up {
            background: rgba(34, 197, 94, 0.2);
            color: #4ade80;
        }
        
        .market-change.down {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
        }
        
        /* Hero Section */
        .hero {
            padding: var(--space-8) 0;
        }
        
        .hero-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: var(--space-6);
        }
        
        @media (max-width: 1024px) {
            .hero-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Featured Card */
        .featured-card {
            position: relative;
            border-radius: var(--radius-2xl);
            overflow: hidden;
            background: var(--dark-900);
        }
        
        .featured-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            opacity: 0.6;
        }
        
        .featured-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: var(--space-8);
            background: linear-gradient(transparent, rgba(0,0,0,0.9));
            color: #fff;
        }
        
        .featured-category {
            display: inline-block;
            padding: var(--space-1) var(--space-3);
            background: var(--primary);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: var(--radius-full);
            margin-bottom: var(--space-3);
        }
        
        .featured-title {
            font-size: clamp(1.5rem, 3vw, 2rem);
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: var(--space-3);
            color: #fff;
        }
        
        .featured-meta {
            display: flex;
            align-items: center;
            gap: var(--space-4);
            font-size: 0.875rem;
            color: rgba(255,255,255,0.7);
        }
        
        /* Sidebar Cards */
        .sidebar-cards {
            display: flex;
            flex-direction: column;
            gap: var(--space-4);
        }
        
        .sidebar-card {
            background: #fff;
            border-radius: var(--radius-xl);
            border: 1px solid var(--dark-100);
            overflow: hidden;
        }
        
        .sidebar-card-header {
            padding: var(--space-4);
            border-bottom: 1px solid var(--dark-100);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .sidebar-card-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--dark-900);
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }
        
        /* Quick Links */
        .quick-links {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--space-3);
            padding: var(--space-4);
        }
        
        .quick-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-3);
            background: var(--dark-50);
            border-radius: var(--radius-lg);
            transition: all var(--transition);
        }
        
        .quick-link:hover {
            background: var(--primary);
            color: #fff;
        }
        
        .quick-link-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary);
            color: #fff;
            border-radius: var(--radius);
        }
        
        .quick-link:hover .quick-link-icon {
            background: #fff;
            color: var(--primary);
        }
        
        .quick-link-text {
            font-size: 0.75rem;
            font-weight: 600;
            text-align: center;
        }
        
        /* Section */
        .section {
            padding: var(--space-12) 0;
        }
        
        .section:nth-child(even) {
            background: var(--dark-50);
        }
        
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--space-6);
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }
        
        .section-title-icon {
            color: var(--primary);
        }
        
        .section-link {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: var(--space-1);
        }
        
        .section-link:hover {
            text-decoration: underline;
        }
        
        /* News Grid */
        .news-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: var(--space-6);
        }
        
        @media (max-width: 1024px) {
            .news-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 640px) {
            .news-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* News Card */
        .news-card {
            background: #fff;
            border-radius: var(--radius-xl);
            overflow: hidden;
            border: 1px solid var(--dark-100);
            transition: all var(--transition);
        }
        
        .news-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--dark-200);
        }
        
        .news-card-image {
            position: relative;
            aspect-ratio: 16/10;
            overflow: hidden;
        }
        
        .news-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform var(--transition-slow);
        }
        
        .news-card:hover .news-card-image img {
            transform: scale(1.05);
        }
        
        .news-card-category {
            position: absolute;
            top: var(--space-3);
            left: var(--space-3);
            padding: var(--space-1) var(--space-2);
            background: var(--primary);
            color: #fff;
            font-size: 0.625rem;
            font-weight: 700;
            border-radius: var(--radius-sm);
            text-transform: uppercase;
        }
        
        .news-card-body {
            padding: var(--space-4);
        }
        
        .news-card-title {
            font-size: 0.9375rem;
            font-weight: 600;
            line-height: 1.4;
            color: var(--dark-900);
            margin-bottom: var(--space-2);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .news-card-meta {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            font-size: 0.75rem;
            color: var(--dark-400);
        }
        
        .news-card-source {
            display: flex;
            align-items: center;
            gap: var(--space-1);
        }
        
        /* Footer */
        .site-footer {
            background: var(--dark-900);
            color: var(--dark-400);
            padding: var(--space-16) 0 var(--space-8);
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: var(--space-12);
            margin-bottom: var(--space-12);
        }
        
        @media (max-width: 768px) {
            .footer-grid {
                grid-template-columns: 1fr;
                gap: var(--space-8);
            }
        }
        
        .footer-brand {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            margin-bottom: var(--space-4);
        }
        
        .footer-brand-logo {
            width: 48px;
            height: 48px;
            background: var(--primary);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 1.5rem;
        }
        
        .footer-brand-name {
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
        }
        
        .footer-description {
            font-size: 0.875rem;
            line-height: 1.7;
            margin-bottom: var(--space-6);
        }
        
        .footer-social {
            display: flex;
            gap: var(--space-3);
        }
        
        .footer-social-link {
            width: 40px;
            height: 40px;
            background: var(--dark-800);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark-400);
            transition: all var(--transition);
        }
        
        .footer-social-link:hover {
            background: var(--primary);
            color: #fff;
        }
        
        .footer-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: var(--space-4);
            padding-bottom: var(--space-2);
            border-bottom: 2px solid var(--primary);
        }
        
        .footer-links {
            display: flex;
            flex-direction: column;
            gap: var(--space-2);
        }
        
        .footer-link {
            font-size: 0.875rem;
            color: var(--dark-400);
            transition: color var(--transition);
        }
        
        .footer-link:hover {
            color: var(--primary);
        }
        
        .footer-bottom {
            padding-top: var(--space-8);
            border-top: 1px solid var(--dark-800);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: var(--space-4);
        }
        
        .footer-copyright {
            font-size: 0.875rem;
        }
        
        .footer-legal {
            display: flex;
            gap: var(--space-4);
            font-size: 0.875rem;
        }
        
        .footer-legal a {
            color: var(--dark-400);
        }
        
        .footer-legal a:hover {
            color: var(--primary);
        }
        
        /* Mobile Menu */
        .mobile-menu {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #fff;
            z-index: 200;
            transform: translateX(-100%);
            transition: transform var(--transition-slow);
            overflow-y: auto;
        }
        
        .mobile-menu.active {
            transform: translateX(0);
        }
        
        .mobile-menu-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: var(--space-4) var(--space-6);
            border-bottom: 1px solid var(--dark-100);
        }
        
        .mobile-menu-nav {
            padding: var(--space-4);
        }
        
        .mobile-nav-link {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-4);
            font-size: 1rem;
            font-weight: 500;
            color: var(--dark-900);
            border-radius: var(--radius-lg);
            transition: background var(--transition);
        }
        
        .mobile-nav-link:hover {
            background: var(--dark-50);
        }
        
        /* Back to Top */
        .back-to-top {
            position: fixed;
            bottom: var(--space-6);
            right: var(--space-6);
            width: 48px;
            height: 48px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: var(--radius-full);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: all var(--transition);
            box-shadow: var(--shadow-lg);
            z-index: 50;
        }
        
        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .back-to-top:hover {
            background: var(--primary-600);
            transform: translateY(-4px);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header-nav {
                display: none;
            }
            
            .header-actions .btn:not(.mobile-menu-btn) {
                display: none;
            }
            
            .featured-image {
                height: 280px;
            }
            
            .featured-content {
                padding: var(--space-4);
            }
        }
    </style>
</head>
<body>
    
    <!-- Header -->
    <header class="site-header">
        <div class="header-top">
            <div class="container">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <span><?= date('l, j F Y') ?></span>
                        <span class="text-muted">|</span>
                        <span class="text-ne"><?= $t('शुभ प्रभात', 'Good Morning') ?></span>
                    </div>
                    <div class="flex items-center gap-4">
                        <a href="/language.php" class="flex items-center gap-2">
                            <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                            <?= $isNepali ? 'EN' : 'ने' ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="header-main">
            <div class="container">
                <div class="flex items-center justify-between gap-4">
                    <!-- Brand -->
                    <a href="/" class="header-brand">
                        <div class="brand-logo">आ</div>
                        <span class="brand-name"><?= $t('आकाशवाणी', 'Aakashvani') ?></span>
                    </a>
                    
                    <!-- Nav -->
                    <nav class="header-nav">
                        <a href="/" class="nav-link active"><?= $t('गृह', 'Home') ?></a>
                        <a href="/news.php" class="nav-link"><?= $t('समाचार', 'News') ?></a>
                        <a href="/nepali-patro.php" class="nav-link"><?= $t('पात्रो', 'Calendar') ?></a>
                        <a href="/rashifal.php" class="nav-link"><?= $t('राशिफल', 'Horoscope') ?></a>
                        <a href="/ipo-tracker.php" class="nav-link"><?= $t('IPO', 'IPO') ?></a>
                        <a href="/tools.php" class="nav-link"><?= $t('टूल', 'Tools') ?></a>
                    </nav>
                    
                    <!-- Actions -->
                    <div class="header-actions">
                        <button class="btn btn-ghost btn-icon" id="searchBtn" aria-label="Search">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </button>
                        <button class="btn btn-ghost btn-icon mobile-menu-btn" id="mobileMenuBtn" aria-label="Menu">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12h16M4 6h16M4 18h16"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Market Bar -->
    <div class="market-bar">
        <div class="container">
            <div class="market-items">
                <div class="market-item">
                    <span class="market-label">NEPSE</span>
                    <span class="market-value">2,845.67</span>
                    <span class="market-change up">+1.2%</span>
                </div>
                <div class="market-item">
                    <span class="market-label"><?= $t('सुन', 'Gold') ?></span>
                    <span class="market-value">रु 145,000</span>
                    <span class="market-change up">+0.5%</span>
                </div>
                <div class="market-item">
                    <span class="market-label">USD</span>
                    <span class="market-value">रु 133.50</span>
                </div>
                <div class="market-item">
                    <span class="market-label"><?= $t('पेट्रोल', 'Petrol') ?></span>
                    <span class="market-value">रु 178</span>
                </div>
                <div class="market-item">
                    <span class="market-label"><?= $t('बिजुली', 'Electricity') ?></span>
                    <span class="market-value">रु 12.50</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hero -->
    <section class="hero">
        <div class="container">
            <div class="hero-grid">
                <!-- Featured News -->
                <?php if (!empty($featuredNews)): ?>
                <a href="/news-post.php?slug=<?= urlencode($featuredNews[0]['slug'] ?? '') ?>" class="featured-card">
                    <img src="<?= htmlspecialchars($featuredNews[0]['image'] ?? '/assets/images/placeholder.jpg') ?>" alt="" class="featured-image">
                    <div class="featured-content">
                        <span class="featured-category"><?= htmlspecialchars($featuredNews[0]['category'] ?? 'समाचार') ?></span>
                        <h2 class="featured-title"><?= htmlspecialchars($featuredNews[0]['title'] ?? '') ?></h2>
                        <div class="featured-meta">
                            <span><?= htmlspecialchars($featuredNews[0]['source_name'] ?? 'आकाशवाणी') ?></span>
                            <span><?= timeAgo($featuredNews[0]['published_at'] ?? '') ?></span>
                        </div>
                    </div>
                </a>
                <?php else: ?>
                <div class="featured-card">
                    <img src="/assets/images/placeholder.jpg" alt="" class="featured-image">
                    <div class="featured-content">
                        <span class="featured-category">समाचार</span>
                        <h2 class="featured-title">ताजा समाचारको लागि जोडिइरहनुहोस्</h2>
                        <div class="featured-meta">
                            <span>आकाशवाणी</span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Sidebar -->
                <div class="sidebar-cards">
                    <!-- Quick Links -->
                    <div class="sidebar-card">
                        <div class="sidebar-card-header">
                            <span class="sidebar-card-title">
                                <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                                <?= $t('छिटो लिंक', 'Quick Links') ?>
                            </span>
                        </div>
                        <div class="quick-links">
                            <a href="/nepali-patro.php" class="quick-link">
                                <div class="quick-link-icon">
                                    <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                </div>
                                <span class="quick-link-text"><?= $t('पात्रो', 'Calendar') ?></span>
                            </a>
                            <a href="/rashifal.php" class="quick-link">
                                <div class="quick-link-icon">
                                    <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3z"/></svg>
                                </div>
                                <span class="quick-link-text"><?= $t('राशिफल', 'Horoscope') ?></span>
                            </a>
                            <a href="/ipo-tracker.php" class="quick-link">
                                <div class="quick-link-icon">
                                    <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                                </div>
                                <span class="quick-link-text"><?= $t('IPO', 'IPO') ?></span>
                            </a>
                            <a href="/emergency.php" class="quick-link">
                                <div class="quick-link-icon" style="background: #ef4444">
                                    <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                </div>
                                <span class="quick-link-text"><?= $t('आपतकालीन', 'Emergency') ?></span>
                            </a>
                            <a href="/gov-services.php" class="quick-link">
                                <div class="quick-link-icon" style="background: #3b82f6">
                                    <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9v.01"/><path d="M9 12v.01"/><path d="M9 15v.01"/><path d="M9 18v.01"/></svg>
                                </div>
                                <span class="quick-link-text"><?= $t('सरकारी', 'Gov') ?></span>
                            </a>
                            <a href="/tools.php" class="quick-link">
                                <div class="quick-link-icon" style="background: #f59e0b">
                                    <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                                </div>
                                <span class="quick-link-text"><?= $t('टूल', 'Tools') ?></span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Weather -->
                    <div class="sidebar-card">
                        <div class="sidebar-card-header">
                            <span class="sidebar-card-title">
                                <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/></svg>
                                <?= $t('मौसम', 'Weather') ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="flex items-center gap-4">
                                <div class="text-4xl">☀️</div>
                                <div>
                                    <div class="text-2xl font-bold">22°C</div>
                                    <div class="text-sm text-secondary"><?= $t('काठमाडौं', 'Kathmandu') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Latest News -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">
                    <svg class="icon section-title-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M13 2v7h7"/></svg>
                    <?= $t('ताजा समाचार', 'Latest News') ?>
                </h2>
                <a href="/news.php" class="section-link">
                    <?= $t('सबै हेर्नुहोस्', 'View All') ?>
                    <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            </div>
            
            <div class="news-grid">
                <?php foreach (array_slice($latestNews, 0, 8) as $news): ?>
                <a href="/news-post.php?slug=<?= urlencode($news['slug'] ?? '') ?>" class="news-card">
                    <div class="news-card-image">
                        <img src="<?= htmlspecialchars($news['image'] ?? '/assets/images/placeholder.jpg') ?>" alt="" loading="lazy">
                        <span class="news-card-category"><?= htmlspecialchars($news['category'] ?? '') ?></span>
                    </div>
                    <div class="news-card-body">
                        <h3 class="news-card-title"><?= htmlspecialchars($news['title'] ?? '') ?></h3>
                        <div class="news-card-meta">
                            <span class="news-card-source">
                                <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                <?= htmlspecialchars($news['source_name'] ?? '') ?>
                            </span>
                            <span><?= timeAgo($news['published_at'] ?? '') ?></span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Brand -->
                <div>
                    <div class="footer-brand">
                        <div class="footer-brand-logo">आ</div>
                        <span class="footer-brand-name"><?= $t('आकाशवाणी', 'Aakashvani') ?></span>
                    </div>
                    <p class="footer-description">
                        <?= $t('नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म। समाचार, NEPSE, IPO, पात्रो, र सरकारी सेवा सबै एकै ठाउँमा।', 'Nepal\'s most trusted information platform. News, NEPSE, IPO, Calendar, and Government services all in one place.') ?>
                    </p>
                    <div class="footer-social">
                        <a href="#" class="footer-social-link" aria-label="Facebook">
                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                        </a>
                        <a href="#" class="footer-social-link" aria-label="Twitter">
                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"/></svg>
                        </a>
                        <a href="#" class="footer-social-link" aria-label="YouTube">
                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02" fill="#fff"/></svg>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 class="footer-title"><?= $t('छिटो लिंक', 'Quick Links') ?></h4>
                    <div class="footer-links">
                        <a href="/news.php" class="footer-link"><?= $t('समाचार', 'News') ?></a>
                        <a href="/nepali-patro.php" class="footer-link"><?= $t('पात्रो', 'Calendar') ?></a>
                        <a href="/rashifal.php" class="footer-link"><?= $t('राशिफल', 'Horoscope') ?></a>
                        <a href="/ipo-tracker.php" class="footer-link"><?= $t('IPO ट्र्याकर', 'IPO Tracker') ?></a>
                        <a href="/emergency.php" class="footer-link"><?= $t('आपतकालीन', 'Emergency') ?></a>
                    </div>
                </div>
                
                <!-- Categories -->
                <div>
                    <h4 class="footer-title"><?= $t('वर्गीकरण', 'Categories') ?></h4>
                    <div class="footer-links">
                        <a href="/news.php?category=politics" class="footer-link"><?= $t('राजनीति', 'Politics') ?></a>
                        <a href="/news.php?category=economy" class="footer-link"><?= $t('अर्थ', 'Economy') ?></a>
                        <a href="/news.php?category=sports" class="footer-link"><?= $t('खेलकुद', 'Sports') ?></a>
                        <a href="/news.php?category=technology" class="footer-link"><?= $t('प्रविधि', 'Technology') ?></a>
                        <a href="/news.php?category=entertainment" class="footer-link"><?= $t('मनोरञ्जन', 'Entertainment') ?></a>
                    </div>
                </div>
                
                <!-- Legal -->
                <div>
                    <h4 class="footer-title"><?= $t('कानूनी', 'Legal') ?></h4>
                    <div class="footer-links">
                        <a href="/privacy.php" class="footer-link"><?= $t('गोपनीयता', 'Privacy Policy') ?></a>
                        <a href="/terms.php" class="footer-link"><?= $t('सेवा सर्त', 'Terms of Service') ?></a>
                        <a href="/contact.php" class="footer-link"><?= $t('सम्पर्क', 'Contact') ?></a>
                        <a href="/about.php" class="footer-link"><?= $t('हाम्रो बारेमा', 'About Us') ?></a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p class="footer-copyright">&copy; <?= date('Y') ?> <?= $t('आकाशवाणी।', 'Aakashvani.') ?> <?= $t('सर्वाधिकार सुरक्षित।', 'All rights reserved.') ?></p>
                <div class="footer-legal">
                    <a href="/privacy.php"><?= $t('गोपनीयता', 'Privacy') ?></a>
                    <a href="/terms.php"><?= $t('सर्त', 'Terms') ?></a>
                    <a href="/contact.php"><?= $t('सम्पर्क', 'Contact') ?></a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top -->
    <button class="back-to-top" id="backToTop" aria-label="Back to top">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m18 15-6-6-6 6"/></svg>
    </button>
    
    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-header">
            <span class="brand-name"><?= $t('आकाशवाणी', 'Aakashvani') ?></span>
            <button class="btn btn-ghost btn-icon" id="mobileMenuClose" aria-label="Close">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <nav class="mobile-menu-nav">
            <a href="/" class="mobile-nav-link">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
                <?= $t('गृहपृष्ठ', 'Home') ?>
            </a>
            <a href="/news.php" class="mobile-nav-link">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 0-2-2zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8z"/></svg>
                <?= $t('समाचार', 'News') ?>
            </a>
            <a href="/nepali-patro.php" class="mobile-nav-link">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <?= $t('पात्रो', 'Calendar') ?>
            </a>
            <a href="/rashifal.php" class="mobile-nav-link">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3z"/></svg>
                <?= $t('राशिफल', 'Horoscope') ?>
            </a>
            <a href="/ipo-tracker.php" class="mobile-nav-link">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                <?= $t('IPO/NEPSE', 'IPO/NEPSE') ?>
            </a>
            <a href="/tools.php" class="mobile-nav-link">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                <?= $t('टूलहरू', 'Tools') ?>
            </a>
            <a href="/gov-services.php" class="mobile-nav-link">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9v.01"/><path d="M9 12v.01"/><path d="M9 15v.01"/><path d="M9 18v.01"/></svg>
                <?= $t('सरकारी सेवा', 'Government Services') ?>
            </a>
            <a href="/emergency.php" class="mobile-nav-link">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                <?= $t('आपतकालीन', 'Emergency') ?>
            </a>
        </nav>
    </div>
    
    <!-- Scripts -->
    <script src="/assets/js/app.js"></script>
</body>
</html>
