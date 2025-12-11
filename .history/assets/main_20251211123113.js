// Webpack Imports
import * as bootstrap from 'bootstrap';

(function () {
	'use strict';

	// Focus input if Searchform is empty
	[].forEach.call(document.querySelectorAll('.search-form'), (el) => {
		el.addEventListener('submit', function (e) {
			var search = el.querySelector('input');
			if (search.value.length < 1) {
				e.preventDefault();
				search.focus();
			}
		});
	});

	// Initialize Popovers: https://getbootstrap.com/docs/5.0/components/popovers
	var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
	var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
		return new bootstrap.Popover(popoverTriggerEl, {
			trigger: 'focus',
		});
	});

	// Toggle `scrolled` class on #header when user scrolls more than 10px
	(function () {
		var headerEl = document.getElementById('header');

		function toggleHeaderScrolled() {
			if (!headerEl) return;
			if (window.scrollY > 10) {
				headerEl.classList.add('scrolled');
			} else {
				headerEl.classList.remove('scrolled');
			}
		}

		if (typeof window !== 'undefined') {
			window.addEventListener('scroll', toggleHeaderScrolled, { passive: true });

			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', toggleHeaderScrolled);
				window.addEventListener('load', toggleHeaderScrolled);
			} else {
				toggleHeaderScrolled();
			}
		}
	})();
})();


