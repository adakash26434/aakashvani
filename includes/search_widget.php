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
