/**
 * swipe-nav.js — Page transition animations + horizontal swipe between sections
 * Gives the app a native feel: pages fade in, links fade out.
 */
(function(){
  'use strict';

  // ── Fade-in on page load ────────────────────────────────────────────────────
  // Apply before DOMContentLoaded so it catches the initial paint
  var body = document.documentElement;
  body.style.opacity = '0';
  body.style.transition = 'opacity .28s cubic-bezier(.4,0,.2,1)';
  document.addEventListener('DOMContentLoaded', function(){
    requestAnimationFrame(function(){
      requestAnimationFrame(function(){
        body.style.opacity = '1';
      });
    });
  });

  // ── Link click → fade-out before navigate ──────────────────────────────────
  document.addEventListener('click', function(e){
    var a = e.target.closest('a[href]');
    if (!a) return;
    var href = a.getAttribute('href');
    // Only animate same-origin, non-hash, non-external links
    if (!href || href.startsWith('#') || href.startsWith('tel:') || href.startsWith('mailto:') ||
        href.startsWith('javascript:') || a.getAttribute('target') === '_blank' ||
        (href.startsWith('http') && !href.startsWith(location.origin))) return;
    // Skip if modifier keys held (open in new tab etc.)
    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    e.preventDefault();
    body.style.opacity = '0';
    setTimeout(function(){ location.href = href; }, 250);
  }, true);

  // ── Swipe left / right between swipeable pages ─────────────────────────────
  var pages = [
    '/', '/index.php',
    '/news.php', '/news',
    '/rashifal.php', '/rashifal',
    '/nepali-patro.php', '/nepali-patro', '/patro',
    '/utilities.php', '/utilities',
  ];
  var orderedPages = [
    ['/','index'],
    ['/news.php','news'],
    ['/rashifal.php','rashifal'],
    ['/nepali-patro.php','patro'],
    ['/utilities.php','utilities'],
  ];
  var cur = location.pathname.replace(/\/$/, '') || '/';
  var curIdx = -1;
  orderedPages.forEach(function(p, i){
    var paths = p[0] === '/' ? ['/', '/index.php'] : [p[0], '/' + p[1]];
    if (paths.indexOf(cur) >= 0) curIdx = i;
  });
  if (curIdx < 0) return; // not on a swipeable page — skip swipe setup

  var sx = 0, sy = 0, moved = false;
  var SWIPE_THRESHOLD = 72;  // px horizontal distance needed
  var AXIS_LOCK = 0.65;       // horizontal must dominate (ratio)

  document.addEventListener('touchstart', function(e){
    sx = e.touches[0].clientX;
    sy = e.touches[0].clientY;
    moved = false;
  }, { passive: true });

  document.addEventListener('touchmove', function(){
    moved = true;
  }, { passive: true });

  document.addEventListener('touchend', function(e){
    if (!moved) return;
    var dx = e.changedTouches[0].clientX - sx;
    var dy = e.changedTouches[0].clientY - sy;
    if (Math.abs(dx) < SWIPE_THRESHOLD) return;
    if (Math.abs(dy) > Math.abs(dx) * AXIS_LOCK) return; // mostly vertical — scroll
    // Check the element under the swipe isn't a horizontal scroll container
    var target = e.target;
    var el = target;
    while (el && el !== document.body) {
      var ow = el.scrollWidth > el.clientWidth + 8;
      if (ow) return; // inside a horizontally scrollable container
      el = el.parentElement;
    }
    if (dx < 0 && curIdx < orderedPages.length - 1) {
      body.style.opacity = '0';
      setTimeout(function(){ location.href = orderedPages[curIdx + 1][0]; }, 220);
    } else if (dx > 0 && curIdx > 0) {
      body.style.opacity = '0';
      setTimeout(function(){ location.href = orderedPages[curIdx - 1][0]; }, 220);
    }
  }, { passive: true });

})();
