/**
 * Inject New Syrian Emblem into banners (physical left via CSS).
 */
(function () {
  'use strict';

  function boot() {
    if (!document.body || !document.body.classList.contains('so-syrian-emblem')) {
      return;
    }
    var url = (window.syriaOilEmblem && window.syriaOilEmblem.url) || '';
    if (!url) return;

    var selectors = [
      '.article-top',
      '.page-hero',
      '.sow-page-hero',
      '.so-banner',
      '.elementor-section.so-banner',
      '.elementor-location-single > .elementor-section:first-child',
      '.elementor-location-archive > .elementor-section:first-child'
    ];

    selectors.forEach(function (sel) {
      document.querySelectorAll(sel).forEach(function (el) {
        if (el.querySelector('img.so-emblem-mark')) return;
        // Parent must be positioning context.
        var cs = window.getComputedStyle(el);
        if (cs.position === 'static') {
          el.style.position = 'relative';
        }
        var img = document.createElement('img');
        img.className = 'so-emblem-mark';
        img.src = url;
        img.alt = '';
        img.setAttribute('aria-hidden', 'true');
        img.width = 160;
        img.height = 120;
        img.loading = 'lazy';
        img.decoding = 'async';
        el.insertBefore(img, el.firstChild);
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
