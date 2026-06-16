# आकाशवाणी - Design System Documentation

## Overview

This document describes the unified Design System for आकाशवाणी (Aakashbani) News Portal. The goal is to create a consistent, professional, and modern user interface that feels like a "big company built this portal."

## Design Principles

1. **One Design System** - All colors, fonts, buttons, cards, icons follow the same design tokens
2. **One Source of Truth** - Global CSS, reusable components, common PHP includes
3. **No Duplicate UI** - Same information should not appear in multiple places
4. **Mobile First** - Design for mobile before desktop
5. **Performance First** - Each page should load in under 2 seconds
6. **Accessibility** - Keyboard navigation, proper contrast, semantic HTML
7. **Consistency** - Every page should look like it belongs to the same family

---

## CSS Architecture

### File Structure

```
assets/css/
├── variables.css    # Design tokens (colors, fonts, spacing)
├── layout.css       # Layout utilities (grid, flex, containers)
├── components.css   # UI components (buttons, cards, forms)
├── responsive.css   # Responsive breakpoints
├── skeleton.css     # Loading states
└── global.css       # Main entry point (imports all)
```

### Import Order

The main `global.css` imports files in this order:

1. `variables.css` - CSS Custom Properties
2. Reset & Base Styles
3. `layout.css` - Layout System
4. `components.css` - Components
5. `responsive.css` - Responsive Design
6. Legacy Utilities - Backward compatibility

---

## Design Tokens

### Colors

```css
/* Primary - Emerald Green (Nepal-inspired) */
--brand-50: #ecfdf5;
--brand-100: #d1fae5;
--brand-200: #a7f3d0;
--brand-300: #6ee7b7;
--brand-400: #34d399;
--brand-500: #10b981;  /* Primary */
--brand-600: #059669;
--brand-700: #047857;

/* Secondary - Teal */
--teal-50: #f0fdfa;
--teal-500: #14b8a6;    /* Secondary */
--teal-600: #0d9488;

/* Neutral - Slate */
--slate-50: #f8fafc;
--slate-100: #f1f5f9;
--slate-200: #e2e8f0;
--slate-300: #cbd5e1;
--slate-400: #94a3b8;
--slate-500: #64748b;
--slate-600: #475569;
--slate-700: #334155;
--slate-800: #1e293b;
--slate-900: #0f172a;

/* Semantic Colors */
--success-500: #22c55e;
--warning-500: #f59e0b;
--error-500: #ef4444;
--info-500: #3b82f6;

/* Shortcuts */
--primary: var(--brand-500);
--secondary: var(--teal-500);
--accent: var(--brand-500);
--bg: var(--bg-primary);
--surface: var(--bg-card);
--ink: var(--text-primary);
--line: var(--border-primary);
--muted: var(--text-tertiary);
```

### Typography

```css
/* Font Families */
--font-sans: 'Inter', 'Hind Siliguri', 'Noto Sans Devanagari', system-ui, sans-serif;

/* Font Sizes - Fixed Scale */
--text-xs: 0.6875rem;    /* 11px */
--text-sm: 0.75rem;      /* 12px */
--text-base: 0.8125rem; /* 13px */
--text-md: 0.875rem;     /* 14px */
--text-lg: 1rem;         /* 16px */
--text-xl: 1.125rem;     /* 18px */
--text-2xl: 1.25rem;     /* 20px */
--text-3xl: 1.5rem;      /* 24px */
--text-4xl: 1.875rem;    /* 30px */
--text-5xl: 2.25rem;     /* 36px */

/* Font Weights */
--font-light: 300;
--font-normal: 400;
--font-medium: 500;
--font-semibold: 600;
--font-bold: 700;
--font-extrabold: 800;
```

### Spacing (4px Base)

```css
--space-0: 0;
--space-1: 0.25rem;   /* 4px */
--space-2: 0.5rem;    /* 8px */
--space-3: 0.75rem;   /* 12px */
--space-4: 1rem;      /* 16px */
--space-5: 1.25rem;   /* 20px */
--space-6: 1.5rem;    /* 24px */
--space-8: 2rem;      /* 32px */
--space-10: 2.5rem;    /* 40px */
--space-12: 3rem;      /* 48px */
--space-16: 4rem;      /* 64px */
```

### Border Radius

```css
--radius-sm: 0.25rem;    /* 4px */
--radius-md: 0.375rem;   /* 6px */
--radius-lg: 0.5rem;     /* 8px */
--radius-xl: 0.75rem;    /* 12px */
--radius-2xl: 1rem;      /* 16px */
--radius-full: 9999px;   /* Pill */
```

### Shadows

