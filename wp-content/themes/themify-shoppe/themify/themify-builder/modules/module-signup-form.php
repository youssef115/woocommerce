<?php
if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

/**
 * Module Name: Sign Up Form
 * Description: Displays sign up form
 */
class TB_Signup_Form_Module extends Themify_Builder_Component_Module
{

	function __construct() {
		parent::__construct( array(
			'name' => __( 'Sign Up Form', 'themify' ),
			'slug' => 'signup-form'
		) );

		// Sign Up module action for processing sign up form
		add_action( 'wp_ajax_tb_signup_process', array( __CLASS__, 'signup_process' ) );
		add_action( 'wp_ajax_nopriv_tb_signup_process', array( __CLASS__, 'signup_process' ) );
	}

	/**
	 * Actions to perform when sign up via Sign Up module is sent
	 *
	 */
	public static function signup_process() {
		if ( empty( $_POST['data'] ) || !wp_verify_nonce( $_POST['nonce'], 'tb_signup_nonce' ) ) {
			die( -1 );
		}
		$data = urldecode($_POST['data']);
		$data = explode('&',$data);
		foreach ( $data as $input ) {
		    $input = explode( '=', $input );
		    if ( ! empty( $input[1] ) ) {
		        $params[ $input[0] ] = $input[1];
            }
        }
		$data = $input = null;
		$errs = array();
		// Validate User role based on saved role in transient for seacurity reason
		if ( false === ( $role = get_transient( 'tb_signup_' . $_POST['nonce'] ) ) ) {
			$errs[] = __( 'User role is not valid.', 'themify' );
		}
		if ( empty( $params['usr'] ) ) {
			$errs[] = __( 'Please enter a username', 'themify' );
		}
		if ( username_exists( $params['usr'] ) ) {
			$errs[] = __( 'Username already taken', 'themify' );
		}
		if ( !validate_username( $params['usr'] ) ) {
			$errs[] = __( 'Invalid username', 'themify' );
		}
		if ( !is_email( $params['email']) ) {
			$errs[] = __( 'Invalid email', 'themify' );
		}
		if ( email_exists( $params['email'] ) ) {
			$errs[] = __( 'Email already registered', 'themify' );
		}
		if ( empty( $params['pwd'] ) ) {
			$errs[] = __( 'Please enter a password', 'themify' );
		}

		if ( empty( $errs ) ) {
			$new_user_id = wp_insert_user( array(
					'user_login' => $params['usr'],
					'user_pass' => $params['pwd'],
					'user_email' => $params['email'],
					'first_name' => $params['first_n'],
					'last_name' => $params['last_n'],
					'description' => $params['bio'],
					'user_registered' => date( 'Y-m-d H:i:s' ),
					'role' => $role
				)
			);
			if ( $new_user_id ) {

				// newsletter subscription
				if ( isset( $params['optin'] ) && $params['optin'] == '1' ) {
					if ( ! class_exists( 'Builder_Optin_Services_Container' ) )
						include_once( THEMIFY_BUILDER_INCLUDES_DIR. '/optin-services/base.php' );
					$optin_instance = Builder_Optin_Services_Container::get_instance()->get_provider( $params['optin-provider'] );
					if ( $optin_instance ) {
						// collect the data for optin service
						$data = array(
							'email' => $params['email'],
							'fname' => $params['first_n'],
							'lname' => $params['last_n'],
						);
						foreach ( $params as $key => $value ) {
							if ( preg_match( '/^optin-/', $key ) ) {
								$key = preg_replace( '/^optin-/', '', $key );
								$data[ $key ] = sanitize_text_field( trim( $value ) );
							}
						}
						$optin_instance->subscribe( $data );
					}
				}

				if ( $params['tb_e_admin'] === '1' || $params['tb_e_user'] === '1' ) {
					$notify = '';
				    if($params['tb_e_admin'] === '1' && $params['tb_e_user'] === '1'){
					    $notify = 'both';
                    }elseif ($params['tb_e_user'] === '1'){
					    $notify = 'user';
                    }
					wp_new_user_notification( $new_user_id, null, $notify );
				}
				$response['result'] = 'success';
			} else {
				$response['result'] = 'fail';
				$response['err'][] = __( 'Something went wrong with registration', 'themify' );
			}
		} else {
			$response['err'] = $errs;
			$response['result'] = 'fail';
		}
		wp_send_json( $response );
	}

