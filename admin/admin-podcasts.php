<?php
/**
 * /admin/admin-podcasts.php — Admin podcast management
 * Upload, edit, delete, and featured selection for podcasts
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/functions.entertainment.php';

// Auth check
if (empty($_SESSION['is_admin'])) {
    header('Location: /admin.php?next=admin-podcasts.php');
    exit;
}

$msg = '';
$action = $_POST['action'] ?? $_GET['action'] ?? 'list';
$pdo = db();

// Helper: Save upload
function savePodcastUpload($files) {
    $max = 100 * 1024 * 1024; // 100MB
    $allowed = ['audio/mpeg', 'audio/mp4', 'audio/wav', 'audio/ogg'];
    
    if (empty($files['tmp_name'])) return null;
    if ($files['size'] > $max) return null;
    if (!in_array($files['type'], $allowed)) return null;
    
    $dir = __DIR__ . '/../uploads/podcasts/';
    @mkdir($dir, 0755, true);
    
    $name = preg_replace('/[^a-z0-9_.-]/i', '', basename($files['name']));
    if (!$name) $name = 'podcast_' . time() . '.mp3';
    
    $path = $dir . $name;
    if (!move_uploaded_file($files['tmp_name'], $path)) return null;
    
    return '/uploads/podcasts/' . $name;
}

// Add podcast
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? 'general';
    $featured = isset($_POST['featured']) ? 1 : 0;
    $audio_url = $_POST['audio_url'] ?? '';
    $duration = (int)($_POST['duration_seconds'] ?? 0);
    
    if (!$title || (!$audio_url && empty($_FILES['audio']['tmp_name']))) {
        $msg = '<div class="err">❌ शीर्षक र अडियो URL आवश्यक छ।</div>';
    } else {
        $url = $audio_url;
        if (empty($url) && !empty($_FILES['audio']['tmp_name'])) {
            $url = savePodcastUpload($_FILES['audio']);
            if (!$url) {
                $msg = '<div class="err">❌ अडियो अपलोड असफल।</div>';
                goto skip_add;
            }
        }
        
        $slug = entSlugify($title);
        $i = 1; $base = $slug;
        while ($pdo->query("SELECT 1 FROM user_podcasts WHERE slug=" . $pdo->quote($slug))->fetch()) {
            $slug = $base . '-' . (++$i);
        }
        
        $stmt = $pdo->prepare("INSERT INTO user_podcasts 
            (title, description, slug, audio_url, duration_seconds, category, featured, status, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'published', ?)");
        
        $stmt->execute([$title, $desc, $slug, $url, $duration, $category, $featured, $_SESSION['admin_user'] ?? 'admin']);
        $msg = '<div class="ok">✓ पोडकास्ट थपियो।</div>';
    }
    skip_add:;
}

// Update podcast
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = $_POST['status'] ?? 'published';
    
    if ($id && $title) {
        $stmt = $pdo->prepare("UPDATE user_podcasts SET title=?, description=?, featured=?, status=? WHERE id=?");
        $stmt->execute([$title, $desc, $featured, $status, $id]);
        $msg = '<div class="ok">✓ अपडेट भयो।</div>';
    }
}

// Delete podcast
if ($action === 'del' && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $pdo->prepare("DELETE FROM user_podcasts WHERE id=?")->execute([$id]);
    $msg = '<div class="ok">✓ हटाइयो।</div>';
}

$podcasts = $pdo->query("SELECT id, title, slug, category, featured, status, views, created_at, duration_seconds 
                         FROM user_podcasts ORDER BY created_at DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC) ?: [];
?>
<!doctype html>
<html lang="ne">
<head>
    <meta charset="utf-8">
    <title>पोडकास्ट प्रबन्धन | <?= SITE_NAME ?></title>
    <style>
        body {
            font-family: system-ui, -apple-system, 'Noto Sans Devanagari', sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 20px;
            color: #0f172a;
        }
        .wrap { max-width: 1200px; margin: 0 auto; }
        h1 { color: #7c3aed; margin-bottom: 20px; }
        .tabs { display: flex; gap: 6px; margin-bottom: 16px; border-bottom: 2px solid #e5e7eb; }
        .tabs a { padding: 10px 18px; color: #64748b; text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -2px; font-weight: 500; cursor: pointer; }
        .tabs a.active { color: #7c3aed; border-color: #7c3aed; }
        .card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,.05); margin-bottom: 20px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 12px; font-weight: 600; text-transform: uppercase; color: #64748b; margin-bottom: 6px; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-family: inherit; }
        .form-group textarea { min-height: 100px; resize: vertical; }
        .btn { background: #7c3aed; color: #fff; padding: 10px 16px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px; }
        .btn:hover { background: #6d28d9; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-danger { background: #e74c3c; }
        .btn-danger:hover { background: #c0392b; }
        .ok { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .err { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f1f5f9; padding: 12px; text-align: left; font-weight: 600; font-size: 12px; color: #475569; }
        td { padding: 12px; border-bottom: 1px solid #e5e7eb; }
        tr:hover { background: #f8fafc; }
        .status { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .status.published { background: #dbeafe; color: #1e40af; }
        .status.draft { background: #fef3c7; color: #92400e; }
        .status.archived { background: #f3f4f6; color: #6b7280; }
        .featured { color: #f59e0b; font-weight: 600; }
        .actions { display: flex; gap: 4px; }
    </style>
</head>
<body>
<div class="wrap">
    <h1>🎙️ पोडकास्ट प्रबन्धन</h1>
    
    <?php if ($msg) echo $msg; ?>
    
    <div class="tabs">
        <a href="?action=list" class="<?= $action === 'list' ? 'active' : '' ?>">पोडकास्टहरू (<?= count($podcasts) ?>)</a>
        <a href="?action=add" class="<?= $action === 'add' ? 'active' : '' ?>">+ नयाँ पोडकास्ट</a>
    </div>
    
    <?php if ($action === 'add'): ?>
    <div class="card">
        <h2>नयाँ पोडकास्ट थप्नुहोस्</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label>शीर्षक *</label>
                <input type="text" name="title" required placeholder="पोडकास्टको शीर्षक">
            </div>
            
            <div class="form-group">
                <label>विवरण</label>
                <textarea name="description" placeholder="पोडकास्टको विस्तृत विवरण..."></textarea>
            </div>
            
            <div class="form-group">
                <label>अडियो URL वा फाइल अपलोड गर्नुहोस् *</label>
                <p style="font-size:12px;color:#64748b;margin:0 0 8px">
                    <strong>विकल्प 1:</strong> बाहिरी URL पेस्ट गर्नुहोस्
                </p>
                <input type="url" name="audio_url" placeholder="https://example.com/podcast.mp3">
                <p style="font-size:12px;color:#64748b;margin:8px 0">
                    <strong>विकल्प 2:</strong> वा यहाँ अपलोड गर्नुहोस् (MP3/M4A/WAV, max 100MB)
                </p>
                <input type="file" name="audio" accept="audio/*">
            </div>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label>अवधि (सेकेन्डमा)</label>
                    <input type="number" name="duration_seconds" placeholder="0">
                </div>
                <div class="form-group">
                    <label>श्रेणी</label>
                    <select name="category">
                        <option value="general">सामान्य</option>
                        <option value="news">समाचार</option>
                        <option value="education">शिक्षा</option>
                        <option value="business">व्यापार</option>
                        <option value="technology">प्रविधि</option>
                        <option value="culture">संस्कृति</option>
                        <option value="health">स्वास्थ्य</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group" style="display:flex;gap:10px;align-items:center">
                <input type="checkbox" id="featured" name="featured" value="1">
                <label for="featured" style="margin:0">विशेष पोडकास्ट बनाउनुहोस्</label>
            </div>
            
            <button type="submit" class="btn">थप्नुहोस्</button>
        </form>
    </div>
    
    <?php elseif ($action === 'list'): ?>
    <div class="card">
        <?php if (!$podcasts): ?>
            <p style="color:#999;text-align:center;padding:40px">अहिले कुनै पोडकास्ट नेभ। <a href="?action=add">नयाँ पोडकास्ट थप्नुहोस्</a></p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>शीर्षक</th>
                        <th>श्रेणी</th>
                        <th>स्थिति</th>
                        <th>दृश्य</th>
                        <th>अवधि</th>
                        <th>कार्य</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($podcasts as $p): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($p['title']) ?></strong>
                            <?php if ($p['featured']): ?><span class="featured"> ⭐</span><?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['category'] ?? '-') ?></td>
                        <td><span class="status <?= $p['status'] ?>"><?= htmlspecialchars($p['status']) ?></span></td>
                        <td><?= $p['views'] ?? 0 ?></td>
                        <td><?php 
                            if ($p['duration_seconds']) {
                                $h = floor($p['duration_seconds'] / 3600);
                                $m = floor(($p['duration_seconds'] % 3600) / 60);
                                echo $h > 0 ? $h . ':' . str_pad($m, 2, '0', STR_PAD_LEFT) . 'h' : $m . 'm';
                            } else {
                                echo '-';
                            }
                        ?></td>
                        <td class="actions">
                            <a href="?action=edit&id=<?= $p['id'] ?>"><button class="btn btn-sm">संपादन</button></a>
                            <a href="?action=del&id=<?= $p['id'] ?>" onclick="return confirm('मेटाउन निश्चित?')"><button class="btn btn-sm btn-danger">मेट्नुहोस्</button></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <?php elseif ($action === 'edit' && !empty($_GET['id'])): ?>
    <?php 
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM user_podcasts WHERE id=?");
        $stmt->execute([$id]);
        $pod = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$pod): echo '<p>पोडकास्ट नभेटिएन</p>'; else:
    ?>
    <div class="card">
        <h2>पोडकास्ट संपादन गर्नुहोस्</h2>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= $pod['id'] ?>">
            
            <div class="form-group">
                <label>शीर्षक</label>
                <input type="text" name="title" value="<?= htmlspecialchars($pod['title']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>विवरण</label>
                <textarea name="description"><?= htmlspecialchars($pod['description'] ?? '') ?></textarea>
            </div>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label>स्थिति</label>
                    <select name="status">
                        <option value="published" <?= $pod['status'] === 'published' ? 'selected' : '' ?>>प्रकाशित</option>
                        <option value="draft" <?= $pod['status'] === 'draft' ? 'selected' : '' ?>>ड्राफ्ट</option>
                        <option value="archived" <?= $pod['status'] === 'archived' ? 'selected' : '' ?>>संग्रहित</option>
                    </select>
                </div>
                <div class="form-group" style="display:flex;align-items:flex-end">
                    <label style="display:flex;align-items:center;gap:8px;margin-bottom:0">
                        <input type="checkbox" name="featured" value="1" <?= $pod['featured'] ? 'checked' : '' ?>>
                        <span>विशेष पोडकास्ट</span>
                    </label>
                </div>
            </div>
            
            <button type="submit" class="btn">सेभ गर्नुहोस्</button>
            <a href="?action=list" style="margin-left:8px"><button type="button" class="btn" style="background:#999">रद्द गर्नुहोस्</button></a>
        </form>
    </div>
    <?php endif; endif; ?>
</div>
</body>
</html>