```css
--shadow-xs: 0 1px 2px rgba(15, 23, 42, 0.04);
--shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.06);
--shadow-md: 0 4px 6px rgba(15, 23, 42, 0.08);
--shadow-lg: 0 10px 15px rgba(15, 23, 42, 0.08);
--shadow-xl: 0 20px 25px rgba(15, 23, 42, 0.1);
--shadow-2xl: 0 25px 50px rgba(15, 23, 42, 0.2);
```

---

## Components

### Buttons

```html
<!-- Primary Button -->
<button class="btn btn-primary">Primary</button>

<!-- Secondary Button -->
<button class="btn btn-secondary">Secondary</button>

<!-- Outline Button -->
<button class="btn btn-outline">Outline</button>

<!-- Ghost Button -->
<button class="btn btn-ghost">Ghost</button>

<!-- Danger Button -->
<button class="btn btn-danger">Danger</button>

<!-- Success Button -->
<button class="btn btn-success">Success</button>

<!-- Button Sizes -->
<button class="btn btn-sm">Small</button>
<button class="btn btn-md">Medium</button>
<button class="btn btn-lg">Large</button>

<!-- Icon Button -->
<button class="btn btn-icon">
    <i data-lucide="search"></i>
</button>

<!-- Loading Button -->
<button class="btn btn-primary btn-loading">Loading...</button>
```

### Cards

```html
<!-- Basic Card -->
<div class="card">
    <div class="card-body">
        Card content here
    </div>
</div>

<!-- News Card -->
<div class="news-card">
    <div class="news-card-image">
        <img src="image.jpg" alt="News image">
    </div>
    <div class="news-card-body">
        <span class="news-card-category">Category</span>
        <h3 class="news-card-title">News Title</h3>
        <p class="news-card-excerpt">Short description...</p>
        <div class="news-card-meta">
            <span><i data-lucide="clock"></i> 2h ago</span>
            <span><i data-lucide="eye"></i> 1.2k</span>
        </div>
    </div>
</div>

<!-- Horizontal News Card -->
<div class="news-card news-card-h">
    <div class="news-card-image">
        <img src="image.jpg" alt="News image">
    </div>
    <div class="news-card-body">
        <!-- Same as above -->
    </div>
</div>

<!-- List Card -->
<div class="list-card">
    <div class="list-card-image">
        <img src="image.jpg" alt="Image">
    </div>
    <div class="list-card-content">
        <h4 class="list-card-title">Title</h4>
        <span class="list-card-meta">2h ago</span>
    </div>
</div>
```

### Badges

```html
<!-- Primary Badge -->
<span class="badge badge-primary">Primary</span>

<!-- Success Badge -->
<span class="badge badge-success">Success</span>

<!-- Warning Badge -->
<span class="badge badge-warning">Warning</span>

<!-- Error Badge -->
<span class="badge badge-error">Error</span>

<!-- Info Badge -->
<span class="badge badge-info">Info</span>

<!-- Gray Badge -->
<span class="badge badge-gray">Gray</span>

<!-- Live Badge (animated) -->
<span class="badge badge-live">LIVE</span>
```

### Forms

```html
<!-- Text Input -->
<input type="text" class="input" placeholder="Enter text...">

<!-- Search Input -->
<div class="search-input">
    <i data-lucide="search" class="search-input-icon"></i>
    <input type="search" class="input" placeholder="Search...">
</div>

<!-- Textarea -->
<textarea class="input textarea" placeholder="Enter message..."></textarea>

<!-- Select -->
<select class="input select">
    <option>Option 1</option>
    <option>Option 2</option>
</select>

<!-- Form Group -->
<div class="form-group">
    <label class="form-label">Email</label>
    <input type="email" class="input" placeholder="email@example.com">
    <span class="form-hint">We'll never share your email.</span>
</div>
```

### Navigation

```html
<!-- Tab List -->
<div class="tab-list">
    <button class="tab-item active">Tab 1</button>
    <button class="tab-item">Tab 2</button>
    <button class="tab-item">Tab 3</button>
</div>

<!-- Pills -->
<div class="pill-list">
    <button class="pill active">All</button>
    <button class="pill">News</button>
    <button class="pill">Sports</button>
</div>

<!-- Breadcrumb -->
<nav class="breadcrumb" aria-label="Breadcrumb">
    <span class="breadcrumb-item"><a href="/">Home</a></span>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-item"><a href="/news">News</a></span>
    <span class="breadcrumb-separator">/</span>
    <span class="breadcrumb-item active">Article</span>
</nav>
```

### Pagination

```html
<nav class="pagination" aria-label="Pagination">
    <button class="pagination-item" disabled>&laquo;</button>
    <button class="pagination-item">&lsaquo;</button>
    <button class="pagination-item active">1</button>
    <button class="pagination-item">2</button>
    <button class="pagination-item">3</button>
    <span class="pagination-ellipsis">...</span>
    <button class="pagination-item">10</button>
    <button class="pagination-item">&rsaquo;</button>
    <button class="pagination-item">&raquo;</button>
</nav>
```

