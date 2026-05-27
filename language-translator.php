<?php
/**
 * Language Translator - Translate text between languages
 * Uses MyMemory Translation API (free)
 */
$pageTitle = 'Language Translator | आकाशवाणी';
$pageDesc  = 'Translate text between Nepali, English, Hindi, and other languages.';
require_once __DIR__ . '/header.php';
?>
<main class="app-main lt-wrap">

<!-- Header -->
<section class="lt-hero">
  <h1><i data-lucide="languages" class="w-6 h-6"></i>Language Translator</h1>
  <p>भाषा अनुवाद गर्नुस्</p>
</section>

<!-- Translator Card -->
<section class="lt-card">
    
    <!-- From Language -->
    <div class="mb-3">
      <label class="text-[12px] font-bold text-slate-600 uppercase tracking-wide mb-2 block">From</label>
      <select id="from-lang" class="w-full text-[13px] bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-purple-400 font-semibold">
        <option value="ne">नेपाली (Nepali)</option>
        <option value="en" selected>English</option>
        <option value="hi">हिन्दी (Hindi)</option>
        <option value="es">Español (Spanish)</option>
        <option value="fr">Français (French)</option>
        <option value="de">Deutsch (German)</option>
        <option value="ja">日本語 (Japanese)</option>
        <option value="ko">한국어 (Korean)</option>
        <option value="zh">中文 (Chinese)</option>
        <option value="ar">العربية (Arabic)</option>
        <option value="bn">বাংলা (Bengali)</option>
        <option value="ta">தமிழ் (Tamil)</option>
      </select>
    </div>

    <!-- Swap Button -->
    <div class="flex justify-center mb-3">
      <button onclick="swapLanguages()" class="w-10 h-10 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center hover:bg-purple-100 transition-colors">
        <i data-lucide="arrow-down-up" class="w-5 h-5"></i>
      </button>
    </div>

    <!-- To Language -->
    <div class="mb-3">
      <label class="text-[12px] font-bold text-slate-600 uppercase tracking-wide mb-2 block">To</label>
      <select id="to-lang" class="w-full text-[13px] bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-purple-400 font-semibold">
        <option value="ne" selected>नेपाली (Nepali)</option>
        <option value="en">English</option>
        <option value="hi">हिन्दी (Hindi)</option>
        <option value="es">Español (Spanish)</option>
        <option value="fr">Français (French)</option>
        <option value="de">Deutsch (German)</option>
        <option value="ja">日本語 (Japanese)</option>
        <option value="ko">한국어 (Korean)</option>
        <option value="zh">中文 (Chinese)</option>
        <option value="ar">العربية (Arabic)</option>
        <option value="bn">বাংলা (Bengali)</option>
        <option value="ta">தமிழ் (Tamil)</option>
      </select>
    </div>

    <!-- Input Text -->
    <div class="mb-3">
      <label class="text-[11px] text-slate-600 mb-1 block">Enter text to translate</label>
      <textarea id="input-text" rows="4" placeholder="Type or paste text here..."
        class="w-full text-[13px] bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-purple-400 resize-none"></textarea>
      <div class="flex justify-between mt-1">
        <span class="text-[10px] text-slate-400" id="char-count">0 characters</span>
        <button onclick="clearText()" class="text-[10px] text-purple-600 font-semibold">Clear</button>
      </div>
    </div>

    <!-- Translate Button -->
    <button onclick="translateText()" id="translate-btn"
      class="w-full bg-purple-600 text-white font-bold py-3 rounded-xl shadow-app text-[15px] flex items-center justify-center gap-2 active:scale-[.98] transition-transform">
      <i data-lucide="languages" class="w-5 h-5"></i>
      Translate
    </button>
  </div>
</section>

<!-- Result Card -->
<section class="lt-card lt-result" id="result-section">
    <div class="flex items-center justify-between mb-3">
      <p class="text-[12px] font-bold text-slate-600 uppercase tracking-wide">Translation</p>
      <button onclick="copyResult()" class="text-[10px] text-purple-600 font-semibold flex items-center gap-1">
        <i data-lucide="copy" class="w-3 h-3"></i> Copy
      </button>
    </div>
    <div id="result-text" class="text-[14px] text-slate-800 leading-relaxed min-h-[80px] bg-slate-50 rounded-xl p-3">
    </div>
  </div>
</section>

