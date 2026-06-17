<?php
/**
 * आकाशवाणी — Nokari (Jobs)
 */
require_once __DIR__ . '/config.php';
$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;
?>
<!DOCTYPE html>
<html lang="<?= $isNepali ? 'ne' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta property="og:title" content="<?= $t('नोकरी', 'Jobs') ?> | आकाशवाणी">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t('नोकरी - रोजगार सुचना', 'Jobs - Employment Information') ?> | आकाशवाणी</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .page-header { background: linear-gradient(135deg, #059669, #10b981); padding: var(--space-8) 0; color: #fff; }
        .job-card { background: #fff; border-radius: var(--radius-xl); padding: var(--space-6); box-shadow: var(--shadow); margin-bottom: var(--space-4); border-left: 4px solid #059669; }
        .job-title { font-size: 1.125rem; font-weight: 700; color: var(--dark-900); margin-bottom: var(--space-2); }
        .job-company { font-size: 0.875rem; color: #059669; font-weight: 600; margin-bottom: var(--space-2); }
        .job-meta { display: flex; flex-wrap: wrap; gap: var(--space-3); font-size: 0.75rem; color: var(--dark-500); margin-bottom: var(--space-3); }
        .job-meta span { display: flex; align-items: center; gap: 4px; }
        .job-desc { font-size: 0.875rem; color: var(--dark-600); margin-bottom: var(--space-3); line-height: 1.6; }
        .job-salary { font-size: 1rem; font-weight: 700; color: #059669; }
        .job-badge { display: inline-block; padding: 2px 10px; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 600; }
        .badge-new { background: #dcfce7; color: #16a34a; }
        .badge-hot { background: #fee2e2; color: #dc2626; }
        .section { padding: var(--space-8) 0; }
        .loading-spinner { display: flex; justify-content: center; padding: var(--space-12); }
        .spinner { width: 40px; height: 40px; border: 3px solid var(--dark-200); border-top-color: #059669; border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        @media (max-width: 768px) { .job-meta { gap: var(--space-2); } }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="header-main">
            <div class="container">
                <div class="flex items-center justify-between gap-4">
                    <a href="/" class="header-brand"><div class="brand-logo">आ</div><span class="brand-name"><?= $t('आकाशवाणी', 'Aakashvani') ?></span></a>
                    <nav class="main-nav">
                        <div class="container"><div class="nav-list">
                            <a href="/" class="nav-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg><?= $t('गृह', 'Home') ?></a>
                            <a href="/news.php" class="nav-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 0-2-2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/></svg><?= $t('समाचार', 'News') ?></a>
                            <a href="/loksewa.php" class="nav-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg><?= $t('लोकसेवा', 'Lok Sewa') ?></a>
                        </div></div>
                    </nav>
                </div>
            </div>
        </div>
    </header>
    
    <section class="page-header">
        <div class="container">
            <h1 class="page-title" style="display:flex;align-items:center;gap:12px">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:#fff"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                <?= $t('नोकरी - रोजगार सुचना', 'Jobs - Employment Information') ?>
            </h1>
            <p class="page-subtitle"><?= $t('नेपाल र बिश्वको ताजा रोजगारी सुचना', 'Latest job vacancies in Nepal and abroad') ?></p>
        </div>
    </section>

    <section class="section">
        <div class="container" style="max-width: 900px;">
            <div id="jobs-loading" class="loading-spinner"><div class="spinner"></div></div>
            <div id="jobs-list" style="display:none"></div>
            <div id="jobs-empty" style="display:none;text-align:center;padding:var(--space-8);color:var(--dark-500)">
                <?= $t('हाल कुनै नोकरी छैन', 'No jobs available at this time') ?>
            </div>
            <div id="jobs-error" style="display:none;text-align:center;padding:var(--space-8);color:var(--error)">
                <?= $t('डाटा लोड हुन सकेन', 'Failed to load data') ?>
            </div>
        </div>
    </section>

    <footer class="site-footer">
        <div class="container"><div class="footer-bottom" style="border:none;padding:0"><p class="footer-copyright">&copy; <?= date('Y') ?> <?= $t('आकाशवाणी', 'Aakashvani') ?></p></div></div>
    </footer>
    <script>
    async function loadJobs() {
        try {
            const resp = await fetch('/api/nokari.php?limit=30');
            const data = await resp.json();
            if (data.ok && data.jobs) {
                renderJobs(data.jobs);
                document.getElementById('jobs-loading').style.display = 'none';
                document.getElementById('jobs-list').style.display = 'block';
            } else { throw new Error(); }
        } catch(e) {
            document.getElementById('jobs-loading').style.display = 'none';
            document.getElementById('jobs-error').style.display = 'block';
        }
    }
    function renderJobs(jobs) {
        const list = document.getElementById('jobs-list');
        if (!jobs || jobs.length === 0) {
            list.style.display = 'none';
            document.getElementById('jobs-empty').style.display = 'block';
            return;
        }
        list.innerHTML = jobs.map(job => {
            const badge = job.is_new ? '<span class="job-badge badge-new"><?= $t("नयाँ", "New") ?></span>' : '';
            return '<div class="job-card"><div class="flex justify-between items-start gap-4" style="margin-bottom:var(--space-2)"><h3 class="job-title">' + (job.title || job.position || '---') + '</h3>' + badge + '</div><p class="job-company">' + (job.company || job.employer || '---') + '</p><div class="job-meta"><span>📍 ' + (job.location || '---') + '</span>' + (job.salary ? '<span>💰 ' + job.salary + '</span>' : '') + (job.deadline ? '<span>⏰ ' + job.deadline + '</span>' : '') + '</div><p class="job-desc">' + (job.description || job.desc || '') + '</p>' + (job.link ? '<a href="' + job.link + '" target="_blank" style="display:inline-block;padding:var(--space-2) var(--space-4);background:#059669;color:#fff;border-radius:var(--radius-lg);font-size:0.875rem;text-decoration:none"><?= $t("आवेदन दिनुहोस्", "Apply Now") ?> →</a>' : '') + '</div>';
        }).join('');
    }
    document.addEventListener('DOMContentLoaded', loadJobs);
    </script>
    <script src="/assets/js/app.js"></script>
</body>
</html>