### Alerts

```html
<!-- Success Alert -->
<div class="alert alert-success">
    <i data-lucide="check-circle" class="alert-icon"></i>
    <div class="alert-content">
        <strong class="alert-title">Success!</strong>
        <p>Your action was completed successfully.</p>
    </div>
</div>

<!-- Warning Alert -->
<div class="alert alert-warning">...</div>

<!-- Error Alert -->
<div class="alert alert-error">...</div>

<!-- Info Alert -->
<div class="alert alert-info">...</div>
```

---

## Layout

### Containers

```html
<div class="container">
    <!-- Content centered with max-width -->
</div>

<div class="container container-sm">
    <!-- Small container (640px) -->
</div>

<div class="container container-lg">
    <!-- Large container (1024px) -->
</div>
```

### Grid System

```html
<!-- 2 Columns -->
<div class="grid grid-cols-2">
    <div>Column 1</div>
    <div>Column 2</div>
</div>

<!-- 3 Columns -->
<div class="grid grid-cols-3">
    <div>Column 1</div>
    <div>Column 2</div>
    <div>Column 3</div>
</div>

<!-- Auto-fit Grid -->
<div class="grid grid-auto">
    <!-- Columns auto-fit based on min-width -->
</div>
```

### Flexbox Utilities

```html
<!-- Flex Container -->
<div class="flex">
    <div>Item 1</div>
    <div>Item 2</div>
</div>

<!-- Center -->
<div class="flex items-center justify-center">
    Centered content
</div>

<!-- Space Between -->
<div class="flex justify-between">
    <div>Left</div>
    <div>Right</div>
</div>

<!-- Column -->
<div class="flex flex-col">
    <div>Stacked</div>
    <div>Items</div>
</div>
```

### Sidebar Layout

```html
<div class="layout-with-sidebar">
    <main class="main-col">
        Main content here
    </main>
    <aside class="sidebar-col">
        Sidebar content here
    </aside>
</div>
```

---

## Skeleton Loaders

Use skeleton loaders for loading states:

```html
<!-- News Card Skeleton -->
<div class="skeleton-card">
    <div class="skeleton-card-image skeleton"></div>
    <div class="skeleton-card-body">
        <div class="skeleton-card-category skeleton"></div>
        <div class="skeleton-card-title skeleton"></div>
        <div class="skeleton-card-title-2 skeleton"></div>
        <div class="skeleton-card-excerpt skeleton"></div>
        <div class="skeleton-card-excerpt-2 skeleton"></div>
        <div class="skeleton-card-meta">
            <div class="skeleton-card-meta-item skeleton"></div>
            <div class="skeleton-card-meta-item skeleton"></div>
        </div>
    </div>
</div>

<!-- List Card Skeleton -->
<div class="skeleton-list-card">
    <div class="skeleton-list-card-image skeleton"></div>
    <div class="skeleton-list-card-body">
        <div class="skeleton-list-card-title skeleton"></div>
        <div class="skeleton-list-card-title-2 skeleton"></div>
        <div class="skeleton-list-card-meta skeleton"></div>
    </div>
</div>

<!-- Loading Spinner -->
<div class="loading-container">
    <div class="loading-spinner"></div>
    <p>Loading...</p>
</div>
```

---

## Responsive Breakpoints

```css
/* Mobile: < 640px (default) */
/* Tablet: 640px - 1023px */
@media (min-width: 640px) { ... }

/* Laptop: 1024px - 1279px */
@media (min-width: 1024px) { ... }

/* Desktop: 1280px - 1535px */
@media (min-width: 1280px) { ... }

/* Large Desktop: 1536px+ */
@media (min-width: 1536px) { ... }
```

### Responsive Utilities

```html
<!-- Hide on mobile -->
<div class="hide-mobile">Desktop only</div>

<!-- Hide on desktop -->
<div class="hide-desktop">Mobile only</div>

<!-- Show on tablet+ -->
<div class="show-tablet">Visible on tablet</div>

<!-- Responsive flex -->
<div class="flex flex-col md:flex-row">
    <div>Stacked on mobile</div>
    <div>Row on tablet+</div>
</div>
```

---

## Icons

Use Lucide Icons:

