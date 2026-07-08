<?php
/**
 * api/news-rss.php — LIVE Nepali news aggregator
 * Fetches from multiple RSS feeds, categorizes, caches for 15 minutes.
 *
 * Query params:
 *   ?cat=politics|economy|sports|entertainment|world|technology|all   (default: all)
 *   ?limit=20                                                          (default 20, max 50)
 *   ?source=onlinekhabar|setopati|ratopati|bbc                         (optional)
 */

@ini_set('default_socket_timeout', 6);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// Security headers
sendSecurityHeaders();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=600');
header('Access-Control-Allow-Origin: *');

// Simple rate limiting
$rateKey = 'rss:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
if (!checkRateLimit($rateKey, 120, 60)) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Rate limit exceeded. Please try again later.']);
    exit;
}

$cat   = isset($_GET['cat'])   ? strtolower(trim($_GET['cat']))   : 'all';
$limit = isset($_GET['limit']) ? max(1, min(50, (int)$_GET['limit'])) : 20;
$sourceFilter = isset($_GET['source']) ? strtolower(trim($_GET['source'])) : '';

function nsh_source_key(string $name): string {
  $key = strtolower(trim($name));
  $key = preg_replace('/[^a-z0-9]+/i', '', $key);
  return $key ?: 'source';
}

try {
  ensureNewsTable();
  $sql = "SELECT id,title,slug,excerpt,content,category,source,source_url,source_name,original_url,image_url,created_at
          FROM tech_news WHERE is_published=1";
  $params = [];
  if ($cat && $cat !== 'all') {
    $sql .= " AND category=?";
    $params[] = $cat;
  }
  if ($sourceFilter) {
    $sql .= " AND (LOWER(REPLACE(COALESCE(source_name,source,''),' ',''))=? OR LOWER(REPLACE(COALESCE(source,''),' ',''))=?)";
    $params[] = $sourceFilter;
    $params[] = $sourceFilter;
  }
  $sql .= " ORDER BY created_at DESC LIMIT " . (int)$limit;
  $stmt = db()->prepare($sql);
  $stmt->execute($params);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  if ($rows) {
    $items = [];
    foreach ($rows as $r) {
      $sourceLabel = $r['source_name'] ?: ($r['source'] ?: 'आकाशवाणी');
      $sourceKey = nsh_source_key($sourceLabel);
      $ts = !empty($r['created_at']) ? strtotime($r['created_at']) : time();
      $items[] = [
        'id'          => (int)$r['id'],
        'slug'        => $r['slug'],
        'title'       => $r['title'],
        'link'        => $r['original_url'] ?: ($r['source_url'] ?: ''),
        'internalUrl' => '/news-detail.php?slug=' . rawurlencode($r['slug']) . '&url=' . rawurlencode($r['original_url'] ?: ($r['source_url'] ?: '')) . '&src=' . rawurlencode($sourceLabel),
        'image'       => $r['image_url'] ?: null,
        'source'      => $sourceKey,
        'sourceLabel' => $sourceLabel,
        'cat'         => strtolower($r['category'] ?: 'general'),
        'pubDate'     => $ts,
        'ago'         => nsh_ago($ts),
        'summary'     => $r['excerpt'] ?: mb_substr(strip_tags((string)$r['content']), 0, 260, 'UTF-8'),
        'hasContent'  => mb_strlen(trim((string)$r['content']), 'UTF-8') > 80,
      ];
    }
    echo json_encode([
      'ok' => true,
      'mode' => 'database',
      'count' => count($items),
      'cat' => $cat,
      'items' => $items,
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }
} catch (Throwable $e) { error_log("news-rss DB fallback: " . $e->getMessage()); }
}

$feeds = [
  // ── OnlineKhabar (general only - remove duplicates from category feeds) ──
  ['OnlineKhabar',          'https://www.onlinekhabar.com/feed',                       'general',       'onlinekhabar'],
  // ── Major Nepali dailies ──
  ['Setopati',              'https://www.setopati.com/feed',                           'general',       'setopati'],
  ['Ratopati',              'https://www.ratopati.com/feed',                           'general',       'ratopati'],
  ['Kantipur',              'https://ekantipur.com/feed',                              'general',       'kantipur'],
  ['Nagarik',               'https://nagariknews.nagariknetwork.com/feed',             'general',       'nagarik'],
  ['Annapurna Post',        'https://www.annapurnapost.com/feed',                      'general',       'annapurna'],
  ['Hamrakura',             'https://www.hamrakura.com/feed',                          'general',       'hamrakura'],
  // ── Tech / प्रविधि ──
  ['TechPana',              'https://techpana.com/feed/',                              'technology',    'techpana'],
  ['TechLekha',             'https://techlekha.com/feed/',                             'technology',    'techlekha'],
  ['TechSansar',            'https://techsansar.com/feed/',                            'technology',    'techsansar'],
  ['NepaliTelecom',         'https://nepalitelecom.com/feed/',                         'technology',    'nepalitelecom'],
  // ── Business / Markets ──
  ['ShareSansar',           'https://sharesansar.com/feed',                            'economy',       'sharesansar'],
  ['MeroLagani',            'https://merolagani.com/feed.aspx',                        'economy',       'merolagani'],
  ['ArthikPati',            'https://www.arthikpati.com/feed',                         'economy',       'arthikpati'],
  // ── Sports ──
  ['GoalNepal',             'https://goalnepal.com/feed/',                             'sports',        'goalnepal'],
  // ── English Nepali papers ──
  ['Kathmandu Post',        'https://kathmandupost.com/feed',                          'general',       'kathmandupost'],
  ['Himalayan Times',       'https://thehimalayantimes.com/feed/',                     'general',       'himalayantimes'],
  ['MyRepublica',           'https://myrepublica.nagariknetwork.com/feed/',            'general',       'myrepublica'],
  ['Rising Nepal',          'https://risingnepaldaily.com/feed/',                      'general',       'risingnepal'],
  // ── World ──
  ['BBC नेपाली',            'https://feeds.bbci.co.uk/nepali/rss.xml',                 'world',         'bbc'],
  ['BBC World',             'http://feeds.bbci.co.uk/news/world/rss.xml',              'world',         'bbcworld'],
  ['Al Jazeera',            'https://www.aljazeera.com/xml/rss/all.xml',               'world',         'aljazeera'],
];

$cacheDir  = __DIR__ . '/../cache';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
$cacheFile = $cacheDir . '/news-rss.json';
$cacheTtl  = 900;

$allItems = null;
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTtl)) {
  $cached = @json_decode(@file_get_contents($cacheFile), true);
  if (is_array($cached) && !empty($cached)) $allItems = $cached;
}

