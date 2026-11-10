<?php
/**
 * The Blue Print website access gate.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the private Access Members post type.
 */
function tbp_register_access_member_post_type() {
	$labels = array(
		'name'               => __( 'Visitor Emails', 'the-blue-print' ),
		'singular_name'      => __( 'Visitor Email', 'the-blue-print' ),
		'add_new'            => __( 'Add Email', 'the-blue-print' ),
		'add_new_item'       => __( 'Add Visitor Email', 'the-blue-print' ),
		'edit_item'          => __( 'Edit Visitor Email', 'the-blue-print' ),
		'new_item'           => __( 'New Visitor Email', 'the-blue-print' ),
		'view_item'          => __( 'View Visitor Email', 'the-blue-print' ),
		'search_items'       => __( 'Search Visitor Emails', 'the-blue-print' ),
		'not_found'          => __( 'No visitor emails found.', 'the-blue-print' ),
		'not_found_in_trash' => __( 'No visitor emails found in Trash.', 'the-blue-print' ),
		'menu_name'          => __( 'Visitor Emails', 'the-blue-print' ),
	);


	register_post_type(
		'tbp_access_member',
		array(
			'labels'              => $labels,
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'menu_icon'           => 'dashicons-shield',
			'supports'            => array( 'title' ),
		)
	);
}
add_action( 'init', 'tbp_register_access_member_post_type' );

/**
 * Normalise email addresses before ACF stores them.
 */
function tbp_normalise_access_email( $value ) {
	return strtolower( sanitize_email( $value ) );
}
add_filter( 'acf/update_value/name=access_email', 'tbp_normalise_access_email' );

/**
 * Cookie configuration.
 */
function tbp_gate_cookie_name() {
	return 'tbp_site_access';
}

/**
 * Base64 URL encoding helpers.
 */
function tbp_gate_base64_encode( $value ) {
	return rtrim( strtr( base64_encode( $value ), '+/', '-_' ), '=' );
}

function tbp_gate_base64_decode( $value ) {
	$padding = strlen( $value ) % 4;

	if ( $padding ) {
		$value .= str_repeat( '=', 4 - $padding );
	}

	return base64_decode( strtr( $value, '-_', '+/' ), true );
}

/**
 * Create a signed access token.
 */
function tbp_gate_create_token( $member_id ) {
	$payload = array(
		'member_id' => absint( $member_id ),
		'issued_at' => time(),
	);

	$encoded_payload = tbp_gate_base64_encode( wp_json_encode( $payload ) );
	$signature       = hash_hmac( 'sha256', $encoded_payload, wp_salt( 'auth' ) );

	return $encoded_payload . '.' . $signature;
}

/**
 * Validate the signed token and ensure the member remains enabled.
 */
function tbp_gate_validate_token( $token ) {
	if ( empty( $token ) || ! is_string( $token ) ) {
		return false;
	}

	$token_parts = explode( '.', $token );

	if ( 2 !== count( $token_parts ) ) {
		return false;
	}

	list( $encoded_payload, $provided_signature ) = $token_parts;

	$expected_signature = hash_hmac(
		'sha256',
		$encoded_payload,
		wp_salt( 'auth' )
	);

	if ( ! hash_equals( $expected_signature, $provided_signature ) ) {
		return false;
	}

	$decoded_payload = tbp_gate_base64_decode( $encoded_payload );

	if ( false === $decoded_payload ) {
		return false;
	}

	$payload = json_decode( $decoded_payload, true );

	if ( ! is_array( $payload ) || empty( $payload['member_id'] ) ) {
		return false;
	}

	$member_id = absint( $payload['member_id'] );
	$member    = get_post( $member_id );

	return (
		$member instanceof WP_Post
		&& 'tbp_access_member' === $member->post_type
		&& 'publish' === $member->post_status
	);
}

/**
 * Determine whether the current visitor has access.
 */
function tbp_gate_visitor_has_access() {
	$cookie_name = tbp_gate_cookie_name();

	if ( empty( $_COOKIE[ $cookie_name ] ) ) {
		return false;
	}

	$token = sanitize_text_field(
		wp_unslash( $_COOKIE[ $cookie_name ] )
	);

	return tbp_gate_validate_token( $token );
}

/**
 * Set a browser-session access cookie.
 */
function tbp_gate_set_cookie( $member_id ) {
	$cookie_name = tbp_gate_cookie_name();
	$token       = tbp_gate_create_token( $member_id );

	setcookie(
		$cookie_name,
		$token,
		array(
			'expires'  => 0,
			'path'     => '/',
			'domain'   => defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '',
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		)
	);

	$_COOKIE[ $cookie_name ] = $token;
}

