<?php
/**
 * API: Radio Podcasts - Fetch from external source or database
 * Falls back to sample data if no data available
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/functions.entertainment.php';

try {
    // Try to get from database first
    $podcasts = getRadioPodcasts(12);
    
    if (empty($podcasts)) {
        // Fallback to sample data
        $podcasts = [
            [
                'id' => 1,
                'title' => 'नेपालको आर्थिक अवस्था',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
                'cover_image' => '',
                'station_id' => 1,
                'station_name' => 'Radio Nepal',
                'published_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'status' => 'published'
            ],
            [
                'id' => 2,
                'title' => 'शिक्षा प्रणाली सुधार',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3',
                'cover_image' => '',
                'station_id' => 2,
                'station_name' => 'Kantipur FM',
                'published_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'status' => 'published'
            ],
            [
                'id' => 3,
                'title' => 'स्वास्थ्य जागरण कार्यक्रम',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3',
                'cover_image' => '',
                'station_id' => 3,
                'station_name' => 'Image FM',
                'published_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'status' => 'published'
            ],
            [
                'id' => 4,
                'title' => 'कृषि तथा विकास',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-4.mp3',
                'cover_image' => '',
                'station_id' => 4,
                'station_name' => 'Hits FM',
                'published_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
                'status' => 'published'
            ],
            [
                'id' => 5,
                'title' => 'पर्यटन प्रवर्द्धन',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-5.mp3',
                'cover_image' => '',
                'station_id' => 5,
                'station_name' => 'Kalika FM',
                'published_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'status' => 'published'
            ]
        ];
    }
    
    echo json_encode([
        'ok' => true,
        'podcasts' => $podcasts,
        'count' => count($podcasts)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
