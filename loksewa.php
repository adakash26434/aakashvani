<?php
/**
 * आकाशवाणी — Lok Sewa (Government Jobs)
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
    <meta property="og:title" content="<?= $t('लोकसेवा', 'Lok Sewa') ?> | आकाशवाणी">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t('लोकसेवा आयोग - सरकारी नोकरी', 'Lok Sewa Commission - Govt Jobs') ?> | आकाशवाणी</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .page-header { background: linear-gradient(135deg, #7c3aed, #a855f7); padding: var(--space-8) 0; color: #fff; }
        .job-card { background: #fff; border-radius: var(--radius-xl); padding: var(--space-6); box-shadow: var(--shadow); margin-bottom: var(--space-4); border-left: 4px solid #7c3aed; }
        .job-title { font-size: 1.125rem; font-weight: 700; color: var(--dark-900); margin-bottom: var(--space-2); }
        .job-org { font-size: 0.875rem; color: #7c3aed; font-weight: 600; margin-bottom: var(--space-2); }
        .job-meta { display: flex; flex-wrap: wrap; gap: var(--space-4); font-size: 0.75rem; color: var(--dark-500); }
        .job-badge { display: inline-block; padding: 2px 10px; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 600; }
        .badge-new { background: #dcfce7; color: #16a34a; }
        .badge-deadline { background: #fee2e2; color: #dc2626; }
        .badge-closed { background: var(--dark-100); color: var(--dark-500); }
        .tabs { display: flex; gap: var(--space-2); margin-bottom: var(--space-6); flex-wrap: wrap; }
        .tab { padding: var(--space-2) var(--space-4); border: 1px solid var(--dark-200); border-radius: var(--radius-full); background: #fff; cursor: pointer; font-size: 0.875rem; transition: all var(--transition); }
        .tab.active { background: #7c3aed; color: #fff; border-color: #7c3aed; }
        .section { padding: var(--space-8) 0; }
        .loading-spinner { display: flex; justify-content: center; padding: var(--space-12); }
        .spinner { width: 40px; height: 40px; border: 3px solid var(--dark-200); border-top-color: #7c3aed; border-radius: 50%; animation: spin 1s linear infinite; }
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
                            <a href="/gov-services.php" class="nav-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg><?= $t('सरकारी सेवा', 'Gov Services') ?></a>
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
                <?= $t('लोकसेवा आयोग - सरकारी नोकरी', 'Lok Sewa Commission - Government Jobs') ?>
            </h1>
            <p class="page-subtitle"><?= $t('नेपाल सरकारको ताजा नोकरी सुचना', 'Latest government job vacancies in Nepal') ?></p>
        </div>
    </section>

    <section class="section">
        <div class="container" style="max-width: 900px;">
            <div class="tabs">
                <button class="tab active" data-type="all"><?= $t('सबै', 'All') ?></button>
                <button class="tab" data-type="vacancy"><?= $t('दरखास्त', 'Vacancy') ?></button>
                <button class="tab" data-type="result"><?= $t('नतिजा', 'Result') ?></button>
                <button class="tab" data-type="notice"><?= $t('सूचना', 'Notice') ?></button>
            </div>
            <div id="jobs-loading" class="loading-spinner"><div class="spinner"></div></div>
            <div id="jobs-list" style="display:none"></div>
            <div id="jobs-empty" style="display:none;text-align:center;padding:var(--space-8);color:var(--dark-500)">
                <?= $t('हाल कुनै सुचना छैन', 'No announcements at this time') ?>
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
    let allJobs = [];
    async function loadJobs() {
        try {
            const resp = await fetch('/api/loksewa.php?type=all&limit=30');
            const data = await resp.json();
            if (data.ok && data.notices) {
                allJobs = data.notices;
                renderJobs('all');
                document.getElementById('jobs-loading').style.display = 'none';
                document.getElementById('jobs-list').style.display = 'block';
            } else { throw new Error(); }
        } catch(e) {
            document.getElementById('jobs-loading').style.display = 'none';
            document.getElementById('jobs-error').style.display = 'block';
        }
    }
    function renderJobs(type) {
        const list = document.getElementById('jobs-list');
        const filtered = type === 'all' ? allJobs : allJobs.filter(j => j.type === type);
        if (filtered.length === 0) {
            list.style.display = 'none';
            document.getElementById('jobs-empty').style.display = 'block';
            return;
        }
        document.getElementById('jobs-empty').style.display = 'none';
        list.style.display = 'block';
        list.innerHTML = filtered.map(job => {
            const badge = job.deadline ? '<?= $t("नयाँ", "New") ?>' : '';
            const deadline = job.deadline ? '<span class="job-badge badge-deadline"><?= $t("अन्तिम मिति", "Deadline") ?>: ' + job.deadline + '</span>' : '';
            return '<div class="job-card"><div class="flex justify-between items-start gap-4" style="margin-bottom:var(--space-3)"><div><h3 class="job-title">' + (job.title || job.name || '---') + '</h3><p class="job-org">' + (job.organization || job.org || 'लोकसेवा आयोग') + '</p></div>' + (badge ? '<span class="job-badge badge-new">' + badge + '</span>' : '') + '</div><div class="job-meta"><span>' + (job.level || job.category || '---') + '</span><span>' + (job.posts || job.vacancy || '') + '</span>' + deadline + '</div>' + (job.link ? '<a href="' + job.link + '" target="_blank" style="display:inline-block;margin-top:var(--space-3);padding:var(--space-2) var(--space-4);background:#7c3aed;color:#fff;border-radius:var(--radius-lg);font-size:0.875rem;text-decoration:none"><?= $t("विवरण हेर्नुहोस्", "View Details") ?> →</a>' : '') + '</div>';
        }).join('');
        // Update tabs
        document.querySelectorAll('.tab').forEach(t => {
            t.classList.toggle('active', t.dataset.type === type);
        });
    }
    document.addEventListener('DOMContentLoaded', () => {
        loadJobs();
        document.querySelectorAll('.tab').forEach(t => {
            t.addEventListener('click', () => renderJobs(t.dataset.type));
        });
    });
    </script>
    <script src="/assets/js/app.js"></script>
</body>
</html>