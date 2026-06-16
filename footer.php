<?php
/**
 * आकाशवाणी — footer.php (World-Class Footer)
 * Premium, clean, professional footer design
 */

$lang = function_exists('siteLang') ? siteLang() : 'ne';
$isNepali = ($lang !== 'en');
$currentYear = date('Y');
?>

<!-- Footer -->
<footer class="site-footer">
    <div class="container">
        
        <!-- Main Footer -->
        <div class="footer-main">
            
            <!-- About -->
            <div class="footer-col">
                <div class="footer-brand">
                    <h3 class="footer-logo"><?= $isNepali ? 'आकाशवाणी' : 'Aakashvani' ?></h3>
                    <p class="footer-tagline"><?= $isNepali ? 'सूचनाको खुला आकाश' : 'Your Gateway to Information' ?></p>
                </div>
                <p class="footer-about">
                    <?= $isNepali 
                        ? 'नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म। समाचार, NEPSE, IPO, पात्रो, र सरकारी सेवा सबै एकै ठाउँमा।'
                        : 'Nepal\'s most trusted information platform. News, NEPSE, IPO, Calendar, and Government services all in one place.'
                    ?>
                </p>
            </div>
            
            <!-- Quick Links -->
            <div class="footer-col">
                <h4 class="footer-heading"><?= $isNepali ? 'छिटो लिंक' : 'Quick Links' ?></h4>
                <ul class="footer-links">
                    <li><a href="/news.php"><?= $isNepali ? 'समाचार' : 'News' ?></a></li>
                    <li><a href="/nepali-patro.php"><?= $isNepali ? 'पात्रो' : 'Calendar' ?></a></li>
                    <li><a href="/rashifal.php"><?= $isNepali ? 'राशिफल' : 'Rashifal' ?></a></li>
                    <li><a href="/ipo-tracker.php"><?= $isNepali ? 'IPO ट्र्याकर' : 'IPO Tracker' ?></a></li>
                    <li><a href="/gov-services.php"><?= $isNepali ? 'सरकारी सेवा' : 'Gov Services' ?></a></li>
                    <li><a href="/emergency.php"><?= $isNepali ? 'आपतकालीन' : 'Emergency' ?></a></li>
                </ul>
            </div>
            
            <!-- Categories -->
            <div class="footer-col">
                <h4 class="footer-heading"><?= $isNepali ? 'वर्गीकरण' : 'Categories' ?></h4>
                <ul class="footer-links">
                    <li><a href="/news.php?category=politics"><?= $isNepali ? 'राजनीति' : 'Politics' ?></a></li>
                    <li><a href="/news.php?category=economy"><?= $isNepali ? 'अर्थ' : 'Economy' ?></a></li>
                    <li><a href="/news.php?category=sports"><?= $isNepali ? 'खेलकुद' : 'Sports' ?></a></li>
                    <li><a href="/news.php?category=technology"><?= $isNepali ? 'प्रविधि' : 'Technology' ?></a></li>
                    <li><a href="/news.php?category=international"><?= $isNepali ? 'विश्व' : 'International' ?></a></li>
                    <li><a href="/news.php?category=entertainment"><?= $isNepali ? 'मनोरञ्जन' : 'Entertainment' ?></a></li>
                </ul>
            </div>
            
            <!-- Connect -->
            <div class="footer-col">
                <h4 class="footer-heading"><?= $isNepali ? 'जडान' : 'Connect' ?></h4>
                <div class="footer-social">
                    <a href="#" class="social-link" aria-label="Facebook">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                    </a>
                    <a href="#" class="social-link" aria-label="Twitter">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"></path></svg>
                    </a>
                    <a href="#" class="social-link" aria-label="YouTube">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon></svg>
                    </a>
                    <a href="#" class="social-link" aria-label="Instagram">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                    </a>
                </div>
                <div class="footer-contact">
                    <p><?= $isNepali ? 'इमेल:' : 'Email:' ?> info@aakashvani.com</p>
                </div>
            </div>
            
        </div>
        
        <!-- Bottom Bar -->
        <div class="footer-bottom">
            <p class="copyright">
                &copy; <?= $currentYear ?> <?= $isNepali ? 'आकाशवाणी।' : 'Aakashvani.' ?> <?= $isNepali ? 'सर्वाधिकार सुरक्षित।' : 'All rights reserved.' ?>
            </p>
            <div class="footer-legal">
                <a href="/privacy.php"><?= $isNepali ? 'गोपनीयता' : 'Privacy' ?></a>
                <span class="divider">|</span>
                <a href="/terms.php"><?= $isNepali ? 'सेवा सर्त' : 'Terms' ?></a>
                <span class="divider">|</span>
                <a href="/contact.php"><?= $isNepali ? 'सम्पर्क' : 'Contact' ?></a>
            </div>
        </div>
        
    </div>