<!-- Common Phrases -->
<section class="px-4 mb-4">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-app p-4">
    <p class="text-[12px] font-bold text-slate-600 uppercase tracking-wide mb-3">Common Phrases</p>
    <div class="space-y-2">
      <div onclick="quickTranslate('Hello','en','ne')" class="flex items-center justify-between p-3 bg-slate-50 rounded-xl cursor-pointer hover:bg-slate-100 transition-colors">
        <span class="text-[13px] font-semibold text-slate-700">Hello → नमस्ते</span>
        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
      </div>
      <div onclick="quickTranslate('Thank you','en','ne')" class="flex items-center justify-between p-3 bg-slate-50 rounded-xl cursor-pointer hover:bg-slate-100 transition-colors">
        <span class="text-[13px] font-semibold text-slate-700">Thank you → धन्यवाद</span>
        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
      </div>
      <div onclick="quickTranslate('How are you?','en','ne')" class="flex items-center justify-between p-3 bg-slate-50 rounded-xl cursor-pointer hover:bg-slate-100 transition-colors">
        <span class="text-[13px] font-semibold text-slate-700">How are you? → कस्तो छ?</span>
        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
      </div>
      <div onclick="quickTranslate('Good morning','en','ne')" class="flex items-center justify-between p-3 bg-slate-50 rounded-xl cursor-pointer hover:bg-slate-100 transition-colors">
        <span class="text-[13px] font-semibold text-slate-700">Good morning → शुभ प्रभात</span>
        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
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
        <p class="font-semibold mb-1">About Translation</p>
        <p>Translation is powered by MyMemory Translation API. For official documents, please use professional translation services.</p>
      </div>
    </div>
  </div>
</section>

<div class="pb-4"></div>
</main>

<script>
(function(){
  window.swapLanguages = function(){
    var fromSelect = document.getElementById('from-lang');
    var toSelect = document.getElementById('to-lang');
    var temp = fromSelect.value;
    fromSelect.value = toSelect.value;
    toSelect.value = temp;
  };
  
  window.clearText = function(){
    document.getElementById('input-text').value = '';
    document.getElementById('char-count').textContent = '0 characters';
    document.getElementById('result-section').classList.remove('show');
  };
  
  window.translateText = function(){
    var fromLang = document.getElementById('from-lang').value;
    var toLang = document.getElementById('to-lang').value;
    var text = document.getElementById('input-text').value.trim();
    
    if(!text){
      alert('Please enter text to translate');
      return;
    }
    
    var btn = document.getElementById('translate-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin inline-block"></span> Translating...';
    
    // Use MyMemory Translation API (free)
    fetch('https://api.mymemory.translated.net/get?q=' + encodeURIComponent(text) + '&langpair=' + fromLang + '|' + toLang)
      .then(function(r){ return r.json(); })
      .then(function(data){
        if(data && data.responseData && data.responseData.translatedText){
          showResult(data.responseData.translatedText);
        } else {
          showResult('Translation failed. Please try again.');
        }
      })
      .catch(function(){
        // Fallback to basic dictionary for common phrases
        var result = basicTranslate(text, fromLang, toLang);
        showResult(result);
      })
      .finally(function(){
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="languages" class="w-5 h-5"></i> Translate';
        if(window.lucide) lucide.createIcons();
      });
  };
  
  function showResult(text){
    document.getElementById('result-text').textContent = text;
    document.getElementById('result-section').classList.add('show');
  }
  
  window.copyResult = function(){
    var text = document.getElementById('result-text').textContent;
    if(navigator.clipboard && navigator.clipboard.writeText){
      navigator.clipboard.writeText(text).then(function(){
        alert('Copied to clipboard!');
      }).catch(function(){
        alert('Failed to copy. Please copy manually.');
      });
    } else {
      // Fallback for older browsers
      var textarea = document.createElement('textarea');
      textarea.value = text;
      document.body.appendChild(textarea);
      textarea.select();
      try{
        document.execCommand('copy');
        alert('Copied to clipboard!');
      } catch(e){
        alert('Failed to copy. Please copy manually.');
      }
      document.body.removeChild(textarea);
    }
  };
  
  window.quickTranslate = function(text, from, to){
    document.getElementById('from-lang').value = from;
    document.getElementById('to-lang').value = to;
    document.getElementById('input-text').value = text;
    translateText();
  };
  
  // Basic dictionary for fallback
  function basicTranslate(text, from, to){
    var dictionary = {
      'en-ne': {
        'hello': 'नमस्ते',
        'thank you': 'धन्यवाद',
        'how are you': 'कस्तो छ?',
        'good morning': 'शुभ प्रभात',
        'good night': 'शुभ रात्री',
        'goodbye': 'बिदा',
        'yes': 'हो',
        'no': 'होइन',
        'please': 'कृपया',
        'sorry': 'माफ गर्नुहोस्'
      },
      'ne-en': {
        'नमस्ते': 'Hello',
        'धन्यवाद': 'Thank you',
        'कस्तो छ': 'How are you?',
        'शुभ प्रभात': 'Good morning',
        'शुभ रात्री': 'Good night',
        'बिदा': 'Goodbye',
        'हो': 'Yes',
        'होइन': 'No',
        'कृपया': 'Please',
        'माफ गर्नुहोस्': 'Sorry'
      }
    };
    
    var key = from + '-' + to;
    var lowerText = text.toLowerCase().trim();
    
    if(dictionary[key] && dictionary[key][lowerText]){
      return dictionary[key][lowerText];
    }
    
    return 'Translation not available in offline mode. Please check your internet connection.';
  }
  
  // Character count
  document.getElementById('input-text').addEventListener('input', function(){
    var count = this.value.length;
    document.getElementById('char-count').textContent = count + ' characters';
  });
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
