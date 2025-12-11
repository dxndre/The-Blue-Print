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
				<div class="d-flex w-100 align-items-center">
					<?php if ( '1' === $search_enabled ) : ?>
						<button class="btn btn-link p-0" id="searchToggle" type="button" data-bs-toggle="collapse" data-bs-target="#searchCollapse" aria-controls="searchCollapse" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle search', 'the-blue-print' ); ?>">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/search.png' ); ?>" alt="<?php esc_attr_e( 'Search', 'the-blue-print' ); ?>" class="search-icon" />
						</button>
					<?php endif; ?>

					<a class="navbar-brand mx-auto order-2 text-center" href="<?php echo esc_url( home_url() ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
						<?php
							$header_logo = get_theme_mod( 'header_logo' ); // Get custom meta-value.

							if ( ! empty( $header_logo ) ) :
							?>
								<img src="<?php echo esc_url( $header_logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" />
							<?php
							else :
								echo esc_attr( get_bloginfo( 'name', 'display' ) );
							endif;
						?>
					</a>

					<button class="navbar-toggler order-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle navigation', 'the-blue-print' ); ?>">
						<span class="navbar-toggler-line"></span>
						<span class="navbar-toggler-line"></span>
						<span class="navbar-toggler-line"></span>
					</button>
				</div>

				<?php if ( '1' === $search_enabled ) : ?>
					<div id="searchCollapse" class="collapse">
						<form class="search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
							<div class="input-group">
								<input type="text" name="s" class="form-control" placeholder="<?php esc_attr_e( 'Search', 'the-blue-print' ); ?>" title="<?php esc_attr_e( 'Search', 'the-blue-print' ); ?>" />
								<!-- <button type="submit" name="submit" class="btn btn-outline-secondary"><?php esc_html_e( 'Search', 'the-blue-print' ); ?></button> -->
							</div>
						</form>
					</div>
				<?php endif; ?>

				<div id="navbar" class="collapse navbar-collapse">
					<h3>Menu</h3>
					<?php
						// Loading WordPress Custom Menu (theme_location).
						wp_nav_menu(
							array(
								'menu_class'     => 'navbar-nav me-auto',
								'container'      => '',
								'fallback_cb'    => 'WP_Bootstrap_Navwalker::fallback',
								'walker'         => new WP_Bootstrap_Navwalker(),
								'theme_location' => 'main-menu',
							)
						);
					?>
				</div><!-- /.navbar-collapse -->
			</div><!-- /.container -->
		</nav><!-- /#header -->
	</header>

	<main id="main" class="container"<?php if ( isset( $navbar_position ) && 'fixed_top' === $navbar_position ) : echo ' style="padding-top: 100px;"'; elseif ( isset( $navbar_position ) && 'fixed_bottom' === $navbar_position ) : echo ' style="padding-bottom: 100px;"'; endif; ?>>
		<?php
			// If Single or Archive (Category, Tag, Author or a Date based page).
			if ( is_single() || is_archive() ) :
		?>
			<div class="row">
				<div class="col-md-8 col-sm-12">
		<?php
			endif;
		?>
