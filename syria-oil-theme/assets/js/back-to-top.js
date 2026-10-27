(function () {
	'use strict';
	var btn = document.getElementById('syria-oil-back-to-top');
	if (!btn) {
		return;
	}
	var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var lastY = window.scrollY || 0;
	var ticking = false;

	function update() {
		var y = window.scrollY || 0;
		var goingDown = y > lastY + 4;
		var goingUp = y < lastY - 4;
		btn.classList.toggle('is-visible', y > 400);
		if (goingDown && y > 400) {
			btn.classList.add('is-scrolling-down');
		} else if (goingUp || y <= 400) {
			btn.classList.remove('is-scrolling-down');
		}
		lastY = y;
		ticking = false;
	}

	window.addEventListener(
		'scroll',
		function () {
			if (!ticking) {
				window.requestAnimationFrame(update);
				ticking = true;
			}
		},
		{ passive: true }
	);
	update();

	btn.addEventListener('click', function () {
		window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
	});
})();
