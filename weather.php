<?php
/**
 * Weather Page - Real-time weather for Nepali cities
 * Uses Open-Meteo API for accurate weather data
 */
require_once __DIR__ . '/header.php';

// Nepali cities with coordinates
$nepalCities = [
    'Kathmandu'   => ['lat' => 27.7172, 'lon' => 85.3240, 'province' => 'Bagmati', 'name_np' => 'काठमाडौं'],
    'Pokhara'     => ['lat' => 28.2096, 'lon' => 83.9856, 'province' => 'Gandaki', 'name_np' => 'पोखरा'],
    'Biratnagar'  => ['lat' => 26.4525, 'lon' => 87.2718, 'province' => 'Province 1', 'name_np' => 'विराटनगर'],
    'Birgunj'     => ['lat' => 27.0104, 'lon' => 84.8770, 'province' => 'Madhesh', 'name_np' => 'वीरगंज'],
    'Janakpur'    => ['lat' => 26.7288, 'lon' => 85.9254, 'province' => 'Madhesh', 'name_np' => 'जनकपुर'],
    'Hetauda'     => ['lat' => 27.4287, 'lon' => 85.0322, 'province' => 'Bagmati', 'name_np' => 'हेटौडा'],
    'Nepalgunj'   => ['lat' => 28.0500, 'lon' => 81.6167, 'province' => 'Lumbini', 'name_np' => 'नेपालगंज'],
    'Dhangadhi'   => ['lat' => 28.6833, 'lon' => 80.6000, 'province' => 'Sudurpashchim', 'name_np' => 'धनगढी'],
    'Butwal'      => ['lat' => 27.7006, 'lon' => 83.4486, 'province' => 'Lumbini', 'name_np' => 'बुटवल'],
    'Dharan'      => ['lat' => 26.8122, 'lon' => 87.2847, 'province' => 'Province 1', 'name_np' => 'धरान'],
];

// Weather condition emojis and Nepali descriptions
function getWeatherInfo($code) {
    $conditions = [
        0 => ['emoji' => 'sun', 'ne' => 'खुला आकाश', 'en' => 'Clear sky'],
        1 => ['emoji' => 'sun-cloud', 'ne' => 'मुख्यतया सफा', 'en' => 'Mainly clear'],
        2 => ['emoji' => '⛅', 'ne' => 'आंशिक बादल', 'en' => 'Partly cloudy'],
        3 => ['emoji' => 'cloud', 'ne' => 'बादल', 'en' => 'Overcast'],
        45 => ['emoji' => 'cloud-fog', 'ne' => 'हुस्सु', 'en' => 'Foggy'],
        48 => ['emoji' => 'cloud-fog', 'ne' => 'तुषार हुस्सु', 'en' => 'Depositing rime fog'],
        51 => ['emoji' => '🌦️', 'ne' => 'हल्का वर्षा', 'en' => 'Light drizzle'],
        53 => ['emoji' => 'cloud-rain', 'ne' => 'मध्यम वर्षा', 'en' => 'Moderate drizzle'],
        55 => ['emoji' => 'cloud-rain', 'ne' => 'भारी वर्षा', 'en' => 'Dense drizzle'],
        61 => ['emoji' => 'cloud-rain', 'ne' => 'हल्का वर्षा', 'en' => 'Slight rain'],
        63 => ['emoji' => 'cloud-rain', 'ne' => 'वर्षा', 'en' => 'Moderate rain'],
        65 => ['emoji' => 'cloud-rain', 'ne' => 'भारी वर्षा', 'en' => 'Heavy rain'],
        71 => ['emoji' => '🌨️', 'ne' => 'हल्का हिउँ', 'en' => 'Slight snow'],
        73 => ['emoji' => '❄️', 'ne' => 'हिउँ', 'en' => 'Moderate snow'],
        75 => ['emoji' => '❄️', 'ne' => 'भारी हिउँ', 'en' => 'Heavy snow'],
        95 => ['emoji' => '⛈️', 'ne' => 'गडगडाहट', 'en' => 'Thunderstorm'],
    ];
    return $conditions[$code] ?? ['emoji' => 'thermometer', 'ne' => 'अज्ञात', 'en' => 'Unknown'];
}

// Fetch weather data
function fetchWeather($lat, $lon) {
    $url = sprintf(
        'https://api.open-meteo.com/v1/forecast?latitude=%.4f&longitude=%.4f&current=temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,wind_speed_10m&daily=temperature_2m_max,temperature_2m_min,weather_code&timezone=Asia%%2FKathmandu&forecast_days=7',
        $lat, $lon
    );
    
    $resp = @file_get_contents($url);
    if (!$resp) return null;
    return json_decode($resp, true);
}

$selectedCity = $_GET['city'] ?? 'Kathmandu';
$weatherData = null;

if (isset($nepalCities[$selectedCity])) {
    $city = $nepalCities[$selectedCity];
    $weatherData = fetchWeather($city['lat'], $city['lon']);
}
?>

