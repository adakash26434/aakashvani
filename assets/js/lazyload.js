/**
 * आकाशवाणी - Lazy Load & Performance Utilities
 * Image lazy loading, intersection observer, and performance optimizations
 */

(function() {
    'use strict';

    // ═══════════════════════════════════════════════════════════════════════════════
    // IMAGE LAZY LOADING
    // ═══════════════════════════════════════════════════════════════════════════════

    /**
     * Lazy load images using Intersection Observer
     * Supports native loading="lazy" fallback
     */
    const LazyLoadImages = {
        init: function() {
            // Check for native support
            if ('loading' in HTMLImageElement.prototype) {
                this.useNativeLazyLoad();
            } else {
                this.useIntersectionObserver();
            }
        },

        useNativeLazyLoad: function() {
            const images = document.querySelectorAll('img[data-src]');
            images.forEach(img => {
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
            });
        },

        useIntersectionObserver: function() {
            if (!('IntersectionObserver' in window)) {
                this.fallbackLazyLoad();
                return;
            }

            const config = {
                root: null,
                rootMargin: '50px 0px',
                threshold: 0.01
            };

            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                            img.classList.add('loaded');
                        }
                        obs.unobserve(img);
                    }
                });
            }, config);

            const images = document.querySelectorAll('img[data-src]');
            images.forEach(img => observer.observe(img));
        },

        fallbackLazyLoad: function() {
            // Fallback for browsers without IntersectionObserver
            const images = document.querySelectorAll('img[data-src]');
            images.forEach(img => {
                const rect = img.getBoundingClientRect();
                if (rect.top < window.innerHeight) {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                }
            });
        }
    };

    // ═══════════════════════════════════════════════════════════════════════════════
    // ELEMENT ANIMATION ON SCROLL
    // ═══════════════════════════════════════════════════════════════════════════════

    /**
     * Animate elements when they scroll into view
     */
    const AnimateOnScroll = {
        init: function() {
            if (!('IntersectionObserver' in window)) {
                // Show all elements immediately if no support
                document.querySelectorAll('.animate-on-scroll').forEach(el => {
                    el.classList.add('animated');
                });
                return;
            }

            const config = {
                root: null,
                rootMargin: '0px 0px -50px 0px',
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animated');
                        observer.unobserve(entry.target);
                    }
                });
            }, config);

            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });
        }
    };

    // ═══════════════════════════════════════════════════════════════════════════════
    // PARALLAX EFFECT
    // ═══════════════════════════════════════════════════════════════════════════════

    /**
     * Simple parallax effect for hero images
     */
    const ParallaxEffect = {
        init: function() {
            const parallaxElements = document.querySelectorAll('[data-parallax]');
            
            if (parallaxElements.length === 0) return;
            
            if ('ontouchstart' in window) {
                // Disable parallax on touch devices
                return;
            }

            let ticking = false;

            const updateParallax = () => {
                parallaxElements.forEach(el => {
                    const speed = parseFloat(el.dataset.parallax) || 0.5;
                    const rect = el.getBoundingClientRect();
                    const scrolled = window.pageYOffset;
                    
                    if (rect.bottom > 0 && rect.top < window.innerHeight) {
                        const yPos = -(scrolled * speed);
                        el.style.transform = `translate3d(0, ${yPos}px, 0)`;
                    }
                });
                ticking = false;
            };

            window.addEventListener('scroll', () => {
                if (!ticking) {
                    requestAnimationFrame(updateParallax);
                    ticking = true;
                }
            }, { passive: true });
        }
    };

    // ═══════════════════════════════════════════════════════════════════════════════
    // STICKY HEADER ON SCROLL
    // ═══════════════════════════════════════════════════════════════════════════════

    /**
     * Add/remove classes on scroll for sticky header effects
     */
    const StickyHeader = {
        init: function() {
            const header = document.getElementById('app-header') || document.querySelector('.app-header');
            if (!header) return;

            let lastScroll = 0;
            const threshold = 100;

            window.addEventListener('scroll', () => {
                const currentScroll = window.pageYOffset;
                
                // Add scrolled class
                if (currentScroll > threshold) {
                    header.classList.add('is-scrolled');
                } else {
                    header.classList.remove('is-scrolled');
                }

                // Hide/show header on scroll direction
                if (currentScroll > lastScroll && currentScroll > threshold) {
                    header.classList.add('is-hidden');
                } else {
                    header.classList.remove('is-hidden');
                }

                lastScroll = currentScroll;
            }, { passive: true });
        }
    };

    // ═══════════════════════════════════════════════════════════════════════════════
    // SMOOTH SCROLL
    // ═══════════════════════════════════════════════════════════════════════════════

    /**
     * Smooth scroll to anchor links
     */
    const SmoothScroll = {
        init: function() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', (e) => {
                    const targetId = anchor.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const target = document.querySelector(targetId);
                    if (target) {
                        e.preventDefault();
                        const headerHeight = document.querySelector('.app-header')?.offsetHeight || 0;
                        const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - headerHeight - 20;
                        
                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });

                        // Update URL hash without jumping
                        history.pushState(null, null, targetId);
                    }
                });
            });
        }
    };

    // ═══════════════════════════════════════════════════════════════════════════════
    // BACK TO TOP BUTTON
    // ═══════════════════════════════════════════════════════════════════════════════

    /**
     * Back to top button functionality
     */
    const BackToTop = {
        init: function() {
            const btn = document.getElementById('back-to-top');
            if (!btn) return;

            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) {
                    btn.classList.add('visible');
                } else {
                    btn.classList.remove('visible');
                }
            }, { passive: true });

            btn.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
    };

    // ═══════════════════════════════════════════════════════════════════════════════
    // OFFLINE DETECTION
    // ═══════════════════════════════════════════════════════════════════════════════

    /**
     * Show offline indicator when network is down
     */
    const OfflineIndicator = {
        init: function() {
            const indicator = document.getElementById('offline-indicator');
            if (!indicator) return;

            const showIndicator = () => {
                indicator.classList.add('visible');
            };

            const hideIndicator = () => {
                indicator.classList.remove('visible');
            };

            window.addEventListener('offline', showIndicator);
            window.addEventListener('online', hideIndicator);
        }
    };

    // ═══════════════════════════════════════════════════════════════════════════════
    // DEBOUNCE & THROTTLE
    // ═══════════════════════════════════════════════════════════════════════════════

    /**
     * Utility functions for performance
     */
    const PerformanceUtils = {
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        throttle: function(func, limit) {
            let inThrottle;
            return function executedFunction(...args) {
                if (!inThrottle) {
                    func(...args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        }
    };

    // ═══════════════════════════════════════════════════════════════════════════════
    // PREFERS REDUCED MOTION
    // ═══════════════════════════════════════════════════════════════════════════════

    /**
     * Check user preference for reduced motion
     */
    const PrefersReducedMotion = {
        init: function() {
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
            
            if (prefersReducedMotion.matches) {
                document.documentElement.classList.add('reduce-motion');
            }

            prefersReducedMotion.addEventListener('change', (e) => {
                if (e.matches) {
                    document.documentElement.classList.add('reduce-motion');
                } else {
                    document.documentElement.classList.remove('reduce-motion');
                }
            });
        }
    };

    // ═══════════════════════════════════════════════════════════════════════════════
    // LAZY LOAD EXTERNAL SCRIPTS
    // ═══════════════════════════════════════════════════════════════════════════════

    /**
     * Load external scripts dynamically
     */
    const LazyScriptLoader = {
        load: function(src, callback, async = true) {
            const script = document.createElement('script');
            script.src = src;
            script.async = async;
            
            script.onload = callback;
            script.onerror = () => {
                console.warn(`Failed to load script: ${src}`);
            };

            document.body.appendChild(script);
        },

        loadMultiple: function(scripts, callback) {
            let loaded = 0;
            const total = scripts.length;

            scripts.forEach(src => {
                this.load(src, () => {
                    loaded++;
                    if (loaded === total && callback) {
                        callback();
                    }
                });
            });
        }
    };

    // ═══════════════════════════════════════════════════════════════════════════════
    // VIDEO LAZY LOAD
    // ═══════════════════════════════════════════════════════════════════════════════

    /**
     * Lazy load video elements
     */
    const LazyVideoLoader = {
        init: function() {
            if (!('IntersectionObserver' in window)) return;

            const config = {
                root: null,
                rootMargin: '100px',
                threshold: 0
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const video = entry.target;
                        const source = video.querySelector('source');
                        
                        if (source && source.dataset.src) {
                            source.src = source.dataset.src;
                            video.load();
                        }
                        
                        // Auto-play when visible
                        if (video.dataset.autoplay) {
                            video.play().catch(() => {});
                        }
                        
                        observer.unobserve(video);
                    }
                });
            }, config);

            document.querySelectorAll('video[data-src]').forEach(video => {
                observer.observe(video);
            });
        }
    };

    // ═══════════════════════════════════════════════════════════════════════════════
    // INITIALIZE ALL
    // ═══════════════════════════════════════════════════════════════════════════════

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        LazyLoadImages.init();
        AnimateOnScroll.init();
        ParallaxEffect.init();
        StickyHeader.init();
        SmoothScroll.init();
        BackToTop.init();
        OfflineIndicator.init();
        PrefersReducedMotion.init();
        LazyVideoLoader.init();

        // Add loaded class to body
        document.body.classList.add('js-loaded');
    }

    // ═══════════════════════════════════════════════════════════════════════════════
    // EXPORT FOR EXTERNAL USE
    // ═══════════════════════════════════════════════════════════════════════════════

    window.AakashbaniUtils = {
        LazyLoadImages,
        AnimateOnScroll,
        ParallaxEffect,
        StickyHeader,
        SmoothScroll,
        BackToTop,
        LazyScriptLoader,
        LazyVideoLoader,
        PerformanceUtils,
        debounce: PerformanceUtils.debounce,
        throttle: PerformanceUtils.throttle
    };

})();
