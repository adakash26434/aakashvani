<?php
/**
 * आकाशवाणी — Premium Header Component
 * Sophisticated glass-morphism header with animations
 */

namespace Aakashvani\Components;

class Header
{
    private array $navItems = [];
    private string $currentPath = '/';

    public function __construct()
    {
        $this->navItems = [
            ['path' => '/', 'label' => 'गृह', 'label_en' => 'Home', 'icon' => 'home'],
            ['path' => '/news.php', 'label' => 'समाचार', 'label_en' => 'News', 'icon' => 'newspaper'],
            ['path' => '/nepali-patro.php', 'label' => 'पात्रो', 'label_en' => 'Calendar', 'icon' => 'calendar'],
            ['path' => '/rashifal.php', 'label' => 'राशिफल', 'label_en' => 'Horoscope', 'icon' => 'star'],
            ['path' => '/ipo-tracker.php', 'label' => 'NEPSE/IPO', 'label_en' => 'NEPSE/IPO', 'icon' => 'chart'],
            ['path' => '/tools.php', 'label' => 'टूलहरू', 'label_en' => 'Tools', 'icon' => 'tool'],
            ['path' => '/gov-services.php', 'label' => 'सरकारी', 'label_en' => 'Gov', 'icon' => 'building'],
            ['path' => '/weather.php', 'label' => 'मौसम', 'label_en' => 'Weather', 'icon' => 'cloud'],
            ['path' => '/cricket.php', 'label' => 'क्रिकेट', 'label_en' => 'Cricket', 'icon' => 'circle'],
            ['path' => '/tenders.php', 'label' => 'टेन्डर', 'label_en' => 'Tenders', 'icon' => 'file'],
            ['path' => '/emergency.php', 'label' => 'आपतकालीन', 'label_en' => 'Emergency', 'icon' => 'phone'],
        ];
        
        $this->currentPath = $_SERVER['REQUEST_URI'] ?? '/';
    }

    public function render(): string
    {
        $isNepali = ($_SESSION['lang'] ?? 'ne') !== 'en';
        $t = fn($ne, $en) => $isNepali ? $ne : $en;
        
        return $this->buildHtml($t);
    }

    private function buildHtml(callable $t): string
    {
        $today = date('l, j F Y');
        $greeting = $this->getGreeting();
        
        $navHtml = $this->buildNav($t);
        
        return <<<HTML
        <!-- Premium Top Bar -->
        <div class="premium-topbar">
            <div class="container">
                <div class="topbar-content">
                    <div class="topbar-left">
                        <span class="topbar-badge">✨</span>
                        <span class="topbar-date">{$today}</span>
                        <span class="topbar-divider">|</span>
                        <span class="topbar-greeting">{$greeting}</span>
                    </div>
                    <div class="topbar-right">
                        <a href="?lang=en" class="lang-btn">EN / ने</a>
                        <a href="/login.php" class="login-btn">{$t('लगइन', 'Login')}</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Premium Header -->
        <header class="premium-header">
            <div class="container">
                <div class="header-grid">
                    <!-- Brand -->
                    <a href="/" class="brand">
                        <div class="brand-logo">
                            <span>आ</span>
                        </div>
                        <div class="brand-text">
                            <h1>आकाशवाणी</h1>
                            <span>{$t('सूचनाको खुला आकाश', 'Your Gateway to Information')}</span>
                        </div>
                    </a>
                    
                    <!-- Premium Search -->
                    <div class="header-search">
                        <div class="search-wrapper">
                            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.3-4.3"/>
                            </svg>
                            <input 
                                type="search" 
                                class="search-input" 
                                placeholder="{$t('समाचार, जानकारी खोज्नुहोस्...', 'Search news, info...')}"
                                id="headerSearch"
                            >
                            <kbd class="search-kbd">⌘K</kbd>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="header-actions">
                        <button class="action-btn" id="themeToggle" title="{$t('Dark Mode', 'Dark Mode')}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                            </svg>
                        </button>
                        <button class="action-btn mobile-menu-toggle" id="mobileMenuToggle">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 12h16M4 6h16M4 18h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="premium-nav">
                <div class="container">
                    <ul class="nav-list">
                        {$navHtml}
                    </ul>
                </div>
            </nav>
        </header>
        
        <!-- Live Banner -->
        <div class="live-banner">
            <div class="container">
                <div class="banner-content">
                    <span class="live-badge">
                        <span class="live-dot"></span>
                        LIVE
                    </span>
                    <span class="banner-text">
                        {$t('स्वागत छ! आकाशवाणी - नेपालको छिटो सूचना प्लेटफर्म', 'Welcome to Aakashbani - Nepal\'s fastest information platform')}
                    </span>
                </div>
            </div>
        </div>
        HTML;
    }

    private function buildNav(callable $t): string
    {
        $html = '';
        
        foreach ($this->navItems as $item) {
            $isActive = $this->currentPath === $item['path'] ? 'active' : '';
            $icon = $this->getIcon($item['icon']);
            $label = $t($item['label'], $item['label_en']);
            
            $html .= <<<ITEM
                        <li>
                            <a href="{$item['path']}" class="nav-link {$isActive}">
                                {$icon}
                                <span>{$label}</span>
                            </a>
                        </li>
            ITEM;
        }
        
        return $html;
    }

    private function getIcon(string $name): string
    {
        $icons = [
            'home' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>',
            'newspaper' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 0-2-2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/></svg>',
            'calendar' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
            'star' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3z"/></svg>',
            'chart' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>',
            'tool' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>',
            'building' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>',
            'cloud' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/></svg>',
            'circle' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>',
            'file' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>',
            'phone' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
        ];
        
        return $icons[$name] ?? '';
    }

    private function getGreeting(): string
    {
        $hour = (int)date('H');
        
        if ($hour < 12) {
            return '🌅 ' . ($_SESSION['lang'] ?? 'ne') === 'en' ? 'Good Morning' : 'शुभ प्रभात';
        } elseif ($hour < 17) {
            return '☀️ ' . ($_SESSION['lang'] ?? 'ne') === 'en' ? 'Good Afternoon' : 'शुभ दिउँसो';
        } elseif ($hour < 21) {
            return '🌆 ' . ($_SESSION['lang'] ?? 'ne') === 'en' ? 'Good Evening' : 'शुभ साँझ';
        } else {
            return '🌙 ' . ($_SESSION['lang'] ?? 'ne') === 'en' ? 'Good Night' : 'शुभ रात्रि';
        }
    }
}
