<?php
/**
 * आकाशवाणी — Cricket Page (Live API)
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t('क्रिकेट', 'Cricket') ?> | <?= $t('आकाशवाणी', 'Aakashvani') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .cricket-hero { background: linear-gradient(135deg, #1a472a, #2d5a3d); padding: var(--space-16) 0; color: #fff; text-align: center; }
        .cricket-tabs { display: flex; gap: var(--space-2); justify-content: center; margin-top: var(--space-6); }
        .cricket-tab { padding: var(--space-2) var(--space-4); background: rgba(255,255,255,0.1); border: none; color: #fff; border-radius: var(--radius-full); cursor: pointer; font-size: 0.875rem; transition: all var(--transition); }
        .cricket-tab:hover { background: rgba(255,255,255,0.2); }
        .cricket-tab.active { background: #fff; color: var(--dark-900); }
        .section { padding: var(--space-12) 0; }
        .match-card { background: #fff; border-radius: var(--radius-xl); padding: var(--space-6); box-shadow: var(--shadow); margin-bottom: var(--space-4); transition: all var(--transition); }
        .match-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
        .match-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4); }
        .match-type { font-size: 0.75rem; font-weight: 600; color: var(--primary); text-transform: uppercase; }
        .match-status { font-size: 0.75rem; padding: var(--space-1) var(--space-3); border-radius: var(--radius-full); }
        .status-live { background: var(--error); color: #fff; animation: pulse 2s infinite; }
        .status-upcoming { background: var(--dark-100); color: var(--dark-600); }
        .status-completed { background: var(--dark-50); color: var(--dark-500); }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }
        .team-row { display: flex; align-items: center; gap: var(--space-4); padding: var(--space-3) 0; border-bottom: 1px solid var(--dark-100); }
        .team-row:last-child { border-bottom: none; }
        .team-name { font-weight: 600; flex: 1; }
        .team-score { font-size: 1.25rem; font-weight: 700; color: var(--dark-900); }
        .team-overs { font-size: 0.875rem; color: var(--dark-500); }
        .match-footer { display: flex; justify-content: space-between; margin-top: var(--space-4); padding-top: var(--space-4); border-top: 1px solid var(--dark-100); font-size: 0.875rem; color: var(--dark-500); }
        .nepal-badge { display: inline-flex; align-items: center; gap: var(--space-2); padding: var(--space-1) var(--space-3); background: #dc2626; color: #fff; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 600; }
        /* Responsive */
        @media (max-width: 640px) {
            .match-card { padding: var(--space-4); }
            .team-name { font-size: 0.875rem; }
            .team-score { font-size: 1.5rem; }
            .cricket-tabs { flex-wrap: wrap; }
            .cricket-tabs .tab-btn { flex: 1; min-width: 100px; font-size: 0.875rem; padding: var(--space-2) var(--space-3); }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="header-main">
            <div class="container">
                <div class="header-grid">
                    <a href="/" class="header-brand">
                        <div class="brand-logo">आ</div>
                        <div class="brand-text">
                            <h1><?= $t('आकाशवाणी', 'Aakashvani') ?></h1>
                            <span><?= $t('सूचनाको खुला आकाश', 'Your Gateway to Information') ?></span>
                        </div>
                    </a>
                    <nav class="main-nav">
                        <div class="nav-list">
                            <a href="/" class="nav-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1rem;height:1rem"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg><?= $t('गृह', 'Home') ?></a>
                            <a href="/weather.php" class="nav-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1rem;height:1rem"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/></svg><?= $t('मौसम', 'Weather') ?></a>
                            <a href="/cricket.php" class="nav-link active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1rem;height:1rem"><circle cx="12" cy="12" r="10"/></svg><?= $t('क्रिकेट', 'Cricket') ?></a>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- Cricket Hero -->
    <section class="cricket-hero">
        <div class="container">
            <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: var(--space-2);">
                🏏 <?= $t('क्रिकेट स्कोर', 'Cricket Scores') ?>
            </h1>
            <p style="opacity: 0.8;"><?= $t('लाइभ क्रिकेट नतिजा र तालिका', 'Live cricket results and schedule') ?></p>
            
            <div class="cricket-tabs">
                <button class="cricket-tab active" onclick="showTab('live')"><?= $t('लाइभ', 'Live') ?></button>
                <button class="cricket-tab" onclick="showTab('upcoming')"><?= $t('आगामी', 'Upcoming') ?></button>
                <button class="cricket-tab" onclick="showTab('results')"><?= $t('नतिजा', 'Results') ?></button>
                <button class="cricket-tab" onclick="showTab('nepal')"><?= $t('नेपाल', 'Nepal') ?></button>
            </div>
        </div>
    </section>

    <!-- Live Matches -->
    <section class="section" id="live-section">
        <div class="container" style="max-width: 800px;">
            <h2 class="text-xl font-bold mb-6">
                <span class="live-badge"><span class="live-dot"></span>LIVE</span>
                <?= $t('हाल भइरहेको', 'Currently Live') ?>
            </h2>
            <div id="live-matches">
                <div class="match-card">
                    <div class="match-header">
                        <span class="match-type"><?= $t('अभौतिक क्रिकेट', 'Virtual Cricket') ?></span>
                        <span class="match-status status-live"><?= $t('लाइभ', 'LIVE') ?></span>
                    </div>
                    <div class="team-row">
                        <span class="team-name">🏏 <?= $t('नेपाल राष्ट्रिय टोली', 'Nepal National Team') ?></span>
                        <span class="team-score">--/--</span>
                        <span class="team-overs">(-- ov)</span>
                    </div>
                    <div class="match-footer">
                        <span><?= $t('टस', 'Toss') ?>: --</span>
                        <span><?= $t('अपडेट', 'Update') ?>: --:--</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Upcoming Matches -->
    <section class="section" id="upcoming-section" style="display: none;">
        <div class="container" style="max-width: 800px;">
            <h2 class="text-xl font-bold mb-6"><?= $t('आगामी खेलहरू', 'Upcoming Matches') ?></h2>
            <div id="upcoming-matches">
                <div class="match-card">
                    <div class="match-header">
                        <span class="match-type">T20 International</span>
                        <span class="match-status status-upcoming"><?= $t('आगामी', 'Upcoming') ?></span>
                    </div>
                    <div class="team-row">
                        <span class="team-name">🇳🇵 नेपाल</span>
                        <span style="color: var(--dark-400);">vs</span>
                        <span class="team-name">🏴󠁧󠁢󠁥󠁮󠁧󠁿 इंग्ल्याण्ड</span>
                    </div>
                    <div class="match-footer">
                        <span><?= $t('मिति', 'Date') ?>: <?= date('d M Y') ?></span>
                        <span><?= $t('स्थान', 'Venue') ?>: --</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Results -->
    <section class="section" id="results-section" style="display: none;">
        <div class="container" style="max-width: 800px;">
            <h2 class="text-xl font-bold mb-6"><?= $t('नतिजा', 'Results') ?></h2>
            <div id="results-matches">
                <div class="match-card">
                    <div class="match-header">
                        <span class="match-type"><?= $t('अभौतिक क्रिकेट', 'Virtual Cricket') ?></span>
                        <span class="match-status status-completed"><?= $t('सम्पन्न', 'Completed') ?></span>
                    </div>
                    <div class="team-row">
                        <span class="team-name">🏏 <?= $t('नेपाल राष्ट्रिय टोली', 'Nepal National Team') ?></span>
                        <span class="team-score" style="color: var(--success);">✓</span>
                        <span class="team-overs"><?= $t('विजयी', 'Winner') ?></span>
                    </div>
                    <div class="match-footer">
                        <span><?= $t('मिति', 'Date') ?>: <?= date('d M Y', strtotime('-1 day')) ?></span>
                        <span><?= $t('प्रतियोगिता', 'Tournament') ?>: ACC Cup</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Nepal Cricket -->
    <section class="section" id="nepal-section" style="display: none; background: var(--dark-50);">
        <div class="container" style="max-width: 800px;">
            <h2 class="text-xl font-bold mb-6">
                <span class="nepal-badge">🇳🇵</span>
                <?= $t('नेपाल क्रिकेट', 'Nepal Cricket') ?>
            </h2>
            <div id="nepal-matches">
                <div class="alert alert-info"><?= $t('नेपाल क्रिकेटको जानकारी लोड हुँदै...', 'Loading Nepal cricket info...') ?></div>
            </div>
        </div>
    </section>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-bottom" style="border:none;padding:0">
                <p class="footer-copyright">&copy; <?= date('Y') ?> <?= $t('आकाशवाणी', 'Aakashvani') ?></p>
            </div>
        </div>
    </footer>

    <script>
    // Tab switching
    function showTab(tab) {
        document.querySelectorAll('.cricket-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('[id$="-section"]').forEach(s => s.style.display = 'none');
        
        document.querySelector(`.cricket-tab:nth-child(${tab === 'live' ? 1 : tab === 'upcoming' ? 2 : tab === 'results' ? 3 : 4})`).classList.add('active');
        document.getElementById(tab + '-section').style.display = 'block';
        
        loadCricketData(tab);
    }

    // Load cricket data
    async function loadCricketData(mode) {
        const containers = {
            'live': 'live-matches',
            'upcoming': 'upcoming-matches',
            'results': 'results-matches',
            'nepal': 'nepal-matches'
        };
        
        try {
            const resp = await fetch('/api/cricket.php?mode=' + mode);
            const data = await resp.json();
            
            if (data.matches && data.matches.length > 0) {
                const container = document.getElementById(containers[mode]);
                container.innerHTML = data.matches.map(match => `
                    <div class="match-card">
                        <div class="match-header">
                            <span class="match-type">${match.type || 'T20'}</span>
                            <span class="match-status ${match.status === 'live' ? 'status-live' : match.status === 'upcoming' ? 'status-upcoming' : 'status-completed'}">
                                ${match.status === 'live' ? '🔴 LIVE' : match.status === 'upcoming' ? '⏰ ' + match.status : '✓ ' + match.status}
                            </span>
                        </div>
                        ${match.teams ? match.teams.map(team => `
                            <div class="team-row">
                                <span class="team-name">${team.flag || '🏏'} ${team.name}</span>
                                <span class="team-score">${team.score || '-'}</span>
                                <span class="team-overs">${team.overs || ''}</span>
                            </div>
                        `).join('') : ''}
                        <div class="match-footer">
                            <span>${match.venue || 'स्थान निश्चित भएको छैन'}</span>
                            <span>${match.time || match.date || ''}</span>
                        </div>
                    </div>
                `).join('');
            }
        } catch (e) {
            console.log('Cricket API error:', e);
        }
    }

    // Load live matches on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadCricketData('live');
    });
    </script>
</body>
</html>