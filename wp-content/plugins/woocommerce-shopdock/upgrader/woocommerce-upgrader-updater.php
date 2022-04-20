<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Shopdock_Updater {

	var $name;
	var $version;
	var $versions_url;
	var $package_url;
	var $hook = 'settings_page_wc-shopdock';

	public function __construct( $name, $version, $slug ) {
		$this->name = $name;
		$this->version = $version;
		$this->slug = $slug;
		$this->versions_url = 'https://themify.me/versions/versions.xml';
		$this->package_url = "https://themify.me/files/{$this->name}/{$this->name}.zip";

		add_action( "admin_print_styles-{$this->hook}", array( $this, 'enqueue' ) );
		add_action( "admin_footer-{$this->hook}", array( $this, 'pormpt_box' ) );
		add_action( 'admin_notices', array( $this, 'dashboard_update_notice' ) );
	}

	public function dashboard_update_notice() {
		global $hook_suffix;
		if( 'index.php' == $hook_suffix ) {
			$this->check_version( false );
		} elseif( $hook_suffix == $this->hook ) {
			$this->page_callback();
		}
	}

	public function page_callback() {
		delete_transient( "{$this->name}_new_update" );
		delete_transient( "{$this->name}_check_update" );

		if( isset( $_GET['action'] ) && $_GET['action'] == 'upgrade' ) {
			wc_shopdock_updater();
		} else {
			$this->check_version();
		}
	}

	/**
	 * $enable_update flag to show the login popup or redirect to a specific page for update
	 */
	public function check_version( $enable_update = true ) {
		$notifications = '';

		// Check update transient
		$current = get_transient( "{$this->name}_check_update" ); // get last check transient
		$timeout = 60;
		$time_not_changed = isset( $current->lastChecked ) && $timeout > ( time() - $current->lastChecked );
		$newUpdate = get_transient( "{$this->name}_new_update" ); // get new update transient

		if ( is_object( $newUpdate ) && $time_not_changed ) {
			if ( version_compare( $this->version, $newUpdate->version, '<') ) {
				if( $enable_update ) {
					$notifications .= sprintf( __('<div class="error update %s" style="padding: 1em 2em;">%s version %s is now available. <a href="%s" title="" class="%s" target="%s" data-plugin="%s"
	data-package_url="%s">Update now</a> or view the <a href="%s" title=""
	class="themify_changelogs" target="_blank" data-changelog="%s">change
	log</a> for details.</div>', 'wc_shopdock'),
						esc_attr( $newUpdate->login ),
						ucwords( $this->name ),
						$newUpdate->version,
						esc_url( $newUpdate->url ),
						esc_attr( $newUpdate->class ),
						esc_attr( $newUpdate->target ),
						esc_attr( $this->slug ),
						esc_attr( $this->package_url ),
						esc_url( 'https://themify.me/changelogs/' . $this->name . '.txt' ),
						esc_url( 'https://themify.me/changelogs/' . $this->name . '.txt' )
					);
					echo '<div class="notifications">'. $notifications . '</div>';
				} else {
					printf( __( '<div class="error"><p>WooCommerce ShopDock update is available. <a href="%s"> Update now. &#8594;</a></p></div>', 'wc_shopdock' ), admin_url( 'options-general.php?page=&page=wc-shopdock' ) );
				}
			}
			return;
		}

		// get remote version
		$remote_version = $this->get_remote_version();

		// delete update checker transient
		delete_transient( "{$this->name}_check_update" );

		$class = "";
		$target = "";
		$url = "#";
		
		$new = new stdClass();
		$new->login = 'login';
		$new->version = $remote_version;
		$new->url = $url;
		$new->class = 'themify-builder-upgrade-plugin';
		$new->target = $target;

		if ( version_compare( $this->version, $remote_version, '<' ) ) {
			if( $enable_update ) {
				set_transient( 'themify_builder_new_update', $new );
				$notifications .= sprintf( __('<div class="error update %s" style="padding: 1em 2em;">%s version %s is now available. <a href="%s" title="" class="%s" target="%s" data-plugin="%s"
	data-package_url="%s">Update now</a> or view the <a href="%s" title=""
	class="themify_changelogs" target="_blank" data-changelog="%s">change
	log</a> for details.</div>', 'wc_shopdock'),
					esc_attr( $new->login ),
					ucwords( $this->name ),
					$new->version,
					esc_url( $new->url ),
					esc_attr( $new->class ),
					esc_attr( $new->target ),
					esc_attr( $this->slug ),
					esc_attr( $this->package_url ),
					esc_url( 'https://themify.me/changelogs/' . $this->name . '.txt' ),
					esc_url( 'https://themify.me/changelogs/' . $this->name . '.txt' )
				);
			} else {
				printf( __( '<div class="error"><p>WooCommerce ShopDock update is available. <a href="%s"> Update now. &#8594;</a></p></div>', 'wc_shopdock' ), admin_url( 'options-general.php?page=&page=wc-shopdock' ) );
			}
		} else {
			if( $enable_update ) {
				$notifications .= __( '<div class="updated"><p>You have the latest version of WooCommerce ShopDock Plugin. Yay!</p></div>', 'wc_shopdock' );
			}
		}

		// update transient
		$this->set_update();

		echo '<div class="notifications">'. $notifications . '</div>';
	}

	public function get_remote_version() {
		$version = '';

		$response = wp_remote_get( $this->versions_url );
		if( is_wp_error( $response ) ) {
			return $version;
		}

		$body = wp_remote_retrieve_body( $response );
		if ( is_wp_error( $body ) || empty( $body ) ) {
			return $version;
		}

		$xml = new DOMDocument;
		$xml->loadXML( trim( $body ) );
		$xml->preserveWhiteSpace = false;
		$xml->formatOutput = true;
		$xpath = new DOMXPath($xml);
		$query = "//version[@name='".$this->name."']";
		$elements = $xpath->query($query);
		if( $elements->length ) {
			foreach ($elements as $field) {
				$version = $field->nodeValue;
			}
		}

		return $version;
	}

	public function set_update() {
		$current = new stdClass();
		$current->lastChecked = time();
		set_transient( "{$this->name}_check_update", $current );
	}

	public function is_update_available() {
		$newUpdate = get_transient( "{$this->name}_new_update" ); // get new update transient

		if ( false === $newUpdate ) {
			$new_version = $this->get_remote_version( $this->name );
		} else {
			$new_version = $newUpdate->version;
		}

		if ( version_compare( $this->version, $new_version, '<') ) {
			return true;
		} else {
			false;
		}
	}

	public function enqueue() {
		wp_enqueue_script( 'themify-builder-plugin-upgrade', SHOPDOCK_URL . '/upgrader/themify.builder.upgrader.js', array('jquery'), false, true );
		wp_localize_script( 'themify-builder-plugin-upgrade', 'themify_lang', array(
			'confirm_reset_styling'	=> __('Are you sure you want to reset your theme style?', 'wc_shopdock'),
			'confirm_reset_settings' => __('Are you sure you want to reset your theme settings?', 'wc_shopdock'),
			'confirm_refresh_webfonts'	=> __('Are you sure you want to refresh the Google Fonts list? This will also save the current settings.', 'wc_shopdock'),
			'check_backup' => __('Make sure to backup before upgrading. Files and settings may get lost or changed.', 'wc_shopdock'),
			'confirm_delete_image' => __('Do you want to delete this image permanently?', 'wc_shopdock'),
			'invalid_login' => __('Invalid username or password.<br/>Contact <a href="https://themify.me/contact">Themify</a> for login issues.', 'wc_shopdock'),
			'unsuscribed' => __('Your membership might be expired. Login to <a href="https://themify.me/member">Themify</a> to check.', 'wc_shopdock'),
		));
		// wp_enqueue_script( 'themify-scripts', THEMIFY_URI . '/js/scripts.js', array( 'jquery' ), THEMIFY_TILES_VERSION );
		wp_enqueue_style( 'themify-ui', SHOPDOCK_URL . '/upgrader/themify-ui.css' );
	}

	public function pormpt_box() { ?>
		<!-- prompts -->
		<div class="overlay">&nbsp;</div>
		<div class="prompt-box">
			<div class="show-login">
				<form id="themify_update_form" method="post" action="options-general.php?page=wc-shopdock&action=upgrade&type=plugin&login=true">
				<p class="prompt-msg"><?php _e('Enter your Themify login info to upgrade', 'wc_shopdock'); ?></p>
				<p><label><?php _e('Username', 'wc_shopdock'); ?></label> <input type="text" name="username" class="username" value=""/></p>
				<p><label><?php _e('Password', 'wc_shopdock'); ?></label> <input type="password" name="password" class="password" value=""/></p>
				<input type="hidden" value="plugin" name="type" />
				<input type="hidden" value="true" name="login" />
				<p class="pushlabel"><input name="login" type="submit" value="Login" class="button upgrade-login" /></p>
				</form>
			</div>
			<div class="show-error">
				<p class="error-msg"><?php _e('There were some errors updating the theme', 'wc_shopdock'); ?></p>
			</div>
		</div>
		<!-- /prompts -->
	<?php
	}
}

/**
 * Updater called through wp_ajax_ action
 */
function wc_shopdock_updater(){
	
	$url = isset( $_POST['package_url'] ) ? $_POST['package_url'] : null;
	$plugin_slug = isset( $_POST['plugin'] ) ? $_POST['plugin'] : null;

	if( ! $url || ! $plugin_slug ) return;

	//If login is required
	if($_GET['login'] == 'true'){

			$response = wp_remote_post(
				'https://themify.me/member/login.php',
				array(
					'timeout' => 300,
					'headers' => array(),
					'body' => array(
						'amember_login' => $_POST['username'],
						'amember_pass'  => $_POST['password']
					)
			    )
			);

			//Was there some error connecting to the server?
			if( is_wp_error( $response ) ) {
				$errorCode = $response->get_error_code();
				echo 'Error: ' . $errorCode;
				die();
			}

			//Connection to server was successful. Test login cookie
			$amember_nr = false;
			foreach($response['cookies'] as $cookie){
				if($cookie->name == 'amember_nr'){
					$amember_nr = true;
				}
			}
			if(!$amember_nr){
				_e('You are not a Themify Member.', 'wc_shopdock');
				die();
			}
	}

	//remote request is executed after all args have been set
	include ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	if( ! class_exists( 'Themify_Builder_Upgrader' ) ) {
		require_once( SHOPDOCK_DIR . '/upgrader/class-themify-builder-upgrader.php' );
	}

	$upgrader = new Themify_Builder_Upgrader( new Plugin_Upgrader_Skin(
		array(
			'plugin' => $plugin_slug,
			'title' => __( 'Update Builder', 'wc_shopdock' )
		)
	));
	$response_cookies = ( isset( $response ) && isset( $response['cookies'] ) ) ? $response['cookies'] : '';
	$upgrader->upgrade( $plugin_slug, $url, $response_cookies );

	//if we got this far, everything went ok!	
	die();
}

/**
 * Validate login credentials against Themify's membership system
 */
function wc_shopdock_validate_login(){
	$response = wp_remote_post(
		'https://themify.me/files/themify-login.php',
		array(
			'timeout' => 300,
			'headers' => array(),
			'body' => array(
				'amember_login' => $_POST['username'],
				'amember_pass'  => $_POST['password']
			)
	    )
	);

	//Was there some error connecting to the server?
	if( is_wp_error( $response ) ) {
		echo 'Error ' . $response->get_error_code() . ': ' . $response->get_error_message( $response->get_error_code() );
		die();
	}

	//Connection to server was successful. Test login cookie
	$amember_nr = false;
	foreach($response['cookies'] as $cookie){
		if($cookie->name == 'amember_nr'){
			$amember_nr = true;
		}
	}
	if(!$amember_nr){
		echo 'invalid';
		die();
	}

	$subs = json_decode($response['body'], true);
	$sub_match = 'false';
	$theme_name = wp_get_theme()->Name;

	foreach ($subs as $key => $value) {
		if(stripos($value['title'], 'WooCommerce Shopdock') !== false){
			$sub_match = 'true';
			break;
		}
		if(stripos($value['title'], 'Master Club') !== false){
			$sub_match = 'true';
			break;
		}
	}
	echo $sub_match;
	die();
}

//Executes themify_updater function using wp_ajax_ action hook
add_action('wp_ajax_wc_shopdock_validate_login', 'wc_shopdock_validate_login');