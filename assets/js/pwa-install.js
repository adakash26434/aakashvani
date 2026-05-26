/**
 * आकाशवाणी — PWA Install Manager v1
 * Captures beforeinstallprompt, exposes window.nshInstallPwa()
 * Works on: Android Chrome, Edge, Samsung Browser, Desktop Chrome/Edge
 * iOS note: uses Share → Add to Home Screen (no JS API available)
 */
(function () {
  'use strict';

  var _deferredPrompt = null;
  var _installed = false;

  // Capture the browser's install prompt
  window.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault();
    _deferredPrompt = e;
    // Reveal any install buttons with data-pwa-install attribute
    document.querySelectorAll('[data-pwa-install]').forEach(function (el) {
      el.style.display = '';
      el.removeAttribute('hidden');
    });
    // Fire custom event so page code can react
    document.dispatchEvent(new CustomEvent('nsh:pwa-installable'));
  });

  // Detect already-installed
  window.addEventListener('appinstalled', function () {
    _installed = true;
    _deferredPrompt = null;
    document.querySelectorAll('[data-pwa-install]').forEach(function (el) {
      el.style.display = 'none';
    });
    document.querySelectorAll('[data-pwa-installed]').forEach(function (el) {
      el.style.display = '';
    });
    document.dispatchEvent(new CustomEvent('nsh:pwa-installed'));
  });

  // Public API
  window.nshPwa = {
    /**
     * Trigger native browser install prompt.
     * Returns true if prompt was shown, false otherwise.
     */
    install: function () {
      if (!_deferredPrompt) return false;
      _deferredPrompt.prompt();
      _deferredPrompt.userChoice.then(function (choice) {
        if (choice.outcome === 'accepted') {
          _installed = true;
          document.dispatchEvent(new CustomEvent('nsh:pwa-installed'));
        }
        _deferredPrompt = null;
      });
      return true;
    },

    isInstallable: function () { return !!_deferredPrompt; },
    isInstalled:   function () { return _installed || window.matchMedia('(display-mode: standalone)').matches; },

    /**
     * Detect platform for instructions.
     */
    platform: function () {
      var ua = navigator.userAgent;
      if (/iphone|ipad|ipod/i.test(ua)) return 'ios';
      if (/android/i.test(ua)) return 'android';
      if (/macintosh/i.test(ua) && navigator.maxTouchPoints > 1) return 'ios'; // iPad desktop
      if (/windows/i.test(ua) || /win64|win32/i.test(ua)) return 'windows';
      if (/macintosh/i.test(ua)) return 'mac';
      if (/linux/i.test(ua)) return 'linux';
      return 'other';
    },
  };

  // Register service worker
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register('/sw.js').catch(function () {});
    });
  }
})();
