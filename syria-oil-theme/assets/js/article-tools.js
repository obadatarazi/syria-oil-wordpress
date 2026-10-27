/**
 * Article font scale, share copy, manual continuous reading.
 */
(function () {
  'use strict';

  var cfg = window.syriaOilArticle || {};
  var STORAGE_KEY = 'syriaOilFontScale';

  function applyFontScale(step) {
    var prose = document.querySelectorAll('.article-prose');
    if (!prose.length) return;
    var scale = step;
    if (typeof scale !== 'number') {
      scale = parseInt(localStorage.getItem(STORAGE_KEY) || '0', 10);
    }
    scale = Math.max(-2, Math.min(2, scale));
    localStorage.setItem(STORAGE_KEY, String(scale));
    prose.forEach(function (el) {
      el.style.setProperty('--article-font-step', String(scale));
    });
  }

  function initFontControls() {
    if (!cfg.fontScale) return;
    applyFontScale(parseInt(localStorage.getItem(STORAGE_KEY) || '0', 10));
    document.querySelectorAll('[data-font-step]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var step = parseInt(btn.getAttribute('data-font-step'), 10);
        if (step === 0) {
          localStorage.removeItem(STORAGE_KEY);
          document.querySelectorAll('.article-prose').forEach(function (el) {
            el.style.removeProperty('--article-font-step');
          });
          return;
        }
        var current = parseInt(localStorage.getItem(STORAGE_KEY) || '0', 10);
        applyFontScale(current + step);
      });
    });
  }

  function initCopyButtons() {
    document.querySelectorAll('[data-copy-url]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var url = btn.getAttribute('data-copy-url') || '';
        if (!url || !navigator.clipboard) return;
        navigator.clipboard.writeText(url).then(function () {
          btn.classList.add('is-copied');
          btn.setAttribute('aria-label', cfg.copyDone || 'Copied');
          setTimeout(function () {
            btn.classList.remove('is-copied');
          }, 2000);
        });
      });
    });
  }

  function collectLoadedIds() {
    var ids = [];
    document.querySelectorAll('.article-root[data-post-id]').forEach(function (el) {
      var id = el.getAttribute('data-post-id');
      if (id) ids.push(id);
    });
    return ids;
  }

  function getAnchorPostId() {
    var roots = document.querySelectorAll('.article-root[data-post-id]');
    if (!roots.length) return '';
    return roots[roots.length - 1].getAttribute('data-post-id') || '';
  }

  function loadMore(btn, container) {
    var anchorId = getAnchorPostId();
    if (!cfg.restUrl || !anchorId || btn.disabled) return;

    var exclude = collectLoadedIds().join(',');
    var count = cfg.loadCount || 5;
    var url = cfg.restUrl.replace(/\/$/, '') + '/' + anchorId + '?count=' + count + '&exclude=' + encodeURIComponent(exclude);
    var original = btn.textContent;
    btn.disabled = true;
    btn.textContent = cfg.loadingLabel || 'Loading…';

    fetch(url, { credentials: 'same-origin' })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data && data.html) {
          var wrap = document.createElement('div');
          wrap.innerHTML = data.html;
          while (wrap.firstChild) {
            container.appendChild(wrap.firstChild);
          }
          if (data.url && window.history && window.history.pushState) {
            window.history.pushState({ postId: data.id }, data.title || '', data.url);
            document.title = data.title || document.title;
          }
          initCopyButtons();
          applyFontScale();
        }
        if (!data || !data.has_more) {
          btn.textContent = cfg.loadMoreDone || 'No more articles';
          btn.disabled = true;
        } else {
          btn.textContent = original;
          btn.disabled = false;
        }
      })
      .catch(function () {
        btn.textContent = original;
        btn.disabled = false;
      });
  }

  function initContinuousReading() {
    if (!cfg.continuousReading) return;
    var stream = document.querySelector('.article-continuous-stream');
    var btn = document.querySelector('.article-load-more');
    if (!stream || !btn) return;

    btn.addEventListener('click', function () {
      loadMore(btn, stream);
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initFontControls();
    initCopyButtons();
    initContinuousReading();
  });
})();
