<?php
/**
 * Quiz & Games - Educational games and quizzes
 * General knowledge quiz, brain teasers
 */
$pageTitle = 'Quiz & Games | आकाशवाणी';
$pageDesc  = 'Play educational quizzes and games - general knowledge, brain teasers.';
require_once __DIR__ . '/header.php';
?>
<main class="app-main">

<!-- Header -->
<section class="px-4 pt-4 pb-2">
  <div class="flex items-center gap-2 mb-1">
    <span class="w-9 h-9 rounded-xl bg-pink-500 text-white flex items-center justify-center flex-shrink-0">
      <i data-lucide="gamepad-2" class="w-5 h-5"></i>
    </span>
    <div>
      <h1 class="text-[18px] font-bold text-slate-900 leading-tight">Quiz & Games</h1>
      <p class="text-[11px] text-slate-500">ज्ञान परीक्षा र खेलहरू</p>
    </div>
  </div>
</section>

<!-- Game Selection -->
<section class="px-4 mb-4">
  <div class="grid grid-cols-2 gap-3">
    <div onclick="startQuiz('general')" class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-4 text-white cursor-pointer hover:scale-[1.02] transition-transform">
      <i data-lucide="brain" class="w-8 h-8 mb-2"></i>
      <p class="text-[14px] font-bold">General Knowledge</p>
      <p class="text-[10px] opacity-80">सामान्य ज्ञान</p>
    </div>
    <div onclick="startQuiz('nepal')" class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl p-4 text-white cursor-pointer hover:scale-[1.02] transition-transform">
      <i data-lucide="mountain" class="w-8 h-8 mb-2"></i>
      <p class="text-[14px] font-bold">Nepal Quiz</p>
      <p class="text-[10px] opacity-80">नेपाल बारे</p>
    </div>
    <div onclick="startQuiz('science')" class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-4 text-white cursor-pointer hover:scale-[1.02] transition-transform">
      <i data-lucide="flask-conical" class="w-8 h-8 mb-2"></i>
      <p class="text-[14px] font-bold">Science</p>
      <p class="text-[10px] opacity-80">विज्ञान</p>
    </div>
    <div onclick="startQuiz('history')" class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl p-4 text-white cursor-pointer hover:scale-[1.02] transition-transform">
      <i data-lucide="scroll" class="w-8 h-8 mb-2"></i>
      <p class="text-[14px] font-bold">History</p>
      <p class="text-[10px] opacity-80">इतिहास</p>
    </div>
  </div>
</section>

<!-- Quiz Section -->
<section class="px-4 mb-4" id="quiz-section" style="display:none">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-app p-4">
    
    <!-- Progress -->
    <div class="flex items-center justify-between mb-3">
      <p class="text-[12px] font-bold text-slate-600">Question <span id="current-q">1</span> of <span id="total-q">10</span></p>
      <p class="text-[12px] font-bold text-pink-600">Score: <span id="score">0</span></p>
    </div>
    
    <!-- Progress Bar -->
    <div class="w-full bg-slate-200 rounded-full h-2 mb-4">
      <div id="progress-bar" class="bg-pink-500 h-2 rounded-full transition-all" style="width: 10%"></div>
    </div>
    
    <!-- Question -->
    <div id="question-card">
      <p class="text-[15px] font-bold text-slate-900 mb-4" id="question-text"></p>
      <div id="options" class="space-y-2">
      </div>
    </div>
    
    <!-- Result -->
    <div id="result-card" style="display:none">
      <div class="text-center py-6">
        <p class="text-[48px] font-black text-pink-600" id="final-score">0</p>
        <p class="text-[14px] text-slate-600 mb-4">out of 10</p>
        <p class="text-[12px] text-slate-500 mb-4" id="result-message"></p>
        <button onclick="restartQuiz()" class="bg-pink-600 text-white font-bold py-3 px-6 rounded-xl">
          Play Again
        </button>
      </div>
    </div>
  </div>
</section>

<!-- High Scores -->
<section class="px-4 mb-4">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-app p-4">
    <p class="text-[12px] font-bold text-slate-600 uppercase tracking-wide mb-3">High Scores</p>
    <div class="space-y-2" id="high-scores">
      <div class="flex items-center justify-between p-2 bg-slate-50 rounded-lg">
        <span class="text-[12px] font-semibold text-slate-700">General Knowledge</span>
        <span class="text-[11px] text-pink-600 font-bold" id="high-general">0</span>
      </div>
      <div class="flex items-center justify-between p-2 bg-slate-50 rounded-lg">
        <span class="text-[12px] font-semibold text-slate-700">Nepal Quiz</span>
        <span class="text-[11px] text-pink-600 font-bold" id="high-nepal">0</span>
      </div>
      <div class="flex items-center justify-between p-2 bg-slate-50 rounded-lg">
        <span class="text-[12px] font-semibold text-slate-700">Science</span>
        <span class="text-[11px] text-pink-600 font-bold" id="high-science">0</span>
      </div>
      <div class="flex items-center justify-between p-2 bg-slate-50 rounded-lg">
        <span class="text-[12px] font-semibold text-slate-700">History</span>
        <span class="text-[11px] text-pink-600 font-bold" id="high-history">0</span>
      </div>
    </div>
  </div>
