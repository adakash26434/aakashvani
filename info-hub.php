<?php
/**
 * info-hub.php — All-in-One Information Hub
 * Organized by category: News, Notices, Astrology, Financial, Contacts
 */
require_once __DIR__ . '/header.php';

// Categories with their configurations
$sections = [
  'news' => [
    'title' => $tH('ताजा समाचार','Latest News'),
    'subtitle' => $tH('२०+ स्रोतबाट लाइभ','From 20+ sources'),
    'icon' => 'newspaper',
    'color' => 'sky',
    'link' => '/news.php',
    'api' => '/api/news-rss.php?cat=all&limit=6',
    'type' => 'news'
  ],
  'notices' => [
    'title' => $tH('सूचना र जानकारी','Notices & Info'),
    'subtitle' => $tH('सरकारी र आधिकारिक','Official notices'),
    'icon' => 'bell',
    'color' => 'amber',
    'link' => '/notices.php',
    'api' => '/api/utilities.php?section=loksewa',
    'type' => 'notices'
  ],
  'astrology' => [
    'title' => $tH('ज्योतिष र राशिफल','Astrology'),
    'subtitle' => $tH('दैनिक राशिफल र पञ्चाङ्ग','Daily horoscope'),
    'icon' => 'sparkles',
    'color' => 'violet',
    'link' => '/rashifal.php',
    'api' => '/api/rashifal.php',
    'type' => 'rashifal'
  ],
  'financial' => [
    'title' => $tH('वित्तीय जानकारी','Financial Info'),
    'subtitle' => $tH('बजार, शेयर, मुद्रा','Markets, Forex'),
    'icon' => 'trending-up',
    'color' => 'emerald',
    'link' => '/utilities.php',
    'api' => '/api/market-data.php?type=summary',
    'type' => 'market'
  ],
  'contacts' => [
    'title' => $tH('आपतकालीन सम्पर्क','Emergency Contacts'),
    'subtitle' => $tH('टोल-फ्री नम्बरहरू','Toll-free numbers'),
    'icon' => 'phone',
    'color' => 'red',
    'link' => '/emergency.php',
    'type' => 'static'
  ],
  'gov' => [
    'title' => $tH('सरकारी सेवाहरू','Govt. Services'),
    'subtitle' => $tH('अनलाइन सेवाहरू','Online services'),
    'icon' => 'building-2',
    'color' => 'blue',
    'link' => '/gov-services.php',
    'type' => 'static'
  ],
];

// Quick access shortcuts
$shortcuts = [
  ['📰', 'समाचार', '/news.php', 'sky'],
  ['🔔', 'सूचना', '/notices.php', 'amber'],
  ['✨', 'राशिफल', '/rashifal.php', 'violet'],
  ['💰', 'बजार', '/utilities.php', 'emerald'],
  ['📞', 'फोन', '/emergency.php', 'red'],
  ['🏛️', 'सेवा', '/gov-services.php', 'blue'],
];
?>



