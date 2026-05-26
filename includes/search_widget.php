<?php
/**
 * Global Search Widget — drop into header.php inside the nav bar.
 * Usage:
 *   <?php require __DIR__ . '/includes/search_widget.php'; ?>
 *
 * Features:
 *  - Type any Nepali / English keyword
 *  - Live autocomplete dropdown with icons (📻 🏆 🏔️ 📢 etc.)
 *  - Keyboard nav: ↑ ↓ Enter Esc
 *  - Mobile-friendly
 *  - Press "/" anywhere to focus
 */
?>
<div class="aak-search" id="aakSearch">
  <span class="aak-search-icon">🔎</span>
  <input id="aakSearchInput" type="search" autocomplete="off" spellcheck="false"
         placeholder="खोज्नुहोस् — रेडियो, कथा, घुम्ने ठाउँ... ( / दबाउनुहोस् )">
  <kbd class="aak-search-kbd">/</kbd>
  <div class="aak-search-drop" id="aakSearchDrop" role="listbox"></div>
</div>
<style>
.aak-search{position:relative;display:flex;align-items:center;gap:8px;width:100%;max-width:480px;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:8px 12px;font-family:system-ui,-apple-system,'Noto Sans Devanagari',sans-serif;transition:border-color .15s,box-shadow .15s}
.aak-search:focus-within{border-color:#0f766e;box-shadow:0 0 0 3px rgba(15,118,110,.15)}
.aak-search-icon{font-size:16px;opacity:.6}
.aak-search input{flex:1;min-width:0;border:0;outline:0;background:transparent;font:inherit;font-size:14px;color:#0f172a}
.aak-search input::placeholder{color:#94a3b8;font-size:13px}
.aak-search-kbd{background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;border-radius:6px;padding:2px 7px;font-size:11px;font-family:ui-monospace,SFMono-Regular,monospace}
.aak-search-drop{position:absolute;top:calc(100% + 6px);left:0;right:0;background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 12px 32px -8px rgba(15,23,42,.18);max-height:420px;overflow:auto;display:none;z-index:9997}
.aak-search-drop.show{display:block}
.aak-sr-group{padding:8px 14px 4px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;background:#f8fafc;border-bottom:1px solid #f1f5f9}
.aak-sr-item{display:flex;align-items:center;gap:12px;padding:10px 14px;cursor:pointer;border-bottom:1px solid #f8fafc;text-decoration:none;color:#0f172a}
.aak-sr-item:hover,.aak-sr-item.active{background:#f0fdfa}
.aak-sr-ico{font-size:20px;width:28px;text-align:center;flex-shrink:0}
.aak-sr-txt{flex:1;min-width:0}
.aak-sr-lbl{font-size:14px;font-weight:600;color:#0f172a;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.aak-sr-sub{font-size:11px;color:#64748b;margin-top:2px}
.aak-sr-arrow{font-size:12px;color:#94a3b8}
.aak-sr-empty{padding:24px;text-align:center;color:#94a3b8;font-size:13px}
.aak-sr-load{padding:14px;text-align:center;color:#0f766e;font-size:13px}
@media (max-width:640px){.aak-search-kbd{display:none}}
</style>
<script>
(function(){
  var input = document.getElementById('aakSearchInput');
  var drop  = document.getElementById('aakSearchDrop');
  if (!input || !drop) return;

  var timer = null, ctrl = null, items = [], cursor = -1;

  function escapeHtml(s){return (s||'').replace(/[&<>"']/g,function(c){return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c];});}

  function render(list){
    items = list;
    cursor = -1;
    if (!list.length){
      drop.innerHTML = '<div class="aak-sr-empty">कुनै नतिजा भेटिएन।</div>';
      drop.classList.add('show');
      return;
    }
    // group by type label
    var groups = {menu:'मेनु', story:'सफलताका कथा', place:'घुम्ने ठाउँ', radio:'रेडियो', notice:'सूचना'};
    var byType = {};
    list.forEach(function(it){ (byType[it.type] = byType[it.type] || []).push(it); });
    var html = '';
    ['menu','notice','story','place','radio'].forEach(function(t){
      if (!byType[t]) return;
      html += '<div class="aak-sr-group">'+groups[t]+'</div>';
      byType[t].forEach(function(it){
        html += '<a class="aak-sr-item" href="'+escapeHtml(it.url)+'">'+
                '<span class="aak-sr-ico">'+escapeHtml(it.icon||'•')+'</span>'+
                '<span class="aak-sr-txt"><div class="aak-sr-lbl">'+escapeHtml(it.label)+'</div>'+
                (it.sub?'<div class="aak-sr-sub">'+escapeHtml(it.sub)+'</div>':'')+'</span>'+
                '<span class="aak-sr-arrow">↗</span></a>';
      });
    });
    drop.innerHTML = html;
    drop.classList.add('show');
  }

  function search(q){
    if (ctrl) ctrl.abort();
    if (!q){ drop.classList.remove('show'); return; }
    drop.innerHTML = '<div class="aak-sr-load">खोज्दै...</div>';
    drop.classList.add('show');
    ctrl = new AbortController();
    fetch('/api-search.php?q='+encodeURIComponent(q), {signal: ctrl.signal})
      .then(function(r){ return r.json(); })
      .then(render)
      .catch(function(){});
  }

  input.addEventListener('input', function(){
    clearTimeout(timer);
    var v = input.value.trim();
    timer = setTimeout(function(){ search(v); }, 180);
  });
  input.addEventListener('focus', function(){
    if (input.value.trim()) drop.classList.add('show');
  });
  input.addEventListener('keydown', function(e){
    var rows = drop.querySelectorAll('.aak-sr-item');
    if (e.key === 'ArrowDown'){ e.preventDefault(); cursor = Math.min(cursor+1, rows.length-1); }
    else if (e.key === 'ArrowUp'){ e.preventDefault(); cursor = Math.max(cursor-1, 0); }
    else if (e.key === 'Enter'){
      if (cursor >= 0 && rows[cursor]){ e.preventDefault(); window.location.href = rows[cursor].getAttribute('href'); }
    } else if (e.key === 'Escape'){ drop.classList.remove('show'); input.blur(); return; }
    else return;
    rows.forEach(function(r,i){ r.classList.toggle('active', i===cursor); });
    if (rows[cursor]) rows[cursor].scrollIntoView({block:'nearest'});
  });

  // Click outside closes
  document.addEventListener('click', function(e){
    if (!document.getElementById('aakSearch').contains(e.target)) drop.classList.remove('show');
  });
  // "/" shortcut
  document.addEventListener('keydown', function(e){
    if (e.key === '/' && document.activeElement !== input && !/input|textarea/i.test((document.activeElement||{}).tagName||'')){
      e.preventDefault(); input.focus();
    }
  });
})();
</script>
