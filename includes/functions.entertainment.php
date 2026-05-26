<?php
/**
 * आकाशवाणी — Entertainment Hub functions
 * 
 * Drop-in include. Add to bottom of functions.php:
 *     require_once __DIR__ . '/includes/functions.entertainment.php';
 * 
 * Provides:
 *   - maybeRefreshSuccessStories()  — RSS-driven auto sync
 *   - getSuccessStories($limit, $featured)
 *   - getVisitPlaces($limit, $featured)
 *   - getRadioStations()
 *   - getRadioPodcasts($limit)
 *   - entSlugify($text)
 */

if (!defined('SS_SYNC_INTERVAL'))      define('SS_SYNC_INTERVAL', 7200);   // 2 hr
if (!defined('SS_MAX_PER_SYNC'))       define('SS_MAX_PER_SYNC', 15);
if (!defined('PODCAST_SYNC_INTERVAL')) define('PODCAST_SYNC_INTERVAL', 10800); // 3 hr
if (!defined('VISIT_UPLOAD_DIR'))      define('VISIT_UPLOAD_DIR', __DIR__ . '/../uploads/visit/');
if (!defined('VISIT_UPLOAD_URL'))      define('VISIT_UPLOAD_URL', '/uploads/visit/');

/* ─── Success Story sources (RSS) ──────────────────────────────────────── */
function ent_success_sources(): array {
    return [
        ['name' => 'OnlineKhabar', 'url' => 'https://www.onlinekhabar.com/feed', 'keywords' => ['सफलता','संघर्ष','प्रेरणा','उद्यमी','कथा','यात्रा']],
        ['name' => 'Setopati',     'url' => 'https://www.setopati.com/feed',     'keywords' => ['सफलता','कथा','प्रेरणा','उद्यमी','युवा']],
        ['name' => 'Ratopati',     'url' => 'https://www.ratopati.com/feed',     'keywords' => ['सफलता','प्रेरणा','कथा','उद्यमी']],
    ];
}

/* ─── Slug helper (Nepali-safe) ────────────────────────────────────────── */
function entSlugify(string $text, int $max = 180): string {
    $text = trim($text);
    $text = preg_replace('/[\/\\\?\&\=\#\.\,\!\?\:\;\"\'\(\)\[\]\{\}]+/u', '', $text);
    $text = preg_replace('/\s+/u', '-', $text);
    $text = trim($text, '-');
    if (mb_strlen($text, 'UTF-8') > $max) {
        $text = mb_substr($text, 0, $max, 'UTF-8');
    }
    if ($text === '') $text = 'item-' . substr(md5(microtime()), 0, 8);
    return $text;
}

/* ─── Lazy refresh: success stories ────────────────────────────────────── */
function maybeRefreshSuccessStories(): void {
    $flag = sys_get_temp_dir() . '/nsh_ss_sync.flag';
    if (file_exists($flag) && (time() - filemtime($flag)) < SS_SYNC_INTERVAL) return;
    @touch($flag);
    try { syncSuccessStoriesFromRss(); } catch (Throwable $e) { error_log('[SS sync] ' . $e->getMessage()); }
}

