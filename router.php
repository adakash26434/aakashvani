<?php
/**
 * PHP Built-in Server Router
 * Handles URL routing for development server
 * Usage: php -S 0.0.0.0:PORT router.php
 */

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Serve static files directly (images, CSS, JS, etc.)
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$staticExts = ['css','js','png','jpg','jpeg','gif','svg','ico','webp','woff','woff2','ttf','eot','pdf'];
if (in_array($ext, $staticExts) && file_exists(__DIR__ . $path)) {
    return false; // Let PHP serve it directly
}

// Route to correct PHP file
$routes = [
    '/'              => '/index.php',
    '/index'         => '/index.php',
    '/home'          => '/index.php',
    '/news'          => '/news.php',
    '/news-post'     => '/news-post.php',
    '/notices'       => '/notices.php',
    '/alerts'        => '/alerts.php',
    '/rashifal'      => '/rashifal.php',
    '/tools'         => '/tools.php',
    '/contact'       => '/contact.php',
    '/search'        => '/search.php',
    '/install'       => '/install.php',
    '/nepali-patro'  => '/nepali-patro.php',
    '/patro'         => '/nepali-patro.php',
    '/utilities'     => '/utilities.php',
    '/ipo-tracker'   => '/ipo-tracker.php',
    '/tax-calculator'=> '/tax-calculator.php',
    '/gov-services'  => '/gov-services.php',
    '/emergency'     => '/emergency.php',
    '/loksewa'       => '/loksewa.php',
    '/downloads'     => '/downloads.php',
    '/morning-brief' => '/morning-brief.php',
    '/vehicle'       => '/vehicle.php',
    '/ssf'           => '/ssf.php',
    '/ai-guides'     => '/ai-guides.php',
    '/pdf-merge'     => '/pdf-merge.php',
    '/pdf-split'     => '/pdf-split.php',
    '/pdf-convert'   => '/pdf-convert.php',
    '/about'         => '/about.php',
    '/bookmarks'     => '/bookmarks.php',
    '/profile'       => '/profile.php',
    '/settings'      => '/settings.php',
    '/dashboard'     => '/dashboard.php',
    '/help'          => '/help.php',
    '/info'          => '/info-hub.php',
    '/info-hub'      => '/info-hub.php',
    '/auth/login'   => '/auth/login.php',
    '/auth/register'=> '/auth/register.php',
    '/auth/logout'  => '/auth/logout.php',
    '/auth/google'  => '/auth/google.php',
    '/auth/facebook'=> '/auth/facebook.php',
];

// Remove trailing slash except root
$clean = rtrim($path, '/') ?: '/';

// Check exact match first
if (isset($routes[$clean])) {
    $file = __DIR__ . $routes[$clean];
    if (file_exists($file)) {
        require $file;
        return true;
    }
}

// Check if it's a direct .php file
if ($ext === 'php' && file_exists(__DIR__ . $path)) {
    return false; // Let PHP serve it
}

// Try adding .php
if (file_exists(__DIR__ . $clean . '.php')) {
    require __DIR__ . $clean . '.php';
    return true;
}

// API routes — /api/*.php
if (str_starts_with($clean, '/api/')) {
    $apiFile = __DIR__ . $clean;
    if (file_exists($apiFile)) { require $apiFile; return true; }
    if (file_exists($apiFile . '.php')) { require $apiFile . '.php'; return true; }
}

// Admin routes
if (str_starts_with($clean, '/admin')) {
    $adminPath = __DIR__ . $clean;
    if (is_dir($adminPath)) {
        $idx = $adminPath . '/index.php';
        if (file_exists($idx)) { require $idx; return true; }
    }
    if (file_exists($adminPath . '.php')) { require $adminPath . '.php'; return true; }
    if (file_exists($adminPath))           { return false; }
}

// 404 fallback
http_response_code(404);
require __DIR__ . '/index.php';
return true;
