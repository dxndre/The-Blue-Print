<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<?php wp_head(); ?>
</head>

<?php
	$navbar_position = get_theme_mod( 'navbar_position', 'static' ); // Get custom meta-value.

	$search_enabled  = get_theme_mod( 'search_enabled', '1' ); // Get custom meta-value.
?>

<body <?php body_class(); ?>>

<?php wp_body_open(); ?>

<a href="#main" class="visually-hidden-focusable"><?php esc_html_e( 'Skip to main content', 'the-blue-print' ); ?></a>

<div id="wrapper">
	<header>
		<nav id="header" class="navbar <?php if ( isset( $navbar_position ) && 'fixed_top' === $navbar_position ) : echo ' fixed-top'; elseif ( isset( $navbar_position ) && 'fixed_bottom' === $navbar_position ) : echo ' fixed-bottom'; endif; if ( is_home() || is_front_page() ) : echo ' home'; endif; ?>">
			<div class="container">
				<?php
					$header_logo = get_theme_mod( 'header_logo' );
				?>

				<!-- Desktop header -->
				<div class="header-desktop">
					<nav
						class="header-desktop__left"
						aria-label="<?php esc_attr_e( 'Primary navigation', 'the-blue-print' ); ?>"
					>
						<?php
							wp_nav_menu(
								array(
									'menu_class'     => 'header-menu',
									'container'      => false,
									'fallback_cb'    => false,
									'depth'          => 1,
									'theme_location' => 'header-left',
								)
							);
						?>
					</nav>

					<a
						class="navbar-brand header-logo"
						href="<?php echo esc_url( home_url( '/' ) ); ?>"
						aria-label="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"
						rel="home"
					>
						<?php if ( ! empty( $header_logo ) ) : ?>
							<img
								src="<?php echo esc_url( $header_logo ); ?>"
								alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"
							/>
						<?php else : ?>
							<?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?>
						<?php endif; ?>
					</a>

					<?php if ( has_nav_menu( 'header-right' ) ) : ?>
						<nav
							class="header-desktop__right"
							aria-label="<?php esc_attr_e( 'Utility navigation', 'the-blue-print' ); ?>"
						>
							<?php
							wp_nav_menu(
								array(
									'theme_location' => 'header-right',
									'container'      => false,
									'menu_class'     => 'header-menu header-menu--right',
									'menu_id'        => 'header-right-menu',
									'fallback_cb'    => false,
									'depth'          => 1,
								)
							);
							?>
						</nav>
					<?php else : ?>
						<!-- No menu is assigned to the header-right location. -->
					<?php endif; ?>
				</div>

				<!-- Mobile header -->
				<div class="header-mobile">
					<button
						class="navbar-toggler"
						type="button"
						data-bs-toggle="collapse"
						data-bs-target="#navbar"
						aria-controls="navbar"
						aria-expanded="false"
						aria-label="<?php esc_attr_e( 'Toggle navigation', 'the-blue-print' ); ?>"
					>
						<span class="navbar-toggler-line"></span>
						<span class="navbar-toggler-line"></span>
						<span class="navbar-toggler-line"></span>
					</button>

					<a
						class="navbar-brand header-logo header-logo--mobile"
						href="<?php echo esc_url( home_url( '/' ) ); ?>"
						aria-label="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"
						rel="home"
					>
						<?php if ( ! empty( $header_logo ) ) : ?>
							<img
								src="<?php echo esc_url( $header_logo ); ?>"
								alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"
							/>
						<?php else : ?>
							<?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?>
						<?php endif; ?>
					</a>

					<button
						class="header-utility header-cart header-cart--mobile"
						type="button"
						data-shopwp-cart-trigger
					>
						<?php esc_html_e( 'Cart', 'the-blue-print' ); ?>
					</button>
				</div>

				<?php if ( '1' === $search_enabled ) : ?>
					<div id="searchCollapse" class="collapse header-search">
						<form
							class="search-form"
							role="search"
							method="get"
							action="<?php echo esc_url( home_url( '/' ) ); ?>"
						>
							<label class="visually-hidden" for="headerSearchInput">
								<?php esc_html_e( 'Search', 'the-blue-print' ); ?>
							</label>

							<input
								id="headerSearchInput"
								type="search"
								name="s"
								class="form-control"
								placeholder="<?php esc_attr_e( 'Search', 'the-blue-print' ); ?>"
							/>
						</form>
					</div>
				<?php endif; ?>

				<!-- Existing mobile drawer -->
				<div id="navbar" class="collapse navbar-collapse">
					<div class="mobile-menu-header">
						<h3><?php esc_html_e( 'Menu', 'the-blue-print' ); ?></h3>
					</div>

					<?php
						wp_nav_menu(
							array(
								'menu_class'     => 'navbar-nav',
								'container'      => false,
								'fallback_cb'    => 'WP_Bootstrap_Navwalker::fallback',
								'walker'         => new WP_Bootstrap_Navwalker(),
								'theme_location' => 'main-menu',
							)
						);
					?>
				</div>
			</div><!-- /.container -->
		</nav><!-- /#header -->
	</header>

	<main id="main" class=""<?php if ( isset( $navbar_position ) && 'fixed_top' === $navbar_position ) : echo ' style="padding-top: 0px;"'; elseif ( isset( $navbar_position ) && 'fixed_bottom' === $navbar_position ) : echo ' style="padding-bottom: 100px;"'; endif; ?>>
		<?php
			// If Single or Archive (Category, Tag, Author or a Date based page).
			if ( is_single() || is_archive() ) :
		?>
			<div class="row">
				<div class="col-md-8 col-sm-12">
		<?php
			endif;
		?>
