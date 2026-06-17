<?php
/**
 * आकाशवाणी — Premium API Client
 * Handles all external API calls with caching and error handling
 */

namespace Aakashvani\Core;

class ApiClient
{
    private static ?ApiClient $instance = null;
    private string $cacheDir;
    private int $defaultTtl = 300;
    private int $timeout = 10;
    private array $headers = [];

    private function __construct()
    {
        $this->cacheDir = __DIR__ . '/../data/cache/';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        
        $this->headers = [
            'User-Agent: Aakashvani/2.0 (Nepal Information Platform)',
            'Accept: application/json',
        ];
    }

    public static function getInstance(): ApiClient
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Fetch URL with error handling
     */
    public function fetch(string $url, array $options = []): ?string
    {
        $timeout = $options['timeout'] ?? $this->timeout;
        $headers = array_merge($this->headers, $options['headers'] ?? []);
        
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $headers),
                'timeout' => $timeout,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            $this->logError("Failed to fetch: $url");
            return null;
        }
        
        return $response;
    }

    /**
     * Fetch with JSON decoding
     */
    public function fetchJson(string $url, array $options = []): ?array
    {
        $response = $this->fetch($url, $options);
        if ($response === null) {
            return null;
        }
        
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logError("JSON decode error for: $url - " . json_last_error_msg());
            return null;
        }
        
        return $data;
    }

    /**
     * Read from cache
     */
    public function getCache(string $key, int $ttl = null): ?array
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $file = $this->cacheDir . $this->sanitizeKey($key) . '.json';
        
        if (!file_exists($file)) {
            return null;
        }
        
        $mtime = filemtime($file);
        if ((time() - $mtime) > $ttl) {
            return null;
        }
        
        $data = json_decode(file_get_contents($file), true);
        return is_array($data) ? $data : null;
    }

    /**
     * Write to cache
     */
    public function setCache(string $key, array $data): void
    {
        $file = $this->cacheDir . $this->sanitizeKey($key) . '.json';
        file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Clear cache for a key
     */
    public function clearCache(string $key): void
    {
        $file = $this->cacheDir . $this->sanitizeKey($key) . '.json';
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Clear all cache
     */
    public function clearAllCache(): void
    {
        $files = glob($this->cacheDir . '*.json');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Get market data (NEPSE, Gold, Forex, Fuel)
     */
    public function getMarketData(string $type = 'all'): ?array
    {
        $cacheKey = "market_{$type}";
        
        // Try cache first
        $cached = $this->getCache($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        // Fetch from internal API
        $url = "/api/market-data.php?type={$type}";
        $data = $this->fetchJson($this->getBaseUrl() . $url);
        
        if ($data !== null) {
            $this->setCache($cacheKey, $data);
        }
        
        return $data;
    }

    /**
     * Get earthquake data from USGS
     */
    public function getEarthquakeData(int $minutes = 1440, float $minMagnitude = 2.5): ?array
    {
        $cacheKey = "earthquake_{$minutes}_{$minMagnitude}";
        
        $cached = $this->getCache($cacheKey, 60); // 1 minute cache
        if ($cached !== null) {
            return $cached;
        }
        
        $url = "https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/{$minMagnitude}_{$minutes}.geojson";
        
        $data = $this->fetchJson($url);
        if ($data === null) {
            return null;
        }
        
        // Transform USGS format to our format
        $earthquakes = [];
        if (isset($data['features'])) {
            foreach ($data['features'] as $feature) {
                $props = $feature['properties'] ?? [];
                $geo = $feature['geometry'] ?? [];
                
                $earthquakes[] = [
                    'id' => $feature['id'] ?? '',
                    'magnitude' => $props['mag'] ?? 0,
                    'place' => $props['place'] ?? 'Unknown',
                    'time' => $props['time'] ?? 0,
                    'depth' => $geo['coordinates'][2] ?? 0,
                    'latitude' => $geo['coordinates'][1] ?? 0,
                    'longitude' => $geo['coordinates'][0] ?? 0,
                    'url' => $props['url'] ?? '',
                    'tsunami' => $props['tsunami'] ?? 0,
                ];
            }
        }
        
        $result = ['earthquakes' => $earthquakes];
        $this->setCache($cacheKey, $result);
        
        return $result;
    }

    /**
     * Get weather data
     */
    public function getWeatherData(string $city = 'Kathmandu'): ?array
    {
        $cacheKey = "weather_{$city}";
        
        $cached = $this->getCache($cacheKey, 900); // 15 minutes
        if ($cached !== null) {
            return $cached;
        }
        
        // Using Open-Meteo API (free, no API key needed)
        $lat = $city === 'Kathmandu' ? 27.7172 : 28.2096;
        $lon = $city === 'Kathmandu' ? 85.3141 : 83.9856;
        
        $url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}&current=temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,wind_speed_10m&timezone=Asia/Kathmandu";
        
        $data = $this->fetchJson($url);
        if ($data === null) {
            return null;
        }
        
        $result = $this->transformWeatherData($data, $city);
        $this->setCache($cacheKey, $result);
        
        return $result;
    }

    /**
     * Transform Open-Meteo weather data
     */
    private function transformWeatherData(array $data, string $city): array
    {
        $current = $data['current'] ?? [];
        
        $weatherCodes = [
            0 => ['description' => 'Clear sky', 'icon' => '☀️'],
            1 => ['description' => 'Mainly clear', 'icon' => '🌤️'],
            2 => ['description' => 'Partly cloudy', 'icon' => '⛅'],
            3 => ['description' => 'Overcast', 'icon' => '☁️'],
            45 => ['description' => 'Foggy', 'icon' => '🌫️'],
            48 => ['description' => 'Depositing rime fog', 'icon' => '🌫️'],
            51 => ['description' => 'Light drizzle', 'icon' => '🌧️'],
            53 => ['description' => 'Moderate drizzle', 'icon' => '🌧️'],
            55 => ['description' => 'Dense drizzle', 'icon' => '🌧️'],
            61 => ['description' => 'Slight rain', 'icon' => '🌧️'],
            63 => ['description' => 'Moderate rain', 'icon' => '🌧️'],
            65 => ['description' => 'Heavy rain', 'icon' => '🌧️'],
            71 => ['description' => 'Slight snow', 'icon' => '🌨️'],
            73 => ['description' => 'Moderate snow', 'icon' => '🌨️'],
            75 => ['description' => 'Heavy snow', 'icon' => '🌨️'],
            77 => ['description' => 'Snow grains', 'icon' => '🌨️'],
            80 => ['description' => 'Slight rain showers', 'icon' => '🌦️'],
            81 => ['description' => 'Moderate rain showers', 'icon' => '🌦️'],
            82 => ['description' => 'Violent rain showers', 'icon' => '🌦️'],
            85 => ['description' => 'Slight snow showers', 'icon' => '🌨️'],
            86 => ['description' => 'Heavy snow showers', 'icon' => '🌨️'],
            95 => ['description' => 'Thunderstorm', 'icon' => '⛈️'],
            96 => ['description' => 'Thunderstorm with slight hail', 'icon' => '⛈️'],
            99 => ['description' => 'Thunderstorm with heavy hail', 'icon' => '⛈️'],
        ];
        
        $code = $current['weather_code'] ?? 0;
        $weather = $weatherCodes[$code] ?? ['description' => 'Unknown', 'icon' => '❓'];
        
        return [
            'city' => $city,
            'current' => [
                'temp' => round($current['temperature_2m'] ?? 0),
                'feels_like' => round($current['apparent_temperature'] ?? 0),
                'humidity' => $current['relative_humidity_2m'] ?? 0,
                'wind_speed' => round($current['wind_speed_10m'] ?? 0),
                'description' => $weather['description'],
                'icon' => $weather['icon'],
                'code' => $code,
            ],
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get news from internal API
     */
    public function getNews(int $limit = 10, string $category = null): ?array
    {
        $cacheKey = "news_{$limit}_{$category}";
        $url = "/api/news-unified.php?limit={$limit}";
        if ($category) {
            $url .= "&category={$category}";
        }
        
        return $this->fetchJson($this->getBaseUrl() . $url);
    }

    /**
     * Check if market is open
     */
    public function isMarketOpen(): bool
    {
        $now = new \DateTime('now', new \DateTimeZone('Asia/Kathmandu'));
        $day = (int)$now->format('N'); // 1 = Monday, 7 = Sunday
        
        // Nepal stock market closed on Saturday (6) and Sunday (7)
        if ($day >= 6) {
            return false;
        }
        
        $hour = (int)$now->format('G');
        $minute = (int)$now->format('i');
        $currentMinutes = $hour * 60 + $minute;
        
        // Market hours: 11:00 AM - 3:00 PM
        return ($currentMinutes >= 660 && $currentMinutes < 900);
    }

    /**
     * Get market status
     */
    public function getMarketStatus(): string
    {
        if ($this->isMarketOpen()) {
            return 'open';
        }
        
        $now = new \DateTime('now', new \DateTimeZone('Asia/Kathmandu'));
        $day = (int)$now->format('N');
        $hour = (int)$now->format('G');
        $minute = (int)$now->format('i');
        $currentMinutes = $hour * 60 + $minute;
        
        if ($day < 6 && $currentMinutes >= 600 && $currentMinutes < 660) {
            return 'pre-open';
        }
        
        if ($day < 6 && $currentMinutes >= 870 && $currentMinutes < 900) {
            return 'closing';
        }
        
        return 'closed';
    }

    private function getBaseUrl(): string
    {
        return defined('SITE_URL') ? SITE_URL : 'https://tankaadhikari.com.np';
    }

    private function sanitizeKey(string $key): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '_', $key);
    }

    private function logError(string $message): void
    {
        $logFile = $this->cacheDir . 'errors.log';
        $entry = date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL;
        @file_put_contents($logFile, $entry, FILE_APPEND);
    }
}
