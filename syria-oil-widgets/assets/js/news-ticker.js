(function () {
  'use strict';

  function initTicker(el) {
    if (!el || el.dataset.soTickerInit === '1') {
      return;
    }
    el.dataset.soTickerInit = '1';

    var speed = parseInt(el.getAttribute('data-speed') || '30', 10);
    if (!speed || speed < 8) {
      speed = 30;
    }
    var pause = el.getAttribute('data-pause') === '1';
    el.style.setProperty('--sow-ticker-duration', speed + 's');
    el.classList.add('is-autoplay');

    if (pause) {
      el.addEventListener('mouseenter', function () {
        el.classList.add('is-paused');
      });
      el.addEventListener('mouseleave', function () {
        el.classList.remove('is-paused');
      });
      el.addEventListener('focusin', function () {
        el.classList.add('is-paused');
      });
      el.addEventListener('focusout', function () {
        el.classList.remove('is-paused');
      });
    }
  }

  function initAll() {
    document.querySelectorAll('.sow-ticker').forEach(initTicker);
  }

  if (document.readyState !== 'loading') {
    initAll();
  } else {
    document.addEventListener('DOMContentLoaded', initAll);
  }

  if (window.elementorFrontend && window.elementorFrontend.hooks) {
    window.elementorFrontend.hooks.addAction(
      'frontend/element_ready/syria_oil_news_ticker.default',
      function ($scope) {
        var root = $scope && $scope[0] ? $scope[0].querySelector('.sow-ticker') : null;
        if (root) {
          root.dataset.soTickerInit = '';
          initTicker(root);
        }
      }
    );
  }
})();