	/**
	 * Get all user roles
	 *
	 * @return array users roles name
	 */
	private function get_user_roles() {
		global $wp_roles;

		$all_roles = $wp_roles->roles;
		$roles = array();
		foreach ( $all_roles as $name => $role ) {
			$roles[ $name ] = $role['name'];
		}

		return $roles;
	}

	public function get_options() {
		return array(
			array(
				'id' => 'mod_title',
				'type' => 'title'
			),
			array(
				'type'  => 'separator',
				'label' => ''
			),
			array(
				'id' => 'success_action',
				'type' => 'select',
				'label' => __( 'After Sign Up', 'themify' ),
				'options' => array(
					'c' => __( 'Redirect to URL', 'themify' ),
					'm' => __( 'Show message', 'themify' ),
				),
				'class' => 'large',
				'binding' => array(
					'c' => array( 'show' => array( 'redirect_to' ), 'hide' => array( 'msg_success' ) ),
					'm' => array( 'hide' => array( 'redirect_to' ), 'show' => array( 'msg_success' ) ),
				)
			),
			array(
				'id' => 'redirect_to',
				'type' => 'url',
				'label' => __( 'Redirect URL', 'themify' ),
				'class' => 'large',
				'help' => __( 'Redirect to this URL after successful sign up', 'themify' )
			),
			array(
				'id' => 'msg_success',
				'type' => 'textarea',
				'label' => __( 'Success Message', 'themify' ),
				'help' => __( 'Message to show after successful sign up.', 'themify' )
			),
			array(
				'id' => 'l_name',
				'type' => 'text',
				'label' => __( 'Labels', 'themify' ),
				'after' => __( 'Name', 'themify' )
			),
			array(
				'id' => 'l_firstname',
				'type' => 'text',
				'label' => '',
				'after' => __( 'First Name', 'themify' )
			),
			array(
				'id' => 'l_lastname',
				'type' => 'text',
				'label' => '',
				'after' => __( 'Last Name', 'themify' )
			),
			array(
				'id' => 'l_username',
				'type' => 'text',
				'label' => '',
				'after' => __( 'Username', 'themify' )
			),
			array(
				'id' => 'l_email',
				'type' => 'text',
				'label' => '',
				'after' => __( 'Email', 'themify' )
			),
			array(
				'id' => 'l_password',
				'type' => 'text',
				'label' => '',
				'after' => __( 'Password', 'themify' )
			),
			array(
				'id' => 'l_bio',
				'type' => 'textarea',
				'label' => '',
				'after' => __( 'Bio', 'themify' )
			),
			array(
				'id' => 'l_submit',
				'type' => 'text',
				'label' => '',
				'after' => __( 'Submit', 'themify' )
			),
			array(
				'id' => 'desc',
				'type' => 'textarea',
				'label' => '',
				'after' => __( 'Description', 'themify' )
			),
			array(
				'id' => 'optin',
				'type' => 'toggle_switch',
				'label' => __( 'Newsletter Optin', 'themify' ),
				'options' => array(
					'on' => array( 'name' => 'yes', 'value' => __( 'Enabled', 'themify' ) ),
					'off' => array( 'name' => 'no', 'value' => __( 'Disabled', 'themify' ) ),
				),
				'binding' => array(
					'yes' => array( 'show' => array( 'optin_label', 'provider' ) ),
					'no' => array( 'hide' => array( 'optin_label', 'provider' ) ),
				)
			),
			array(
				'id' => 'optin_label',
				'type' => 'text',
				'label' => __( 'Subscribe Label', 'themify' ),
			),
			array(
				'id' => 'provider',
				'type' => 'optin_provider',
			),
			array(
				'id' => 'gdpr',
				'label' => __('GDPR Checkbox', 'builder-contact'),
				'type' => 'toggle_switch',
				'options' => array(
					'on' => array( 'name' => 'on', 'value' => 'en' ),
					'off' => array( 'name' => '', 'value' => 'dis' )
				),
				'binding' => array(
					'checked' => array( 'show' => array( 'gdpr_label' ) ),
					'not_checked' => array( 'hide' => array( 'gdpr_label' ) ),
				)
			),
			array(
				'id' => 'gdpr_label',
				'type' => 'textarea',
				'class' => 'fullwidth',
				'label' => __( 'GDPR Message', 'builder-contact' )
			),
			array(
				'id' => 'u_role',
				'type' => 'select',
				'label' => __( 'User Role', 'themify' ),
				'class' => 'large',
				'options' => $this->get_user_roles()
			),
			array(
				'id' => 'e_user',
				'type' => 'checkbox',
				'label' => '',
				'options' => array(
					array(
						'name' => '1',
						'value' => __( 'Send account info to user', 'themify' )
					)
				)
			),
			array(
				'id' => 'e_admin',
				'type' => 'checkbox',
				'label' => '',
				'options' => array(
					array(
						'name' => '1',
						'value' => __( 'Send notification to admin', 'themify' )
					)
				)
			),
			array(
				'id' => 'css',
				'type' => 'custom_css'
			),
			array( 'type' => 'custom_css_id' )
		);
	}

