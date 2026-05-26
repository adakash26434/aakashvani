<?php
/**
 * PDF Split Tool - Client-side processing with pdf-lib.js
 * Extract specific pages or page ranges from PDF
 */
require_once __DIR__ . '/header.php';
?>
<main class="app-main">
  <section class="px-4 pt-3">
    <div class="flex items-center gap-3 mb-3">
      <a href="/tools.php" class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center">
        <i data-lucide="arrow-left" class="w-4 h-4 text-slate-600"></i>
      </a>
      <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
        <i data-lucide="scissors" class="w-5 h-5"></i>
      </div>
      <div>
        <h1 class="ne">PDF छुट्याउने</h1>
        <p class="text-[11.5px] text-slate-500">PDF Split</p>
      </div>
    </div>

    <div class="bg-white rounded-2xl p-4 shadow-app mb-3">
      <p class="ne text-slate-700">
        PDF बाट विशेष page वा page range छुट्याउनुहोस्। <strong>Privacy:</strong> फाइलहरू browser मै process हुन्छ।
      </p>
    </div>

    <!-- File Upload -->
    <div class="bg-white rounded-2xl p-4 shadow-app mb-3">
      <div id="dropZone" class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center cursor-pointer hover:border-amber-500 hover:bg-amber-50 transition-colors">
        <i data-lucide="upload-cloud" class="w-10 h-10 mx-auto text-slate-400 mb-2"></i>
        <p class="ne text-slate-600 text-sm font-medium">PDF फाइल यहाँ थाप्नुहोस्</p>
        <p class="ne text-slate-400 text-xs mt-1">वा क्लिक गर्नुहोस्</p>
        <input type="file" id="pdfInput" accept=".pdf" class="hidden">
      </div>

      <!-- File Info -->
      <div id="fileInfo" class="hidden mt-3 p-3 bg-slate-50 rounded-lg">
        <div class="flex items-center gap-2">
          <i data-lucide="file-text" class="w-5 h-5 text-red-500"></i>
          <span id="fileName" class="text-sm text-slate-700 font-medium"></span>
          <span id="pageCount" class="text-xs text-slate-500 bg-white px-2 py-1 rounded-full"></span>
        </div>
      </div>
    </div>

    <!-- Split Options -->
    <div id="splitOptions" class="bg-white rounded-2xl p-4 shadow-app mb-3 hidden">
      <h3 class="ne text-sm font-bold text-slate-700 mb-3">Split विकल्पहरू:</h3>
      
      <!-- Option 1: Extract range -->
      <div class="mb-4">
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="radio" name="splitType" value="range" checked class="w-4 h-4 text-amber-600">
          <span class="ne text-sm text-slate-700">Page range छुट्याउने</span>
        </label>
        <div class="mt-2 ml-6 flex gap-2">
          <div class="flex-1">
            <label class="ne text-xs text-slate-500">सुरु page</label>
            <input type="number" id="startPage" min="1" value="1" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm">
          </div>
          <div class="flex-1">
            <label class="ne text-xs text-slate-500">अन्तिम page</label>
            <input type="number" id="endPage" min="1" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm">
          </div>
        </div>
      </div>

      <!-- Option 2: Split all pages -->
      <div class="mb-4">
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="radio" name="splitType" value="all" class="w-4 h-4 text-amber-600">
          <span class="ne text-sm text-slate-700">प्रत्येक page अलग PDF बनाउने</span>
        </label>
      </div>

      <!-- Option 3: Extract specific pages -->
      <div class="mb-4">
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="radio" name="splitType" value="specific" class="w-4 h-4 text-amber-600">
          <span class="ne text-sm text-slate-700">विशेष pages (comma separated)</span>
        </label>
        <div class="mt-2 ml-6">
          <input type="text" id="specificPages" placeholder="e.g., 1,3,5,7-10" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm">
          <p class="ne text-xs text-slate-400 mt-1">Example: 1,3,5 वा 1-5,8,10-12</p>
        </div>
      </div>

      <!-- Split Button -->
      <button id="splitBtn" class="w-full py-3 rounded-xl bg-amber-600 text-white font-bold text-sm ne">
        <i data-lucide="cut" class="w-4 h-4 inline-block mr-1"></i>
        PDF Split गर्नुहोस्
      </button>
    </div>

    <!-- Progress -->
    <div id="progress" class="hidden bg-white rounded-2xl p-4 shadow-app mb-3">
      <div class="flex items-center gap-2">
        <div class="w-5 h-5 border-2 border-amber-600 border-t-transparent rounded-full animate-spin"></div>
        <span class="ne text-sm text-slate-600">Processing...</span>
      </div>
    </div>

    <!-- Download Area -->
    <div id="downloadArea" class="hidden bg-white rounded-2xl p-4 shadow-app mb-3">
      <p class="ne text-sm font-bold text-slate-700 mb-2">Split भएका PDFहरू:</p>
      <div id="downloadList" class="space-y-2"></div>
    </div>
  </section>
</main>

<!-- pdf-lib: local first, CDN fallback (works offline / blocked-CDN ma pani) -->
<script src="/assets/js/vendor/pdf-lib.min.js" onerror="this.onerror=null;var s=document.createElement('script');s.src='https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js';document.head.appendChild(s);"></script>

