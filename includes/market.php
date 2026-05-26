<?php
/**
 * includes/market.php — single source of truth for market data
 * Wraps api/market-data.php in library mode and normalizes the shape
 * expected by index.php / utilities.php.
 *
 * Shape returned by getMarket():
 *   [
 *     'gold'   => ['fine'=>?, 'tejabi'=>?, 'silver'=>?, 'available'=>bool,
 *                  'source'=>?, 'source_url'=>?, 'updatedAt'=>?],
 *     'forex'  => ['USD'=>float, 'EUR'=>float, 'INR'=>float, 'rates'=>[...]],
 *     'nepse'  => ['index'=>float, 'change'=>float, 'percent'=>float, ...],
 *     'fuel'   => ['petrol'=>?, 'diesel'=>?, 'kerosene'=>?, 'lpg'=>?,
 *                  'available'=>bool, 'source'=>?, 'source_url'=>?],
 *   ]
 */

if (!defined('MARKET_LIB_ONLY')) define('MARKET_LIB_ONLY', true);
require_once __DIR__ . '/../api/market-data.php';

if (!function_exists('getMarket')) {
function getMarket(bool $refresh = false): array {
    // refresh flag is advisory — the underlying cache TTLs still apply.
    $gold   = function_exists('getGoldData')   ? getGoldData()   : [];
    $forex  = function_exists('getForexData')  ? getForexData()  : [];
    $nepse  = function_exists('getNepseData')  ? getNepseData()  : [];
    $petrol = function_exists('getPetrolData') ? getPetrolData() : [];

    // ── GOLD: map FENEGOSIDA keys → fine/tejabi/silver ──
    $goldOut = [
        'fine'       => $gold['hallmarkPerTola'] ?? null,
        'tejabi'     => $gold['tajbiPerTola']    ?? null,
        'silver'     => $gold['silverPerTola']   ?? null,
        'available'  => !empty($gold['available']),
        'is_live'    => !empty($gold['is_live']),
        'source'     => $gold['source']     ?? 'FENEGOSIDA',
        'source_url' => $gold['source_url'] ?? 'https://www.fenegosida.org/',
        'updatedAt'  => $gold['updatedAt']  ?? null,
        'note'       => $gold['note']       ?? null,
    ];

    // ── FOREX: flat code → buy rate map + raw rates list ──
    $forexOut = [
        'rates'      => $forex['rates'] ?? [],
        'available'  => !empty($forex['rates']) || !isset($forex['available']) ? !empty($forex['rates']) : false,
        'source'     => $forex['source']     ?? 'Nepal Rastra Bank',
        'source_url' => $forex['source_url'] ?? 'https://www.nrb.org.np/forex/',
        'updatedAt'  => $forex['updatedAt']  ?? null,
    ];
    foreach (($forex['rates'] ?? []) as $r) {
        $code = $r['code'] ?? null;
        if (!$code) continue;
        $unit = max(1, (int)($r['unit'] ?? 1));
        $buy  = (float)($r['buy'] ?? 0);
        // per-unit rate so callers can multiply directly
        $forexOut[$code] = $unit > 1 ? round($buy / $unit, 4) : $buy;
    }

    // ── NEPSE: normalize index/change/percent keys ──
    $nepseOut = [
        'index'        => (float)($nepse['index'] ?? 0),
        'change'       => isset($nepse['change']) ? (float)$nepse['change'] : null,
        'percent'      => isset($nepse['changePercent']) ? (float)$nepse['changePercent']
                          : (isset($nepse['percent']) ? (float)$nepse['percent'] : null),
        'turnover'     => (float)($nepse['turnover'] ?? 0),
        'marketStatus' => $nepse['marketStatus'] ?? null,
        'source'       => $nepse['source']    ?? 'NEPSE',
        'updatedAt'    => $nepse['updatedAt'] ?? null,
    ];

    // ── FUEL: rename lpg_cylinder → lpg for legacy callers ──
    $fuelOut = [
        'petrol'     => $petrol['petrol']       ?? null,
        'diesel'     => $petrol['diesel']       ?? null,
        'kerosene'   => $petrol['kerosene']     ?? null,
        'lpg'        => $petrol['lpg_cylinder'] ?? null,
        'available'  => !empty($petrol['available']),
        'is_live'    => !empty($petrol['is_live']),
        'source'     => $petrol['source']     ?? 'Nepal Oil Corporation',
        'source_url' => $petrol['source_url'] ?? 'https://noc.org.np/priceupdate',
        'updatedAt'  => $petrol['updatedAt']  ?? null,
        'note'       => $petrol['note']       ?? null,
    ];

    return [
        'gold'      => $goldOut,
        'forex'     => $forexOut,
        'nepse'     => $nepseOut,
        'fuel'      => $fuelOut,
        'updatedAt' => date('Y-m-d H:i'),
    ];
}
}