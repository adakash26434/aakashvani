<?php
/**
 * Canonical HTTP fetch helper.
 * Replaces six near-identical fetchUrl() copies across api/*.php.
 * New code MUST use nh_fetchUrl(). SSL verify ON by default.
 */

if (!function_exists('nh_fetchUrl')) {
function nh_fetchUrl(
    string $url,
    array  $headers   = [],
    int    $timeout   = 10,
    bool   $verifySsl = true,
    ?string $method   = 'GET',
    ?string $body     = null
): ?string {
    $ch = curl_init($url);
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_CONNECTTIMEOUT => min(5, $timeout),
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; AakashvaniBot/1.0; +https://www.tankaadhikari.com.np)',
        CURLOPT_SSL_VERIFYPEER => $verifySsl,
        CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0,
        CURLOPT_HTTPHEADER     => array_merge([
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,application/json;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.7,ne;q=0.3',
        ], $headers),
        CURLOPT_ENCODING       => '',
    ];
    if ($method && strtoupper($method) !== 'GET') {
        $opts[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
        if ($body !== null) $opts[CURLOPT_POSTFIELDS] = $body;
    }
    curl_setopt_array($ch, $opts);
    $result = curl_exec($ch);
    $code   = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err    = curl_error($ch);
    curl_close($ch);
    if ($err) { error_log("[nh_fetchUrl] $url → $err"); return null; }
    return ($result !== false && $code >= 200 && $code < 300) ? $result : null;
}
}

if (!function_exists('nh_fetchJson')) {
function nh_fetchJson(string $url, array $headers = [], int $timeout = 10): ?array {
    $raw = nh_fetchUrl($url, array_merge(['Accept: application/json'], $headers), $timeout);
    if (!$raw) return null;
    $j = json_decode($raw, true);
    return is_array($j) ? $j : null;
}
}