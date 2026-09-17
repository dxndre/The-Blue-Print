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
 	* Enhance the standalone Shopify cart.
	*
	* Shopify's cart line items are rendered inside an open Shadow DOM.
	* This fills each image container and links the image and title to
	* the corresponding WordPress Shopify product route.
	*/
	function initStandaloneCartEnhancements() {
		const cart = document.getElementById('cart-page-display');

		if (!cart) {
			return;
		}

		const productHandleOverrides = {
			/*
			* Add entries here only when a Shopify handle does not
			* match the slugified product title.
			*
			* Example:
			* 'My Product Name': 'different-shopify-handle',
			*/
		};

		const getProductHandle = (productTitle) => {
			if (productHandleOverrides[productTitle]) {
				return productHandleOverrides[productTitle];
			}

			return productTitle
				.toLowerCase()
				.normalize('NFKD')
				.replace(/[\u0300-\u036f]/g, '')
				.replace(/&/g, 'and')
				.replace(/[^a-z0-9]+/g, '-')
				.replace(/^-+|-+$/g, '');
		};

		const getSiteUrl = () => {
			const homeLink = document.querySelector(
				'#header .header-logo'
			);

			if (homeLink instanceof HTMLAnchorElement) {
				return homeLink.href.endsWith('/')
					? homeLink.href
					: `${homeLink.href}/`;
			}

			return `${window.location.origin}/`;
		};

		const createProductUrl = (productTitle) => {
			const productHandle = getProductHandle(productTitle);

			return new URL(
				`products/${productHandle}/`,
				getSiteUrl()
			).href;
		};

		const addShadowStyles = (shadowRoot) => {
			if (
				shadowRoot.querySelector(
					'[data-blueprint-cart-styles]'
				)
			) {
				return;
			}

			const style = document.createElement('style');

			style.dataset.blueprintCartStyles = '';

			style.textContent = `
				.line-image {
					overflow: hidden !important;
					border-radius: 0 !important;
				}

				.line-image > .blueprint-cart-product-link {
					display: block;
					width: 100%;
					height: 100%;
					overflow: hidden;
					border-radius: 0;
				}

				.line-image img {
					display: block !important;
					width: 100% !important;
					min-width: 100% !important;
					max-width: none !important;
					height: 100% !important;
					min-height: 100% !important;
					margin: 0 !important;
					border-radius: 0 !important;
					object-fit: cover !important;
					object-position: center !important;
				}

				.line-heading > .blueprint-cart-product-link {
					color: inherit;
					font: inherit;
					line-height: inherit;
					text-decoration: none;
				}

				.line-heading > .blueprint-cart-product-link:hover {
					text-decoration: underline;
					text-underline-offset: 0.2em;
				}

				.line-heading > .blueprint-cart-product-link:focus-visible,
				.line-image > .blueprint-cart-product-link:focus-visible {
					outline: 2px solid currentColor;
					outline-offset: 2px;
				}
			`;

			shadowRoot.appendChild(style);
		};

		const wrapContentsWithLink = (
			element,
			productUrl,
			accessibleLabel
		) => {
			if (
				element.querySelector(
					':scope > .blueprint-cart-product-link'
				)
			) {
				return;
			}

			const link = document.createElement('a');

			link.className = 'blueprint-cart-product-link';
			link.href = productUrl;

			if (accessibleLabel) {
				link.setAttribute(
					'aria-label',
					accessibleLabel
				);
			}

			while (element.firstChild) {
				link.appendChild(element.firstChild);
			}

			element.appendChild(link);
		};

		const enhanceLineItems = () => {
			const shadowRoot = cart.shadowRoot;

			if (!shadowRoot) {
				return false;
			}

			addShadowStyles(shadowRoot);

			const lineItems = shadowRoot.querySelectorAll(
				'.line-item-container'
			);

			lineItems.forEach((lineItem) => {
				const heading = lineItem.querySelector(
					'.line-heading'
				);

				const image = lineItem.querySelector(
					'.line-image'
				);

				if (!heading) {
					return;
				}

				const productTitle =
					heading.textContent.trim();

				if (!productTitle) {
					return;
				}

				const productUrl =
					createProductUrl(productTitle);

				if (image) {
					wrapContentsWithLink(
						image,
						productUrl,
						`View ${productTitle}`
					);
				}

				wrapContentsWithLink(
					heading,
					productUrl,
					''
				);
			});

			return true;
		};

		const observeCart = () => {
			const shadowRoot = cart.shadowRoot;

			if (!shadowRoot) {
				window.requestAnimationFrame(observeCart);
				return;
			}

			enhanceLineItems();

			const observer = new MutationObserver(() => {
				enhanceLineItems();
			});

			observer.observe(shadowRoot, {
				childList: true,
				subtree: true,
			});
		};

		if (
			window.customElements &&
			typeof window.customElements.whenDefined === 'function'
		) {
			window.customElements
				.whenDefined('shopify-cart')
				.then(observeCart);
		} else {
			observeCart();
		}
	}

	/**
	 * Shopify product search
	 */
	function initProductSearch() {
		const dialog = document.getElementById('productSearchDialog');
		const input = document.getElementById('productSearchInput');

		if (!dialog || !input) {
			return;
		}

		const results = dialog.querySelector('[data-product-search-results]');
		const status = dialog.querySelector('[data-product-search-status]');
		const openButtons = document.querySelectorAll(
			'.js-product-search-toggle > a, a.js-product-search-toggle'
		);
		const closeButtons = dialog.querySelectorAll(
			'[data-product-search-close]'
		);

		const getProductCards = () => {
			return Array.from(
				dialog.querySelectorAll('.product-search-card')
			);
		};

		const resetSearch = () => {
			input.value = '';

			getProductCards().forEach((card) => {
				card.hidden = true;
			});

			status.textContent = 'Start typing to search products.';
		};

		const filterProducts = () => {
			const searchTerm = input.value.trim().toLowerCase();
			const cards = getProductCards();

			if (!searchTerm) {
				cards.forEach((card) => {
					card.hidden = true;
				});

				status.textContent = 'Start typing to search products.';
				return;
			}

			let matchCount = 0;

			cards.forEach((card) => {
				const titleElement = card.querySelector(
					'[data-product-search-title]'
				);

				const productTitle = titleElement
					? titleElement.textContent.trim().toLowerCase()
					: '';

				const isMatch = productTitle.includes(searchTerm);

				card.hidden = !isMatch;

				if (isMatch) {
					matchCount += 1;
				}
			});

			if (matchCount === 0) {
				status.textContent = 'No products found.';
			} else if (matchCount === 1) {
				status.textContent = '1 product found.';
			} else {
				status.textContent = `${matchCount} products found.`;
			}
		};

		const openSearch = () => {
			if (typeof dialog.showModal === 'function') {
				dialog.showModal();
			} else {
				dialog.setAttribute('open', '');
			}

			document.body.classList.add('product-search-open');

			const mobileMenu = document.getElementById('navbar');

			if (mobileMenu && mobileMenu.classList.contains('show')) {
				bootstrap.Collapse.getOrCreateInstance(mobileMenu).hide();
			}

			window.setTimeout(() => {
				input.focus();
			}, 50);
		};

		const closeSearch = () => {
			if (typeof dialog.close === 'function') {
				dialog.close();
			} else {
				dialog.removeAttribute('open');
			}

			document.body.classList.remove('product-search-open');
			resetSearch();
		};

		openButtons.forEach((button) => {
			button.addEventListener('click', (event) => {
				event.preventDefault();
				openSearch();
			});
		});

		closeButtons.forEach((button) => {
			button.addEventListener('click', closeSearch);
		});

		input.addEventListener('input', filterProducts);

		dialog.addEventListener('click', (event) => {
			if (event.target === dialog) {
				closeSearch();
			}
		});

		dialog.addEventListener('close', () => {
			document.body.classList.remove('product-search-open');
			resetSearch();
		});

		/*
		* Shopify renders product cards asynchronously.
		* Reapply the current search when new cards appear.
		*/
		if (results) {
			const observer = new MutationObserver(() => {
				filterProducts();
			});

			observer.observe(results, {
				childList: true,
				subtree: true,
			});
		}

		resetSearch();
	}

	/**
	 * Remove image rounding inside Shopify's cart shadow DOM.
	 */
	function initShopifyCartImageStyles() {
		const applyCartStyles = () => {
			const cart = document.querySelector('shopify-cart');

			if (!cart || !cart.shadowRoot) {
				return false;
			}

			if (cart.shadowRoot.getElementById('blueprintCartImageStyles')) {
				return true;
			}

			const style = document.createElement('style');

			style.id = 'blueprintCartImageStyles';

			style.textContent = `
				.line-image,
				.line-image.img-placeholder,
				.line-image img {
					border-radius: 0 !important;
					clip-path: none !important;
				}

				.line-image {
					overflow: hidden !important;
				}

				.line-image img {
					display: block !important;
					width: 100% !important;
					height: 100% !important;
					object-fit: cover !important;
				}
			`;

			cart.shadowRoot.appendChild(style);

			return true;
		};

		let attempts = 0;
		const maximumAttempts = 100;

		const waitForCart = () => {
			attempts += 1;

			if (applyCartStyles() || attempts >= maximumAttempts) {
				return;
			}

			window.setTimeout(waitForCart, 100);
		};

		if (
			window.customElements &&
			typeof window.customElements.whenDefined === 'function'
		) {
			window.customElements
				.whenDefined('shopify-cart')
				.then(waitForCart);
		} else {
			waitForCart();
		}
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
		initStandaloneCartEnhancements();
		initProductSearch();
		initShopifyCartImageStyles();
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
