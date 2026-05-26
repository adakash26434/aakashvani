<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/functions.php';
requireAdmin();
require_once dirname(__DIR__) . '/includes/bs-date.php';

$db  = db();
$tab = $_GET['tab'] ?? 'subscriptions';
$msg = getFlash();

// ─── Ensure all tables (SQLite-compatible via functions.php) ──────────────────
function ensureAll(): void {
    ensureNewsTable();
    ensureAlertsTable();
    ensureNoticesTable();
    ensureGuideTable();
    ensureContactDirectoryTable();
    ensureCabinetDecisionsTable();
}
ensureAll();

// Seed defaults on first run
try { seedDefaultNotices(); } catch(Exception $e) {}
try { seedAlerts(); }          catch(Exception $e) {}
try { seedDefaultGuides(); }   catch(Exception $e) {}

// ─── POST Handlers ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── Subscriptions ─────────────────────────────────────────────────────────
    if ($action === 'add_sub') {
        $stmt = $db->prepare('INSERT INTO subscriptions (name,category,badge,initials,description,price_npr,unit,original_usd,original_price_npr,offer_label,is_active,sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([$_POST['name'],$_POST['category'],$_POST['badge']??'',$_POST['initials']??'',$_POST['description'],(int)$_POST['price_npr'],$_POST['unit']??'mo',$_POST['original_usd']?(float)$_POST['original_usd']:null,$_POST['original_price_npr']?(int)$_POST['original_price_npr']:null,$_POST['offer_label']?:null,isset($_POST['is_active'])?1:0,(int)($_POST['sort_order']??0)]);
        flash('Subscription added.'); header('Location: /admin/dashboard.php?tab=subscriptions'); exit;
    }
    if ($action === 'edit_sub') {
        $stmt = $db->prepare('UPDATE subscriptions SET name=?,category=?,badge=?,initials=?,description=?,price_npr=?,unit=?,original_usd=?,original_price_npr=?,offer_label=?,is_active=?,sort_order=? WHERE id=?');
        $stmt->execute([$_POST['name'],$_POST['category'],$_POST['badge']??'',$_POST['initials']??'',$_POST['description'],(int)$_POST['price_npr'],$_POST['unit']??'mo',$_POST['original_usd']?(float)$_POST['original_usd']:null,$_POST['original_price_npr']?(int)$_POST['original_price_npr']:null,$_POST['offer_label']?:null,isset($_POST['is_active'])?1:0,(int)($_POST['sort_order']??0),(int)$_POST['id']]);
        flash('Subscription updated.'); header('Location: /admin/dashboard.php?tab=subscriptions'); exit;
    }
    if ($action === 'delete_sub') { $db->prepare('DELETE FROM subscriptions WHERE id=?')->execute([(int)$_POST['id']]); flash('Deleted.'); header('Location: /admin/dashboard.php?tab=subscriptions'); exit; }
    if ($action === 'toggle_sub') { $db->prepare('UPDATE subscriptions SET is_active = NOT is_active WHERE id=?')->execute([(int)$_POST['id']]); header('Location: /admin/dashboard.php?tab=subscriptions'); exit; }

    // ── Blog ─────────────────────────────────────────────────────────────────
    if ($action === 'add_post') {
        $slug = slugify($_POST['title']);
        $db->prepare('INSERT INTO blog_posts (title,slug,district,province,content,excerpt,image_url,is_published) VALUES (?,?,?,?,?,?,?,?)')->execute([$_POST['title'],$slug,$_POST['district'],$_POST['province']??'',$_POST['content'],$_POST['excerpt']??'',$_POST['image_url'],isset($_POST['is_published'])?1:0]);
        flash('Blog post published.'); header('Location: /admin/dashboard.php?tab=blog'); exit;
    }
    if ($action === 'edit_post') {
        $db->prepare('UPDATE blog_posts SET title=?,district=?,province=?,content=?,excerpt=?,image_url=?,is_published=? WHERE id=?')->execute([$_POST['title'],$_POST['district'],$_POST['province']??'',$_POST['content'],$_POST['excerpt']??'',$_POST['image_url'],isset($_POST['is_published'])?1:0,(int)$_POST['id']]);
        flash('Post updated.'); header('Location: /admin/dashboard.php?tab=blog'); exit;
    }
    if ($action === 'delete_post') { $db->prepare('DELETE FROM blog_posts WHERE id=?')->execute([(int)$_POST['id']]); flash('Post deleted.'); header('Location: /admin/dashboard.php?tab=blog'); exit; }

    // ── News ─────────────────────────────────────────────────────────────────
    if ($action === 'add_news') {
        $slug = slugify($_POST['title']);
        $db->prepare('INSERT INTO tech_news (title,slug,category,source,excerpt,content,image_url,is_published) VALUES (?,?,?,?,?,?,?,?)')->execute([$_POST['title'],$slug,$_POST['category']?:'General',$_POST['source']??'',$_POST['excerpt']??'',$_POST['content'],$_POST['image_url']??'',isset($_POST['is_published'])?1:0]);
        flash('News published.'); header('Location: /admin/dashboard.php?tab=news'); exit;
    }
    if ($action === 'edit_news') {
        $db->prepare('UPDATE tech_news SET title=?,category=?,source=?,excerpt=?,content=?,image_url=?,is_published=? WHERE id=?')->execute([$_POST['title'],$_POST['category']?:'General',$_POST['source']??'',$_POST['excerpt']??'',$_POST['content'],$_POST['image_url']??'',isset($_POST['is_published'])?1:0,(int)$_POST['id']]);
        flash('News updated.'); header('Location: /admin/dashboard.php?tab=news'); exit;
    }
    if ($action === 'delete_news') { $db->prepare('DELETE FROM tech_news WHERE id=?')->execute([(int)$_POST['id']]); flash('Deleted.'); header('Location: /admin/dashboard.php?tab=news'); exit; }
    if ($action === 'toggle_news') { $db->prepare('UPDATE tech_news SET is_published = NOT is_published WHERE id=?')->execute([(int)$_POST['id']]); header('Location: /admin/dashboard.php?tab=news'); exit; }

    // ── Notices ───────────────────────────────────────────────────────────────
    if ($action === 'add_notice') {
        $db->prepare('INSERT INTO notices (title,category,source,content,importance,is_published,expires_at) VALUES (?,?,?,?,?,?,?)')->execute([$_POST['title'],$_POST['category']?:'General',$_POST['source']??'',$_POST['content'],$_POST['importance']?:'normal',isset($_POST['is_published'])?1:0,$_POST['expires_at']?:null]);
        flash('Notice added.'); header('Location: /admin/dashboard.php?tab=notices'); exit;
    }
    if ($action === 'edit_notice') {
        $db->prepare('UPDATE notices SET title=?,category=?,source=?,content=?,importance=?,is_published=?,expires_at=? WHERE id=?')->execute([$_POST['title'],$_POST['category']?:'General',$_POST['source']??'',$_POST['content'],$_POST['importance']?:'normal',isset($_POST['is_published'])?1:0,$_POST['expires_at']?:null,(int)$_POST['id']]);
        flash('Notice updated.'); header('Location: /admin/dashboard.php?tab=notices'); exit;
    }
    if ($action === 'delete_notice') { $db->prepare('DELETE FROM notices WHERE id=?')->execute([(int)$_POST['id']]); flash('Deleted.'); header('Location: /admin/dashboard.php?tab=notices'); exit; }
    if ($action === 'toggle_notice') { $db->prepare('UPDATE notices SET is_published = NOT is_published WHERE id=?')->execute([(int)$_POST['id']]); header('Location: /admin/dashboard.php?tab=notices'); exit; }

    // ── AI Guides ─────────────────────────────────────────────────────────────
    if ($action === 'add_guide') {
        $slug = slugify($_POST['title']);
        $db->prepare('INSERT INTO ai_guides (title,slug,category,icon,level,excerpt,content,is_published) VALUES (?,?,?,?,?,?,?,?)')->execute([$_POST['title'],$slug,$_POST['category']?:'General',$_POST['icon']??'🤖',$_POST['level']??'Beginner',$_POST['excerpt']??'',$_POST['content'],isset($_POST['is_published'])?1:0]);
        flash('AI Guide published.'); header('Location: /admin/dashboard.php?tab=guides'); exit;
    }
    if ($action === 'edit_guide') {
        $db->prepare('UPDATE ai_guides SET title=?,category=?,icon=?,level=?,excerpt=?,content=?,is_published=? WHERE id=?')->execute([$_POST['title'],$_POST['category']?:'General',$_POST['icon']??'🤖',$_POST['level']??'Beginner',$_POST['excerpt']??'',$_POST['content'],isset($_POST['is_published'])?1:0,(int)$_POST['id']]);
        flash('Guide updated.'); header('Location: /admin/dashboard.php?tab=guides'); exit;
    }
    if ($action === 'delete_guide') { $db->prepare('DELETE FROM ai_guides WHERE id=?')->execute([(int)$_POST['id']]); flash('Deleted.'); header('Location: /admin/dashboard.php?tab=guides'); exit; }
    if ($action === 'toggle_guide') { $db->prepare('UPDATE ai_guides SET is_published = NOT is_published WHERE id=?')->execute([(int)$_POST['id']]); header('Location: /admin/dashboard.php?tab=guides'); exit; }

    // ── Alerts ───────────────────────────────────────────────────────────────
    if ($action === 'add_alert') {
        $db->prepare('INSERT INTO alerts (title,category,district,severity,content,source,is_active,expires_at) VALUES (?,?,?,?,?,?,?,?)')->execute([$_POST['title'],$_POST['category']?:'General',$_POST['district']??'',$_POST['severity']?:'medium',$_POST['content'],$_POST['source']??'',isset($_POST['is_active'])?1:0,$_POST['expires_at']?:null]);
        flash('Alert published.'); header('Location: /admin/dashboard.php?tab=alerts'); exit;
    }
    if ($action === 'edit_alert') {
        $db->prepare('UPDATE alerts SET title=?,category=?,district=?,severity=?,content=?,source=?,is_active=?,expires_at=? WHERE id=?')->execute([$_POST['title'],$_POST['category']?:'General',$_POST['district']??'',$_POST['severity']?:'medium',$_POST['content'],$_POST['source']??'',isset($_POST['is_active'])?1:0,$_POST['expires_at']?:null,(int)$_POST['id']]);
        flash('Alert updated.'); header('Location: /admin/dashboard.php?tab=alerts'); exit;
    }
    if ($action === 'delete_alert') { $db->prepare('DELETE FROM alerts WHERE id=?')->execute([(int)$_POST['id']]); flash('Deleted.'); header('Location: /admin/dashboard.php?tab=alerts'); exit; }
    if ($action === 'toggle_alert') { $db->prepare('UPDATE alerts SET is_active = NOT is_active WHERE id=?')->execute([(int)$_POST['id']]); header('Location: /admin/dashboard.php?tab=alerts'); exit; }

    // ── Messages ─────────────────────────────────────────────────────────────
    if ($action === 'mark_read') { $db->prepare('UPDATE contact_messages SET is_read=1 WHERE id=?')->execute([(int)$_POST['id']]); header('Location: /admin/dashboard.php?tab=messages'); exit; }
    if ($action === 'delete_msg') { $db->prepare('DELETE FROM contact_messages WHERE id=?')->execute([(int)$_POST['id']]); header('Location: /admin/dashboard.php?tab=messages'); exit; }

    // ── Contact Directory ───────────────────────────────────────────────────────
    if ($action === 'add_contact') {
        $db->prepare('INSERT INTO contact_directory (name,name_ne,category,category_ne,city,phone,address,email) VALUES (?,?,?,?,?,?,?,?)')->execute([$_POST['name'],$_POST['name_ne'],$_POST['category'],$_POST['category_ne'],$_POST['city'],$_POST['phone'],$_POST['address'],$_POST['email']]);
        flash('Contact added.'); header('Location: /admin/dashboard.php?tab=contacts'); exit;
    }
    if ($action === 'edit_contact') {
        $db->prepare('UPDATE contact_directory SET name=?,name_ne=?,category=?,category_ne=?,city=?,phone=?,address=?,email=? WHERE id=?')->execute([$_POST['name'],$_POST['name_ne'],$_POST['category'],$_POST['category_ne'],$_POST['city'],$_POST['phone'],$_POST['address'],$_POST['email'],(int)$_POST['id']]);
        flash('Contact updated.'); header('Location: /admin/dashboard.php?tab=contacts'); exit;
    }
    if ($action === 'delete_contact') { $db->prepare('DELETE FROM contact_directory WHERE id=?')->execute([(int)$_POST['id']]); flash('Deleted.'); header('Location: /admin/dashboard.php?tab=contacts'); exit; }

    // ── Cabinet Decisions ────────────────────────────────────────────────────────
    if ($action === 'add_decision') {
        $db->prepare('INSERT INTO cabinet_decisions (date,date_np,title,title_ne,category,category_ne,summary,summary_ne,details,details_ne) VALUES (?,?,?,?,?,?,?,?,?,?)')->execute([$_POST['date'],$_POST['date_np'],$_POST['title'],$_POST['title_ne'],$_POST['category'],$_POST['category_ne'],$_POST['summary'],$_POST['summary_ne'],$_POST['details'],$_POST['details_ne']]);
        flash('Decision added.'); header('Location: /admin/dashboard.php?tab=decisions'); exit;
    }
    if ($action === 'edit_decision') {
        $db->prepare('UPDATE cabinet_decisions SET date=?,date_np=?,title=?,title_ne=?,category=?,category_ne=?,summary=?,summary_ne=?,details=?,details_ne=? WHERE id=?')->execute([$_POST['date'],$_POST['date_np'],$_POST['title'],$_POST['title_ne'],$_POST['category'],$_POST['category_ne'],$_POST['summary'],$_POST['summary_ne'],$_POST['details'],$_POST['details_ne'],(int)$_POST['id']]);
        flash('Decision updated.'); header('Location: /admin/dashboard.php?tab=decisions'); exit;
    }
    if ($action === 'delete_decision') { $db->prepare('DELETE FROM cabinet_decisions WHERE id=?')->execute([(int)$_POST['id']]); flash('Deleted.'); header('Location: /admin/dashboard.php?tab=decisions'); exit; }
}