	public function get_default_settings() {
		return array(
			'success_action' => 'c',
			'l_name' => __( 'Name', 'themify' ),
			'l_firstname' => __( 'First', 'themify' ),
			'l_lastname' => __( 'Last', 'themify' ),
			'l_username' => __( 'Username', 'themify' ),
			'l_email' => __( 'Email', 'themify' ),
			'l_password' => __( 'Password', 'themify' ),
			'l_bio' => __( 'Short Bio', 'themify' ),
			'l_submit' => __( 'Submit', 'themify' ),
			'desc' => __( 'Share a little information about yourself.', 'themify' ),
			'u_role' => 'subscriber',
			'e_user' => '1',
			'e_admin' => '1',
			'optin' => 'no',
			'optin_label' => __( 'Subscribe to my newsletter', 'themify' ),
			'gdpr_label' => __( 'I consent to my submitted data being collected and stored', 'themify' ),
		);
	}

	public function get_styling() {
		$general = array(
			self::get_expand( 'bg', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_image( '', 'b_i', 'bg_c', 'b_r', 'b_p' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_image( '', 'b_i', 'bg_c', 'b_r', 'b_p', 'h' )
						)
					)
				) )
			) ),
			self::get_expand( 'f', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_font_family( '', 'f_f' ),
							self::get_color_type( ' form', '', 'f_c_t', 'f_c', 'f_g_c' ),
							self::get_font_size( '', 'f_s' ),
							self::get_line_height( '', 'l_h' ),
							self::get_letter_spacing( '', 'l_s' ),
							self::get_text_align( '', 't_a' ),
							self::get_text_transform( '', 't_t' ),
							self::get_font_style( '', 'f_st', 'f_w' ),
							self::get_text_decoration( '', 't_d_r' ),
							self::get_text_shadow(),
						)
					),
					'h' => array(
						'options' => array(
							self::get_font_family( '', 'f_f', 'h' ),
							self::get_color_type( ' form', 'h' ),
							self::get_font_size( '', 'f_s', '', 'h' ),
							self::get_line_height( '', 'l_h', 'h' ),
							self::get_letter_spacing( '', 'l_s', 'h' ),
							self::get_text_align( '', 't_a', 'h' ),
							self::get_text_transform( '', 't_t', 'h' ),
							self::get_font_style( '', 'f_st', 'f_w', 'h' ),
							self::get_text_decoration( '', 't_d_r', 'h' ),
							self::get_text_shadow( '', 't_sh', 'h' ),
						)
					)
				) )
			) ),
			// Padding
			self::get_expand( 'p', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_padding( '', 'p' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding( '', 'p', 'h' )
						)
					)
				) )
			) ),
			// Margin
			self::get_expand( 'm', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_margin( '', 'm' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_margin( '', 'm', 'h' )
						)
					)
				) )
			) ),
			// Border
			self::get_expand( 'b', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border( '', 'b' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_border( '', 'b', 'h' )
						)
					)
				) )
			) ),
			// Filter
			self::get_expand( 'f_l',
				array(
					self::get_tab( array(
						'n' => array(
							'options' => self::get_blend()

						),
						'h' => array(
							'options' => self::get_blend( '', '', 'h' )
						)
					) )
				)
			),
			// Height & Min Height
			self::get_expand( 'ht', array(
					self::get_height(),
					self::get_min_height(),
                    self::get_max_height()
				)
			),
			// Rounded Corners
			self::get_expand( 'r_c', array(
					self::get_tab( array(
						'n' => array(
							'options' => array(
								self::get_border_radius()
							)
						),
						'h' => array(
							'options' => array(
								self::get_border_radius( '', 'r_c', 'h' )
							)
						)
					) )
				)
			),
			// Shadow
			self::get_expand( 'sh', array(
					self::get_tab( array(
						'n' => array(
							'options' => array(
								self::get_box_shadow()
							)
						),
						'h' => array(
							'options' => array(
								self::get_box_shadow( '', 'sh', 'h' )
							)
						)
					) )
				)
			),
		);

		$labels = array(
			// Font
			self::get_seperator( 'f' ),
			self::get_tab( array(
				'n' => array(
					'options' => array(
						self::get_font_family( ' label', 'f_f_l' ),
						self::get_color( ' label', 'f_c_l' ),
						self::get_font_size( ' label', 'f_s_l' ),
						self::get_text_shadow( ' label', 't_sh_l' ),
					)
				),
				'h' => array(
					'options' => array(
						self::get_font_family( ' label', 'f_f_l', 'h' ),
						self::get_color( ' label', 'f_c_l', null, null, 'h' ),
						self::get_font_size( ' label', 'f_s_l', '', 'h' ),
						self::get_text_shadow( ' label', 't_sh_l', 'h' ),
					)
				)
			) )
		);

		$inputs = array(
			//background
			self::get_expand( 'bg', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_color( array(' input',' textarea'), 'bg_c_i', 'bg_c', 'background-color' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_color( array(' input',' textarea'), 'bg_c_i', 'bg_c', 'background-color', 'h' )
						)
					)
				) )
			) ),
			// Font
			self::get_expand( 'f', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_font_family( array(' input',' textarea'), 'f_f_i' ),
							self::get_color( array(' input',' textarea'), 'f_c_i' ),
							self::get_font_size( array(' input',' textarea'), 'f_s_i' ),
							self::get_text_shadow( array(' input',' textarea'), 't_sh_i' ),
						)
					),
					'h' => array(
						'options' => array(
							self::get_font_family( array(' input',' textarea'), 'f_f_i', 'h' ),
							self::get_color( array(' input',' textarea'), 'f_c_i', null, null, 'h' ),
							self::get_font_size( array(' input',' textarea'), 'f_s_i', '', 'h' ),
							self::get_text_shadow( array(' input',' textarea'), 't_sh_i', 'h' ),
						)
					)
				) )
			) ),
			// Placeholder
			self::get_expand( 'Placeholder', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_font_family( array(' input::placeholder',' textarea::placeholder'), 'f_f_in_ph' ),
							self::get_color( array(' input::placeholder',' textarea::placeholder'), 'f_c_in_ph' ),
							self::get_font_size( array(' input::placeholder',' textarea::placeholder'), 'f_s_in_ph' ),
							self::get_text_shadow( array(' input::placeholder',' textarea::placeholder'), 't_sh_in_ph' ),
						)
					),
					'h' => array(
						'options' => array(
							self::get_font_family( array(' input:hover::placeholder',' textarea:hover::placeholder'), 'f_f_in_ph_h', '' ),
							self::get_color( array(' input:hover::placeholder',' textarea:hover::placeholder'), 'f_c_in_ph_h', null, null, '' ),
							self::get_font_size( array(' input:hover::placeholder',' textarea:hover::placeholder'), 'f_s_in_ph_h', '', '' ),
							self::get_text_shadow( array(' input:hover::placeholder',' textarea:hover::placeholder'), 't_sh_in_ph_h', '' ),
						)
					)
				) )
			) ),
			// Border
			self::get_expand( 'b', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border( array(' input',' textarea'), 'b_in' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_border( array(' input',' textarea'), 'b_in', 'h' )
						)
					)
				) )
			) ),
			// Padding
			self::get_expand( 'p', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_padding( array(' input',' textarea'), 'in_p' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding( array(' input',' textarea'), 'in_p', 'h' )
						)
					)
				) )
			) ),
			// Rounded Corners
			self::get_expand( 'r_c', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border_radius( array(' input',' textarea'), 'in_r_c' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius( array(' input',' textarea'), 'in_r_c', 'h' )
						)
					)
				) )
			) ),
			// Shadow
			self::get_expand( 'sh', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_box_shadow( array(' input',' textarea'), 'in_b_sh' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow( array(' input',' textarea'), 'in_b_sh', 'h' )
						)
					)
				) )
			) )
		);

		$send_button = array(
			//background
			self::get_expand( 'bg', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_color( ' button', 'bg_c_s_b', 'bg_c', 'background-color' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_color( ' button:hover', 'bg_c_s_b_h', 'bg_c', 'background-color', '' )
						)
					)
				) )
			) ),
			// Font
			self::get_expand( 'f', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_font_family( ' button', 'f_f_b_s' ),
							self::get_color( ' button', 'f_c_b' ),
							self::get_font_size( ' button', 'f_s_b' ),
							self::get_text_shadow( ' button', 't_sh_b' ),
						)
					),
					'h' => array(
						'options' => array(
							self::get_font_family( ' button:hover', 'f_f_b_s_h', '' ),
							self::get_color( ' button:hover', 'f_c_b_s_h', null, null, '' ),
							self::get_font_size( ' button:hover', 'f_s_b_h', '', '' ),
							self::get_text_shadow( ' button:hover', 't_sh_b_h', '' ),
						)
					)
				) )
			) ),
			// Border
			self::get_expand( 'b', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border( ' button', 'b_b' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_border( ' button:hover', 'b_b_s', '' )
						)
					)
				) )
			) ),
			// Padding
			self::get_expand( 'p', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_padding( ' button', 'bt_p' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding( ' button:hover', 'bt_p', 'h' )
						)
					)
				) )
			) ),
			// Rounded Corners
			self::get_expand( 'r_c', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border_radius( ' button', 'bt_r_c' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius( ' button:hover', 'bt_r_c', 'h' )
						)
					)
				) )
			) ),
			// Shadow
			self::get_expand( 'sh', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_box_shadow( ' button', 'bt_sh' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow( ' button:hover', 'bt_sh', 'h' )
						)
					)
				) )
			) )
		);

		$signup_error = array(
			//background
			self::get_expand( 'bg', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_color( ' .tb_signup_errors', 'bg_c_e', 'bg_c', 'background-color' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_color( ' .tb_signup_errors:hover', 'bg_c_e_h', 'bg_c', 'background-color', '' )
						)
					)
				) )
			) ),
			// Font
			self::get_expand( 'f', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_font_family( ' .tb_signup_errors', 'f_f_e' ),
							self::get_color( ' .tb_signup_errors', 'f_c_e' ),
							self::get_font_size( ' .tb_signup_errors', 'f_s_e' ),
							self::get_text_shadow( ' .tb_signup_errors', 't_sh_e' ),
						)
					),
					'h' => array(
						'options' => array(
							self::get_font_family( ' tb_signup_errors:hover', 'f_f_e_h', '' ),
							self::get_color( ' .tb_signup_errors:hover', 'f_c_e_h', null, null, '' ),
							self::get_font_size( ' .tb_signup_errors:hover', 'f_s_e_h', '', '' ),
							self::get_text_shadow( ' .tb_signup_errors:hover', 't_sh_e_h', '' ),
						)
					)
				) )
			) ),
			// Border
			self::get_expand( 'b', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border( ' .tb_signup_errors', 'b_e' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_border( ' .tb_signup_errors:hover', 'b_e_s', '' )
						)
					)
				) )
			) ),
			// Padding
			self::get_expand( 'p', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_padding( ' .tb_signup_errors', 'e_p' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding( ' .tb_signup_errors:hover', 'e_p_h', 'h' )
						)
					)
				) )
			) ),
			// Rounded Corners
			self::get_expand( 'r_c', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border_radius( ' .tb_signup_errors', 'e_r_c' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius( ' .tb_signup_errors:hover', 'e_r_c', 'h' )
						)
					)
				) )
			) ),
			// Shadow
			self::get_expand( 'sh', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_box_shadow( ' .tb_signup_errors', 'e_sh' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow( ' .tb_signup_errors:hover', 'e_sh', 'h' )
						)
					)
				) )
			) )
		);

		$signup_success = array(
			//background
			self::get_expand( 'bg', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_color( ' .tb_signup_success', 'bg_c_s', 'bg_c', 'background-color' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_color( ' .tb_signup_success:hover', 'bg_c_s_h', 'bg_c', 'background-color', '' )
						)
					)
				) )
			) ),
			// Font
			self::get_expand( 'f', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_font_family( ' .tb_signup_success', 'f_f_s' ),
							self::get_color( ' .tb_signup_success', 'f_c_s' ),
							self::get_font_size( ' .tb_signup_success', 'f_s_s' ),
							self::get_text_shadow( ' .tb_signup_success', 't_sh_s' ),
						)
					),
					'h' => array(
						'options' => array(
							self::get_font_family( ' tb_signup_success:hover', 'f_f_s_h', '' ),
							self::get_color( ' .tb_signup_success:hover', 'f_c_s_h', null, null, '' ),
							self::get_font_size( ' .tb_signup_success:hover', 'f_s_s_h', '', '' ),
							self::get_text_shadow( ' .tb_signup_success:hover', 't_sh_s_h', '' ),
						)
					)
				) )
			) ),
			// Border
			self::get_expand( 'b', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border( ' .tb_signup_success', 'b_s' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_border( ' .tb_signup_success:hover', 'b_s_s', '' )
						)
					)
				) )
			) ),
			// Padding
			self::get_expand( 'p', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_padding( ' .tb_signup_success', 'e_p_s' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding( ' .tb_signup_success:hover', 'e_p_s_h', 'h' )
						)
					)
				) )
			) ),
			// Rounded Corners
			self::get_expand( 'r_c', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border_radius( ' .tb_signup_success', 'e_r_c_s' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius( ' .tb_signup_success:hover', 'e_r_c_s', 'h' )
						)
					)
				) )
			) ),
			// Shadow
			self::get_expand( 'sh', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_box_shadow( ' .tb_signup_success', 'e_sh_s' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow( ' .tb_signup_success:hover', 'e_sh_s', 'h' )
						)
					)
				) )
			) )
		);

		return
			array(
				'type' => 'tabs',
				'options' => array(
					'g' => array(
						'options' => $general
					),
					'm_t' => array(
						'options' => $this->module_title_custom_style()
					),
					'labels' => array(
						'label' => __( 'Labels', 'themify' ),
						'options' => $labels
					),
					'inputs' => array(
						'label' => __( 'Input Fields', 'themify' ),
						'options' => $inputs
					),
					'send_button' => array(
						'label' => __( 'Submit Button', 'themify' ),
						'options' => $send_button
					),
					'signup_error' => array(
						'label' => __( 'Error Message', 'themify' ),
						'options' => $signup_error
					),
					'signup_success' => array(
						'label' => __( 'Success Message', 'themify' ),
						'options' => $signup_success
					),
				)
			);
	}

	protected function _visual_template() {
		$module_args = self::get_module_args();
		?>
        <div class="module module-<?php echo $this->slug; ?> {{ data.css }}">
            <# if ( data.mod_title ) { #>
			<?php echo $module_args['before_title']; ?>{{{ data.mod_title }}}<?php echo $module_args['after_title']; ?>
            <# } #>
            <form class="tb_signup_form" name="tb_signup_form">
                <div>
                    <label>
                        <span class="tb_signup_label">{{{ data.l_name }}}</span>
                    </label>
                    <div class="row_inner">
                        <div class="col2-1 first">
                            <label>
                                <input type="text" name="first_n"/>
                                <span>{{{ data.l_firstname }}}</span>
                            </label>
                        </div>
                        <div class="col2-1 last">
                            <label>
                                <input type="text" name="last_n"/>
                                <span>{{{ data.l_lastname }}}</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div>
                    <label>
                        <span class="tb_signup_label" data-required="yes">{{{ data.l_username }}}</span>
                        <input type="text" name="usr" />
                    </label>
                </div>
                <div>
                    <label>
                        <span class="tb_signup_label" data-required="yes">{{{ data.l_email }}}</span>
                        <input type="email" name="email" />
                    </label>
                </div>
                <div>
                    <label>
                        <span class="tb_signup_label" data-required="yes">{{{ data.l_password }}}</span>
                        <input type="password" name="pwd" />
                    </label>
                </div>
                <div>
                    <label>
                        <span class="tb_signup_label">{{{ data.l_bio }}}</span>
                        <textarea name="bio"></textarea>
                    </label>
                    <# if ( '' !== data.desc ) { #>
                    <p>{{{ data.desc }}}</p>
                    <# } #>
                </div>

				<# if ( 'yes' === data.optin ) { #>
					<div>
						<label>
							<input type="checkbox" name="optin" /> 
							<span class="tb_signup_optin">{{{ data.optin_label }}}</span>
						</label>
					</div>
				<# } #>

				<# if ( 'on' === data.gdpr ) { #>
					<div>
						<label>
							<input type="checkbox" name="gdpr" required="required" /> 
							<span class="tb_signup_gdpr">{{{ data.gdpr_label }}}</span>
						</label>
					</div>
				<# } #>

                <button>{{{ data.l_submit }}}</button>
            </form>
        </div>
		<?php
	}

	/**
	 * Render plain content for static content.
	 *
	 * @param array $module
	 *
	 * @return string
	 */
	public function get_plain_content( $module ) {
		return '';
	}

}

Themify_Builder_Model::register_module( 'TB_Signup_Form_Module' );
