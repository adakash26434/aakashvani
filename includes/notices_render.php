<?php
/**
 * Renders active app notices (modal + banner).
 * Include in header.php right after <body> tag:
 *   <?php require_once __DIR__ . '/includes/notices_render.php'; ?>
 *
 * - Modal: shows top-priority notice on first visit per session (or per notice if dismissed).
 * - Banner: pinned notices show as a sticky top ticker.
 * - "Don't show again" stores notice ID in localStorage.
 */
if (!function_exists('getActiveAppNotices')) {
    require_once __DIR__ . '/functions.notices.php';
}

$_notices = getActiveAppNotices(5);
if (!$_notices) return;

// Separate pinned (banner) from regular (modal)
$_pinned = array_values(array_filter($_notices, fn($n) => (int) $n['pin_top'] === 1));
$_modals = array_values(array_filter($_notices, fn($n) => in_array($n['display_mode'], ['modal','both'], true) && (int) $n['pin_top'] !== 1));
// "both" pinned should also appear as modal once
foreach ($_notices as $n) {
    if ($n['display_mode'] === 'both' && (int)$n['pin_top'] === 1) $_modals[] = $n;
}

$_typeStyles = [
    'info'        => ['icon' => 'ℹ️', 'color' => '#0891b2', 'bg' => '#cffafe'],
    'success'     => ['icon' => '✅', 'color' => '#059669', 'bg' => '#d1fae5'],
    'warning'     => ['icon' => '⚠️', 'color' => '#d97706', 'bg' => '#fef3c7'],
    'urgent'      => ['icon' => '🚨', 'color' => '#dc2626', 'bg' => '#fee2e2'],
    'janachetana' => ['icon' => '📢', 'color' => '#7c3aed', 'bg' => '#ede9fe'],
];