function nsh_fetch($url, $timeout = 5) {
  $ctx = stream_context_create([
    'http' => ['timeout'=>$timeout, 'user_agent'=>'Mozilla/5.0 Aakashvani/1.0', 'follow_location'=>1],
    'https'=> ['timeout'=>$timeout, 'user_agent'=>'Mozilla/5.0 Aakashvani/1.0', 'follow_location'=>1],
  ]);
  $body = @file_get_contents($url, false, $ctx);
  return $body !== false ? $body : '';
}

function nsh_extract_img($html) {
  if (!$html) return null;
  if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $m)) {
    $u = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
    if (filter_var($u, FILTER_VALIDATE_URL)) return $u;
  }
  if (preg_match('/<media:content[^>]+url=["\']([^"\']+)["\']/i', $html, $m)) {
    return html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
  }
  if (preg_match('/<enclosure[^>]+url=["\']([^"\']+\.(?:jpg|jpeg|png|webp|gif))["\']/i', $html, $m)) {
    return html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
  }
  return null;
}

function nsh_clean($html, $maxLen = 280) {
  $t = strip_tags(html_entity_decode($html, ENT_QUOTES, 'UTF-8'));
  // Strip RSS boilerplate
  $t = preg_replace('/The post .*? appeared first on .*?\.?$/iu', '', $t);
  $t = preg_replace('/\[\s*&#8230;\s*\]|\[\.\.\.\]/u', '', $t);
  $t = preg_replace('/(Read more|Continue reading|थप पढ्नुहोस्|विस्तृत.*$)/iu', '', $t);
  $t = preg_replace('/\s+/u', ' ', $t);
  $t = trim($t);
  if ($t === '') return '';
  if (mb_strlen($t, 'UTF-8') <= $maxLen) return $t;
  // Try to cut at last sentence boundary within maxLen window
  $window = mb_substr($t, 0, $maxLen + 80, 'UTF-8');
  if (preg_match_all('/[।\.\!\?]\s/u', $window, $m, PREG_OFFSET_CAPTURE)) {
    $lastPos = 0;
    foreach ($m[0] as $hit) {
      $bytePos = $hit[1] + strlen($hit[0]);
      $charPos = mb_strlen(substr($window, 0, $bytePos), 'UTF-8');
      if ($charPos <= $maxLen) $lastPos = $charPos;
    }
    if ($lastPos >= 80) {
      return rtrim(mb_substr($t, 0, $lastPos, 'UTF-8'));
    }
  }
  $cut = mb_substr($t, 0, $maxLen, 'UTF-8');
  $sp = mb_strrpos($cut, ' ', 0, 'UTF-8');
  if ($sp && $sp > 80) $cut = mb_substr($cut, 0, $sp, 'UTF-8');
  return rtrim($cut, " ,;:-") . '…';
}

