<?php
/**
 * Error Logger & Monitoring Utility
 * Centralized error logging for APIs and application
 */

if (!defined('LOG_DIR')) {
    define('LOG_DIR', __DIR__ . '/../data/logs');
}

/**
 * Log an error or event
 */
function logError(string $message, string $level = 'ERROR', string $context = ''): void {
    $dir = LOG_DIR;
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    
    $date = date('Y-m-d');
    $datetime = date('Y-m-d H:i:s');
    $file = $dir . '/error-' . $date . '.log';
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
    $uri = $_SERVER['REQUEST_URI'] ?? 'unknown';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    
    $line = "[$datetime] [$level] [IP:$ip] [$method $uri]";
    if ($context) {
        $line .= " [$context]";
    }
    $line .= " $message" . PHP_EOL;
    
    @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
}

/**
 * Log API errors specifically
 */
function logApiError(string $api, string $error, array $extra = []): void {
    $context = "API:$api";
    if (!empty($extra)) {
        $context .= ' ' . json_encode($extra);
    }
    logError($error, 'API_ERROR', $context);
}

/**
 * Log external fetch failures
 */
function logFetchError(string $source, string $url, string $error): void {
    logError("Failed to fetch from $source: $error", 'FETCH_ERROR', $url);
}

/**
 * Log database errors
 */
function logDbError(string $operation, string $error): void {
    logError("DB Error during $operation: $error", 'DB_ERROR', $operation);
}

/**
 * Get recent errors from log
 */
function getRecentErrors(int $lines = 50): array {
    $dir = LOG_DIR;
    $date = date('Y-m-d');
    $file = $dir . '/error-' . $date . '.log';
    
    if (!file_exists($file)) {
        return [];
    }
    
    $content = file_get_contents($file);
    $allLines = explode("\n", trim($content));
    $allLines = array_filter($allLines);
    
    return array_slice(array_reverse($allLines), 0, $lines);
}

/**
 * Get error statistics
 */
function getErrorStats(int $days = 7): array {
    $dir = LOG_DIR;
    $stats = [];
    
    for ($i = 0; $i < $days; $i++) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $file = $dir . '/error-' . $date . '.log';
        
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $lines = explode("\n", trim($content));
            $lines = array_filter($lines);
            
            $errors = 0;
            $warnings = 0;
            $apiErrors = 0;
            
            foreach ($lines as $line) {
                if (strpos($line, '[ERROR]') !== false) $errors++;
                if (strpos($line, '[WARNING]') !== false) $warnings++;
                if (strpos($line, '[API_ERROR]') !== false) $apiErrors++;
            }
            
            $stats[$date] = [
                'total' => count($lines),
                'errors' => $errors,
                'warnings' => $warnings,
                'api_errors' => $apiErrors
            ];
        } else {
            $stats[$date] = ['total' => 0, 'errors' => 0, 'warnings' => 0, 'api_errors' => 0];
        }
    }
    
    return $stats;
}

/**
 * Global exception handler
 */
function setupGlobalErrorHandler(): void {
    set_error_handler(function($severity, $message, $file, $line) {
        $level = 'ERROR';
        switch ($severity) {
            case E_WARNING: $level = 'WARNING'; break;
            case E_NOTICE: $level = 'NOTICE'; break;
            case E_DEPRECATED: $level = 'DEPRECATED'; break;
        }
        logError("$message in $file:$line", $level, 'PHP');
        return false; // Let PHP handle it too
    });
    
    set_exception_handler(function($e) {
        logError(
            $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(),
            'EXCEPTION',
            get_class($e)
        );
    });
}
