<?php
/**
 * Custom Shopify Product Detail Page.
 *
 * Shopify supplies:
 * - Product title
 * - Price
 * - Description
 * - Product image
 * - Variants
 * - Availability
 * - Add-to-cart functionality
 *
 * WordPress/ACF supplies:
 * - Editorial gallery
 * - Product information
 * - Shipping and returns
 * - Size-guide link
 *
 * @package The_Blue_Print
 */

defined( 'ABSPATH' ) || exit;

/*
 * The Shopify plugin supplies $product_handle.
 */
$product_handle = isset( $product_handle )
	? sanitize_title( $product_handle )
	: '';

/*
 * Find the matching WordPress presentation record.
 *
 * The Shopify product handle entered in ACF must exactly match
 * the handle supplied by Shopify.
 */
$presentation_posts = array();

if ( ! empty( $product_handle ) ) {
	$presentation_posts = get_posts(
		array(
			'post_type'              => 'blueprint_product',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
			'meta_query'             => array(
				array(
					'key'     => 'shopify_product_handle',
					'value'   => $product_handle,
					'compare' => '=',
				),
			),
		)
	);
}

$presentation_id = ! empty( $presentation_posts )
	? (int) $presentation_posts[0]
	: 0;

/*
 * ACF presentation values.
 */
$editorial_images   = array();
$product_information = '';
$shipping_returns    = '';
$size_guide          = array();

if ( $presentation_id && function_exists( 'get_field' ) ) {
	$editorial_images = array_filter(
		array(
			get_field( 'editorial_image_1', $presentation_id ),
			get_field( 'editorial_image_2', $presentation_id ),
			get_field( 'editorial_image_3', $presentation_id ),
			get_field( 'editorial_image_4', $presentation_id ),
		)
	);

	$product_information = get_field(
		'product_information_override',
		$presentation_id
	);

	$shipping_returns = get_field(
		'shipping_returns_override',
		$presentation_id
	);

	$size_guide = get_field(
		'size_guide_override',
		$presentation_id
	);
}

/*
 * Normalise ACF Image fields.
 *
 * This supports Image ID and Image Array return formats.
 */
$normalise_image_id = static function ( $image ) {
	if ( is_array( $image ) && ! empty( $image['ID'] ) ) {
		return (int) $image['ID'];
	}

	return absint( $image );
};

$editorial_images = array_values(
	array_filter(
		array_map(
			$normalise_image_id,
			$editorial_images
		)
	)
);

/*
 * Normalise the ACF Link field.
 */
if ( is_string( $size_guide ) && ! empty( $size_guide ) ) {
	$size_guide = array(
		'url'    => $size_guide,
		'title'  => __( 'Size guide', 'the-blue-print' ),
		'target' => '',
	);
}

$has_supporting_information = (
	! empty( $product_information ) ||
	! empty( $shipping_returns ) ||
	! empty( $size_guide['url'] )
);
?>

<div
	class="blueprint-pdp"
	data-product-handle="<?php echo esc_attr( $product_handle ); ?>"
