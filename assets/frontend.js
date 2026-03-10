(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		// Toggle.
		document.addEventListener('click', function (e) {
			var btn = e.target.closest('.zuno-toc__toggle');
			if (!btn) return;

			var nav = btn.closest('.zuno-toc');
			if (!nav) return;

			var body = nav.querySelector('.zuno-toc__body');
			if (!body) return;

			var isHidden = body.hidden;
			body.hidden = !isHidden;
			btn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
			btn.textContent = isHidden ? 'Skryť' : 'Zobraziť';
			nav.classList.toggle('zuno-toc--collapsed', body.hidden);
		});

		// Smooth scroll.
		document.addEventListener('click', function (e) {
			var link = e.target.closest('.zuno-toc__link');
			if (!link) return;

			var href = link.getAttribute('href');
			if (!href || href.charAt(0) !== '#') return;

			var target = document.getElementById(href.slice(1));
			if (!target) return;

			e.preventDefault();

			target.scrollIntoView({ behavior: 'smooth', block: 'start' });

			// Update URL hash without jumping.
			if (history.replaceState) {
				history.replaceState(null, '', href);
			}
		});
	});
})();
