<?php

class Builder_Optin_Service_Newsletter extends Builder_Optin_Service {

	function is_available() {
		return defined( 'NEWSLETTER_VERSION' );
	}

	function get_id() {
		return 'newsletter';
	}

	function get_label() {
		return __( 'Newsletter', 'themify' );
	}

	function get_options() {
		if ( $this->is_available() ) {
			$lists = $this->get_lists();
			if ( empty( $lists ) ) {
				return array(); // no options to show
			} else {
				return array(
					array(
						'id' => 'newsletter_list',
						'type' => 'select',
						'label' => __( 'List', 'themify' ),
						'options' => $lists
					),
				);
			}
		} else {
			return array(
				array(
					'type' => 'separator',
					'html' => '<p class="tb_field_error_msg">' . sprintf( __( '<a href="%s" target="_blank">Newsletter plugin</a> is not installed or active.', 'themify' ), 'https://wordpress.org/plugins/newsletter/' ) . '</p>'
				)
			);
		}
	}

	/**
	 * Get list of Lists (/wp-admin/admin.php?page=newsletter_subscription_lists)
	 *
	 * @return WP_Error|Array
	 */
	function get_lists() {
		$lists = Newsletter::instance()->get_lists_for_subscription();
		if ( empty( $lists ) ) {
			return array();
		} else {
			$_lists = array();
			foreach ( $lists as $list ) {
				$_lists[ $list->id ] = $list->name;
			}
			return $_lists;
		}
	}

	/**
	 * Gets data from module and validates API key
	 *
	 * @return bool|WP_Error
	 */
	function check_user_data( $fields_args ) {
		if ( $this->is_available() ) {
			return true;
		} else {
			return new WP_Error( 'missing_plugin', __( 'Newsletter plugin is missing or is not active.', 'themify' ) );
		}
	}

	/**
	 * Subscribe action
	 */
	function subscribe( $args ) {
		$instance = NewsletterSubscription::instance();

		$ip = $instance->get_remote_ip();
		$email = $instance->normalize_email( $args['email'] );
		$first_name = $instance->normalize_name( $args['fname'] );
		$last_name = $instance->normalize_name( $args['lname'] );
		$full_name = sprintf( '%s %s', $first_name, $last_name );

		// validation, based on NewsletterSubscription::hook_wp_loaded()
		if ( $instance->is_missing_domain_mx( $email ) ) {
			return new WP_Error( 'error', $email . ' - ' . $ip . ' - MX check failed' );
		}
		if ( $instance->is_ip_blacklisted( $ip ) ) {
			return new WP_Error( 'error', $email . ' - ' . $ip . ' - IP blacklisted' );
		}
		if ( $instance->is_address_blacklisted( $email ) ) {
			return new WP_Error( 'error', $email . ' - ' . $ip . ' - Address blacklisted' );
		}
		if ( $instance->is_spam_by_akismet( $email, $full_name, $ip, $_SERVER['HTTP_USER_AGENT'], $_SERVER['HTTP_REFERER'] ) ) {
			return new WP_Error( 'error', $email . ' - ' . $ip . ' - Akismet blocked' );
		}
		if ( $instance->is_flood( $email, $ip ) ) {
			return new WP_Error( 'error', $email . ' - ' . $ip . ' - Antiflood triggered' );
		}

		// hijack $_REQUEST, this is required for $instance->subscribe() method
		$_REQUEST = array(
			'ne' => $email,
			'nn' => $full_name,
			'nhr' => '',
		);
		if ( isset( $args['newsletter_list'] ) ) {
			$_REQUEST['nl'] = array( (int) $args['newsletter_list'] );
		}
		$user = $instance->subscribe();

		if ( $user->status == 'E' )
			return new WP_Error( 'error', __( 'Error', 'themify' ) );
		if ( $user->status == 'C' )
			// confirmed
			return true;
		if ( $user->status == 'A' )
			// already_confirmed
			return true;
		if ( $user->status == 'S' )
			// confirmation
			return true;
	}
}
