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


	/**
	 * Homepage splash screen
	 */
	function initHomepageSplash() {
		const splash = document.querySelector('[data-site-splash]');

		if (!splash) {
			return;
		}

		const body = document.body;
		const minimumDuration = 1300;
		const failsafeDuration = 4000;
		const startedAt = performance.now();

		let dismissed = false;
		let removalTimer;

		const removeSplash = () => {
			if (splash.isConnected) {
				splash.remove();
			}
		};

		const dismissSplash = () => {
			if (dismissed) {
				return;
			}

			dismissed = true;

			splash.classList.add('is-leaving');
			body.classList.remove('home-splash-active');

			/*
			* Fallback removal in case transitionend does not fire.
			*/
			removalTimer = window.setTimeout(removeSplash, 800);
		};

		const dismissAfterMinimumDuration = () => {
			const elapsed = performance.now() - startedAt;
			const remaining = Math.max(0, minimumDuration - elapsed);

			window.setTimeout(dismissSplash, remaining);
		};

		body.classList.add('home-splash-active');

		splash.addEventListener('transitionend', (event) => {
			if (
				event.target === splash &&
				event.propertyName === 'opacity' &&
				splash.classList.contains('is-leaving')
			) {
				window.clearTimeout(removalTimer);
				removeSplash();
			}
		});

		if (document.readyState === 'complete') {
			dismissAfterMinimumDuration();
		} else {
			window.addEventListener(
				'load',
				dismissAfterMinimumDuration,
				{ once: true }
			);
		}

		/*
		* Prevent the splash from getting stuck if the load event
		* is delayed by a failed image or third-party resource.
		*/
		window.setTimeout(dismissSplash, failsafeDuration);
	}

	/*
	* This works whether main.js is loaded in the document head
	* or at the end of the body.
	*/
	if (document.readyState === 'loading') {
		document.addEventListener(
			'DOMContentLoaded',
			initHomepageSplash,
			{ once: true }
		);
	} else {
		initHomepageSplash();
	}
})();


