/**
 * Instant category filtering for Posts Filter Hub.
 */
(function () {
  'use strict';

  function qs(root, sel) {
    return root.querySelector(sel);
  }

  function qsa(root, sel) {
    return Array.prototype.slice.call(root.querySelectorAll(sel));
  }

  function setActive(root, catId) {
    qsa(root, '[data-cat-id]').forEach(function (el) {
      var on = String(el.getAttribute('data-cat-id') || '0') === String(catId);
      el.classList.toggle('is-active', on);
      if (el.hasAttribute('aria-pressed')) {
        el.setAttribute('aria-pressed', on ? 'true' : 'false');
      }
    });
  }

  function updateUrl(slug) {
    try {
      var url = new URL(window.location.href);
      if (slug) {
        url.searchParams.set('sow_cat', slug);
      } else {
        url.searchParams.delete('sow_cat');
      }
      window.history.replaceState({}, '', url.toString());
    } catch (e) {
      /* ignore */
    }
  }

  function chipLabel(btn) {
    var title = btn.querySelector('.sow-posts-filter__card-title');
    if (title) return title.textContent.trim();
    var clone = btn.cloneNode(true);
    var small = clone.querySelector('small');
    if (small) small.remove();
    return clone.textContent.replace(/\s+/g, ' ').trim();
  }

  function escapeAttr(value) {
    if (window.CSS && typeof window.CSS.escape === 'function') {
      return window.CSS.escape(value);
    }
    return String(value).replace(/"/g, '\\"');
  }

  function filter(root, catId, slug, label) {
    var grid = qs(root, '[data-sow-grid]');
    var status = qs(root, '[data-sow-status]');
    var emptyMsg = root.getAttribute('data-empty') || '';
    if (!grid) return;

    var id = String(catId || '0');
    var items = qsa(grid, '.sow-posts-filter__item');
    var shown = 0;

    items.forEach(function (item) {
      item.classList.add('is-filtering');
    });

    window.setTimeout(function () {
      items.forEach(function (item) {
        var ids = (item.getAttribute('data-cat-ids') || '').split(',').filter(Boolean);
        var match = id === '0' || ids.indexOf(id) !== -1;
        item.classList.toggle('is-hidden', !match);
        item.classList.remove('is-filtering');
        if (match) shown += 1;
      });

      var empty = qs(grid, '.sow-posts-filter__empty');
      if (shown === 0) {
        if (!empty) {
          empty = document.createElement('p');
          empty.className = 'sow-posts-filter__empty';
          empty.textContent = emptyMsg;
          grid.appendChild(empty);
        }
        empty.hidden = false;
      } else if (empty) {
        empty.hidden = true;
      }

      if (status) {
        status.textContent = id === '0' ? 'عرض: كل الأخبار' : 'عرض: ' + (label || '');
      }
    }, 120);

    setActive(root, id);
    updateUrl(slug || '');
  }

  function init(root) {
    root.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-cat-id]');
      if (!btn || !root.contains(btn)) return;
      e.preventDefault();
      filter(root, btn.getAttribute('data-cat-id') || '0', btn.getAttribute('data-cat-slug') || '', chipLabel(btn));
    });

    try {
      var params = new URLSearchParams(window.location.search);
      var slug = params.get('sow_cat');
      if (slug) {
        var match = qs(root, '[data-cat-slug="' + escapeAttr(slug) + '"]');
        if (match) {
          filter(root, match.getAttribute('data-cat-id'), slug, chipLabel(match));
          return;
        }
      }
    } catch (err) {
      /* ignore */
    }
    filter(root, '0', '', '');
  }

  function boot() {
    qsa(document, '[data-sow-filter]').forEach(init);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }

  // Elementor preview / SPA re-init.
  if (window.elementorFrontend && window.elementorFrontend.hooks) {
    window.elementorFrontend.hooks.addAction('frontend/element_ready/syria_oil_posts_filter.default', function ($scope) {
      var el = $scope[0] && $scope[0].querySelector('[data-sow-filter]');
      if (el) init(el);
    });
  }
})();