function syncSuccessStoriesFromRss(): int {
    $pdo = db();
    $inserted = 0;
    $perSource = max(1, (int) ceil(SS_MAX_PER_SYNC / max(1, count(ent_success_sources()))));

    foreach (ent_success_sources() as $src) {
        $items = ent_fetch_rss($src['url'], $perSource * 3);
        if (!$items) continue;

        $matched = 0;
        foreach ($items as $it) {
            if ($matched >= $perSource) break;
            $title   = trim($it['title'] ?? '');
            $summary = trim(strip_tags($it['description'] ?? ''));
            if ($title === '') continue;

            // keyword filter
            $haystack = $title . ' ' . $summary;
            $hit = false;
            foreach ($src['keywords'] as $kw) {
                if (mb_stripos($haystack, $kw) !== false) { $hit = true; break; }
            }
            if (!$hit) continue;

            $guid = $it['guid'] ?? ($it['link'] ?? md5($title));
            // dedup
            $chk = $pdo->prepare('SELECT id FROM success_stories WHERE source_guid = ? LIMIT 1');
            $chk->execute([$guid]);
            if ($chk->fetch()) continue;

            $slug = entSlugify($title);
            // ensure unique slug
            $i = 1; $base = $slug;
            while (true) {
                $u = $pdo->prepare('SELECT id FROM success_stories WHERE slug = ? LIMIT 1');
                $u->execute([$slug]);
                if (!$u->fetch()) break;
                $slug = $base . '-' . (++$i);
            }

            $stmt = $pdo->prepare('INSERT INTO success_stories
                (slug, title, summary, hero_image, category, source_type, source_name, source_url, source_guid, published_at, status)
                VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([
                $slug,
                mb_substr($title, 0, 480, 'UTF-8'),
                mb_substr($summary, 0, 1200, 'UTF-8'),
                $it['image'] ?? null,
                'general',
                'rss',
                $src['name'],
                $it['link'] ?? null,
                $guid,
                !empty($it['pubDate']) ? date('Y-m-d H:i:s', strtotime($it['pubDate'])) : date('Y-m-d H:i:s'),
                'published',
            ]);
            $inserted++; $matched++;
        }
    }
    return $inserted;
}

/* ─── Tiny RSS parser (no deps) ────────────────────────────────────────── */
function ent_fetch_rss(string $url, int $limit = 30): array {
    $ctx = stream_context_create(['http' => ['timeout' => 8, 'user_agent' => 'AakashvaniBot/1.0']]);
    $xml = @file_get_contents($url, false, $ctx);
    if (!$xml) return [];
    libxml_use_internal_errors(true);
    $sx = simplexml_load_string($xml);
    if (!$sx) return [];
    $out = [];
    $items = $sx->channel->item ?? $sx->entry ?? [];
    foreach ($items as $it) {
        $img = null;
        $enc = $it->enclosure ?? null;
        if ($enc && isset($enc['url'])) $img = (string) $enc['url'];
        if (!$img) {
            $desc = (string) ($it->description ?? $it->summary ?? '');
            if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $desc, $m)) $img = $m[1];
        }
        $out[] = [
            'title'       => (string) ($it->title ?? ''),
            'link'        => (string) ($it->link['href'] ?? $it->link ?? ''),
            'description' => (string) ($it->description ?? $it->summary ?? ''),
            'pubDate'     => (string) ($it->pubDate ?? $it->updated ?? ''),
            'guid'        => (string) ($it->guid ?? $it->id ?? ''),
            'image'       => $img,
        ];
        if (count($out) >= $limit) break;
    }
    return $out;
}