<!-- ═══ WEATHER PAGE ═══════════════════════════════════════════════════════ -->
<section class="px-4 py-4 max-w-4xl mx-auto">
  
  <!-- Header -->
  <div class="flex items-center gap-4 mb-6">
    <a href="/index.php" class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-colors">
      <i data-lucide="arrow-left" class="w-5 h-5 text-gray-600"></i>
    </a>
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 rounded-2xl bg-sky-100 flex items-center justify-center">
        <i data-lucide="cloud-sun" class="w-6 h-6 text-sky-600"></i>
      </div>
      <div>
        <h1 class="text-xl font-bold text-gray-900 ne">मौसम</h1>
        <p class="text-sm text-gray-500">Live Weather • Nepal</p>
      </div>
    </div>
  </div>

  <!-- City Selector -->
  <div class="mb-6">
    <label class="text-sm font-medium text-gray-700 ne block mb-2">शहर छान्नुहोस्</label>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2">
      <?php foreach ($nepalCities as $cityKey => $cityInfo): ?>
        <a href="?city=<?= urlencode($cityKey) ?>" 
           class="px-3 py-2 rounded-xl text-center text-sm font-medium transition-all <?= $selectedCity === $cityKey ? 'bg-sky-500 text-white shadow-lg shadow-sky-200' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' ?>">
          <span class="ne"><?= htmlspecialchars($cityInfo['name_np']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <?php if ($weatherData && !empty($weatherData['current'])): 
    $current = $weatherData['current'];
    $weatherInfo = getWeatherInfo($current['weather_code'] ?? 0);
    $daily = $weatherData['daily'] ?? null;
  ?>
  
  <!-- Current Weather Card -->
  <div class="bg-gradient-to-br from-sky-400 to-blue-600 rounded-3xl p-6 text-white shadow-xl shadow-sky-200 mb-6">
    <div class="flex items-start justify-between">
      <div>
        <h2 class="text-3xl font-bold ne"><?= htmlspecialchars($nepalCities[$selectedCity]['name_np']) ?></h2>
        <p class="text-sky-100 text-sm mt-1"><?= htmlspecialchars($nepalCities[$selectedCity]['province']) ?></p>
        <div class="mt-4">
          <span class="text-6xl font-bold"><?= round($current['temperature_2m']) ?>°</span>
          <span class="text-2xl ml-2">C</span>
        </div>
        <p class="text-xl mt-2 ne"><?= $weatherInfo['ne'] ?></p>
        <p class="text-sky-100 text-sm">Feels like <?= round($current['apparent_temperature']) ?>°C</p>
      </div>
      <div class="text-8xl">
        <?= $weatherInfo['emoji'] ?>
      </div>
    </div>
    
    <!-- Weather Details Grid -->
    <div class="grid grid-cols-3 gap-4 mt-6 pt-6 border-t border-white/20">
      <div class="text-center">
        <div class="text-2xl mb-1">💧</div>
        <div class="text-lg font-semibold"><?= $current['relative_humidity_2m'] ?? '--' ?>%</div>
        <div class="text-xs text-sky-100 ne">आर्द्रता</div>
      </div>
      <div class="text-center">
        <div class="text-2xl mb-1">💨</div>
        <div class="text-lg font-semibold"><?= $current['wind_speed_10m'] ?? '--' ?> km/h</div>
        <div class="text-xs text-sky-100 ne">हावा</div>
      </div>
      <div class="text-center">
        <div class="text-2xl mb-1">thermometer</div>
        <div class="text-lg font-semibold"><?= round($current['apparent_temperature']) ?>°</div>
        <div class="text-xs text-sky-100 ne">अनुभव तापक्रम</div>
      </div>
    </div>
  </div>

  <!-- 7-Day Forecast -->
  <?php if ($daily && !empty($daily['time'])): ?>
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
      <i data-lucide="calendar" class="w-5 h-5 text-sky-500"></i>
      <span class="ne">७ दिनको पूर्वानुमान</span>
    </h3>
    
    <div class="space-y-3">
      <?php for ($i = 0; $i < min(7, count($daily['time'])); $i++): 
        $dayCode = $daily['weather_code'][$i] ?? 0;
        $dayInfo = getWeatherInfo($dayCode);
        $date = new DateTime($daily['time'][$i]);
        $dayName = $date->format('D');
        $dayNames = ['Sun' => 'आइत', 'Mon' => 'सोम', 'Tue' => 'मंगल', 'Wed' => 'बुध', 'Thu' => 'बिहि', 'Fri' => 'शुक्र', 'Sat' => 'शनि'];
        $neDay = $dayNames[$dayName] ?? $dayName;
      ?>
      <div class="flex items-center justify-between py-2 <?= $i > 0 ? 'border-t border-gray-100' : '' ?>">
        <div class="flex items-center gap-3">
          <span class="w-12 text-sm font-medium text-gray-600 ne"><?= $neDay ?></span>
          <span class="text-xl"><?= $dayInfo['emoji'] ?></span>
          <span class="text-sm text-gray-700 ne"><?= $dayInfo['ne'] ?></span>
        </div>
        <div class="flex items-center gap-3">
          <span class="text-sm font-semibold text-blue-600"><?= round($daily['temperature_2m_min'][$i]) ?>°</span>
          <span class="text-sm font-semibold text-gray-900"><?= round($daily['temperature_2m_max'][$i]) ?>°</span>
        </div>
      </div>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php else: ?>
  
  <!-- Loading/Error State -->
  <div class="bg-gray-50 rounded-2xl p-8 text-center">
    <div class="text-6xl mb-4">sun-cloud</div>
    <h3 class="text-lg font-semibold text-gray-900 mb-2 ne">मौसम डाटा लोड हुँदैछ</h3>
    <p class="text-gray-500">Unable to fetch weather data. Please try again.</p>
  </div>
  
  <?php endif; ?>

  <!-- Earthquake Monitor -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
      <i data-lucide="activity" class="w-5 h-5 text-red-500"></i>
      <span class="ne">भूकम्प निगरानी</span>
    </h3>
    
    <div class="bg-gradient-to-r from-orange-50 to-red-50 rounded-xl p-4 border border-orange-100 mb-4">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center">
          <span class="text-2xl">🌋</span>
        </div>
        <div>
          <p class="text-sm text-orange-800 ne">नेपाल र छिमेकी क्षेत्रमा हालैका भूकम्पहरू</p>
          <p class="text-xs text-orange-600 mt-1">Real-time data from USGS Earthquake Catalog</p>
        </div>
      </div>
    </div>
    
    <div id="earthquake-list" class="space-y-3">
      <div class="text-center py-4">
        <div class="inline-block w-6 h-6 border-2 border-red-500 border-t-transparent rounded-full animate-spin"></div>
        <p class="text-sm text-gray-500 mt-2 ne">भूकम्प डाटा लोड हुँदैछ...</p>
      </div>
    </div>
    
    <div class="mt-4 pt-4 border-t border-gray-100">
      <a href="https://www.seismonepal.gov.np/" target="_blank" class="inline-flex items-center gap-2 text-sm text-red-600 hover:text-red-700 font-medium">
        <span class="ne">National Earthquake Monitoring Centre मा हेर्नुहोस्</span>
        <i data-lucide="external-link" class="w-4 h-4"></i>
      </a>
    </div>
  </div>

  <script>
  // Load earthquake data
  (function(){
    fetch('/api/earthquake.php?minmag=4.0&days=7')
      .then(r => r.ok ? r.json() : null)
      .then(d => {
        var box = document.getElementById('earthquake-list');
        if(!box || !d || !d.ok || !d.data || !d.data.length){
          if(box) box.innerHTML = '<div class="text-center py-4 text-gray-500 ne">भूकम्प डाटा उपलब्ध छैन</div>';
          return;
        }
        
        box.innerHTML = d.data.slice(0, 5).map(function(eq){
          var color = eq.significance.color || '#6b7280';
          var timeAgo = Math.floor((Date.now() - eq.time) / 3600000);
          var timeText = timeAgo < 1 ? 'केही मिनेट अघि' : timeAgo + ' घण्टा अघि';
          
          return '<div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">'+
            '<div class="flex items-center gap-3">'+
              '<div class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold text-sm" style="background:'+color+'">'+
                eq.magnitude+
              '</div>'+
              '<div>'+
                '<p class="font-medium text-gray-900 ne">'+eq.place+'</p>'+
                '<p class="text-xs text-gray-500">'+timeText+' • '+eq.distance_from_kathmandu+' km from Kathmandu</p>'+
              '</div>'+
            '</div>'+
            '<a href="'+eq.url+'" target="_blank" class="text-gray-400 hover:text-gray-600">'+
              '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>'+
            '</a>'+
          '</div>';
        }).join('');
      })
      .catch(() => {
        var box = document.getElementById('earthquake-list');
        if(box) box.innerHTML = '<div class="text-center py-4 text-gray-500 ne">भूकम्प डाटा लोड हुन सकेन</div>';
      });
  })();
  </script>

  <!-- Official Source -->
  <div class="bg-amber-50 rounded-xl p-4 border border-amber-100">
    <p class="text-sm text-amber-800 ne">
      <span class="font-medium">आधिकारिक स्रोत:</span> 
      यो डाटा Open-Meteo API बाट लिइएको हो। आधिकारिक मौसम जानकारीका लागि 
      <a href="https://www.dhm.gov.np/" target="_blank" class="underline font-medium">DHM</a> 
      वा <a href="https://www.mfd.gov.np/" target="_blank" class="underline font-medium">MFD</a> 
      हेर्नुहोस्।
    </p>
  </div>

</section>

<script>
// Initialize Lucide icons
document.addEventListener('DOMContentLoaded', function() {
  if (window.lucide) {
    lucide.createIcons();
  }
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