<div class="info-hub">
  <!-- Header -->
  <div class="hub-header">
    <div>
      <h1 class="ne"><?= $tH('जानकारी केन्द्र','Information Hub') ?></h1>
      <p class="ne"><?= $tH('सबै जानकारी एकै ठाउँमा','All info in one place') ?></p>
    </div>
    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center text-white">
      <i data-lucide="layout-grid" class="w-6 h-6"></i>
    </div>
  </div>

  <!-- Search -->
  <div class="hub-search">
    <i data-lucide="search"></i>
    <input type="text" id="hubSearch" placeholder="<?= $tH('समाचार, सूचना, राशिफल खोज्नुहोस्...','Search news, notices, horoscope...') ?>" class="ne">
  </div>

  <!-- Quick Access -->
  <div class="quick-access">
    <?php foreach($shortcuts as $s): ?>
      <a href="<?= $s[2] ?>">
        <div class="emoji bg-<?= $s[3] ?>-100"><?= $s[0] ?></div>
        <span class="ne"><?= $s[1] ?></span>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- Live News Section -->
  <div class="section-card" id="newsSection">
    <div class="section-header">
      <div class="section-icon bg-sky-100 text-sky-600">
        <i data-lucide="newspaper"></i>
      </div>
      <div class="section-title">
        <h2 class="ne"><?= $sections['news']['title'] ?></h2>
        <p class="ne"><?= $sections['news']['subtitle'] ?></p>
      </div>
      <a href="<?= $sections['news']['link'] ?>" class="section-more bg-sky-100 text-sky-700 hover:bg-sky-200 ne">
        <?=$tH('सबै हेर्नुहोस्','View All')?>
      </a>
    </div>
    <div class="preview-list" id="newsPreview">
      <!-- Loading skeleton -->
      <?php for($i=0; $i<3; $i++): ?>
        <div class="preview-item">
          <div class="preview-thumb skeleton" style="width:72px;height:72px;border-radius:10px;"></div>
          <div class="preview-content" style="flex:1;">
            <div class="skeleton" style="width:60px;height:18px;margin-bottom:8px;"></div>
            <div class="skeleton" style="width:100%;height:16px;margin-bottom:6px;"></div>
            <div class="skeleton" style="width:80%;height:14px;"></div>
          </div>
        </div>
      <?php endfor; ?>
    </div>
  </div>

  <!-- Two Column Layout -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
    <!-- Notices -->
    <div class="section-card" id="noticesSection">
      <div class="section-header">
        <div class="section-icon bg-amber-100 text-amber-600">
          <i data-lucide="bell"></i>
        </div>
        <div class="section-title">
          <h2 class="ne"><?= $sections['notices']['title'] ?></h2>
        </div>
      </div>
      <div class="preview-list" id="noticesPreview">
        <div class="mini-item" style="background:linear-gradient(135deg,#fef3c7,#fde68a);">
          <div class="mini-title ne text-amber-900"><?=$tH('लोक सेवा आयोग','PSC')?></div>
          <div class="mini-desc ne text-amber-700">विज्ञापन र नतिजा</div>
        </div>
        <div class="mini-item" style="background:linear-gradient(135deg,#fee2e2,#fecaca);">
          <div class="mini-title ne text-red-900"><?=$tH('नेपाली सेना','Army')?></div>
          <div class="mini-desc ne text-red-700">भर्ना सूचना</div>
        </div>
      </div>
      <a href="<?= $sections['notices']['link'] ?>" class="block mt-3 text-center py-2 bg-amber-50 text-amber-700 rounded-lg text-sm font-semibold ne hover:bg-amber-100">
        <?=$tH('सबै सूचना हेर्नुहोस्','View All Notices')?>
      </a>
    </div>

    <!-- Astrology -->
    <div class="section-card" id="astroSection">
      <div class="section-header">
        <div class="section-icon bg-violet-100 text-violet-600">
          <i data-lucide="sparkles"></i>
        </div>
        <div class="section-title">
          <h2 class="ne"><?= $sections['astrology']['title'] ?></h2>
        </div>
      </div>
      <div class="mini-grid" id="rashifalPreview">
        <?php 
        $rashis = ['मेष','वृष','मिथुन','कर्कट','सिंह','कन्या','तुला','वृश्चिक','धनु','मकर','कुम्भ','मीन'];
        $rashiIcons = ['♈','♉','♊','♋','♌','♍','♎','♏','♐','♑','♒','♓'];
        foreach(array_slice($rashis, 0, 4) as $i => $r): 
        ?>
          <a href="/rashifal.php#<?= $r ?>" class="mini-item text-center" style="background:linear-gradient(135deg,#ede9fe,#ddd6fe);">
            <div style="font-size:24px;margin-bottom:4px;"><?= $rashiIcons[$i] ?></div>
            <div class="mini-title ne text-violet-900"><?= $r ?></div>
          </a>
        <?php endforeach; ?>
      </div>
      <a href="<?= $sections['astrology']['link'] ?>" class="block mt-3 text-center py-2 bg-violet-50 text-violet-700 rounded-lg text-sm font-semibold ne hover:bg-violet-100">
        <?=$tH('आफ्नो राशि हेर्नुहोस्','View Your Horoscope')?>
      </a>
    </div>
  </div>

  <!-- Financial Market -->
  <div class="section-card" id="financeSection">
    <div class="section-header">
      <div class="section-icon bg-emerald-100 text-emerald-600">
        <i data-lucide="trending-up"></i>
      </div>
      <div class="section-title">
        <h2 class="ne"><?= $sections['financial']['title'] ?></h2>
        <p class="ne"><?= $sections['financial']['subtitle'] ?></p>
      </div>
      <a href="<?= $sections['financial']['link'] ?>" class="section-more bg-emerald-100 text-emerald-700 hover:bg-emerald-200 ne">
        <?=$tH('विस्तृत','Details')?>
      </a>
    </div>
    <div class="mini-grid" id="marketPreview">
      <div class="mini-item" style="background:linear-gradient(135deg,#d1fae5,#a7f3d0);">
        <div class="mini-icon bg-emerald-500 text-white">
          <i data-lucide="coins" class="w-5 h-5"></i>
        </div>
        <div class="mini-title ne text-emerald-900">सुन/चाँदी</div>
        <div class="mini-desc ne text-emerald-700" id="goldPrice">लोड हुँदै...</div>
      </div>
      <div class="mini-item" style="background:linear-gradient(135deg,#dbeafe,#bfdbfe);">
        <div class="mini-icon bg-blue-500 text-white">
          <i data-lucide="fuel" class="w-5 h-5"></i>
        </div>
        <div class="mini-title ne text-blue-900">इन्धन मूल्य</div>
        <div class="mini-desc ne text-blue-700" id="fuelPrice">लोड हुँदै...</div>
      </div>
      <div class="mini-item" style="background:linear-gradient(135deg,#fef3c7,#fde68a);">
        <div class="mini-icon bg-amber-500 text-white">
          <i data-lucide="landmark" class="w-5 h-5"></i>
        </div>
        <div class="mini-title ne text-amber-900">ब्याजदर</div>
        <div class="mini-desc ne text-amber-700" id="bankRate">NRB Rates</div>
      </div>
      <div class="mini-item" style="background:linear-gradient(135deg,#e0e7ff,#c7d2fe);">
        <div class="mini-icon bg-indigo-500 text-white">
          <i data-lucide="activity" class="w-5 h-5"></i>
        </div>
        <div class="mini-title ne text-indigo-900">NEPSE</div>
        <div class="mini-desc ne text-indigo-700" id="nepseIndex">लोड हुँदै...</div>
      </div>
    </div>
  </div>

  <!-- Emergency Contacts -->
  <div class="section-card" id="emergencySection">
    <div class="section-header">
      <div class="section-icon bg-red-100 text-red-600">
        <i data-lucide="phone"></i>
      </div>
      <div class="section-title">
        <h2 class="ne"><?= $sections['contacts']['title'] ?></h2>
        <p class="ne"><?= $sections['contacts']['subtitle'] ?></p>
      </div>
      <a href="<?= $sections['contacts']['link'] ?>" class="section-more bg-red-100 text-red-700 hover:bg-red-200 ne">
        <?=$tH('सबै','All')?>
      </a>
    </div>
    <div class="mini-grid">
      <a href="tel:100" class="mini-item" style="background:linear-gradient(135deg,#fee2e2,#fecaca);">
        <div class="mini-icon bg-red-600 text-white">
          <i data-lucide="shield-alert" class="w-5 h-5"></i>
        </div>
        <div class="mini-title ne text-red-900">१००</div>
        <div class="mini-desc ne text-red-700">पुलिस</div>
      </a>
      <a href="tel:102" class="mini-item" style="background:linear-gradient(135deg,#fee2e2,#fecaca);">
        <div class="mini-icon bg-red-600 text-white">
          <i data-lucide="ambulance" class="w-5 h-5"></i>
        </div>
        <div class="mini-title ne text-red-900">१०२</div>
        <div class="mini-desc ne text-red-700">एम्बुलेन्स</div>
      </a>
      <a href="tel:101" class="mini-item" style="background:linear-gradient(135deg,#fee2e2,#fecaca);">
        <div class="mini-icon bg-red-600 text-white">
          <i data-lucide="flame" class="w-5 h-5"></i>
        </div>
        <div class="mini-title ne text-red-900">१०१</div>
        <div class="mini-desc ne text-red-700">दमकल</div>
      </a>
      <a href="tel:197" class="mini-item" style="background:linear-gradient(135deg,#fee2e2,#fecaca);">
        <div class="mini-icon bg-red-600 text-white">
          <i data-lucide="heart-pulse" class="w-5 h-5"></i>
        </div>
        <div class="mini-title ne text-red-900">१९७</div>
        <div class="mini-desc ne text-red-700">स्वास्थ्य</div>
      </a>
    </div>
  </div>

  <!-- Government Services -->
  <div class="section-card" id="govSection">
    <div class="section-header">
      <div class="section-icon bg-blue-100 text-blue-600">
        <i data-lucide="building-2"></i>
      </div>
      <div class="section-title">
        <h2 class="ne"><?= $sections['gov']['title'] ?></h2>
        <p class="ne"><?= $sections['gov']['subtitle'] ?></p>
      </div>
      <a href="<?= $sections['gov']['link'] ?>" class="section-more bg-blue-100 text-blue-700 hover:bg-blue-200 ne">
        <?=$tH('सबै','All')?>
      </a>
    </div>
    <div class="mini-grid">
      <a href="https://nepal.gov.np/" target="_blank" rel="noopener" class="mini-item">
        <div class="mini-icon bg-blue-500 text-white">
          <i data-lucide="globe" class="w-5 h-5"></i>
        </div>
        <div class="mini-title ne">नेपाल सरकार</div>
        <div class="mini-desc ne">आधिकारिक पोर्टल</div>
      </a>
      <a href="https://www.nrb.org.np/" target="_blank" rel="noopener" class="mini-item">
        <div class="mini-icon bg-emerald-500 text-white">
          <i data-lucide="banknote" class="w-5 h-5"></i>
        </div>
        <div class="mini-title ne">राष्ट्रिय बैंक</div>
        <div class="mini-desc ne">NRB</div>
      </a>
      <a href="https://www.mof.gov.np/" target="_blank" rel="noopener" class="mini-item">
        <div class="mini-icon bg-amber-500 text-white">
          <i data-lucide="landmark" class="w-5 h-5"></i>
        </div>
        <div class="mini-title ne">अर्थ मन्त्रालय</div>
        <div class="mini-desc ne">MOF</div>
      </a>
      <a href="https://www.doit.gov.np/" target="_blank" rel="noopener" class="mini-item">
        <div class="mini-icon bg-violet-500 text-white">
          <i data-lucide="monitor" class="w-5 h-5"></i>
        </div>
        <div class="mini-title ne">सूचना प्रविधि</div>
        <div class="mini-desc ne">DOIT</div>
      </a>
    </div>
  </div>

  <!-- Footer Info -->
  <div style="text-align:center;padding:20px;color:#94a3b8;font-size:12px;" class="ne">
    <p><?= $tH('सबै जानकारी आधिकारिक स्रोतहरूबाट','All info from official sources') ?></p>
    <p style="margin-top:4px;">आकाशवाणी · <?= date('Y') ?></p>
  </div>