/* ─── Readers ──────────────────────────────────────────────────────────── */
function getSuccessStories(int $limit = 12, bool $featuredOnly = false): array {
    $where = "status='published'" . ($featuredOnly ? " AND featured=1" : "");
    $sql = "SELECT id, slug, title, summary, hero_image, source_name, source_url,
                   category, featured, published_at, views
            FROM success_stories WHERE $where
            ORDER BY featured DESC, published_at DESC LIMIT " . (int) $limit;
    return db()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function getSuccessStoryBySlug(string $slug): ?array {
    $s = db()->prepare("SELECT * FROM success_stories WHERE slug=? AND status='published' LIMIT 1");
    $s->execute([$slug]);
    $row = $s->fetch(PDO::FETCH_ASSOC);
    if ($row) db()->prepare('UPDATE success_stories SET views=views+1 WHERE id=?')->execute([$row['id']]);
    return $row ?: null;
}

function getVisitPlaces(int $limit = 24, bool $featuredOnly = false, ?string $district = null): array {
    $sql = "SELECT id, slug, title, title_en, short_caption, image_path, image_thumb,
                   district, province, region, category, featured, views
            FROM visit_places WHERE status='published'";
    $params = [];
    if ($featuredOnly) $sql .= " AND featured=1";
    if ($district)     { $sql .= " AND district = ?"; $params[] = $district; }
    $sql .= " ORDER BY featured DESC, sort_order ASC, created_at DESC LIMIT " . (int) $limit;
    $s = db()->prepare($sql); $s->execute($params);
    return $s->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function getVisitPlaceBySlug(string $slug): ?array {
    $s = db()->prepare("SELECT * FROM visit_places WHERE slug=? AND status='published' LIMIT 1");
    $s->execute([$slug]);
    $row = $s->fetch(PDO::FETCH_ASSOC);
    if ($row) db()->prepare('UPDATE visit_places SET views=views+1 WHERE id=?')->execute([$row['id']]);
    return $row ?: null;
}

function getRadioStations(bool $activeOnly = true): array {
    $sql = "SELECT * FROM radio_stations" . ($activeOnly ? " WHERE status='active'" : "")
         . " ORDER BY featured DESC, sort_order ASC, name ASC";
    return db()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Sync radio stations from Radio-Browser API
 * This fetches Nepal radio stations and updates/creates them in database
 */
function syncRadioStationsFromAPI(): array {
    $apiUrl = 'https://de1.api.radio-browser.info/json/stations/search?countrycode=NP&limit=100&order=clickcount&reverse=true';
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Aakashvani/1.0');
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$response) {
        return ['success' => false, 'message' => 'Failed to fetch from API', 'count' => 0];
    }
    
    $stations = json_decode($response, true);
    if (!is_array($stations)) {
        return ['success' => false, 'message' => 'Invalid API response', 'count' => 0];
    }
    
    $db = db();
    $added = 0;
    $updated = 0;
    
    foreach ($stations as $station) {
        $name = $station['name'] ?? '';
        $streamingUrl = $station['url_resolved'] ?? $station['url'] ?? '';
        $website = $station['homepage'] ?? '';
        $favicon = $station['favicon'] ?? '';
        
        if (empty($name) || empty($streamingUrl)) continue;
        
        // Check if station already exists by streaming URL
        $existing = $db->prepare('SELECT id FROM radio_stations WHERE streaming_url = ? LIMIT 1');
        $existing->execute([$streamingUrl]);
        $row = $existing->fetch();
        
        if ($row) {
            // Update existing
            $db->prepare('UPDATE radio_stations SET name=?, website=?, logo_path=?, status=? WHERE id=?')->execute([
                $name, $website, $favicon, 'active', $row['id']
            ]);
            $updated++;
        } else {
            // Insert new
            $db->prepare('INSERT INTO radio_stations (name, name_en, logo_path, website, streaming_url, status, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)')->execute([
                $name, $name, $favicon, $website, $streamingUrl, 'active', 999
            ]);
            $added++;
        }
    }
    
    return [
        'success' => true,
        'message' => "Synced: $added added, $updated updated",
        'count' => $added + $updated,
        'added' => $added,
        'updated' => $updated
    ];
}

function getRadioPodcasts(int $limit = 20): array {
    $sql = "SELECT p.*, s.name AS station_name, s.logo_path AS station_logo
            FROM radio_podcasts p
            LEFT JOIN radio_stations s ON s.id = p.station_id
            WHERE p.status='published'
            ORDER BY p.published_at DESC LIMIT " . (int) $limit;
    return db()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/* ─── Admin helpers: visit place upload ────────────────────────────────── */
function ent_save_uploaded_image(array $file): ?string {
    if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
    if (!is_dir(VISIT_UPLOAD_DIR)) @mkdir(VISIT_UPLOAD_DIR, 0755, true);

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) return null;
    if ($file['size'] > 8 * 1024 * 1024) return null; // 8 MB cap

    $ext  = $allowed[$mime];
    $name = date('Ymd-His') . '-' . substr(md5(uniqid('', true)), 0, 8) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], VISIT_UPLOAD_DIR . $name)) return null;
    return VISIT_UPLOAD_URL . $name;
}
