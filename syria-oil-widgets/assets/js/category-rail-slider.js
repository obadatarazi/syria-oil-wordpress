/**
 * Shared horizontal news-card slider — arrows, dots, RTL, autoplay.
 */
(function () {
  "use strict";

  var MOBILE_MAX = 767;

  function getTrack(root) {
    return (
      root.querySelector(".so-news-card-slider__track") ||
      root.querySelector(".sow-cat-sec__rail") ||
      root.querySelector(".so-card-grid__track")
    );
  }

  function getCards(track) {
    return track ? track.querySelectorAll(".so-news-card") : [];
  }

  function sliderBreakpoint(root) {
    var bp = parseInt(root.getAttribute("data-so-slider-bp") || String(MOBILE_MAX), 10);
    return isNaN(bp) ? MOBILE_MAX : bp;
  }

  function isMobileSlider(root) {
    if (root.classList.contains("so-news-card-slider--responsive")) {
      return window.innerWidth <= sliderBreakpoint(root);
    }
    return window.innerWidth <= MOBILE_MAX;
  }

  function autoplayDelay(root) {
    if (!root.hasAttribute("data-so-autoplay")) {
      return 0;
    }
    var ms = parseInt(root.getAttribute("data-so-autoplay"), 10);
    return ms >= 1000 ? ms : 3000;
  }

  function trackCenterX(track) {
    var rect = track.getBoundingClientRect();
    return rect.left + rect.width / 2;
  }

  function cardCenterX(card) {
    var rect = card.getBoundingClientRect();
    return rect.left + rect.width / 2;
  }

  function activeIndex(track) {
    var cards = getCards(track);
    if (!cards.length) {
      return 0;
    }
    var center = trackCenterX(track);
    var best = 0;
    var bestDist = Infinity;
    for (var i = 0; i < cards.length; i++) {
      var dist = Math.abs(cardCenterX(cards[i]) - center);
      if (dist < bestDist) {
        bestDist = dist;
        best = i;
      }
    }
    return best;
  }

  function scrollToIndex(track, index) {
    var cards = getCards(track);
    if (!cards.length) {
      return;
    }
    var clamped = Math.max(0, Math.min(cards.length - 1, index));
    var delta = cardCenterX(cards[clamped]) - trackCenterX(track);
    if (Math.abs(delta) < 1) {
      return;
    }
    track.scrollBy({ left: delta, behavior: "smooth" });
  }

  function isInViewport(el) {
    var rect = el.getBoundingClientRect();
    return rect.bottom > 0 && rect.top < window.innerHeight;
  }

  function goNext(track) {
    var cards = getCards(track);
    if (cards.length <= 1) {
      return;
    }
    var idx = activeIndex(track);
    scrollToIndex(track, idx >= cards.length - 1 ? 0 : idx + 1);
  }

  function goPrev(track) {
    var cards = getCards(track);
    if (cards.length <= 1) {
      return;
    }
    var idx = activeIndex(track);
    scrollToIndex(track, idx <= 0 ? cards.length - 1 : idx - 1);
  }

  function updateArrows(root, track) {
    var cards = getCards(track);
    var prev = root.querySelector(".so-news-card-slider__arrow--prev");
    var next = root.querySelector(".so-news-card-slider__arrow--next");
    if (cards.length <= 1) {
      if (prev) prev.disabled = true;
      if (next) next.disabled = true;
      return;
    }
    if (prev) prev.disabled = false;
    if (next) next.disabled = false;
  }

  function updateDots(root, track) {
    var dotsWrap = root.querySelector(".so-news-card-slider__dots");
    if (!dotsWrap) {
      return;
    }
    var dots = dotsWrap.querySelectorAll(".so-news-card-slider__dot");
    if (!dots.length) {
      return;
    }
    var idx = activeIndex(track);
    dots.forEach(function (dot, i) {
      dot.classList.toggle("is-active", i === idx);
      dot.setAttribute("aria-current", i === idx ? "true" : "false");
    });
  }

  function buildDots(root, track) {
    var dotsWrap = root.querySelector(".so-news-card-slider__dots");
    if (!dotsWrap || dotsWrap.dataset.soDotsBuilt === "1") {
      return;
    }
    dotsWrap.dataset.soDotsBuilt = "1";
    dotsWrap.innerHTML = "";
    var cards = getCards(track);
    if (cards.length <= 1) {
      dotsWrap.hidden = true;
      return;
    }
    dotsWrap.hidden = false;
    cards.forEach(function (card, i) {
      var dot = document.createElement("button");
      dot.type = "button";
      dot.className = "so-news-card-slider__dot";
      dot.setAttribute("aria-label", "Slide " + (i + 1));
      dot.addEventListener("click", function () {
        scrollToIndex(track, i);
      });
      dotsWrap.appendChild(dot);
    });
    updateDots(root, track);
  }

  function stopAutoplay(root) {
    if (root._soAutoplayTimer) {
      clearInterval(root._soAutoplayTimer);
      root._soAutoplayTimer = null;
    }
  }

  function startAutoplay(root, track) {
    stopAutoplay(root);
    var delay = autoplayDelay(root);
    if (!delay || !isMobileSlider(root)) {
      return;
    }
    root._soAutoplayTimer = setInterval(function () {
      if (root.dataset.soPaused === "1" || !isInViewport(root)) {
        return;
      }
      goNext(track);
    }, delay);
  }

  function syncAutoplay(root, track) {
    if (autoplayDelay(root) && isMobileSlider(root)) {
      startAutoplay(root, track);
    } else {
      stopAutoplay(root);
    }
  }

  function pauseAutoplay(root) {
    root.dataset.soPaused = "1";
  }

  function resumeAutoplay(root, track) {
    delete root.dataset.soPaused;
    syncAutoplay(root, track);
  }

  function initSlider(root) {
    if (!root || root.dataset.soSliderInit === "1") {
      return;
    }
    var track = getTrack(root);
    if (!track) {
      return;
    }
    root.dataset.soSliderInit = "1";

    var prev = root.querySelector(".so-news-card-slider__arrow--prev");
    var next = root.querySelector(".so-news-card-slider__arrow--next");

    if (prev) {
      prev.addEventListener("click", function () {
        goPrev(track);
      });
    }
    if (next) {
      next.addEventListener("click", function () {
        goNext(track);
      });
    }

    function onScroll() {
      updateArrows(root, track);
      updateDots(root, track);
    }

    track.addEventListener("scroll", onScroll, { passive: true });

    root.addEventListener("mouseenter", function () {
      pauseAutoplay(root);
    });
    root.addEventListener("mouseleave", function () {
      resumeAutoplay(root, track);
    });
    root.addEventListener("focusin", function () {
      pauseAutoplay(root);
    });
    root.addEventListener("focusout", function () {
      resumeAutoplay(root, track);
    });
    track.addEventListener(
      "touchstart",
      function () {
        pauseAutoplay(root);
      },
      { passive: true }
    );
    track.addEventListener(
      "touchend",
      function () {
        window.setTimeout(function () {
          resumeAutoplay(root, track);
        }, 1200);
      },
      { passive: true }
    );

    window.addEventListener(
      "resize",
      function () {
        buildDots(root, track);
        updateArrows(root, track);
        updateDots(root, track);
        syncAutoplay(root, track);
      },
      { passive: true }
    );

    buildDots(root, track);
    updateArrows(root, track);
    updateDots(root, track);
    syncAutoplay(root, track);
  }

  function initPartnersStrip(strip) {
    if (!strip || strip.dataset.soStripInit === "1") {
      return;
    }
    strip.dataset.soStripInit = "1";
    strip.classList.add("so-scrollbar-brand");

    var items = strip.querySelectorAll("a, span");
    if (items.length <= 1) {
      return;
    }

    var delay = parseInt(strip.getAttribute("data-so-autoplay") || "3000", 10);
    if (delay < 1000) {
      delay = 3000;
    }

    function step() {
      if (strip.dataset.soPaused === "1" || !isMobileSlider(strip)) {
        return;
      }
      var max = Math.max(0, strip.scrollWidth - strip.clientWidth - 2);
      if (max <= 2) {
        return;
      }
      var next = strip.scrollLeft + strip.clientWidth * 0.75;
      if (next >= max) {
        strip.scrollTo({ left: 0, behavior: "smooth" });
      } else {
        strip.scrollBy({ left: strip.clientWidth * 0.75, behavior: "smooth" });
      }
    }

    function syncStripAutoplay() {
      if (strip._soStripTimer) {
        clearInterval(strip._soStripTimer);
        strip._soStripTimer = null;
      }
      if (window.innerWidth <= MOBILE_MAX) {
        strip._soStripTimer = setInterval(step, delay);
      }
    }

    strip.addEventListener("touchstart", function () {
      strip.dataset.soPaused = "1";
    }, { passive: true });
    strip.addEventListener("touchend", function () {
      delete strip.dataset.soPaused;
    }, { passive: true });

    window.addEventListener("resize", syncStripAutoplay, { passive: true });
    syncStripAutoplay();
  }

  function initAll(scope) {
    (scope || document).querySelectorAll(".so-news-card-slider").forEach(initSlider);
    (scope || document).querySelectorAll(".sow-partners[data-so-autoplay]").forEach(initPartnersStrip);
  }

  if (document.readyState !== "loading") {
    initAll();
  } else {
    document.addEventListener("DOMContentLoaded", function () {
      initAll();
    });
  }

  if (window.elementorFrontend && window.elementorFrontend.hooks) {
    window.elementorFrontend.hooks.addAction(
      "frontend/element_ready/syria_oil_category_sections.default",
      function ($scope) {
        initAll($scope && $scope[0] ? $scope[0] : document);
      }
    );
    window.elementorFrontend.hooks.addAction(
      "frontend/element_ready/syria_oil_article_card_grid.default",
      function ($scope) {
        initAll($scope && $scope[0] ? $scope[0] : document);
      }
    );
    window.elementorFrontend.hooks.addAction(
      "frontend/element_ready/syria_oil_partners.default",
      function ($scope) {
        initAll($scope && $scope[0] ? $scope[0] : document);
      }
    );
  }
})();