/**
 * Remove the access cookie.
 */
function tbp_gate_clear_cookie() {
	$cookie_name = tbp_gate_cookie_name();

	setcookie(
		$cookie_name,
		'',
		array(
			'expires'  => time() - HOUR_IN_SECONDS,
			'path'     => '/',
			'domain'   => defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '',
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		)
	);

	unset( $_COOKIE[ $cookie_name ] );
}


/**
 * Find a visitor using their email address.
 */
function tbp_gate_find_member( $email ) {
    $email = strtolower( sanitize_email( $email ) );

    if ( ! is_email( $email ) ) {
        return false;
    }

    $members = get_posts(
        array(
            'post_type'      => 'tbp_access_member',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'meta_key'       => 'access_email',
            'meta_value'     => $email,
        )
    );

    return ! empty( $members )
        ? absint( $members[0] )
        : false;
}

/**
 * Find an existing visitor or register a new one.
 */
function tbp_gate_get_or_create_member( $email ) {
    $email = strtolower( sanitize_email( $email ) );

    if ( ! is_email( $email ) ) {
        return new WP_Error(
            'invalid_email',
            __( 'Please enter a valid email address.', 'the-blue-print' )
        );
    }

    // Reuse existing records to prevent repeat registrations.
    $member_id = tbp_gate_find_member( $email );

    if ( $member_id ) {
        return $member_id;
    }

    // Create a new visitor in the existing custom post type.
    $member_id = wp_insert_post(
        array(
            'post_type'   => 'tbp_access_member',
            'post_status' => 'publish',
            'post_title'  => $email,
            'meta_input'  => array(
                'access_email'   => $email,
                'access_enabled' => '1',
            ),
        ),
        true
    );

    if ( is_wp_error( $member_id ) ) {
        return $member_id;
    }

    if ( ! $member_id ) {
        return new WP_Error(
            'registration_failed',
            __( 'Unable to save your email address.', 'the-blue-print' )
        );
    }

    // Keep the existing ACF field in sync where available.
    if ( function_exists( 'update_field' ) ) {
        update_field( 'access_email', $email, $member_id );
    }

    return absint( $member_id );
}


/**
 * Create a rate-limit key from the visitor's IP address.
 */
function tbp_gate_rate_limit_key() {
	$ip_address = isset( $_SERVER['REMOTE_ADDR'] )
		? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
		: 'unknown';

	return 'tbp_gate_attempts_' . md5( $ip_address );
}


/**
 * Limit form submissions per IP address.
 */
function tbp_gate_is_rate_limited() {
    $attempts = (int) get_transient(
        tbp_gate_rate_limit_key()
    );

    return $attempts >= 20;
}

/**
 * Count every form submission, including successful ones.
 */
function tbp_gate_record_attempt() {
    $key = tbp_gate_rate_limit_key();

    $attempts = (int) get_transient( $key );

    set_transient(
        $key,
        $attempts + 1,
        15 * MINUTE_IN_SECONDS
    );
}

/**
 * Clear failed attempts after a successful submission.
 */
function tbp_gate_clear_failed_attempts() {
	delete_transient( tbp_gate_rate_limit_key() );
}

/**
 * Return the current page URL without gate testing parameters.
 */
function tbp_gate_requested_url() {
	$request_uri = isset( $_SERVER['REQUEST_URI'] )
		? wp_unslash( $_SERVER['REQUEST_URI'] )
		: '/';

	$url = home_url( $request_uri );

	return remove_query_arg(
		array(
			'reset_site_access',
			'preview_access_gate',
		),
		$url
	);
}

/**
 * Determine whether the access gate should be skipped.
 */
function tbp_gate_should_bypass() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return true;
	}

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return true;
	}

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return true;
	}

	$is_preview = isset( $_GET['preview_access_gate'] )
		&& '1' === sanitize_text_field(
			wp_unslash( $_GET['preview_access_gate'] )
		);

	if (
		is_user_logged_in() &&
		current_user_can( 'manage_options' ) &&
		! $is_preview
	) {
		return true;
	}

	return false;
}

/**
 * Determine whether the access-gate template is required.
 */
function tbp_gate_is_required() {
	if ( tbp_gate_should_bypass() ) {
		return false;
	}

	$is_preview = isset( $_GET['preview_access_gate'] )
		&& '1' === sanitize_text_field(
			wp_unslash( $_GET['preview_access_gate'] )
		);

	if ( $is_preview && current_user_can( 'manage_options' ) ) {
		return true;
	}

	return ! tbp_gate_visitor_has_access();
}

