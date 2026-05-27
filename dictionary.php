<?php
/**
 * Dictionary - Nepali-English Dictionary
 * Search words and get meanings
 */
$pageTitle = 'Dictionary | आकाशवाणी';
$pageDesc  = 'Nepali-English dictionary with word meanings and definitions.';
require_once __DIR__ . '/header.php';
?>
<main class="app-main dict-wrap">

<!-- Header -->
<section class="dict-hero">
  <h1><i data-lucide="book" class="w-6 h-6"></i>Dictionary</h1>
  <p>शब्दकोश - नेपाली-English</p>
</section>

<!-- Search Card -->
<section class="dict-card">
    
    <!-- Language Toggle -->
    <div class="flex gap-2 mb-3">
      <button onclick="setLang('ne-en')" id="ne-en-btn" class="flex-1 py-2 rounded-lg text-[12px] font-semibold bg-indigo-100 text-indigo-700 border border-indigo-200">
        नेपाली → English
      </button>
      <button onclick="setLang('en-ne')" id="en-ne-btn" class="flex-1 py-2 rounded-lg text-[12px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
        English → नेपाली
      </button>
    </div>
    
    <!-- Search Input -->
    <div class="mb-3">
      <input type="text" id="search-input" placeholder="Type word to search..."
        class="w-full text-[13px] bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400">
    </div>
    
    <!-- Search Button -->
    <button onclick="searchWord()" id="search-btn"
      class="w-full bg-indigo-600 text-white font-bold py-3 rounded-xl shadow-app text-[15px] flex items-center justify-center gap-2 active:scale-[.98] transition-transform">
      <i data-lucide="search" class="w-5 h-5"></i>
      Search
    </button>
  </div>
</section>

<!-- Result Card -->
<section class="dict-card dict-result" id="result-section">
    <div class="flex items-center justify-between mb-3">
      <p class="text-[12px] font-bold text-slate-600 uppercase tracking-wide">Result</p>
      <button onclick="playPronunciation()" class="text-[10px] text-indigo-600 font-semibold flex items-center gap-1">
        <i data-lucide="volume-2" class="w-3 h-3"></i> Pronounce
      </button>
    </div>
    <div id="word-display" class="mb-3">
      <p class="text-[20px] font-black text-indigo-900" id="word-text"></p>
      <p class="text-[11px] text-slate-500" id="word-type"></p>
    </div>
    <div id="meanings" class="space-y-2">
    </div>
  </div>
</section>

<!-- Recent Searches -->
<section class="px-4 mb-4">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-app p-4">
    <p class="text-[12px] font-bold text-slate-600 uppercase tracking-wide mb-3">Recent Searches</p>
    <div class="flex flex-wrap gap-2" id="recent-searches">
      <span class="text-[11px] text-slate-400">No recent searches</span>
    </div>
  </div>
</section>

<!-- Popular Words -->
<section class="px-4 mb-4">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-app p-4">
    <p class="text-[12px] font-bold text-slate-600 uppercase tracking-wide mb-3">Popular Words</p>
    <div class="space-y-2">
      <div onclick="quickSearch('नमस्ते')" class="flex items-center justify-between p-3 bg-slate-50 rounded-xl cursor-pointer hover:bg-slate-100 transition-colors">
        <span class="text-[13px] font-semibold text-slate-700">नमस्ते</span>
        <span class="text-[11px] text-slate-500">Hello</span>
      </div>
      <div onclick="quickSearch('धन्यवाद')" class="flex items-center justify-between p-3 bg-slate-50 rounded-xl cursor-pointer hover:bg-slate-100 transition-colors">
        <span class="text-[13px] font-semibold text-slate-700">धन्यवाद</span>
        <span class="text-[11px] text-slate-500">Thank you</span>
      </div>
      <div onclick="quickSearch('प्रेम')" class="flex items-center justify-between p-3 bg-slate-50 rounded-xl cursor-pointer hover:bg-slate-100 transition-colors">
        <span class="text-[13px] font-semibold text-slate-700">प्रेम</span>
        <span class="text-[11px] text-slate-500">Love</span>
      </div>
      <div onclick="quickSearch('शान्ति')" class="flex items-center justify-between p-3 bg-slate-50 rounded-xl cursor-pointer hover:bg-slate-100 transition-colors">
        <span class="text-[13px] font-semibold text-slate-700">शान्ति</span>
        <span class="text-[11px] text-slate-500">Peace</span>
      </div>
      <div onclick="quickSearch('सफलता')" class="flex items-center justify-between p-3 bg-slate-50 rounded-xl cursor-pointer hover:bg-slate-100 transition-colors">
        <span class="text-[13px] font-semibold text-slate-700">सफलता</span>
        <span class="text-[11px] text-slate-500">Success</span>
      </div>
    </div>
  </div>
</section>

