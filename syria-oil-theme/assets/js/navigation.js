/**
 * Syria Oil — responsive navigation.
 * Handles: off-canvas drawer toggle, backdrop, ESC close, bottom-bar toggle,
 * touch-friendly collapsible submenus, and mobile reparenting so the fixed
 * drawer is not trapped under .site-chrome sticky stacking.
 *
 * Must init even when LiteSpeed Delay JS runs the file after DOMContentLoaded.
 */
(function () {
  "use strict";

  var i18n = window.syriaOilNav || {};
  var navHomeParent = null;
  var navHomeNext = null;
  var booted = false;

  function byId(id) {
    return document.getElementById(id);
  }

  function isMobileNav() {
    /* Match --bp-laptop: dense headers on ~11–14" screens use the drawer. */
    return window.matchMedia("(max-width: 1280px)").matches;
  }

  function syncNavMount() {
    var nav = byId("primary-nav");
    if (!nav) return;

    if (!navHomeParent) {
      navHomeParent = nav.parentNode;
      navHomeNext = nav.nextSibling;
    }

    if (isMobileNav()) {
      if (nav.parentNode !== document.body) {
        document.body.appendChild(nav);
      }
    } else if (navHomeParent && nav.parentNode !== navHomeParent) {
      if (navHomeNext && navHomeNext.parentNode === navHomeParent) {
        navHomeParent.insertBefore(nav, navHomeNext);
      } else {
        navHomeParent.appendChild(nav);
      }
    }
  }

  function setOpen(open) {
    var nav = byId("primary-nav");
    var toggle = byId("nav-toggle");
    var toggleBottom = byId("nav-toggle-bottom");
    var backdrop = byId("nav-backdrop");
    if (!nav) return;

    if (open) {
      syncNavMount();
    }

    nav.classList.toggle("is-open", open);
    document.body.classList.toggle("nav-open", open);

    if (toggle) toggle.setAttribute("aria-expanded", String(open));
    if (toggleBottom) toggleBottom.setAttribute("aria-expanded", String(open));
    if (backdrop) {
      if (open) backdrop.removeAttribute("hidden");
      else backdrop.setAttribute("hidden", "");
    }
  }

  function toggleNav(e) {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }
    var nav = byId("primary-nav");
    if (!nav) return;
    setOpen(!nav.classList.contains("is-open"));
  }

  function closeNav() {
    setOpen(false);
  }

  function setSubmenuExpanded(item, expanded) {
    item.classList.toggle("is-expanded", expanded);
    var btn = item.querySelector(":scope > .submenu-toggle");
    if (btn) {
      btn.setAttribute("aria-expanded", String(expanded));
    }
  }

  function toggleSubmenu(item) {
    setSubmenuExpanded(item, !item.classList.contains("is-expanded"));
  }

  function buildSubmenuToggles() {
    var parents = document.querySelectorAll(".main-nav .menu-item-has-children");
    var submenuLabel = i18n.submenuToggle || "Open submenu";
    var submenuClose = i18n.submenuClose || "Close submenu";

    parents.forEach(function (item) {
      if (item.getAttribute("data-so-submenu-ready") === "1") {
        return;
      }
      item.setAttribute("data-so-submenu-ready", "1");

      var link = item.querySelector(":scope > a");
      var btn = item.querySelector(":scope > .submenu-toggle");

      if (!btn) {
        btn = document.createElement("button");
        btn.type = "button";
        btn.className = "submenu-toggle";
        btn.setAttribute("aria-expanded", "false");
        btn.innerHTML =
          '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">' +
          '<path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" ' +
          'stroke-linecap="round" stroke-linejoin="round"/></svg>';

        if (link && link.nextSibling) {
          item.insertBefore(btn, link.nextSibling);
        } else {
          item.appendChild(btn);
        }
      }

      function syncLabel() {
        var open = item.classList.contains("is-expanded");
        btn.setAttribute(
          "aria-label",
          (link ? link.textContent.trim() + " — " : "") + (open ? submenuClose : submenuLabel)
        );
      }

      btn.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        toggleSubmenu(item);
        syncLabel();
      });

      if (link) {
        link.addEventListener("click", function (e) {
          if (!isMobileNav()) {
            return;
          }
          e.preventDefault();
          toggleSubmenu(item);
          syncLabel();
        });
      }

      syncLabel();
    });
  }

  function boot() {
    if (booted) return;
    var toggle = byId("nav-toggle");
    var toggleBottom = byId("nav-toggle-bottom");
    var backdrop = byId("nav-backdrop");
    var nav = byId("primary-nav");

    /* Wait until the drawer markup exists (classic header or footer fallback). */
    if (!nav && !toggle && !toggleBottom) {
      return;
    }
    booted = true;

    syncNavMount();

    if (toggle) toggle.addEventListener("click", toggleNav);
    if (toggleBottom) toggleBottom.addEventListener("click", toggleNav);
    if (backdrop) backdrop.addEventListener("click", closeNav);

    document.querySelectorAll("[data-nav-close]").forEach(function (btn) {
      btn.addEventListener("click", closeNav);
    });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") closeNav();
    });

    if (nav) {
      nav.querySelectorAll(".sub-menu a").forEach(function (a) {
        a.addEventListener("click", function () {
          if (isMobileNav()) closeNav();
        });
      });
    }

    buildSubmenuToggles();

    document.querySelectorAll("[data-search-toggle]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var form = btn.closest(".header-search");
        var input = form ? form.querySelector('input[type="search"]') : null;
        if (!form || !input) return;
        form.classList.add("is-open");
        input.focus();
      });
    });
    document.querySelectorAll(".header-search input[type='search']").forEach(function (input) {
      input.addEventListener("blur", function () {
        var form = input.closest(".header-search");
        if (form && !input.value) {
          window.setTimeout(function () {
            form.classList.remove("is-open");
          }, 150);
        }
      });
    });

    var chrome = document.querySelector(".site-chrome");
    if (chrome) {
      var onScroll = function () {
        chrome.classList.toggle("is-scrolled", window.scrollY > 8);
      };
      onScroll();
      window.addEventListener("scroll", onScroll, { passive: true });
    }

    var mq = window.matchMedia("(min-width: 1281px)");
    var onChange = function (ev) {
      if (ev.matches) closeNav();
      syncNavMount();
    };
    if (mq.addEventListener) mq.addEventListener("change", onChange);
    else if (mq.addListener) mq.addListener(onChange);
  }

  function scheduleBoot() {
    boot();
    /* LiteSpeed Delay JS may inject/localize after first paint. */
    window.setTimeout(boot, 0);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", scheduleBoot);
  } else {
    scheduleBoot();
  }

  document.addEventListener("DOMContentLiteSpeedLoaded", scheduleBoot);
  window.addEventListener("load", scheduleBoot);
})();
