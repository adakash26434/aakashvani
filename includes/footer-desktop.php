<?php
/**
 * आकाशवाणी - Desktop Footer Component
 * Professional News Portal Footer
 */

$lang = $_COOKIE['site_lang'] ?? 'ne';
$isNepali = ($lang !== 'en');

$getLabel = fn($item) => $isNepali ? $item['ne'] : $item['en'];

// Footer Navigation
$footerSections = [
    'quick_links' => [
        'title' => $isNepali ? 'छिटो लिंक' : 'Quick Links',
        'items' => [
            ['href' => '/', 'ne' => 'गृहपृष्ठ', 'en' => 'Home'],
            ['href' => '/news.php', 'ne' => 'समाचार', 'en' => 'News'],
            ['href' => '/info-hub.php', 'ne' => 'जानकारी', 'en' => 'Info Hub'],
            ['href' => '/search.php', 'ne' => 'खोज', 'en' => 'Search'],
        ]
    ],
    'services' => [
        'title' => $isNepali ? 'सेवाहरू' : 'Services',
        'items' => [
            ['href' => '/ipo-tracker.php', 'ne' => 'IPO/NEPSE', 'en' => 'IPO/NEPSE'],
            ['href' => '/nepali-patro.php', 'ne' => 'पात्रो', 'en' => 'Calendar'],
            ['href' => '/rashifal.php', 'ne' => 'राशिफल', 'en' => 'Rashifal'],
            ['href' => '/tools.php', 'ne' => 'टुलहरू', 'en' => 'Tools'],
        ]
    ],
    'government' => [
        'title' => $isNepali ? 'सरकारी' : 'Government',
        'items' => [
            ['href' => '/gov-services.php', 'ne' => 'सरकारी सेवा', 'en' => 'Gov Services'],
            ['href' => '/loksewa.php', 'ne' => 'लोकसेवा', 'en' => 'Loksewa'],
            ['href' => '/emergency.php', 'ne' => 'आपतकालीन', 'en' => 'Emergency'],
            ['href' => '/notices.php', 'ne' => 'सूचनाहरू', 'en' => 'Notices'],
        ]
    ],
    'company' => [
        'title' => $isNepali ? 'कम्पनी' : 'Company',
        'items' => [
            ['href' => '/about.php', 'ne' => 'हाम्रो बारे', 'en' => 'About'],
            ['href' => '/contact.php', 'ne' => 'सम्पर्क', 'en' => 'Contact'],
            ['href' => '/privacy.php', 'ne' => 'गोपनीयता', 'en' => 'Privacy'],
            ['href' => '/terms.php', 'ne' => 'सेवा सर्त', 'en' => 'Terms'],
        ]
    ],
];

$socialLinks = [
    ['href' => 'https://facebook.com', 'icon' => 'facebook', 'label' => 'Facebook'],
    ['href' => 'https://twitter.com', 'icon' => 'twitter', 'label' => 'Twitter'],
    ['href' => 'https://instagram.com', 'icon' => 'instagram', 'label' => 'Instagram'],
    ['href' => 'https://youtube.com', 'icon' => 'youtube', 'label' => 'YouTube'],
];

$currentYear = date('Y');
?>
        </main><!-- .main-content-desktop -->
    </div><!-- .content-wrapper-desktop -->
    
    <!-- ═══════════════════════════════════════════════════════════════════════════
         FOOTER - Professional Desktop Footer
         ═══════════════════════════════════════════════════════════════════════════ -->
    <footer class="site-footer-desktop">
        
        <!-- Footer Main -->
        <div class="footer-main-desktop">
            <div class="footer-container">
                <div class="footer-grid-desktop">
                    
                    <!-- Brand Column -->
                    <div class="footer-brand-desktop">
                        <a href="/" class="footer-logo-desktop">
                            <div class="footer-logo-icon">
                                <svg width="48" height="48" viewBox="0 0 32 32" fill="none">
                                    <rect width="32" height="32" rx="8" fill="url(#footerLogoGrad)"/>
                                    <path d="M8 22V10L16 6L24 10V22L16 26L8 22Z" stroke="white" stroke-width="2" stroke-linejoin="round"/>
                                    <path d="M16 14V18M16 21V23" stroke="white" stroke-width="2" stroke-linecap="round"/>
                                    <circle cx="16" cy="11" r="2" fill="white"/>
                                    <defs>
                                        <linearGradient id="footerLogoGrad" x1="0" y1="0" x2="32" y2="32" gradientUnits="userSpaceOnUse">
                                            <stop stop-color="#10B981"/>
                                            <stop offset="1" stop-color="#059669"/>
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </div>
                            <div class="footer-logo-text">
                                <span class="footer-logo-title"><?= $isNepali ? 'आकाशवाणी' : 'Aakashbani' ?></span>
                                <span class="footer-logo-tagline"><?= $isNepali ? 'सूचनाको खुला आकाश' : 'Open Sky of Information' ?></span>
                            </div>
                        </a>
                        
                        <p class="footer-about-desktop">
                            <?= $isNepali 
                                ? 'नेपालको सबैभन्दा विश्वसनीय र छिटो सूचना प्लेटफर्म। AI समाचार, NEPSE, IPO, पात्रो, र सरकारी सेवा सबै एकै ठाउँमा।'
                                : 'Nepal\'s most reliable and fastest information platform. AI News, NEPSE, IPO, Calendar, and Government Services all in one place.'
                            ?>
                        </p>
                        
                        <!-- Social Links -->
                        <div class="footer-social-desktop">
                            <?php foreach ($socialLinks as $social): ?>
                                <a href="<?= $social['href'] ?>" class="social-link-desktop" target="_blank" rel="noopener noreferrer" aria-label="<?= $social['label'] ?>">
                                    <i data-lucide="<?= $social['icon'] ?>" class="social-icon-desktop"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Navigation Columns -->
                    <?php foreach ($footerSections as $section): ?>
                        <div class="footer-nav-desktop">
                            <h3 class="footer-nav-title-desktop"><?= $section['title'] ?></h3>
                            <ul class="footer-nav-list-desktop">
                                <?php foreach ($section['items'] as $item): ?>
                                    <li>
                                        <a href="<?= $item['href'] ?>" class="footer-nav-link-desktop">
                                            <?= $getLabel($item) ?>
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
        <div class="footer-bottom-desktop">
            <div class="footer-container">
                <div class="footer-bottom-inner-desktop">
                    
                    <!-- Copyright -->
                    <p class="footer-copyright-desktop">
                        &copy; <?= $currentYear ?> <?= $isNepali ? 'आकाशवाणी' : 'Aakashbani' ?>. 
                        <?= $isNepali ? 'सबै अधिकार सुरक्षित।' : 'All rights reserved.' ?>
                    </p>
                    
                    <!-- Footer Links -->
                    <div class="footer-bottom-links-desktop">
                        <a href="/privacy.php" class="footer-bottom-link-desktop">
                            <?= $isNepali ? 'गोपनीयता नीति' : 'Privacy Policy' ?>
                        </a>
                        <span class="footer-separator-desktop">•</span>
                        <a href="/terms.php" class="footer-bottom-link-desktop">
                            <?= $isNepali ? 'सेवा सर्त' : 'Terms of Service' ?>
                        </a>
                        <span class="footer-separator-desktop">•</span>
                        <a href="/sitemap.php" class="footer-bottom-link-desktop">Sitemap</a>
                    </div>
                    
                </div>
            </div>
        </div>
        
    </footer>
    
    <!-- Back to Top -->
    <button type="button" class="back-to-top-desktop" id="back-to-top-desktop" aria-label="Back to top">
        <i data-lucide="chevron-up" class="back-to-top-icon-desktop"></i>
    </button>

