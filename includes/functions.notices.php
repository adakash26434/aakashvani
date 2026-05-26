<?php
/**
 * App Notices — popup/banner announcements
 * Add to functions.entertainment.php OR include separately:
 *   require_once __DIR__ . '/includes/functions.notices.php';
 *
 * Usage in header.php (or layout):
 *   <?php require_once __DIR__ . '/includes/notices_render.php'; ?>
 */

if (!defined('NOTICE_UPLOAD_DIR')) define('NOTICE_UPLOAD_DIR', __DIR__ . '/../uploads/notices/');
if (!defined('NOTICE_UPLOAD_URL')) define('NOTICE_UPLOAD_URL', '/uploads/notices/');

/**
 * Get currently active notices (respects schedule window + active flag).
 * Returns ordered by priority DESC, created_at DESC.
 */
function getActiveAppNotices(int $limit = 5): array {
    // Defensive: ensure db() is available (legacy include paths may skip functions.php)
    if (!function_exists('db')) {
        $fn = dirname(__DIR__) . '/functions.php';
        if (is_file($fn)) { require_once dirname(__DIR__) . '/config.php'; require_once $fn; }
    }
    if (!function_exists('db')) { error_log('[notices] db() unavailable'); return []; }
    try {
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT * FROM app_notices
                WHERE active = 1
                  AND (show_from IS NULL OR show_from <= ?)
                  AND (show_until IS NULL OR show_until >= ?)
                ORDER BY priority DESC, created_at DESC
                LIMIT " . (int) $limit;
        $s = db()->prepare($sql);
        $s->execute([$now, $now]);
        return $s->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        error_log('[notices] ' . $e->getMessage());
        return [];
    }
}

function trackNoticeView(int $noticeId): void {
    if (!function_exists('db')) return;
    try { db()->prepare('UPDATE app_notices SET views = views + 1 WHERE id = ?')->execute([$noticeId]); }
    catch (Throwable $e) { error_log('[notices view] '.$e->getMessage()); }
}

function trackNoticeClick(int $noticeId): void {
    if (!function_exists('db')) return;
    try { db()->prepare('UPDATE app_notices SET clicks = clicks + 1 WHERE id = ?')->execute([$noticeId]); }
    catch (Throwable $e) { error_log('[notices click] '.$e->getMessage()); }
}

/**
 * Save uploaded notice document (PDF / image / doc).
 * Returns associative array [path, name, size, mime] or null on failure.
 */
function notice_save_document(array $file): ?array {
    if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
    if (!is_dir(NOTICE_UPLOAD_DIR)) @mkdir(NOTICE_UPLOAD_DIR, 0755, true);

    $allowed = [
        'application/pdf' => 'pdf',
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png',
        'image/webp'      => 'webp',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    ];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) return null;
    if ($file['size'] > 12 * 1024 * 1024) return null; // 12 MB cap

    $ext  = $allowed[$mime];
    $orig = preg_replace('/[^A-Za-z0-9_\-\.\x{0900}-\x{097F}]/u', '_', $file['name'] ?? 'document');
    $name = date('Ymd-His') . '-' . substr(md5(uniqid('', true)), 0, 6) . '.' . $ext;

    if (!move_uploaded_file($file['tmp_name'], NOTICE_UPLOAD_DIR . $name)) return null;
    return [
        'path' => NOTICE_UPLOAD_URL . $name,
        'name' => $orig,
        'size' => (int) $file['size'],
        'mime' => $mime,
    ];
}

function notice_delete_document(?string $path): void {
    if (!$path) return;
    $abs = __DIR__ . '/..' . str_replace(NOTICE_UPLOAD_URL, '/uploads/notices/', $path);
    if (is_file($abs)) @unlink($abs);
}