function nsh_make_slug(string $title, string $url): string {
  // Create URL-safe slug from title + url hash
  $a = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $title) ?: '';
  $a = preg_replace('/[^a-zA-Z0-9\s-]/', '', $a);
  $s = preg_replace('/\s+/', '-', strtolower(trim($a)));
  $s = preg_replace('/-+/', '-', trim($s, '-'));
  $urlHash = substr(md5($url), 0, 6);
  return mb_substr($s ?: 'article', 0, 60) . '-' . $urlHash;
}

function nsh_ago($ts) {
  if (!$ts) return '';
  $diff = time() - $ts;
  if ($diff < 60) return 'भर्खरै';
  if ($diff < 3600) return floor($diff/60) . ' मिनेट अघि';
  if ($diff < 86400) return floor($diff/3600) . ' घण्टा अघि';
  if ($diff < 604800) return floor($diff/86400) . ' दिन अघि';
  $d = new DateTime('@'.$ts); $d->setTimezone(new DateTimeZone('Asia/Kathmandu'));
  return $d->format('M j');
}

function nsh_categorize($title, $defaultCat) {
  $t = mb_strtolower($title, 'UTF-8');
  $kw = [
    'politics'      => ['प्रधानमन्त्री','सरकार','संसद','मन्त्री','राजनीति','कां���्रेस','एमाले','माओवादी','राष्ट्रपति','दल','चुनाव','मन्त्रालय'],
    'economy'       => ['बजार','अर्थ','बैंक','शेयर','sharemarket','nepse','सुन','चाँदी','डलर','मूल्य','व्यापार','उद्योग','कर','बजेट','ipo'],
    'sports'        => ['खेल','क्रिकेट','फुटबल','गोल','खेलाडी','म्याच','टोली','विश्वकप','olympic','cricket','football'],
    'entertainment' => ['फिल्म','गायक','गायिका','कलाकार','नायक','नायिका','गीत','म्युजिक','सिनेमा','धारावाहिक','मनोरञ्जन'],
    'technology'    => ['प्रविधि','मोबाइल','इन्टरनेट','ai','गुगल','फेसबुक','एप','सफ्टवेयर','ntc','ncell','5g','technology','tech'],
    'world'         => ['विश्व','भारत','चीन','अमेरिका','रुस','युक्रेन','इजरायल','गाजा','world','global'],
  ];
  foreach ($kw as $c => $words) {
    foreach ($words as $w) if (mb_strpos($t, $w) !== false) return $c;
  }
  return $defaultCat !== 'general' ? $defaultCat : 'general';
}

