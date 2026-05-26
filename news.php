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

<style>
.news-wrap{padding:14px 12px 110px;max-width:980px;margin:0 auto}
.news-hdr{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px;flex-wrap:wrap}
.news-hdr h1{font-size:22px;font-weight:800;color:#0b1220;margin:0;display:flex;align-items:center;gap:8px}
.news-hdr .live{font-size:10.5px;font-weight:700;background:#dc2626;color:#fff;padding:3px 8px;border-radius:999px;display:inline-flex;align-items:center;gap:4px}
.news-hdr .live::before{content:'';width:6px;height:6px;background:#fff;border-radius:50%;animation:nh-pulse 1.5s infinite}
@keyframes nh-pulse{0%,100%{opacity:1}50%{opacity:.35}}
.news-search{position:relative;flex:1;min-width:200px;max-width:360px}
.news-search input{width:100%;padding:9px 14px 9px 36px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;font-size:13.5px;color:#0b1220;outline:none;transition:border-color .15s,box-shadow .15s}
.news-search input:focus{border-color:#0d9488;box-shadow:0 0 0 3px rgba(13,148,136,.15)}
.news-search i{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#94a3b8}
.news-refresh{background:#0d9488;color:#fff;border:none;padding:8px 14px;border-radius:10px;font-size:12.5px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:5px;white-space:nowrap}
.news-refresh:hover{background:#0f766e}

.news-chips{display:flex;gap:7px;overflow-x:auto;padding:4px 0 10px;margin-bottom:6px;scrollbar-width:none}
.news-chips::-webkit-scrollbar{display:none}
.news-chip{flex-shrink:0;padding:8px 14px;border-radius:999px;font-size:12.5px;font-weight:600;background:#fff;color:#475569;border:1px solid #e2e8f0;cursor:pointer;text-decoration:none;white-space:nowrap;display:inline-flex;align-items:center;gap:5px;transition:all .15s}
.news-chip:hover{border-color:#0d9488;color:#0d9488}
.news-chip.active{background:#0f172a;color:#fff;border-color:#0f172a}

.news-srcbar{display:flex;gap:6px;overflow-x:auto;padding:2px 0 12px;margin-bottom:6px;scrollbar-width:none}
.news-srcbar::-webkit-scrollbar{display:none}
.news-src{flex-shrink:0;padding:5px 11px;border-radius:8px;font-size:11.5px;font-weight:600;background:#f8fafc;color:#64748b;border:1px solid #e2e8f0;cursor:pointer;white-space:nowrap;transition:all .15s}
.news-src:hover{border-color:#cbd5e1;color:#0b1220}
.news-src.active{background:#0d9488;color:#fff;border-color:#0d9488}

.news-list{display:flex;flex-direction:column;gap:12px}
.news-card{display:flex;gap:14px;background:#fff;border:1px solid #e6eaf2;border-radius:18px;padding:14px;text-decoration:none;color:inherit;transition:transform .15s,border-color .15s,box-shadow .15s;align-items:flex-start}
.news-card:hover{transform:translateY(-1px);border-color:#0d9488;box-shadow:0 4px 14px -8px rgba(13,148,136,.35)}
.news-card .thumb{width:110px;height:110px;flex-shrink:0;border-radius:14px;overflow:hidden;background:linear-gradient(135deg,#f0fdfa,#cffafe);position:relative}
.news-card .thumb img{width:100%;height:100%;object-fit:cover;display:block}
.news-card .body{flex:1;min-width:0;display:flex;flex-direction:column;gap:6px}
.news-card .meta{font-size:11px;color:#64748b;display:flex;align-items:center;gap:6px;flex-wrap:wrap}
.news-card .meta .cat{color:#0f766e;font-weight:700;font-size:10.5px;background:#ccfbf1;padding:2px 8px;border-radius:6px}
.news-card .meta .src{font-weight:600;color:#475569;background:#f1f5f9;padding:2px 8px;border-radius:6px}
.news-card .title{font-size:15.5px;font-weight:700;line-height:1.45;color:#0b1220;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;letter-spacing:-.01em}
.news-card .sum{font-size:12.5px;color:#475569;line-height:1.6;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
.news-card .foot{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:6px}
.news-card .time{font-size:11px;color:#94a3b8;display:flex;align-items:center;gap:4px}
.news-card .srclink{font-size:11px;font-weight:700;color:#0d9488;display:inline-flex;align-items:center;gap:3px;text-decoration:none;background:#f0fdfa;padding:3px 8px;border-radius:8px;border:1px solid #ccfbf1}
.news-card .srclink:hover{background:#0d9488;color:#fff;border-color:#0d9488}

@media (max-width: 520px){
  .news-card{padding:12px;gap:11px;border-radius:14px}
  .news-card .thumb{width:88px;height:88px;border-radius:11px}
  .news-card .title{font-size:14px;-webkit-line-clamp:3}
  .news-card .sum{-webkit-line-clamp:3;font-size:12px}
}

.news-skel{background:#fff;border:1px solid #e6eaf2;border-radius:18px;padding:14px;display:flex;gap:14px}
.news-skel .s1{width:110px;height:110px;border-radius:14px;background:linear-gradient(90deg,#f1f5f9,#e2e8f0,#f1f5f9);background-size:200% 100%;animation:nh-shim 1.4s infinite}
.news-skel .s2{flex:1;display:flex;flex-direction:column;gap:8px}
.news-skel .s2 i{height:12px;border-radius:6px;background:linear-gradient(90deg,#f1f5f9,#e2e8f0,#f1f5f9);background-size:200% 100%;animation:nh-shim 1.4s infinite;display:block}
.news-skel .s2 i:nth-child(1){width:40%;height:10px}
.news-skel .s2 i:nth-child(2){width:95%}
.news-skel .s2 i:nth-child(3){width:80%}
@keyframes nh-shim{0%{background-position:200% 0}100%{background-position:-200% 0}}

.news-empty{text-align:center;padding:50px 16px;color:#64748b;background:#fff;border:1px dashed #e2e8f0;border-radius:14px}
.news-empty i{display:block;margin:0 auto 10px;color:#cbd5e1}
.news-more{display:flex;justify-content:center;margin-top:14px}
.news-more button{background:#fff;color:#0d9488;border:1px solid #0d9488;padding:10px 22px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px}
.news-more button:hover{background:#0d9488;color:#fff}
.news-count{font-size:11.5px;color:#94a3b8;text-align:center;margin-top:10px}
</style>

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
        ? '<div class="thumb"><img src="'+escapeHtml(it.image)+'" alt="" loading="lazy" onerror="this.parentNode.style.display=\'none\'"></div>'
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
