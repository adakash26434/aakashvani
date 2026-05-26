<?php
/**
 * API: Radio Stations - Fetch from external source or database
 * Falls back to sample data if no data available
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/functions.entertainment.php';

try {
    // Try to get from database first
    $stations = getRadioStations(true);
    
    if (empty($stations)) {
        // Fallback to sample data
        $stations = [
            [
                'id' => 1,
                'name' => 'Radio Nepal',
                'stream_url' => 'https://stream.zeno.fm/yn8s9y5y598uv',
                'stream_type' => 'mp3',
                'city' => 'Kathmandu',
                'frequency' => '103.0 FM',
                'logo_path' => '',
                'status' => 'active',
                'featured' => 1,
                'sort_order' => 1
            ],
            [
                'id' => 2,
                'name' => 'Kantipur FM',
                'stream_url' => 'https://stream.zeno.fm/0r0xa792kwzuv',
                'stream_type' => 'mp3',
                'city' => 'Kathmandu',
                'frequency' => '96.6 FM',
                'logo_path' => '',
                'status' => 'active',
                'featured' => 1,
                'sort_order' => 2
            ],
            [
                'id' => 3,
                'name' => 'Image FM',
                'stream_url' => 'https://stream.zeno.fm/f3wv6q5g2k8uv',
                'stream_type' => 'mp3',
                'city' => 'Kathmandu',
                'frequency' => '97.9 FM',
                'logo_path' => '',
                'status' => 'active',
                'featured' => 0,
                'sort_order' => 3
            ],
            [
                'id' => 4,
                'name' => 'Hits FM',
                'stream_url' => 'https://stream.zeno.fm/s45x6y5g2k8uv',
                'stream_type' => 'mp3',
                'city' => 'Kathmandu',
                'frequency' => '91.2 FM',
                'logo_path' => '',
                'status' => 'active',
                'featured' => 0,
                'sort_order' => 4
            ],
            [
                'id' => 5,
                'name' => 'Kalika FM',
                'stream_url' => 'https://stream.zeno.fm/6y8s9y5y598uv',
                'stream_type' => 'mp3',
                'city' => 'Pokhara',
                'frequency' => '95.2 FM',
                'logo_path' => '',
                'status' => 'active',
                'featured' => 0,
                'sort_order' => 5
            ],
            [
                'id' => 6,
                'name' => 'Radio Nagarik',
                'stream_url' => 'https://stream.zeno.fm/2r0xa792kwzuv',
                'stream_type' => 'mp3',
                'city' => 'Kathmandu',
                'frequency' => '107.5 FM',
                'logo_path' => '',
                'status' => 'active',
                'featured' => 0,
                'sort_order' => 6
            ],
            [
                'id' => 7,
                'name' => 'Focal FM',
                'stream_url' => 'https://stream.zeno.fm/7y8s9y5y598uv',
                'stream_type' => 'mp3',
                'city' => 'Kathmandu',
                'frequency' => '92.4 FM',
                'logo_path' => '',
                'status' => 'active',
                'featured' => 0,
                'sort_order' => 7
            ],
            [
                'id' => 8,
                'name' => 'Maitri FM',
                'stream_url' => 'https://stream.zeno.fm/8r0xa792kwzuv',
                'stream_type' => 'mp3',
                'city' => 'Kathmandu',
                'frequency' => '106.6 FM',
                'logo_path' => '',
                'status' => 'active',
                'featured' => 0,
                'sort_order' => 8
            ],
            [
                'id' => 9,
                'name' => 'Nepal FM',
                'stream_url' => 'https://stream.zeno.fm/9y8s9y5y598uv',
                'stream_type' => 'mp3',
                'city' => 'Kathmandu',
                'frequency' => '91.8 FM',
                'logo_path' => '',
                'status' => 'active',
                'featured' => 0,
                'sort_order' => 9
            ],
            [
                'id' => 10,
                'name' => 'Star FM',
                'stream_url' => 'https://stream.zeno.fm/0r0xa792kwzuv',
                'stream_type' => 'mp3',
                'city' => 'Kathmandu',
                'frequency' => '94.0 FM',
                'logo_path' => '',
                'status' => 'active',
                'featured' => 0,
                'sort_order' => 10
            ],
            [
                'id' => 11,
                'name' => 'Radio Birgunj',
                'stream_url' => 'https://stream.zeno.fm/1r0xa792kwzuv',
                'stream_type' => 'mp3',
                'city' => 'Birgunj',
                'frequency' => '94.6 FM',
                'logo_path' => '',
                'status' => 'active',
                'featured' => 0,
                'sort_order' => 11
            ],
            [
                'id' => 12,
                'name' => 'Radio Chitwan',
                'stream_url' => 'https://stream.zeno.fm/2r0xa792kwzuv',
                'stream_type' => 'mp3',
                'city' => 'Chitwan',
                'frequency' => '92.0 FM',
                'logo_path' => '',
                'status' => 'active',
                'featured' => 0,
                'sort_order' => 12
            ],
            [
                'id'  => 13,
                'name' => 'Radio Nepal News',
                'stream_url' => 'https://stream.zeno.fm/3r0xa792kwzuv',
                'stream_type' => 'mp3',
                'city' => 'Kathmandu',
                'frequency' => '103.0 FM',
                'logo_path' => '',
                'status' => 'active',
                'featured' => 1,
                'sort_order' => 13
            ]
        ];
    }
    
    echo json_encode([
        'ok' => true,
        'stations' => $stations,
        'count' => count($stations)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
