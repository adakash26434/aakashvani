<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/functions.php';

requireAdmin();

// ─── Handle POST actions ───────────────────────────────────────────────────
$action = trim($_POST['action'] ?? $_GET['action'] ?? '');
$msg    = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── DELETE ──────────────────────────────────────────────────────────────
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            db()->prepare('DELETE FROM ai_guides WHERE id = ?')->execute([$id]);
            flash('Guide deleted successfully.', 'success');
        }
        redirect('/admin/ai-guides.php');
    }

    // ── TOGGLE PUBLISH ───────────────────────────────────────────────────────
    if ($action === 'toggle') {
        $id  = (int)($_POST['id'] ?? 0);
        $cur = (int)($_POST['current'] ?? 0);
        if ($id) {
            db()->prepare('UPDATE ai_guides SET is_published = ? WHERE id = ?')->execute([$cur ? 0 : 1, $id]);
            flash('Guide ' . ($cur ? 'unpublished' : 'published') . '.', 'success');
        }
        redirect('/admin/ai-guides.php');
    }

    // ── SAVE (create or update) ──────────────────────────────────────────────
    if ($action === 'save') {
        $id       = (int)($_POST['id'] ?? 0);
        $title    = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? 'General');
        $icon     = trim($_POST['icon'] ?? '🤖');
        $level    = trim($_POST['level'] ?? 'Beginner');
        $excerpt  = trim($_POST['excerpt'] ?? '');
        $content  = trim($_POST['content'] ?? '');
        $pub      = isset($_POST['is_published']) ? 1 : 0;

        if (!$title || !$content) {
            flash('Title र Content required छ।', 'error');
            redirect('/admin/ai-guides.php?action=edit&id=' . $id);
        }

        // Auto-generate slug from title
        $rawSlug = strtolower(preg_replace('/[^a-zA-Z0-9\s-]/', '', $title));
        $rawSlug = preg_replace('/[\s-]+/', '-', trim($rawSlug));
        $rawSlug = substr($rawSlug, 0, 80);

        if ($id) {
            // Update — keep existing slug if title unchanged
            $existing = db()->prepare('SELECT slug, title FROM ai_guides WHERE id = ?');
            $existing->execute([$id]);
            $row = $existing->fetch();
            $slug = ($row && strtolower(trim($row['title'])) === strtolower($title)) ? $row['slug'] : $rawSlug . '-' . $id;
            db()->prepare('UPDATE ai_guides SET title=?,slug=?,category=?,icon=?,level=?,excerpt=?,content=?,is_published=? WHERE id=?')
                ->execute([$title,$slug,$category,$icon,$level,$excerpt,$content,$pub,$id]);
            flash('Guide updated!', 'success');
        } else {
            // Insert
            $slug = $rawSlug . '-' . time();
            db()->prepare('INSERT INTO ai_guides (title,slug,category,icon,level,excerpt,content,is_published) VALUES (?,?,?,?,?,?,?,?)')
                ->execute([$title,$slug,$category,$icon,$level,$excerpt,$content,$pub]);
            flash('New guide created!', 'success');
        }
        redirect('/admin/ai-guides.php');
    }
}

// ─── Load flash message ────────────────────────────────────────────────────
$flash = getFlash();

// ─── EDIT / CREATE form ────────────────────────────────────────────────────
$editId   = (int)($_GET['id'] ?? 0);
$editMode = ($action === 'edit' || $action === 'create');
$guide    = null;
if ($editMode && $editId) {
    $s = db()->prepare('SELECT * FROM ai_guides WHERE id = ?');
    $s->execute([$editId]);
    $guide = $s->fetch();
    if (!$guide) redirect('/admin/ai-guides.php');
}

// ─── List all guides ───────────────────────────────────────────────────────
ensureGuideTable();
try { seedDefaultGuides(); } catch (Exception $e) {}
$guides = db()->query('SELECT * FROM ai_guides ORDER BY created_at DESC')->fetchAll();

