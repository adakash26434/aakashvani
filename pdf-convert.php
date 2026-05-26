<?php
/**
 * Image to PDF Converter - Client-side processing with pdf-lib.js
 * Convert JPG, PNG, WEBP images to PDF
 */
require_once __DIR__ . '/header.php';
?>
<main class="app-main">
  <section class="px-4 pt-3">
    <div class="flex items-center gap-3 mb-3">
      <a href="/tools.php" class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center">
        <i data-lucide="arrow-left" class="w-4 h-4 text-slate-600"></i>
      </a>
      <div class="w-11 h-11 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center">
        <i data-lucide="file-type" class="w-5 h-5"></i>
      </div>
      <div>
        <h1 class="ne">Image → PDF</h1>
        <p class="text-[11.5px] text-slate-500">Photo to PDF</p>
      </div>
    </div>

    <div class="bg-white rounded-2xl p-4 shadow-app mb-3">
      <p class="ne text-slate-700">
        JPG, PNG, WEBP फोटोहरूलाई PDF मा बदल्नुहोस्। <strong>Privacy:</strong> फाइलहरू browser मै process हुन्छ।
      </p>
    </div>

    <!-- File Upload Area -->
    <div class="bg-white rounded-2xl p-4 shadow-app mb-3">
      <div id="dropZone" class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center cursor-pointer hover:border-rose-500 hover:bg-rose-50 transition-colors">
        <i data-lucide="image-plus" class="w-10 h-10 mx-auto text-slate-400 mb-2"></i>
        <p class="ne text-slate-600 text-sm font-medium">फोटोहरू यहाँ थाप्नुहोस्</p>
        <p class="ne text-slate-400 text-xs mt-1">JPG, PNG, WEBP supported</p>
        <input type="file" id="imageInput" accept="image/*" multiple class="hidden">
      </div>

      <!-- Preview Grid -->
      <div id="previewGrid" class="hidden mt-3">
        <p class="ne text-xs font-semibold text-slate-500 mb-2">फोटोहरू (drag to reorder):</p>
        <div id="imageItems" class="grid grid-cols-3 gap-2"></div>
      </div>

      <!-- Options -->
      <div id="options" class="hidden mt-4 space-y-3">
        <div>
          <label class="ne text-xs text-slate-500">Page size</label>
          <select id="pageSize" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm">
            <option value="fit">Fit to image (original size)</option>
            <option value="A4">A4 (standard)</option>
            <option value="letter">Letter</option>
          </select>
        </div>

        <div>
          <label class="ne text-xs text-slate-500">Orientation</label>
          <select id="orientation" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm">
            <option value="auto">Auto (based on image)</option>
            <option value="portrait">Portrait</option>
            <option value="landscape">Landscape</option>
          </select>
        </div>

        <div>
          <label class="ne text-xs text-slate-500">Quality</label>
          <select id="quality" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm">
            <option value="1.0">High (100%)</option>
            <option value="0.8">Medium (80%)</option>
            <option value="0.6">Low (60%)</option>
          </select>
        </div>
      </div>

      <!-- Convert Button -->
      <button id="convertBtn" class="w-full mt-4 py-3 rounded-xl bg-rose-600 text-white font-bold text-sm ne hidden">
        <i data-lucide="file-plus" class="w-4 h-4 inline-block mr-1"></i>
        PDF बनाउनुहोस्
      </button>

      <!-- Progress -->
      <div id="progress" class="hidden mt-4">
        <div class="flex items-center gap-2">
          <div class="w-5 h-5 border-2 border-rose-600 border-t-transparent rounded-full animate-spin"></div>
          <span class="ne text-sm text-slate-600">Converting...</span>
        </div>
      </div>

      <!-- Download -->
      <div id="downloadArea" class="hidden mt-4">
        <a id="downloadBtn" class="block w-full py-3 rounded-xl bg-emerald-600 text-white font-bold text-sm ne text-center">
          <i data-lucide="download" class="w-4 h-4 inline-block mr-1"></i>
          PDF डाउनलोड गर्नुहोस्
        </a>
      </div>
    </div>

    <!-- Instructions -->
    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200">
      <h3 class="ne text-sm font-bold text-slate-700 mb-2">कसरी प्रयोग गर्ने:</h3>
      <ol class="ne text-xs text-slate-600 space-y-1 list-decimal list-inside">
        <li>फोटोहरू select गर्नुहोस् (धेरै JPG/PNG)</li>
        <li>Page size र quality छान्नुहोस्</li>
        <li>"PDF बनाउनुहोस्" थिच्नुहोस्</li>
        <li>Download गर्नुहोस्</li>
      </ol>
    </div>
  </section>
</main>

<!-- pdf-lib: local first, CDN fallback (works offline / blocked-CDN ma pani) -->
<script src="/assets/js/vendor/pdf-lib.min.js" onerror="this.onerror=null;var s=document.createElement('script');s.src='https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js';document.head.appendChild(s);"></script>

