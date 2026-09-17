<?php
/**
 * Website access-gate template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gate_background = get_theme_mod( 'access_gate_background' );
$header_logo     = get_theme_mod( 'header_logo' );
$gate_error      = isset( $GLOBALS['tbp_gate_error'] )
	? $GLOBALS['tbp_gate_error']
	: '';

if ( empty( $gate_background ) ) {
	$gate_background = get_template_directory_uri() . '/assets/access-gate.webp';
}

$redirect_url = tbp_gate_requested_url();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow, noarchive">

	<?php wp_head(); ?>
</head>

<body <?php body_class( 'access-gate-page' ); ?>>

<?php wp_body_open(); ?>

<main class="access-gate">
	<img
		class="access-gate__background"
		src="<?php echo esc_url( $gate_background ); ?>"
		alt=""
		aria-hidden="true"
	/>

	<div class="access-gate__overlay" aria-hidden="true"></div>

	<div class="access-gate__content">
		<?php if ( ! empty( $header_logo ) ) : ?>
			<img
				class="access-gate__logo"
				src="<?php echo esc_url( $header_logo ); ?>"
				alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"
			/>
		<?php else : ?>
			<h1 class="access-gate__site-name">
				<?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?>
			</h1>
		<?php endif; ?>

		<form
			class="access-gate__form"
			method="post"
			action=""
			novalidate
		>
			<?php wp_nonce_field( 'tbp_unlock_site', 'tbp_gate_nonce' ); ?>

			<input
				type="hidden"
				name="tbp_gate_action"
				value="unlock"
			/>

			<input
				type="hidden"
				name="redirect_to"
				value="<?php echo esc_url( $redirect_url ); ?>"
			/>

			<label
				class="visually-hidden"
				for="accessGateEmail"
			>
				<?php esc_html_e( 'Email address', 'the-blue-print' ); ?>
			</label>

			<div class="access-gate__field">
				<input
					id="accessGateEmail"
					class="access-gate__input"
					type="email"
					name="access_email"
					placeholder="<?php esc_attr_e( 'Enter email address', 'the-blue-print' ); ?>"
					inputmode="email"
					autocomplete="email"
					autocapitalize="none"
					spellcheck="false"
					required
					autofocus
				/>

				<button
					class="access-gate__submit"
					type="submit"
					aria-label="<?php esc_attr_e( 'Enter website', 'the-blue-print' ); ?>"
				>
					<span><?php esc_html_e( 'Enter', 'the-blue-print' ); ?></span>
					<i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
				</button>
			</div>

			<?php if ( ! empty( $gate_error ) ) : ?>
				<p
					class="access-gate__message access-gate__message--error"
					role="alert"
				>
					<?php echo esc_html( $gate_error ); ?>
				</p>
			<?php else : ?>
				<p class="access-gate__message">
					<?php esc_html_e( 'Enter your approved email address for access.', 'the-blue-print' ); ?>
				</p>
			<?php endif; ?>
		</form>

		<?php if ( get_privacy_policy_url() ) : ?>
			<a
				class="access-gate__privacy"
				href="<?php echo esc_url( get_privacy_policy_url() ); ?>"
			>
				<?php esc_html_e( 'Privacy policy', 'the-blue-print' ); ?>
			</a>
		<?php endif; ?>
	</div>
</main>

<?php wp_footer(); ?>

</body>
</html>