<?php
/**
 * आकाशवाणी - Professional Footer Component
 * Enterprise News Portal Design
 */

$lang = siteLang();
$isNepali = ($lang !== 'en');
$currentYear = date('Y');

// Footer Sections
$footerSections = [
    'quick' => [
        'title' => $isNepali ? 'छिटो लिंक' : 'Quick Links',
        'items' => [
            ['href' => '/', 'label' => $isNepali ? 'गृहपृष्ठ' : 'Home'],
            ['href' => '/news.php', 'label' => $isNepali ? 'समाचार' : 'News'],
            ['href' => '/info-hub.php', 'label' => $isNepali ? 'जानकारी' : 'Info Hub'],
            ['href' => '/search.php', 'label' => $isNepali ? 'खोज' : 'Search'],
        ]
    ],
    'services' => [
        'title' => $isNepali ? 'सेवाहरू' : 'Services',
        'items' => [
            ['href' => '/ipo-tracker.php', 'label' => 'NEPSE/IPO'],
            ['href' => '/nepali-patro.php', 'label' => $isNepali ? 'पात्रो' : 'Calendar'],
            ['href' => '/rashifal.php', 'label' => $isNepali ? 'राशिफल' : 'Rashifal'],
            ['href' => '/tools.php', 'label' => $isNepali ? 'टूल' : 'Tools'],
        ]
    ],
    'government' => [
        'title' => $isNepali ? 'सरकारी' : 'Government',
        'items' => [
            ['href' => '/gov-services.php', 'label' => $isNepali ? 'सरकारी सेवा' : 'Gov Services'],
            ['href' => '/loksewa.php', 'label' => $isNepali ? 'लोकसेवा' : 'Loksewa'],
            ['href' => '/emergency.php', 'label' => $isNepali ? 'आपतकालीन' : 'Emergency'],
            ['href' => '/notices.php', 'label' => $isNepali ? 'सूचनाहरू' : 'Notices'],
        ]
    ],
    'company' => [
        'title' => $isNepali ? 'कम्पनी' : 'Company',
        'items' => [
            ['href' => '/about.php', 'label' => $isNepali ? 'हाम्रो बारे' : 'About'],
            ['href' => '/contact.php', 'label' => $isNepali ? 'सम्पर्क' : 'Contact'],
            ['href' => '/privacy.php', 'label' => $isNepali ? 'गोपनीयता' : 'Privacy'],
            ['href' => '/terms.php', 'label' => $isNepali ? 'सेवा सर्त' : 'Terms'],
        ]
    ],
];

$socialLinks = [
    ['href' => 'https://facebook.com', 'icon' => 'facebook', 'label' => 'Facebook'],
    ['href' => 'https://twitter.com', 'icon' => 'twitter', 'label' => 'Twitter'],
    ['href' => 'https://instagram.com', 'icon' => 'instagram', 'label' => 'Instagram'],
    ['href' => 'https://youtube.com', 'icon' => 'youtube', 'label' => 'YouTube'],
];

// Category colors
$categoryColors = [
    'politics' => '#ef4444',
    'economy' => '#10b981',
    'sports' => '#f59e0b',
    'technology' => '#3b82f6',
    'entertainment' => '#8b5cf6',
    'international' => '#06b6d4',
];
?>

        </article><!-- .content-area -->
    </div><!-- .content-grid -->
</main><!-- .main-content -->

<!-- ─── FOOTER ─────────────────────────────────────────────── -->
<footer class="main-footer">
    
    <!-- Footer Main -->
    <div class="footer-main">
        <div class="container">
            <div class="footer-grid">
                
                <!-- Brand -->
                <div class="footer-brand">
                    <a href="/" class="footer-logo">
                        <div class="footer-logo-icon">
                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                                <rect width="48" height="48" rx="12" fill="url(#fl)"/>
                                <path d="M12 33V15L24 9L36 15V33L24 39L12 33Z" stroke="white" stroke-width="2.5" stroke-linejoin="round"/>
                                <path d="M24 21V27M24 33V36" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                                <circle cx="24" cy="16" r="3" fill="white"/>
                                <defs>
                                    <linearGradient id="fl" x1="0" y1="0" x2="48" y2="48">
                                        <stop stop-color="#10B981"/>
                                        <stop offset="1" stop-color="#059669"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                        <div class="footer-logo-text">
                            <span class="footer-logo-title">आकाशवाणी</span>
                            <span class="footer-logo-tagline">सूचनाको खुला आकाश</span>
                        </div>
                    </a>
                    
                    <p class="footer-about">
                        <?= $isNepali 
                            ? 'नेपालको सबैभन्दा विश्वसनीय र छिटो सूचना प्लेटफर्म। AI समाचार, NEPSE, IPO, पात्रो, र सरकारी सेवा सबै एकै ठाउँमा।'
                            : "Nepal's most reliable and fastest information platform. AI News, NEPSE, IPO, Calendar, and Government Services all in one place."
                        ?>
                    </p>
                    
                    <!-- Social -->
                    <div class="footer-social">
                        <?php foreach ($socialLinks as $social): ?>
                            <a href="<?= $social['href'] ?>" class="social-link" target="_blank" rel="noopener noreferrer" title="<?= $social['label'] ?>">
                                <i data-lucide="<?= $social['icon'] ?>"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Nav Columns -->
                <?php foreach ($footerSections as $section): ?>
                    <div class="footer-nav">
                        <h3 class="footer-nav-title"><?= $section['title'] ?></h3>
                        <ul class="footer-nav-list">
                            <?php foreach ($section['items'] as $item): ?>
                                <li>
                                    <a href="<?= $item['href'] ?>" class="footer-nav-link">
                                        <?= $item['label'] ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
                
            </div>
        </div>
    </div>
    
    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-inner">
                <p class="footer-copyright">
                    &copy; <?= $currentYear ?> आकाशवाणी. <?= $isNepali ? 'सबै अधिकार सुरक्षित।' : 'All rights reserved.' ?>
                </p>
                <div class="footer-bottom-links">
                    <a href="/privacy.php"><?= $isNepali ? 'गोपनीयता नीति' : 'Privacy Policy' ?></a>
                    <span class="footer-sep">|</span>
                    <a href="/terms.php"><?= $isNepali ? 'सेवा सर्त' : 'Terms of Service' ?></a>
                    <span class="footer-sep">|</span>
                    <a href="/sitemap.php">Sitemap</a>
                </div>
            </div>
        </div>
    </div>
    
