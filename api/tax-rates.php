<?php
/**
 * Nepal Tax Rates API
 * Returns current Nepal tax rates from DB (auto-seeded with FY 2081/82 rates).
 * Admin can update after each annual budget announcement.
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: max-age=3600');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

try {
    $info = getTaxInfo();
    if (empty($info)) {
        echo json_encode(['success' => false, 'error' => 'Tax data not available'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    echo json_encode([
        'success'            => true,
        'fiscal_year'        => $info['fiscal_year'],
        'vat_rate'           => (float)$info['vat_rate'],
        'income_tax_single'  => $info['income_tax_single'],
        'income_tax_married' => $info['income_tax_married'],
        'corporate_tax'      => $info['corporate_tax'],
        'tds_rates'          => $info['tds_rates'],
        'vehicle_tax_2w'     => $info['vehicle_tax_2w'],
        'vehicle_tax_4w'     => $info['vehicle_tax_4w'],
        'source'             => $info['source'],
        'notes'              => $info['notes'],
        'updated_at'         => $info['updated_at'],
        'updated_by'         => $info['updated_by'],
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (\Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
