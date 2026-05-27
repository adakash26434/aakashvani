<?php
/**
 * आकाशवाणी — Market Data Page
 * NEPSE, Gold, Forex, Petrol with Charts
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$lang = siteLang();
$t = fn($ne, $en) => $lang === 'ne' ? $ne : $en;

// Get all market data
$gold = getGoldData();
$nepse = getNepseData();
$forex = getForexData();
$petrol = getPetrolData();

$pageTitle = $t('बजार — आकाशवाणी', 'Market — Aakashvani');
$pageDesc = $t('आजको NEPSE, सुनचाँदी भाउ, विनिमय दर र इन्धन मूल्य।', 'Today\'s NEPSE, Gold & Silver prices, Forex rates and Fuel prices.');

include __DIR__ . '/header.php';
?>

<!-- Page Header -->
<section class="mt-3">
  <div class="app-card p-4 bg-gradient-to-br from-sky-900 to-sky-700 text-white">
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
        <i data-lucide="bar-chart-2" class="w-6 h-6"></i>
      </div>
      <div>
        <h1 class="text-xl font-bold ne"><?= $t('आजको बजार', 'Today\'s Market') ?></h1>
        <p class="text-sm opacity-80"><?= date('F j, Y') ?> · <?= getBsDate()['short'] ?></p>
      </div>
      <div class="ml-auto flex items-center gap-1 text-xs bg-white/20 px-2 py-1 rounded-full">
        <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
        Live
      </div>
    </div>
  </div>
</section>

<!-- Market Tabs -->
<div class="flex gap-2 mt-4 overflow-x-auto no-sb px-1">
  <a href="#nepse" class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-semibold bg-sky-100 text-sky-700 border border-sky-200">NEPSE</a>
  <a href="#gold" class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200"><?= $t('सुनचाँदी', 'Gold') ?></a>
  <a href="#forex" class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200"><?= $t('विनिमय', 'Forex') ?></a>
  <a href="#fuel" class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200"><?= $t('इन्धन', 'Fuel') ?></a>
</div>

<!-- NEPSE Section -->
<section id="nepse" class="mt-4 fade-up">
  <div class="sec-title">
    <i data-lucide="trending-up" class="w-4 h-4 text-blue-600"></i>
    <span class="font-bold">NEPSE Index</span>
    <?php if (($nepse['market_status']['status'] ?? '') === 'open'): ?>
      <span class="ml-auto text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-semibold"><?= $t('खुला', 'Open') ?></span>
    <?php else: ?>
      <span class="ml-auto text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-semibold"><?= $t($nepse['market_status']['text'] ?? 'बन्द', $nepse['market_status']['text_en'] ?? 'Closed') ?></span>
    <?php endif; ?>
  </div>
  
  <?php if ($nepse['available'] ?? false): ?>
  <div class="app-card p-4">
    <div class="flex items-end justify-between mb-4">
      <div>
        <div class="text-sm text-slate-500"><?= $t('सूचकांक', 'Index') ?></div>
        <div class="text-4xl font-extrabold text-slate-900"><?= number_format($nepse['index'], 2) ?></div>
      </div>
      <div class="text-right">
        <?php $isUp = ($nepse['change'] ?? 0) >= 0; ?>
        <div class="text-2xl font-bold <?= $isUp ? 'text-emerald-600' : 'text-red-600' ?>">
          <?= $isUp ? '▲' : '▼' ?> <?= number_format(abs($nepse['change']), 2) ?>
        </div>
        <div class="text-sm <?= $isUp ? 'text-emerald-600' : 'text-red-600' ?>">
          (<?= $isUp ? '+' : '' ?><?= number_format($nepse['change_percent'], 2) ?>%)
        </div>
      </div>
    </div>
    
    <!-- Mini Chart Placeholder -->
    <div class="h-24 bg-gradient-to-r from-sky-50 to-sky-100 rounded-xl flex items-center justify-center mb-4">
      <div class="text-center text-slate-400">
        <i data-lucide="line-chart" class="w-8 h-8 mx-auto mb-1"></i>
        <span class="text-xs"><?= $t('चार्ट', 'Chart') ?></span>
      </div>
    </div>
    
    <!-- Stats Grid -->
    <div class="grid grid-cols-3 gap-3 text-center">
      <div class="p-2 bg-slate-50 rounded-xl">
        <div class="text-xs text-slate-500"><?= $t('कारोबार', 'Turnover') ?></div>
        <div class="text-sm font-bold text-slate-900">रु <?= number_format($nepse['turnover'] / 10000000, 2) ?> Cr</div>
      </div>
      <div class="p-2 bg-emerald-50 rounded-xl">
        <div class="text-xs text-emerald-600"><?= $t('बढेको', 'Gainers') ?></div>
        <div class="text-sm font-bold text-emerald-700"><?= $nepse['gainers'] ?? 0 ?></div>
      </div>
      <div class="p-2 bg-red-50 rounded-xl">
        <div class="text-xs text-red-600"><?= $t('घटेको', 'Losers') ?></div>
        <div class="text-sm font-bold text-red-700"><?= $nepse['losers'] ?? 0 ?></div>
      </div>
    </div>
    
    <!-- Technical Indicators -->
    <?php if (!empty($nepse['indicators'])): ?>
    <div class="mt-4 pt-4 border-t border-slate-100">
      <div class="text-xs font-semibold text-slate-700 mb-2"><?= $t('तकनीकी संकेत', 'Technical Indicators') ?></div>
      <div class="grid grid-cols-2 gap-2 text-xs">
        <div class="flex justify-between p-2 bg-slate-50 rounded-lg">
          <span class="text-slate-500"><?= $t('ट्रेन्ड', 'Trend') ?></span>
          <span class="font-semibold <?= ($nepse['indicators']['trend'] ?? '') === 'uptrend' ? 'text-emerald-600' : (($nepse['indicators']['trend'] ?? '') === 'downtrend' ? 'text-red-600' : 'text-slate-700') ?>">
            <?= $t(ucfirst($nepse['indicators']['trend'] ?? 'sideways'), ucfirst($nepse['indicators']['trend'] ?? 'sideways')) ?>
          </span>
        </div>
        <div class="flex justify-between p-2 bg-slate-50 rounded-lg">
          <span class="text-slate-500"><?= $t('RSI', 'RSI') ?></span>
          <span class="font-semibold <?= ($nepse['indicators']['rsi_signal'] ?? '') === 'overbought' ? 'text-red-600' : (($nepse['indicators']['rsi_signal'] ?? '') === 'oversold' ? 'text-emerald-600' : 'text-slate-700') ?>">
            <?= number_format($nepse['indicators']['rsi'] ?? 50, 2) ?> (<?= $t($nepse['indicators']['rsi_signal'] ?? 'neutral', $nepse['indicators']['rsi_signal'] ?? 'neutral') ?>)
          </span>
        </div>
        <div class="flex justify-between p-2 bg-slate-50 rounded-lg">
          <span class="text-slate-500"><?= $t('सपोर्ट', 'Support') ?></span>
          <span class="font-semibold text-slate-700"><?= number_format($nepse['indicators']['support'] ?? 0, 2) ?></span>
        </div>
        <div class="flex justify-between p-2 bg-slate-50 rounded-lg">
          <span class="text-slate-500"><?= $t('रेजिस्टेन्स', 'Resistance') ?></span>
          <span class="font-semibold text-slate-700"><?= number_format($nepse['indicators']['resistance'] ?? 0, 2) ?></span>
        </div>
      </div>
    </div>
    
    <!-- Forecast -->
    <?php if (!empty($nepse['forecast'])): ?>
    <div class="mt-4 pt-4 border-t border-slate-100">
      <div class="text-xs font-semibold text-slate-700 mb-2"><?= $t('भविष्यवाणी', 'Forecast') ?></div>
      <div class="p-3 bg-gradient-to-r from-sky-50 to-blue-50 rounded-xl">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs text-slate-500"><?= $t('भोलिको अनुमान', 'Next Day Estimate') ?></span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-slate-200 text-slate-600"><?= $t('विश्वास', 'Confidence') ?>: <?= $t($nepse['forecast']['next_day']['confidence'] ?? 'low', $nepse['forecast']['next_day']['confidence'] ?? 'low') ?></span>
        </div>
        <div class="flex items-center justify-between">
          <div>
            <div class="text-lg font-bold text-slate-900"><?= number_format($nepse['forecast']['next_day']['index'] ?? $nepse['index'], 2) ?></div>
            <div class="text-xs <?= ($nepse['forecast']['next_day']['direction'] ?? '') === 'up' ? 'text-emerald-600' : (($nepse['forecast']['next_day']['direction'] ?? '') === 'down' ? 'text-red-600' : 'text-slate-500') ?>">
              <?= ($nepse['forecast']['next_day']['direction'] ?? '') === 'up' ? '▲' : (($nepse['forecast']['next_day']['direction'] ?? '') === 'down' ? '▼' : '→') ?> <?= number_format($nepse['forecast']['next_day']['change_percent'] ?? 0, 2) ?>%
            </div>
          </div>
          <div class="text-right">
            <div class="text-xs text-slate-500"><?= $t('सिफारिस', 'Recommendation') ?></div>
            <div class="text-sm font-bold <?= ($nepse['forecast']['recommendation'] ?? '') === 'buy' ? 'text-emerald-600' : (($nepse['forecast']['recommendation'] ?? '') === 'sell' ? 'text-red-600' : 'text-slate-700') ?>">
              <?= $t(ucfirst($nepse['forecast']['recommendation'] ?? 'hold'), ucfirst($nepse['forecast']['recommendation'] ?? 'hold')) ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
    
    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
      <span><?= $t('स्रोत', 'Source') ?>: <?= h($nepse['source'] ?? 'NEPSE') ?></span>
      <span><?= $t('अपडेट', 'Updated') ?>: <?= $nepse['updated_at'] ?? date('H:i') ?></span>
    </div>
  </div>
  <?php else: ?>
  <div class="app-card p-6 text-center">
    <i data-lucide="cloud-off" class="w-12 h-12 mx-auto text-slate-300 mb-3"></i>
    <p class="text-sm text-slate-500 ne"><?= h($nepse['note'] ?? $t('NEPSE डाटा उपलब्ध छैन', 'NEPSE data unavailable')) ?></p>
    <a href="https://nepalstock.com.np" target="_blank" class="inline-flex items-center gap-1 mt-2 text-sm text-sky-600">
      <?= $t('NEPSE वेबसाइट हेर्नुस्', 'Visit NEPSE website') ?> <i data-lucide="external-link" class="w-4 h-4"></i>
    </a>
  </div>
  <?php endif; ?>
  
  <?php if (!empty($nepse['source'])): ?>
  <div class="mt-2 text-xs text-slate-400 flex items-center justify-between">
    <span><?= $t('स्रोत', 'Source') ?>: <?= $nepse['source'] ?></span>
    <?php if (!empty($nepse['source_url'])): ?>
    <a href="<?= htmlspecialchars($nepse['source_url']) ?>" target="_blank" class="text-sky-600 hover:underline"><?= $t('आधिकारिक स्रोत', 'Official Source') ?> →</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</section>

<!-- Gold & Silver Section -->
<section id="gold" class="mt-6 fade-up">
  <div class="sec-title">
    <i data-lucide="gem" class="w-4 h-4 text-amber-500"></i>
    <span class="font-bold ne"><?= $t('सुनचाँदी भाउ', 'Gold & Silver Price') ?></span>
  </div>
  
  <?php if ($gold['available'] ?? false): ?>
  <div class="app-card overflow-hidden">
    <!-- Gold -->
    <div class="p-4 bg-gradient-to-r from-amber-50 to-orange-50 border-b border-amber-100">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 text-white flex items-center justify-center">
          <i data-lucide="gem" class="w-6 h-6"></i>
        </div>
        <div class="flex-1">
          <h3 class="font-bold text-slate-900 ne"><?= $t('छापावाल सुन', 'Hallmark Gold') ?></h3>
          <div class="text-xs text-slate-500"><?= $t('प्रति तोला (11.664g)', 'Per Tola (11.664g)') ?></div>
        </div>
        <div class="text-right">
          <div class="text-2xl font-extrabold text-amber-700">रु <?= number_format($gold['hallmark_per_tola']) ?></div>
          <div class="text-xs text-slate-500">रु <?= number_format($gold['hallmark_per_gram'], 2) ?>/g</div>
        </div>
      </div>
    </div>
    
    <!-- Tejabi Gold -->
    <div class="p-4 border-b border-slate-100">
      <div class="flex items-center justify-between">
        <div>
          <h4 class="font-semibold text-slate-700 ne"><?= $t('तेजाबी सुन', 'Tejabi Gold') ?></h4>
          <div class="text-xs text-slate-500"><?= $t('प्रति तोला', 'Per Tola') ?></div>
        </div>
        <div class="text-right">
          <div class="text-lg font-bold text-slate-900">रु <?= number_format($gold['tejabi_per_tola']) ?></div>
        </div>
      </div>
    </div>
    
    <!-- Silver -->
    <div class="p-4 bg-gradient-to-r from-slate-50 to-slate-100">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-slate-400 to-slate-600 text-white flex items-center justify-center">
          <i data-lucide="circle-dot" class="w-5 h-5"></i>
        </div>
        <div class="flex-1">
          <h4 class="font-semibold text-slate-700 ne"><?= $t('चाँदी', 'Silver') ?></h4>
          <div class="text-xs text-slate-500"><?= $t('प्रति तोला', 'Per Tola') ?></div>
        </div>
        <div class="text-lg font-bold text-slate-700">रु <?= number_format($gold['silver_per_tola']) ?></div>
      </div>
    </div>
    
    <div class="px-4 py-2 bg-slate-50 flex items-center justify-between text-xs text-slate-400">
      <span><?= $t('स्रोत', 'Source') ?>: <?= h($gold['source'] ?? 'FENEGOSIDA') ?></span>
      <span><?= $gold['updated_at'] ?? date('Y-m-d') ?></span>
    </div>
  </div>
  <?php else: ?>
  <div class="app-card p-6 text-center">
    <p class="text-sm text-slate-500 ne"><?= $t('सुनचाँदी भाउ उपलब्ध छैन', 'Gold price unavailable') ?></p>
  </div>
  <?php endif; ?>
  
  <?php if (!empty($gold['source'])): ?>
  <div class="mt-2 text-xs text-slate-400 flex items-center justify-between">
    <span><?= $t('स्रोत', 'Source') ?>: <?= $gold['source'] ?></span>
    <?php if (!empty($gold['source_url'])): ?>
    <a href="<?= htmlspecialchars($gold['source_url']) ?>" target="_blank" class="text-sky-600 hover:underline"><?= $t('आधिकारिक स्रोत', 'Official Source') ?> →</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</section>

<!-- Forex Section -->
<section id="forex" class="mt-6 fade-up">
  <div class="sec-title">
    <i data-lucide="currency" class="w-4 h-4 text-emerald-500"></i>
    <span class="font-bold ne"><?= $t('विनिमय दर', 'Exchange Rates') ?></span>
  </div>
  
  <?php if (($forex['available'] ?? false) && !empty($forex['rates'])): ?>
  <div class="app-card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-600">
          <tr>
            <th class="text-left px-4 py-3 font-semibold"><?= $t('मुद्रा', 'Currency') ?></th>
            <th class="text-right px-4 py-3 font-semibold"><?= $t('एकाइ', 'Unit') ?></th>
            <th class="text-right px-4 py-3 font-semibold"><?= $t('खरिद', 'Buy') ?></th>
            <th class="text-right px-4 py-3 font-semibold"><?= $t('बिक्री', 'Sell') ?></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($forex['rates'] as $rate): ?>
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">
                  <?= $rate['code'] ?>
                </span>
                <span class="font-medium text-slate-700"><?= h($rate['currency']) ?></span>
              </div>
            </td>
            <td class="text-right px-4 py-3 text-slate-500"><?= $rate['unit'] ?></td>
            <td class="text-right px-4 py-3 font-semibold text-slate-900"><?= number_format($rate['buy'], 2) ?></td>
            <td class="text-right px-4 py-3 font-semibold text-emerald-600"><?= number_format($rate['sell'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    
    <div class="px-4 py-2 bg-slate-50 flex items-center justify-between text-xs text-slate-400">
      <span><?= $t('स्रोत', 'Source') ?>: Nepal Rastra Bank</span>
      <span><?= $forex['updated_at'] ?? date('Y-m-d') ?></span>
    </div>
  </div>
  <?php else: ?>
  <div class="app-card p-6 text-center">
    <p class="text-sm text-slate-500 ne"><?= h($forex['note'] ?? $t('विनिमय दर उपलब्ध छैन', 'Forex rates unavailable')) ?></p>
  </div>
  <?php endif; ?>
  
  <?php if (!empty($forex['source'])): ?>
  <div class="mt-2 text-xs text-slate-400 flex items-center justify-between">
    <span><?= $t('स्रोत', 'Source') ?>: <?= $forex['source'] ?></span>
    <?php if (!empty($forex['source_url'])): ?>
    <a href="<?= htmlspecialchars($forex['source_url']) ?>" target="_blank" class="text-sky-600 hover:underline"><?= $t('आधिकारिक स्रोत', 'Official Source') ?> →</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</section>

<!-- Fuel Section -->
<section id="fuel" class="mt-6 mb-8 fade-up">
  <div class="sec-title">
    <i data-lucide="fuel" class="w-4 h-4 text-orange-500"></i>
    <span class="font-bold ne"><?= $t('इन्धन मूल्य', 'Fuel Prices') ?></span>
  </div>
  
  <?php if ($petrol['available'] ?? false): ?>
  <div class="app-card overflow-hidden">
    <div class="grid grid-cols-2 divide-x divide-slate-100">
      <!-- Petrol -->
      <div class="p-4 text-center">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-500 to-orange-600 text-white flex items-center justify-center mx-auto mb-2">
          <i data-lucide="fuel" class="w-6 h-6"></i>
        </div>
        <h4 class="font-semibold text-slate-700 ne"><?= $t('पेट्रोल', 'Petrol') ?></h4>
        <div class="text-2xl font-extrabold text-slate-900 mt-1">रु <?= number_format($petrol['petrol'], 2) ?></div>
        <div class="text-xs text-slate-500"><?= $t('प्रति लिटर', 'Per Litre') ?></div>
      </div>
      
      <!-- Diesel -->
      <div class="p-4 text-center">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-slate-600 to-slate-800 text-white flex items-center justify-center mx-auto mb-2">
          <i data-lucide="fuel" class="w-6 h-6"></i>
        </div>
        <h4 class="font-semibold text-slate-700 ne"><?= $t('डिजेल', 'Diesel') ?></h4>
        <div class="text-2xl font-extrabold text-slate-900 mt-1">रु <?= number_format($petrol['diesel'], 2) ?></div>
        <div class="text-xs text-slate-500"><?= $t('प्रति लिटर', 'Per Litre') ?></div>
      </div>
    </div>
    
    <div class="grid grid-cols-2 divide-x divide-slate-100 border-t border-slate-100">
      <!-- Kerosene -->
      <div class="p-3 text-center">
        <h4 class="text-sm font-medium text-slate-600 ne"><?= $t('मट्टितेल', 'Kerosene') ?></h4>
        <div class="text-lg font-bold text-slate-900">रु <?= number_format($petrol['kerosene'], 2) ?>/L</div>
      </div>
      
      <!-- LPG -->
      <div class="p-3 text-center">
        <h4 class="text-sm font-medium text-slate-600 ne"><?= $t('ग्यास (14.2kg)', 'LPG (14.2kg)') ?></h4>
        <div class="text-lg font-bold text-slate-900">रु <?= number_format($petrol['lpg_cylinder']) ?></div>
      </div>
    </div>
    
    <div class="px-4 py-2 bg-slate-50 flex items-center justify-between text-xs text-slate-400">
      <span><?= $t('स्रोत', 'Source') ?>: <?= $petrol['source'] ?? 'Nepal Oil Corporation' ?></span>
      <span><?= $petrol['updated_at'] ?? date('Y-m-d') ?></span>
    </div>
    <?php if (!empty($petrol['source_url'])): ?>
    <div class="px-4 py-1 bg-slate-50 flex items-center justify-between text-xs text-slate-400 border-t border-slate-100">
      <a href="<?= htmlspecialchars($petrol['source_url']) ?>" target="_blank" class="text-sky-600 hover:underline"><?= $t('आधिकारिक स्रोत हेर्नुहोस्', 'View Official Source') ?> →</a>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/footer.php'; ?>