// ─── Load Data ────────────────────────────────────────────────────────────────
$subs        = getAllSubscriptions();
$posts       = getAllPosts();
$newsItems   = getAllNews();
$messages    = $db->query('SELECT * FROM contact_messages ORDER BY created_at DESC')->fetchAll();
$noticeItems = $db->query('SELECT * FROM notices ORDER BY created_at DESC LIMIT 200')->fetchAll();
$guides      = $db->query('SELECT id,title,category,icon,level,is_published,created_at FROM ai_guides ORDER BY created_at DESC')->fetchAll();
$alertItems  = $db->query('SELECT * FROM alerts ORDER BY created_at DESC LIMIT 200')->fetchAll();
$contacts    = $db->query('SELECT * FROM contact_directory ORDER BY name ASC')->fetchAll();
$decisions   = $db->query('SELECT * FROM cabinet_decisions ORDER BY date DESC')->fetchAll();
$unread      = count(array_filter($messages, fn($m) => !$m['is_read']));
$activeAlerts = count(array_filter($alertItems, fn($a) => $a['is_active']));

$editSub    = isset($_GET['edit_sub'])    ? $db->query('SELECT * FROM subscriptions WHERE id='.(int)$_GET['edit_sub'])->fetch() : null;
$editPost   = isset($_GET['edit_post'])   ? $db->query('SELECT * FROM blog_posts WHERE id='.(int)$_GET['edit_post'])->fetch() : null;
$editNews   = isset($_GET['edit_news'])   ? $db->query('SELECT * FROM tech_news WHERE id='.(int)$_GET['edit_news'])->fetch() : null;
$editNotice = isset($_GET['edit_notice']) ? $db->query('SELECT * FROM notices WHERE id='.(int)$_GET['edit_notice'])->fetch() : null;
$editGuide  = isset($_GET['edit_guide'])  ? $db->query('SELECT * FROM ai_guides WHERE id='.(int)$_GET['edit_guide'])->fetch() : null;
$editAlert  = isset($_GET['edit_alert'])  ? $db->query('SELECT * FROM alerts WHERE id='.(int)$_GET['edit_alert'])->fetch() : null;
$editContact = isset($_GET['edit_contact']) ? $db->query('SELECT * FROM contact_directory WHERE id='.(int)$_GET['edit_contact'])->fetch() : null;
$editDecision = isset($_GET['edit_decision']) ? $db->query('SELECT * FROM cabinet_decisions WHERE id='.(int)$_GET['edit_decision'])->fetch() : null;

// ─── Field Helpers ────────────────────────────────────────────────────────────
function adField(string $name, string $label, string $type = 'text', $value = '', string $ph = '', string $hint = ''): void {
    $v = htmlspecialchars((string)($value ?? ''), ENT_QUOTES);
    echo "<div><label class='block text-xs font-bold uppercase tracking-wider text-[#64748b] mb-1.5'>$label</label>";
    if ($type === 'textarea') {
        echo "<textarea name='$name' rows='5' class='w-full bg-[#fafaf9] border border-[#e2e8f0] px-3 py-2 text-sm text-[#0f172a] rounded focus:outline-none focus:border-[#0f766e]' placeholder='$ph'>$v</textarea>";
    } elseif ($type === 'select') {
        // handled inline
    } else {
        echo "<input type='$type' name='$name' value='$v' placeholder='$ph' class='w-full bg-[#fafaf9] border border-[#e2e8f0] px-3 py-2 text-sm text-[#0f172a] rounded focus:outline-none focus:border-[#0f766e]'/>";
    }
    if ($hint) echo "<p class='text-xs text-[#64748b] mt-1'>$hint</p>";
    echo "</div>";
}