</body>
</html>

<script>
// Re-initialize icons
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}

// Back to Top
const backToTopBtn = document.getElementById('back-to-top-desktop');
if (backToTopBtn) {
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTopBtn.classList.add('visible');
        } else {
            backToTopBtn.classList.remove('visible');
        }
    });
    
    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}
</script>

<style>
/* ═══════════════════════════════════════════════════════════════════════════════
   FOOTER STYLES - Professional Desktop Footer
   ═══════════════════════════════════════════════════════════════════════════════ */

.site-footer-desktop {
    background: #0f172a;
    color: #e2e8f0;
    margin-top: auto;
}

/* Footer Main */
.footer-main-desktop {
    padding: 60px 0 40px;
}

.footer-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 24px;
}

.footer-grid-desktop {
    display: grid;
    grid-template-columns: 2fr repeat(4, 1fr);
    gap: 48px;
}

@media (max-width: 1200px) {
    .footer-grid-desktop {
        grid-template-columns: repeat(3, 1fr);
        gap: 32px;
    }
    
    .footer-brand-desktop {
        grid-column: 1 / -1;
        max-width: 400px;
    }
}

@media (max-width: 768px) {
    .footer-grid-desktop {
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
    }
}

/* Brand Section */
.footer-brand-desktop {
    max-width: 320px;
}

.footer-logo-desktop {
    display: flex;
    align-items: center;
    gap: 12px;
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
    font-size: 20px;
    font-weight: 800;
    color: white;
    line-height: 1.2;
}

.footer-logo-tagline {
    font-size: 12px;
    color: #94a3b8;
    margin-top: 2px;
}

.footer-about-desktop {
    font-size: 14px;
    line-height: 1.7;
    color: #94a3b8;
    margin-bottom: 24px;
}

/* Social Links */
.footer-social-desktop {
    display: flex;
    gap: 12px;
}

.social-link-desktop {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #1e293b;
    color: #94a3b8;
    border-radius: 10px;
    transition: all 0.2s;
}

.social-link-desktop:hover {
    background: #10b981;
    color: white;
    transform: translateY(-2px);
}

.social-icon-desktop {
    width: 18px;
    height: 18px;
}

/* Navigation Sections */
.footer-nav-title-desktop {
    font-size: 14px;
    font-weight: 700;
    color: white;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #10b981;
}

.footer-nav-list-desktop {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.footer-nav-link-desktop {
    font-size: 14px;
    color: #94a3b8;
    text-decoration: none;
    transition: all 0.2s;
    display: inline-block;
}

.footer-nav-link-desktop:hover {
    color: white;
    transform: translateX(4px);
}

/* Footer Bottom */
.footer-bottom-desktop {
    border-top: 1px solid #1e293b;
    padding: 24px 0;
}

.footer-bottom-inner-desktop {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.footer-copyright-desktop {
    font-size: 13px;
    color: #64748b;
    margin: 0;
}

.footer-bottom-links-desktop {
    display: flex;
    align-items: center;
    gap: 12px;
}

.footer-bottom-link-desktop {
    font-size: 13px;
    color: #64748b;
    text-decoration: none;
    transition: color 0.2s;
}

.footer-bottom-link-desktop:hover {
    color: white;
}

.footer-separator-desktop {
    color: #475569;
    font-size: 13px;
}

/* Back to Top */
.back-to-top-desktop {
    position: fixed;
    bottom: 32px;
    right: 32px;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #10b981;
    color: white;
    border-radius: 50%;
    box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: all 0.3s;
    z-index: 1000;
}

.back-to-top-desktop.visible {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.back-to-top-desktop:hover {
    background: #059669;
    transform: translateY(-2px);
}

.back-to-top-icon-desktop {
    width: 24px;
    height: 24px;
}
</style>
