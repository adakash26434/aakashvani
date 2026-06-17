<?php
/**
 * आकाशवाणी — Premium Page Template
 * Clean, reusable template for all pages
 */

namespace Aakashvani;

class PageTemplate
{
    private string $title = '';
    private string $description = '';
    private array $navItems = [];
    private string $activePage = '/';
    private bool $showMarketBar = false;
    private bool $showHero = false;
    private string $heroTitle = '';
    private string $heroSubtitle = '';
    private string $heroIcon = '';
    
    public function __construct()
    {
        $this->navItems = [
            ['path' => '/', 'label' => 'गृह'],
            ['path' => '/news.php', 'label' => 'समाचार'],
            ['path' => '/nepali-patro.php', 'label' => 'पात्रो'],
            ['path' => '/rashifal.php', 'label' => 'राशिफल'],
            ['path' => '/ipo-tracker.php', 'label' => 'NEPSE/IPO'],
            ['path' => '/tools.php', 'label' => 'टूलहरू'],
            ['path' => '/gov-services.php', 'label' => 'सरकारी'],
            ['path' => '/weather.php', 'label' => 'मौसम'],
            ['path' => '/cricket.php', 'label' => 'क्रिकेट'],
            ['path' => '/tenders.php', 'label' => 'टेन्डर'],
            ['path' => '/emergency.php', 'label' => 'आपतकालीन'],
        ];
        
        $this->activePage = $_SERVER['REQUEST_URI'] ?? '/';
    }
    
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }
    
    public function setDescription(string $desc): self
    {
        $this->description = $desc;
        return $this;
    }
    
    public function setActivePage(string $path): self
    {
        $this->activePage = $path;
        return $this;
    }
    
    public function showMarketBar(bool $show = true): self
    {
        $this->showMarketBar = $show;
        return $this;
    }
    
    public function showHero(string $title, string $subtitle = '', string $icon = ''): self
    {
        $this->showHero = true;
        $this->heroTitle = $title;
        $this->heroSubtitle = $subtitle;
        $this->heroIcon = $icon;
        return $this;
    }
    
    public function renderHeader(): string
    {
        $isNepali = ($_SESSION['lang'] ?? 'ne') !== 'en';
        $t = fn($ne) => $isNepali ? $ne : $ne;
        
        $navHtml = '';
        foreach ($this->navItems as $item) {
            $active = ($this->activePage === $item['path']) ? 'active' : '';
            $navHtml .= "<li><a href=\"{$item['path']}\" class=\"nav-link {$active}\">{$item['label']}</a></li>";
        }
        
        return <<<HTML
        <!-- TOP BAR -->
        <div class="premium-topbar">
            <div class="container">
                <div class="topbar-content">
                    <div class="topbar-left">
                        <span class="topbar-badge">✨</span>
                        <span class="topbar-date">{$this->getDate()}</span>
                    </div>
                    <div class="topbar-right">
                        <a href="?lang=en" class="lang-btn">EN / ने</a>
                        <a href="/login.php" class="login-btn">{$t('लगइन')}</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- HEADER -->
        <header class="premium-header">
            <div class="container">
                <div class="header-grid">
                    <a href="/" class="brand">
                        <div class="brand-logo"><span>आ</span></div>
                        <div class="brand-text">
                            <h1>आकाशवाणी</h1>
                            <span>{$t('सूचनाको खुला आकाश')}</span>
                        </div>
                    </a>
                    
                    <div class="header-search">
                        <div class="search-wrapper">
                            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                            </svg>
                            <input type="search" class="search-input" placeholder="{$t('खोज्नुहोस्...', 'Search...')}">
                        </div>
                    </div>
                    
                    <div class="header-actions">
                        <button class="action-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <nav class="premium-nav">
                <div class="container">
                    <ul class="nav-list">
                        {$navHtml}
                    </ul>
                </div>
            </nav>
        </header>
        HTML;
    }
    
    public function renderMarketBar(): string
    {
        if (!$this->showMarketBar) return '';
        
        return <<<HTML
        <!-- MARKET BAR -->
        <section class="premium-market" id="marketSection">
            <div class="container">
                <div class="market-grid">
                    <div class="market-card" data-type="nepse">
                        <div class="card-icon">📈</div>
                        <div class="card-label">NEPSE</div>
                        <div class="card-value" id="nepse-value">...</div>
                        <div class="card-change" id="nepse-change">...</div>
                    </div>
                    <div class="market-card" data-type="gold">
                        <div class="card-icon">🥇</div>
                        <div class="card-label">{$this->t('सुन (10g)', 'Gold')}</div>
                        <div class="card-value" id="gold-value">...</div>
                    </div>
                    <div class="market-card" data-type="forex">
                        <div class="card-icon">💵</div>
                        <div class="card-label">USD</div>
                        <div class="card-value" id="forex-value">...</div>
                    </div>
                    <div class="market-card" data-type="petrol">
                        <div class="card-icon">⛽</div>
                        <div class="card-label">{$this->t('पेट्रोल', 'Petrol')}</div>
                        <div class="card-value" id="petrol-value">...</div>
                    </div>
                    <div class="market-card" data-type="electricity">
                        <div class="card-icon">⚡</div>
                        <div class="card-label">{$this->t('बिजुली', 'Electricity')}</div>
                        <div class="card-value" id="electricity-value">...</div>
                    </div>
                </div>
            </div>
        </section>
        HTML;
    }
    
    public function renderHero(): string
    {
        if (!$this->showHero) return '';
        
        return <<<HTML
        <!-- HERO SECTION -->
        <section class="page-hero">
            <div class="container">
                <h1 class="page-title-hero">{$this->heroIcon} {$this->heroTitle}</h1>
                <p class="page-subtitle-hero">{$this->heroSubtitle}</p>
            </div>
        </section>
        HTML;
    }
    
    public function renderFooter(): string
    {
        $isNepali = ($_SESSION['lang'] ?? 'ne') !== 'en';
        $t = fn($ne) => $isNepali ? $ne : $ne;
        $year = date('Y');
        
        return <<<HTML
        <!-- FOOTER -->
        <footer class="premium-footer">
            <div class="container">
                <div class="footer-grid">
                    <div>
                        <div class="footer-brand">
                            <div class="footer-logo">आ</div>
                            <div class="footer-brand-text">
                                <h3>आकाशवाणी</h3>
                                <span>{$t('सूचनाको खुला आकाश')}</span>
                            </div>
                        </div>
                        <p class="footer-description">
                            {$t('नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म।', 'Nepal\'s most trusted information platform.')}
                        </p>
                    </div>
                    <div>
                        <h4 class="footer-title">{$t('लिंकहरू', 'Links')}</h4>
                        <ul class="footer-links">
                            <li><a href="/">{$t('गृहपृष्ठ', 'Home')}</a></li>
                            <li><a href="/news.php">{$t('समाचार', 'News')}</a></li>
                            <li><a href="/ipo-tracker.php">{$t('NEPSE/IPO')}</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="footer-title">{$t('स्रोतहरू', 'Resources')}</h4>
                        <ul class="footer-links">
                            <li><a href="/rashifal.php">{$t('राशिफल', 'Horoscope')}</a></li>
                            <li><a href="/weather.php">{$t('मौसम', 'Weather')}</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="footer-title">{$t('कानूनी', 'Legal')}</h4>
                        <ul class="footer-links">
                            <li><a href="/privacy.php">{$t('गोपनीयता', 'Privacy')}</a></li>
                            <li><a href="/terms.php">{$t('सर्तहरू', 'Terms')}</a></li>
                        </ul>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p class="footer-copyright">&copy; {$year} {$t('आकाशवाणी। सर्वाधिकार सुरक्षित।', 'Aakashvani. All rights reserved.')}</p>
                </div>
            </div>
        </footer>
        HTML;
    }
    
    public function renderHead(): string
    {
        $title = !empty($this->title) ? $this->title . ' | ' : '';
        $title .= 'आकाशवाणी';
        $desc = !empty($this->description) ? $this->description : 'नेपालको सूचना प्लेटफर्म।';
        
        return <<<HTML
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{$title}</title>
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        <meta name="description" content="{$desc}">
        <meta name="theme-color" content="#10b981">
        <link rel="manifest" href="/manifest.json">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="/assets/css/premium.css">
        HTML;
    }
    
    public function renderMarketScript(): string
    {
        if (!$this->showMarketBar) return '';
        
        return <<<JS
        <script>
        (function() {
            const MarketLoader = {
                apiUrl: '/api/market-data.php',
                init() {
                    this.loadMarketData();
                    setInterval(() => this.loadMarketData(), 5 * 60 * 1000);
                },
                async loadMarketData() {
                    try {
                        const resp = await fetch(this.apiUrl + '?type=all');
                        const data = await resp.json();
                        this.updateUI(data);
                        document.querySelectorAll('.market-card').forEach((c, i) => setTimeout(() => c.classList.add('loaded'), i * 100));
                    } catch (err) { console.warn('Market data unavailable'); }
                },
                updateUI(data) {
                    if (data.nepse) {
                        const n = data.nepse;
                        const vEl = document.getElementById('nepse-value');
                        const cEl = document.getElementById('nepse-change');
                        if (vEl && n.index) vEl.textContent = n.index.toLocaleString('en-US', {maximumFractionDigits: 2});
                        if (cEl && n.change !== undefined) {
                            const isUp = n.change >= 0;
                            cEl.innerHTML = \`<span class="change-value \${isUp ? 'up' : 'down'}">\${isUp ? '+' : ''}\${n.change.toFixed(2)} (\${isUp ? '+' : ''}\${n.changePercent.toFixed(2)}%)</span>\`;
                            cEl.className = \`card-change \${isUp ? 'up' : 'down'}\`;
                        }
                    }
                    if (data.gold && data.gold.hallmarkPerTola) {
                        const gEl = document.getElementById('gold-value');
                        if (gEl) gEl.textContent = 'रु ' + Number(data.gold.hallmarkPerTola).toLocaleString('en-US');
                    }
                    if (data.forex && data.forex.length > 0) {
                        const usd = data.forex.find(r => r.code === 'USD');
                        if (usd) {
                            const fEl = document.getElementById('forex-value');
                            if (fEl) fEl.textContent = 'रु ' + usd.sell.toFixed(2);
                        }
                    }
                    if (data.petrol && data.petrol.petrol) {
                        const pEl = document.getElementById('petrol-value');
                        if (pEl) pEl.textContent = 'रु ' + data.petrol.petrol;
                    }
                }
            };
            document.addEventListener('DOMContentLoaded', () => MarketLoader.init());
        })();
        </script>
        JS;
    }
    
    private function getDate(): string
    {
        return date('l, j F Y');
    }
    
    private function t(string $ne, string $en = ''): string
    {
        return ($_SESSION['lang'] ?? 'ne') !== 'en' ? $ne : ($en ?: $ne);
    }
}
