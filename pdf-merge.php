<?php
/**
 * PDF Merge Tool - Client-side processing with pdf-lib.js
 * Privacy-first: Files never leave the browser
 */
require_once __DIR__ . '/header.php';
?>
<main class="app-main">
  <section class="px-4 pt-3">
    <div class="flex items-center gap-3 mb-3">
      <a href="/tools.php" class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center">
        <i data-lucide="arrow-left" class="w-4 h-4 text-slate-600"></i>
      </a>
      <div class="w-11 h-11 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center">
        <i data-lucide="files" class="w-5 h-5"></i>
      </div>
      <div>
        <h1 class="ne">PDF जोड्ने</h1>
        <p class="text-[11.5px] text-slate-500">PDF Merge</p>
      </div>
    </div>

    <div class="bg-white rounded-2xl p-4 shadow-app mb-3">
      <p class="ne text-slate-700">
        धेरै PDF फाइलहरू एउटै PDF मा जोड्नुहोस्। <strong>Privacy:</strong> फाइलहरू तपाईंको browser मै process हुन्छ, server मा अपलोड हुँदैन।
      </p>
    </div>

    <!-- File Upload Area -->
    <div class="bg-white rounded-2xl p-4 shadow-app mb-3">
      <div id="dropZone" class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center cursor-pointer hover:border-sky-500 hover:bg-sky-50 transition-colors">
        <i data-lucide="upload-cloud" class="w-10 h-10 mx-auto text-slate-400 mb-2"></i>
        <p class="ne text-slate-600 text-sm font-medium">PDF फाइलहरू यहाँ थाप्नुहोस्</p>
        <p class="ne text-slate-400 text-xs mt-1">वा क्लिक गर्नुहोस्</p>
        <input type="file" id="pdfInput" accept=".pdf" multiple class="hidden">
      </div>

      <!-- File List -->
      <div id="fileList" class="mt-3 space-y-2 hidden">
        <p class="ne text-xs font-semibold text-slate-500 mb-2">फाइलहरू (drag to reorder):</p>
        <div id="fileItems" class="space-y-2"></div>
      </div>

      <!-- Merge Button -->
      <button id="mergeBtn" class="w-full mt-4 py-3 rounded-xl bg-sky-600 text-white font-bold text-sm ne hidden">
        <i data-lucide="combine" class="w-4 h-4 inline-block mr-1"></i>
        PDF जोड्नुहोस्
      </button>

      <!-- Progress -->
      <div id="progress" class="hidden mt-4">
        <div class="flex items-center gap-2">
          <div class="w-5 h-5 border-2 border-sky-600 border-t-transparent rounded-full animate-spin"></div>
          <span class="ne text-sm text-slate-600">Processing...</span>
        </div>
      </div>

      <!-- Download Button -->
      <div id="downloadArea" class="hidden mt-4">
        <a id="downloadBtn" class="block w-full py-3 rounded-xl bg-emerald-600 text-white font-bold text-sm ne text-center">
          <i data-lucide="download" class="w-4 h-4 inline-block mr-1"></i>
          मर्ज भएको PDF डाउनलोड गर्नुहोस्
        </a>
      </div>
    </div>

    <!-- Instructions -->
    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200">
      <h3 class="ne text-sm font-bold text-slate-700 mb-2">कसरी प्रयोग गर्ने:</h3>
      <ol class="ne text-xs text-slate-600 space-y-1 list-decimal list-inside">
        <li>PDF फाइलहरू select गर्नुहोस् (एकै पटक धेरै)</li>
        <li>फाइलहरूको क्रम arrange गर्नुहोस् (drag गरेर)</li>
        <li>"PDF जोड्नुहोस्" button थिच्नुहोस्</li>
        <li>Download गर्नुहोस्</li>
      </ol>
    </div>
  </section>
</main>

<!-- pdf-lib.js from CDN -->
<!-- pdf-lib: local first, CDN fallback (works offline / blocked-CDN ma pani) -->
<script src="/assets/js/vendor/pdf-lib.min.js" onerror="this.onerror=null;var s=document.createElement('script');s.src='https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js';document.head.appendChild(s);"></script>

