<?php
/**
 * news.php — LIVE Nepali news listing (RSS-powered)
 * v4 — added fatal-error safety net so partial-load 500s become graceful pages
 */
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
register_shutdown_function(function () {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!headers_sent()) { header('Content-Type: text/html; charset=utf-8'); }
        error_log('[news.php fatal] '.$e['message'].' @ '.$e['file'].':'.$e['line']);
        echo '<div style="max-width:680px;margin:40px auto;padding:24px;font-family:system-ui,-apple-system,sans-serif;background:#fff;border:1px solid #fee2e2;border-radius:14px;color:#0b1220">'
            .'<h2 style="margin:0 0 8px;font-size:20px">समाचार लोड हुन सकेन</h2>'
            .'<p style="margin:0 0 12px;color:#475569;font-size:14px">केहि बेर पछि फेरि प्रयास गर्नुहोस्। तपाईं तुरुन्तै <a href="/" style="color:#0d9488;font-weight:600">गृहपृष्ठ</a> मा फर्कन सक्नुहुन्छ।</p>'
            .'<a href="/news.php" style="display:inline-block;background:#0d9488;color:#fff;padding:9px 16px;border-radius:10px;text-decoration:none;font-weight:600;font-size:13px">पुनः लोड गर्नुस्</a>'
            .'</div>';
    }
});

$pageTitle = 'समाचार · आकाशवाणी';
$pageDesc  = 'OnlineKhabar, Setopati, Ratopati, Kantipur, ShareSansar, TechPana, BBC नेपाली लगायत २०+ स्रोतबाट live समाचार।';
$pageImg   = '/assets/images/og-image.jpg';
@include __DIR__ . '/includes/header.php';

$cats = [
  ['all','सबै','📰'],
  ['politics','राजनीति','🏛️'],
  ['economy','अर्थ / बजार','💰'],
  ['sports','खेलकुद','⚽'],
  ['entertainment','मनोरञ्जन','🎬'],
  ['technology','प्रविधि','💻'],
  ['world','विश्व','🌏'],
];
$activeCat = isset($_GET['cat']) ? strtolower(trim($_GET['cat'])) : 'all';
?>

<div class="news-wrap">
  <div class="news-hdr">
    <h1 class="ne">
      <i data-lucide="newspaper" class="w-5 h-5" style="color:#0d9488"></i>
      ताजा समाचार
      <span class="live">LIVE</span>
    </h1>
    <div class="news-search">
      <i data-lucide="search" class="w-4 h-4"></i>
      <input type="search" id="newsSearch" placeholder="शीर्षक खोज्नुस्…" class="ne" />
    </div>
    <button class="news-refresh" onclick="loadNews(true)" title="Refresh">
      <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> <span class="ne">पुनः</span>
    </button>
  </div>

  <div class="news-chips" id="newsChips">
    <?php foreach ($cats as $c): ?>
      <a href="?cat=<?= $c[0] ?>" class="news-chip ne <?= $activeCat === $c[0] ? 'active' : '' ?>" data-cat="<?= $c[0] ?>">
        <span><?= $c[2] ?></span> <?= htmlspecialchars($c[1], ENT_QUOTES, 'UTF-8') ?>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="news-srcbar" id="newsSrcBar">
    <button class="news-src active" data-src="">सबै स्रोत</button>
  </div>

  <div class="news-list" id="newsList">
    <?php for ($i=0; $i<5; $i++): ?>
      <div class="news-skel"><div class="s1"></div><div class="s2"><i></i><i></i><i></i></div></div>
    <?php endfor; ?>
  </div>

  <div class="news-more" id="newsMoreWrap" style="display:none">
    <button id="newsMoreBtn"><i data-lucide="chevron-down" class="w-4 h-4"></i> <span class="ne">अरू देखाउनुस्</span></button>
  </div>
  <div class="news-count" id="newsCount"></div>
</div>

