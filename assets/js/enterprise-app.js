/**
 * आकाशवाणी - Enterprise JavaScript
 * Global Application Logic
 * ES6+ Modern JavaScript
 */

(function() {
    'use strict';

    // ═══════════════════════════════════════════════════════════════
    // INITIALIZATION
    // ═══════════════════════════════════════════════════════════════

    document.addEventListener('DOMContentLoaded', function() {
        initLucideIcons();
        initBackToTop();
        initDropdowns();
        initSearch();
        initLazyLoad();
        initSmoothScroll();
        initShareButtons();
        initMobileMenu();
    });

    // ═══════════════════════════════════════════════════════════════
    // LUCIDE ICONS
    // ═══════════════════════════════════════════════════════════════

    function initLucideIcons() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // BACK TO TOP BUTTON
    // ═══════════════════════════════════════════════════════════════

    function initBackToTop() {
        const btn = document.getElementById('backToTop') || createBackToTopButton();
        
        if (!btn) return;

        // Show/hide on scroll
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                btn.classList.add('visible');
            } else {
                btn.classList.remove('visible');
            }
        });

        // Scroll to top on click
        btn.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    function createBackToTopButton() {
        const btn = document.createElement('button');
        btn.id = 'backToTop';
        btn.className = 'back-to-top';
        btn.innerHTML = '<i data-lucide="chevron-up"></i>';
        btn.title = 'Back to top';
        btn.style.cssText = `
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 3rem;
            height: 3rem;
            background: var(--primary, #10b981);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
        `;
        
        document.body.appendChild(btn);
        
        btn.addEventListener('mouseenter', () => btn.style.transform = 'translateY(-2px)');
        btn.addEventListener('mouseleave', () => btn.style.transform = 'translateY(0)');
        
        initLucideIcons();
        return btn;
    }

    // ═══════════════════════════════════════════════════════════════
    // DROPDOWN MENUS
    // ═══════════════════════════════════════════════════════════════

    function initDropdowns() {
        const triggers = document.querySelectorAll('.dropdown-trigger, .more-trigger');
        
        triggers.forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                const isOpen = this.getAttribute('aria-expanded') === 'true';
                
                // Close all dropdowns first
                closeAllDropdowns();
                
                if (!isOpen) {
                    this.setAttribute('aria-expanded', 'true');
                    this.closest('.has-dropdown, .nav-item').classList.add('open');
                }
            });
        });

        // Close on outside click
        document.addEventListener('click', closeAllDropdowns);

        // Close on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAllDropdowns();
            }
        });
    }

    function closeAllDropdowns() {
        document.querySelectorAll('.dropdown-trigger, .more-trigger').forEach(t => {
            t.setAttribute('aria-expanded', 'false');
        });
        document.querySelectorAll('.has-dropdown.open, .nav-item.open').forEach(d => {
            d.classList.remove('open');
        });
    }

    // ═══════════════════════════════════════════════════════════════
    // SEARCH
    // ═══════════════════════════════════════════════════════════════

    function initSearch() {
        const searchForms = document.querySelectorAll('.search-form, form[action*="search"]');
        
        searchForms.forEach(form => {
            const input = form.querySelector('input[type="search"], input[name="q"]');
            const btn = form.querySelector('button[type="submit"]');
            
            if (input && btn) {
                // Disable button when input is empty
                input.addEventListener('input', function() {
                    btn.disabled = this.value.trim() === '';
                });
                
                // Prevent form submission if empty
                form.addEventListener('submit', function(e) {
                    if (input.value.trim() === '') {
                        e.preventDefault();
                        input.focus();
                    }
                });
            }
        });
    }

    // ═══════════════════════════════════════════════════════════════
    // LAZY LOADING
    // ═══════════════════════════════════════════════════════════════

    function initLazyLoad() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                        }
                        img.classList.add('loaded');
                        observer.unobserve(img);
                    }
                });
            }, { rootMargin: '50px' });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // SMOOTH SCROLL
    // ═══════════════════════════════════════════════════════════════

    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const target = document.querySelector(targetId);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    // ═══════════════════════════════════════════════════════════════
    // SHARE BUTTONS
    // ═══════════════════════════════════════════════════════════════

    function initShareButtons() {
        const shareButtons = document.querySelectorAll('.share-btn, [data-share]');
        
        shareButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const shareUrl = this.dataset.url || window.location.href;
                const shareTitle = this.dataset.title || document.title;
                const platform = this.dataset.platform || '';
                
                let url = '';
                
                switch(platform) {
                    case 'facebook':
                        url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`;
                        break;
                    case 'twitter':
                        url = `https://twitter.com/intent/tweet?url=${encodeURIComponent(shareUrl)}&text=${encodeURIComponent(shareTitle)}`;
                        break;
                    case 'whatsapp':
                        url = `https://wa.me/?text=${encodeURIComponent(shareTitle + ' ' + shareUrl)}`;
                        break;
                    case 'telegram':
                        url = `https://t.me/share/url?url=${encodeURIComponent(shareUrl)}&text=${encodeURIComponent(shareTitle)}`;
                        break;
                    case 'copy':
                        copyToClipboard(shareUrl);
                        showToast('Link copied!');
                        return;
                    case 'native':
                        if (navigator.share) {
                            navigator.share({ title: shareTitle, url: shareUrl });
                            return;
                        }
                        break;
                    default:
                        // Open share modal if exists
                        const modal = document.querySelector('.share-modal');
                        if (modal) modal.classList.add('active');
                        return;
                }
                
                if (url) {
                    window.open(url, '_blank', 'width=600,height=400');
                }
            });
        });
    }

    // ═══════════════════════════════════════════════════════════════
    // MOBILE MENU
    // ═══════════════════════════════════════════════════════════════

    function initMobileMenu() {
        const menuToggle = document.querySelector('.mobile-menu-toggle, .menu-toggle');
        const mobileMenu = document.querySelector('.mobile-menu, .nav-mobile');
        
        if (menuToggle && mobileMenu) {
            menuToggle.addEventListener('click', function() {
                mobileMenu.classList.toggle('active');
                this.setAttribute('aria-expanded', mobileMenu.classList.contains('active'));
                
                // Toggle icon
                const icon = this.querySelector('i');
                if (icon) {
                    icon.setAttribute('data-lucide', mobileMenu.classList.contains('active') ? 'x' : 'menu');
                    initLucideIcons();
                }
            });
            
            // Close on outside click
            document.addEventListener('click', function(e) {
                if (!mobileMenu.contains(e.target) && !menuToggle.contains(e.target)) {
                    mobileMenu.classList.remove('active');
                    menuToggle.setAttribute('aria-expanded', 'false');
                }
            });
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // UTILITY FUNCTIONS
    // ═══════════════════════════════════════════════════════════════

    function copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text);
        } else {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        }
    }

    function showToast(message, duration = 3000) {
        const existing = document.querySelector('.toast');
        if (existing) existing.remove();
        
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 5rem;
            left: 50%;
            transform: translateX(-50%);
            background: var(--neutral-800, #1e293b);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            z-index: 10000;
            animation: toastIn 0.3s ease;
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'toastOut 0.3s ease forwards';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }

    // ═══════════════════════════════════════════════════════════════
    // API HELPER
    // ═══════════════════════════════════════════════════════════════

    window.App = {
        // HTTP methods
        async get(url, params = {}) {
            const queryString = new URLSearchParams(params).toString();
            const fullUrl = queryString ? `${url}?${queryString}` : url;
            
            try {
                const response = await fetch(fullUrl);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return await response.json();
            } catch (error) {
                console.error('GET request failed:', error);
                throw error;
            }
        },

        async post(url, data = {}) {
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return await response.json();
            } catch (error) {
                console.error('POST request failed:', error);
                throw error;
            }
        },

        // Toast notifications
        toast(message, duration) {
            showToast(message, duration);
        },

        // Copy to clipboard
        copy(text) {
            copyToClipboard(text);
            this.toast('Copied to clipboard!');
        },

        // Format number
        formatNumber(num) {
            return new Intl.NumberFormat('ne-NP').format(num);
        },

        // Format date
        formatDate(date, options = {}) {
            return new Intl.DateTimeFormat('ne-NP', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                ...options
            }).format(new Date(date));
        },

        // Relative time
        timeAgo(date) {
            const seconds = Math.floor((new Date() - new Date(date)) / 1000);
            
            const intervals = {
                year: 31536000,
                month: 2592000,
                week: 604800,
                day: 86400,
                hour: 3600,
                minute: 60
            };
            
            for (const [unit, secondsInUnit] of Object.entries(intervals)) {
                const interval = Math.floor(seconds / secondsInUnit);
                if (interval >= 1) {
                    return `${interval} ${unit} ago`;
                }
            }
            return 'Just now';
        }
    };

    // Add toast animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes toastIn {
            from { opacity: 0; transform: translateX(-50%) translateY(10px); }
            to { opacity: 1; transform: translateX(-50%) translateY(0); }
        }
        @keyframes toastOut {
            from { opacity: 1; transform: translateX(-50%) translateY(0); }
            to { opacity: 0; transform: translateX(-50%) translateY(10px); }
        }
        .back-to-top.visible {
            opacity: 1 !important;
            visibility: visible !important;
            transform: translateY(0) !important;
        }
    `;
    document.head.appendChild(style);

})();