>
	<section class="blueprint-pdp__hero">
		<shopify-context
			type="product"
			handle="<?php echo esc_attr( $product_handle ); ?>"
		>
			<template>
				<div class="blueprint-pdp__layout">
					<div class="blueprint-pdp__summary">
						<div class="blueprint-pdp__heading">
							<h1 class="blueprint-pdp__title">
								<shopify-data query="product.title"></shopify-data>
							</h1>

							<div class="blueprint-pdp__price">
								<shopify-money
									query="product.selectedOrFirstAvailableVariant.price"
								></shopify-money>

								<shopify-money
									class="blueprint-pdp__compare-price"
									query="product.selectedOrFirstAvailableVariant.compareAtPrice"
								></shopify-money>
							</div>
						</div>

						<div class="blueprint-pdp__description">
							<shopify-data
								query="product.descriptionHtml"
							></shopify-data>
						</div>

						<div class="blueprint-pdp__purchase">
							<div class="blueprint-pdp__variants">
								<shopify-variant-selector>
								</shopify-variant-selector>
							</div>

							<button
								class="blueprint-pdp__add-button"
								type="button"
								onclick="getElementById('cart').addLine(event); getElementById('cart').showModal();"
								shopify-attr--disabled="!product.selectedOrFirstAvailableVariant.product.availableForSale"
							>
								<i
									class="fa-solid fa-bag-shopping"
									aria-hidden="true"
								></i>

								<span>
									<?php
									esc_html_e(
										'Add to cart',
										'the-blue-print'
									);
									?>
								</span>
							</button>
						</div>

						<?php if ( $has_supporting_information ) : ?>
							<nav
								class="blueprint-pdp__information-links"
								aria-label="<?php esc_attr_e( 'Product information', 'the-blue-print' ); ?>"
							>
								<?php if ( ! empty( $size_guide['url'] ) ) : ?>
									<a
										class="blueprint-pdp__information-link"
										href="<?php echo esc_url( $size_guide['url'] ); ?>"
										<?php if ( ! empty( $size_guide['target'] ) ) : ?>
											target="<?php echo esc_attr( $size_guide['target'] ); ?>"
										<?php endif; ?>
										<?php if ( '_blank' === ( $size_guide['target'] ?? '' ) ) : ?>
											rel="noopener noreferrer"
										<?php endif; ?>
									>
										<?php
										echo esc_html(
											$size_guide['title']
												?: __( 'Size guide', 'the-blue-print' )
										);
										?>
									</a>
								<?php endif; ?>

								<?php if ( ! empty( $product_information ) ) : ?>
									<button
										class="blueprint-pdp__information-link"
										type="button"
										data-product-dialog-open="product-information"
									>
										<?php
										esc_html_e(
											'Product information',
											'the-blue-print'
										);
										?>
									</button>
								<?php endif; ?>

								<?php if ( ! empty( $shipping_returns ) ) : ?>
									<button
										class="blueprint-pdp__information-link"
										type="button"
										data-product-dialog-open="shipping-returns"
									>
										<?php
										esc_html_e(
											'Shipping & returns',
											'the-blue-print'
										);
										?>
									</button>
								<?php endif; ?>
							</nav>
						<?php endif; ?>
					</div>

					<div class="blueprint-pdp__media">
                        <div
                            class="blueprint-pdp__image-slider"
                            data-product-image-slider
                        >
                            <shopify-list-context
                                type="image"
                                query="product.selectedOrFirstAvailableVariant.product.images"
                                first="20"
                            >
                                <template>
                                    <figure class="blueprint-pdp__image-slide">
                                        <shopify-media
                                            class="blueprint-pdp__shopify-media"
                                            layout="fullWidth"
                                            aspect-ratio="1"
                                            query="image"
                                            sizes="(min-width: 992px) 50vw, 100vw"
                                        ></shopify-media>
                                    </figure>
                                </template>
                            </shopify-list-context>
                        </div>

                        <div class="blueprint-pdp__image-controls">
                            <button
                                class="blueprint-pdp__image-control blueprint-pdp__image-control--previous"
                                type="button"
                                data-product-image-previous
                                aria-label="<?php esc_attr_e( 'Previous product image', 'the-blue-print' ); ?>"
                            >
                                <span aria-hidden="true">&larr;</span>
                            </button>

                            <p class="blueprint-pdp__image-status" aria-live="polite">
                                <span data-product-image-current>1</span>
                                <span aria-hidden="true"> / </span>
                                <span data-product-image-total>1</span>
                            </p>

                            <button
                                class="blueprint-pdp__image-control blueprint-pdp__image-control--next"
                                type="button"
                                data-product-image-next
                                aria-label="<?php esc_attr_e( 'Next product image', 'the-blue-print' ); ?>"
                            >
                                <span aria-hidden="true">&rarr;</span>
                            </button>
                        </div>
                    </div>
				</div>
			</template>
		</shopify-context>
	</section>

	<?php if ( ! empty( $editorial_images ) ) : ?>
		<section
			class="product-gallery"
			aria-label="<?php esc_attr_e( 'Product editorial gallery', 'the-blue-print' ); ?>"
		>
			<?php foreach ( $editorial_images as $index => $image_id ) : ?>
				<figure class="product-gallery__item">
					<?php
					echo wp_get_attachment_image(
						$image_id,
						'full',
						false,
						array(
							'class'   => 'product-gallery__image',
							'loading' => 0 === $index ? 'eager' : 'lazy',
							'sizes'   => '(min-width: 768px) 50vw, 100vw',
						)
					);
					?>
				</figure>
			<?php endforeach; ?>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $product_information ) ) : ?>
		<dialog
			class="product-dialog"
			id="product-information"
			aria-labelledby="product-information-title"
		>
			<div class="product-dialog__inner">
				<header class="product-dialog__header">
					<h2
						class="product-dialog__title"
						id="product-information-title"
					>
						<?php
						esc_html_e(
							'Product information',
							'the-blue-print'
						);
						?>
					</h2>

					<button
						class="product-dialog__close"
						type="button"
						data-product-dialog-close
						aria-label="<?php esc_attr_e( 'Close product information', 'the-blue-print' ); ?>"
					>
						<span aria-hidden="true">&times;</span>
					</button>
				</header>

				<div class="product-dialog__content">
					<?php echo wp_kses_post( $product_information ); ?>
				</div>
			</div>
		</dialog>
	<?php endif; ?>

	<?php if ( ! empty( $shipping_returns ) ) : ?>
		<dialog
			class="product-dialog"
			id="shipping-returns"
			aria-labelledby="shipping-returns-title"
		>
			<div class="product-dialog__inner">
				<header class="product-dialog__header">
					<h2
						class="product-dialog__title"
						id="shipping-returns-title"
					>
						<?php
						esc_html_e(
							'Shipping & returns',
							'the-blue-print'
						);
						?>
					</h2>

					<button
						class="product-dialog__close"
						type="button"
						data-product-dialog-close
						aria-label="<?php esc_attr_e( 'Close shipping and returns', 'the-blue-print' ); ?>"
					>
						<span aria-hidden="true">&times;</span>
					</button>
				</header>

				<div class="product-dialog__content">
					<?php echo wp_kses_post( $shipping_returns ); ?>
				</div>
			</div>
		</dialog>
	<?php endif; ?>
</div>