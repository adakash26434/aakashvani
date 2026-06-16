<?php
/**
 * आकाशवाणी - Error Handler
 * Centralized error handling for the application
 */

// Set error reporting based on environment
if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 0);
}

// Set custom error handler
set_error_handler(function($severity, $message, $file, $line) {
    // Don't report if error reporting is disabled
    if (!(error_reporting() & $severity)) {
        return false;
    }
    
    // Log the error
    log_error($message, [
        'severity' => $severity,
        'file' => $file,
        'line' => $line,
        'uri' => $_SERVER['REQUEST_URI'] ?? '',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);
    
    // In production, don't display errors
    if (!defined('DEBUG_MODE') || DEBUG_MODE !== true) {
        return true;
    }
    
    return false;
});

// Set custom exception handler
set_exception_handler(function($exception) {
    log_error('Uncaught Exception: ' . $exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString(),
        'uri' => $_SERVER['REQUEST_URI'] ?? '',
    ]);
    
    // Display error page in production
    if (!defined('DEBUG_MODE') || DEBUG_MODE !== true) {
        http_response_code(500);
        if (defined('IS_API_REQUEST') && IS_API_REQUEST) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error' => 'An unexpected error occurred. Please try again later.',
                'code' => 'SERVER_ERROR',
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo '<!DOCTYPE html><html><head><title>Error</title></head><body>';
            echo '<h1>Something went wrong</h1>';
            echo '<p>Please try again later.</p>';
            echo '</body></html>';
        }
        exit;
    }
    
    // In debug mode, show detailed error
    echo '<h1>Error</h1>';
    echo '<p><strong>Message:</strong> ' . htmlspecialchars($exception->getMessage()) . '</p>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($exception->getFile()) . '</p>';
    echo '<p><strong>Line:</strong> ' . $exception->getLine() . '</p>';
    echo '<pre>' . htmlspecialchars($exception->getTraceAsString()) . '</pre>';
    exit;
});

// Set shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        log_error('Fatal Error: ' . $error['message'], [
            'file' => $error['file'],
            'line' => $error['line'],
            'type' => $error['type'],
        ]);
        
        if (!defined('DEBUG_MODE') || DEBUG_MODE !== true) {
            http_response_code(500);
            if (defined('IS_API_REQUEST') && IS_API_REQUEST) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'error' => 'A fatal error occurred. Please try again later.',
                    'code' => 'FATAL_ERROR',
                ], JSON_UNESCAPED_UNICODE);
            }
        }
    }
});

/**
 * Log error to file
 * 
 * @param string $message Error message
 * @param array $context Additional context
 */
function log_error(string $message, array $context = []): void {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/error-' . date('Y-m-d') . '.log';
    
    $logEntry = [
        'time' => date('Y-m-d H:i:s'),
        'message' => $message,
        'context' => $context,
    ];
    
    @file_put_contents($logFile, json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
}

/**
 * Log info message
 * 
 * @param string $message Log message
 * @param array $context Additional context
 */
function log_info(string $message, array $context = []): void {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/info-' . date('Y-m-d') . '.log';
    
    $logEntry = [
        'time' => date('Y-m-d H:i:s'),
        'message' => $message,
        'context' => $context,
    ];
    
    @file_put_contents($logFile, json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
}

/**
 * Log debug message (only in debug mode)
 * 
 * @param string $message Log message
 * @param array $context Additional context
 */
function log_debug(string $message, array $context = []): void {
    if (!defined('DEBUG_MODE') || DEBUG_MODE !== true) {
        return;
    }
    
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/debug-' . date('Y-m-d') . '.log';
    
    $logEntry = [
        'time' => date('Y-m-d H:i:s'),
        'message' => $message,
        'context' => $context,
    ];
    
    @file_put_contents($logFile, json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
}
