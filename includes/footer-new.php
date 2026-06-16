<?php
/**
 * आकाशवाणी - Modern Footer Component (v2)
 * Professional News Portal Footer
 * Clean, Simple, Informative
 */

// Language helper
$lang = $_COOKIE['site_lang'] ?? 'ne';
$isNepali = ($lang !== 'en');

// Function to get label based on language
$getLabel = function($item) use ($isNepali) {
    return $isNepali ? $item['ne'] : $item['en'];
};

// Footer Navigation Sections
$footerSections = [
    'quick_links' => [
        'title' => $isNepali ? 'छिटो लिंकहरू' : 'Quick Links',
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

// Social Links
$socialLinks = [
    ['href' => 'https://facebook.com', 'icon' => 'facebook', 'label' => 'Facebook'],
    ['href' => 'https://twitter.com', 'icon' => 'twitter', 'label' => 'Twitter'],
    ['href' => 'https://instagram.com', 'icon' => 'instagram', 'label' => 'Instagram'],
    ['href' => 'https://youtube.com', 'icon' => 'youtube', 'label' => 'YouTube'],
];

// Current year
$currentYear = date('Y');
?>
        </div><!-- .container -->
    </main><!-- #main-content -->
    
    <!-- ═══════════════════════════════════════════════════════════════════════════════
         FOOTER - Professional News Portal Footer
         ═══════════════════════════════════════════════════════════════════════════════ -->
    <footer class="site-footer">
        
        <!-- Footer Main -->
        <div class="footer-main">
            <div class="container">
                <div class="footer-grid">
                    
                    <!-- Brand Column -->
                    <div class="footer-brand">
                        <a href="/" class="footer-logo">
                            <div class="footer-logo-icon">
                                <svg width="40" height="40" viewBox="0 0 32 32" fill="none">
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
                        
                        <p class="footer-about">
                            <?= $isNepali 
                                ? 'नेपालको सबैभन्दा विश्वसनीय र छिटो सूचना प्लेटफर्म। AI समाचार, NEPSE, IPO, पात्रो, राशिफल, र सरकारी सेवा सबै एकै ठाउँमा।'
                                : 'Nepal\'s most reliable and fastest information platform. AI News, NEPSE, IPO, Calendar, Rashifal, and Government Services all in one place.'
                            ?>
                        </p>
                        
                        <!-- Social Links -->
                        <div class="footer-social">
                            <?php foreach ($socialLinks as $social): ?>
                                <a href="<?= $social['href'] ?>" class="social-link" target="_blank" rel="noopener noreferrer" aria-label="<?= $social['label'] ?>">
                                    <i data-lucide="<?= $social['icon'] ?>" class="social-icon"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Navigation Columns -->
                    <?php foreach ($footerSections as $section): ?>
                        <div class="footer-nav">
                            <h3 class="footer-nav-title"><?= $section['title'] ?></h3>
                            <ul class="footer-nav-list">
                                <?php foreach ($section['items'] as $item): ?>
                                    <li>
                                        <a href="<?= $item['href'] ?>" class="footer-nav-link">
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
        <div class="footer-bottom">
            <div class="container">
                <div class="footer-bottom-inner">
                    
                    <!-- Copyright -->
                    <p class="footer-copyright">
                        &copy; <?= $currentYear ?> <?= $isNepali ? 'आकाशवाणी' : 'Aakashbani' ?>. 
                        <?= $isNepali ? 'सबै अधिकार सुरक्षित।' : 'All rights reserved.' ?>
                    </p>
                    
                    <!-- Footer Links -->
                    <div class="footer-bottom-links">
                        <a href="/privacy.php" class="footer-bottom-link">
                            <?= $isNepali ? 'गोपनीयता नीति' : 'Privacy Policy' ?>
                        </a>
                        <span class="footer-separator">•</span>
                        <a href="/terms.php" class="footer-bottom-link">
                            <?= $isNepali ? 'सेवा सर्त' : 'Terms of Service' ?>
                        </a>
                        <span class="footer-separator">•</span>
                        <a href="/sitemap.php" class="footer-bottom-link">
                            <?= $isNepali ? 'Sitemap' : 'Sitemap' ?>
                        </a>
                    </div>
                    
                </div>
            </div>
        </div>
        
    </footer>
    
    <!-- Back to Top Button -->
    <button type="button" class="back-to-top" id="back-to-top" aria-label="Back to top">
        <i data-lucide="chevron-up" class="back-to-top-icon"></i>
    </button>
    
    <!-- Toast Container -->
    <div class="toast-container" id="toast-container"></div>

<script>
// Re-initialize Lucide Icons for dynamically added elements
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}

// Back to Top Button
const backToTopBtn = document.getElementById('back-to-top');
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

// Simple Toast System
const toastContainer = document.getElementById('toast-container');

function showToast(message, type = 'info', duration = 4000) {
    if (!toastContainer) return;
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-icon">
            <i data-lucide="${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'info'}" class="icon-lg"></i>
        </div>
        <div class="toast-content">
            <p class="toast-message">${message}</p>
        </div>
        <button type="button" class="toast-close" onclick="this.parentElement.remove()">
            <i data-lucide="x" class="icon-sm"></i>
        </button>
    `;
    
    toastContainer.appendChild(toast);
    lucide.createIcons();
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// Expose showToast globally
window.showToast = showToast;
</script>

<style>
/* ═══════════════════════════════════════════════════════════════════════════════
   FOOTER STYLES - Modern News Portal Footer
   ═══════════════════════════════════════════════════════════════════════════════ */

/* Footer Base */
.site-footer {
    background: var(--slate-900);
    color: var(--slate-300);
    margin-top: auto;
}

/* Footer Main */
.footer-main {
    padding: var(--space-12) 0 var(--space-8);
}

.footer-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-8);
}

@media (min-width: 640px) {
    .footer-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .footer-grid {
        grid-template-columns: 2fr repeat(4, 1fr);
    }
}

/* Brand Section */
.footer-brand {
    max-width: 320px;
}

@media (min-width: 1024px) {
    .footer-brand {
        max-width: none;
    }
}

.footer-logo {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    text-decoration: none;
    margin-bottom: var(--space-4);
}

.footer-logo-icon {
    width: 40px;
    height: 40px;
}

.footer-logo-text {
    display: flex;
    flex-direction: column;
}

.footer-logo-title {
    font-size: var(--text-lg);
    font-weight: var(--font-bold);
    color: white;
    line-height: 1.2;
}

.footer-logo-tagline {
    font-size: var(--text-xs);
    color: var(--slate-400);
}

.footer-about {
    font-size: var(--text-sm);
    line-height: var(--leading-relaxed);
    color: var(--slate-400);
    margin-bottom: var(--space-4);
}

/* Social Links */
.footer-social {
    display: flex;
    gap: var(--space-2);
}

.social-link {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--slate-800);
    color: var(--slate-400);
    border-radius: var(--radius-lg);
    transition: all var(--transition-fast);
}

.social-link:hover {
    background: var(--brand-500);
    color: white;
    transform: translateY(-2px);
}

.social-icon {
    width: 18px;
    height: 18px;
}

/* Navigation Sections */
.footer-nav-title {
    font-size: var(--text-sm);
    font-weight: var(--font-bold);
    color: white;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: var(--space-4);
}

.footer-nav-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.footer-nav-link {
    font-size: var(--text-sm);
    color: var(--slate-400);
    transition: all var(--transition-fast);
    display: inline-block;
}

.footer-nav-link:hover {
    color: white;
    transform: translateX(4px);
}

/* Footer Bottom */
.footer-bottom {
    border-top: 1px solid var(--slate-800);
    padding: var(--space-4) 0;
}

.footer-bottom-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--space-3);
    text-align: center;
}

@media (min-width: 768px) {
    .footer-bottom-inner {
        flex-direction: row;
        justify-content: space-between;
        text-align: left;
    }
}

.footer-copyright {
    font-size: var(--text-xs);
    color: var(--slate-500);
    margin: 0;
}

.footer-bottom-links {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    flex-wrap: wrap;
    justify-content: center;
}

.footer-bottom-link {
    font-size: var(--text-xs);
    color: var(--slate-500);
    transition: color var(--transition-fast);
}

.footer-bottom-link:hover {
    color: white;
}

.footer-separator {
    color: var(--slate-600);
    font-size: var(--text-xs);
}

/* Back to Top Button */
.back-to-top {
    position: fixed;
    bottom: var(--space-6);
    right: var(--space-4);
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--brand-500);
    color: white;
    border-radius: var(--radius-full);
    box-shadow: var(--shadow-lg);
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: all var(--transition-base);
    z-index: var(--z-fixed);
}

.back-to-top.visible {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.back-to-top:hover {
    background: var(--brand-600);
    transform: translateY(-2px);
}

.back-to-top-icon {
    width: 20px;
    height: 20px;
}

/* Toast Container */
.toast-container {
    position: fixed;
    bottom: var(--space-4);
    left: 50%;
    transform: translateX(-50%);
    z-index: var(--z-toast);
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
    width: calc(100% - var(--space-8));
    max-width: 400px;
    pointer-events: none;
}

.toast {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-3) var(--space-4);
    background: var(--bg-card);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-xl);
    border-left: 4px solid;
    pointer-events: auto;
    opacity: 0;
    transform: translateY(10px);
    transition: all var(--transition-base);
}

.toast.show {
    opacity: 1;
    transform: translateY(0);
}

.toast-success { border-color: var(--success-500); }
.toast-error { border-color: var(--error-500); }
.toast-warning { border-color: var(--warning-500); }
.toast-info { border-color: var(--info-500); }

.toast-icon {
    flex-shrink: 0;
    color: var(--text-tertiary);
}

.toast-message {
    font-size: var(--text-sm);
    color: var(--text-primary);
    margin: 0;
}

.toast-close {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    color: var(--text-tertiary);
    cursor: pointer;
    border-radius: var(--radius-sm);
    margin-left: auto;
}

.toast-close:hover {
    background: var(--slate-100);
    color: var(--text-primary);
}

/* Footer Dark Mode Adjustments */
@media (prefers-color-scheme: dark) {
    .site-footer {
        background: #0a0a0f;
    }
}
</style>

</body>
</html>
