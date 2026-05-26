<?php
/**
 * आकाशवाणी — Nepali Stories Page
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$lang = siteLang();
$t = fn($ne, $en) => $lang === 'ne' ? $ne : $en;

$pageTitle = $t('नेपाली कथाहरू — आकाशवाणी', 'Nepali Stories — Aakashvani');
$pageDesc = $t('नेपाली लोक कथा, नैतिक कथा, रहस्य कथा र शैक्षिक कथाहरू।', 'Nepali folk tales, moral stories, mystery stories and educational stories.');

include __DIR__ . '/header.php';
?>

<!-- Page Header -->
<section class="mt-3">
  <div class="app-card p-4 bg-gradient-to-br from-purple-900 to-purple-700 text-white">
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
        <i data-lucide="book-open" class="w-6 h-6"></i>
      </div>
      <div>
        <h1 class="text-xl font-bold ne"><?= $t('नेपाली कथाहरू', 'Nepali Stories') ?></h1>
        <p class="text-sm opacity-80"><?= $t('मनोरञ्जनको लागि', 'For Entertainment') ?></p>
      </div>
    </div>
  </div>
</section>

<!-- Category Filter -->
<div class="flex gap-2 mt-4 overflow-x-auto no-sb px-1" id="category-filters">
  <button onclick="filterStories('all')" class="filter-btn active flex-shrink-0 px-4 py-2 rounded-full text-sm font-semibold bg-purple-100 text-purple-700 border border-purple-200">
    <?= $t('सबै', 'All') ?>
  </button>
  <button onclick="filterStories('moral')" class="filter-btn flex-shrink-0 px-4 py-2 rounded-full text-sm font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200">
    <?= $t('नैतिक कथा', 'Moral') ?>
  </button>
  <button onclick="filterStories('mystery')" class="filter-btn flex-shrink-0 px-4 py-2 rounded-full text-sm font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200">
    <?= $t('रहस्य', 'Mystery') ?>
  </button>
  <button onclick="filterStories('educational')" class="filter-btn flex-shrink-0 px-4 py-2 rounded-full text-sm font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200">
    <?= $t('शैक्षिक', 'Educational') ?>
  </button>
  <button onclick="filterStories('adventure')" class="filter-btn flex-shrink-0 px-4 py-2 rounded-full text-sm font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200">
    <?= $t('साहसिक', 'Adventure') ?>
  </button>
  <button onclick="filterStories('historical')" class="filter-btn flex-shrink-0 px-4 py-2 rounded-full text-sm font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200">
    <?= $t('ऐतिहासिक', 'Historical') ?>
  </button>
</div>

<!-- Stories Grid -->
<div id="stories-container" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
  <!-- Stories will be loaded here -->
</div>

<!-- Loading State -->
<div id="loading" class="mt-4 text-center py-8">
  <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-purple-500 border-t-transparent"></div>
  <p class="text-sm text-slate-500 mt-2"><?= $t('लोड हुँदैछ...', 'Loading...') ?></p>
</div>

<!-- Story Modal -->
<div id="story-modal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
  <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
    <div class="p-6">
      <div class="flex justify-between items-start mb-4">
        <h2 id="modal-title" class="text-xl font-bold ne"></h2>
        <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600">
          <i data-lucide="x" class="w-6 h-6"></i>
        </button>
      </div>
      <div id="modal-image" class="w-full h-48 object-cover rounded-lg mb-4 hidden"></div>
      <div class="flex items-center gap-2 text-xs text-slate-500 mb-4">
        <span id="modal-category"></span>
        <span>•</span>
        <span id="modal-author"></span>
        <span>•</span>
        <span id="modal-reading-time"></span>
      </div>
      <div id="modal-content" class="text-slate-700 leading-relaxed ne"></div>
      <div class="mt-4 pt-4 border-t border-slate-100">
        <div class="flex flex-wrap gap-2" id="modal-tags"></div>
      </div>
    </div>
  </div>
</div>

<script>
let currentCategory = 'all';
let allStories = [];

// Load stories on page load
document.addEventListener('DOMContentLoaded', function() {
  loadStories();
});

function loadStories(category = 'all') {
  currentCategory = category;
  
  // Update active button
  document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.classList.remove('active', 'bg-purple-100', 'text-purple-700', 'border-purple-200');
    btn.classList.add('bg-slate-100', 'text-slate-600');
  });
  event?.target?.classList.add('active', 'bg-purple-100', 'text-purple-700', 'border-purple-200');
  event?.target?.classList.remove('bg-slate-100', 'text-slate-600');
  
  // Show loading
  document.getElementById('loading').classList.remove('hidden');
  document.getElementById('stories-container').innerHTML = '';
  
  // Fetch stories
  const url = category === 'all' 
    ? '/api/stories.php' 
    : `/api/stories.php?category=${category}`;
  
  fetch(url)
    .then(res => res.json())
    .then(data => {
      allStories = data.stories || [];
      renderStories(allStories);
      document.getElementById('loading').classList.add('hidden');
    })
    .catch(err => {
      console.error('Error loading stories:', err);
      document.getElementById('loading').classList.add('hidden');
    });
}

function filterStories(category) {
  loadStories(category);
}

function renderStories(stories) {
  const container = document.getElementById('stories-container');
  const lang = document.documentElement.lang || 'ne';
  
  container.innerHTML = stories.map(story => {
    if (story.source) return ''; // Skip source info
    
    const title = lang === 'ne' ? story.title : story.title_en;
    const category = lang === 'ne' ? story.category_ne : story.category;
    const excerpt = (lang === 'ne' ? story.content : story.content_en).substring(0, 100) + '...';
    
    return `
      <div class="app-card cursor-pointer hover:shadow-lg transition-shadow" onclick="openStory(${story.id})">
        <div class="relative">
          <img src="${story.image_url}" alt="${title}" class="w-full h-40 object-cover rounded-t-lg">
          <div class="absolute top-2 right-2 bg-purple-600 text-white text-xs px-2 py-1 rounded-full">
            ${category}
          </div>
        </div>
        <div class="p-4">
          <h3 class="font-bold text-slate-900 ne mb-2">${title}</h3>
          <p class="text-sm text-slate-600 ne mb-3">${excerpt}</p>
          <div class="flex items-center justify-between text-xs text-slate-500">
            <span><i data-lucide="clock" class="w-3 h-3 inline"></i> ${story.reading_time} min</span>
            <span><i data-lucide="eye" class="w-3 h-3 inline"></i> ${story.views}</span>
          </div>
        </div>
      </div>
    `;
  }).join('');
  
  // Reinitialize icons
  if (window.lucide) lucide.createIcons();
}

function openStory(id) {
  const story = allStories.find(s => s.id === id);
  if (!story) return;
  
  const lang = document.documentElement.lang || 'ne';
  const title = lang === 'ne' ? story.title : story.title_en;
  const content = lang === 'ne' ? story.content : story.content_en;
  const category = lang === 'ne' ? story.category_ne : story.category;
  const author = lang === 'ne' ? story.author : story.author_en;
  const tags = lang === 'ne' ? story.tags : story.tags_en;
  
  document.getElementById('modal-title').textContent = title;
  document.getElementById('modal-category').textContent = category;
  document.getElementById('modal-author').textContent = author;
  document.getElementById('modal-reading-time').textContent = story.reading_time + ' min read';
  document.getElementById('modal-content').textContent = content;
  
  const img = document.getElementById('modal-image');
  img.src = story.image_url;
  img.classList.remove('hidden');
  
  const tagsContainer = document.getElementById('modal-tags');
  tagsContainer.innerHTML = tags.map(tag => 
    `<span class="bg-slate-100 text-slate-600 text-xs px-2 py-1 rounded-full">${tag}</span>`
  ).join('');
  
  document.getElementById('story-modal').classList.remove('hidden');
  document.getElementById('story-modal').classList.add('flex');
}

function closeModal() {
  document.getElementById('story-modal').classList.add('hidden');
  document.getElementById('story-modal').classList.remove('flex');
}
</script>

<?php include __DIR__ . '/footer.php'; ?>