// Track views (best-effort)
foreach ($_notices as $n) { trackNoticeView((int) $n['id']); }
?>
<style>
  /* Banner ticker */
  .notice-banner{position:sticky;top:0;z-index:9998;display:flex;align-items:center;gap:10px;padding:10px 16px;font-family:system-ui,-apple-system,'Noto Sans Devanagari',sans-serif;font-size:14px;border-bottom:1px solid rgba(0,0,0,.06)}
  .notice-banner .ico{font-size:18px;flex-shrink:0}
  .notice-banner .msg{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .notice-banner .msg b{margin-right:6px}
  .notice-banner a{color:inherit;text-decoration:underline;margin-left:6px}
  .notice-banner .x{background:none;border:none;color:inherit;cursor:pointer;font-size:18px;padding:0 4px;opacity:.7}
  .notice-banner .x:hover{opacity:1}

  /* Modal */
  .notice-modal-bg{position:fixed;inset:0;background:rgba(15,23,42,.6);backdrop-filter:blur(4px);z-index:9999;display:none;align-items:center;justify-content:center;padding:16px;font-family:system-ui,-apple-system,'Noto Sans Devanagari',sans-serif;animation:nFade .25s ease}
  .notice-modal-bg.show{display:flex}
  @keyframes nFade{from{opacity:0}to{opacity:1}}
  @keyframes nSlide{from{transform:translateY(20px);opacity:0}to{transform:translateY(0);opacity:1}}
  .notice-modal{background:#fff;border-radius:16px;max-width:520px;width:100%;max-height:90vh;overflow:auto;box-shadow:0 20px 60px -10px rgba(0,0,0,.4);animation:nSlide .3s ease}
  .notice-modal .head{padding:20px 24px 12px;display:flex;align-items:flex-start;gap:14px;border-bottom:1px solid #f1f5f9}
  .notice-modal .head .big-ico{font-size:32px;flex-shrink:0}
  .notice-modal .head h3{margin:0;font-size:20px;color:#0f172a;line-height:1.3}
  .notice-modal .head .type-pill{display:inline-block;font-size:11px;font-weight:700;padding:2px 10px;border-radius:20px;margin-top:6px;text-transform:uppercase;letter-spacing:.5px}
  .notice-modal .body{padding:18px 24px;color:#334155;font-size:15px;line-height:1.7}
  .notice-modal .body p{margin:0 0 10px}
  .notice-modal .doc{display:flex;align-items:center;gap:12px;padding:12px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;margin-top:14px;text-decoration:none;color:#0f172a}
  .notice-modal .doc:hover{background:#f1f5f9;border-color:#94a3b8}
  .notice-modal .doc .di{font-size:24px}
  .notice-modal .doc .dn{flex:1;font-size:14px;font-weight:600}
  .notice-modal .doc .ds{font-size:11px;color:#64748b}
  .notice-modal .actions{padding:14px 24px 20px;display:flex;justify-content:space-between;align-items:center;gap:12px;border-top:1px solid #f1f5f9}
  .notice-modal .actions .left{display:flex;align-items:center;gap:6px;font-size:13px;color:#64748b}
  .notice-modal .actions button,.notice-modal .actions a.btn{padding:9px 18px;border-radius:8px;font:inherit;font-size:14px;cursor:pointer;border:none;text-decoration:none;display:inline-block}
  .notice-modal .actions .primary{background:#0f766e;color:#fff;font-weight:600}
  .notice-modal .actions .primary:hover{background:#0d5d56}
  .notice-modal .actions .ghost{background:#f1f5f9;color:#475569}
  .notice-modal .actions .ghost:hover{background:#e2e8f0}
</style>

<?php /* ─── BANNER (pinned) ─── */
foreach ($_pinned as $n):
    $st = $_typeStyles[$n['type']] ?? $_typeStyles['info'];
?>
<div class="notice-banner" id="ntcB<?= $n['id'] ?>" style="background:<?= $st['bg'] ?>;color:<?= $st['color'] ?>">
  <span class="ico"><?= $st['icon'] ?></span>
  <span class="msg"><b><?= htmlspecialchars($n['title']) ?></b><?= htmlspecialchars(mb_substr(strip_tags($n['body']), 0, 200, 'UTF-8')) ?>
    <?php if (!empty($n['document_path'])): ?>
      <a href="<?= htmlspecialchars($n['document_path']) ?>" target="_blank" rel="noopener" onclick="ntcClick(<?= $n['id'] ?>)">📎 Document</a>
    <?php endif; ?>
    <?php if (!empty($n['cta_url']) && !empty($n['cta_label'])): ?>
      <a href="<?= htmlspecialchars($n['cta_url']) ?>" onclick="ntcClick(<?= $n['id'] ?>)"><?= htmlspecialchars($n['cta_label']) ?></a>
    <?php endif; ?>
  </span>
  <?php if ((int)$n['dismissible'] === 1): ?>
    <button class="x" onclick="ntcDismiss(<?= $n['id'] ?>,'banner')" title="बन्द गर्नुहोस्">✕</button>
  <?php endif; ?>
</div>
<?php endforeach; ?>

<?php /* ─── MODAL (one at a time, highest priority first) ─── */
$_modal = $_modals[0] ?? null;
if ($_modal):
    $st = $_typeStyles[$_modal['type']] ?? $_typeStyles['info'];
?>
<div class="notice-modal-bg" id="ntcM<?= $_modal['id'] ?>" data-notice-id="<?= $_modal['id'] ?>">
  <div class="notice-modal">
    <div class="head">
      <div class="big-ico"><?= $st['icon'] ?></div>
      <div>
        <h3><?= htmlspecialchars($_modal['title']) ?></h3>
        <span class="type-pill" style="background:<?= $st['bg'] ?>;color:<?= $st['color'] ?>"><?= htmlspecialchars($_modal['type']) ?></span>
      </div>
    </div>
    <div class="body">
      <?= nl2br(htmlspecialchars($_modal['body'])) ?>
      <?php if (!empty($_modal['document_path'])):
        $sizeKb = !empty($_modal['document_size']) ? round($_modal['document_size']/1024) . ' KB' : '';
      ?>
      <a class="doc" href="<?= htmlspecialchars($_modal['document_path']) ?>" target="_blank" rel="noopener" onclick="ntcClick(<?= $_modal['id'] ?>)">
        <span class="di">📎</span>
        <div style="flex:1">
          <div class="dn"><?= htmlspecialchars($_modal['document_name'] ?: 'सम्बन्धित कागजात') ?></div>
          <div class="ds">क्लिक गरेर हेर्नुहोस् · <?= $sizeKb ?></div>
        </div>
        <span>↗</span>
      </a>
      <?php endif; ?>
    </div>
    <div class="actions">
      <label class="left">
        <input type="checkbox" id="ntcNoShow<?= $_modal['id'] ?>"> फेरि नदेखाउनुहोस्
      </label>
      <div>
        <?php if (!empty($_modal['cta_url']) && !empty($_modal['cta_label'])): ?>
          <a class="btn primary" href="<?= htmlspecialchars($_modal['cta_url']) ?>" onclick="ntcClick(<?= $_modal['id'] ?>)"><?= htmlspecialchars($_modal['cta_label']) ?></a>
        <?php endif; ?>
        <?php if ((int)$_modal['dismissible'] === 1): ?>
          <button class="ghost" onclick="ntcDismiss(<?= $_modal['id'] ?>,'modal')">ठिक छ</button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<script>
(function(){
  var id = <?= (int) $_modal['id'] ?>;
  var key = 'ntc_seen_' + id;
  try {
    if (localStorage.getItem(key) === 'never') return;
  } catch(e){}
  // Show after a tiny delay so it doesn't fight first paint
  setTimeout(function(){
    var el = document.getElementById('ntcM' + id);
    if (el) el.classList.add('show');
  }, 350);
})();
function ntcDismiss(id, kind){
  var el = document.getElementById((kind === 'modal' ? 'ntcM' : 'ntcB') + id);
  if (el) el.style.display = 'none';
  if (kind === 'modal') {
    var cb = document.getElementById('ntcNoShow' + id);
    if (cb && cb.checked) {
      try { localStorage.setItem('ntc_seen_' + id, 'never'); } catch(e){}
    }
  }
}
function ntcClick(id){
  try {
    fetch('/api-notice-click.php?id=' + id, {method:'POST', keepalive:true});
  } catch(e){}
}
// Close modal on backdrop click
document.addEventListener('click', function(e){
  if (e.target.classList && e.target.classList.contains('notice-modal-bg')) {
    var id = e.target.getAttribute('data-notice-id');
    ntcDismiss(id, 'modal');
  }
});
</script>
<?php endif; ?>
