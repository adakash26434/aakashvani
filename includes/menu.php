<?php
/**
 * आकाशवाणी — Central Menu Registry
 * Single source of truth for navigation + search suggestions.
 * Edit this file to add/rename/remove menu items everywhere at once.
 *
 * Each item: ['label' => ne, 'label_en' => en, 'url' => path,
 *             'icon' => emoji, 'keywords' => 'extra search words',
 *             'group' => grouping label]
 */

function aakashvani_menu(): array {
    return [
        // ── Core / News ──────────────────────────────────────────────
        ['label'=>'गृहपृष्ठ',        'label_en'=>'Home',           'url'=>'/',                       'icon'=>'🏠','keywords'=>'home main index ghar','group'=>'मुख्य'],
        ['label'=>'ताजा समाचार',     'label_en'=>'Latest News',    'url'=>'/news.php',               'icon'=>'📰','keywords'=>'news samachar taja headlines','group'=>'समाचार'],
        ['label'=>'सूचना',           'label_en'=>'Notices',        'url'=>'/notices.php',            'icon'=>'📢','keywords'=>'notice suchana janakari govt','group'=>'समाचार'],
        ['label'=>'सरकारी जानकारी',  'label_en'=>'Govt Info',      'url'=>'/govt.php',               'icon'=>'🏛️','keywords'=>'sarkar government nepal info','group'=>'समाचार'],

        // ── Data / Live ─────────────────────────────────────────────
        ['label'=>'बजार भाउ',        'label_en'=>'Market Rates',   'url'=>'/market.php',             'icon'=>'💹','keywords'=>'gold silver fuel petrol diesel forex usd','group'=>'लाइभ डाटा'],
        ['label'=>'मौसम',            'label_en'=>'Weather',        'url'=>'/weather.php',            'icon'=>'🌤️','keywords'=>'mausam weather temperature rain','group'=>'लाइभ डाटा'],
        ['label'=>'पात्रो',          'label_en'=>'Nepali Patro',   'url'=>'/patro.php',              'icon'=>'📅','keywords'=>'patro calendar date tithi nepali','group'=>'लाइभ डाटा'],

        // ── Entertainment Hub ───────────────────────────────────────
        ['label'=>'सफलताका कथा',     'label_en'=>'Success Stories','url'=>'/success-stories.php',    'icon'=>'🏆','keywords'=>'safalta katha success inspiration youth','group'=>'मनोरञ्जन'],
        ['label'=>'नेपाल घुम्ने ठाउँ','label_en'=>'Visit Nepal',   'url'=>'/visit-nepal.php',        'icon'=>'🏔️','keywords'=>'tourism ghumne thau visit nepal photo travel','group'=>'मनोरञ्जन'],
        ['label'=>'अनलाइन रेडियो',   'label_en'=>'Online Radio',   'url'=>'/radio.php',              'icon'=>'📻','keywords'=>'radio fm kantipur image hits ujyaalo audio','group'=>'मनोरञ्जन'],

        // ── AI / Prediction ─────────────────────────────────────────
        ['label'=>'AI सहायक',        'label_en'=>'AI Assistant',   'url'=>'/ai.php',                 'icon'=>'🤖','keywords'=>'ai chat gpt sahayak assistant','group'=>'AI'],
        ['label'=>'AI भविष्यवाणी',   'label_en'=>'AI Prediction',  'url'=>'/prediction.php',         'icon'=>'🔮','keywords'=>'prediction forecast bhavishya ai','group'=>'AI'],

        // ── About / Misc ────────────────────────────────────────────
        ['label'=>'हाम्रो बारेमा',   'label_en'=>'About',          'url'=>'/about.php',              'icon'=>'ℹ️','keywords'=>'about hamro barema info','group'=>'अन्य'],
        ['label'=>'सम्पर्क',         'label_en'=>'Contact',        'url'=>'/contact.php',            'icon'=>'✉️','keywords'=>'contact sampark email phone','group'=>'अन्य'],
    ];
}

/** Filter menu by query (matches Nepali label, English label, keywords) */
function aakashvani_menu_search(string $q, int $limit = 8): array {
    $q = trim($q);
    if ($q === '') return [];
    $ql = mb_strtolower($q, 'UTF-8');
    $out = [];
    foreach (aakashvani_menu() as $item) {
        $hay = mb_strtolower(
            $item['label'].' '.$item['label_en'].' '.($item['keywords'] ?? '').' '.($item['group'] ?? ''),
            'UTF-8'
        );
        if (mb_strpos($hay, $ql) !== false) {
            $out[] = $item + ['type' => 'menu'];
            if (count($out) >= $limit) break;
        }
    }
    return $out;
}