/**
 * Process resets and access submissions.
 */
function tbp_gate_process_request() {
	if ( is_admin() || wp_doing_ajax() ) {
		return;
	}

	if (
		isset( $_GET['reset_site_access'] ) &&
		'1' === sanitize_text_field(
			wp_unslash( $_GET['reset_site_access'] )
		)
	) {
		tbp_gate_clear_cookie();

		$redirect_url = remove_query_arg(
			'reset_site_access',
			tbp_gate_requested_url()
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	if (
		empty( $_POST['tbp_gate_action'] ) ||
		'unlock' !== sanitize_text_field(
			wp_unslash( $_POST['tbp_gate_action'] )
		)
	) {
		return;
	}

	$nonce = isset( $_POST['tbp_gate_nonce'] )
		? sanitize_text_field( wp_unslash( $_POST['tbp_gate_nonce'] ) )
		: '';

	if ( ! wp_verify_nonce( $nonce, 'tbp_unlock_site' ) ) {
		$GLOBALS['tbp_gate_error'] = __(
			'Your request could not be verified. Please try again.',
			'the-blue-print'
		);

		return;
	}

	if ( tbp_gate_is_rate_limited() ) {
		$GLOBALS['tbp_gate_error'] = __(
			'Too many attempts have been made. Please wait 15 minutes and try again.',
			'the-blue-print'
		);

		return;
	}

	
	// Record the submission for rate limiting.
	tbp_gate_record_attempt();

	// Retrieve and normalise the submitted email.
	$raw_email = (
		isset( $_POST['access_email'] )
		&& is_string( $_POST['access_email'] )
	)
		? trim( wp_unslash( $_POST['access_email'] ) )
		: '';

	$email = strtolower( sanitize_email( $raw_email ) );

	// Reject malformed input rather than silently correcting it.
	if (
		! is_email( $email )
		|| strtolower( $raw_email ) !== $email
	) {
		$GLOBALS['tbp_gate_error'] = __(
			'Please enter a valid email address.',
			'the-blue-print'
		);

		return;
	}

	// Find an existing record or automatically create one.
	$member_id = tbp_gate_get_or_create_member( $email );

	if ( is_wp_error( $member_id ) || ! $member_id ) {
		$GLOBALS['tbp_gate_error'] = __(
			'We could not process your email address. Please try again.',
			'the-blue-print'
		);

		return;
	}

	// Grant access using the existing signed session cookie.
	tbp_gate_set_cookie( $member_id );


	$redirect_url = isset( $_POST['redirect_to'] )
		? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) )
		: home_url( '/' );

	$redirect_url = wp_validate_redirect(
		$redirect_url,
		home_url( '/' )
	);

	wp_safe_redirect( $redirect_url );
	exit;
}
add_action( 'template_redirect', 'tbp_gate_process_request', 1 );

/**
 * Replace the normal theme template with the gate.
 */
function tbp_gate_template( $template ) {
	if ( ! tbp_gate_is_required() ) {
		return $template;
	}

	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}

	nocache_headers();

	$gate_template = locate_template( 'access-gate.php' );

	if ( $gate_template ) {
		return $gate_template;
	}

	return $template;
}
add_filter( 'template_include', 'tbp_gate_template', 999 );

/**
 * Add Customizer controls for the gate background.
 */
function tbp_gate_customizer_settings( $wp_customize ) {
	$wp_customize->add_section(
		'tbp_access_gate',
		array(
			'title'    => __( 'Access Gate', 'the-blue-print' ),
			'priority' => 35,
		)
	);

	$wp_customize->add_setting(
		'access_gate_background',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'access_gate_background',
			array(
				'label'    => __( 'Background image', 'the-blue-print' ),
				'section'  => 'tbp_access_gate',
				'settings' => 'access_gate_background',
			)
		)
	);
}
add_action( 'customize_register', 'tbp_gate_customizer_settings' );

/**
 * Prevent shared caching of protected HTML pages.
 */
function tbp_gate_disable_html_cache() {
    if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
        return;
    }

    if ( ! defined( 'DONOTCACHEPAGE' ) ) {
        define( 'DONOTCACHEPAGE', true );
    }
}
add_action( 'init', 'tbp_gate_disable_html_cache', 0 );

function tbp_gate_send_no_cache_headers() {
    if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
        return;
    }

    nocache_headers();
    header( 'Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0', true );
}
add_action( 'send_headers', 'tbp_gate_send_no_cache_headers', 20 );