</div>

<script>
// Load News
fetch('/api/news-rss.php?cat=all&limit=4')
  .then(r => r.json())
  .then(d => {
    if (d.items && d.items.length > 0) {
      const container = document.getElementById('newsPreview');
      container.innerHTML = d.items.slice(0, 4).map(item => `
        <a href="${item.internalUrl || item.link}" class="preview-item">
          <div class="preview-thumb">
            ${item.image ? `<img src="${item.image}" alt="" onerror="this.style.display='none'">` : '<i data-lucide="newspaper"></i>'}
          </div>
          <div class="preview-content">
            <div class="preview-meta">
              <span class="preview-tag bg-sky-100 text-sky-700">${item.cat || 'समाचार'}</span>
              <span class="preview-tag bg-slate-100 text-slate-600">${item.sourceLabel || item.source || ''}</span>
            </div>
            <div class="preview-title ne">${item.title}</div>
            <div class="preview-time">
              <i data-lucide="clock" class="w-3 h-3"></i>
              ${item.ago || 'भर्खरै'}
            </div>
          </div>
        </a>
      `).join('');
      if (window.lucide) lucide.createIcons();
    }
  })
  .catch(e => { console.error('News load failed:', e); });

// Load Market Data
fetch('/api/market-data.php?type=summary')
  .then(r => r.json())
  .then(d => {
    if (d.gold) {
      document.getElementById('goldPrice').textContent = 
        'Fine: रु ' + (d.gold.fine || '—').toLocaleString();
    }
    if (d.petrol) {
      document.getElementById('fuelPrice').textContent = 
        'Petrol: रु ' + (d.petrol || '—');
    }
    if (d.nepse) {
      document.getElementById('nepseIndex').textContent = 
        (d.nepse.index || '—') + ' (' + (d.nepse.change || '—') + ')';
    }
  })
  .catch(e => { console.error('Market load failed:', e); });

// Search functionality
document.getElementById('hubSearch').addEventListener('input', function(e) {
  const query = e.target.value.toLowerCase();
  if (!query) {
    document.querySelectorAll('.section-card').forEach(s => s.style.display = 'block');
    return;
  }
  
  // Simple search - can be enhanced
  const sections = ['news','notices','astro','finance','emergency','gov'];
  sections.forEach(id => {
    const el = document.getElementById(id + 'Section');
    if (el) {
      const text = el.textContent.toLowerCase();
      el.style.display = text.includes(query) ? 'block' : 'none';
    }
  });
});

if (window.lucide) lucide.createIcons();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
