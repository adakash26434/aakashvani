<?php
/**
 * sources.php — Sources, attribution & legal page
 * Lists every data source Aakashvani uses with attribution + terms link.
 */
$pageTitle = 'Sources & Attribution · आकाशवाणी';
$pageDesc  = 'हाम्रो app ले प्रयोग गर्ने सबै data sources, license, र attribution विवरण।';
@include __DIR__ . '/includes/header.php';

$sources = [
  ['name'=>'Hamro Patro',         'cat'=>'सुन/चाँदी मूल्य',     'url'=>'https://www.hamropatro.com/gold',         'usage'=>'Daily gold (Hallmark/Tajbi) र silver per-tola दर',                'license'=>'Public daily rates'],
  ['name'=>'NOC Nepal',           'cat'=>'इन्धन मूल्य',          'url'=>'https://www.nepaloil.com.np',             'usage'=>'पेट्रोल, डिजेल, मट्टितेल, हवाई इन्धन, LPG आधिकारिक मूल्य',         'license'=>'Government of Nepal (public)'],
  ['name'=>'Nepal Rastra Bank',   'cat'=>'विदेशी मुद्रा',        'url'=>'https://www.nrb.org.np/forex/',          'usage'=>'USD-NPR र अन्य currency exchange rates',                            'license'=>'Government of Nepal (public)'],
  ['name'=>'FENEGOSIDA',          'cat'=>'सुन/चाँदी (reference)','url'=>'https://www.fenegosida.org',              'usage'=>'Federation of Nepal Gold & Silver Dealers — official daily rate',  'license'=>'Public association data'],
  ['name'=>'BIPAD Portal (NDRRMA)','cat'=>'सरकारी चेतावनी',     'url'=>'https://bipadportal.gov.np',              'usage'=>'बाढी, पहिरो, आगो र मौसम चेतावनी (live alerts)',                  'license'=>'Government of Nepal, Open API'],
  ['name'=>'DHM Nepal',           'cat'=>'मौसम (आधिकारिक)',     'url'=>'https://www.dhm.gov.np',                  'usage'=>'Department of Hydrology & Meteorology — official forecasts',       'license'=>'Government of Nepal (public)'],
  ['name'=>'Open-Meteo',          'cat'=>'मौसम (forecast)',      'url'=>'https://open-meteo.com',                  'usage'=>'Current weather + 3-day forecast (काठमाडौं)',                     'license'=>'CC-BY 4.0 (Free, no API key)'],
  ['name'=>'USGS',                'cat'=>'भूकम्प',               'url'=>'https://earthquake.usgs.gov',             'usage'=>'Nepal-region earthquakes (M≥3, last 14 days)',                     'license'=>'U.S. Geological Survey (Public domain)'],
  ['name'=>'OnlineKhabar',        'cat'=>'समाचार',               'url'=>'https://www.onlinekhabar.com',            'usage'=>'RSS — शीर्षक, सारांश, थम्बनेल मात्र (full link स्रोतमै)',         'license'=>'© OnlineKhabar — fair use under RSS terms'],
  ['name'=>'Setopati',            'cat'=>'समाचार',               'url'=>'https://www.setopati.com',                'usage'=>'RSS — शीर्षक, सारांश, थम्बनेल मात्र',                              'license'=>'© Setopati — fair use'],
  ['name'=>'Ratopati',            'cat'=>'समाचार',               'url'=>'https://www.ratopati.com',                'usage'=>'RSS — शीर्षक, सारांश, थम्बनेल मात्र',                              'license'=>'© Ratopati — fair use'],
  ['name'=>'BBC नेपाली',          'cat'=>'समाचार',               'url'=>'https://www.bbc.com/nepali',              'usage'=>'RSS — विश्व समाचार nepali भाषामा',                                 'license'=>'© BBC — fair use'],
  ['name'=>'eSewa',               'cat'=>'भुक्तानी (link)',      'url'=>'https://esewa.com.np',                    'usage'=>'External deep-link मात्र — payment processing eSewa मै',          'license'=>'Third-party service'],
  ['name'=>'Khalti',              'cat'=>'भुक्तानी (link)',      'url'=>'https://khalti.com',                      'usage'=>'External deep-link मात्र',                                          'license'=>'Third-party service'],
  ['name'=>'Nagarik App',         'cat'=>'सरकारी सेवा (link)',  'url'=>'https://nagarikapp.gov.np',               'usage'=>'PAN, राहदानी, सवारी, नागरिकता — सरकारी portal link',              'license'=>'Government of Nepal'],
  ['name'=>'Lok Sewa Aayog',      'cat'=>'विज्ञापन (link)',     'url'=>'https://psc.gov.np',                      'usage'=>'PSC notices, exams, results — आधिकारिक link',                       'license'=>'Government of Nepal'],
  ['name'=>'Lucide Icons',        'cat'=>'Design',               'url'=>'https://lucide.dev',                      'usage'=>'Icon set',                                                          'license'=>'ISC License'],
  ['name'=>'Tailwind CSS',        'cat'=>'Design',               'url'=>'https://tailwindcss.com',                 'usage'=>'CSS framework',                                                     'license'=>'MIT License'],
];
?>

