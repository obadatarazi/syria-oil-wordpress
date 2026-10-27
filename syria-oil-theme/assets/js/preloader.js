(function () {
	'use strict';
	var el = document.getElementById('syria-oil-preloader');
	if (!el) {
		return;
	}
	function hide() {
		el.classList.add('is-done');
		window.setTimeout(function () {
			if (el.parentNode) {
				el.parentNode.removeChild(el);
			}
		}, 400);
	}
	if (document.readyState === 'complete') {
		hide();
	} else {
		window.addEventListener('load', hide);
	}
})();