<!-- Info Note -->
<section class="px-4 mb-4">
  <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
    <div class="flex items-start gap-3">
      <i data-lucide="info" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
      <div class="text-[11px] text-blue-800">
        <p class="font-semibold mb-1">About Dictionary</p>
        <p>This dictionary contains common Nepali-English word pairs. For comprehensive definitions, please use a full dictionary service.</p>
      </div>
    </div>
  </div>
</section>

<div class="pb-4"></div>
</main>

<script>
(function(){
  var currentLang = 'ne-en';
  var recentSearches = [];
  
  // Dictionary data
  var dictionary = {
    'ne-en': {
      'नमस्ते': { word: 'नमस्ते', type: 'noun', meanings: ['Hello', 'Greetings', 'Salutation'] },
      'धन्यवाद': { word: 'धन्यवाद', type: 'noun', meanings: ['Thank you', 'Gratitude', 'Thanks'] },
      'प्रेम': { word: 'प्रेम', type: 'noun', meanings: ['Love', 'Affection', 'Romance'] },
      'शान्ति': { word: 'शान्ति', type: 'noun', meanings: ['Peace', 'Tranquility', 'Calm'] },
      'सफलता': { word: 'सफलता', type: 'noun', meanings: ['Success', 'Achievement', 'Victory'] },
      'खुशी': { word: 'खुशी', type: 'adjective', meanings: ['Happy', 'Joyful', 'Glad'] },
      'दुःख': { word: 'दुःख', type: 'noun', meanings: ['Sadness', 'Sorrow', 'Grief'] },
      'समय': { word: 'समय', type: 'noun', meanings: ['Time', 'Period', 'Duration'] },
      'घर': { word: 'घर', type: 'noun', meanings: ['House', 'Home', 'Residence'] },
      'पानी': { word: 'पानी', type: 'noun', meanings: ['Water', 'Aqua', 'Liquid'] },
      'आकाश': { word: 'आकाश', type: 'noun', meanings: ['Sky', 'Heaven', 'Space'] },
      'धरती': { word: 'धरती', type: 'noun', meanings: ['Earth', 'Ground', 'Soil'] },
      'सूर्य': { word: 'सूर्य', type: 'noun', meanings: ['Sun', 'Solar', 'Day star'] },
      'चन्द्रमा': { word: 'चन्द्रमा', type: 'noun', meanings: ['Moon', 'Lunar', 'Satellite'] },
      'वन': { word: 'वन', type: 'noun', meanings: ['Forest', 'Woods', 'Jungle'] },
      'नदी': { word: 'नदी', type: 'noun', meanings: ['River', 'Stream', 'Waterway'] },
      'पहाड': { word: 'पहाड', type: 'noun', meanings: ['Mountain', 'Hill', 'Peak'] },
      'समुद्र': { word: 'समुद्र', type: 'noun', meanings: ['Sea', 'Ocean', 'Marine'] },
      'फूल': { word: 'फूल', type: 'noun', meanings: ['Flower', 'Blossom', 'Bloom'] },
      'फल': { word: 'फल', type: 'noun', meanings: ['Fruit', 'Product', 'Result'] },
    },
    'en-ne': {
      'hello': { word: 'Hello', type: 'noun', meanings: ['नमस्ते', 'अभिवादन', 'सलाम'] },
      'thank you': { word: 'Thank you', type: 'noun', meanings: ['धन्यवाद', 'कृतज्ञता', 'धन्यवाद दिनु'] },
      'love': { word: 'Love', type: 'noun', meanings: ['प्रेम', 'माया', 'स्नेह'] },
      'peace': { word: 'Peace', type: 'noun', meanings: ['शान्ति', 'शान्तिपूर्ण', 'अमन'] },
      'success': { word: 'Success', type: 'noun', meanings: ['सफलता', 'उपलब्धि', 'विजय'] },
      'happy': { word: 'Happy', type: 'adjective', meanings: ['खुशी', 'प्रसन्न', 'आनन्दित'] },
      'sad': { word: 'Sad', type: 'adjective', meanings: ['दुःखी', 'उदास', 'खिन्न'] },
      'time': { word: 'Time', type: 'noun', meanings: ['समय', 'अवधि', 'समयकाल'] },
      'house': { word: 'House', type: 'noun', meanings: ['घर', 'घरटोल', 'निवास'] },
      'water': { word: 'Water', type: 'noun', meanings: ['पानी', 'जल', 'अविर'] },
      'sky': { word: 'Sky', type: 'noun', meanings: ['आकाश', 'गगन', 'अन्तरिक्ष'] },
      'earth': { word: 'Earth', type: 'noun', meanings: ['धरती', 'पृथ्वी', 'भूमि'] },
      'sun': { word: 'Sun', type: 'noun', meanings: ['सूर्य', 'दिनको तारा', 'सूर्यदेव'] },
      'moon': { word: 'Moon', type: 'noun', meanings: ['चन्द्रमा', 'चन्द्र', 'चाँद'] },
      'forest': { word: 'Forest', type: 'noun', meanings: ['वन', 'जंगल', 'वनस्पति'] },
      'river': { word: 'River', type: 'noun', meanings: ['नदी', 'खोला', 'सरिता'] },
      'mountain': { word: 'Mountain', type: 'noun', meanings: ['पहाड', 'हिमाल', 'गिरि'] },
      'sea': { word: 'Sea', type: 'noun', meanings: ['समुद्र', 'सागर', 'जलाशय'] },
      'flower': { word: 'Flower', type: 'noun', meanings: ['फूल', 'पुष्प', 'कुसुम'] },
      'fruit': { word: 'Fruit', type: 'noun', meanings: ['फल', 'फलफूल', 'उत्पादन'] },
    }
  };
  
  window.setLang = function(lang){
    currentLang = lang;
    
    var neEnBtn = document.getElementById('ne-en-btn');
    var enNeBtn = document.getElementById('en-ne-btn');
    
    if(lang === 'ne-en'){
      neEnBtn.classList.remove('bg-slate-100', 'text-slate-600', 'border-slate-200');
      neEnBtn.classList.add('bg-indigo-100', 'text-indigo-700', 'border-indigo-200');
      enNeBtn.classList.remove('bg-indigo-100', 'text-indigo-700', 'border-indigo-200');
      enNeBtn.classList.add('bg-slate-100', 'text-slate-600', 'border-slate-200');
    } else {
      enNeBtn.classList.remove('bg-slate-100', 'text-slate-600', 'border-slate-200');
      enNeBtn.classList.add('bg-indigo-100', 'text-indigo-700', 'border-indigo-200');
      neEnBtn.classList.remove('bg-indigo-100', 'text-indigo-700', 'border-indigo-200');
      neEnBtn.classList.add('bg-slate-100', 'text-slate-600', 'border-slate-200');
    }
  };
  
  window.searchWord = function(){
    var input = document.getElementById('search-input').value.trim();
    if(!input){
      alert('Please enter a word to search');
      return;
    }
    
    var dict = dictionary[currentLang];
    var lowerInput = input.toLowerCase();
    
    // Search in dictionary
    var result = null;
    for(var word in dict){
      if(word.toLowerCase() === lowerInput){
        result = dict[word];
        break;
      }
    }
    
    if(result){
      displayResult(result);
      addToRecent(input);
    } else {
      // Try partial match
      for(var word in dict){
        if(word.toLowerCase().includes(lowerInput) || lowerInput.includes(word.toLowerCase())){
          result = dict[word];
          displayResult(result);
          addToRecent(input);
          return;
        }
      }
      
      alert('Word not found in dictionary');
    }
  };
  
  function displayResult(result){
    document.getElementById('word-text').textContent = result.word;
    document.getElementById('word-type').textContent = result.type;
    
    var meaningsHTML = result.meanings.map(function(m){
      return '<div class="flex items-center gap-2 p-2 bg-slate-50 rounded-lg">' +
        '<i data-lucide="chevron-right" class="w-4 h-4 text-indigo-600"></i>' +
        '<span class="text-[13px] text-slate-700">' + m + '</span>' +
      '</div>';
    }).join('');
    
    document.getElementById('meanings').innerHTML = meaningsHTML;
    document.getElementById('result-section').classList.add('show');
    
    if(window.lucide) lucide.createIcons();
  }
  
  window.playPronunciation = function(){
    var word = document.getElementById('word-text').textContent;
    if('speechSynthesis' in window){
      window.speechSynthesis.cancel(); // Cancel any ongoing speech
      var utterance = new SpeechSynthesisUtterance(word);
      utterance.lang = currentLang === 'ne-en' ? 'ne-NP' : 'en-US';
      utterance.rate = 1;
      utterance.pitch = 1;
      window.speechSynthesis.speak(utterance);
    } else {
      alert('Text-to-speech not supported');
    }
  };
  
  window.quickSearch = function(word){
    document.getElementById('search-input').value = word;
    searchWord();
  };
  
  function addToRecent(word){
    if(recentSearches.includes(word)) return;
    recentSearches.unshift(word);
    if(recentSearches.length > 5) recentSearches.pop();
    
    var container = document.getElementById('recent-searches');
    container.innerHTML = recentSearches.map(function(w){
      return '<button onclick="quickSearch(\'' + w + '\')" class="text-[11px] bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full font-semibold">' + w + '</button>';
    }).join('');
  }
  
  // Search on Enter key
  document.getElementById('search-input').addEventListener('keypress', function(e){
    if(e.key === 'Enter'){
      searchWord();
    }
  });
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