<script>
(function(){
  var active = <?= json_encode($activeCat) ?>;
  var allItems = [];
  var srcFilter = '';
  var query = '';
  var visible = 20;

  function timeAgo(ts){
    if(!ts) return '';
    var d = (Date.now()/1000) - ts;
    if(d<60) return 'भर्खरै';
    if(d<3600) return Math.floor(d/60)+' मिनेट अघि';
    if(d<86400) return Math.floor(d/3600)+' घण्टा अघि';
    return Math.floor(d/86400)+' दिन अघि';
  }
  function catLabel(c){
    var m = {politics:'राजनीति',economy:'अर्थ',sports:'खेलकुद',entertainment:'मनोरञ्जन',technology:'प्रविधि',world:'विश्व',general:'समाचार'};
    return m[c] || 'समाचार';
  }
  function escapeHtml(s){return String(s).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];});}

  function filtered(){
    var q = query.trim().toLowerCase();
    return allItems.filter(function(it){
      if(srcFilter && it.source !== srcFilter) return false;
      if(q && (it.title||'').toLowerCase().indexOf(q) === -1 && (it.summary||'').toLowerCase().indexOf(q) === -1) return false;
      return true;
    });
  }

  function buildSrcBar(){
    var bar = document.getElementById('newsSrcBar');
    var seen = {};
    var sources = [];
    allItems.forEach(function(it){
      var k = it.source || '';
      if(!k || seen[k]) return;
      seen[k] = 1;
      sources.push({key:k, label: it.sourceLabel || k});
    });
    sources.sort(function(a,b){ return a.label.localeCompare(b.label); });
    var html = '<button class="news-src '+(srcFilter===''?'active':'')+'" data-src="">सबै स्रोत</button>';
    sources.forEach(function(s){
      html += '<button class="news-src '+(srcFilter===s.key?'active':'')+'" data-src="'+escapeHtml(s.key)+'">'+escapeHtml(s.label)+'</button>';
    });
    bar.innerHTML = html;
    bar.querySelectorAll('.news-src').forEach(function(b){
      b.addEventListener('click', function(){
        srcFilter = this.getAttribute('data-src') || '';
        visible = 20;
        buildSrcBar();
        render();
      });
    });
  }

  function render(){
    var items = filtered();
    var list = document.getElementById('newsList');
    var moreWrap = document.getElementById('newsMoreWrap');
    var countEl = document.getElementById('newsCount');

    if(!items.length){
      list.innerHTML = '<div class="news-empty"><i data-lucide="inbox" class="w-10 h-10"></i><p class="ne">कुनै समाचार भेटिएन</p></div>';
      moreWrap.style.display = 'none';
      countEl.textContent = '';
      if(window.lucide) lucide.createIcons();
      return;
    }

    var shown = items.slice(0, visible);
    var html = shown.map(function(it){
      var thumb = it.image
        ? '<div class="thumb"><img src="'+escapeHtml(it.image)+'" alt="'+escapeHtml(it.title || 'News image')+'" loading="lazy" onerror="this.parentNode.style.display=\'none\'"></div>'
        : '';
      var sum = it.summary ? '<div class="sum ne">'+escapeHtml(it.summary)+'</div>' : '';
      var ago = it.ago || timeAgo(it.pubDate);
      var detailUrl = it.internalUrl || (it.slug ? '/news-detail.php?slug=' + encodeURIComponent(it.slug) : '/news-detail.php?url=' + encodeURIComponent(it.link || '') + '&src=' + encodeURIComponent(it.sourceLabel || ''));
      return '<a href="'+detailUrl+'" class="news-card" data-nsh-open="1">'+
        thumb +
        '<div class="body">' +
          '<div class="meta"><span class="cat ne">'+escapeHtml(catLabel(it.cat))+'</span><span class="src ne">'+escapeHtml(it.sourceLabel||'')+'</span></div>'+
          '<div class="title ne">'+escapeHtml(it.title)+'</div>'+
          sum +
          '<div class="foot">'+
            '<div class="time"><i data-lucide="clock" class="w-3 h-3"></i> '+escapeHtml(ago)+'</div>'+
            '<span class="srclink ne">'+
              'हाम्रोमै पढ्नुहोस् <i data-lucide="book-open" class="w-3 h-3"></i>'+
            '</span>'+
          '</div>'+
        '</div>'+
      '</a>';
    }).join('');
    list.innerHTML = html;

    moreWrap.style.display = items.length > visible ? 'flex' : 'none';
    countEl.textContent = 'जम्मा ' + items.length + ' समाचार · देखाइएको ' + Math.min(visible, items.length);
    if(window.lucide) lucide.createIcons();
  }

  window.loadNews = function(force){
    var url = '/api/news-rss.php?cat='+encodeURIComponent(active)+'&limit=80' + (force?'&t='+Date.now():'');
    fetch(url, {credentials:'same-origin'})
      .then(function(r){
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
      })
      .then(function(d){
        if (d && d.ok && d.items && Array.isArray(d.items)) { 
          allItems = d.items; 
          buildSrcBar(); 
          render(); 
        } else { 
          allItems = []; 
          render(); 
          console.error('[news.php] Invalid API response', d);
        }
      })
      .catch(function(err){
        console.error('[news.php] Fetch error:', err);
        allItems = []; 
        render();
      });
  };

  // Search (debounced)
  var searchTimer;
  document.getElementById('newsSearch').addEventListener('input', function(e){
    clearTimeout(searchTimer);
    var v = e.target.value;
    searchTimer = setTimeout(function(){ query = v; visible = 20; render(); }, 180);
  });

  // Load more
  document.getElementById('newsMoreBtn').addEventListener('click', function(){
    visible += 20; render();
  });

  loadNews();
})();
</script>

<?php @include __DIR__ . '/includes/footer.php'; ?>
