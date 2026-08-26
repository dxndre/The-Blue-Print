			<?php
				// If Single or Archive (Category, Tag, Author or a Date based page).
				if ( is_single() || is_archive() ) :
			?>
					</div><!-- /.col -->

					<?php
						get_sidebar();
					?>

				</div><!-- /.row -->
			<?php
				endif;
			?>
		</main><!-- /#main -->
		<footer id="footer">
			<div class="">
				<div class="row">
					<div class="col-md-10">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/house.png' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" />
						<p><?php printf( esc_html__( '&copy; %1$s %2$s', 'the-blue-print' ), wp_date( 'Y' ), get_bloginfo( 'name', 'display' ) ); ?> by <a href="https://www.dxndre.co.uk" target="_blank" rel="noopener noreferrer">DXNDRE</a>.</p>
						<p>All Rights Reserved. The Blue Print and the House Logo are registered trademarks of The Blue Print.</p>
					</div>

					<div class="col-md-2">
						<ul class="social-icons list-unstyled d-flex justify-content-end">
							<li><a href="mailto:info@theblueprint.co.uk"><i class="fas fa-envelope"></i></a></li>
							<li><a href="https://www.instagram.com/theblueprint" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a></li>
						</ul>
					</div>

					<?php
						if ( has_nav_menu( 'footer-menu' ) ) : // See function register_nav_menus() in functions.php
							/*
								Loading WordPress Custom Menu (theme_location) ... remove <div> <ul> containers and show only <li> items!!!
								Menu name taken from functions.php!!! ... register_nav_menu( 'footer-menu', 'Footer Menu' );
								!!! IMPORTANT: After adding all pages to the menu, don't forget to assign this menu to the Footer menu of "Theme locations" /wp-admin/nav-menus.php (on left side) ... Otherwise the themes will not know, which menu to use!!!
							*/
							wp_nav_menu(
								array(
									'container'       => 'nav',
									'container_class' => 'col-md-6',
									//'fallback_cb'     => 'WP_Bootstrap4_Navwalker_Footer::fallback',
									'walker'          => new WP_Bootstrap4_Navwalker_Footer(),
									'theme_location'  => 'footer-menu',
									'items_wrap'      => '<ul class="menu nav justify-content-end">%3$s</ul>',
								)
							);
						endif;

						if ( is_active_sidebar( 'third_widget_area' ) ) :
					?>
						<div class="col-md-12">
							<?php
								dynamic_sidebar( 'third_widget_area' );

								if ( current_user_can( 'manage_options' ) ) :
							?>
								<span class="edit-link"><a href="<?php echo esc_url( admin_url( 'widgets.php' ) ); ?>" class="badge bg-secondary"><?php esc_html_e( 'Edit', 'the-blue-print' ); ?></a></span><!-- Show Edit Widget link -->
							<?php
								endif;
							?>
						</div>
					<?php
						endif;
					?>
				</div><!-- /.row -->
			</div><!-- /.container -->
		</footer><!-- /#footer -->
	</div><!-- /#wrapper -->
	<?php
		wp_footer();
	?>
</body>
</html>
