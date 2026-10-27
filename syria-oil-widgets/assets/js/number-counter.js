/**
 * Syria Oil — format-aware number counters (requestAnimationFrame).
 * Hooks: [data-so-count] — scroll-into-view (default) or value-change via storage key.
 */
(function () {
  'use strict';

  var SELECTOR = '[data-so-count]';
  var rafIds = Object.create(null);
  var rafSeq = 0;
  var reduced =
    typeof window.matchMedia === 'function' &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function easeOutCubic(t) {
    return 1 - Math.pow(1 - t, 3);
  }

  function parseNum(v) {
    var n = parseFloat(String(v).replace(/,/g, ''));
    return isNaN(n) ? 0 : n;
  }

  function durationFor(from, to, baseMs) {
    var distance = Math.abs(to - from);
    var base = baseMs > 0 ? baseMs : 1100;
    // Magnitude-aware within 800–1600ms.
    var scaled = base;
    if (distance < 20) {
      scaled = Math.min(base, 800);
    } else if (distance > 10000) {
      scaled = Math.min(1600, Math.max(base, 1400));
    } else if (distance > 1000) {
      scaled = Math.min(1600, Math.max(base, 1200));
    }
    return Math.max(800, Math.min(1600, scaled));
  }

  function formatValue(value, opts) {
    var decimals = opts.decimals;
    var grouping = opts.grouping;
    var locale = opts.locale || 'en-US';
    var abs = Math.abs(value);
    var fixed = decimals > 0 ? abs.toFixed(decimals) : String(Math.round(abs));

    if (grouping) {
      var parts = fixed.split('.');
      parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
      fixed = parts.join('.');
    }

    var sign = value < 0 ? '-' : '';
    return (
      (opts.prefix || '') +
      sign +
      fixed +
      (opts.suffix || '')
    );
  }

  function readOpts(el) {
    var decimals = parseInt(el.getAttribute('data-so-decimals') || '0', 10);
    if (isNaN(decimals) || decimals < 0) {
      decimals = 0;
    }
    return {
      target: parseNum(el.getAttribute('data-so-count')),
      prefix: el.getAttribute('data-so-prefix') || '',
      suffix: el.getAttribute('data-so-suffix') || '',
      decimals: decimals,
      grouping: el.getAttribute('data-so-grouping') === '1',
      locale: el.getAttribute('data-so-locale') || 'en-US',
      duration: parseInt(el.getAttribute('data-so-duration') || '1100', 10),
      key: el.getAttribute('data-so-key') || '',
      fromPrevious: el.getAttribute('data-so-from-previous') === '1',
      trigger: el.getAttribute('data-so-trigger') || 'scroll',
    };
  }

  function storageGet(key) {
    if (!key) {
      return null;
    }
    try {
      var v = window.sessionStorage.getItem('so-count:' + key);
      return v === null ? null : parseNum(v);
    } catch (e) {
      return null;
    }
  }

  function storageSet(key, value) {
    if (!key) {
      return;
    }
    try {
      window.sessionStorage.setItem('so-count:' + key, String(value));
    } catch (e) {
      /* ignore */
    }
  }

  function cancel(el) {
    var id = el.getAttribute('data-so-raf');
    if (id && rafIds[id]) {
      cancelAnimationFrame(rafIds[id]);
      delete rafIds[id];
    }
    el.removeAttribute('data-so-raf');
  }

  function setFinal(el, opts, value) {
    cancel(el);
    var end = typeof value === 'number' ? value : opts.target;
    el.textContent = formatValue(end, opts);
    el.setAttribute('data-so-done', '1');
    storageSet(opts.key, end);
  }

  function animate(el, from, to, opts) {
    cancel(el);
    opts = opts || readOpts(el);

    if (reduced) {
      setFinal(el, opts, to);
      return;
    }

    var start = from;
    var end = to;
    var dur = durationFor(start, end, opts.duration);
    var t0 = performance.now();
    var token = String(++rafSeq);
    el.setAttribute('data-so-raf', token);

    function tick(now) {
      var p = Math.min(1, (now - t0) / dur);
      var eased = easeOutCubic(p);
      var current = start + (end - start) * eased;
      el.textContent = formatValue(current, opts);
      if (p < 1) {
        rafIds[token] = requestAnimationFrame(tick);
      } else {
        delete rafIds[token];
        el.removeAttribute('data-so-raf');
        setFinal(el, opts, end);
      }
    }

    rafIds[token] = requestAnimationFrame(tick);
  }

  function startFrom(el) {
    if (el.getAttribute('data-so-done') === '1' && el.getAttribute('data-so-from-previous') !== '1') {
      return;
    }
    var opts = readOpts(el);

    if (opts.fromPrevious && opts.key) {
      var prev = storageGet(opts.key);
      if (prev === null) {
        // First paint — no prior value to morph from.
        setFinal(el, opts, opts.target);
        return;
      }
      if (prev === opts.target && el.getAttribute('data-so-live') !== '1') {
        setFinal(el, opts, opts.target);
        return;
      }
      animate(el, prev, opts.target, opts);
      return;
    }

    animate(el, 0, opts.target, opts);
  }

  function observeCounters(root) {
    var scope = root && root.querySelectorAll ? root : document;
    var nodes = scope.querySelectorAll(SELECTOR);
    if (!nodes.length) {
      return;
    }

    if (reduced || !('IntersectionObserver' in window)) {
      for (var i = 0; i < nodes.length; i++) {
        startFrom(nodes[i]);
      }
      return;
    }

    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) {
            return;
          }
          var el = entry.target;
          // Prefer waiting for entrance reveal on the same / ancestor card.
          var host = el.closest('[data-sor-animate]');
          if (host && !host.classList.contains('is-visible')) {
            host.addEventListener(
              'sor:revealed',
              function onReveal() {
                startFrom(el);
              },
              { once: true }
            );
          } else {
            startFrom(el);
          }
          io.unobserve(el);
        });
      },
      { threshold: 0.35, rootMargin: '0px 0px -20px 0px' }
    );

    for (var j = 0; j < nodes.length; j++) {
      var el = nodes[j];
      if (el.getAttribute('data-so-trigger') === 'change') {
        // Value-change mode: run once on init (from previous → current).
        startFrom(el);
        continue;
      }
      if (el.getAttribute('data-so-done') === '1') {
        continue;
      }
      io.observe(el);
    }
  }

  /**
   * Update a live counter to a new target (Price Ticker refresh).
   * @param {Element} el
   * @param {number} next
   */
  function updateTo(el, next) {
    if (!el) {
      return;
    }
    var opts = readOpts(el);
    var from = parseNum(el.textContent.replace(/[^\d.\-]/g, '')) || opts.target;
    el.setAttribute('data-so-count', String(next));
    el.setAttribute('data-so-live', '1');
    el.removeAttribute('data-so-done');
    opts.target = next;
    animate(el, from, next, opts);
  }

  function boot() {
    observeCounters(document);
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
            observeCounters(el);
          }
        }
      );
    }
  });

  window.addEventListener('pagehide', function () {
    Object.keys(rafIds).forEach(function (k) {
      cancelAnimationFrame(rafIds[k]);
      delete rafIds[k];
    });
  });

  document.addEventListener('syria-oil:price-update', function (ev) {
    var detail = ev.detail || {};
    if (!detail.key) {
      return;
    }
    var el = document.querySelector('[data-so-key="' + detail.key + '"]');
    if (el && typeof detail.value === 'number') {
      updateTo(el, detail.value);
    }
  });

  window.SyriaOilCounter = {
    init: observeCounters,
    animate: animate,
    updateTo: updateTo,
    formatValue: formatValue,
  };
})();