function nsh_parse_feed($xml, $sourceLabel, $defaultCat, $sourceKey) {
  if (!$xml) return [];
  libxml_use_internal_errors(true);
  $sx = @simplexml_load_string($xml);
  if (!$sx) return [];
  $namespaces = $sx->getNamespaces(true);
  $items = [];
  $entries = isset($sx->channel->item) ? $sx->channel->item : (isset($sx->entry) ? $sx->entry : []);

  foreach ($entries as $e) {
    $title = trim((string)$e->title);
    if (!$title) continue;
    $link = trim((string)$e->link);
    if (!$link && isset($e->link['href'])) $link = (string)$e->link['href'];
    if (!$link) continue;

    $desc = (string)$e->description;
    if (!$desc && isset($namespaces['content'])) {
      $c = $e->children($namespaces['content']);
      if (isset($c->encoded)) $desc = (string)$c->encoded;
    }

    $img = nsh_extract_img($desc);
    if (!$img && isset($namespaces['media'])) {
      $m = $e->children($namespaces['media']);
      if (isset($m->content) && isset($m->content->attributes()->url)) {
        $img = (string)$m->content->attributes()->url;
      } elseif (isset($m->thumbnail) && isset($m->thumbnail->attributes()->url)) {
        $img = (string)$m->thumbnail->attributes()->url;
      }
    }
    if (!$img && isset($e->enclosure) && isset($e->enclosure->attributes()->url)) {
      $u = (string)$e->enclosure->attributes()->url;
      if (preg_match('/\.(jpg|jpeg|png|webp|gif)/i', $u)) $img = $u;
    }

    $pubRaw = (string)($e->pubDate ?: $e->published ?: $e->updated);
    $pubTs  = $pubRaw ? @strtotime($pubRaw) : 0;
    $cat = nsh_categorize($title, $defaultCat);

    $items[] = [
      'title'       => $title,
      'link'        => $link,
      'image'       => $img ?: null,
      'source'      => $sourceKey,
      'sourceLabel' => $sourceLabel,
      'cat'         => $cat,
      'pubDate'     => $pubTs,
      'ago'         => nsh_ago($pubTs),
      'summary'     => nsh_clean($desc, 280),
    ];
  }
  return $items;
}

