/**
 * Client-side chip filters for media / gallery sections (data-filter + data-target).
 */
(function () {
  'use strict';

  function bindBar(bar) {
    var chips = bar.querySelectorAll('.chip[data-filter]');
    if (!chips.length) return;

    chips.forEach(function (chip) {
      chip.addEventListener('click', function () {
        chips.forEach(function (c) {
          c.classList.remove('is-active');
          if (c.hasAttribute('aria-pressed')) {
            c.setAttribute('aria-pressed', 'false');
          }
        });
        chip.classList.add('is-active');
        if (chip.hasAttribute('aria-pressed')) {
          chip.setAttribute('aria-pressed', 'true');
        }

        var filter = chip.getAttribute('data-filter') || 'all';
        var target = chip.getAttribute('data-target');
        if (!target) return;

        document.querySelectorAll(target).forEach(function (el) {
          var cat = el.getAttribute('data-category') || 'all';
          var show = filter === 'all' || cat === filter;
          el.classList.toggle('is-filter-hidden', !show);
          el.style.display = show ? '' : 'none';
        });
      });
    });
  }

  function init() {
    document.querySelectorAll('.filter-bar').forEach(bindBar);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
