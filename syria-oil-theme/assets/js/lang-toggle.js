/**
 * UI-only AR/EN language toggle (no content swap).
 */
(function () {
  "use strict";

  function setActive(root, lang) {
    var buttons = root.querySelectorAll(".lang-switch__btn");
    buttons.forEach(function (btn) {
      var active = (btn.getAttribute("data-lang") || "") === lang;
      btn.classList.toggle("is-active", active);
      btn.setAttribute("aria-pressed", active ? "true" : "false");
    });
    document.documentElement.setAttribute("data-syria-oil-lang", lang);
  }

  function initSwitch(root) {
    if (!root || root.getAttribute("data-bound") === "1") return;
    root.setAttribute("data-bound", "1");

    root.querySelectorAll(".lang-switch__btn").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var lang = btn.getAttribute("data-lang") || "ar";
        setActive(root, lang);
        try {
          window.localStorage.setItem("syriaOilLangUi", lang);
        } catch (e) {
          /* ignore */
        }
      });
    });

    try {
      var stored = window.localStorage.getItem("syriaOilLangUi");
      if (stored === "en" || stored === "ar") {
        setActive(root, stored);
      }
    } catch (e) {
      /* ignore */
    }
  }

  function boot() {
    document.querySelectorAll("[data-syria-oil-lang-switch]").forEach(initSwitch);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
})();