```html
<!-- Include in <head> -->
<script src="https://unpkg.com/lucide@latest"></script>

<!-- Usage -->
<i data-lucide="home"></i>
<i data-lucide="search"></i>
<i data-lucide="bell"></i>

<!-- Icon Sizes -->
<i data-lucide="home" class="icon-xs"></i>
<i data-lucide="home" class="icon-sm"></i>
<i data-lucide="home" class="icon-md"></i>
<i data-lucide="home" class="icon-lg"></i>
<i data-lucide="home" class="icon-xl"></i>

<!-- Initialize icons -->
<script>
    lucide.createIcons();
</script>
```

---

## Animation Utilities

```html
<!-- Fade In -->
<div class="animate-fade-in">Fade in on load</div>

<!-- Slide In -->
<div class="animate-slide-in">Slide in animation</div>

<!-- Pulse -->
<span class="animate-pulse-soft">Pulsing</span>

<!-- Hover Effects -->
<div class="card-hover">Hover to lift</div>
```

---

## Accessibility

### Skip Link

```html
<a href="#main-content" class="skip-link">
    Skip to main content
</a>
```

### Focus States

All interactive elements have visible focus states:

```css
:focus-visible {
    outline: 2px solid var(--brand-500);
    outline-offset: 2px;
}
```

### Reduced Motion

Respects user's motion preferences:

```css
@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation-duration: 0.01ms !important;
        transition-duration: 0.01ms !important;
    }
}
```

---

## JavaScript Utilities

Include `lazyload.js` for performance optimizations:

```html
<script src="assets/js/lazyload.js"></script>
```

Features:
- Image lazy loading
- Scroll animations
- Sticky header
- Back to top button
- Smooth scroll
- Offline detection

---

## PHP Includes

### Header Component

```php
<?php
$pageTitle = 'Page Title';
$pageDesc = 'Page description';
require_once __DIR__ . '/includes/header-new.php';
?>
```

### Footer Component

```php
<?php require_once __DIR__ . '/includes/footer-new.php'; ?>
```

---

## Performance Guidelines

1. **Images**: Always use lazy loading and WebP format
2. **Fonts**: Preconnect to Google Fonts
3. **Icons**: Use Lucide Icons (tree-shakeable)
4. **CSS**: Load only used components
5. **JavaScript**: Defer non-critical scripts
6. **Caching**: Enable browser caching for static assets

---

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- iOS Safari 14+
- Chrome for Android 90+

---

## Migration Guide

### From Old Classes

| Old Class | New Class |
|-----------|-----------|
| `bg-white` | `bg-card` or `bg-primary` |
| `text-gray-600` | `text-secondary` |
| `border-gray-200` | `border-primary` |
| `rounded-lg` | `radius-lg` or `radius-xl` |
| `shadow-sm` | `shadow-sm` |
| `p-4` | `p-4` (unchanged) |
| `mx-auto` | `mx-auto` (unchanged) |

---

## Examples

### Complete News Card

```html
<article class="news-card card-clickable">
    <a href="/news/123" class="news-card-image">
        <img src="image.jpg" alt="News image" loading="lazy">
    </a>
    <div class="news-card-body">
        <a href="/category/news" class="news-card-category">समाचार</a>
        <a href="/news/123">
            <h3 class="news-card-title">नेपालमा नयाँ प्रविधिको विकास</h3>
        </a>
        <p class="news-card-excerpt">
            नेपाल सरकारले नयाँ प्रविधि नीति अपनाउने भएको छ...
        </p>
        <div class="news-card-meta">
            <span>
                <i data-lucide="clock" class="icon-xs"></i>
                2 घण्टा अघि
            </span>
            <span>
                <i data-lucide="eye" class="icon-xs"></i>
                1.2k
            </span>
        </div>
    </div>
</article>
```

### Complete Section

```html
<section class="section">
    <div class="container">
        <header class="section-header">
            <div class="section-icon" style="background: var(--brand-100); color: var(--brand-600);">
                <i data-lucide="newspaper"></i>
            </div>
            <div class="section-title">
                <h2>ताजा समाचार</h2>
                <p class="section-subtitle">नयाँ र भरपर्दो सूचना</p>
            </div>
            <a href="/news" class="section-more">
                सबै हेर्नुहोस्
                <i data-lucide="arrow-right" class="icon-sm"></i>
            </a>
        </header>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <!-- News cards here -->
        </div>
    </div>
</section>
```

---

## Color Palette Summary

```
Primary (Emerald):    #10B981
Primary Dark:         #059669
Secondary (Teal):     #14B8A6
Secondary Dark:       #0D9488
Success:              #22C55E
Warning:              #F59E0B
Error:                #EF4444
Info:                 #3B82F6
Background:           #F8FAFC
Surface:              #FFFFFF
Text Primary:         #0F172A
Text Secondary:       #64748B
Text Muted:           #94A3B8
Border:               #E2E8F0
```

---

## Support

For questions or issues with the design system, please refer to the project documentation or contact the development team.
