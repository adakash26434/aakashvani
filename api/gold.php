<?php
/**
 * api/gold.php — Gold & Silver Prices
 * Serves data from gold.json (updated by market-data.php scrape)
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=300');
header('Access-Control-Allow-Origin: *');

$jsonFile = __DIR__ . '/gold.json';

if (file_exists($jsonFile) && is_readable($jsonFile)) {
    $data = json_decode(file_get_contents($jsonFile), true);
    if ($data) {
        echo json_encode([
            'ok' => true,
            'source' => 'Aakashvani',
            'hallmark_per_tola' => $data['hallmarkPerTola'] ?? 0,
            'hallmark_per_gram' => $data['hallmarkPerGram'] ?? 0,
            'tajbi_per_tola' => $data['tajbiPerTola'] ?? 0,
            'tajbi_per_gram' => $data['tajbiPerGram'] ?? 0,
            'silver_per_tola' => $data['silverPerTola'] ?? 0,
            'silver_per_gram' => $data['silverPerGram'] ?? 0,
            'usd_npr' => $data['usdNpr'] ?? 0,
            'updated_at' => $data['updatedAt'] ?? null,
            'currency' => $data['currency'] ?? 'NPR',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

echo json_encode(['ok' => false, 'error' => 'Gold price data unavailable'], JSON_UNESCAPED_UNICODE);