</footer>

<!-- Back to Top Button (in case not created in header) -->
<button class="back-to-top" id="backToTopBtn" title="Back to top" style="display:none;">
    <i data-lucide="chevron-up"></i>
</button>

</body>
</html>

<script>
// Re-initialize icons for any new elements
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}

// Ensure back to top button works
var btt = document.getElementById('backToTopBtn');
if (!btt) {
    btt = document.getElementById('backToTop');
}
if (btt) {
    btt.style.display = 'flex';
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            btt.classList.add('visible');
        } else {
            btt.classList.remove('visible');
        }
    });
    btt.addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}
</script>

<style>
/* ─── FOOTER STYLES ─────────────────────────────────────── */
.main-footer {
    background: #0f172a;
    color: #e2e8f0;
    margin-top: 48px;
}

/* Main Footer */
.footer-main {
    padding: 64px 0 48px;
}

.footer-grid {
    display: grid;
    grid-template-columns: 2fr repeat(4, 1fr);
    gap: 48px;
}

@media (max-width: 1200px) {
    .footer-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 32px;
    }
    .footer-brand {
        grid-column: 1 / -1;
    }
}

@media (max-width: 768px) {
    .footer-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
    }
    .footer-main {
        padding: 40px 0 32px;
    }
}

/* Brand */
.footer-brand {
    max-width: 360px;
}

.footer-logo {
    display: flex;
    align-items: center;
    gap: 14px;
    text-decoration: none;
    margin-bottom: 20px;
}

.footer-logo-icon {
    width: 48px;
    height: 48px;
}

.footer-logo-text {
    display: flex;
    flex-direction: column;
}

.footer-logo-title {
    font-size: 22px;
    font-weight: 800;
    color: #fff;
    line-height: 1.2;
}

.footer-logo-tagline {
    font-size: 12px;
    color: #94a3b8;
    margin-top: 2px;
}

.footer-about {
    font-size: 14px;
    line-height: 1.7;
    color: #94a3b8;
    margin-bottom: 24px;
}

/* Social */
.footer-social {
    display: flex;
    gap: 12px;
}

.social-link {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #1e293b;
    color: #94a3b8;
    border-radius: 12px;
    transition: all 0.2s;
}

.social-link:hover {
    background: #10b981;
    color: #fff;
    transform: translateY(-2px);
}

.social-link i {
    width: 20px;
    height: 20px;
}

/* Nav */
.footer-nav-title {
    font-size: 13px;
    font-weight: 700;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #10b981;
}

.footer-nav-list {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.footer-nav-link {
    font-size: 14px;
    color: #94a3b8;
    text-decoration: none;
    transition: all 0.2s;
    display: inline-block;
}

.footer-nav-link:hover {
    color: #fff;
    transform: translateX(4px);
}

/* Bottom */
.footer-bottom {
    border-top: 1px solid #1e293b;
    padding: 24px 0;
}

.footer-bottom-inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.footer-copyright {
    font-size: 13px;
    color: #64748b;
    margin: 0;
}

.footer-bottom-links {
    display: flex;
    align-items: center;
    gap: 12px;
}

.footer-bottom-links a {
    font-size: 13px;
    color: #64748b;
    text-decoration: none;
    transition: color 0.2s;
}

.footer-bottom-links a:hover {
    color: #fff;
}

.footer-sep {
    color: #475569;
}

/* Back to Top (already defined in header, ensure visibility) */
.back-to-top {
    position: fixed;
    bottom: 32px;
    right: 32px;
    width: 48px;
    height: 48px;
    background: #10b981;
    color: #fff;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: all 0.3s;
    box-shadow: 0 4px 20px rgba(16,185,129,0.4);
    z-index: 1000;
}

.back-to-top.visible {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.back-to-top:hover {
    background: #059669;
    transform: translateY(-2px);
}

.back-to-top i {
    width: 24px;
    height: 24px;
}
</style>
