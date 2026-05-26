<?php
/**
 * आकाशवाणी - Design System & Glassmorphism Theme
 * Unified theme tokens, component styles, and Nepali typography
 */

return [
  // ====== COLORS (Premium Palette) ======
  'colors' => [
    'primary'    => '#15803d',  // Calm forest green (Nepal inspired)
    'primary-light' => '#22c55e',
    'accent'     => '#0f766e',  // Teal
    'accent-light' => '#14b8a6',
    'success'    => '#16a34a',
    'warning'    => '#d97706',
    'error'      => '#dc2626',
    'info'       => '#1d4ed8',
    'bg'         => '#fafaf9',  // Warm off-white
    'bg-secondary' => '#f5f5f4',
    'surface'    => '#ffffff',
    'surface-alt' => '#f8fafc',
    'border'     => '#e7e5e4',
    'border-light' => '#f1f5f9',
    'text-primary' => '#0f172a',  // Dark navy
    'text-secondary' => '#64748b',  // Muted gray
    'text-tertiary' => '#94a3b8',  // Light gray
  ],

  // ====== GLASSMORPHISM CLASSES ======
  'glass' => [
    'card' => 'bg-white/45 backdrop-blur-md border border-white/20 shadow-lg hover:shadow-xl transition-all',
    'card-dark' => 'dark:bg-slate-900/45 dark:backdrop-blur-md dark:border dark:border-white/10',
    'modal' => 'fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50',
    'button' => 'px-4 py-2.5 rounded-xl font-semibold backdrop-blur-sm transition-all duration-200',
    'input' => 'w-full px-4 py-2.5 rounded-xl border border-white/30 bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-offset-2',
  ],

  // ====== TYPOGRAPHY (Nepali + English Unified) ======
  'typography' => [
    'font-sans' => "'Inter', 'Hind Siliguri', 'Noto Sans Devanagari', system-ui, sans-serif",
    'font-nepali' => "'Hind Siliguri', 'Noto Sans Devanagari', sans-serif",
    'font-mono' => "'Courier New', monospace",
    
    'heading-1' => 'text-4xl md:text-5xl font-bold text-slate-900 tracking-tight',
    'heading-2' => 'text-2xl md:text-3xl font-bold text-slate-900',
    'heading-3' => 'text-xl md:text-2xl font-semibold text-slate-900',
    'heading-4' => 'text-lg font-semibold text-slate-900',
    
    'body' => 'text-base leading-relaxed text-slate-700',
    'body-sm' => 'text-sm leading-relaxed text-slate-600',
    'body-xs' => 'text-xs leading-relaxed text-slate-500',
    
    'nepali-heading' => 'text-2xl md:text-3xl font-bold text-slate-900 font-hind',
    'nepali-body' => 'text-base leading-relaxed text-slate-700 font-hind',
  ],

  // ====== SPACING (Consistent Grid) ======
  'spacing' => [
    'xs' => '0.25rem',  // 4px
    'sm' => '0.5rem',   // 8px
    'md' => '1rem',     // 16px
    'lg' => '1.5rem',   // 24px
    'xl' => '2rem',     // 32px
    '2xl' => '3rem',    // 48px
  ],

  // ====== COMPONENTS (Reusable Markup) ======
  'components' => [
    
    // Card Component
    'card' => function($content, $class = '') {
      return <<<HTML
        <div class="bg-white/45 backdrop-blur-md border border-white/20 shadow-lg hover:shadow-xl hover:scale-[1.01] transition-all duration-300 rounded-2xl p-6 md:p-8 $class">
          $content
        </div>
      HTML;
    },

    // Button Variants
    'btn-primary' => 'px-6 py-2.5 rounded-xl font-semibold bg-gradient-to-r from-emerald-600 to-teal-600 text-white hover:shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all duration-200',
    'btn-secondary' => 'px-6 py-2.5 rounded-xl font-semibold bg-slate-200 text-slate-900 hover:bg-slate-300 transition-all duration-200',
    'btn-ghost' => 'px-6 py-2.5 rounded-xl font-semibold text-slate-700 hover:bg-slate-100 transition-all',
    'btn-outline' => 'px-6 py-2.5 rounded-xl font-semibold border-2 border-slate-300 text-slate-900 hover:border-emerald-600 hover:text-emerald-600 transition-all',

    // Input Components
    'input-base' => 'w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white/80 backdrop-blur-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-all',
    'textarea-base' => 'w-full px-4 py-3 rounded-xl border border-slate-200 bg-white/80 backdrop-blur-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 resize-none',

    // Badge/Pill
    'badge' => 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold',
    'badge-primary' => 'bg-emerald-100 text-emerald-700',
    'badge-warning' => 'bg-amber-100 text-amber-700',
    'badge-error' => 'bg-red-100 text-red-700',

    // Alert Box
    'alert' => 'p-4 rounded-xl border-l-4 flex items-start gap-3',
    'alert-success' => 'bg-green-50 border-green-500 text-green-700',
    'alert-warning' => 'bg-amber-50 border-amber-500 text-amber-700',
    'alert-error' => 'bg-red-50 border-red-500 text-red-700',
    'alert-info' => 'bg-blue-50 border-blue-500 text-blue-700',
  ],

  // ====== RESPONSIVE BREAKPOINTS ======
  'breakpoints' => [
    'sm' => '640px',
    'md' => '768px',
    'lg' => '1024px',
    'xl' => '1280px',
    '2xl' => '1536px',
  ],

  // ====== SHADOWS (Premium) ======
  'shadows' => [
    'soft' => '0 1px 2px rgba(15,23,42,0.05)',
    'base' => '0 4px 12px rgba(15,23,42,0.08)',
    'md' => '0 8px 24px rgba(15,23,42,0.12)',
    'lg' => '0 16px 40px rgba(15,23,42,0.16)',
    'xl' => '0 24px 48px rgba(15,23,42,0.2)',
    'glow' => '0 0 20px rgba(21,128,61,0.2)',
  ],

  // ====== TRANSITIONS ======
  'transitions' => [
    'fast' => 'transition-all duration-150',
    'base' => 'transition-all duration-200',
    'slow' => 'transition-all duration-300',
  ],

  // ====== NEPALI TEXT SNIPPETS (Common UI Labels) ======
  'nepali' => [
    'home' => 'गृहपृष्ठ',
    'news' => 'समाचार',
    'tools' => 'टुलहरू',
    'rashifal' => 'राशिफल',
    'downloads' => 'डाउनलोड',
    'search' => 'खोज्नुस्',
    'loading' => 'लोड हुँदै छ...',
    'error' => 'त्रुटि भयो',
    'success' => 'सफल',
    'cancel' => 'रद्द गर्नुस्',
    'save' => 'बचत गर्नुस्',
    'delete' => 'मेटाउनुस्',
  ],
];
