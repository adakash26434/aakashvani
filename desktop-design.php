<?php
/**
 * Desktop Design Preview - Test new professional layout
 * Uses header-new-design.php and footer-new-design.php
 */

$pageTitle = 'Desktop Preview | आकाशवाणी';
$pageDesc = 'Preview of the new professional desktop design';

require_once __DIR__ . '/header-new-design.php';
?>

<!-- Demo Content -->
<section class="preview-section">
    
    <h1 class="preview-title">
        <i data-lucide="layout-grid" class="icon-lg"></i>
        Welcome to आकाशवाणी Desktop
    </h1>
    <p class="preview-desc">
        This is the new professional desktop design based on enterprise News Portal principles.
        Clean, modern, and user-friendly interface.
    </p>
    
    <!-- Feature Cards -->
    <div class="feature-grid">
        <div class="feature-card">
            <div class="feature-icon">
                <i data-lucide="newspaper"></i>
            </div>
            <h3>Latest News</h3>
            <p>Stay updated with the latest news from Nepal and around the world.</p>
            <a href="/news.php" class="feature-link">
                View All <i data-lucide="arrow-right"></i>
            </a>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">
                <i data-lucide="trending-up"></i>
            </div>
            <h3>Market Data</h3>
            <p>Real-time NEPSE, gold prices, and currency exchange rates.</p>
            <a href="/ipo-tracker.php" class="feature-link">
                View All <i data-lucide="arrow-right"></i>
            </a>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">
                <i data-lucide="calendar"></i>
            </div>
            <h3>Nepali Patro</h3>
            <p>Complete Nepali calendar with holidays and important dates.</p>
            <a href="/nepali-patro.php" class="feature-link">
                View All <i data-lucide="arrow-right"></i>
            </a>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">
                <i data-lucide="sparkles"></i>
            </div>
            <h3>Rashifal</h3>
            <p>Daily horoscope readings for all 12 zodiac signs.</p>
            <a href="/rashifal.php" class="feature-link">
                View All <i data-lucide="arrow-right"></i>
            </a>
        </div>
    </div>
    
</section>

<style>
.preview-section {
    padding: 24px 0;
}

.preview-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 28px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 12px;
}

.preview-desc {
    font-size: 16px;
    color: #64748b;
    margin-bottom: 32px;
    max-width: 700px;
    line-height: 1.7;
}

.feature-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

@media (max-width: 768px) {
    .feature-grid {
        grid-template-columns: 1fr;
    }
}

.feature-card {
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 24px;
    transition: all 0.2s;
}

.feature-card:hover {
    border-color: #10b981;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.1);
}

.feature-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #10b981, #059669);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
}

.feature-icon i {
    width: 24px;
    height: 24px;
    color: #fff;
}

.feature-card h3 {
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 8px;
}

.feature-card p {
    font-size: 14px;
    color: #64748b;
    margin-bottom: 16px;
    line-height: 1.6;
}

.feature-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #10b981;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: gap 0.2s;
}

.feature-link:hover {
    gap: 10px;
}

.feature-link i {
    width: 16px;
    height: 16px;
}
</style>

<?php require_once __DIR__ . '/footer-new-design.php'; ?>
