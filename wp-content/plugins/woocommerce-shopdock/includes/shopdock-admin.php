<?php
/**
 * Shopdock Admin Pages
 *
 * Admin page setting
 *
 * @author    Themify
 * @category  Core
 * @package   Shopdock
 */

class Shopdock_Admin {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 100 );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	public function admin_menu() {
		$hook = add_options_page(
			__( 'ShopDock', 'wc_shopdock' ),
			__( 'ShopDock', 'wc_shopdock' ),
			'manage_options',
			'wc-shopdock',
			array( $this, 'page_callback' )
		);
	}

    public function page_callback() {
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e( 'Shopdock Settings', 'wc_shopdock' ); ?></h2>           
			<form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				settings_fields( 'wc-shopdock' ); 
				do_settings_sections( 'wc-shopdock' );
				submit_button(); 
				?>
			</form>
		</div>
		<?php
    }

	/**
	 * Register and add settings
	 */
	public function admin_init() {        
		register_setting( 'wc-shopdock', 'woocommerce_shopdock_skin' );
		register_setting( 'wc-shopdock', 'woocommerce_shopdock_position' );

		add_settings_section(
			'shopdock_settings',
			'',
			null,
			'wc-shopdock'
		);

		add_settings_field(
			'woocommerce_shopdock_skin', // ID
			__( 'Skin', 'wc_shopdock' ),
			array( $this, 'skin_callback' ), // Callback
			'wc-shopdock', // Page
			'shopdock_settings' // Section           
		);

		add_settings_field(
			'woocommerce_shopdock_position', // ID
			__( 'Add item button position', 'wc_shopdock' ),
			array( $this, 'position_callback' ), // Callback
			'wc-shopdock', // Page
			'shopdock_settings' // Section           
		);
    }

	public function skin_callback() {
		$options = array(
			'default' => __( 'Default', 'wc_shopdock' ),
			'black' => __( 'Black', 'wc_shopdock' ),
			'blue' => __( 'Blue', 'wc_shopdock' ),
			'green' => __( 'Green', 'wc_shopdock' ),
			'orange' => __( 'Orange', 'wc_shopdock' ),
			'pink' => __( 'Pink', 'wc_shopdock' ),
			'purple' => __( 'Purple', 'wc_shopdock' ),
			'red' => __( 'Red', 'wc_shopdock' )
		);
		$value = get_option( 'woocommerce_shopdock_skin' );
		?><select name="woocommerce_shopdock_skin">
			<?php foreach( $options as $key => $label ) : ?>
				<option value="<?php echo $key ?>" <?php selected( $value, $key ) ?>><?php echo $label ?></option>
			<?php endforeach; ?>
		</select><?php
	}

	public function position_callback() {
		$options = array(
			'top-left' => __( 'Top Left', 'wc_shopdock' ),
			'top-right' => __( 'Top Right', 'wc_shopdock' ),
		);
		$value = get_option( 'woocommerce_shopdock_position' );
		?><select name="woocommerce_shopdock_position">
			<?php foreach( $options as $key => $label ) : ?>
				<option value="<?php echo $key ?>" <?php selected( $value, $key ) ?>><?php echo $label ?></option>
			<?php endforeach; ?>
		</select><?php
	}
}
new Shopdock_Admin;