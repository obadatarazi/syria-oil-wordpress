/**
 * Syria Oil — Featured News Slider.
 * Dependency-free carousel (no React / no Swiper). Supports autoplay, arrows,
 * dots, keyboard, touch swipe, Ken Burns, content rise, progress bar,
 * and respects prefers-reduced-motion.
 */
(function () {
  "use strict";

  var prefersReduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  function restartAnim(el) {
    if (!el) return;
    el.style.animation = "none";
    // Force reflow so the next animation frame restarts cleanly.
    void el.offsetWidth;
    el.style.animation = "";
  }

  function initSlider(root) {
    if (!root || root.dataset.soSliderInit === "1") {
      return;
    }
    root.dataset.soSliderInit = "1";

    var slides = Array.prototype.slice.call(root.querySelectorAll(".so-slider__slide"));
    var dots = Array.prototype.slice.call(root.querySelectorAll(".so-slider__dot"));
    var prev = root.querySelector(".so-slider__arrow--prev");
    var next = root.querySelector(".so-slider__arrow--next");
    var progress = root.querySelector(".so-slider__progress-bar");

    var delay = parseInt(root.getAttribute("data-delay"), 10) || 6000;
    if (delay < 2000) {
      delay = 6000;
    }
    root.style.setProperty("--so-slider-delay", delay + "ms");

    function pulseActive(slide) {
      if (!slide || prefersReduced) return;
      var mediaImg = slide.querySelector(".so-slider__media img");
      var contentKids = slide.querySelectorAll(".so-slider__content > *");
      restartAnim(mediaImg);
      for (var i = 0; i < contentKids.length; i++) {
        restartAnim(contentKids[i]);
      }
    }

    function restartProgress() {
      if (!progress || prefersReduced) return;
      restartAnim(progress);
    }

    if (slides.length <= 1) {
      if (slides[0]) {
        slides[0].classList.add("is-active");
        pulseActive(slides[0]);
      }
      root.classList.remove("is-autoplay");
      return;
    }

    var index = 0;
    var timer = null;
    var rail = root.closest(".so-hero-split");
    // Default ON when attribute missing (older Elementor saves).
    var autoplayAttr = root.getAttribute("data-autoplay");
    var autoplay = autoplayAttr !== "0" && !prefersReduced;

    function setRailActive(idx) {
      if (!rail) return;
      rail.querySelectorAll(".so-hero-split__item").forEach(function (li, n) {
        li.classList.toggle("is-active", n === idx);
      });
      rail.querySelectorAll(".so-hero-split__btn").forEach(function (b, n) {
        b.setAttribute("aria-selected", n === idx ? "true" : "false");
      });
    }

    function show(i) {
      index = (i + slides.length) % slides.length;
      slides.forEach(function (s, n) {
        s.classList.toggle("is-active", n === index);
      });
      dots.forEach(function (d, n) {
        d.classList.toggle("is-active", n === index);
        d.setAttribute("aria-selected", String(n === index));
      });
      setRailActive(index);
      pulseActive(slides[index]);
      if (autoplay) {
        restartProgress();
      }
    }

    function nextSlide() {
      show(index + 1);
    }
    function prevSlide() {
      show(index - 1);
    }

    function start() {
      if (!autoplay) return;
      stop();
      timer = window.setInterval(nextSlide, delay);
      root.classList.add("is-autoplay");
      restartProgress();
    }
    function stop() {
      if (timer) {
        window.clearInterval(timer);
        timer = null;
      }
      root.classList.remove("is-autoplay");
      if (progress) {
        progress.style.animation = "none";
      }
    }

    if (next) {
      next.addEventListener("click", function () {
        nextSlide();
        start();
      });
    }
    if (prev) {
      prev.addEventListener("click", function () {
        prevSlide();
        start();
      });
    }

    dots.forEach(function (dot) {
      dot.addEventListener("click", function () {
        show(parseInt(dot.getAttribute("data-index"), 10) || 0);
        start();
      });
    });

    var rail = root.closest(".so-hero-split");
    if (rail) {
      rail.querySelectorAll(".so-hero-split__btn").forEach(function (btn) {
        btn.addEventListener("click", function () {
          show(parseInt(btn.getAttribute("data-index"), 10) || 0);
          start();
        });
      });
    }

    root.setAttribute("tabindex", "0");
    root.addEventListener("keydown", function (e) {
      if (e.key === "ArrowRight") {
        nextSlide();
        start();
      } else if (e.key === "ArrowLeft") {
        prevSlide();
        start();
      }
    });

    root.addEventListener("mouseenter", stop);
    root.addEventListener("mouseleave", start);
    root.addEventListener("focusin", stop);
    root.addEventListener("focusout", start);

    var startX = 0;
    var deltaX = 0;
    root.addEventListener(
      "touchstart",
      function (e) {
        startX = e.touches[0].clientX;
        deltaX = 0;
        stop();
      },
      { passive: true }
    );
    root.addEventListener(
      "touchmove",
      function (e) {
        deltaX = e.touches[0].clientX - startX;
      },
      { passive: true }
    );
    root.addEventListener("touchend", function () {
      if (Math.abs(deltaX) > 40) {
        if (deltaX < 0) nextSlide();
        else prevSlide();
      }
      start();
    });

    document.addEventListener("visibilitychange", function () {
      if (document.hidden) stop();
      else start();
    });

    show(0);
    start();
  }

  function initAll() {
    document.querySelectorAll(".so-slider").forEach(initSlider);
  }

  if (document.readyState !== "loading") initAll();
  else document.addEventListener("DOMContentLoaded", initAll);

  if (window.elementorFrontend && window.elementorFrontend.hooks) {
    window.elementorFrontend.hooks.addAction(
      "frontend/element_ready/syria_oil_featured_news_slider.default",
      function ($scope) {
        var root = $scope && $scope[0] ? $scope[0].querySelector(".so-slider") : null;
        if (root) {
          root.dataset.soSliderInit = "";
          initSlider(root);
        }
      }
    );
  }
})();
