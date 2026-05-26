<?php
/**
 * Admin Data Manager API
 * For managing data when auto-fetch fails
 * All admin-manageable data in one place
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// Check admin auth
session_start();
if (empty($_SESSION['nh_admin']) && empty($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

$cacheDir = __DIR__ . '/../cache';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$section = $_GET['section'] ?? $_POST['section'] ?? '';

$allowedSections = [
    'nepse' => ['name' => 'NEPSE Market Data', 'file' => 'nepse.json'],
    'gold' => ['name' => 'Gold/Silver Prices', 'file' => 'gold.json'],
    'petrol' => ['name' => 'Fuel Prices', 'file' => 'petrol.json'],
    'forex' => ['name' => 'Forex Rates', 'file' => 'forex.json'],
    'loksewa' => ['name' => 'Lok Sewa Notices', 'file' => 'loksewa.json'],
    'loadshedding' => ['name' => 'Load Shedding', 'file' => 'loadshedding.json'],
    'traffic' => ['name' => 'Traffic Updates', 'file' => 'traffic.json'],
    'water' => ['name' => 'Water Supply', 'file' => 'water.json'],
    'weather' => ['name' => 'Weather', 'file' => 'weather.json'],
];

function readAdminData(string $file): ?array {
    global $cacheDir;
    $path = $cacheDir . '/' . $file;
    if (!file_exists($path)) return null;
    $json = @file_get_contents($path);
    return $json ? json_decode($json, true) : null;
}

function writeAdminData(string $file, array $data): bool {
    global $cacheDir;
    $path = $cacheDir . '/' . $file;
    $data['admin_updated'] = date('Y-m-d H:i:s');
    $data['admin_user'] = $_SESSION['admin_user'] ?? 'admin';
    return @file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) !== false;
}

switch ($action) {
    case 'list':
        // List all sections with their status
        $result = [];
        foreach ($allowedSections as $key => $cfg) {
            $data = readAdminData($cfg['file']);
            $result[$key] = [
                'name' => $cfg['name'],
                'has_data' => !empty($data),
                'last_updated' => $data['admin_updated'] ?? $data['updatedAt'] ?? null,
                'source' => $data['source'] ?? 'auto',
                'is_auto' => empty($data['admin_updated']),
            ];
        }
        echo json_encode(['ok' => true, 'sections' => $result]);
        break;

    case 'get':
        if (!isset($allowedSections[$section])) {
            echo json_encode(['ok' => false, 'error' => 'Invalid section']);
            exit;
        }
        $data = readAdminData($allowedSections[$section]['file']);
        echo json_encode(['ok' => true, 'data' => $data, 'section' => $section]);
        break;

    case 'save':
        if (!isset($allowedSections[$section])) {
            echo json_encode(['ok' => false, 'error' => 'Invalid section']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            echo json_encode(['ok' => false, 'error' => 'Invalid data']);
            exit;
        }
        
        if (writeAdminData($allowedSections[$section]['file'], $input)) {
            echo json_encode(['ok' => true, 'message' => 'Data saved successfully']);
        } else {
            echo json_encode(['ok' => false, 'error' => 'Failed to save']);
        }
        break;

    case 'clear':
        if (!isset($allowedSections[$section])) {
            echo json_encode(['ok' => false, 'error' => 'Invalid section']);
            exit;
        }
        $path = $cacheDir . '/' . $allowedSections[$section]['file'];
        if (file_exists($path)) {
            unlink($path);
        }
        echo json_encode(['ok' => true, 'message' => 'Cache cleared']);
        break;

    case 'api-status':
        // Check API health
        $apis = [
            'nepse' => ['url' => 'https://merolagani.com/LatestMarket.aspx', 'type' => ' scrape'],
            'gold' => ['url' => 'https://www.fenegosida.org/', 'type' => 'scrape'],
            'forex' => ['url' => 'https://www.nrb.org.np/forex/', 'type' => 'scrape'],
            'psc' => ['url' => 'https://psc.gov.np/', 'type' => 'scrape'],
            'weather' => ['url' => 'https://api.open-meteo.com/v1/forecast', 'type' => 'api'],
        ];
        
        $status = [];
        foreach ($apis as $name => $cfg) {
            $start = microtime(true);
            $ch = curl_init($cfg['url']);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $result = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $time = round((microtime(true) - $start) * 1000);
            curl_close($ch);
            
            $status[$name] = [
                'status' => $code === 200 ? 'ok' : 'error',
                'code' => $code,
                'response_time_ms' => $time,
                'url' => $cfg['url'],
            ];
        }
        
        echo json_encode(['ok' => true, 'apis' => $status]);
        break;

    default:
        echo json_encode(['ok' => false, 'error' => 'Unknown action']);
}
