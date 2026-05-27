<?php
/**
 * आकाशवाणी — BS Date Helper
 * Shared PHP functions for Bikram Sambat date conversion & display.
 * Include this file once in dashboard, settings, or functions.php.
 */
if (!function_exists('adToBs')) {

$_NSH_BS_DATA = [
    2070=>[0,31,31,32,31,31,31,30,29,30,29,30,30], 2071=>[0,31,31,32,31,31,31,30,29,30,29,30,30],
    2072=>[0,31,32,31,32,31,30,30,29,30,29,30,30], 2073=>[0,31,32,31,32,31,30,30,30,29,29,30,31],
    2074=>[0,31,31,31,32,31,31,30,29,30,29,30,30], 2075=>[0,31,31,32,31,31,31,30,29,30,29,30,30],
    2076=>[0,31,32,31,32,31,30,30,29,30,29,30,30], 2077=>[0,31,32,31,32,31,30,30,30,29,30,29,31],
    2078=>[0,31,31,31,32,31,31,30,29,30,29,30,30], 2079=>[0,31,31,32,31,31,31,30,29,30,29,30,30],
    2080=>[0,31,31,32,31,31,30,30,29,30,29,30,30], 2081=>[0,31,31,32,31,31,31,30,29,30,29,30,30],
    2082=>[0,31,32,31,32,31,30,30,30,29,30,29,31], 2083=>[0,31,32,31,32,31,30,30,30,29,30,30,30],
    2084=>[0,31,31,32,31,31,30,30,30,29,30,30,30], 2085=>[0,31,32,31,32,31,30,30,30,29,30,29,31],
    2086=>[0,31,32,31,32,31,30,30,30,29,30,29,31], 2087=>[0,31,31,32,31,31,31,30,29,30,29,30,30],
    2088=>[0,31,31,32,31,32,30,29,29,30,29,30,30], 2089=>[0,31,32,31,32,31,30,30,29,30,29,30,31],
    2090=>[0,30,32,31,32,31,30,30,30,29,29,30,31],
];
$_NSH_BS_MONTHS = ['','बैशाख','जेठ','असार','श्रावण','भाद्र','आश्विन','कार्तिक','मंसिर','पौष','माघ','फाल्गुन','चैत्र'];
$_NSH_BS_DAYS   = ['आइतबार','सोमबार','मंगलबार','बुधबार','बिहिबार','शुक्रबार','शनिबार'];
$_NSH_NE_DIGITS = ['०','१','२','३','४','५','६','७','८','९'];

/**
 * Convert AD (Gregorian) date to BS.
 * Returns [bsY, bsM, bsD] or null on error.
 */
function adToBs(int $y, int $m, int $d): ?array {
    global $_NSH_BS_DATA;
    // Reference: BS 2083 Baisakh 1 = AD 2026 April 14
    $refJd  = gregoriantojd(4, 14, 2026);
    $dayJd  = gregoriantojd($m, $d, $y);
    $diff   = $dayJd - $refJd;
    $bsY = 2083; $bsM = 1; $bsD = 1;
    if ($diff >= 0) {
        $rem = $diff;
        while ($rem > 0) {
            $dim  = $_NSH_BS_DATA[$bsY][$bsM] ?? 30;
            $left = $dim - $bsD;
            if ($rem <= $left) { $bsD += $rem; $rem = 0; }
            else { $rem -= ($left + 1); $bsD = 1; $bsM++; if ($bsM > 12) { $bsM = 1; $bsY++; } }
        }
    } else {
        $rem = -$diff;
        while ($rem > 0) {
            if ($bsD > 1) { $s = min($rem, $bsD - 1); $bsD -= $s; $rem -= $s; }
            else { $bsM--; if ($bsM < 1) { $bsM = 12; $bsY--; } $bsD = $_NSH_BS_DATA[$bsY][$bsM] ?? 30; $rem -= 1; }
        }
    }
    return [$bsY, $bsM, $bsD];
}

/**
 * Convert a digit character to Nepali (Devanagari) equivalent.
 */
function toNeDigits(string $s): string {
    return strtr($s, ['0'=>'०','1'=>'१','2'=>'२','3'=>'३','4'=>'४','5'=>'५','6'=>'६','7'=>'७','8'=>'८','9'=>'९']);
}

/**
 * Format an AD date string to BS display.
 * Example: "2024-12-15 14:30:00" → "१५ मंसिर २०८१" or with time → "१५ मंसिर २०८१, दिउँसो २:३०"
 */
function bsDate(string $adStr, bool $withTime = false, bool $nepaliDigits = true): string {
    global $_NSH_BS_MONTHS;
    if (!$adStr || $adStr === '0000-00-00' || $adStr === '0000-00-00 00:00:00') return '—';
    try {
        $dt = new DateTime($adStr, new DateTimeZone('Asia/Kathmandu'));
    } catch (\Exception $e) { return '—'; }
    $bs = adToBs((int)$dt->format('Y'), (int)$dt->format('n'), (int)$dt->format('j'));
    if (!$bs) return '—';
    [$bsY,$bsM,$bsD] = $bs;
    $day   = $nepaliDigits ? toNeDigits((string)$bsD) : $bsD;
    $year  = $nepaliDigits ? toNeDigits((string)$bsY) : $bsY;
    $month = $_NSH_BS_MONTHS[$bsM];
    $out   = "$day $month $year";
    if ($withTime) {
        $h = (int)$dt->format('G');
        $min = $dt->format('i');
        $period = $h < 4 ? 'राति' : ($h < 12 ? 'बिहान' : ($h < 17 ? 'दिउँसो' : ($h < 20 ? 'साँझ' : 'राति')));
        $h12    = $h % 12 ?: 12;
        $time   = $nepaliDigits ? toNeDigits("$h12:$min") : "$h12:$min";
        $out   .= ", $period $time";
    }
    return $out;
}

/**
 * Get today's BS date as an array.
 * Returns ['year' => bsY, 'month' => bsM, 'day' => bsD, 'weekday' => nepaliDayName]
 */
function getTodayBS(): array {
    global $_NSH_BS_DAYS;
    $now = new DateTime('now', new DateTimeZone('Asia/Kathmandu'));
    $bs = adToBs((int)$now->format('Y'), (int)$now->format('n'), (int)$now->format('j'));
    if (!$bs) {
        return ['year' => 2083, 'month' => 1, 'day' => 1, 'weekday' => 'आइतबार'];
    }
    [$bsY, $bsM, $bsD] = $bs;
    $weekdayIndex = (int)$now->format('w');
    $weekday = $_NSH_BS_DAYS[$weekdayIndex] ?? 'आइतबार';
    return ['year' => $bsY, 'month' => $bsM, 'day' => $bsD, 'weekday' => $weekday];
}

/**
 * Return BS date string for use as placeholder/display value of a date input.
 * Format: "२०८२-०२-०५" (Y-M-D in Nepali digits)
 */
function bsDateForInput(string $adStr): string {
    if (!$adStr) return '';
    try {
        $dt = new DateTime($adStr, new DateTimeZone('Asia/Kathmandu'));
    } catch (\Exception $e) { return ''; }
    $bs = adToBs((int)$dt->format('Y'), (int)$dt->format('n'), (int)$dt->format('j'));
    if (!$bs) return '';
    [$bsY,$bsM,$bsD] = $bs;
    return toNeDigits(sprintf('%04d-%02d-%02d', $bsY, $bsM, $bsD));
}

/**
 * Render a BS date picker field with hidden AD input.
 * Usage: adBsDateField('expires_at','Expires At','2024-12-15 00:00:00','Leave blank = never');
 */
function adBsDateField(string $name, string $label, string $adVal = '', string $hint = '', bool $required = false): void {
    $bsDisplay = $adVal ? bsDateForInput($adVal) : '';
    $adIso     = $adVal ? (new DateTime($adVal))->format('Y-m-d') : '';
    $req       = $required ? 'required' : '';
    $hiddenId  = 'ndp_ad_' . preg_replace('/\W/', '_', $name) . '_' . rand(1000,9999);
    $req_attr  = $required ? " required" : "";
    echo "
<div>
  <label class='block text-xs font-bold uppercase tracking-wider text-[#64748b] mb-1.5'>$label " . ($required ? "<span style='color:#ef4444'>*</span>" : "(optional)") . "</label>
  <div class='flex gap-2'>
    <input type='text' name='{$name}_bs_display'
           data-nepali-date data-nepali-target='$hiddenId'
           value='$bsDisplay'
           placeholder='मिति छान्नुस् (BS)'
           class='flex-1 bg-[#fafaf9] border border-[#e2e8f0] px-3 py-2 text-sm text-[#0f172a] rounded focus:outline-none focus:border-[#0f766e]'
           autocomplete='off'{$req_attr} />
    <input type='time' name='{$name}_time_part' value='' placeholder='समय'
           class='w-28 bg-[#fafaf9] border border-[#e2e8f0] px-3 py-2 text-sm text-[#0f172a] rounded focus:outline-none focus:border-[#0f766e]'/>
  </div>
  <input type='hidden' id='$hiddenId' name='$name' value='$adIso' />
  " . ($hint ? "<p class='text-[11px] text-[#64748b] mt-1 font-mono'>$hint</p>" : "") . "
  <p class='text-[10px] text-[#94a3b8] mt-0.5'>📅 Bikram Sambat (BS) मिति — AD मा automatically रूपान्तरण हुन्छ</p>
</div>
<script>
(function(){
  var t = document.querySelector('input[name=\"{$name}_time_part\"]');
  var h = document.getElementById('$hiddenId');
  t && t.addEventListener('change', function(){
    var d = h.value ? h.value.split('T')[0] : h.value;
    if(d) h.value = d + 'T' + t.value;
  });
})();
</script>
";
}

} // end if (!function_exists)