</section>

<div class="pb-4"></div>
</main>

<script>
(function(){
  var currentQuiz = null;
  var currentQuestion = 0;
  var score = 0;
  var questions = [];
  
  var quizData = {
    general: [
      { q: 'What is the capital of France?', options: ['London', 'Berlin', 'Paris', 'Madrid'], answer: 2 },
      { q: 'Which planet is known as the Red Planet?', options: ['Venus', 'Mars', 'Jupiter', 'Saturn'], answer: 1 },
      { q: 'What is the largest ocean on Earth?', options: ['Atlantic', 'Indian', 'Arctic', 'Pacific'], answer: 3 },
      { q: 'Who painted the Mona Lisa?', options: ['Van Gogh', 'Picasso', 'Da Vinci', 'Michelangelo'], answer: 2 },
      { q: 'What is the chemical symbol for gold?', options: ['Go', 'Gd', 'Au', 'Ag'], answer: 2 },
      { q: 'How many continents are there?', options: ['5', '6', '7', '8'], answer: 2 },
      { q: 'What is the hardest natural substance?', options: ['Gold', 'Iron', 'Diamond', 'Platinum'], answer: 2 },
      { q: 'Which country has the largest population?', options: ['USA', 'India', 'China', 'Russia'], answer: 2 },
      { q: 'What is the speed of light?', options: ['300,000 km/s', '150,000 km/s', '500,000 km/s', '100,000 km/s'], answer: 0 },
      { q: 'Who discovered penicillin?', options: ['Curie', 'Fleming', 'Pasteur', 'Darwin'], answer: 1 },
    ],
    nepal: [
      { q: 'What is the capital of Nepal?', options: ['Pokhara', 'Lalitpur', 'Kathmandu', 'Bhaktapur'], answer: 2 },
      { q: 'Which is the highest mountain in Nepal?', options: ['K2', 'Kangchenjunga', 'Everest', 'Lhotse'], answer: 2 },
      { q: 'When was Nepal declared a republic?', options: ['2006', '2007', '2008', '2009'], answer: 1 },
      { q: 'Who is the first President of Nepal?', options: ['Ram Baran Yadav', 'Bidhya Devi Bhandari', 'Ram Chandra Paudel', 'None'], answer: 0 },
      { q: 'Which river is the longest in Nepal?', options: ['Karnali', 'Gandaki', 'Koshi', 'Bagmati'], answer: 0 },
      { q: 'What is the national flower of Nepal?', options: ['Rose', 'Laliguras', 'Sunflower', 'Lotus'], answer: 1 },
      { q: 'Which district is known as the Switzerland of Nepal?', options: ['Mustang', 'Solukhumbu', 'Dolpa', 'Mugu'], answer: 0 },
      { q: 'When did the unification of Nepal begin?', options: ['1743', '1768', '1814', '1846'], answer: 0 },
      { q: 'Who built the Pashupatinath Temple?', options: ['Prithvi Narayan Shah', 'Jayasthiti Malla', 'Bhakti Thapa', 'Amar Singh Thapa'], answer: 1 },
      { q: 'What is the national bird of Nepal?', options: ['Peacock', 'Danphe', 'Eagle', 'Sparrow'], answer: 1 },
    ],
    science: [
      { q: 'What is the powerhouse of the cell?', options: ['Nucleus', 'Mitochondria', 'Ribosome', 'Golgi body'], answer: 1 },
      { q: 'What gas do plants absorb from air?', options: ['Oxygen', 'Nitrogen', 'Carbon Dioxide', 'Hydrogen'], answer: 2 },
      { q: 'What is the chemical formula for water?', options: ['H2O', 'CO2', 'NaCl', 'O2'], answer: 0 },
      { q: 'How many bones are in the human body?', options: ['186', '206', '226', '246'], answer: 1 },
      { q: 'What is the largest organ in the human body?', options: ['Heart', 'Liver', 'Skin', 'Brain'], answer: 2 },
      { q: 'What is the boiling point of water?', options: ['90°C', '100°C', '110°C', '120°C'], answer: 1 },
      { q: 'Which planet has the most moons?', options: ['Jupiter', 'Saturn', 'Uranus', 'Neptune'], answer: 1 },
      { q: 'What is the speed of sound?', options: ['340 m/s', '343 m/s', '350 m/s', '360 m/s'], answer: 1 },
      { q: 'What vitamin is produced by sunlight?', options: ['Vitamin A', 'Vitamin C', 'Vitamin D', 'Vitamin E'], answer: 2 },
      { q: 'What is the smallest unit of life?', options: ['Cell', 'Atom', 'Molecule', 'Tissue'], answer: 0 },
    ],
    history: [
      { q: 'When did World War II end?', options: ['1943', '1944', '1945', '1946'], answer: 2 },
      { q: 'Who was the first President of USA?', options: ['Lincoln', 'Washington', 'Jefferson', 'Adams'], answer: 1 },
      { q: 'When did the Berlin Wall fall?', options: ['1987', '1988', '1989', '1990'], answer: 2 },
      { q: 'Who discovered America?', options: ['Columbus', 'Magellan', 'Vasco da Gama', 'Cook'], answer: 0 },
      { q: 'When did the French Revolution begin?', options: ['1776', '1789', '1799', '1804'], answer: 1 },
      { q: 'Who built the Great Wall of China?', options: ['Qin Dynasty', 'Han Dynasty', 'Ming Dynasty', 'Tang Dynasty'], answer: 0 },
      { q: 'When did the Titanic sink?', options: ['1910', '1911', '1912', '1913'], answer: 2 },
      { q: 'Who was the first man on the moon?', options: ['Gagarin', 'Armstrong', 'Aldrin', 'Collins'], answer: 1 },
      { q: 'When did India gain independence?', options: ['1945', '1946', '1947', '1948'], answer: 2 },
      { q: 'Who founded the Maurya Empire?', options: ['Ashoka', 'Chandragupta', 'Bindusara', 'Brihadratha'], answer: 1 },
    ]
  };
  
  window.startQuiz = function(category){
    currentQuiz = category;
    currentQuestion = 0;
    score = 0;
    questions = quizData[category];
    
    document.getElementById('quiz-section').style.display = 'block';
    document.getElementById('result-card').style.display = 'none';
    document.getElementById('question-card').style.display = 'block';
    
    document.getElementById('total-q').textContent = questions.length;
    document.getElementById('score').textContent = '0';
    
    showQuestion();
  };
  
  function showQuestion(){
    var q = questions[currentQuestion];
    document.getElementById('current-q').textContent = currentQuestion + 1;
    document.getElementById('question-text').textContent = q.q;
    
    var optionsHTML = q.options.map(function(opt, i){
      return '<button onclick="selectAnswer(' + i + ')" class="option-btn w-full text-left p-3 bg-slate-50 border border-slate-200 rounded-xl text-[13px] font-semibold text-slate-700 hover:bg-slate-100 transition-colors">' +
        opt +
      '</button>';
    }).join('');
    
    document.getElementById('options').innerHTML = optionsHTML;
    document.getElementById('progress-bar').style.width = ((currentQuestion + 1) / questions.length * 100) + '%';
  }
  
  window.selectAnswer = function(index){
    var q = questions[currentQuestion];
    var buttons = document.querySelectorAll('.option-btn');
    
    buttons.forEach(function(btn, i){
      btn.disabled = true;
      if(i === q.answer){
        btn.classList.remove('bg-slate-50', 'border-slate-200');
        btn.classList.add('bg-green-100', 'border-green-500', 'text-green-700');
      } else if(i === index && i !== q.answer){
        btn.classList.remove('bg-slate-50', 'border-slate-200');
        btn.classList.add('bg-red-100', 'border-red-500', 'text-red-700');
      }
    });
    
    if(index === q.answer){
      score++;
      document.getElementById('score').textContent = score;
    }
    
    setTimeout(function(){
      currentQuestion++;
      if(currentQuestion < questions.length){
        showQuestion();
      } else {
        showResult();
      }
    }, 1000);
  };
  
  function showResult(){
    document.getElementById('question-card').style.display = 'none';
    document.getElementById('result-card').style.display = 'block';
    document.getElementById('final-score').textContent = score;
    
    var message = '';
    if(score >= 9) message = 'Excellent! You are a genius!';
    else if(score >= 7) message = 'Great job! Keep learning!';
    else if(score >= 5) message = 'Good effort! Try again!';
    else message = 'Keep practicing! You can do better!';
    
    document.getElementById('result-message').textContent = message;
    
    // Save high score
    var highScore = localStorage.getItem('high_' + currentQuiz) || 0;
    if(score > highScore){
      localStorage.setItem('high_' + currentQuiz, score);
      updateHighScores();
    }
  }
  
  window.restartQuiz = function(){
    startQuiz(currentQuiz);
  };
  
  function updateHighScores(){
    document.getElementById('high-general').textContent = localStorage.getItem('high_general') || 0;
    document.getElementById('high-nepal').textContent = localStorage.getItem('high_nepal') || 0;
    document.getElementById('high-science').textContent = localStorage.getItem('high_science') || 0;
    document.getElementById('high-history').textContent = localStorage.getItem('high_history') || 0;
  }
  
  // Initialize high scores
  try{
    updateHighScores();
  } catch(e){
    console.error('Error loading high scores:', e);
  }
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
