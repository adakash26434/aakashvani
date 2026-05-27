<?php
/**
 * Health Tips - Daily health advice and wellness tips
 * Covers nutrition, exercise, mental health, and more
 */
$pageTitle = 'स्वास्थ्य सुझाव | आकाशवाणी';
$pageDesc  = 'दैनिक स्वास्थ्य सुझाव र स्वस्थ जीवनशैलीको लागि।';
require_once __DIR__ . '/header.php';
?>
<main class="app-main ht-wrap">

<!-- Header -->
<section class="ht-hero">
  <h1><i data-lucide="heart-pulse" class="w-6 h-6"></i>स्वास्थ्य सुझाव</h1>
  <p>दैनिक स्वास्थ्य सुझावहरू</p>
</section>

<!-- Daily Tip Card -->
<section class="ht-daily">
  <div class="flex items-center gap-2 mb-2">
    <i data-lucide="sun" class="w-5 h-5"></i>
    <p class="text-[12px] font-bold">आजको सुझाव</p>
  </div>
  <p class="text-[16px] font-bold leading-relaxed" id="daily-tip">लोड हुँदैछ...</p>
</section>

<!-- Category Tabs -->
<section class="flex gap-2 overflow-x-auto no-sb pb-2 mb-4">
  <button onclick="filterTips('all')" class="ht-cat-btn active">सबै</button>
  <button onclick="filterTips('nutrition')" class="ht-cat-btn">पोषण</button>
  <button onclick="filterTips('exercise')" class="ht-cat-btn">व्यायाम</button>
  <button onclick="filterTips('mental')" class="ht-cat-btn">मानसिक</button>
  <button onclick="filterTips('sleep')" class="ht-cat-btn">निद्रा</button>
</section>

<!-- Tips List -->
<section class="space-y-3 mb-4" id="tips-list">
</section>

<!-- Quick Health Check -->
<section class="ft-card">
  <p class="text-[12px] font-bold text-slate-600 uppercase tracking-wide mb-3">द्रुत स्वास्थ्य जाँच</p>
  <div class="space-y-2">
    <div class="ft-stat">
      <span class="text-[13px] font-semibold text-slate-700">पानी पिउने</span>
      <span class="text-[11px] text-teal-600 font-bold">८ गिलास/दिन</span>
    </div>
    <div class="ft-stat">
      <span class="text-[13px] font-semibold text-slate-700">निद्रा</span>
      <span class="text-[11px] text-teal-600 font-bold">७-९ घण्टा</span>
    </div>
    <div class="ft-stat">
      <span class="text-[13px] font-semibold text-slate-700">चाल</span>
      <span class="text-[11px] text-teal-600 font-bold">१०,०००/दिन</span>
    </div>
  </div>
</section>

<!-- Info Note -->
<section class="px-4 mb-4">
  <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
    <div class="flex items-start gap-3">
      <i data-lucide="info" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
      <div class="text-[11px] text-blue-800">
        <p class="font-semibold mb-1">अस्वीकरण</p>
        <p>यी सुझावहरू सामान्य जानकारीका लागि मात्र हुन्। चिकित्सकीय सल्लाहका लागि स्वास्थ्य व्यवसायीसँग सम्पर्क गर्नुहोस्।</p>
      </div>
    </div>
  </div>
</section>

<div class="pb-4"></div>
</main>