<div class="src-wrap">
  <div class="src-hero">
    <h1 class="ne">📚 Sources र Attribution</h1>
    <p class="ne">Aakashvani कुनै पनि data आफै बनाउँदैन। हामी आधिकारिक सरकारी, मिडिया, र free open-data sources बाट जानकारी ल्याउँछौँ — सबै source credit दिएर।</p>
  </div>

  <div class="src-grid">
  <?php foreach ($sources as $i=>$s): ?>
    <div class="src-card">
      <div class="num"><?= $i+1 ?></div>
      <div class="body">
        <div class="top"><h3><?= htmlspecialchars($s['name']) ?></h3><span class="cat ne"><?= htmlspecialchars($s['cat']) ?></span></div>
        <div class="usage ne"><?= htmlspecialchars($s['usage']) ?></div>
        <div class="lic ne">📜 <?= htmlspecialchars($s['license']) ?> · <a class="link" href="<?= htmlspecialchars($s['url']) ?>" target="_blank" rel="noopener nofollow">वेबसाइट खोल्नुहोस् ↗</a></div>
      </div>
    </div>
  <?php endforeach; ?>
  </div>

  <div class="legal-card">
    <h2 class="ne">⚖️ कानूनी नीति (Legal Policy)</h2>
    <p class="ne"><strong>१. Fair Use (नेपाल Copyright Act 2059):</strong> समाचारको शीर्षक, सारांश (excerpt), र थम्बनेल RSS/OpenGraph feed बाट लिइन्छ — यो प्रकाशकले खुला रूपमा syndication को लागि दिएको content हो। पूरा लेख हाम्रो server मा store हुँदैन; user ले "पढ्नुहोस्" मा click गर्दा मूल publisher कै site मा पुग्छ।</p>
    <p class="ne"><strong>२. Attribution सधैं देखाइन्छ:</strong> हरेक news item मा source name + logo + canonical URL छ। हाम्रो news-detail page ले <code>rel="canonical"</code> मूल URL तर्फ point गर्छ।</p>
    <p class="ne"><strong>३. सरकारी डाटा (NOC, NRB, BIPAD, DHM, USGS):</strong> सार्वजनिक data हो — government license अनुसार free redistribution allowed।</p>
    <p class="ne"><strong>४. हटाउने अनुरोध (Takedown):</strong> कुनै publisher ले आफ्नो content हटाउन चाहेमा, तल contact गर्नुहोस् — २४ घण्टा भित्र हट्छ।</p>
  </div>

  <div class="contact ne">
    <strong>📧 सम्पर्क / Contact:</strong> Takedown, attribution correction, वा data partnership को लागि →
    <a href="mailto:contact@tankaadhikari.com.np">contact@tankaadhikari.com.np</a>
  </div>
</div>

<?php @include __DIR__ . '/includes/footer.php'; ?>