</footer>

<style>
/* ═══════════════════════════════════════════════════════════════
   WORLD-CLASS FOOTER STYLES
   ═══════════════════════════════════════════════════════════════ */

.site-footer {
    background: #0f172a;
    color: #94a3b8;
    padding: 60px 0 0;
    margin-top: 48px;
}

.footer-main {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 48px;
    padding-bottom: 48px;
    border-bottom: 1px solid #1e293b;
}

@media (max-width: 1024px) {
    .footer-main {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 640px) {
    .footer-main {
        grid-template-columns: 1fr;
        gap: 32px;
    }
}

.footer-col {
    display: flex;
    flex-direction: column;
}

.footer-brand {
    margin-bottom: 16px;
}

.footer-logo {
    font-size: 24px;
    font-weight: 800;
    color: #fff;
    margin-bottom: 4px;
}

.footer-tagline {
    font-size: 14px;
    color: #10b981;
    font-weight: 500;
}

.footer-about {
    font-size: 14px;
    line-height: 1.7;
    color: #94a3b8;
}

.footer-heading {
    font-size: 14px;
    font-weight: 700;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #10b981;
}

.footer-links {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.footer-links a {
    font-size: 14px;
    color: #94a3b8;
    text-decoration: none;
    transition: color 0.2s;
}

.footer-links a:hover {
    color: #10b981;
}

.footer-social {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
}

.social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: #1e293b;
    border-radius: 8px;
    color: #94a3b8;
    transition: all 0.2s;
}

.social-link:hover {
    background: #10b981;
    color: #fff;
    transform: translateY(-2px);
}

.footer-contact {
    font-size: 14px;
}

.footer-contact p {
    margin-bottom: 4px;
}

.footer-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 24px 0;
    flex-wrap: wrap;
    gap: 16px;
}

.copyright {
    font-size: 13px;
    color: #64748b;
}

.footer-legal {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13px;
}

.footer-legal a {
    color: #64748b;
    text-decoration: none;
    transition: color 0.2s;
}

.footer-legal a:hover {
    color: #10b981;
}

.footer-legal .divider {
    color: #334155;
}

/* Responsive */
@media (max-width: 640px) {
    .footer-bottom {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<!-- Back to Top Button -->
<button id="backToTop" class="back-to-top" aria-label="Back to top">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
</button>

<script>
// Back to Top functionality
document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('backToTop');
    if (!btn) return;
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            btn.classList.add('visible');
        } else {
            btn.classList.remove('visible');
        }
    });
    
    btn.addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});
</script>

<style>
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
    transform: translateY(20px);
    transition: all 0.3s ease;
    z-index: 1000;
    box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
}

.back-to-top.visible {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.back-to-top:hover {
    background: #059669;
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(16, 185, 129, 0.5);
}

@media (max-width: 640px) {
    .back-to-top {
        bottom: 24px;
        right: 24px;
        width: 44px;
        height: 44px;
    }
}
</style>

</body>
</html>
