/**
 * Syria Oil — scroll entrance reveals (IntersectionObserver + CSS).
 * Observes [data-sor-animate], adds .is-visible once, then unobserves.
 * Plugin copy — used when theme handle is unavailable.
 */
(function () {
  'use strict';

  var SELECTOR = '[data-sor-animate]';
  var VISIBLE = 'is-visible';
  var reduced =
    typeof window.matchMedia === 'function' &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function reveal(el) {
    var delay = parseInt(el.getAttribute('data-sor-delay') || '0', 10);
    if (delay > 0 && !reduced) {
      el.style.transitionDelay = delay + 'ms';
    }
    el.classList.add(VISIBLE);
    el.dispatchEvent(new CustomEvent('sor:revealed', { bubbles: true }));
  }

  function revealAll(root) {
    var nodes = (root || document).querySelectorAll(SELECTOR);
    for (var i = 0; i < nodes.length; i++) {
      reveal(nodes[i]);
    }
  }

  function init(root) {
    var scope = root && root.querySelectorAll ? root : document;
    var nodes = scope.querySelectorAll(SELECTOR);
    if (!nodes.length) {
      return;
    }

    if (reduced || !('IntersectionObserver' in window)) {
      revealAll(scope);
      return;
    }

    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) {
            return;
          }
          reveal(entry.target);
          io.unobserve(entry.target);
        });
      },
      { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
    );

    for (var i = 0; i < nodes.length; i++) {
      var el = nodes[i];
      if (el.classList.contains(VISIBLE)) {
        continue;
      }
      io.observe(el);
    }
  }

  function boot() {
    init(document);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }

  window.addEventListener('elementor/frontend/init', function () {
    if (window.elementorFrontend && window.elementorFrontend.hooks) {
      window.elementorFrontend.hooks.addAction(
        'frontend/element_ready/global',
        function ($scope) {
          var el = $scope && $scope[0] ? $scope[0] : null;
          if (el) {
            init(el);
          }
        }
      );
    }
  });

  window.SyriaOilScrollReveal = {
    init: init,
    revealAll: revealAll,
  };
})();
