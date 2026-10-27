(function () {
  'use strict';
  document.querySelectorAll('.sow-accordion').forEach(function (root) {
    root.addEventListener('click', function (e) {
      var btn = e.target.closest('.sow-accordion-btn');
      if (!btn || !root.contains(btn)) {
        return;
      }
      var item = btn.closest('.sow-accordion-item');
      var panel = item && item.querySelector('.sow-accordion-panel');
      if (!item || !panel) {
        return;
      }
      var willOpen = !item.classList.contains('is-open');
      root.querySelectorAll('.sow-accordion-item.is-open').forEach(function (openItem) {
        openItem.classList.remove('is-open');
        var openBtn = openItem.querySelector('.sow-accordion-btn');
        var openPanel = openItem.querySelector('.sow-accordion-panel');
        if (openBtn) {
          openBtn.setAttribute('aria-expanded', 'false');
          var openIcon = openBtn.querySelector('.icon');
          if (openIcon) {
            openIcon.textContent = '+';
          }
        }
        if (openPanel) {
          openPanel.hidden = true;
        }
      });
      if (willOpen) {
        item.classList.add('is-open');
        btn.setAttribute('aria-expanded', 'true');
        panel.hidden = false;
        var icon = btn.querySelector('.icon');
        if (icon) {
          icon.textContent = '−';
        }
      } else {
        var closedIcon = btn.querySelector('.icon');
        if (closedIcon) {
          closedIcon.textContent = '+';
        }
      }
    });
  });
})();