<script>
(function(){
  var healthTips = [
    { category: 'nutrition', title: 'बढी पानी पिउनुहोस्', desc: 'दिनमा कम्तिमा ८ गिलास पानी पिएर हाइड्रेटेड रहनुहोस्। पानीले शरीरको तापक्रम मर्मत गर्छ, जोर्नीहरूलाई चिक्नयो दिन्छ र पोषक तत्वहरू वितरण गर्छ।', icon: 'droplets', color: 'bg-blue-100 text-blue-700' },
    { category: 'nutrition', title: 'बढी तरकारी खानुहोस्', desc: 'आफ्नो खानेमा विभिन्न रंगीन तरकारीहरू समावेश गर्नुहोस्। तिनीहरूले आवश्यक भिटामिन, खनिज र फाइबर प्रदान गर्छन्।', icon: 'carrot', color: 'bg-orange-100 text-orange-700' },
    { category: 'nutrition', title: 'चिनी कम गर्नुहोस्', desc: 'आफ्नो खानेमा थपिएको चिनी सीमित गर्नुहोस्। अत्यधिक चिनीले वजन बढाउन, मधुमेह र हृदय रोग हुन सक्छ।', icon: 'candy-off', color: 'bg-pink-100 text-pink-700' },
    { category: 'nutrition', title: 'हरेक खानामा प्रोटिन खानुहोस्', desc: 'प्रोटिनले ऊतकहरू निर्माण र मर्मत गर्न मद्दत गर्छ। हरेक खानामा दुबौ मासु, अण्डा, डेयरी, दाल वा नट्स समावेश गर्नुहोस्।', icon: 'beef', color: 'bg-red-100 text-red-700' },
    { category: 'exercise', title: 'दैनिक ३० मिनेट हिँड्नुहोस्', desc: 'नियमित हिँड्दाले हृदय स्वास्थ्य सुधार्छ, हाडहरूलाई मजबुत बनाउँछ र मनोवृत्ति बढाउँछ।', icon: 'footprints', color: 'bg-green-100 text-green-700' },
    { category: 'exercise', title: 'शक्ति प्रशिक्षण', desc: 'मांसपेशी द्रव्यमान निर्माण गर्न र चयापचय बढाउन हप्तामा दुई पटक प्रतिरोध व्यायाम समावेश गर्नुहोस्।', icon: 'dumbbell', color: 'bg-purple-100 text-purple-700' },
    { category: 'exercise', title: 'नियमित तनाव खुल्नुहोस्', desc: 'तनावले लचिलता सुधार्छ, मांसपेशी तनाव कम गर्छ र चोटपटक रोक्छ।', icon: 'stretch-horizontal', color: 'bg-teal-100 text-teal-700' },
    { category: 'exercise', title: 'बस्नुबाट ब्रेक लिनुहोस्', desc: 'दीर्घकालीन रोगको जोखिम कम गर्न हरेक ३० मिनेटमा उठेर हिँड्नुहोस्।', icon: 'armchair', color: 'bg-amber-100 text-amber-700' },
    { category: 'mental', title: 'ध्यान अभ्यास गर्नुहोस्', desc: 'तनाव कम गर्न र ध्यान केन्द्रित गर्न दिनमा १० मिनेट ध्यान वा गहिरो सास फेर्नुहोस्।', icon: 'brain', color: 'bg-violet-100 text-violet-700' },
    { category: 'mental', title: 'अरूसँग जोड्नुहोस्', desc: 'सामाजिक सम्बन्धले मानसिक स्वास्थ्य सुधार्छ। नियमित रूपमा साथीहरू र परिवारसँग समय बिताउनुहोस्।', icon: 'users', color: 'bg-pink-100 text-pink-700' },
    { category: 'mental', title: 'स्क्रिन समय सीमित गर्नुहोस्', desc: 'निद्रा र मानसिक स्पष्टता सुधार्न विशेष गरी सुत्नुअघि इलेक्ट्रोनिक उपकरणहरूमा समय कम गर्नुहोस्।', icon: 'smartphone', color: 'bg-slate-100 text-slate-700' },
    { category: 'mental', title: 'कृतज्ञता अभ्यास गर्नुहोस्', desc: 'खुशी बढाउन र तनाव कम गर्न दिनमा तपाईं कृतज्ञ छन् तीन चीजहरू लेख्नुहोस्।', icon: 'heart', color: 'bg-rose-100 text-rose-700' },
    { category: 'sleep', title: 'निद्रा अनुसूची पालन गर्नुहोस्', desc: 'शरीरको घडी नियमित गर्न हप्तामा पनि बिहान उठ्ने र सुत्ने समय एउटै राख्नुहोस्।', icon: 'clock', color: 'bg-indigo-100 text-indigo-700' },
    { category: 'sleep', title: 'सुत्ने अनुसूची बनाउनुहोस्', desc: 'पढ्ने वा गरम पानी मा नुहाउने जस्ता आरामदायक पूर्व-निद्रा गतिविधिहरू विकास गर्नुहोस्।', icon: 'moon', color: 'bg-blue-100 text-blue-700' },
    { category: 'sleep', title: 'सुत्नुअघि क्याफिन टार्नुहोस्', desc: 'निद्राको गुणस्तर सुधार्न सुत्नुभन्दा कम्तिमा ६ घण्टा अघि क्याफिन खपत रोक्नुहोस्।', icon: 'coffee', color: 'bg-amber-100 text-amber-700' },
    { category: 'sleep', title: 'कोठा चिसो राख्नुहोस्', desc: 'उत्तम निद्राको लागि शयनकोठाको तापक्रम ६०-६७°F (१५-१९°C) को बीचमा राख्नुहोस्।', icon: 'thermometer', color: 'bg-cyan-100 text-cyan-700' },
  ];
  
  // Show random daily tip
  function showDailyTip(){
    var randomTip = healthTips[Math.floor(Math.random() * healthTips.length)];
    document.getElementById('daily-tip').textContent = randomTip.title + ': ' + randomTip.desc;
  }
  
  window.filterTips = function(category){
    var buttons = document.querySelectorAll('.tip-cat-btn');
    buttons.forEach(function(btn){
      btn.classList.remove('active', 'bg-teal-100', 'text-teal-700', 'border-teal-200');
      btn.classList.add('bg-slate-100', 'text-slate-600', 'border-slate-200');
    });
    event.target.classList.remove('bg-slate-100', 'text-slate-600', 'border-slate-200');
    event.target.classList.add('active', 'bg-teal-100', 'text-teal-700', 'border-teal-200');
    
    var filtered = category === 'all' ? healthTips : healthTips.filter(function(t){ return t.category === category; });
    renderTips(filtered);
  };
  
  function renderTips(tips){
    var container = document.getElementById('tips-list');
    container.innerHTML = tips.map(function(tip){
      return '<div class="bg-white rounded-xl border border-slate-100 shadow-app p-4">' +
        '<div class="flex items-start gap-3">' +
          '<div class="w-10 h-10 rounded-xl ' + tip.color + ' flex items-center justify-center flex-shrink-0">' +
            '<i data-lucide="' + tip.icon + '" class="w-5 h-5"></i>' +
          '</div>' +
          '<div class="flex-1">' +
            '<p class="text-[13px] font-bold text-slate-900 mb-1">' + tip.title + '</p>' +
            '<p class="text-[11px] text-slate-600 leading-relaxed">' + tip.desc + '</p>' +
          '</div>' +
        '</div>' +
      '</div>';
    }).join('');
    
    if(window.lucide) lucide.createIcons();
  }
  
  // Initialize
  showDailyTip();
  renderTips(healthTips);
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
