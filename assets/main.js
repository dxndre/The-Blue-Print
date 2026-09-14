// Webpack imports
import * as bootstrap from 'bootstrap';

(function () {
	'use strict';

	/**
	 * Search form validation
	 *
	 * Focus the search input when an empty form is submitted.
	 */
	function initSearchForms() {
		document.querySelectorAll('.search-form').forEach((form) => {
			form.addEventListener('submit', (event) => {
				const searchInput = form.querySelector(
					'input[type="search"], input[name="s"]'
				);

				if (!searchInput || searchInput.value.trim().length > 0) {
					return;
				}

				event.preventDefault();
				searchInput.focus();
			});
		});
	}

	/**
	 * Bootstrap popovers
	 */
	function initPopovers() {
		document
			.querySelectorAll('[data-bs-toggle="popover"]')
			.forEach((trigger) => {
				new bootstrap.Popover(trigger, {
					trigger: 'focus',
				});
			});
	}

	/**
	 * Header scroll state
	 */
	function initHeaderScrollState() {
		const header = document.getElementById('header');

		if (!header) {
			return;
		}

		let ticking = false;

		const updateHeader = () => {
			header.classList.toggle(
				'scrolled',
				window.scrollY > 10
			);

			ticking = false;
		};

		const requestHeaderUpdate = () => {
			if (ticking) {
				return;
			}

			ticking = true;
			window.requestAnimationFrame(updateHeader);
		};

		updateHeader();

		window.addEventListener(
			'scroll',
			requestHeaderUpdate,
			{ passive: true }
		);
	}

	/**
	 * Homepage splash screen
	 */
	function initHomepageSplash() {
		const splash = document.querySelector(
			'[data-site-splash]'
		);

		if (!splash) {
			return;
		}

		const body = document.body;
		const minimumDuration = 1300;
		const failsafeDuration = 4000;
		const startedAt = performance.now();

		let dismissed = false;
		let removalTimer = null;

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
			 * Remove the element even if transitionend
			 * does not fire.
			 */
			removalTimer = window.setTimeout(
				removeSplash,
				800
			);
		};

		const dismissAfterMinimumDuration = () => {
			const elapsed = performance.now() - startedAt;
			const remaining = Math.max(
				0,
				minimumDuration - elapsed
			);

			window.setTimeout(
				dismissSplash,
				remaining
			);
		};

		body.classList.add('home-splash-active');

		splash.addEventListener(
			'transitionend',
			(event) => {
				if (
					event.target !== splash ||
					event.propertyName !== 'opacity' ||
					!splash.classList.contains('is-leaving')
				) {
					return;
				}

				window.clearTimeout(removalTimer);
				removeSplash();
			}
		);

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
		 * Prevent the splash from becoming stuck if an
		 * image or third-party resource fails to load.
		 */
		window.setTimeout(
			dismissSplash,
			failsafeDuration
		);
	}

	/**
	 * Product information dialogs
	 */
	function initProductDialogs() {
		const dialogs = document.querySelectorAll(
			'.product-dialog'
		);

		if (!dialogs.length) {
			return;
		}

		document
			.querySelectorAll('[data-product-dialog-open]')
			.forEach((button) => {
				button.addEventListener('click', () => {
					const dialogId =
						button.dataset.productDialogOpen;

					const dialog =
						document.getElementById(dialogId);

					if (
						dialog &&
						typeof dialog.showModal === 'function'
					) {
						dialog.showModal();
					}
				});
			});

		document
			.querySelectorAll('[data-product-dialog-close]')
			.forEach((button) => {
				button.addEventListener('click', () => {
					const dialog = button.closest('dialog');

					if (dialog) {
						dialog.close();
					}
				});
			});

		dialogs.forEach((dialog) => {
			/*
			 * Close when the backdrop itself is clicked,
			 * but not when the inner content is clicked.
			 */
			dialog.addEventListener('click', (event) => {
				if (event.target === dialog) {
					dialog.close();
				}
			});

			/*
			 * The browser natively handles Escape, but
			 * explicitly closing keeps the state reliable.
			 */
			dialog.addEventListener('cancel', (event) => {
				event.preventDefault();
				dialog.close();
			});
		});
	}

	/**
	 * Shopify product image sliders
	 */
	function initProductImageSliders() {
		const sliders = document.querySelectorAll(
			'[data-product-image-slider]'
		);

		sliders.forEach((slider) => {
			const mediaContainer = slider.closest(
				'.blueprint-pdp__media'
			);

			if (!mediaContainer) {
				return;
			}

			const previousButton = mediaContainer.querySelector(
				'[data-product-image-previous]'
			);

			const nextButton = mediaContainer.querySelector(
				'[data-product-image-next]'
			);

			const currentOutput = mediaContainer.querySelector(
				'[data-product-image-current]'
			);

			const totalOutput = mediaContainer.querySelector(
				'[data-product-image-total]'
			);

			const getSlides = () => {
				return Array.from(
					slider.querySelectorAll(
						'.blueprint-pdp__image-slide'
					)
				);
			};

			const updateStatus = () => {
				const slides = getSlides();

				if (!slides.length) {
					return;
				}

				const sliderBounds =
					slider.getBoundingClientRect();

				const sliderCentre =
					sliderBounds.left +
					sliderBounds.width / 2;

				let activeIndex = 0;
				let smallestDistance = Infinity;

				slides.forEach((slide, index) => {
					const slideBounds =
						slide.getBoundingClientRect();

					const slideCentre =
						slideBounds.left +
						slideBounds.width / 2;

					const distance = Math.abs(
						sliderCentre - slideCentre
					);

					if (distance < smallestDistance) {
						smallestDistance = distance;
						activeIndex = index;
					}
				});

				if (currentOutput) {
					currentOutput.textContent =
						String(activeIndex + 1);
				}

				if (totalOutput) {
					totalOutput.textContent =
						String(slides.length);
				}

				if (previousButton) {
					previousButton.disabled =
						activeIndex === 0;
				}

				if (nextButton) {
					nextButton.disabled =
						activeIndex === slides.length - 1;
				}
			};

			const moveSlider = (direction) => {
				slider.scrollBy({
					left: slider.clientWidth * direction,
					behavior: 'smooth',
				});
			};

			previousButton?.addEventListener(
				'click',
				() => moveSlider(-1)
			);

			nextButton?.addEventListener(
				'click',
				() => moveSlider(1)
			);

			let scrollTimer;

			slider.addEventListener(
				'scroll',
				() => {
					window.clearTimeout(scrollTimer);

					scrollTimer = window.setTimeout(
						updateStatus,
						80
					);
				},
				{ passive: true }
			);

			/*
			* Shopify renders its component template
			* asynchronously, so watch until the image
			* slides have appeared.
			*/
			const observer = new MutationObserver(() => {
				if (!getSlides().length) {
					return;
				}

				updateStatus();
				observer.disconnect();
			});

			observer.observe(slider, {
				childList: true,
				subtree: true,
			});

			window.addEventListener(
				'resize',
				updateStatus
			);

			updateStatus();
		});
	}

	/**
	 * Initialise the theme.
	 */
	function initTheme() {
		initSearchForms();
		initPopovers();
		initHeaderScrollState();
		initHomepageSplash();
		initProductDialogs();
		initProductImageSliders();
	}

	if (document.readyState === 'loading') {
		document.addEventListener(
			'DOMContentLoaded',
			initTheme,
			{ once: true }
		);
	} else {
		initTheme();
	}
})();