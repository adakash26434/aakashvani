<?php
/**
 * आकाशवाणी - Unified API Response Helper
 * Standardized API response format for all endpoints
 */

/**
 * Send JSON response with standardized format
 * 
 * @param mixed $data Response data
 * @param string $message Optional message
 * @param int $code HTTP status code
 * @param array $meta Additional metadata
 */
function api_response($data = null, string $message = '', int $code = 200, array $meta = []): void {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($code);
    
    $response = [
        'success' => $code >= 200 && $code < 300,
        'data' => $data,
        'message' => $message,
        'meta' => array_merge([
            'timestamp' => time(),
            'server' => 'aakashvani',
            'version' => '1.0',
        ], $meta),
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Send success response
 */
function api_success($data = null, string $message = 'Success', array $meta = []): void {
    api_response($data, $message, 200, $meta);
}

/**
 * Send error response
 */
function api_error(string $message, int $code = 400, $data = null): void {
    api_response($data, $message, $code);
}

/**
 * Send not found response
 */
function api_not_found(string $message = 'Resource not found'): void {
    api_error($message, 404);
}

/**
 * Send unauthorized response
 */
function api_unauthorized(string $message = 'Unauthorized'): void {
    api_error($message, 401);
}

/**
 * Send server error response
 */
function api_server_error(string $message = 'Internal server error', $data = null): void {
    api_error($message, 500, $data);
}

/**
 * Validate required parameters
 * 
 * @param array $params Required parameters
 * @param array $data Data to validate against
 * @return array Missing parameters
 */
function api_validate_params(array $params, array $data): array {
    $missing = [];
    foreach ($params as $param) {
        if (!isset($data[$param]) || $data[$param] === '') {
            $missing[] = $param;
        }
    }
    return $missing;
}

/**
 * Check and send error if validation fails
 */
function api_check_params(array $params, array $data): bool {
    $missing = api_validate_params($params, $data);
    if (!empty($missing)) {
        api_error('Missing required parameters: ' . implode(', ', $missing), 400);
        return false;
    }
    return true;
}

/**
 * Log API errors
 */
function api_log_error(string $endpoint, string $message, $context = null): void {
    $log = [
        'time' => date('Y-m-d H:i:s'),
        'endpoint' => $endpoint,
        'message' => $message,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    ];
    
    if ($context !== null) {
        $log['context'] = $context;
    }
    
    // Log to file
    $logFile = __DIR__ . '/../logs/api-errors.log';
    if (!is_dir(dirname($logFile))) {
        @mkdir(dirname($logFile), 0755, true);
    }
    @file_put_contents($logFile, json_encode($log, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
}

/**
 * Cache API response
 */
function api_cache_get(string $key): ?string {
    $cacheFile = __DIR__ . '/../cache/api/' . md5($key) . '.json';
    if (file_exists($cacheFile) && filemtime($cacheFile) > time() - 300) { // 5 min cache
        return file_get_contents($cacheFile);
    }
    return null;
}

function api_cache_set(string $key, string $data, int $ttl = 300): void {
    $cacheDir = __DIR__ . '/../cache/api';
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0755, true);
    }
    $cacheFile = $cacheDir . '/' . md5($key) . '.json';
    @file_put_contents($cacheFile, $data);
}

function api_cache_clear(string $pattern = '*'): void {
    $cacheDir = __DIR__ . '/../cache/api';
    if (is_dir($cacheDir)) {
        foreach (glob($cacheDir . '/' . $pattern . '.json') as $file) {
            @unlink($file);
        }
    }
}
