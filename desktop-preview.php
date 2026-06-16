<?php
/**
 * Desktop Preview Page - Test new desktop layout
 * This file demonstrates the new professional desktop header/footer
 */

$pageTitle = 'Desktop Preview | आकाशवाणी';
$pageDesc = 'Preview of the new professional desktop layout';

require_once __DIR__ . '/includes/header-desktop-new.php';
?>

<!-- Demo Content - Replace with actual page content -->
<section class="content-section">
    <h2 class="section-title">
        <i data-lucide="layout-grid" class="icon-lg"></i>
        Welcome to Desktop Preview
    </h2>
    <p class="section-desc">
        This demonstrates the new professional desktop layout with full-width design, 
        sidebar navigation, and enterprise-grade styling.
    </p>
    
    <!-- Demo Cards -->
    <div class="demo-grid">
        <div class="demo-card">
            <i data-lucide="newspaper" class="demo-icon"></i>
            <h3>Latest News</h3>
            <p>Stay updated with the latest news from Nepal and around the world.</p>
            <a href="/news.php" class="demo-link">View All <i data-lucide="arrow-right" class="icon-sm"></i></a>
        </div>
        
        <div class="demo-card">
            <i data-lucide="trending-up" class="demo-icon"></i>
            <h3>Market Data</h3>
            <p>Real-time NEPSE, gold prices, and currency exchange rates.</p>
            <a href="/ipo-tracker.php" class="demo-link">View All <i data-lucide="arrow-right" class="icon-sm"></i></a>
        </div>
        
        <div class="demo-card">
            <i data-lucide="calendar" class="demo-icon"></i>
            <h3>Nepali Patro</h3>
            <p>Complete Nepali calendar with holidays and important dates.</p>
            <a href="/nepali-patro.php" class="demo-link">View All <i data-lucide="arrow-right" class="icon-sm"></i></a>
        </div>
        
        <div class="demo-card">
            <i data-lucide="sparkles" class="demo-icon"></i>
            <h3>Rashifal</h3>
            <p>Daily horoscope readings for all 12 zodiac signs.</p>
            <a href="/rashifal.php" class="demo-link">View All <i data-lucide="arrow-right" class="icon-sm"></i></a>
        </div>
    </div>
</section>

<style>
.content-section {
    padding: 24px 0;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 24px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 12px;
}

.section-desc {
    font-size: 15px;
    color: #64748b;
    margin-bottom: 32px;
    max-width: 700px;
}

.demo-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

@media (max-width: 768px) {
    .demo-grid {
        grid-template-columns: 1fr;
    }
}

.demo-card {
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 24px;
    transition: all 0.2s;
}

.demo-card:hover {
    border-color: #10b981;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.1);
}

.demo-icon {
    width: 40px;
    height: 40px;
    color: #10b981;
    margin-bottom: 16px;
}

.demo-card h3 {
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 8px;
}

.demo-card p {
    font-size: 14px;
    color: #64748b;
    margin-bottom: 16px;
    line-height: 1.6;
}

.demo-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #10b981;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: gap 0.2s;
}

.demo-link:hover {
    gap: 10px;
}
</style>

<?php require_once __DIR__ . '/includes/footer-desktop-new.php'; ?>
