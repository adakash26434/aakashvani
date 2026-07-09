<?php
/**
 * /admin-notices.php — Manage app-wide pop-up notices
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/includes/functions.notices.php';

if (empty($_SESSION['is_admin'])) {
    header('Location: /admin/index.php?next=' . basename(__FILE__)); exit;
}
require_once __DIR__ . '/includes/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrfVerify()) { die('CSRF verification failed'); }

    header('Location: /admin/index.php?next=' . basename(__FILE__)); exit;
}

$msg = '';
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ── CREATE / UPDATE ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['create','update'], true)) {
    $id           = (int) ($_POST['id'] ?? 0);
    $title        = trim($_POST['title'] ?? '');
    $body         = trim($_POST['body'] ?? '');
    $type         = $_POST['type'] ?? 'info';
    $display_mode = $_POST['display_mode'] ?? 'modal';
    $dismissible  = isset($_POST['dismissible']) ? 1 : 0;
    $pin_top      = isset($_POST['pin_top']) ? 1 : 0;
    $priority     = (int) ($_POST['priority'] ?? 0);
    $active       = isset($_POST['active']) ? 1 : 0;
    $show_from    = $_POST['show_from'] ?: null;
    $show_until   = $_POST['show_until'] ?: null;
    $cta_label    = trim($_POST['cta_label'] ?? '') ?: null;
    $cta_url      = trim($_POST['cta_url'] ?? '') ?: null;

    if (!$title || !$body) {
        $msg = '<div class="err">शीर्षक र विवरण आवश्यक छ।</div>';
    } else {
        $doc = null;
        if (!empty($_FILES['document']['tmp_name'])) {
            $doc = notice_save_document($_FILES['document']);
            if (!$doc) $msg = '<div class="err">कागजात अपलोड असफल (PDF/JPG/PNG/DOC, ≤12MB)।</div>';
        }

        if ($action === 'create') {
            $sql = "INSERT INTO app_notices (title,body,type,display_mode,dismissible,pin_top,priority,active,show_from,show_until,cta_label,cta_url,document_path,document_name,document_size,document_mime,created_by)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            db()->prepare($sql)->execute([
                $title,$body,$type,$display_mode,$dismissible,$pin_top,$priority,$active,$show_from,$show_until,$cta_label,$cta_url,
                $doc['path'] ?? null, $doc['name'] ?? null, $doc['size'] ?? null, $doc['mime'] ?? null,
                $_SESSION['admin_user'] ?? 'admin'
            ]);
            $msg = '<div class="ok">✓ नयाँ सूचना थपियो।</div>';
        } else {
            $sets = ['title=?','body=?','type=?','display_mode=?','dismissible=?','pin_top=?','priority=?','active=?','show_from=?','show_until=?','cta_label=?','cta_url=?'];
            $vals = [$title,$body,$type,$display_mode,$dismissible,$pin_top,$priority,$active,$show_from,$show_until,$cta_label,$cta_url];
            if ($doc) {
                // delete old doc
                $old = db()->prepare("SELECT document_path FROM app_notices WHERE id=?");
                $old->execute([$id]);
                if ($r = $old->fetch(PDO::FETCH_ASSOC)) notice_delete_document($r['document_path']);
                $sets[] = 'document_path=?'; $sets[] = 'document_name=?'; $sets[] = 'document_size=?'; $sets[] = 'document_mime=?';
                array_push($vals, $doc['path'], $doc['name'], $doc['size'], $doc['mime']);
            }
            $vals[] = $id;
            db()->prepare("UPDATE app_notices SET " . implode(',', $sets) . " WHERE id=?")->execute($vals);
            $msg = '<div class="ok">✓ अद्यावधिक भयो।</div>';
        }
    }
}

if ($action === 'delete' && !empty($_GET['id'])) {
    $id = (int) $_GET['id'];
    $r = db()->prepare("SELECT document_path FROM app_notices WHERE id=?");
    $r->execute([$id]);
    if ($row = $r->fetch(PDO::FETCH_ASSOC)) notice_delete_document($row['document_path']);
    db()->prepare("DELETE FROM app_notices WHERE id=?")->execute([$id]);
    $msg = '<div class="ok">✓ हटाइयो।</div>';
}

if ($action === 'toggle' && !empty($_GET['id'])) {
    db()->prepare("UPDATE app_notices SET active = 1 - active WHERE id=?")->execute([(int)$_GET['id']]);
    $msg = '<div class="ok">✓ स्थिति परिवर्तन भयो।</div>';
}

// ── Load for edit ────────────────────────────────────────────────────────
$editing = null;
if (!empty($_GET['edit'])) {
    $s = db()->prepare("SELECT * FROM app_notices WHERE id=?");
    $s->execute([(int)$_GET['edit']]);
    $editing = $s->fetch(PDO::FETCH_ASSOC) ?: null;
}

$all = db()->query("SELECT * FROM app_notices ORDER BY active DESC, priority DESC, created_at DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);

$typeColors = [
    'info'=>'#0891b2','success'=>'#059669','warning'=>'#d97706','urgent'=>'#dc2626','janachetana'=>'#7c3aed',
];
?>
<!doctype html>
<html lang="ne"><head>
<meta charset="utf-8"><title>App Notices | <?= SITE_NAME ?></title>
<style>
  body{font-family:system-ui,-apple-system,'Noto Sans Devanagari',sans-serif;background:#f8fafc;margin:0;padding:20px;color:#0f172a}
  .wrap{max-width:1200px;margin:0 auto}
  h1{color:#0f766e;display:flex;align-items:center;gap:10px}
  .grid{display:grid;grid-template-columns:380px 1fr;gap:20px}
  @media(max-width:900px){.grid{grid-template-columns:1fr}}
  .card{background:#fff;border-radius:12px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.06)}
  .card h2{margin:0 0 14px;font-size:18px;color:#0f172a}
  label{display:block;font-size:12px;font-weight:600;margin:10px 0 4px;color:#475569;text-transform:uppercase;letter-spacing:.3px}
  input[type=text],input[type=number],input[type=url],input[type=datetime-local],textarea,select{width:100%;padding:9px 11px;border:1px solid #cbd5e1;border-radius:6px;font:inherit;box-sizing:border-box;font-size:14px}
  textarea{min-height:100px;resize:vertical;font-family:inherit}
  .row2{display:grid;grid-template-columns:1fr 1fr;gap:10px}
  .chk{display:flex;align-items:center;gap:8px;font-size:13px;color:#334155;margin-top:10px;cursor:pointer}
  button,.btn{background:#0f766e;color:#fff;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;font:inherit;font-size:14px;text-decoration:none;display:inline-block;font-weight:600}
  button:hover{background:#0d5d56}
  .btn-sm{padding:5px 10px;font-size:12px;font-weight:500}
  .btn-ghost{background:#f1f5f9;color:#475569}
  .btn-danger{background:#dc2626}
  .btn-warn{background:#f59e0b}
  .ok{background:#d1fae5;color:#065f46;padding:10px 14px;border-radius:8px;margin-bottom:14px}
  .err{background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:8px;margin-bottom:14px}
  table{width:100%;border-collapse:collapse;font-size:13px}
  th,td{text-align:left;padding:10px 8px;border-bottom:1px solid #e5e7eb;vertical-align:top}
  th{background:#f8fafc;font-size:11px;text-transform:uppercase;color:#64748b}
  .pill{display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:600;color:#fff}
  .stat{background:#f1f5f9;padding:2px 8px;border-radius:4px;font-size:11px;color:#475569}
  .badge-on{background:#10b981;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px}
  .badge-off{background:#94a3b8;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px}
  .doc-link{color:#0f766e;font-size:12px;text-decoration:none}
  .help{font-size:11px;color:#64748b;margin-top:4px}
</style></head><body>
<div class="wrap">
  <h1>📢 App Notices <span style="font-size:13px;background:#fef3c7;color:#78350f;padding:4px 10px;border-radius:6px;font-weight:500">Pop-up & Banner</span></h1>
  <?= $msg ?>

  <div class="grid">
    <div class="card">
      <h2><?= $editing ? '✏️ सम्पादन गर्दै' : '➕ नयाँ सूचना थप्नुहोस्' ?></h2>
      <form method="post" enctype="multipart/form-data">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
        <?php if ($editing): ?><input type="hidden" name="id" value="<?= $editing['id'] ?>"><?php endif; ?>

        <label>शीर्षक *</label>
        <input type="text" name="title" required value="<?= htmlspecialchars($editing['title'] ?? '') ?>" placeholder="जस्तै: जनचेतनामूलक सूचना">

        <label>विवरण *</label>
        <textarea name="body" required placeholder="सूचनाको विस्तृत विवरण..."><?= htmlspecialchars($editing['body'] ?? '') ?></textarea>

        <div class="row2">
          <div>
            <label>प्रकार</label>
            <select name="type">
              <?php foreach (['info'=>'ℹ️ जानकारी','success'=>'✅ सफलता','warning'=>'⚠️ चेतावनी','urgent'=>'🚨 जरुरी','janachetana'=>'📢 जनचेतना'] as $k=>$v): ?>
                <option value="<?= $k ?>" <?= ($editing['type'] ?? 'info')===$k?'selected':'' ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label>देखाउने तरिका</label>
            <select name="display_mode">
              <option value="modal"  <?= ($editing['display_mode'] ?? 'modal')==='modal'?'selected':'' ?>>Modal Pop-up</option>
              <option value="banner" <?= ($editing['display_mode'] ?? '')==='banner'?'selected':'' ?>>Top Banner</option>
              <option value="both"   <?= ($editing['display_mode'] ?? '')==='both'?'selected':'' ?>>दुबै</option>
            </select>
          </div>
        </div>

        <label>📎 कागजात (PDF/JPG/PNG/DOC, ≤12MB)</label>
        <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
        <?php if (!empty($editing['document_path'])): ?>
          <div class="help">हाल: <a href="<?= htmlspecialchars($editing['document_path']) ?>" target="_blank" class="doc-link">📎 <?= htmlspecialchars($editing['document_name'] ?? 'document') ?></a> (नयाँ अपलोड गरे यो हटाइनेछ)</div>
        <?php endif; ?>

        <div class="row2">
          <div>
            <label>CTA Button Label</label>
            <input type="text" name="cta_label" value="<?= htmlspecialchars($editing['cta_label'] ?? '') ?>" placeholder="थप पढ्नुहोस्">
          </div>
          <div>
            <label>CTA URL</label>
            <input type="url" name="cta_url" value="<?= htmlspecialchars($editing['cta_url'] ?? '') ?>" placeholder="https://...">
          </div>
        </div>

        <div class="row2">
          <div>
            <label>देखाउन सुरु</label>
            <input type="datetime-local" name="show_from" value="<?= $editing['show_from'] ? date('Y-m-d\TH:i', strtotime($editing['show_from'])) : '' ?>">
          </div>
          <div>
            <label>सम्म</label>
            <input type="datetime-local" name="show_until" value="<?= $editing['show_until'] ? date('Y-m-d\TH:i', strtotime($editing['show_until'])) : '' ?>">
          </div>
        </div>

        <label>प्राथमिकता (उच्च = पहिले देखिन्छ)</label>
        <input type="number" name="priority" value="<?= (int)($editing['priority'] ?? 0) ?>">

        <label class="chk"><input type="checkbox" name="active" value="1" <?= !isset($editing) || $editing['active'] ? 'checked' : '' ?>> ✅ Active (देखाउन सकिने)</label>
        <label class="chk"><input type="checkbox" name="dismissible" value="1" <?= !isset($editing) || $editing['dismissible'] ? 'checked' : '' ?>> ❌ Dismissible (बन्द गर्न मिल्ने)</label>
        <label class="chk"><input type="checkbox" name="pin_top" value="1" <?= isset($editing) && $editing['pin_top'] ? 'checked' : '' ?>> 📌 Top Banner (हर पेजमा पिन गर्ने)</label>

        <div style="margin-top:18px;display:flex;gap:8px">
          <button type="submit"><?= $editing ? '✓ अद्यावधिक' : '✓ थप्नुहोस्' ?></button>
          <?php if ($editing): ?><a href="/admin/admin-notices.php" class="btn btn-ghost">रद्द</a><?php endif; ?>
        </div>
      </form>
    </div>

    <div class="card">
      <h2>सबै सूचना (<?= count($all) ?>)</h2>
      <?php if (!$all): ?>
        <p style="color:#64748b">कुनै सूचना छैन। बायाँबाट नयाँ थप्नुहोस्।</p>
      <?php else: ?>
        <table>
          <thead><tr><th>शीर्षक</th><th>प्रकार</th><th>स्थिति</th><th>तथ्याङ्क</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($all as $n): ?>
            <tr>
              <td>
                <b><?= htmlspecialchars($n['title']) ?></b>
                <?php if ($n['pin_top']): ?><span class="stat">📌 PIN</span><?php endif; ?>
                <?php if ($n['document_path']): ?><br><a class="doc-link" href="<?= htmlspecialchars($n['document_path']) ?>" target="_blank">📎 <?= htmlspecialchars($n['document_name']) ?></a><?php endif; ?>
                <div style="color:#64748b;font-size:11px;margin-top:4px"><?= htmlspecialchars(mb_substr($n['body'], 0, 80, 'UTF-8')) ?>…</div>
              </td>
              <td><span class="pill" style="background:<?= $typeColors[$n['type']] ?? '#64748b' ?>"><?= $n['type'] ?></span><br><small style="color:#64748b"><?= $n['display_mode'] ?></small></td>
              <td><?= $n['active'] ? '<span class="badge-on">ON</span>' : '<span class="badge-off">OFF</span>' ?><br><small style="color:#64748b">P:<?= $n['priority'] ?></small></td>
              <td><span class="stat">👁 <?= (int)$n['views'] ?></span><br><span class="stat">🖱 <?= (int)$n['clicks'] ?></span></td>
              <td>
                <a href="?edit=<?= $n['id'] ?>" class="btn btn-sm btn-ghost">✏️</a>
                <a href="?action=toggle&id=<?= $n['id'] ?>" class="btn btn-sm btn-warn"><?= $n['active'] ? '⏸' : '▶' ?></a>
                <a href="?action=delete&id=<?= $n['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('पक्का हटाउने?')">🗑</a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>

      <div style="margin-top:20px;padding:14px;background:#f8fafc;border-radius:8px;font-size:13px;color:#475569">
        <b>💡 कसरी काम गर्छ?</b>
        <ul style="margin:8px 0 0;padding-left:20px;line-height:1.7">
          <li><b>Modal:</b> App खोल्ने बित्तिकै पप-अप — प्रति यन्त्रमा एकपटक "फेरि नदेखाउनुहोस्" अप्शन</li>
          <li><b>Banner:</b> हरेक पेजको माथि sticky bar</li>
          <li><b>Pin Top:</b> हटाए पनि banner ले देखाइरहन्छ</li>
          <li><b>Schedule:</b> "देखाउन सुरु/सम्म" खाली राखे — तुरुन्तै र अनिश्चित कालसम्म देखिन्छ</li>
          <li><b>जनचेतना:</b> ⚠️ चेतावनी वा 📢 जनचेतना प्रकार छनोट गर्नुहोस्, document अपलोड गर्नुहोस्</li>
        </ul>
      </div>
    </div>
  </div>
</div>
</body></html>
