<?php
/**
 * /admin-entertainment.php — Admin: manage visit places + radio + stories
 * Requires session admin login (uses your existing admin auth check).
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/includes/functions.entertainment.php';

// ── Admin gate (reuse your existing pattern) ──────────────────────────────
if (empty($_SESSION['is_admin'])) {
    header('Location: /admin.php?next=admin-entertainment.php');
    exit;
}

$msg = '';
$action = $_POST['action'] ?? $_GET['action'] ?? 'list';

// ── Add new visit place ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_place') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $short       = trim($_POST['short_caption'] ?? '');
    $district    = trim($_POST['district'] ?? '');
    $province    = trim($_POST['province'] ?? '');
    $region      = $_POST['region'] ?? 'unknown';
    $category    = $_POST['category'] ?? 'general';
    $best_season = trim($_POST['best_season'] ?? '');
    $how_to      = trim($_POST['how_to_reach'] ?? '');
    $lat         = $_POST['latitude'] !== '' ? (float) $_POST['latitude'] : null;
    $lng         = $_POST['longitude'] !== '' ? (float) $_POST['longitude'] : null;
    $featured    = isset($_POST['featured']) ? 1 : 0;

    if (!$title || !$description) {
        $msg = '<div class="err">शीर्षक र विवरण आवश्यक छ।</div>';
    } else {
        $img = ent_save_uploaded_image($_FILES['image'] ?? []);
        if (!$img) {
            $msg = '<div class="err">तस्बिर अपलोड असफल (JPEG/PNG/WebP, ≤8MB)।</div>';
        } else {
            $slug = entSlugify($title);
            $i = 1; $base = $slug;
            while (db()->query("SELECT 1 FROM visit_places WHERE slug=" . db()->quote($slug))->fetch()) {
                $slug = $base . '-' . (++$i);
            }
            db()->prepare("INSERT INTO visit_places
                (slug,title,description,short_caption,district,province,region,category,best_season,how_to_reach,latitude,longitude,image_path,featured,created_by,status)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'published')")
                ->execute([$slug,$title,$description,$short,$district,$province,$region,$category,$best_season,$how_to,$lat,$lng,$img,$featured,$_SESSION['admin_user']??'admin']);
            $msg = '<div class="ok">✓ "'.htmlspecialchars($title).'" थपियो।</div>';
        }
    }
}

// ── Delete place ─────────────────────────────────────────────────────────
if ($action === 'del_place' && !empty($_GET['id'])) {
    db()->prepare("DELETE FROM visit_places WHERE id=?")->execute([(int)$_GET['id']]);
    $msg = '<div class="ok">✓ हटाइयो।</div>';
}

// ── Force success-story sync ─────────────────────────────────────────────
if ($action === 'sync_stories') {
    $n = syncSuccessStoriesFromRss();
    $msg = '<div class="ok">✓ '.$n.' वटा कथा सिंक भयो।</div>';
}

$places   = db()->query("SELECT id,title,district,featured,image_thumb,image_path,views,created_at FROM visit_places ORDER BY created_at DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
$stations = getRadioStations(false);
$storyCount = (int) db()->query("SELECT COUNT(*) FROM success_stories")->fetchColumn();
?>
<!doctype html>
<html lang="ne"><head>
<meta charset="utf-8"><title>Entertainment Admin | <?= SITE_NAME ?></title>
<style>
  body{font-family:system-ui,-apple-system,'Noto Sans Devanagari',sans-serif;background:#f8fafc;margin:0;padding:20px;color:#0f172a}
  .wrap{max-width:1100px;margin:0 auto}
  h1{color:#0f766e}
  .tabs{display:flex;gap:6px;margin-bottom:16px;border-bottom:2px solid #e5e7eb}
  .tabs a{padding:10px 18px;color:#64748b;text-decoration:none;border-bottom:2px solid transparent;margin-bottom:-2px;font-weight:500}
  .tabs a.active{color:#0f766e;border-color:#0f766e}
  .card{background:#fff;border-radius:12px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.05);margin-bottom:16px}
  label{display:block;font-size:13px;font-weight:600;margin:10px 0 4px;color:#334155}
  input[type=text],input[type=number],textarea,select{width:100%;padding:8px 10px;border:1px solid #cbd5e1;border-radius:6px;font:inherit;box-sizing:border-box}
  textarea{min-height:90px;resize:vertical}
  .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  .row3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
  button,.btn{background:#0f766e;color:#fff;border:none;padding:10px 18px;border-radius:8px;cursor:pointer;font:inherit;text-decoration:none;display:inline-block}
  button:hover{background:#0d5d56}
  .btn-danger{background:#dc2626}
  .ok{background:#d1fae5;color:#065f46;padding:10px 14px;border-radius:8px;margin-bottom:12px}
  .err{background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:8px;margin-bottom:12px}
  table{width:100%;border-collapse:collapse;margin-top:10px}
  th,td{text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;font-size:14px}
  th{background:#f1f5f9;font-size:12px;text-transform:uppercase;color:#475569}
  .thumb{width:60px;height:45px;object-fit:cover;border-radius:6px}
  .stat{display:inline-block;background:#f1f5f9;padding:6px 12px;border-radius:6px;margin-right:8px;font-size:13px}
</style></head><body>
<div class="wrap">
  <h1>🎭 Entertainment Admin</h1>
  <div class="tabs">
    <a href="?tab=places" class="<?= ($_GET['tab']??'places')==='places'?'active':'' ?>">📸 Visit Places</a>
    <a href="?tab=stories" class="<?= ($_GET['tab']??'')==='stories'?'active':'' ?>">🏆 Success Stories</a>
    <a href="?tab=radio" class="<?= ($_GET['tab']??'')==='radio'?'active':'' ?>">📻 Radio</a>
  </div>

  <?= $msg ?>

  <?php $tab = $_GET['tab'] ?? 'places'; ?>

  <?php if ($tab === 'places'): ?>
    <div class="card">
      <h2>📸 नयाँ घुम्ने ठाउँ थप्नुहोस्</h2>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_place">
        <label>शीर्षक (नेपालीमा) *</label>
        <input type="text" name="title" required placeholder="जस्तै: फेवाताल, पोखरा">
        <label>विवरण *</label>
        <textarea name="description" required placeholder="यो ठाउँ के को लागि प्रसिद्ध छ..."></textarea>
        <label>छोटो क्याप्शन (कार्डमा देखाउने)</label>
        <input type="text" name="short_caption" placeholder="हिमालले घेरिएको शान्त ताल">
        <div class="row3">
          <div><label>जिल्ला</label><input type="text" name="district" placeholder="कास्की"></div>
          <div><label>प्रदेश</label><input type="text" name="province" placeholder="गण्डकी"></div>
          <div><label>भू-भाग</label><select name="region"><option value="unknown">छान्नुहोस्</option><option value="himal">हिमाल</option><option value="pahad">पहाड</option><option value="tarai">तराई</option></select></div>
        </div>
        <div class="row">
          <div><label>श्रेणी</label><select name="category">
            <option value="general">सामान्य</option><option value="lake">ताल</option><option value="mountain">हिमाल</option><option value="temple">मन्दिर</option><option value="heritage">सम्पदा</option><option value="trek">ट्रेक</option><option value="wildlife">वन्यजन्तु</option><option value="culture">संस्कृति</option>
          </select></div>
          <div><label>उत्तम समय</label><input type="text" name="best_season" placeholder="Sep–Nov, Mar–May"></div>
        </div>
        <label>कसरी पुग्ने?</label>
        <textarea name="how_to_reach" placeholder="काठमाडौंबाट २०० किमी, बस/जहाज..."></textarea>
        <div class="row">
          <div><label>Latitude</label><input type="number" step="any" name="latitude" placeholder="28.2096"></div>
          <div><label>Longitude</label><input type="number" step="any" name="longitude" placeholder="83.9856"></div>
        </div>
        <label>तस्बिर * (JPEG/PNG/WebP, ≤8MB)</label>
        <input type="file" name="image" accept="image/*" required>
        <label><input type="checkbox" name="featured" value="1"> ⭐ Featured ठाउँ बनाउनुहोस्</label>
        <br><br>
        <button type="submit">✓ थप्नुहोस्</button>
      </form>
    </div>

    <div class="card">
      <h2>हालका ठाउँहरू (<?= count($places) ?>)</h2>
      <table>
        <thead><tr><th>तस्बिर</th><th>शीर्षक</th><th>जिल्ला</th><th>Views</th><th>Featured</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($places as $p): ?>
          <tr>
            <td><img src="<?= htmlspecialchars($p['image_thumb'] ?: $p['image_path']) ?>" class="thumb"></td>
            <td><?= htmlspecialchars($p['title']) ?></td>
            <td><?= htmlspecialchars($p['district']) ?></td>
            <td><?= (int)$p['views'] ?></td>
            <td><?= $p['featured'] ? '⭐' : '—' ?></td>
            <td><a href="?action=del_place&id=<?= $p['id'] ?>&tab=places" class="btn btn-danger" onclick="return confirm('हटाउने?')">हटाउनुहोस्</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  <?php elseif ($tab === 'stories'): ?>
    <div class="card">
      <h2>🏆 Success Stories</h2>
      <p><span class="stat">📊 जम्मा: <?= $storyCount ?></span> <span class="stat">⏱ Auto-sync: हरेक <?= SS_SYNC_INTERVAL/60 ?> मिनेट</span></p>
      <a href="?action=sync_stories&tab=stories" class="btn">🔄 अहिले Sync गर्नुहोस्</a>
      <p style="color:#64748b;font-size:13px;margin-top:14px">Stories OnlineKhabar, Setopati, Ratopati RSS बाट keyword-filter गरेर automatic आउँछन्। Manual story add गर्न phpMyAdmin बाट <code>success_stories</code> table मा insert गर्नुहोस्।</p>
    </div>

  <?php else: /* radio */ ?>
    <div class="card">
      <h2>📻 Radio Stations (<?= count($stations) ?>)</h2>
      <p style="color:#64748b;font-size:13px">Default stations seed गरिएका छन्। थप्न/सम्पादन गर्न phpMyAdmin बाट <code>radio_stations</code> table प्रयोग गर्नुहोस्।</p>
      <table>
        <thead><tr><th>Name</th><th>Frequency</th><th>City</th><th>Status</th><th>Featured</th></tr></thead>
        <tbody>
        <?php foreach ($stations as $s): ?>
          <tr>
            <td><b><?= htmlspecialchars($s['name']) ?></b><br><small style="color:#64748b"><?= htmlspecialchars($s['tagline']) ?></small></td>
            <td><?= htmlspecialchars($s['frequency']) ?></td>
            <td><?= htmlspecialchars($s['city']) ?></td>
            <td><?= htmlspecialchars($s['status']) ?></td>
            <td><?= $s['featured'] ? '⭐' : '—' ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
</body></html>
