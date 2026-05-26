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