<script>
(function(){
  const dropZone = document.getElementById('dropZone');
  const pdfInput = document.getElementById('pdfInput');
  const fileList = document.getElementById('fileList');
  const fileItems = document.getElementById('fileItems');
  const mergeBtn = document.getElementById('mergeBtn');
  const progress = document.getElementById('progress');
  const downloadArea = document.getElementById('downloadArea');
  const downloadBtn = document.getElementById('downloadBtn');

  let files = [];

  // Click to select
  dropZone.addEventListener('click', () => pdfInput.click());

  // File selection
  pdfInput.addEventListener('change', (e) => {
    handleFiles(Array.from(e.target.files));
  });

  // Drag & drop
  dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-sky-500', 'bg-sky-50');
  });

  dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-sky-500', 'bg-sky-50');
  });

  dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-sky-500', 'bg-sky-50');
    handleFiles(Array.from(e.dataTransfer.files).filter(f => f.type === 'application/pdf'));
  });

  function handleFiles(newFiles) {
    const pdfFiles = newFiles.filter(f => f.type === 'application/pdf');
    if (pdfFiles.length === 0) {
      alert('कृपया PDF फाइलहरू मात्र छान्नुहोस्');
      return;
    }
    files = [...files, ...pdfFiles];
    renderFileList();
  }

  function renderFileList() {
    if (files.length === 0) {
      fileList.classList.add('hidden');
      mergeBtn.classList.add('hidden');
      return;
    }

    fileList.classList.remove('hidden');
    mergeBtn.classList.remove('hidden');
    fileItems.innerHTML = '';

    files.forEach((file, index) => {
      const div = document.createElement('div');
      div.className = 'flex items-center gap-2 bg-slate-50 p-2 rounded-lg border border-slate-200';
      div.draggable = true;
      div.dataset.index = index;
      div.innerHTML = `
        <i data-lucide="grip-vertical" class="w-4 h-4 text-slate-400 cursor-move"></i>
        <i data-lucide="file-text" class="w-4 h-4 text-red-500"></i>
        <span class="flex-1 text-sm text-slate-700 truncate">${file.name}</span>
        <span class="text-xs text-slate-400">${(file.size/1024).toFixed(1)} KB</span>
        <button onclick="removeFile(${index})" class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center hover:bg-red-200">
          <i data-lucide="x" class="w-3 h-3"></i>
        </button>
      `;

      // Drag handlers
      div.addEventListener('dragstart', (e) => {
        e.dataTransfer.setData('text/plain', index);
        div.classList.add('opacity-50');
      });

      div.addEventListener('dragend', () => {
        div.classList.remove('opacity-50');
      });

      div.addEventListener('dragover', (e) => {
        e.preventDefault();
        const draggingIndex = parseInt(e.dataTransfer.getData('text/plain'));
        const targetIndex = parseInt(div.dataset.index);
        if (draggingIndex !== targetIndex) {
          const temp = files[draggingIndex];
          files[draggingIndex] = files[targetIndex];
          files[targetIndex] = temp;
          renderFileList();
        }
      });

      fileItems.appendChild(div);
    });

    if (window.lucide) lucide.createIcons();
  }

  window.removeFile = function(index) {
    files.splice(index, 1);
    renderFileList();
  };

  // Merge PDFs
  mergeBtn.addEventListener('click', async () => {
    if (files.length < 2) {
      alert('कम्तीमा २ वटा PDF फाइलहरू आवश्यक छन्');
      return;
    }

    progress.classList.remove('hidden');
    mergeBtn.disabled = true;

    try {
      const { PDFDocument } = PDFLib;
      const mergedPdf = await PDFDocument.create();

      for (const file of files) {
        const arrayBuffer = await file.arrayBuffer();
        const pdf = await PDFDocument.load(arrayBuffer);
        const pages = await mergedPdf.copyPages(pdf, pdf.getPageIndices());
        pages.forEach(page => mergedPdf.addPage(page));
      }

      const mergedPdfBytes = await mergedPdf.save();
      const blob = new Blob([mergedPdfBytes], { type: 'application/pdf' });
      const url = URL.createObjectURL(blob);

      downloadBtn.href = url;
      downloadBtn.download = `merged_${Date.now()}.pdf`;
      
      progress.classList.add('hidden');
      downloadArea.classList.remove('hidden');
      mergeBtn.classList.add('hidden');

    } catch (error) {
      console.error('Merge error:', error);
      alert('PDF merge गर्न समस्या भयो। कृपया फेरि प्रयास गर्नुहोस्।');
      progress.classList.add('hidden');
      mergeBtn.disabled = false;
    }
  });

  if (window.lucide) lucide.createIcons();
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
