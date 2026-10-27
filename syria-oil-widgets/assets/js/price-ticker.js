(function () {
  'use strict';
  document.querySelectorAll('.sow-prices--ticker').forEach(function (el) {
    var speed = parseInt(el.getAttribute('data-speed') || '30', 10);
    var pause = el.getAttribute('data-pause') === '1';
    el.style.setProperty('--sow-prices-duration', speed + 's');
    if (pause) {
      el.addEventListener('mouseenter', function () {
        el.classList.add('is-paused');
      });
      el.addEventListener('mouseleave', function () {
        el.classList.remove('is-paused');
      });
    }
  });
})();