// ─── Page variables ────────────────────────────────────────────────────────
$pageTitle  = 'AI Guides Admin | ' . SITE_NAME;
$totalGuides = count($guides);
$published   = count(array_filter($guides, fn($g) => $g['is_published']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= h($pageTitle) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
  <style>
    body{background:#fafaf9;color:#0f172a;font-family:'Inter',sans-serif;margin:0}
    .card{background:#ffffff;border:1px solid #e2e8f0;border-radius:.5rem}
    .btn-primary{background:#0f766e;color:#fff;border:none;cursor:pointer;transition:background .15s}
    .btn-primary:hover{background:#14b8a6}
    .btn-danger{background:#da3633;color:#fff;border:none;cursor:pointer;transition:background .15s}
    .btn-danger:hover{background:#b91c1c}
    .btn-outline{background:transparent;border:1px solid #e2e8f0;color:#64748b;cursor:pointer;transition:all .15s}
    .btn-outline:hover{border-color:#0f766e;color:#14b8a6}
    .inp{background:#fafaf9;border:1px solid #e2e8f0;color:#0f172a;border-radius:.375rem;padding:.5rem .75rem;font-size:.875rem;outline:none;font-family:'Inter',sans-serif}
    .inp:focus{border-color:#0f766e;box-shadow:0 0 0 3px rgba(35,134,54,.15)}
    .badge-green{background:rgba(35,134,54,.15);border:1px solid rgba(46,160,67,.4);color:#14b8a6;padding:.15rem .6rem;border-radius:.25rem;font-size:.7rem;font-family:'Space Mono',monospace}
    .badge-gray{background:#f5f5f4;border:1px solid #e2e8f0;color:#64748b;padding:.15rem .6rem;border-radius:.25rem;font-size:.7rem;font-family:'Space Mono',monospace}
    .badge-orange{background:rgba(210,90,0,.15);border:1px solid rgba(210,90,0,.4);color:#f59e0b;padding:.15rem .6rem;border-radius:.25rem;font-size:.7rem;font-family:'Space Mono',monospace}
    .badge-blue{background:rgba(31,111,235,.15);border:1px solid rgba(31,111,235,.4);color:#0ea5e9;padding:.15rem .6rem;border-radius:.25rem;font-size:.7rem;font-family:'Space Mono',monospace}
    a{transition:color .15s}
    textarea.inp{resize:vertical;line-height:1.6}
    .tbl-row:hover td{background:rgba(255,255,255,.02)}
    .alert-success{background:rgba(35,134,54,.15);border:1px solid rgba(46,160,67,.4);color:#14b8a6;padding:.75rem 1rem;border-radius:.375rem;margin-bottom:1rem}
    .alert-error{background:rgba(218,54,51,.15);border:1px solid rgba(218,54,51,.4);color:#ef4444;padding:.75rem 1rem;border-radius:.375rem;margin-bottom:1rem}
  </style>
</head>
<body class="min-h-screen">

<!-- Top Bar -->
<header class="border-b border-[#e2e8f0] bg-[#ffffff] sticky top-0 z-40">
  <div class="max-w-7xl mx-auto px-4 h-12 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="/admin/" class="text-[#64748b] hover:text-[#0f172a] text-sm font-mono flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
        Admin
      </a>
      <span class="text-[#e2e8f0]">/</span>
      <span class="text-[#0f172a] text-sm font-semibold">🤖 AI Guides</span>
    </div>
    <div class="flex items-center gap-3">
      <a href="/" target="_blank" class="text-xs text-[#64748b] hover:text-[#14b8a6] font-mono transition-colors">View Site →</a>
      <a href="/admin/logout.php" class="text-xs text-[#64748b] hover:text-[#ef4444] font-mono transition-colors">Logout</a>
    </div>
  </div>
</header>

<div class="max-w-7xl mx-auto px-4 py-8">

  <!-- Flash message -->
  <?php if ($flash): ?>
    <div class="<?= $flash['type']==='success' ? 'alert-success' : 'alert-error' ?>">
      <?= h($flash['msg']) ?>
    </div>
  <?php endif; ?>

  <?php if ($editMode): ?>
  <!-- ══════════════════════════════════════════════════════════════════════ -->
  <!-- EDITOR FORM                                                           -->
  <!-- ══════════════════════════════════════════════════════════════════════ -->
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-[#0f172a]">
      <?= $guide ? '✏️ Edit Guide' : '➕ New AI Guide' ?>
    </h1>
    <a href="/admin/ai-guides.php" class="btn-outline text-sm px-4 py-2 rounded">← Back to List</a>
  </div>

  <form method="POST" action="/admin/ai-guides.php">
    <input type="hidden" name="action" value="save" />
    <?php if ($guide): ?><input type="hidden" name="id" value="<?= (int)$guide['id'] ?>" /><?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Left: Main content -->
      <div class="lg:col-span-2 space-y-5">

        <div class="card p-5 space-y-4">
          <h2 class="text-sm font-semibold text-[#64748b] uppercase tracking-wider font-mono">Content</h2>

          <div>
            <label class="block text-xs text-[#64748b] font-mono uppercase tracking-wider mb-1.5">Title *</label>
            <input type="text" name="title" required value="<?= h($guide['title'] ?? '') ?>"
                   placeholder="e.g. ChatGPT Beginner Guide — Nepali मा"
                   class="inp w-full text-base" />
          </div>

          <div>
            <label class="block text-xs text-[#64748b] font-mono uppercase tracking-wider mb-1.5">Short Excerpt (for cards &amp; SEO)</label>
            <textarea name="excerpt" rows="2" placeholder="1-2 sentence summary..."
                      class="inp w-full"><?= h($guide['excerpt'] ?? '') ?></textarea>
          </div>

          <div>
            <label class="block text-xs text-[#64748b] font-mono uppercase tracking-wider mb-1.5">Content * (HTML allowed)</label>
            <div class="mb-2 flex flex-wrap gap-1.5">
              <?php foreach ([
                ['<h2>Heading</h2>','H2'],
                ['<h3>Sub-heading</h3>','H3'],
                ['<p>Paragraph</p>','Para'],
                ['<ul><li>Item</li></ul>','List'],
                ['<ol><li>Step</li></ol>','OL'],
                ['<blockquote>Quote</blockquote>','Quote'],
                ['<strong>Bold</strong>','Bold'],
                ['<code>code</code>','Code'],
              ] as [$tag,$lbl]): ?>
                <button type="button" onclick="insertTag(`<?= addslashes($tag) ?>`)"
                        class="px-2 py-1 text-xs bg-[#f5f5f4] border border-[#e2e8f0] text-[#64748b] hover:text-[#14b8a6] hover:border-[#0f766e] rounded transition-colors font-mono">
                  <?= $lbl ?>
                </button>
              <?php endforeach; ?>
            </div>
            <textarea id="content-editor" name="content" rows="18"
                      placeholder="Guide content — HTML tags supported (h2, h3, p, ul, ol, blockquote, strong, code)..."
                      class="inp w-full font-mono text-sm leading-relaxed"><?= h($guide['content'] ?? '') ?></textarea>
            <p class="text-xs text-[#64748b] font-mono mt-1.5">Tip: heading tags, lists, blockquotes, code — सबै HTML work गर्छ।</p>
          </div>
        </div>

      </div>

      <!-- Right: Meta -->
      <div class="space-y-5">

        <div class="card p-5 space-y-4">
          <h2 class="text-sm font-semibold text-[#64748b] uppercase tracking-wider font-mono">Settings</h2>

          <div>
            <label class="block text-xs text-[#64748b] font-mono uppercase tracking-wider mb-1.5">Icon (Emoji)</label>
            <input type="text" name="icon" value="<?= h($guide['icon'] ?? '🤖') ?>"
                   placeholder="🤖" class="inp w-full text-2xl" maxlength="10" />
            <p class="text-xs text-[#64748b] font-mono mt-1">One emoji — e.g. 🤖 ✨ 💡 🎨 💻 🇳🇵 📱 🔒</p>
          </div>

          <div>
            <label class="block text-xs text-[#64748b] font-mono uppercase tracking-wider mb-1.5">Category</label>
            <input type="text" name="category" value="<?= h($guide['category'] ?? '') ?>"
                   list="cats" placeholder="e.g. ChatGPT, Gemini..."
                   class="inp w-full" />
            <datalist id="cats">
              <?php
              $existingCats = db()->query("SELECT DISTINCT category FROM ai_guides ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
              foreach ($existingCats as $c): ?>
                <option value="<?= h($c) ?>">
              <?php endforeach; ?>
              <option value="ChatGPT"><option value="Gemini"><option value="Canva">
              <option value="Developer Tools"><option value="Prompt Tips">
              <option value="AI Trends"><option value="Design Tools"><option value="Nepal Tech">
            </datalist>
          </div>

          <div>
            <label class="block text-xs text-[#64748b] font-mono uppercase tracking-wider mb-1.5">Level</label>
            <select name="level" class="inp w-full">
              <?php foreach (['Beginner','Intermediate','Advanced'] as $l): ?>
                <option value="<?= $l ?>" <?= ($guide['level'] ?? 'Beginner') === $l ? 'selected' : '' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="flex items-center gap-3 pt-2 border-t border-[#e2e8f0]">
            <input type="checkbox" name="is_published" id="pub" value="1"
                   class="w-4 h-4 accent-green-600 cursor-pointer"
                   <?= ($guide['is_published'] ?? 1) ? 'checked' : '' ?> />
            <label for="pub" class="text-sm text-[#0f172a] cursor-pointer font-medium">Published</label>
          </div>
        </div>

        <!-- Actions -->
        <div class="card p-5 space-y-3">
          <button type="submit" class="btn-primary w-full py-2.5 rounded font-semibold text-sm">
            <?= $guide ? 'Update Guide' : 'Publish Guide' ?>
          </button>
          <a href="/admin/ai-guides.php" class="btn-outline block text-center w-full py-2.5 rounded text-sm">Cancel</a>
          <?php if ($guide): ?>
          <hr class="border-[#e2e8f0]">
          <form method="POST" onsubmit="return confirm('Delete this guide? This cannot be undone.')">
            <input type="hidden" name="action" value="delete" />
            <input type="hidden" name="id" value="<?= (int)$guide['id'] ?>" />
            <button type="submit" class="btn-danger w-full py-2.5 rounded font-semibold text-sm">Delete Guide</button>
          </form>
          <?php endif; ?>
        </div>

        <!-- Preview hint -->
        <?php if ($guide): ?>
        <div class="card p-4">
          <h3 class="text-xs font-mono text-[#64748b] uppercase tracking-wider mb-2">Preview</h3>
          <a href="/ai-guide.php?slug=<?= urlencode($guide['slug']) ?>" target="_blank"
             class="text-xs text-[#14b8a6] hover:underline font-mono break-all">
            /ai-guide.php?slug=<?= h(mb_substr($guide['slug'], 0, 40)) ?>...
          </a>
        </div>
        <?php endif; ?>

      </div>
    </div>
  </form>

  <?php else: ?>
  <!-- ══════════════════════════════════════════════════════════════════════ -->
  <!-- LIST VIEW                                                             -->
  <!-- ══════════════════════════════════════════════════════════════════════ -->
  <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
    <div>
      <h1 class="text-xl font-bold text-[#0f172a]">🤖 AI Guides</h1>
      <p class="text-xs text-[#64748b] font-mono mt-0.5"><?= $totalGuides ?> total · <?= $published ?> published</p>
    </div>
    <a href="/admin/ai-guides.php?action=create"
       class="btn-primary inline-flex items-center gap-2 px-5 py-2 rounded font-semibold text-sm">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      New Guide
    </a>
  </div>

  <!-- Stats row -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <?php
    $levels = ['Beginner'=>0,'Intermediate'=>0,'Advanced'=>0];
    foreach ($guides as $g) { if (isset($levels[$g['level']])) $levels[$g['level']]++; }
    ?>
    <div class="card p-4 text-center">
      <div class="text-2xl font-bold text-[#14b8a6] font-mono"><?= $totalGuides ?></div>
      <div class="text-xs text-[#64748b] font-mono mt-1">Total Guides</div>
    </div>
    <div class="card p-4 text-center">
      <div class="text-2xl font-bold text-[#0ea5e9] font-mono"><?= $published ?></div>
      <div class="text-xs text-[#64748b] font-mono mt-1">Published</div>
    </div>
    <div class="card p-4 text-center">
      <div class="text-2xl font-bold text-[#f59e0b] font-mono"><?= $levels['Beginner'] ?></div>
      <div class="text-xs text-[#64748b] font-mono mt-1">Beginner</div>
    </div>
    <div class="card p-4 text-center">
      <div class="text-2xl font-bold text-[#0f172a] font-mono"><?= array_sum(array_column($guides,'views')) ?></div>
      <div class="text-xs text-[#64748b] font-mono mt-1">Total Views</div>
    </div>
  </div>

  <!-- Guide table -->
  <div class="card overflow-hidden">
    <?php if (empty($guides)): ?>
      <div class="text-center py-16 text-[#64748b] font-mono">
        <div class="text-5xl mb-4">🤖</div>
        <p>No guides yet.</p>
        <a href="/admin/ai-guides.php?action=create" class="mt-4 inline-block text-[#14b8a6] hover:underline">Create first guide →</a>
      </div>
    <?php else: ?>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-[#e2e8f0]">
            <th class="text-left text-xs text-[#64748b] font-mono uppercase tracking-wider px-4 py-3 font-normal">Guide</th>
            <th class="text-left text-xs text-[#64748b] font-mono uppercase tracking-wider px-4 py-3 font-normal hidden md:table-cell">Category</th>
            <th class="text-left text-xs text-[#64748b] font-mono uppercase tracking-wider px-4 py-3 font-normal hidden lg:table-cell">Level</th>
            <th class="text-center text-xs text-[#64748b] font-mono uppercase tracking-wider px-4 py-3 font-normal hidden md:table-cell">Views</th>
            <th class="text-center text-xs text-[#64748b] font-mono uppercase tracking-wider px-4 py-3 font-normal">Status</th>
            <th class="text-right text-xs text-[#64748b] font-mono uppercase tracking-wider px-4 py-3 font-normal">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#f5f5f4]">
          <?php foreach ($guides as $g): ?>
          <tr class="tbl-row">
            <td class="px-4 py-3.5">
              <div class="flex items-center gap-3">
                <span class="text-xl shrink-0"><?= h($g['icon']) ?></span>
                <div>
                  <a href="/admin/ai-guides.php?action=edit&id=<?= $g['id'] ?>"
                     class="font-semibold text-[#0f172a] hover:text-[#14b8a6] transition-colors line-clamp-1">
                    <?= h($g['title']) ?>
                  </a>
                  <p class="text-xs text-[#64748b] font-mono mt-0.5"><?= date('M j, Y', strtotime($g['created_at'])) ?></p>
                </div>
              </div>
            </td>
            <td class="px-4 py-3.5 hidden md:table-cell">
              <span class="text-xs font-mono text-[#64748b]"><?= h($g['category']) ?></span>
            </td>
            <td class="px-4 py-3.5 hidden lg:table-cell">
              <span class="<?= $g['level']==='Beginner'?'badge-green':($g['level']==='Intermediate'?'badge-orange':'badge-blue') ?>">
                <?= h($g['level']) ?>
              </span>
            </td>
            <td class="px-4 py-3.5 text-center hidden md:table-cell">
              <span class="text-xs font-mono text-[#64748b]"><?= number_format($g['views']) ?></span>
            </td>
            <td class="px-4 py-3.5 text-center">
              <form method="POST" class="inline">
                <input type="hidden" name="action" value="toggle" />
                <input type="hidden" name="id" value="<?= $g['id'] ?>" />
                <input type="hidden" name="current" value="<?= $g['is_published'] ?>" />
                <button type="submit"
                  class="<?= $g['is_published'] ? 'badge-green' : 'badge-gray' ?> cursor-pointer hover:opacity-80 transition-opacity border-0">
                  <?= $g['is_published'] ? 'Live' : 'Draft' ?>
                </button>
              </form>
            </td>
            <td class="px-4 py-3.5">
              <div class="flex items-center justify-end gap-2">
                <a href="/ai-guide.php?slug=<?= urlencode($g['slug']) ?>" target="_blank"
                   class="text-xs font-mono text-[#64748b] hover:text-[#14b8a6] transition-colors" title="View">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </a>
                <a href="/admin/ai-guides.php?action=edit&id=<?= $g['id'] ?>"
                   class="text-xs font-mono text-[#64748b] hover:text-[#0ea5e9] transition-colors" title="Edit">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </a>
                <form method="POST" class="inline" onsubmit="return confirm('Delete this guide?')">
                  <input type="hidden" name="action" value="delete" />
                  <input type="hidden" name="id" value="<?= $g['id'] ?>" />
                  <button type="submit" class="text-[#64748b] hover:text-[#ef4444] transition-colors" title="Delete">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- Help box -->
  <div class="mt-6 card p-5">
    <h3 class="text-xs font-semibold text-[#64748b] uppercase tracking-wider font-mono mb-3">Tips</h3>
    <ul class="space-y-1.5 text-xs text-[#64748b] font-mono">
      <li>• <strong class="text-[#0f172a]">Status badge</strong> — click to toggle Live/Draft instantly</li>
      <li>• <strong class="text-[#0f172a]">Guides</strong> appear on homepage AI Guide section र /ai-guide.php page</li>
      <li>• <strong class="text-[#0f172a]">HTML tags</strong> — h2, h3, p, ul, ol, blockquote, strong, code सबै content मा काम गर्छन्</li>
      <li>• <strong class="text-[#0f172a]">Emoji icon</strong> — एउटा emoji हाल्नुस्, card मा देखिन्छ</li>
    </ul>
  </div>
  <?php endif; ?>

</div><!-- /container -->

<script>
function insertTag(tag) {
  const ta = document.getElementById('content-editor');
  if (!ta) return;
  const start = ta.selectionStart, end = ta.selectionEnd;
  const selected = ta.value.substring(start, end);
  // Extract opening tag and inject selected text inside it
  const openTag = tag.match(/^<[^>]+>/)[0];
  const closeTag = tag.match(/<\/[^>]+>$/)[0];
  const replacement = openTag + (selected || closeTag.replace('</','').replace('>','')) + closeTag;
  ta.value = ta.value.substring(0, start) + replacement + ta.value.substring(end);
  ta.focus();
  ta.selectionStart = ta.selectionEnd = start + replacement.length;
}
</script>
</body>
</html>
