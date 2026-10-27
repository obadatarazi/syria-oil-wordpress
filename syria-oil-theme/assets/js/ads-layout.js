/**
 * Side Ads layout — same Premium / Normal / Basic math on every page.
 *
 * - Measures the page hero (or first top section) and sets --ad-normal-offset
 *   so desktop rail "Normal" starts near the end of that block.
 * - Moves the mobile Normal strip to sit right after that same block.
 */
(function () {
	'use strict';

	var HERO_SELECTORS = [
		'.home-hero',
		'.page-hero',
		'.sow-page-hero',
		'.article-hero',
		'.article-hero-img',
		'.elementor-widget-syria_oil_featured_news_slider',
		'.elementor-widget-syria_oil_page_hero',
		'main .elementor-top-section',
		'main .elementor > .elementor-element:first-child',
		'#main > .section:first-of-type',
		'#main > section:first-of-type'
	].join(',');

	function findHeroRaw() {
		var main = document.getElementById('main') || document.querySelector('main');
		if (!main) {
			return document.querySelector(HERO_SELECTORS);
		}
		var nodes = main.querySelectorAll(HERO_SELECTORS);
		return nodes.length ? nodes[0] : null;
	}

	/**
	 * Prefer the full Elementor/top section wrapping the hero widget.
	 */
	function resolveHeroAnchor(el) {
		if (!el) {
			return null;
		}
		var section = el.closest(
			'.home-hero, .page-hero, .sow-page-hero, .article-hero, .elementor-top-section, .elementor-section.elementor-top-section, section.section, section'
		);
		if (section) {
			return section;
		}
		return el;
	}

	function placeNormalStrip(hero) {
		var strip = document.querySelector('.site-shell__mobile-ads--normal, .site-shell__ad-strip--normal');
		if (!strip || !hero || !hero.parentNode) {
			return;
		}
		if (strip.previousElementSibling === hero) {
			return;
		}
		hero.insertAdjacentElement('afterend', strip);
	}

	function updateNormalOffset(hero) {
		var shell = document.querySelector('.site-shell');
		if (!shell) {
			return;
		}

		var offset = Math.round(Math.min(window.innerHeight * 0.42, 480));
		if (hero) {
			var shellTop = shell.getBoundingClientRect().top + window.scrollY;
			var heroBottom = hero.getBoundingClientRect().bottom + window.scrollY;
			var rail = document.querySelector('.site-shell__rail');
			var railMargin = 44;
			if (rail) {
				var mt = parseFloat(window.getComputedStyle(rail).marginTop);
				if (!isNaN(mt)) {
					railMargin = mt;
				}
			}
			// Start Normal a bit before the hero fully ends (same on every page).
			offset = Math.round(heroBottom - shellTop - railMargin - 28);
			offset = Math.max(96, Math.min(offset, 1400));
		}

		document.documentElement.style.setProperty('--ad-normal-offset', offset + 'px');
	}

	function run() {
		var hero = resolveHeroAnchor(findHeroRaw());
		placeNormalStrip(hero);
		updateNormalOffset(hero);
	}

	var scheduled = false;
	function schedule() {
		if (scheduled) {
			return;
		}
		scheduled = true;
		window.requestAnimationFrame(function () {
			scheduled = false;
			run();
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', run);
	} else {
		run();
	}

	window.addEventListener('load', schedule);
	window.addEventListener('resize', schedule);

	if (typeof ResizeObserver !== 'undefined') {
		var ro = new ResizeObserver(schedule);
		ro.observe(document.documentElement);
		document.addEventListener('DOMContentLoaded', function () {
			var hero = resolveHeroAnchor(findHeroRaw());
			if (hero) {
				ro.observe(hero);
			}
			var shell = document.querySelector('.site-shell');
			if (shell) {
				ro.observe(shell);
			}
		});
	}
})();
