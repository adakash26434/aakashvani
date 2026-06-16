<?php
/**
 * आकाशवाणी — Homepage (LIVE API DATA)
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;

// Get news from database (will also be fetched via API)
$featuredNews = getPublishedNews(null, null, 1, 0);
$latestNews = getPublishedNews(null, null, 12, 1);

// SEO
$pageTitle = $t('आकाशवाणी — सूचनाको खुला आकाश', 'Aakashvani — Your Gateway to Information');
?>
<!DOCTYPE html>
<html lang="<?= $isNepali ? 'ne' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <meta name="description" content="<?= $t('नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म। समाचार, NEPSE, IPO, पात्रो, र सरकारी सेवा।', 'Nepal\'s most trusted information platform.') ?>">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#16a34a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Aakashvani">
    <link rel="manifest" href="/manifest.json">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="/assets/css/app.css">
    
    <style>
        /* Top Bar */
        .top-bar {
            background: var(--dark-900);
            color: #fff;
            padding: var(--space-2) 0;
            font-size: 0.75rem;
        }
        
        .top-bar-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .top-bar a {
            color: rgba(255,255,255,0.8);
            margin-left: var(--space-3);
        }
        
        .top-bar a:hover { color: #fff; }
        
        /* Header */
        .site-header {
            background: #fff;
            border-bottom: 1px solid var(--dark-100);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-main {
            padding: var(--space-3) 0;
        }
        
        .header-grid {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: var(--space-6);
            align-items: center;
        }
        
        @media (max-width: 768px) {
            .header-grid {
                grid-template-columns: 1fr auto;
            }
            .header-search { display: none; }
        }
        
        .brand {
            display: flex;
            align-items: center;
            gap: var(--space-3);
        }
        
        .brand-logo {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--primary), var(--primary-600));
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 1.5rem;
        }
        
        .brand-text h1 {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--dark-900);
            line-height: 1.2;
        }
        
        .brand-text span {
            font-size: 0.75rem;
            color: var(--dark-400);
        }
        
        /* Search */
        .search-box {
            position: relative;
            max-width: 500px;
        }
        
        .search-input {
            width: 100%;
            padding: var(--space-3) var(--space-4);
            padding-left: var(--space-10);
            border: 2px solid var(--dark-200);
            border-radius: var(--radius-full);
            font-size: 0.875rem;
            transition: all var(--transition);
        }
        
        .search-input:focus {
            border-color: var(--primary);
            outline: none;
        }
        
        .search-icon {
            position: absolute;
            left: var(--space-4);
            top: 50%;
            transform: translateY(-50%);
            color: var(--dark-400);
        }
        
        /* Header Actions */
        .header-actions {
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }
        
        .lang-toggle {
            padding: var(--space-2) var(--space-3);
            background: var(--dark-50);
            border-radius: var(--radius);
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        /* Navigation */
        .main-nav {
            background: #fff;
            border-top: 1px solid var(--dark-100);
            overflow-x: auto;
        }
        
        .nav-list {
            display: flex;
            gap: var(--space-1);
            padding: var(--space-2) 0;
        }
        
        .nav-item {
            position: relative;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-2) var(--space-4);
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--dark-700);
            border-radius: var(--radius);
            white-space: nowrap;
            transition: all var(--transition);
        }
        
        .nav-link:hover, .nav-link.active {
            background: var(--primary);
            color: #fff;
        }
        
        .nav-link svg {
            width: 16px;
            height: 16px;
        }
        
        /* Live Bar */
        .live-bar {
            background: linear-gradient(90deg, var(--primary), var(--primary-600));
            color: #fff;
            padding: var(--space-2) 0;
            font-size: 0.875rem;
        }
        
        .live-bar-content {
            display: flex;
            align-items: center;
            gap: var(--space-4);
        }
        
        .live-badge {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-1) var(--space-3);
            background: rgba(255,255,255,0.2);
            border-radius: var(--radius-full);
            font-weight: 600;
            animation: pulse 2s infinite;
        }
        
        .live-dot {
            width: 8px;
            height: 8px;
            background: #fff;
            border-radius: 50%;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        /* Market Section */
        .market-section {
            background: var(--dark-900);
            padding: var(--space-6) 0;
        }
        
        .market-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: var(--space-4);
        }
        
        @media (max-width: 1024px) {
            .market-grid { grid-template-columns: repeat(3, 1fr); }
        }
        
        @media (max-width: 640px) {
            .market-grid { grid-template-columns: repeat(2, 1fr); }
        }
        
        .market-card {
            background: var(--dark-800);
            border-radius: var(--radius-lg);
            padding: var(--space-4);
            text-align: center;
        }
        
        .market-card-label {
            font-size: 0.75rem;
            color: var(--dark-400);
            margin-bottom: var(--space-1);
        }
        
        .market-card-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: var(--space-1);
        }
        
        .market-card-change {
            font-size: 0.75rem;
            padding: var(--space-1) var(--space-2);
            border-radius: var(--radius-sm);
            display: inline-block;
        }
        
        .market-card-change.up {
            background: rgba(34, 197, 94, 0.2);
            color: #4ade80;
        }
        
        .market-card-change.down {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
        }
        
        /* Quick Links Bar */
        .quick-bar {
            background: var(--dark-50);
            padding: var(--space-3) 0;
            border-bottom: 1px solid var(--dark-100);
        }
        
        .quick-links {
            display: flex;
            gap: var(--space-4);
            overflow-x: auto;
            scrollbar-width: none;
        }
        
        .quick-links::-webkit-scrollbar { display: none; }
        
        .quick-link {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-2) var(--space-3);
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--dark-600);
            border-radius: var(--radius);
            white-space: nowrap;
            transition: all var(--transition);
        }
        
        .quick-link:hover {
            background: var(--primary);
            color: #fff;
        }
        
        /* Main Content */
        .main-content {
            padding: var(--space-8) 0;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: var(--space-8);
        }
        
        @media (max-width: 1024px) {
            .content-grid { grid-template-columns: 1fr; }
        }
        
        /* Featured News */
        .featured {
            position: relative;
            border-radius: var(--radius-2xl);
            overflow: hidden;
            background: var(--dark-900);
        }
        
        .featured-image {
            width: 100%;
            height: 450px;
            object-fit: cover;
            opacity: 0.7;
        }
        
        @media (max-width: 640px) {
            .featured-image { height: 280px; }
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
        
        .featured-badge {
            display: inline-block;
            padding: var(--space-1) var(--space-3);
            background: var(--primary);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: var(--radius-full);
            margin-bottom: var(--space-3);
        }
        
        .featured-title {
            font-size: clamp(1.25rem, 3vw, 1.75rem);
            font-weight: 800;
            line-height: 1.3;
            color: #fff;
            margin-bottom: var(--space-3);
        }
        
        .featured-meta {
            display: flex;
            align-items: center;
            gap: var(--space-4);
            font-size: 0.875rem;
            color: rgba(255,255,255,0.7);
        }
        
        /* News Grid */
        .news-section {
            margin-top: var(--space-8);
        }
        
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--space-6);
            padding-bottom: var(--space-3);
            border-bottom: 2px solid var(--primary);
        }
        
        .section-title {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--dark-900);
        }
        
        .section-link {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: var(--space-1);
        }
        
        .news-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--space-6);
        }
        
        @media (max-width: 768px) {
            .news-grid { grid-template-columns: repeat(2, 1fr); }
        }
        
        @media (max-width: 480px) {
            .news-grid { grid-template-columns: 1fr; }
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
        }
        
        .news-card-image {
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
        
        .news-card-body {
            padding: var(--space-4);
        }
        
        .news-card-category {
            display: inline-block;
            padding: var(--space-1) var(--space-2);
            background: var(--primary-50);
            color: var(--primary-700);
            font-size: 0.625rem;
            font-weight: 700;
            border-radius: var(--radius-sm);
            text-transform: uppercase;
            margin-bottom: var(--space-2);
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
        
        /* Sidebar */
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: var(--space-6);
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
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--dark-900);
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }
        
        .sidebar-card-body {
            padding: var(--space-4);
        }
        
        /* Quick Links Grid */
        .quick-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--space-2);
        }
        
        .quick-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-3);
            background: var(--dark-50);
            border-radius: var(--radius-lg);
            text-align: center;
            text-decoration: none;
            transition: all var(--transition);
        }
        
        .quick-item:hover {
            background: var(--primary);
            color: #fff;
        }
        
        .quick-item-icon {
            width: 36px;
            height: 36px;
            background: var(--primary);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        
        .quick-item:hover .quick-item-icon {
            background: #fff;
            color: var(--primary);
        }
        
        .quick-item span {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--dark-700);
        }
        
        .quick-item:hover span {
            color: #fff;
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
            .footer-grid { grid-template-columns: 1fr; gap: var(--space-8); }
        }
        
        .footer-brand {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            margin-bottom: var(--space-4);
        }
        
        .footer-brand-logo {
            width: 56px;
            height: 56px;
            background: var(--primary);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 1.5rem;
        }
        
        .footer-brand h3 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
        }
        
        .footer-brand span {
            font-size: 0.75rem;
            color: var(--dark-400);
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
        
        .footer-social a {
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
        
        .footer-social a:hover {
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
        
        .footer-links a {
            font-size: 0.875rem;
            color: var(--dark-400);
            transition: color var(--transition);
        }
        
        .footer-links a:hover {
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
        .mobile-menu-btn {
            display: none;
        }
        
        @media (max-width: 768px) {
            .nav-list { display: none; }
            .mobile-menu-btn { display: flex; }
        }
    </style>
</head>
<body>
    
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-content">
                <div class="flex items-center gap-4">
                    <span><?= date('l, j F Y') ?></span>
                    <span class="text-muted">|</span>
                    <span><?= $t('शुभ प्रभात', 'Good Morning') ?></span>
                </div>
                <div class="flex items-center">
                    <a href="?lang=en" class="lang-toggle">EN</a>
                    <a href="/login.php"><?= $t('लगइन', 'Login') ?></a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Header -->
    <header class="site-header">
        <div class="header-main">
            <div class="container">
                <div class="header-grid">
                    <!-- Brand -->
                    <a href="/" class="brand">
                        <div class="brand-logo">आ</div>
                        <div class="brand-text">
                            <h1><?= $t('आकाशवाणी', 'Aakashvani') ?></h1>
                            <span><?= $t('सूचनाको खुला आकाश', 'Your Gateway to Information') ?></span>
                        </div>
                    </a>
                    
                    <!-- Search -->
                    <div class="header-search">
                        <div class="search-box">
                            <svg class="search-icon icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            <input type="search" class="search-input" placeholder="<?= $t('समाचार, जानकारी खोज्नुहोस्...', 'Search news, info...') ?>">
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="header-actions">
                        <button id="themeToggle" class="btn btn-ghost btn-icon" aria-label="Toggle theme" title="<?= $t('Dark Mode', 'Dark Mode') ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                        </button>
                        <button class="btn btn-ghost btn-icon mobile-menu-btn" aria-label="Menu">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12h16M4 6h16M4 18h16"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="main-nav">
            <div class="container">
                <div class="nav-list">
                    <a href="/" class="nav-link active">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
                        <?= $t('गृह', 'Home') ?>
                    </a>
                    <a href="/news.php" class="nav-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 0-2-2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/></svg>
                        <?= $t('समाचार', 'News') ?>
                    </a>
                    <a href="/nepali-patro.php" class="nav-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <?= $t('पात्रो', 'Calendar') ?>
                    </a>
                    <a href="/rashifal.php" class="nav-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3z"/></svg>
                        <?= $t('राशिफल', 'Horoscope') ?>
                    </a>
                    <a href="/ipo-tracker.php" class="nav-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                        <?= $t('NEPSE/IPO', 'NEPSE/IPO') ?>
                    </a>
                    <a href="/tools.php" class="nav-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                        <?= $t('टूलहरू', 'Tools') ?>
                    </a>
                    <a href="/gov-services.php" class="nav-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
                        <?= $t('सरकारी सेवा', 'Gov Services') ?>
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
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
                        <?= $t('टेन्डर', 'Tenders') ?>
                    </a>
                    <a href="/emergency.php" class="nav-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <?= $t('आपतकालीन', 'Emergency') ?>
                    </a>
                </div>
            </div>
        </nav>
    </header>
    
    <!-- Live Bar -->
    <div class="live-bar">
        <div class="container">
            <div class="live-bar-content">
                <span class="live-badge">
                    <span class="live-dot"></span>
                    LIVE
                </span>
                <span><?= $t('स्वागत छ! आकाशवाणी - नेपालको छिटो सूचना प्लेटफर्म', 'Welcome to Aakashbani - Nepal\'s fastest information platform') ?></span>
            </div>
        </div>
    </div>
    
    <!-- Market Section - LIVE DATA via JS API -->
    <section class="market-section">
        <div class="container">
            <div class="market-grid">
                <div class="market-card">
                    <div class="market-card-label">NEPSE</div>
                    <div class="market-card-value" id="nepse-value">...</div>
                    <span class="market-card-change up" id="nepse-change">...</span>
                </div>
                <div class="market-card">
                    <div class="market-card-label"><?= $t('सुन (10g)', 'Gold (10g)') ?></div>
                    <div class="market-card-value" id="gold-value">...</div>
                </div>
                <div class="market-card">
                    <div class="market-card-label">USD</div>
                    <div class="market-card-value" id="usd-value">...</div>
                </div>
                <div class="market-card">
                    <div class="market-card-label"><?= $t('पेट्रोल', 'Petrol') ?></div>
                    <div class="market-card-value" id="petrol-value">...</div>
                </div>
                <div class="market-card">
                    <div class="market-card-label"><?= $t('बिजुली', 'Electricity') ?></div>
                    <div class="market-card-value" id="electricity-value">...</div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="content-grid">
                
                <!-- Main Column -->
                <div class="main-column">
                    
                    <!-- Featured News - LIVE API -->
                    <?php if (!empty($featuredNews)): ?>
                    <a href="/news-post.php?slug=<?= urlencode($featuredNews[0]['slug'] ?? '') ?>" class="featured" id="featured-news">
                        <img src="<?= htmlspecialchars($featuredNews[0]['image'] ?? '/assets/images/placeholder.svg') ?>" alt="" class="featured-image">
                        <div class="featured-content">
                            <span class="featured-badge"><?= htmlspecialchars($featuredNews[0]['category'] ?? 'समाचार') ?></span>
                            <h2 class="featured-title"><?= htmlspecialchars($featuredNews[0]['title'] ?? '') ?></h2>
                            <div class="featured-meta">
                                <span><?= htmlspecialchars($featuredNews[0]['source_name'] ?? 'आकाशवाणी') ?></span>
                                <span><?= timeAgo($featuredNews[0]['published_at'] ?? '') ?></span>
                            </div>
                        </div>
                    </a>
                    <?php else: ?>
                    <a href="#" class="featured" id="featured-news">
                        <img src="https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=1200&h=600&fit=crop" alt="" class="featured-image">
                        <div class="featured-content">
                            <span class="featured-badge">ताजा</span>
                            <h2 class="featured-title" id="featured-title"><?= $t('आकाशवाणी - सूचनाको खुला आकाश', 'Aakashvani - Your Gateway to Information') ?></h2>
                            <div class="featured-meta">
                                <span>आकाशवाणी</span>
                                <span id="featured-time">अहिले</span>
                            </div>
                        </div>
                    </a>
                    <?php endif; ?>
                    
                    <!-- Latest News -->
                    <section class="news-section">
                        <div class="section-header">
                            <h2 class="section-title">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--primary)"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 0-2-2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/></svg>
                                <?= $t('ताजा समाचार', 'Latest News') ?>
                            </h2>
                            <a href="/news.php" class="section-link">
                                <?= $t('सबै हेर्नुहोस्', 'View All') ?>
                                <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                            </a>
                        </div>
                        
                        <div class="news-grid" id="news-grid">
                            <?php if (!empty($latestNews)): ?>
                            <?php foreach (array_slice($latestNews, 0, 6) as $news): ?>
                            <a href="/news-post.php?slug=<?= urlencode($news['slug'] ?? '') ?>" class="news-card">
                                <div class="news-card-image">
                                    <img src="<?= htmlspecialchars($news['image'] ?? '/assets/images/placeholder.svg') ?>" alt="" loading="lazy">
                                </div>
                                <div class="news-card-body">
                                    <span class="news-card-category"><?= htmlspecialchars($news['category'] ?? '') ?></span>
                                    <h3 class="news-card-title"><?= htmlspecialchars($news['title'] ?? '') ?></h3>
                                    <div class="news-card-meta">
                                        <span><?= htmlspecialchars($news['source_name'] ?? '') ?></span>
                                        <span><?= timeAgo($news['published_at'] ?? '') ?></span>
                                    </div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <!-- Skeleton loader - News loaded via API -->
                            <div class="news-card">
                                <div class="news-card-image skeleton"></div>
                                <div class="news-card-body">
                                    <span class="news-card-category skeleton" style="width:60px;height:18px"></span>
                                    <h3 class="news-card-title skeleton" style="height:20px;margin-top:8px"></h3>
                                    <div class="news-card-meta"><span class="skeleton" style="width:40px;height:12px"></span></div>
                                </div>
                            </div>
                            <div class="news-card">
                                <div class="news-card-image skeleton"></div>
                                <div class="news-card-body">
                                    <span class="news-card-category skeleton" style="width:60px;height:18px"></span>
                                    <h3 class="news-card-title skeleton" style="height:20px;margin-top:8px"></h3>
                                    <div class="news-card-meta"><span class="skeleton" style="width:40px;height:12px"></span></div>
                                </div>
                            </div>
                            <div class="news-card">
                                <div class="news-card-image skeleton"></div>
                                <div class="news-card-body">
                                    <span class="news-card-category skeleton" style="width:60px;height:18px"></span>
                                    <h3 class="news-card-title skeleton" style="height:20px;margin-top:8px"></h3>
                                    <div class="news-card-meta"><span class="skeleton" style="width:40px;height:12px"></span></div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
                
                <!-- Sidebar -->
                <aside class="sidebar">
                    
                    <!-- Quick Links -->
                    <div class="sidebar-card">
                        <div class="sidebar-card-header">
                            <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--primary)"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                            <?= $t('छिटो लिंक', 'Quick Links') ?>
                        </div>
                        <div class="sidebar-card-body">
                            <div class="quick-grid">
                                <a href="/news.php?sort=latest" class="quick-item">
                                    <div class="quick-item-icon">
                                        <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    </div>
                                    <span><?= $t('ताजा', 'Latest') ?></span>
                                </a>
                                <a href="/news.php?sort=trending" class="quick-item">
                                    <div class="quick-item-icon">
                                        <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                                    </div>
                                    <span><?= $t('ट्रेन्डिङ', 'Trending') ?></span>
                                </a>
                                <a href="/news.php?sort=popular" class="quick-item">
                                    <div class="quick-item-icon">
                                        <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                    </div>
                                    <span><?= $t('लोकप्रिय', 'Popular') ?></span>
                                </a>
                                <a href="/nepali-patro.php" class="quick-item">
                                    <div class="quick-item-icon">
                                        <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    </div>
                                    <span><?= $t('पात्रो', 'Calendar') ?></span>
                                </a>
                                <a href="/rashifal.php" class="quick-item">
                                    <div class="quick-item-icon">
                                        <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3z"/></svg>
                                    </div>
                                    <span><?= $t('राशिफल', 'Horoscope') ?></span>
                                </a>
                                <a href="/ipo-tracker.php" class="quick-item">
                                    <div class="quick-item-icon">
                                        <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                                    </div>
                                    <span><?= $t('IPO', 'IPO') ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Categories -->
                    <div class="sidebar-card">
                        <div class="sidebar-card-header">
                            <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--primary)"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                            <?= $t('वर्गीकरण', 'Categories') ?>
                        </div>
                        <div class="sidebar-card-body">
                            <div class="footer-links">
                                <a href="/news.php?category=politics"><?= $t('राजनीति', 'Politics') ?></a>
                                <a href="/news.php?category=economy"><?= $t('अर्थ', 'Economy') ?></a>
                                <a href="/news.php?category=sports"><?= $t('खेलकुद', 'Sports') ?></a>
                                <a href="/news.php?category=technology"><?= $t('प्रविधि', 'Technology') ?></a>
                                <a href="/news.php?category=entertainment"><?= $t('मनोरञ्जन', 'Entertainment') ?></a>
                                <a href="/news.php?category=international"><?= $t('विश्व', 'International') ?></a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Weather -->
                    <div class="sidebar-card">
                        <div class="sidebar-card-header">
                            <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--primary)"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/></svg>
                            <?= $t('मौसम', 'Weather') ?>
                        </div>
                        <div class="sidebar-card-body">
                            <div class="flex items-center gap-4">
                                <span class="text-4xl">☀️</span>
                                <div>
                                    <div class="text-2xl font-bold">22°C</div>
                                    <div class="text-sm text-secondary"><?= $t('काठमाडौं', 'Kathmandu') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </aside>
            </div>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Brand -->
                <div>
                    <div class="footer-brand">
                        <div class="footer-brand-logo">आ</div>
                        <div>
                            <h3><?= $t('आकाशवाणी', 'Aakashvani') ?></h3>
                            <span><?= $t('सूचनाको खुला आकाश', 'Your Gateway to Information') ?></span>
                        </div>
                    </div>
                    <p class="footer-description">
                        <?= $t('नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म। समाचार, NEPSE, IPO, पात्रो, र सरकारी सेवा सबै एकै ठाउँमा।', 'Nepal\'s most trusted information platform. News, NEPSE, IPO, Calendar, and Government services all in one place.') ?>
                    </p>
                    <div class="footer-social">
                        <a href="#" aria-label="Facebook">
                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                        </a>
                        <a href="#" aria-label="Twitter">
                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"/></svg>
                        </a>
                        <a href="#" aria-label="YouTube">
                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02" fill="#fff"/></svg>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 class="footer-title"><?= $t('छिटो लिंक', 'Quick Links') ?></h4>
                    <div class="footer-links">
                        <a href="/news.php"><?= $t('समाचार', 'News') ?></a>
                        <a href="/nepali-patro.php"><?= $t('पात्रो', 'Calendar') ?></a>
                        <a href="/rashifal.php"><?= $t('राशिफल', 'Horoscope') ?></a>
                        <a href="/ipo-tracker.php"><?= $t('IPO ट्र्याकर', 'IPO Tracker') ?></a>
                        <a href="/emergency.php"><?= $t('आपतकालीन', 'Emergency') ?></a>
                    </div>
                </div>
                
                <!-- Categories -->
                <div>
                    <h4 class="footer-title"><?= $t('वर्गीकरण', 'Categories') ?></h4>
                    <div class="footer-links">
                        <a href="/news.php?category=politics"><?= $t('राजनीति', 'Politics') ?></a>
                        <a href="/news.php?category=economy"><?= $t('अर्थ', 'Economy') ?></a>
                        <a href="/news.php?category=sports"><?= $t('खेलकुद', 'Sports') ?></a>
                        <a href="/news.php?category=technology"><?= $t('प्रविधि', 'Technology') ?></a>
                        <a href="/news.php?category=entertainment"><?= $t('मनोरञ्जन', 'Entertainment') ?></a>
                    </div>
                </div>
                
                <!-- Legal -->
                <div>
                    <h4 class="footer-title"><?= $t('कानूनी', 'Legal') ?></h4>
                    <div class="footer-links">
                        <a href="/privacy.php"><?= $t('गोपनीयता', 'Privacy') ?></a>
                        <a href="/terms.php"><?= $t('सेवा सर्त', 'Terms') ?></a>
                        <a href="/contact.php"><?= $t('सम्पर्क', 'Contact') ?></a>
                        <a href="/about.php"><?= $t('हाम्रो बारेमा', 'About') ?></a>
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
    
    <!-- LIVE DATA APIS -->
    <script>
    // Load Market Data (NEPSE, Gold, USD, Petrol)
    async function loadMarketData() {
        try {
            const resp = await fetch('/api/market-data.php?type=all');
            const data = await resp.json();
            
            // NEPSE
            if (data.nepse) {
                const n = data.nepse;
                document.getElementById('nepse-value').textContent = n.index ? n.index.toLocaleString() : '2,755.41';
                const changeText = n.change >= 0 ? '+' + n.change.toFixed(2) : n.change.toFixed(2);
                const pctText = n.changePercent >= 0 ? '+' + n.changePercent.toFixed(2) + '%' : n.changePercent.toFixed(2) + '%';
                document.getElementById('nepse-change').textContent = changeText + ' (' + pctText + ')';
                document.getElementById('nepse-change').className = 'market-card-change ' + (n.change >= 0 ? 'up' : 'down');
            }
            
            // Gold
            if (data.gold && data.gold.available) {
                const g = data.gold;
                const goldValue = g.hallmarkPerTola ? Math.round(g.hallmarkPerTola).toLocaleString() : '298,500';
                document.getElementById('gold-value').textContent = 'रु ' + goldValue;
            }
            
            // Forex (USD)
            if (data.forex && data.forex.length > 0) {
                const usd = data.forex.find(r => r.code === 'USD');
                if (usd) {
                    document.getElementById('usd-value').textContent = 'रु ' + usd.sell.toFixed(2);
                }
            }
            
            // Petrol
            if (data.petrol && data.petrol.available) {
                document.getElementById('petrol-value').textContent = 'रु ' + data.petrol.petrol;
            }
            
        } catch (e) {
            // Fallback values - show sample data
            console.log('Market API unavailable, using fallback');
        }
    }
    
    // Load News from API
    async function loadNews() {
        try {
            const resp = await fetch('/api/news-unified.php?limit=6');
            const data = await resp.json();
            
            if (data.news && data.news.length > 0) {
                const grid = document.getElementById('news-grid');
                if (grid) {
                    grid.innerHTML = data.news.slice(0, 6).map(news => `
                        <a href="/news-post.php?slug=${news.slug || news.id}" class="news-card">
                            <div class="news-card-image">
                                <img src="${news.image || '/assets/images/placeholder.svg'}" alt="" loading="lazy">
                            </div>
                            <div class="news-card-body">
                                <span class="news-card-category">${news.category || 'समाचार'}</span>
                                <h3 class="news-card-title">${news.title || ''}</h3>
                                <div class="news-card-meta">
                                    <span>${news.source || 'आकाशवाणी'}</span>
                                    <span>${timeAgo(news.published_at)}</span>
                                </div>
                            </div>
                        </a>
                    `).join('');
                }
                
                // Update featured news if exists
                if (data.news[0]) {
                    const f = data.news[0];
                    const featuredLink = document.getElementById('featured-news');
                    const featuredTitle = document.getElementById('featured-title');
                    const featuredTime = document.getElementById('featured-time');
                    
                    if (featuredTitle) featuredTitle.textContent = f.title || '';
                    if (featuredTime) featuredTime.textContent = timeAgo(f.published_at);
                    if (featuredLink && f.slug) {
                        featuredLink.href = '/news-post.php?slug=' + f.slug;
                    }
                    if (featuredLink) {
                        const img = featuredLink.querySelector('img');
                        if (img && f.image) img.src = f.image;
                    }
                }
            }
        } catch (e) {
            console.log('News API unavailable, using database content');
        }
    }
    
    // Time ago helper
    function timeAgo(dateStr) {
        if (!dateStr) return 'अहिले';
        const date = new Date(dateStr);
        const now = new Date();
        const diff = Math.floor((now - date) / 1000);
        
        if (diff < 60) return diff + 's ago';
        if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
        if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
        return date.toLocaleDateString('ne-NP');
    }
    
    // Load all data on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadMarketData();
        loadNews();
    });
    </script>
    <script src="/assets/js/app.js"></script>
    <!-- Service Worker Registration for PWA -->
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(reg => console.log('SW registered:', reg.scope))
                .catch(err => console.log('SW registration failed:', err));
        });
    }
    </script>
</body>
</html>