<script>
(function(){
  const dropZone = document.getElementById('dropZone');
  const pdfInput = document.getElementById('pdfInput');
  const fileInfo = document.getElementById('fileInfo');
  const fileName = document.getElementById('fileName');
  const pageCount = document.getElementById('pageCount');
  const splitOptions = document.getElementById('splitOptions');
  const splitBtn = document.getElementById('splitBtn');
  const progress = document.getElementById('progress');
  const downloadArea = document.getElementById('downloadArea');
  const downloadList = document.getElementById('downloadList');
  const startPage = document.getElementById('startPage');
  const endPage = document.getElementById('endPage');

  let currentFile = null;
  let totalPages = 0;

  // Click to select
  dropZone.addEventListener('click', () => pdfInput.click());

  // File selection
  pdfInput.addEventListener('change', (e) => {
    if (e.target.files[0]) {
      loadPdf(e.target.files[0]);
    }
  });

  // Drag & drop
  dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-amber-500', 'bg-amber-50');
  });

  dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-amber-500', 'bg-amber-50');
  });

  dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-amber-500', 'bg-amber-50');
    const file = e.dataTransfer.files[0];
    if (file && file.type === 'application/pdf') {
      loadPdf(file);
    } else {
      alert('कृपया PDF फाइल मात्र छान्नुहोस्');
    }
  });

  async function loadPdf(file) {
    currentFile = file;
    fileName.textContent = file.name;
    fileInfo.classList.remove('hidden');

    try {
      const { PDFDocument } = PDFLib;
      const arrayBuffer = await file.arrayBuffer();
      const pdf = await PDFDocument.load(arrayBuffer);
      totalPages = pdf.getPageCount();
      
      pageCount.textContent = `${totalPages} pages`;
      endPage.max = totalPages;
      endPage.value = totalPages;
      
      splitOptions.classList.remove('hidden');
    } catch (error) {
      console.error('Load error:', error);
      alert('PDF load गर्न समस्या भयो');
    }
  }

  // Split PDF
  splitBtn.addEventListener('click', async () => {
    if (!currentFile) return;

    progress.classList.remove('hidden');
    splitBtn.disabled = true;
    downloadArea.classList.add('hidden');

    try {
      const { PDFDocument } = PDFLib;
      const arrayBuffer = await currentFile.arrayBuffer();
      const pdf = await PDFDocument.load(arrayBuffer);
      const splitType = document.querySelector('input[name="splitType"]:checked').value;
      
      const downloads = [];

      if (splitType === 'range') {
        const start = parseInt(startPage.value) || 1;
        const end = parseInt(endPage.value) || totalPages;
        
        const newPdf = await PDFDocument.create();
        const pages = await newPdf.copyPages(pdf, 
          Array.from({length: end - start + 1}, (_, i) => start - 1 + i)
        );
        pages.forEach(page => newPdf.addPage(page));
        
        const pdfBytes = await newPdf.save();
        downloads.push({
          name: `pages_${start}-${end}.pdf`,
          bytes: pdfBytes
        });

      } else if (splitType === 'all') {
        for (let i = 0; i < totalPages; i++) {
          const newPdf = await PDFDocument.create();
          const [page] = await newPdf.copyPages(pdf, [i]);
          newPdf.addPage(page);
          
          const pdfBytes = await newPdf.save();
          downloads.push({
            name: `page_${i + 1}.pdf`,
            bytes: pdfBytes
          });
        }

      } else if (splitType === 'specific') {
        const input = document.getElementById('specificPages').value;
        const pages = parsePageInput(input, totalPages);
        
        if (pages.length === 0) {
          alert('कृपया valid page numbers हाल्नुहोस्');
          progress.classList.add('hidden');
          splitBtn.disabled = false;
          return;
        }

        const newPdf = await PDFDocument.create();
        const copiedPages = await newPdf.copyPages(pdf, pages.map(p => p - 1));
        copiedPages.forEach(page => newPdf.addPage(page));
        
        const pdfBytes = await newPdf.save();
        downloads.push({
          name: `selected_pages.pdf`,
          bytes: pdfBytes
        });
      }

      // Render downloads
      downloadList.innerHTML = '';
      downloads.forEach((item, index) => {
        const blob = new Blob([item.bytes], { type: 'application/pdf' });
        const url = URL.createObjectURL(blob);
        
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 p-2 bg-slate-50 rounded-lg';
        div.innerHTML = `
          <i data-lucide="file-text" class="w-4 h-4 text-red-500"></i>
          <span class="flex-1 text-sm text-slate-700">${item.name}</span>
          <a href="${url}" download="${item.name}" class="px-3 py-1.5 bg-amber-600 text-white text-xs font-bold rounded-lg hover:bg-amber-700">
            Download
          </a>
        `;
        downloadList.appendChild(div);
      });

      progress.classList.add('hidden');
      downloadArea.classList.remove('hidden');

      if (window.lucide) lucide.createIcons();

    } catch (error) {
      console.error('Split error:', error);
      alert('PDF split गर्न समस्या भयो। कृपया फेरि प्रयास गर्नुहोस्।');
      progress.classList.add('hidden');
    }
    
    splitBtn.disabled = false;
  });

  function parsePageInput(input, maxPages) {
    const pages = new Set();
    const parts = input.split(',');
    
    for (const part of parts) {
      const trimmed = part.trim();
      if (trimmed.includes('-')) {
        const [start, end] = trimmed.split('-').map(n => parseInt(n.trim()));
        if (!isNaN(start) && !isNaN(end)) {
          for (let i = start; i <= end && i <= maxPages; i++) {
            if (i >= 1) pages.add(i);
          }
        }
      } else {
        const num = parseInt(trimmed);
        if (!isNaN(num) && num >= 1 && num <= maxPages) {
          pages.add(num);
        }
      }
    }
    
    return Array.from(pages).sort((a, b) => a - b);
  }

  if (window.lucide) lucide.createIcons();
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