function adSelect(string $name, string $label, array $opts, $sel = ''): void {
    echo "<div><label class='block text-xs font-bold uppercase tracking-wider text-[#64748b] mb-1.5'>$label</label><select name='$name' class='w-full bg-[#fafaf9] border border-[#e2e8f0] px-3 py-2 text-sm text-[#0f172a] rounded focus:outline-none focus:border-[#0f766e]'>";
    foreach ($opts as $v => $l) echo "<option value='".htmlspecialchars($v)."'".($sel==$v?' selected':'').">".htmlspecialchars($l)."</option>";
    echo "</select></div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Admin Dashboard | <?= htmlspecialchars(brandName()) ?></title>
  <meta name="robots" content="noindex,nofollow"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    :root{--bg:#fafaf9;--card:#ffffff;--border:#e7e5e4;--ink:#0f172a;--ink-2:#334155;--muted:#64748b;--muted-2:#94a3b8;--brand:#15803d;--brand-2:#166534;--brand-soft:#dcfce7;--danger:#dc2626;--warn:#d97706;--surface:#f1f5f9;}
    *{box-sizing:border-box}
    body{background:var(--bg);color:var(--ink);font-family:'Inter','Hind Siliguri',sans-serif;margin:0;-webkit-font-smoothing:antialiased;}
    a{text-decoration:none;color:inherit;}
    .modal{display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(4px);z-index:100;align-items:center;justify-content:center;padding:16px;overflow-y:auto;}
    .modal.open{display:flex;}
    .btn-p{background:var(--brand);color:#fff;border-radius:8px;font-weight:600;transition:.15s;display:inline-flex;align-items:center;justify-content:center;gap:6px;}
    .btn-p:hover{background:var(--brand-2);}
    .btn-o{border:1px solid var(--border);color:var(--ink-2);border-radius:8px;background:#fff;transition:.15s;display:inline-flex;align-items:center;justify-content:center;gap:6px;}
    .btn-o:hover{border-color:var(--brand);color:var(--brand);}
    .inp,input[type=text],input[type=number],input[type=email],input[type=url],input[type=password],select,textarea{background:#fff;border:1px solid var(--border);color:var(--ink);padding:.625rem .75rem;font-size:.875rem;border-radius:8px;width:100%;outline:none;transition:.15s;font-family:inherit;}
    .inp:focus,input:focus,select:focus,textarea:focus{border-color:var(--brand);box-shadow:0 0 0 3px rgba(21,128,61,.1);}
    .badge-pub{background:var(--brand-soft);color:var(--brand-2);border:1px solid #bbf7d0;}
    .badge-draft{background:#f1f5f9;color:var(--muted);border:1px solid var(--border);}
    .card{background:var(--card);border:1px solid var(--border);border-radius:12px;}
    .ic{width:16px;height:16px;stroke-width:2;}
    .ic-sm{width:14px;height:14px;stroke-width:2;}
    .nav-item{display:flex;align-items:center;justify-content:space-between;padding:.55rem .75rem;border-radius:8px;font-size:.8125rem;font-weight:500;color:var(--muted);transition:.12s;}
    .nav-item:hover{background:var(--surface);color:var(--ink);}
    .nav-item.active{background:var(--brand);color:#fff;}
    .nav-item.active .nav-badge{background:rgba(255,255,255,.22);color:#fff;}
    .nav-badge{font-size:10px;padding:1px 7px;border-radius:999px;background:var(--brand-soft);color:var(--brand-2);font-weight:600;}
    .stat{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:14px;display:flex;align-items:center;gap:12px;transition:.15s;}
    .stat:hover{border-color:var(--brand);transform:translateY(-1px);box-shadow:0 6px 20px -10px rgba(21,128,61,.25);}
    .stat.active{border-color:var(--brand);background:linear-gradient(180deg,#fff 0%,#f0fdf4 100%);}
    .stat-ic{width:38px;height:38px;border-radius:10px;background:var(--brand-soft);color:var(--brand-2);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .stat-val{font-size:1.35rem;font-weight:700;color:var(--ink);line-height:1.1;}
    .stat-lbl{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;font-weight:600;margin-top:2px;}
    .section-title{font-size:1.05rem;font-weight:700;color:var(--ink);letter-spacing:-.01em;display:inline-flex;align-items:center;gap:8px;}
    .pill{display:inline-flex;align-items:center;gap:5px;padding:2px 8px;border-radius:999px;background:var(--surface);color:var(--muted);font-size:11px;font-weight:600;}
    table{border-collapse:separate;border-spacing:0;}
    /* Force-clean any legacy dark text colors */
    [class*="text-[#0f172a]"]{color:var(--ink)!important;}
    [class*="text-[#64748b]"]{color:var(--muted)!important;}
    [class*="text-[#14b8a6]"]{color:var(--brand)!important;}
    [class*="text-[#ef4444]"]{color:var(--danger)!important;}
    [class*="text-[#f59e0b]"]{color:var(--warn)!important;}
    [class*="bg-[#fafaf9]"]{background:var(--bg)!important;}
    [class*="bg-[#ffffff]"]{background:var(--card)!important;}
    [class*="bg-[#f5f5f4]"]{background:var(--surface)!important;}
    [class*="border-[#e2e8f0]"]{border-color:var(--border)!important;}
    [class*="border-[#0f766e]"]{border-color:var(--brand)!important;}
    [class*="bg-[#0f766e]"]{background:var(--brand)!important;color:#fff;}
  </style>
</head>
<body class="min-h-screen flex">

<?php
$navItems = [
  ['subscriptions','package','Subscriptions',count($subs)],
  ['blog','pen-tool','Blog Posts',count($posts)],
  ['news','newspaper','AI News',count($newsItems)],
  ['notices','bell','Notices',count($noticeItems)],
  ['alerts','siren','Alerts',$activeAlerts>0?"$activeAlerts active":''],
  ['guides','bot','AI Guides',count($guides)],
  ['contacts','phone','Contact Directory',count($contacts)],
  ['decisions','scroll-text','Cabinet Decisions',count($decisions)],
  ['messages','message-circle','Messages',$unread>0?"$unread unread":''],
];
?>

<!-- ═══ ADMIN SIDEBAR ════════════════════════════════════════════════════════ -->
<aside class="w-60 shrink-0 border-r border-[color:var(--border)] bg-white flex-col min-h-screen hidden md:flex sticky top-0">
  <div class="px-4 py-4 border-b border-[color:var(--border)]">
    <div class="flex items-center gap-2.5">
      <?php $logo = brandLogoUrl(); if ($logo): ?>
        <img src="<?= htmlspecialchars($logo) ?>" alt="" style="width:34px;height:34px;object-fit:contain;border-radius:9px;background:#fff;border:1px solid var(--border);padding:3px;"/>
      <?php else: ?>
        <div style="width:34px;height:34px;background:var(--brand);border-radius:9px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:13px;letter-spacing:-.5px;"><?= htmlspecialchars(brandInitials()) ?></div>
      <?php endif; ?>
      <div class="leading-tight min-w-0">
        <div class="text-[13px] font-bold text-[color:var(--ink)] truncate"><?= htmlspecialchars(brandName()) ?></div>
        <div class="text-[10px] text-[color:var(--muted-2)] uppercase tracking-[.18em] font-semibold">Admin</div>
      </div>
    </div>
  </div>
  <nav class="flex-1 p-2.5 space-y-0.5 overflow-y-auto">
    <?php foreach ($navItems as [$key,$icon,$label,$badge]): ?>
    <a href="?tab=<?= $key ?>" class="nav-item <?= $tab===$key?'active':'' ?>">
      <span class="flex items-center gap-2.5"><i data-lucide="<?= $icon ?>" class="ic-sm"></i><?= $label ?></span>
      <?php if ($badge): ?><span class="nav-badge"><?= $badge ?></span><?php endif; ?>
    </a>
    <?php endforeach; ?>

    <div style="height:1px;background:var(--border);margin:10px 4px;"></div>

    <a href="/admin/seo.php" class="nav-item">
      <span class="flex items-center gap-2.5"><i data-lucide="search" class="ic-sm"></i>SEO Manager</span>
    </a>
    <a href="/admin/settings.php" class="nav-item">
      <span class="flex items-center gap-2.5"><i data-lucide="settings" class="ic-sm"></i>Settings</span>
    </a>
    <a href="/" target="_blank" class="nav-item">
      <span class="flex items-center gap-2.5"><i data-lucide="external-link" class="ic-sm"></i>View Site</span>
    </a>
    <a href="/admin/logout.php" class="nav-item" style="color:var(--danger);">
      <span class="flex items-center gap-2.5"><i data-lucide="log-out" class="ic-sm"></i>Logout</span>
    </a>
  </nav>
</aside>

<!-- ═══ MAIN CONTENT ═════════════════════════════════════════════════════════ -->
<div class="flex-1 flex flex-col min-h-screen overflow-hidden">

  <!-- Mobile Top Bar -->
  <header class="md:hidden border-b border-[color:var(--border)] bg-white px-4 h-14 flex items-center justify-between">
    <div class="flex items-center gap-2">
      <?php $logo = brandLogoUrl(); if ($logo): ?>
        <img src="<?= htmlspecialchars($logo) ?>" alt="" style="width:28px;height:28px;object-fit:contain;border-radius:7px;background:#fff;border:1px solid var(--border);padding:2px;"/>
      <?php else: ?>
        <div style="width:28px;height:28px;background:var(--brand);border-radius:7px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:11px;"><?= htmlspecialchars(brandInitials()) ?></div>
      <?php endif; ?>
      <span class="font-bold text-sm"><?= htmlspecialchars(brandName()) ?></span>
    </div>
    <div class="flex items-center gap-3 text-xs">
      <a href="/admin/settings.php" class="text-[color:var(--muted)] hover:text-[color:var(--brand)]"><i data-lucide="settings" class="ic-sm"></i></a>
      <a href="/" target="_blank" class="text-[color:var(--muted)] hover:text-[color:var(--brand)]"><i data-lucide="external-link" class="ic-sm"></i></a>
      <a href="/admin/logout.php" class="text-[color:var(--muted)] hover:text-[color:var(--danger)]"><i data-lucide="log-out" class="ic-sm"></i></a>
    </div>
  </header>

  <!-- Mobile tab bar -->
  <div class="md:hidden border-b border-[color:var(--border)] bg-white overflow-x-auto">
    <div class="flex px-2 py-2 gap-1">
      <?php foreach ($navItems as [$k,$i,$lbl]): ?>
      <a href="?tab=<?= $k ?>" class="shrink-0 px-3 py-1.5 rounded-md text-xs font-semibold inline-flex items-center gap-1.5 <?= $tab===$k?'bg-[color:var(--brand)] text-white':'text-[color:var(--muted)] hover:bg-[color:var(--surface)]' ?>"><i data-lucide="<?= $i ?>" class="ic-sm"></i><?= $lbl ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="flex-1 p-5 md:p-8 max-w-6xl w-full mx-auto">

    <?php if ($msg): ?>
      <div class="card px-4 py-3 text-sm mb-6 flex items-center gap-2.5" style="background:#f0fdf4;border-color:#bbf7d0;color:#15803d;">
        <i data-lucide="check-circle-2" class="ic"></i><?= htmlspecialchars($msg['msg']) ?>
      </div>
    <?php endif; ?>

    <!-- Page header -->
    <div class="mb-6">
      <div class="text-[11px] text-[color:var(--muted)] uppercase tracking-[.14em] font-semibold mb-1">Admin Dashboard</div>
      <h1 class="text-2xl md:text-[28px] font-bold tracking-tight text-[color:var(--ink)]">Overview</h1>
      <p class="text-sm text-[color:var(--muted)] mt-1">Manage subscriptions, content, alerts and messages.</p>
    </div>

    <!-- Stats row -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-8">
      <?php foreach ([
        ['package','Subs',count($subs),'subscriptions'],
        ['pen-tool','Posts',count($posts),'blog'],
        ['newspaper','News',count($newsItems),'news'],
        ['bell','Notices',count($noticeItems),'notices'],
        ['siren','Alerts',$activeAlerts?:count($alertItems),'alerts'],
        ['bot','Guides',count($guides),'guides'],
        ['phone','Contacts',count($contacts),'contacts'],
        ['scroll-text','Decisions',count($decisions),'decisions'],
        ['message-circle','Messages',$unread?:count($messages),'messages'],
      ] as [$ico,$label,$val,$key]): ?>
      <a href="?tab=<?= $key ?>" class="stat <?= $tab===$key?'active':'' ?>">
        <div class="stat-ic"><i data-lucide="<?= $ico ?>" class="ic"></i></div>
        <div>
          <div class="stat-val"><?= $val ?></div>
          <div class="stat-lbl"><?= $label ?></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>



    
    
    <!-- Morning Brief Quick Card -->
    <div class="bg-gradient-to-r from-slate-800 to-slate-700 border border-slate-600/40 rounded-xl p-4 mb-4 flex items-center gap-4">
      <div class="text-3xl flex-shrink-0">☀️</div>
      <div class="flex-1 min-w-0">
        <div class="text-white font-black text-sm">बिहानको AI ब्रिफ</div>
        <div class="text-white/70 text-xs mt-0.5">हरेक बिहान AI ले आजका ५ मुख्य समाचारको बुँदा auto-generate गर्छ — cron बाट।</div>
      </div>
      <div class="flex gap-2 flex-shrink-0">
        <a href="/morning-brief.php" target="_blank" class="bg-white/20 text-white border border-white/30 font-bold text-xs px-4 py-2.5 rounded-lg hover:bg-white/30 transition-all">
          👁 Preview
        </a>
        <button onclick="regenBrief(this)"
                class="bg-emerald-600 text-white font-bold text-xs px-4 py-2.5 rounded-lg hover:bg-emerald-500 transition-all">
          ↺ अहिले बनाउनुस्
        </button>
      </div>
    </div>
    <script>
    function regenBrief(btn){
      btn.disabled=true; btn.textContent='⟳ बनाउँदैछ...';
      fetch('/api/morning-brief.php?gen=1&key=<?= defined("CRON_KEY")?htmlspecialchars(CRON_KEY):"" ?>')
        .then(function(r){return r.json();})
        .then(function(d){
          btn.disabled=false;
          btn.textContent = d.ok ? '✅ '+d.bullets+' बुँदा ('+d.source+')' : '❌ Error';
          setTimeout(function(){btn.textContent='↺ अहिले बनाउनुस्';},4000);
        }).catch(function(){btn.disabled=false;btn.textContent='❌ Network error';});
    }
    </script>

    <!-- PWA Install Quick Card -->
    <div class="bg-gradient-to-r from-teal-700 to-emerald-600 border border-teal-600/30 rounded-xl p-4 mb-6 flex items-center gap-4">
      <div class="text-3xl flex-shrink-0">📲</div>
      <div class="flex-1 min-w-0">
        <div class="text-white font-black text-sm">App Install Link</div>
        <div class="text-white/70 text-xs mt-0.5">यूजरले यो website mobile/desktop मा App जस्तै install गर्नसक्छ — एकदम सजिलो!</div>
      </div>
      <div class="flex gap-2 flex-shrink-0">
        <button onclick="nshPwaDashInstall()" id="dashPwaBtn" class="bg-white text-teal-800 font-black text-xs px-4 py-2.5 rounded-lg hover:bg-teal-50 transition-all" style="display:none" data-pwa-install>
          📥 Install गर्नुस्
        </button>
        <a href="/admin/pwa-install.php" class="bg-white/20 text-white border border-white/30 font-bold text-xs px-4 py-2.5 rounded-lg hover:bg-white/30 transition-all">
          📋 Install Guide
        </a>
        <a href="/admin/settings.php#pwa" class="bg-white/15 text-white border border-white/25 font-bold text-xs px-4 py-2.5 rounded-lg hover:bg-white/25 transition-all">
          ✏ नाम बदल्नुस्
        </a>
      </div>
    </div>
    <script>
    document.addEventListener('nsh:pwa-installable',function(){
      var btn=document.getElementById('dashPwaBtn');
      if(btn){btn.style.display='';btn.removeAttribute('hidden');}
    });
    function nshPwaDashInstall(){
      if(window.nshPwa&&!window.nshPwa.install()){
        window.location.href='/admin/pwa-install.php';
      }
    }
    </script>

    <!-- ═══════════ SUBSCRIPTIONS TAB ══════════════════════════════════════ -->
    <?php if ($tab === 'subscriptions'): ?>
    <div class="flex justify-between items-center mb-5">
      <h2 class="section-title"><i data-lucide="package" class="ic"></i> Manage Subscriptions</h2>
      <button onclick="document.getElementById('modal-add-sub').classList.add('open')" class="px-4 py-2 text-xs font-bold btn-p" style="border-radius:8px;"><i data-lucide="plus" class="ic-sm"></i> New Package</button>
    </div>
    <div class="bg-[#ffffff] border border-[#e2e8f0] rounded overflow-x-auto">
      <table class="w-full text-sm text-left">
        <thead class="text-[10px] uppercase bg-[#f5f5f4] border-b border-[#e2e8f0] text-[#64748b] font-mono">
          <tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">Category</th><th class="px-4 py-3">Price</th><th class="px-4 py-3">Offer</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($subs as $s): ?>
          <tr class="border-b border-[#e2e8f0]/50 hover:bg-[#f5f5f4]/50">
            <td class="px-4 py-3 font-bold uppercase text-[#0f172a] text-xs"><?= htmlspecialchars($s['name']) ?></td>
            <td class="px-4 py-3 font-mono text-xs text-[#64748b]"><?= htmlspecialchars($s['category']) ?></td>
            <td class="px-4 py-3 text-[#14b8a6] font-bold font-mono text-xs">रू <?= number_format($s['price_npr']) ?>/<?= $s['unit'] ?></td>
            <td class="px-4 py-3">
              <?php if ($s['offer_label']): ?>
                <span class="bg-[#0f766e]/20 text-[#14b8a6] border border-[#0f766e]/30 text-[10px] font-bold px-2 py-0.5 rounded uppercase"><?= htmlspecialchars($s['offer_label']) ?></span>
              <?php else: ?><span class="text-[#e2e8f0] text-xs">—</span><?php endif; ?>
            </td>
            <td class="px-4 py-3">
              <form method="POST" class="inline"><input type="hidden" name="action" value="toggle_sub"/><input type="hidden" name="id" value="<?= $s['id'] ?>"/>
                <button type="submit" class="text-[10px] font-bold px-2 py-1 rounded border <?= $s['is_active']?'badge-pub':'badge-draft' ?>"><?= $s['is_active']?'ACTIVE':'INACTIVE' ?></button>
              </form>
            </td>
            <td class="px-4 py-3 text-right space-x-2">
              <a href="?tab=subscriptions&edit_sub=<?= $s['id'] ?>" class="text-xs text-[#64748b] hover:text-[#14b8a6] font-mono">Edit</a>
              <form method="POST" class="inline" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="delete_sub"/><input type="hidden" name="id" value="<?= $s['id'] ?>"/><button type="submit" class="text-xs text-[#ef4444] font-mono">Del</button></form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if ($editSub): ?>
    <div class="mt-6 bg-[#ffffff] border border-[#0f766e]/40 rounded p-6">
      <h3 class="text-base font-bold uppercase tracking-wider text-[#14b8a6] mb-5">Editing: <?= htmlspecialchars($editSub['name']) ?></h3>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="action" value="edit_sub"/><input type="hidden" name="id" value="<?= $editSub['id'] ?>"/>
        <div class="grid grid-cols-2 gap-4">
          <?php adField('name','Name','text',$editSub['name']); adField('category','Category','text',$editSub['category']); ?>
          <?php adField('badge','Badge','text',$editSub['badge']); adField('initials','Initials','text',$editSub['initials']); ?>
          <?php adField('price_npr','Price (NPR)','number',$editSub['price_npr']); adField('unit','Unit','text',$editSub['unit']); ?>
          <?php adField('original_usd','Original USD','number',$editSub['original_usd']??''); adField('sort_order','Sort Order','number',$editSub['sort_order']); ?>
        </div>
        <?php adField('description','Description','textarea',$editSub['description']); ?>
        <div class="grid grid-cols-2 gap-4 border border-[#0f766e]/20 rounded p-4">
          <?php adField('offer_label','Offer Label','text',$editSub['offer_label']??'','e.g. LIMITED TIME','Leave blank to hide'); ?>
          <?php adField('original_price_npr','Original Price NPR','number',$editSub['original_price_npr']??'','','Crossed-out price'); ?>
        </div>
        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="is_active" value="1" <?= $editSub['is_active']?'checked':'' ?> class="w-4 h-4 accent-[#0f766e]"/><span class="text-sm font-mono text-[#64748b]">Active</span></label>
        <div class="flex gap-3"><button type="submit" class="flex-1 py-2.5 font-bold uppercase tracking-wider btn-p rounded text-sm">Update</button><a href="?tab=subscriptions" class="flex-1 py-2.5 text-center font-bold uppercase tracking-wider btn-o rounded text-sm">Cancel</a></div>
      </form>
    </div>
    <?php endif; ?>

    <!-- ═══════════ BLOG TAB ════════════════════════════════════════════════ -->
    <?php elseif ($tab === 'blog'): ?>
    <div class="flex justify-between items-center mb-5">
      <h2 class="section-title"><i data-lucide="pen-tool" class="ic"></i> Blog Posts</h2>
      <button onclick="document.getElementById('modal-add-post').classList.add('open')" class="px-4 py-2 text-xs font-bold btn-p" style="border-radius:8px;"><i data-lucide="plus" class="ic-sm"></i> New Post</button>
    </div>
    <div class="space-y-3">
      <?php foreach ($posts as $post): ?>
      <div class="bg-[#ffffff] border border-[#e2e8f0] rounded p-4 flex items-start gap-4">
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 mb-1 flex-wrap">
            <span class="text-[10px] border border-[#e2e8f0] px-2 py-0.5 rounded text-[#64748b] font-mono uppercase"><?= htmlspecialchars($post['district']) ?></span>
            <span class="text-[10px] font-mono <?= $post['is_published']?'text-[#14b8a6]':'text-[#64748b]' ?>"><?= $post['is_published']?'PUBLISHED':'DRAFT' ?></span>
            <span class="text-[10px] text-[#64748b] font-mono"><?= bsDate($post['created_at']) ?></span>
          </div>
          <h3 class="font-bold uppercase text-[#0f172a] text-sm truncate"><?= htmlspecialchars($post['title']) ?></h3>
        </div>
        <div class="flex gap-2 shrink-0">
          <a href="?tab=blog&edit_post=<?= $post['id'] ?>" class="text-xs text-[#64748b] hover:text-[#14b8a6] font-mono">Edit</a>
          <form method="POST" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="delete_post"/><input type="hidden" name="id" value="<?= $post['id'] ?>"/><button type="submit" class="text-xs text-[#ef4444] font-mono">Del</button></form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if ($editPost): ?>
    <div class="mt-6 bg-[#ffffff] border border-[#0f766e]/40 rounded p-6">
      <h3 class="text-base font-bold uppercase tracking-wider text-[#14b8a6] mb-5">Editing: <?= htmlspecialchars($editPost['title']) ?></h3>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="action" value="edit_post"/><input type="hidden" name="id" value="<?= $editPost['id'] ?>"/>
        <?php adField('title','Title','text',$editPost['title']); ?>
        <div class="grid grid-cols-2 gap-4"><?php adField('district','District','text',$editPost['district']); adField('province','Province','text',$editPost['province']??''); ?></div>
        <?php adField('image_url','Image URL','text',$editPost['image_url']??''); ?>
        <?php adField('excerpt','Excerpt','textarea',$editPost['excerpt']??''); ?>
        <?php adField('content','Content','textarea',$editPost['content']); ?>
        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="is_published" value="1" <?= $editPost['is_published']?'checked':'' ?> class="w-4 h-4 accent-[#0f766e]"/><span class="text-sm font-mono text-[#64748b]">Published</span></label>
        <div class="flex gap-3"><button type="submit" class="flex-1 py-2.5 btn-p rounded font-bold uppercase text-sm">Update</button><a href="?tab=blog" class="flex-1 py-2.5 text-center btn-o rounded font-bold uppercase text-sm">Cancel</a></div>
      </form>
    </div>
    <?php endif; ?>

    <!-- ═══════════ NEWS TAB ════════════════════════════════════════════════ -->
    <?php elseif ($tab === 'news'): ?>
    <div class="flex justify-between items-center mb-5">
      <h2 class="section-title"><i data-lucide="newspaper" class="ic"></i> AI News</h2>
      <button onclick="document.getElementById('modal-add-news').classList.add('open')" class="px-4 py-2 text-xs font-bold btn-p" style="border-radius:8px;"><i data-lucide="plus" class="ic-sm"></i> New Article</button>
    </div>
    <div class="space-y-3">
      <?php foreach ($newsItems as $n): ?>
      <div class="bg-[#ffffff] border border-[#e2e8f0] rounded p-4 flex items-start gap-4">
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 mb-1 flex-wrap">
            <span class="text-[10px] border border-[#e2e8f0] px-2 py-0.5 rounded text-[#64748b] font-mono uppercase"><?= htmlspecialchars($n['category']) ?></span>
            <span class="text-[10px] font-mono <?= $n['is_published']?'text-[#14b8a6]':'text-[#64748b]' ?>"><?= $n['is_published']?'LIVE':'DRAFT' ?></span>
            <span class="text-[10px] text-[#64748b] font-mono"><?= bsDate($n['created_at']) ?></span>
          </div>
          <h3 class="font-bold uppercase text-[#0f172a] text-sm truncate"><?= htmlspecialchars($n['title']) ?></h3>
        </div>
        <div class="flex gap-2 shrink-0 items-center">
          <form method="POST" class="inline"><input type="hidden" name="action" value="toggle_news"/><input type="hidden" name="id" value="<?= $n['id'] ?>"/><button type="submit" class="text-[10px] font-mono px-2 py-0.5 rounded border <?= $n['is_published']?'badge-pub':'badge-draft' ?>"><?= $n['is_published']?'Live':'Draft' ?></button></form>
          <a href="?tab=news&edit_news=<?= $n['id'] ?>" class="text-xs text-[#64748b] hover:text-[#14b8a6] font-mono">Edit</a>
          <form method="POST" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="delete_news"/><input type="hidden" name="id" value="<?= $n['id'] ?>"/><button type="submit" class="text-xs text-[#ef4444] font-mono">Del</button></form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if ($editNews): ?>
    <div class="mt-6 bg-[#ffffff] border border-[#0f766e]/40 rounded p-6">
      <h3 class="text-base font-bold uppercase tracking-wider text-[#14b8a6] mb-5">Editing: <?= htmlspecialchars($editNews['title']) ?></h3>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="action" value="edit_news"/><input type="hidden" name="id" value="<?= $editNews['id'] ?>"/>
        <?php adField('title','Title','text',$editNews['title']); ?>
        <div class="grid grid-cols-2 gap-4"><?php adField('category','Category','text',$editNews['category']); adField('source','Source','text',$editNews['source']??''); ?></div>
        <?php adField('image_url','Image URL','text',$editNews['image_url']??''); ?>
        <?php adField('excerpt','Excerpt','textarea',$editNews['excerpt']??''); ?>
        <?php adField('content','Content','textarea',$editNews['content']??''); ?>
        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="is_published" value="1" <?= $editNews['is_published']?'checked':'' ?> class="w-4 h-4 accent-[#0f766e]"/><span class="text-sm font-mono text-[#64748b]">Published</span></label>
        <div class="flex gap-3"><button type="submit" class="flex-1 py-2.5 btn-p rounded font-bold uppercase text-sm">Update</button><a href="?tab=news" class="flex-1 py-2.5 text-center btn-o rounded font-bold uppercase text-sm">Cancel</a></div>
      </form>
    </div>
    <?php endif; ?>

    <!-- ═══════════ NOTICES TAB ═════════════════════════════════════════════ -->
    <?php elseif ($tab === 'notices'): ?>
    <div class="flex justify-between items-center mb-5">
      <h2 class="section-title"><i data-lucide="bell" class="ic"></i> Notices</h2>
      <button onclick="document.getElementById('modal-add-notice').classList.add('open')" class="px-4 py-2 text-xs font-bold btn-p" style="border-radius:8px;"><i data-lucide="plus" class="ic-sm"></i> New Notice</button>
    </div>
    <div class="space-y-3">
      <?php foreach ($noticeItems as $n):
        $imp = $n['importance'];
        $cls = $imp==='urgent'?'border-[#ef4444]/30':($imp==='important'?'border-[#f59e0b]/30':'border-[#e2e8f0]');
      ?>
      <div class="bg-[#ffffff] border <?= $cls ?> rounded p-4 flex items-start gap-4">
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 mb-1 flex-wrap">
            <span class="text-[10px] font-mono uppercase <?= $imp==='urgent'?'text-[#ef4444]':($imp==='important'?'text-[#f59e0b]':'text-[#64748b]') ?>"><?= strtoupper($imp) ?></span>
            <span class="text-[10px] border border-[#e2e8f0] px-1.5 py-0.5 rounded text-[#64748b] font-mono"><?= htmlspecialchars($n['category']) ?></span>
            <span class="text-[10px] font-mono <?= $n['is_published']?'text-[#14b8a6]':'text-[#64748b]' ?>"><?= $n['is_published']?'PUB':'DRAFT' ?></span>
            <span class="text-[10px] text-[#64748b] font-mono"><?= bsDate($n['created_at']) ?></span>
          </div>
          <h3 class="font-bold text-[#0f172a] text-sm truncate"><?= htmlspecialchars($n['title']) ?></h3>
        </div>
        <div class="flex gap-2 shrink-0 items-center">
          <form method="POST" class="inline"><input type="hidden" name="action" value="toggle_notice"/><input type="hidden" name="id" value="<?= $n['id'] ?>"/><button type="submit" class="text-[10px] font-mono px-2 py-0.5 rounded border <?= $n['is_published']?'badge-pub':'badge-draft' ?>"><?= $n['is_published']?'On':'Off' ?></button></form>
          <a href="?tab=notices&edit_notice=<?= $n['id'] ?>" class="text-xs text-[#64748b] hover:text-[#14b8a6] font-mono">Edit</a>
          <form method="POST" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="delete_notice"/><input type="hidden" name="id" value="<?= $n['id'] ?>"/><button type="submit" class="text-xs text-[#ef4444] font-mono">Del</button></form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if ($editNotice): ?>
    <div class="mt-6 bg-[#ffffff] border border-[#0f766e]/40 rounded p-6">
      <h3 class="text-base font-bold uppercase tracking-wider text-[#14b8a6] mb-5">Editing Notice</h3>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="action" value="edit_notice"/><input type="hidden" name="id" value="<?= $editNotice['id'] ?>"/>
        <?php adField('title','Title','text',$editNotice['title']); ?>
        <div class="grid grid-cols-3 gap-4">
          <?php adField('category','Category','text',$editNotice['category']); adField('source','Source','text',$editNotice['source']??''); ?>
          <?php adSelect('importance','Importance',['normal'=>'Normal','important'=>'Important','urgent'=>'Urgent'],$editNotice['importance']); ?>
        </div>
        <?php adField('content','Content','textarea',$editNotice['content']); ?>
        <?php adBsDateField('expires_at', 'Expires At (optional)', $editNotice['expires_at'] ?? '', 'खाली छोड्नुस् = कहिले सकिँदैन'); ?>
        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="is_published" value="1" <?= $editNotice['is_published']?'checked':'' ?> class="w-4 h-4 accent-[#0f766e]"/><span class="text-sm font-mono text-[#64748b]">Published</span></label>
        <div class="flex gap-3"><button type="submit" class="flex-1 py-2.5 btn-p rounded font-bold uppercase text-sm">Update</button><a href="?tab=notices" class="flex-1 py-2.5 text-center btn-o rounded font-bold uppercase text-sm">Cancel</a></div>
      </form>
    </div>
    <?php endif; ?>

    <!-- ═══════════ ALERTS TAB ══════════════════════════════════════════════ -->
    <?php elseif ($tab === 'alerts'): ?>
    <div class="flex justify-between items-center mb-5">
      <div>
        <h2 class="section-title"><i data-lucide="siren" class="ic"></i> Nepal Alerts</h2>
        <p class="text-xs text-[#64748b] font-mono mt-0.5">Earthquake, Flood, Storm, Health Alerts — is_active ON भएकाहरू website मा देखिन्छन्</p>
      </div>
      <button onclick="document.getElementById('modal-add-alert').classList.add('open')" class="px-4 py-2 text-xs font-bold btn-p" style="border-radius:8px;"><i data-lucide="plus" class="ic-sm"></i> New Alert</button>
    </div>

    <?php if (empty($alertItems)): ?>
    <div class="text-center py-12 border border-dashed border-[#e2e8f0] rounded text-[#64748b] font-mono">
      <i data-lucide="siren" style="width:42px;height:42px;color:#94a3b8;margin:0 auto 12px;display:block;"></i>
      <p>कुनै Alert छैन। माथिको button बाट नयाँ Alert थप्नुस्।</p>
    </div>
    <?php else: ?>
    <div class="space-y-3">
      <?php foreach ($alertItems as $al):
        $sevColor = match($al['severity']) {
          'critical' => ['text-[#ef4444]','border-[#ef4444]/40','bg-[#ef4444]/10'],
          'high'     => ['text-[#f59e0b]','border-[#f59e0b]/40','bg-[#f59e0b]/08'],
          'medium'   => ['text-[#eab308]','border-[#eab308]/40','bg-[#eab308]/08'],
          default    => ['text-[#64748b]','border-[#e2e8f0]','bg-transparent'],
        };
        [$sevTxt,$sevBorder,$sevBg] = $sevColor;
      ?>
      <div class="bg-[#ffffff] border <?= $sevBorder ?> rounded p-4 flex items-start gap-4 <?= $sevBg ?>">
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 mb-1.5 flex-wrap">
            <span class="text-[10px] font-bold font-mono uppercase px-2 py-0.5 rounded border <?= $sevBorder ?> <?= $sevTxt ?>">
              <?= strtoupper($al['severity']) ?>
            </span>
            <span class="text-[10px] border border-[#e2e8f0] px-1.5 py-0.5 rounded text-[#64748b] font-mono"><?= htmlspecialchars($al['category']) ?></span>
            <?php if ($al['district']): ?><span class="text-[10px] text-[#64748b] font-mono">📍 <?= htmlspecialchars($al['district']) ?></span><?php endif; ?>
            <span class="text-[10px] font-mono <?= $al['is_active']?'text-[#14b8a6]':'text-[#64748b]' ?>">
              <?= $al['is_active'] ? '🟢 ACTIVE' : '⚫ INACTIVE' ?>
            </span>
            <span class="text-[10px] text-[#64748b] font-mono"><?= bsDate($al['created_at'], true) ?></span>
            <?php if ($al['expires_at']): ?><span class="text-[10px] text-[#eab308] font-mono">⏱ Expires: <?= bsDate($al['expires_at'], true) ?></span><?php endif; ?>
          </div>
          <h3 class="font-bold text-[#0f172a] text-sm"><?= htmlspecialchars($al['title']) ?></h3>
          <p class="text-xs text-[#64748b] font-mono mt-1 line-clamp-2"><?= htmlspecialchars(strip_tags($al['content'])) ?></p>
          <?php if ($al['source']): ?><p class="text-[10px] text-[#64748b] font-mono mt-1">📡 Source: <?= htmlspecialchars($al['source']) ?></p><?php endif; ?>
        </div>
        <div class="flex flex-col gap-1.5 shrink-0 items-end">
          <form method="POST" class="inline">
            <input type="hidden" name="action" value="toggle_alert"/>
            <input type="hidden" name="id" value="<?= $al['id'] ?>"/>
            <button type="submit" class="text-[10px] font-bold px-2 py-1 rounded border <?= $al['is_active']?'badge-pub':'badge-draft' ?> w-20">
              <?= $al['is_active'] ? 'Deactivate' : 'Activate' ?>
            </button>
          </form>
          <a href="?tab=alerts&edit_alert=<?= $al['id'] ?>" class="text-xs text-[#64748b] hover:text-[#14b8a6] font-mono text-center w-20">Edit</a>
          <form method="POST" onsubmit="return confirm('Delete this alert?')" class="inline">
            <input type="hidden" name="action" value="delete_alert"/>
            <input type="hidden" name="id" value="<?= $al['id'] ?>"/>
            <button type="submit" class="text-xs text-[#ef4444] font-mono w-20 text-left">Delete</button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($editAlert): ?>
    <div class="mt-6 bg-[#ffffff] border border-[#f59e0b]/40 rounded p-6">
      <h3 class="text-base font-bold uppercase tracking-wider text-[#f59e0b] mb-5">✏️ Editing Alert: <?= htmlspecialchars(mb_substr($editAlert['title'],0,50)) ?></h3>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="action" value="edit_alert"/>
        <input type="hidden" name="id" value="<?= $editAlert['id'] ?>"/>
        <?php adField('title','Alert Title','text',$editAlert['title'],'e.g. काठमाडौंमा ५.५ म्याग्निच्युडको भूकम्प'); ?>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <?php adField('category','Category','text',$editAlert['category'],'Earthquake'); ?>
          <?php adField('district','District/Area','text',$editAlert['district']??'','Kathmandu'); ?>
          <?php adField('source','Source','text',$editAlert['source']??'','NSC Nepal'); ?>
          <?php adSelect('severity','Severity',['low'=>'Low','medium'=>'Medium','high'=>'High','critical'=>'Critical'],$editAlert['severity']); ?>
        </div>
        <?php adField('content','Alert Content','textarea',$editAlert['content'],'Full alert description...'); ?>
        <?php adBsDateField('expires_at', 'Auto-Expire At (optional)', $editAlert['expires_at'] ?? '', 'खाली छोड्नुस् = कहिले सकिँदैन'); ?>
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" name="is_active" value="1" <?= $editAlert['is_active']?'checked':'' ?> class="w-4 h-4 accent-[#0f766e]"/>
          <span class="text-sm font-mono text-[#64748b]">Active (website मा देखिन्छ)</span>
        </label>
        <div class="flex gap-3">
          <button type="submit" class="flex-1 py-2.5 btn-p rounded font-bold uppercase text-sm">Update Alert</button>
          <a href="?tab=alerts" class="flex-1 py-2.5 text-center btn-o rounded font-bold uppercase text-sm">Cancel</a>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <!-- ═══════════ AI GUIDES TAB ═══════════════════════════════════════════ -->
    <?php elseif ($tab === 'guides'): ?>
    <div class="flex justify-between items-center mb-5">
      <h2 class="section-title"><i data-lucide="bot" class="ic"></i> AI Guides</h2>
      <button onclick="document.getElementById('modal-add-guide').classList.add('open')" class="px-4 py-2 text-xs font-bold btn-p" style="border-radius:8px;"><i data-lucide="plus" class="ic-sm"></i> New Guide</button>
    </div>
    <div class="space-y-3">
      <?php foreach ($guides as $g): ?>
      <div class="bg-[#ffffff] border border-[#e2e8f0] rounded p-4 flex items-start gap-4">
        <div class="text-2xl shrink-0"><?= $g['icon'] ?></div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 mb-1 flex-wrap">
            <span class="text-[10px] border border-[#e2e8f0] px-1.5 py-0.5 rounded text-[#64748b] font-mono"><?= htmlspecialchars($g['category']) ?></span>
            <span class="text-[10px] border border-[#e2e8f0] px-1.5 py-0.5 rounded text-[#64748b] font-mono"><?= htmlspecialchars($g['level']) ?></span>
            <span class="text-[10px] font-mono <?= $g['is_published']?'text-[#14b8a6]':'text-[#64748b]' ?>"><?= $g['is_published']?'PUB':'DRAFT' ?></span>
          </div>
          <h3 class="font-bold text-[#0f172a] text-sm truncate"><?= htmlspecialchars($g['title']) ?></h3>
        </div>
        <div class="flex gap-2 shrink-0 items-center">
          <form method="POST" class="inline"><input type="hidden" name="action" value="toggle_guide"/><input type="hidden" name="id" value="<?= $g['id'] ?>"/><button type="submit" class="text-[10px] font-mono px-2 py-0.5 rounded border <?= $g['is_published']?'badge-pub':'badge-draft' ?>"><?= $g['is_published']?'On':'Off' ?></button></form>
          <a href="?tab=guides&edit_guide=<?= $g['id'] ?>" class="text-xs text-[#64748b] hover:text-[#14b8a6] font-mono">Edit</a>
          <form method="POST" onsubmit="return confirm('Delete this guide?')"><input type="hidden" name="action" value="delete_guide"/><input type="hidden" name="id" value="<?= $g['id'] ?>"/><button type="submit" class="text-xs text-[#ef4444] font-mono">Del</button></form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if ($editGuide): ?>
    <div class="mt-6 bg-[#ffffff] border border-[#0f766e]/40 rounded p-6">
      <h3 class="text-base font-bold uppercase tracking-wider text-[#14b8a6] mb-5">Editing: <?= htmlspecialchars($editGuide['title']) ?></h3>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="action" value="edit_guide"/><input type="hidden" name="id" value="<?= $editGuide['id'] ?>"/>
        <?php adField('title','Title','text',$editGuide['title']); ?>
        <div class="grid grid-cols-3 gap-4">
          <?php adField('category','Category','text',$editGuide['category']); adField('icon','Icon Emoji','text',$editGuide['icon']); ?>
          <?php adSelect('level','Level',['Beginner'=>'Beginner','Intermediate'=>'Intermediate','Advanced'=>'Advanced'],$editGuide['level']); ?>
        </div>
        <?php adField('excerpt','Short Excerpt','textarea',$editGuide['excerpt']??''); ?>
        <?php adField('content','Full Content (HTML/Text)','textarea',$editGuide['content']); ?>
        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="is_published" value="1" <?= $editGuide['is_published']?'checked':'' ?> class="w-4 h-4 accent-[#0f766e]"/><span class="text-sm font-mono text-[#64748b]">Published</span></label>
        <div class="flex gap-3"><button type="submit" class="flex-1 py-2.5 btn-p rounded font-bold uppercase text-sm">Update</button><a href="?tab=guides" class="flex-1 py-2.5 text-center btn-o rounded font-bold uppercase text-sm">Cancel</a></div>
      </form>
    </div>
    <?php endif; ?>

    <!-- ═══════════ MESSAGES TAB ════════════════════════════════════════════ -->
    <?php elseif ($tab === 'messages'): ?>
    <h2 class="section-title mb-5"><i data-lucide="message-circle" class="ic"></i> Messages <?= $unread?"<span class='text-sm text-[#f59e0b]'>($unread unread)</span>":'' ?></h2>
    <div class="space-y-3">
      <?php foreach ($messages as $m): ?>
      <div class="bg-[#ffffff] border <?= !$m['is_read']?'border-[#0f766e]/40':'border-[#e2e8f0]' ?> rounded p-4">
        <div class="flex justify-between items-start gap-4">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-1 flex-wrap">
              <span class="font-bold text-[#0f172a] text-sm"><?= htmlspecialchars($m['name']) ?></span>
              <a href="mailto:<?= htmlspecialchars($m['email']) ?>" class="text-xs text-[#0ea5e9] font-mono hover:underline"><?= htmlspecialchars($m['email']) ?></a>
              <?php if (!$m['is_read']): ?><span class="text-[10px] badge-pub px-1.5 py-0.5 rounded font-mono">NEW</span><?php endif; ?>
              <span class="text-[10px] text-[#64748b] font-mono ml-auto"><?= bsDate($m['created_at'], true) ?></span>
            </div>
            <?php if ($m['subject']): ?><p class="text-xs font-mono text-[#64748b] mb-2">Re: <?= htmlspecialchars($m['subject']) ?></p><?php endif; ?>
            <p class="text-sm text-[#64748b] leading-relaxed"><?= nl2br(htmlspecialchars($m['message'])) ?></p>
          </div>
          <div class="flex gap-2 shrink-0">
            <?php if (!$m['is_read']): ?>
            <form method="POST"><input type="hidden" name="action" value="mark_read"/><input type="hidden" name="id" value="<?= $m['id'] ?>"/><button type="submit" class="text-[10px] px-2 py-1 border border-[#0f766e]/40 text-[#14b8a6] font-mono rounded">Mark Read</button></form>
            <?php endif; ?>
            <form method="POST" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="delete_msg"/><input type="hidden" name="id" value="<?= $m['id'] ?>"/><button type="submit" class="text-[10px] px-2 py-1 border border-[#ef4444]/30 text-[#ef4444] font-mono rounded">Del</button></form>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- ═══════════ CONTACT DIRECTORY TAB ═════════════════════════════════════ -->
    <?php elseif ($tab === 'contacts'): ?>
    <div class="flex justify-between items-center mb-5">
      <h2 class="section-title"><i data-lucide="phone" class="ic"></i> Contact Directory</h2>
      <button onclick="document.getElementById('modal-add-contact').classList.add('open')" class="px-4 py-2 text-xs font-bold btn-p" style="border-radius:8px;"><i data-lucide="plus" class="ic-sm"></i> New Contact</button>
    </div>
    <div class="space-y-3">
      <?php foreach ($contacts as $c): ?>
      <div class="bg-[#ffffff] border border-[#e2e8f0] rounded p-4 flex items-start gap-4">
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 mb-1 flex-wrap">
            <span class="text-[10px] border border-[#e2e8f0] px-2 py-0.5 rounded text-[#64748b] font-mono uppercase"><?= htmlspecialchars($c['category']) ?></span>
            <span class="text-[10px] border border-[#e2e8f0] px-2 py-0.5 rounded text-[#64748b] font-mono uppercase"><?= htmlspecialchars($c['city']) ?></span>
          </div>
          <h3 class="font-bold text-[#0f172a] text-sm"><?= htmlspecialchars($c['name']) ?> <span class="text-[#64748b] ne"><?= htmlspecialchars($c['name_ne']) ?></span></h3>
          <p class="text-xs text-[#64748b] font-mono mt-1"><?= htmlspecialchars($c['phone']) ?></p>
          <?php if ($c['email']): ?><p class="text-xs text-[#64748b] font-mono"><?= htmlspecialchars($c['email']) ?></p><?php endif; ?>
        </div>
        <div class="flex gap-2 shrink-0">
          <a href="?tab=contacts&edit_contact=<?= $c['id'] ?>" class="text-xs text-[#64748b] hover:text-[#14b8a6] font-mono">Edit</a>
          <form method="POST" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="delete_contact"/><input type="hidden" name="id" value="<?= $c['id'] ?>"/><button type="submit" class="text-xs text-[#ef4444] font-mono">Del</button></form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if ($editContact): ?>
    <div class="mt-6 bg-[#ffffff] border border-[#0f766e]/40 rounded p-6">
      <h3 class="text-base font-bold uppercase tracking-wider text-[#14b8a6] mb-5">Editing: <?= htmlspecialchars($editContact['name']) ?></h3>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="action" value="edit_contact"/><input type="hidden" name="id" value="<?= $editContact['id'] ?>"/>
        <div class="grid grid-cols-2 gap-4">
          <?php adField('name','Name (English)','text',$editContact['name']); adField('name_ne','Name (Nepali)','text',$editContact['name_ne']); ?>
          <?php adField('category','Category (English)','text',$editContact['category']); adField('category_ne','Category (Nepali)','text',$editContact['category_ne']); ?>
          <?php adField('city','City','text',$editContact['city']); adField('phone','Phone','text',$editContact['phone']); ?>
        </div>
        <?php adField('address','Address','textarea',$editContact['address']??''); ?>
        <?php adField('email','Email','text',$editContact['email']??''); ?>
        <div class="flex gap-3"><button type="submit" class="flex-1 py-2.5 btn-p rounded font-bold uppercase text-sm">Update</button><a href="?tab=contacts" class="flex-1 py-2.5 text-center btn-o rounded font-bold uppercase text-sm">Cancel</a></div>
      </form>
    </div>
    <?php endif; ?>

    <!-- ═══════════ CABINET DECISIONS TAB ═════════════════════════════════════ -->
    <?php elseif ($tab === 'decisions'): ?>
    <div class="flex justify-between items-center mb-5">
      <h2 class="section-title"><i data-lucide="scroll-text" class="ic"></i> Cabinet Decisions</h2>
      <button onclick="document.getElementById('modal-add-decision').classList.add('open')" class="px-4 py-2 text-xs font-bold btn-p" style="border-radius:8px;"><i data-lucide="plus" class="ic-sm"></i> New Decision</button>
    </div>
    <div class="space-y-3">
      <?php foreach ($decisions as $d): ?>
      <div class="bg-[#ffffff] border border-[#e2e8f0] rounded p-4 flex items-start gap-4">
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 mb-1 flex-wrap">
            <span class="text-[10px] border border-[#e2e8f0] px-2 py-0.5 rounded text-[#64748b] font-mono uppercase"><?= htmlspecialchars($d['category']) ?></span>
            <span class="text-[10px] text-[#64748b] font-mono"><?= htmlspecialchars($d['date_np']) ?></span>
          </div>
          <h3 class="font-bold text-[#0f172a] text-sm"><?= htmlspecialchars($d['title']) ?> <span class="text-[#64748b] ne"><?= htmlspecialchars($d['title_ne']) ?></span></h3>
          <p class="text-xs text-[#64748b] mt-1"><?= htmlspecialchars($d['summary_ne']) ?></p>
        </div>
        <div class="flex gap-2 shrink-0">
          <a href="?tab=decisions&edit_decision=<?= $d['id'] ?>" class="text-xs text-[#64748b] hover:text-[#14b8a6] font-mono">Edit</a>
          <form method="POST" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="delete_decision"/><input type="hidden" name="id" value="<?= $d['id'] ?>"/><button type="submit" class="text-xs text-[#ef4444] font-mono">Del</button></form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if ($editDecision): ?>
    <div class="mt-6 bg-[#ffffff] border border-[#0f766e]/40 rounded p-6">
      <h3 class="text-base font-bold uppercase tracking-wider text-[#14b8a6] mb-5">Editing: <?= htmlspecialchars($editDecision['title']) ?></h3>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="action" value="edit_decision"/><input type="hidden" name="id" value="<?= $editDecision['id'] ?>"/>
        <div class="grid grid-cols-2 gap-4">
          <?php adField('date','Date (YYYY-MM-DD)','date',$editDecision['date']); adField('date_np','Date (Nepali)','text',$editDecision['date_np']); ?>
          <?php adField('category','Category (English)','text',$editDecision['category']); adField('category_ne','Category (Nepali)','text',$editDecision['category_ne']); ?>
        </div>
        <?php adField('title','Title (English)','text',$editDecision['title']); ?>
        <?php adField('title_ne','Title (Nepali)','text',$editDecision['title_ne']); ?>
        <?php adField('summary','Summary (English)','textarea',$editDecision['summary']??''); ?>
        <?php adField('summary_ne','Summary (Nepali)','textarea',$editDecision['summary_ne']??''); ?>
        <?php adField('details','Details (English, JSON array)','textarea',$editDecision['details']??''); ?>
        <?php adField('details_ne','Details (Nepali, JSON array)','textarea',$editDecision['details_ne']??''); ?>
        <div class="flex gap-3"><button type="submit" class="flex-1 py-2.5 btn-p rounded font-bold uppercase text-sm">Update</button><a href="?tab=decisions" class="flex-1 py-2.5 text-center btn-o rounded font-bold uppercase text-sm">Cancel</a></div>
      </form>
    </div>
    <?php endif; ?>
    <?php endif; ?>

  </div><!-- /max-w -->
</div><!-- /main -->

<!-- ═══ MODALS ════════════════════════════════════════════════════════════════ -->
<!-- Add Subscription -->
<div id="modal-add-sub" class="modal"><div class="bg-[#ffffff] border border-[#e2e8f0] rounded w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6">
  <div class="flex justify-between items-center mb-5"><h3 class="font-bold uppercase tracking-wider text-[#0f172a]">New Subscription</h3><button onclick="this.closest('.modal').classList.remove('open')" class="text-[#64748b] text-2xl leading-none">&times;</button></div>
  <form method="POST" class="space-y-4"><input type="hidden" name="action" value="add_sub"/>
    <div class="grid grid-cols-2 gap-4">
      <?php adField('name','Name','text','','ChatGPT Plus'); adField('category','Category','text','','AI'); ?>
      <?php adField('badge','Badge','text','','POPULAR'); adField('initials','Initials','text','','GPT'); ?>
      <?php adField('price_npr','Price (NPR)','number','','1800'); adField('unit','Unit','text','mo','mo / yr'); ?>
      <?php adField('original_usd','Original USD','number','','20'); adField('sort_order','Sort Order','number','0'); ?>
    </div>
    <?php adField('description','Description','textarea','','Describe subscription...'); ?>
    <div class="grid grid-cols-2 gap-4 border border-[#0f766e]/20 rounded p-4">
      <?php adField('offer_label','Offer Label','text','','LIMITED TIME','Blank = no offer'); ?>
      <?php adField('original_price_npr','Original NPR (crossed)','number','','3000'); ?>
    </div>
    <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 accent-[#0f766e]"/><span class="text-sm font-mono text-[#64748b]">Active</span></label>
    <button type="submit" class="w-full py-2.5 btn-p rounded font-bold uppercase text-sm">Save Subscription</button>
  </form>
</div></div>

<!-- Add Blog Post -->
<div id="modal-add-post" class="modal"><div class="bg-[#ffffff] border border-[#e2e8f0] rounded w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6">
  <div class="flex justify-between items-center mb-5"><h3 class="font-bold uppercase tracking-wider text-[#0f172a]">New Blog Post</h3><button onclick="this.closest('.modal').classList.remove('open')" class="text-[#64748b] text-2xl leading-none">&times;</button></div>
  <form method="POST" class="space-y-4"><input type="hidden" name="action" value="add_post"/>
    <?php adField('title','Title'); ?>
    <div class="grid grid-cols-2 gap-4"><?php adField('district','District','text','','Kaski'); adField('province','Province','text','','Gandaki'); ?></div>
    <?php adField('image_url','Image URL','text','','https://...'); ?>
    <?php adField('excerpt','Excerpt','textarea'); ?>
    <?php adField('content','Full Content','textarea'); ?>
    <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="is_published" value="1" checked class="w-4 h-4 accent-[#0f766e]"/><span class="text-sm font-mono text-[#64748b]">Publish immediately</span></label>
    <button type="submit" class="w-full py-2.5 btn-p rounded font-bold uppercase text-sm">Publish Post</button>
  </form>
</div></div>

<!-- Add News -->
<div id="modal-add-news" class="modal"><div class="bg-[#ffffff] border border-[#e2e8f0] rounded w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6">
  <div class="flex justify-between items-center mb-5"><h3 class="font-bold uppercase tracking-wider text-[#0f172a]">New AI News Article</h3><button onclick="this.closest('.modal').classList.remove('open')" class="text-[#64748b] text-2xl leading-none">&times;</button></div>
  <form method="POST" class="space-y-4"><input type="hidden" name="action" value="add_news"/>
    <?php adField('title','Title'); ?>
    <div class="grid grid-cols-2 gap-4"><?php adField('category','Category','text','','AI Tools'); adField('source','Source','text','','OpenAI Blog'); ?></div>
    <?php adField('image_url','Image URL','text','','https://...'); ?>
    <?php adField('excerpt','Excerpt','textarea'); ?>
    <?php adField('content','Full Content','textarea'); ?>
    <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="is_published" value="1" checked class="w-4 h-4 accent-[#0f766e]"/><span class="text-sm font-mono text-[#64748b]">Publish</span></label>
    <button type="submit" class="w-full py-2.5 btn-p rounded font-bold uppercase text-sm">Publish Article</button>
  </form>
</div></div>

<!-- Add Alert -->
<div id="modal-add-alert" class="modal"><div class="bg-[#ffffff] border border-[#ef4444]/30 rounded w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6">
  <div class="flex justify-between items-center mb-5">
    <h3 class="font-bold uppercase tracking-wider text-[#ef4444]"><i data-lucide="siren" class="ic"></i> New Nepal Alert</h3>
    <button onclick="this.closest('.modal').classList.remove('open')" class="text-[#64748b] text-2xl leading-none">&times;</button>
  </div>
  <form method="POST" class="space-y-4"><input type="hidden" name="action" value="add_alert"/>
    <?php adField('title','Alert Title','text','','e.g. काठमाडौंमा ५.५ म्याग्निच्युडको भूकम्प'); ?>
    <div class="grid grid-cols-2 gap-4">
      <?php adField('category','Category','text','','Earthquake / Flood / Storm / Health'); ?>
      <?php adField('district','District / Area','text','','Kathmandu / Nationwide'); ?>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <?php adField('source','Source','text','','NSC Nepal / DoHS / DHM'); ?>
      <?php adSelect('severity','Severity Level',['low'=>'🟡 Low','medium'=>'🟠 Medium','high'=>'🔴 High','critical'=>'🚨 Critical'],'medium'); ?>
    </div>
    <?php adField('content','Full Alert Description','textarea','','Detailed alert information in Nepali or English...'); ?>
    <?php adBsDateField('expires_at', 'स्वतः समाप्त हुने मिति', '', 'खाली छोड्नुस् = कहिले सकिँदैन'); ?>
    <label class="flex items-center gap-2 cursor-pointer">
      <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 accent-[#0f766e]"/>
      <span class="text-sm font-mono text-[#64748b]">Active — website मा तुरुन्त देखाउनुस्</span>
    </label>
    <button type="submit" class="w-full py-2.5 font-bold uppercase tracking-wider rounded text-sm text-white" style="background:#b91c1c">Publish Alert</button>
  </form>
</div></div>

<!-- Add Notice -->
<div id="modal-add-notice" class="modal"><div class="bg-[#ffffff] border border-[#e2e8f0] rounded w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6">
  <div class="flex justify-between items-center mb-5"><h3 class="font-bold uppercase tracking-wider text-[#0f172a]">New Notice</h3><button onclick="this.closest('.modal').classList.remove('open')" class="text-[#64748b] text-2xl leading-none">&times;</button></div>
  <form method="POST" class="space-y-4"><input type="hidden" name="action" value="add_notice"/>
    <?php adField('title','Title'); ?>
    <div class="grid grid-cols-3 gap-4">
      <?php adField('category','Category','text','','Finance'); adField('source','Source','text','','NRB'); ?>
      <?php adSelect('importance','Importance',['normal'=>'Normal','important'=>'Important','urgent'=>'Urgent'],'normal'); ?>
    </div>
    <?php adField('content','Content','textarea'); ?>
    <?php adBsDateField('expires_at', 'समाप्त हुने मिति', '', 'खाली छोड्नुस् = कहिले सकिँदैन'); ?>
    <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="is_published" value="1" checked class="w-4 h-4 accent-[#0f766e]"/><span class="text-sm font-mono text-[#64748b]">Published</span></label>
    <button type="submit" class="w-full py-2.5 btn-p rounded font-bold uppercase text-sm">Add Notice</button>
  </form>
</div></div>

<!-- Add AI Guide -->
<div id="modal-add-guide" class="modal"><div class="bg-[#ffffff] border border-[#e2e8f0] rounded w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6">
  <div class="flex justify-between items-center mb-5"><h3 class="font-bold uppercase tracking-wider text-[#0f172a]">New AI Guide</h3><button onclick="this.closest('.modal').classList.remove('open')" class="text-[#64748b] text-2xl leading-none">&times;</button></div>
  <form method="POST" class="space-y-4"><input type="hidden" name="action" value="add_guide"/>
    <?php adField('title','Title'); ?>
    <div class="grid grid-cols-3 gap-4">
      <?php adField('category','Category','text','','ChatGPT'); adField('icon','Icon Emoji','text','🤖','🤖'); ?>
      <?php adSelect('level','Level',['Beginner'=>'Beginner','Intermediate'=>'Intermediate','Advanced'=>'Advanced'],'Beginner'); ?>
    </div>
    <?php adField('excerpt','Short Excerpt','textarea'); ?>
    <?php adField('content','Full Content (HTML/Text)','textarea'); ?>
    <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="is_published" value="1" checked class="w-4 h-4 accent-[#0f766e]"/><span class="text-sm font-mono text-[#64748b]">Publish</span></label>
    <button type="submit" class="w-full py-2.5 btn-p rounded font-bold uppercase text-sm">Publish Guide</button>
  </form>
</div></div>

<!-- Add Contact -->
<div id="modal-add-contact" class="modal"><div class="bg-[#ffffff] border border-[#e2e8f0] rounded w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6">
  <div class="flex justify-between items-center mb-5"><h3 class="font-bold uppercase tracking-wider text-[#0f172a]">New Contact</h3><button onclick="this.closest('.modal').classList.remove('open')" class="text-[#64748b] text-2xl leading-none">&times;</button></div>
  <form method="POST" class="space-y-4"><input type="hidden" name="action" value="add_contact"/>
    <div class="grid grid-cols-2 gap-4">
      <?php adField('name','Name (English)','text','',''); adField('name_ne','Name (Nepali)','text','',''); ?>
      <?php adField('category','Category (English)','text','','government'); adField('category_ne','Category (Nepali)','text','','सरकारी'); ?>
      <?php adField('city','City','text','','Kathmandu'); adField('phone','Phone','text','',''); ?>
    </div>
    <?php adField('address','Address','textarea',''); ?>
    <?php adField('email','Email','text',''); ?>
    <button type="submit" class="w-full py-2.5 btn-p rounded font-bold uppercase text-sm">Add Contact</button>
  </form>
</div></div>

<!-- Add Decision -->
<div id="modal-add-decision" class="modal"><div class="bg-[#ffffff] border border-[#e2e8f0] rounded w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6">
  <div class="flex justify-between items-center mb-5"><h3 class="font-bold uppercase tracking-wider text-[#0f172a]">New Cabinet Decision</h3><button onclick="this.closest('.modal').classList.remove('open')" class="text-[#64748b] text-2xl leading-none">&times;</button></div>
  <form method="POST" class="space-y-4"><input type="hidden" name="action" value="add_decision"/>
    <div class="grid grid-cols-2 gap-4">
      <?php adField('date','Date (YYYY-MM-DD)','date',''); adField('date_np','Date (Nepali)','text',''); ?>
      <?php adField('category','Category (English)','text','economic'); adField('category_ne','Category (Nepali)','text','आर्थिक'); ?>
    </div>
    <?php adField('title','Title (English)','text',''); ?>
    <?php adField('title_ne','Title (Nepali)','text',''); ?>
    <?php adField('summary','Summary (English)','textarea',''); ?>
    <?php adField('summary_ne','Summary (Nepali)','textarea',''); ?>
    <?php adField('details','Details (English, JSON array)','textarea','[]'); ?>
    <?php adField('details_ne','Details (Nepali, JSON array)','textarea','[]'); ?>
    <button type="submit" class="w-full py-2.5 btn-p rounded font-bold uppercase text-sm">Add Decision</button>
  </form>
</div></div>

<script>
document.querySelectorAll('.modal').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
});
</script>
<script>if(window.lucide)lucide.createIcons();</script>
</body>
</html>