<script>
(function(){
  const dropZone = document.getElementById('dropZone');
  const imageInput = document.getElementById('imageInput');
  const previewGrid = document.getElementById('previewGrid');
  const imageItems = document.getElementById('imageItems');
  const options = document.getElementById('options');
  const convertBtn = document.getElementById('convertBtn');
  const progress = document.getElementById('progress');
  const downloadArea = document.getElementById('downloadArea');
  const downloadBtn = document.getElementById('downloadBtn');

  let images = [];

  // Click to select
  dropZone.addEventListener('click', () => imageInput.click());

  // File selection
  imageInput.addEventListener('change', (e) => {
    handleFiles(Array.from(e.target.files));
  });

  // Drag & drop
  dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-rose-500', 'bg-rose-50');
  });

  dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-rose-500', 'bg-rose-50');
  });

  dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-rose-500', 'bg-rose-50');
    handleFiles(Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/')));
  });

  function handleFiles(newFiles) {
    const imageFiles = newFiles.filter(f => f.type.startsWith('image/'));
    if (imageFiles.length === 0) {
      alert('कृपया image फाइलहरू मात्र छान्नुहोस्');
      return;
    }
    images = [...images, ...imageFiles];
    renderImages();
  }

  function renderImages() {
    if (images.length === 0) {
      previewGrid.classList.add('hidden');
      options.classList.add('hidden');
      convertBtn.classList.add('hidden');
      return;
    }

    previewGrid.classList.remove('hidden');
    options.classList.remove('hidden');
    convertBtn.classList.remove('hidden');
    imageItems.innerHTML = '';

    images.forEach((file, index) => {
      const div = document.createElement('div');
      div.className = 'relative group aspect-square rounded-lg overflow-hidden border border-slate-200';
      div.draggable = true;
      div.dataset.index = index;

      const img = document.createElement('img');
      img.className = 'w-full h-full object-cover';
      img.src = URL.createObjectURL(file);
      img.alt = file.name;

      const removeBtn = document.createElement('button');
      removeBtn.className = 'absolute top-1 right-1 w-6 h-6 rounded-full bg-red-500 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity';
      removeBtn.innerHTML = '<i data-lucide="x" class="w-3 h-3"></i>';
      removeBtn.onclick = () => removeImage(index);

      const orderLabel = document.createElement('span');
      orderLabel.className = 'absolute bottom-1 left-1 w-5 h-5 rounded-full bg-black/50 text-white text-xs flex items-center justify-center';
      orderLabel.textContent = index + 1;

      div.appendChild(img);
      div.appendChild(removeBtn);
      div.appendChild(orderLabel);

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
      });

      div.addEventListener('drop', (e) => {
        e.preventDefault();
        const fromIndex = parseInt(e.dataTransfer.getData('text/plain'));
        const toIndex = parseInt(div.dataset.index);
        if (fromIndex !== toIndex) {
          const temp = images[fromIndex];
          images[fromIndex] = images[toIndex];
          images[toIndex] = temp;
          renderImages();
        }
      });

      imageItems.appendChild(div);
    });

    if (window.lucide) lucide.createIcons();
  }

  window.removeImage = function(index) {
    images.splice(index, 1);
    renderImages();
  };

  // Convert to PDF
  convertBtn.addEventListener('click', async () => {
    if (images.length === 0) return;

    progress.classList.remove('hidden');
    convertBtn.disabled = true;
    downloadArea.classList.add('hidden');

    try {
      const { PDFDocument, PageSizes } = PDFLib;
      const pdf = await PDFDocument.create();
      
      const pageSize = document.getElementById('pageSize').value;
      const orientation = document.getElementById('orientation').value;
      const quality = parseFloat(document.getElementById('quality').value);

      for (const file of images) {
        const imageBytes = await file.arrayBuffer();
        let embeddedImage;

        if (file.type === 'image/png') {
          embeddedImage = await pdf.embedPng(imageBytes);
        } else if (file.type === 'image/jpeg' || file.type === 'image/jpg') {
          embeddedImage = await pdf.embedJpg(imageBytes);
        } else {
          // For webp and others, convert to canvas first
          const bitmap = await createImageBitmap(file);
          const canvas = document.createElement('canvas');
          canvas.width = bitmap.width;
          canvas.height = bitmap.height;
          const ctx = canvas.getContext('2d');
          ctx.drawImage(bitmap, 0, 0);
          const pngData = canvas.toDataURL('image/png');
          embeddedImage = await pdf.embedPng(pngData);
        }

        const imgDims = embeddedImage.size();
        let pageDims;

        // Calculate page dimensions
        if (pageSize === 'A4') {
          pageDims = PageSizes.A4;
        } else if (pageSize === 'letter') {
          pageDims = PageSizes.Letter;
        } else {
          // Fit to image
          pageDims = [imgDims.width, imgDims.height];
        }

        // Handle orientation
        if (orientation === 'landscape' || 
            (orientation === 'auto' && imgDims.width > imgDims.height)) {
          if (pageSize !== 'fit') {
            pageDims = [pageDims[1], pageDims[0]];
          }
        }

        const page = pdf.addPage(pageDims);
        
        // Calculate scale to fit image on page
        const pageWidth = page.getWidth();
        const pageHeight = page.getHeight();
        const scaleX = pageWidth / imgDims.width;
        const scaleY = pageHeight / imgDims.height;
        const scale = pageSize === 'fit' ? 1 : Math.min(scaleX, scaleY);

        const scaledWidth = imgDims.width * scale;
        const scaledHeight = imgDims.height * scale;
        const x = (pageWidth - scaledWidth) / 2;
        const y = (pageHeight - scaledHeight) / 2;

        page.drawImage(embeddedImage, {
          x: x,
          y: y,
          width: scaledWidth,
          height: scaledHeight,
        });
      }

      const pdfBytes = await pdf.save();
      const blob = new Blob([pdfBytes], { type: 'application/pdf' });
      const url = URL.createObjectURL(blob);

      downloadBtn.href = url;
      downloadBtn.download = `photos_${Date.now()}.pdf`;
      
      progress.classList.add('hidden');
      downloadArea.classList.remove('hidden');

    } catch (error) {
      console.error('Convert error:', error);
      alert('PDF convert गर्न समस्या भयो। कृपया फेरि प्रयास गर्नुहोस्।');
      progress.classList.add('hidden');
    }
    
    convertBtn.disabled = false;
  });

  if (window.lucide) lucide.createIcons();
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
