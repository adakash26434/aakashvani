<?php
/**
 * /api/pwa-manifest.php
 * Dynamic PWA manifest. Icons point to /assets/icons/ where files actually live.
 */
header('Content-Type: application/manifest+json; charset=utf-8');
header('Cache-Control: public, max-age=3600');

require_once __DIR__ . '/../config.php';

$name      = defined('PWA_NAME')       ? PWA_NAME       : (defined('SITE_NAME') ? SITE_NAME : 'आकाशवाणी');
$shortName = defined('PWA_SHORT_NAME') ? PWA_SHORT_NAME : 'आकाशवाणी';
$theme     = defined('PWA_THEME_COLOR') ? PWA_THEME_COLOR : '#0f766e';

echo json_encode([
  'name'              => $name,
  'short_name'        => $shortName,
  'description'       => 'Nepal को सबै सेवा, समाचार, पात्रो, राशिफल, IPO र AI एकै App मा',
  'lang'              => 'ne-NP',
  'dir'               => 'ltr',
  'start_url'         => '/?utm_source=pwa&utm_medium=homescreen',
  'scope'             => '/',
  'display'           => 'standalone',
  'orientation'       => 'portrait-primary',
  'background_color'  => '#fafaf9',
  'theme_color'       => $theme,
  'categories'        => ['news','government','utilities','lifestyle'],
  'prefer_related_applications' => false,
  // FIX: correct path is /assets/icons/ (not /assets/images/)
  'icons' => [
    ['src'=>'/assets/icons/icon-48.png',  'sizes'=>'48x48',  'type'=>'image/png','purpose'=>'any'],
    ['src'=>'/assets/icons/icon-72.png',  'sizes'=>'72x72',  'type'=>'image/png','purpose'=>'any'],
    ['src'=>'/assets/icons/icon-96.png',  'sizes'=>'96x96',  'type'=>'image/png','purpose'=>'any'],
    ['src'=>'/assets/icons/icon-128.png', 'sizes'=>'128x128','type'=>'image/png','purpose'=>'any'],
    ['src'=>'/assets/icons/icon-192.png', 'sizes'=>'192x192','type'=>'image/png','purpose'=>'any'],
    ['src'=>'/assets/icons/icon-256.png', 'sizes'=>'256x256','type'=>'image/png','purpose'=>'any'],
    ['src'=>'/assets/icons/icon-512.png', 'sizes'=>'512x512','type'=>'image/png','purpose'=>'any'],
    ['src'=>'/assets/icons/icon-maskable-192.png','sizes'=>'192x192','type'=>'image/png','purpose'=>'maskable'],
    ['src'=>'/assets/icons/icon-maskable-512.png','sizes'=>'512x512','type'=>'image/png','purpose'=>'maskable'],
  ],
  'shortcuts' => [
    ['name'=>'समाचार','short_name'=>'News',  'url'=>'/news.php',
     'icons'=>[['src'=>'/assets/icons/icon-192.png','sizes'=>'192x192','type'=>'image/png']]],
    ['name'=>'पात्रो','short_name'=>'Patro','url'=>'/nepali-patro.php',
     'icons'=>[['src'=>'/assets/icons/icon-192.png','sizes'=>'192x192','type'=>'image/png']]],
    ['name'=>'राशिफल','short_name'=>'Rashifal','url'=>'/rashifal.php',
     'icons'=>[['src'=>'/assets/icons/icon-192.png','sizes'=>'192x192','type'=>'image/png']]],
    ['name'=>'IPO/NEPSE','short_name'=>'IPO','url'=>'/ipo-tracker.php',
     'icons'=>[['src'=>'/assets/icons/icon-192.png','sizes'=>'192x192','type'=>'image/png']]],
    ['name'=>'Alerts','short_name'=>'Alerts','url'=>'/alerts.php',
     'icons'=>[['src'=>'/assets/icons/icon-192.png','sizes'=>'192x192','type'=>'image/png']]],
  ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
