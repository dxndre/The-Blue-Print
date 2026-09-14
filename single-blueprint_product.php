<?php
/**
 * Single Blueprint Product template.
 *
 * @package The_Blue_Print
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$product_id = get_the_ID();

	/*
	 * WordPress presentation fields.
	 */
	$shopify_handle = get_field(
		'shopify_product_handle',
		$product_id
	);

	$primary_image_id = get_field(
		'primary_image_override',
		$product_id
	);

	if ( empty( $primary_image_id ) ) {
		$primary_image_id = get_post_thumbnail_id( $product_id );
	}

	$editorial_images = array_filter(
		array(
			get_field( 'editorial_image_1', $product_id ),
			get_field( 'editorial_image_2', $product_id ),
			get_field( 'editorial_image_3', $product_id ),
			get_field( 'editorial_image_4', $product_id ),
		)
	);

	$product_information = get_field(
		'product_information_override',
		$product_id
	);

	$shipping_returns = get_field(
		'shipping_returns_override',
		$product_id
	);

	$size_guide = get_field(
		'size_guide_override',
		$product_id
	);

	/*
	 * Temporary display data.
	 *
	 * Shopify will eventually replace the price, colour, variants,
	 * availability and add-to-cart interface.
	 */
	$placeholder_colour = __( 'Black', 'the-blue-print' );
	$placeholder_price  = __( '£1,295.00', 'the-blue-print' );
	$placeholder_sizes  = array( 'XS', 'S', 'M', 'L', 'XL', 'XXL' );
	?>

	<article
		id="product-<?php the_ID(); ?>"
		<?php post_class( 'product-page' ); ?>
		<?php if ( ! empty( $shopify_handle ) ) : ?>
			data-shopify-product-handle="<?php echo esc_attr( $shopify_handle ); ?>"
		<?php endif; ?>
	>
		<section class="product-hero">
			<div class="product-hero__summary">
				<header class="product-summary__header">
					<h1 class="product-summary__title">
						<?php the_title(); ?>
					</h1>

					<p class="product-summary__meta">
						<span class="product-summary__colour">
							<?php echo esc_html( $placeholder_colour ); ?>
						</span>

						<span aria-hidden="true">//</span>

						<span class="product-summary__price">
							<?php echo esc_html( $placeholder_price ); ?>
						</span>
					</p>
				</header>

				<div class="product-summary__description">
					<?php if ( has_excerpt() ) : ?>
						<?php echo wp_kses_post( wpautop( get_the_excerpt() ) ); ?>
					<?php else : ?>
						<p>
							<?php
							esc_html_e(
								'Product information will be supplied by Shopify.',
								'the-blue-print'
							);
							?>
						</p>
					<?php endif; ?>
				</div>

				<!--
					This entire section is temporary.
					Replace it with the official Shopify product component
					once the store has been connected.
				-->
				<div
					class="product-commerce product-commerce--placeholder"
					aria-label="<?php esc_attr_e( 'Product purchasing options', 'the-blue-print' ); ?>"
				>
					<button
						class="product-commerce__button"
						type="button"
						disabled
					>
						<i
							class="fa-solid fa-bag-shopping"
							aria-hidden="true"
						></i>

						<span>
							<?php esc_html_e( 'Add to cart', 'the-blue-print' ); ?>
						</span>
					</button>

					<div class="product-commerce__options">
						<span class="product-commerce__option-label">
							<?php esc_html_e( 'Size guide', 'the-blue-print' ); ?>
						</span>

						<div
							class="product-commerce__sizes"
							aria-label="<?php esc_attr_e( 'Available sizes', 'the-blue-print' ); ?>"
						>
							<?php foreach ( $placeholder_sizes as $size ) : ?>
								<button
									class="product-commerce__size"
									type="button"
									disabled
								>
									<?php echo esc_html( $size ); ?>
								</button>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<nav
					class="product-summary__links"
					aria-label="<?php esc_attr_e( 'Product information', 'the-blue-print' ); ?>"
				>
					<?php if ( ! empty( $product_information ) ) : ?>
						<button
							class="product-summary__detail-toggle"
							type="button"
							data-product-dialog-open="product-information"
						>
							<?php esc_html_e( 'Product information', 'the-blue-print' ); ?>
						</button>
					<?php endif; ?>

					<?php if ( ! empty( $shipping_returns ) ) : ?>
						<button
							class="product-summary__detail-toggle"
							type="button"
							data-product-dialog-open="shipping-returns"
						>
							<?php esc_html_e( 'Shipping & returns', 'the-blue-print' ); ?>
						</button>
					<?php endif; ?>

					<?php if ( ! empty( $size_guide['url'] ) ) : ?>
						<a
							class="product-summary__detail-toggle"
							href="<?php echo esc_url( $size_guide['url'] ); ?>"
							<?php if ( ! empty( $size_guide['target'] ) ) : ?>
								target="<?php echo esc_attr( $size_guide['target'] ); ?>"
							<?php endif; ?>
						>
							<?php
							echo esc_html(
								$size_guide['title'] ?: __( 'Size guide', 'the-blue-print' )
							);
							?>
						</a>
					<?php endif; ?>
				</nav>
			</div>

			<div class="product-hero__media">
				<?php if ( ! empty( $primary_image_id ) ) : ?>
					<?php
					echo wp_get_attachment_image(
						$primary_image_id,
						'full',
						false,
						array(
							'class'         => 'product-hero__image',
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'sizes'         => '(min-width: 992px) 50vw, 100vw',
						)
					);
					?>
				<?php else : ?>
					<div class="product-hero__image-placeholder">
						<?php esc_html_e( 'Add a primary product image.', 'the-blue-print' ); ?>
					</div>
				<?php endif; ?>
			</div>
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
			>
				<div class="product-dialog__inner">
					<button
						class="product-dialog__close"
						type="button"
						data-product-dialog-close
						aria-label="<?php esc_attr_e( 'Close product information', 'the-blue-print' ); ?>"
					>
						<span aria-hidden="true">&times;</span>
					</button>

					<h2 class="product-dialog__title">
						<?php esc_html_e( 'Product information', 'the-blue-print' ); ?>
					</h2>

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
			>
				<div class="product-dialog__inner">
					<button
						class="product-dialog__close"
						type="button"
						data-product-dialog-close
						aria-label="<?php esc_attr_e( 'Close shipping and returns', 'the-blue-print' ); ?>"
					>
						<span aria-hidden="true">&times;</span>
					</button>

					<h2 class="product-dialog__title">
						<?php esc_html_e( 'Shipping & returns', 'the-blue-print' ); ?>
					</h2>

					<div class="product-dialog__content">
						<?php echo wp_kses_post( $shipping_returns ); ?>
					</div>
				</div>
			</dialog>
		<?php endif; ?>
	</article>

	<?php
endwhile;

get_footer();