if ($allItems === null) {
  $allItems = [];
  $storedCount = 0;
  
  // Ensure DB table exists
  try {
    ensureNewsTable();
  } catch (Throwable $e) {}
  
  foreach ($feeds as $f) {
    [$label, $url, $defaultCat, $key] = $f;
    $xml = nsh_fetch($url, 5);
    $parsed = nsh_parse_feed($xml, $label, $defaultCat, $key);
    
    foreach ($parsed as $item) {
      // Generate slug for internal URL
      $slug = nsh_make_slug($item['title'], $item['link']);
      $item['slug'] = $slug;
      $item['internalUrl'] = '/news-detail.php?slug=' . rawurlencode($slug) . '&url=' . rawurlencode($item['link']) . '&src=' . rawurlencode($label);
      
      // Store to database for internal reading
      try {
        $hash = md5($item['link']);
        // Check if already exists
        $check = db()->prepare("SELECT id, content FROM tech_news WHERE url_hash=? OR slug=? LIMIT 1");
        $check->execute([$hash, $slug]);
        $existing = $check->fetch();
        
        // Fetch full article content for storage
        $fullContent = '';
        $hasFullContent = false;
        
        // Only fetch if not already stored with substantial content
        if (!$existing || mb_strlen(trim($existing['content'] ?? ''), 'UTF-8') < 300) {
          // Try to fetch full article content
          require_once __DIR__ . '/../includes/article-fetch.php';
          if (function_exists('aakFetchArticle')) {
            try {
              $fetched = aakFetchArticle($item['link'], 21600);
              $scraped = trim($fetched['plain'] ?? implode("\n\n", $fetched['paragraphs'] ?? []));
              if (mb_strlen($scraped) > 300) {
                $fullContent = $scraped;
                $hasFullContent = true;
              }
            } catch (\Throwable $e) {}
          }
        }
        
        if (!$existing) {
          // Insert new article
          $pubDate = date('Y-m-d H:i:s', $item['pubDate'] ?: time());
          $excerpt = mb_substr($item['summary'], 0, 600);
          // Use full content if fetched, otherwise use excerpt as fallback
          $contentToStore = $fullContent ?: $excerpt;
          
          db()->prepare("INSERT IGNORE INTO tech_news 
            (title, slug, excerpt, content, category, source_name, original_url, url_hash, image_url, is_published, lang, scope, ai_processed, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 'ne', 'national', ?, ?, ?)")
            ->execute([
              mb_substr($item['title'], 0, 255),
              $slug,
              $excerpt,
              $contentToStore,
              $item['cat'],
              $label,
              mb_substr($item['link'], 0, 999),
              $hash,
              $item['image'] ?? null,
              $hasFullContent ? 1 : 0,
              $pubDate,
              $pubDate
            ]);
          $storedCount++;
        } elseif ($hasFullContent && $existing) {
          // Update existing article with full content if we now have it
          try {
            $up = db()->prepare("UPDATE tech_news SET content=?, ai_processed=1, updated_at=NOW() WHERE id=? AND (LENGTH(content) < 300 OR content IS NULL)");
            $up->execute([$fullContent, $existing['id']]);
          } catch (\Throwable $e) {}
        }
      } catch (Throwable $e) {
        // Continue even if DB insert fails
      }
      
      $allItems[] = $item;
    }
  }
  
  usort($allItems, fn($a,$b) => ($b['pubDate'] ?? 0) <=> ($a['pubDate'] ?? 0));
  
  // DEDUPLICATION
  $seen = []; 
  $unique = [];
  foreach ($allItems as $it) {
    $titleHash = md5(mb_strtolower(preg_replace('/\s+/u', '', $it['title']), 'UTF-8'));
    $urlHash = md5($it['link']);
    $combined = $titleHash . '|' . $urlHash;
    
    if (isset($seen[$combined])) continue;
    $seen[$combined] = 1;
    $unique[] = $it;
  }
  $allItems = $unique;
  
  // Add internalUrl to cached items if missing
  foreach ($allItems as &$it) {
    if (empty($it['internalUrl']) && !empty($it['slug'])) {
      $it['internalUrl'] = '/news-detail.php?slug=' . rawurlencode($it['slug']) . '&url=' . rawurlencode($it['link'] ?? '') . '&src=' . rawurlencode($it['sourceLabel'] ?? '');
    }
  }
  
  @file_put_contents($cacheFile, json_encode($allItems, JSON_UNESCAPED_UNICODE));
  
  // Trigger background content expansion if articles were stored
  if ($storedCount > 0) {
    @file_get_contents('/api/news-expand.php?background=1', false, stream_context_create([
      'http' => ['timeout' => 1, 'ignore_errors' => true]
    ]));
  }
}

$out = $allItems;
if ($cat && $cat !== 'all') {
  $out = array_values(array_filter($out, fn($x) => $x['cat'] === $cat));
}
if ($sourceFilter) {
  $out = array_values(array_filter($out, fn($x) => $x['source'] === $sourceFilter));
}
$out = array_slice($out, 0, $limit);

// Ensure internalUrl is set for all items
foreach ($out as &$item) {
  if (empty($item['internalUrl']) && !empty($item['slug'])) {
    $item['internalUrl'] = '/news-detail.php?slug=' . rawurlencode($item['slug']) . '&url=' . rawurlencode($item['link'] ?? '') . '&src=' . rawurlencode($item['sourceLabel'] ?? '');
  }
  // Map to expected API format
  $item['id'] = $item['id'] ?? 0;
  $item['hasContent'] = true; // All items now have DB content
}

echo json_encode([
  'ok'    => true,
  'mode'  => 'database-synced',
  'count' => count($out),
  'cat'   => $cat,
  'items' => $out,
], JSON_UNESCAPED_UNICODE);
