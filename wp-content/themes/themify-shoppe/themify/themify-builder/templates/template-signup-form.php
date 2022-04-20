<?php
if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Sing Up
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */

$fields_default = array(
	'mod_title' => '',
	'success_action' => 'c',
	'redirect_to' => '',
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
	'provider' => '',
	'gdpr' => '',
	'gdpr_label' => '',
	'css' => '',
	'animation_effect' => '',
);
$fields_args = wp_parse_args( $args['mod_settings'], $fields_default );
unset( $args['mod_settings'] );

$mod_name=$args['mod_name'];
$builder_id = $args['builder_id'];
$element_id = isset($args['element_id'])?'tb_'.$args['element_id']:$args['module_ID'];

$container_class = apply_filters( 'themify_builder_module_classes', array(
	'module',
	'module-' . $mod_name,
	$element_id,
	$fields_args['css'],
	self::parse_animation_effect( $fields_args['animation_effect'], $fields_args )
), $mod_name, $element_id, $fields_args );

$container_props = apply_filters( 'themify_builder_module_container_props', array(
	'class' => implode( ' ', $container_class ),
), $fields_args, $mod_name, $element_id );
$args = null;
$nonce = wp_create_nonce( 'tb_signup_nonce' );
//Store the user role as transient for security reason and use it in signup_process function
set_transient( 'tb_signup_' . $nonce, $fields_args['u_role'], HOUR_IN_SECONDS );
?>
<!-- module signup form -->
<div <?php echo self::get_element_attributes( self::sticky_element_props( $container_props, $fields_args ) ); ?>>
	<?php	$container_props = $container_class = null;
		do_action('themify_builder_background_styling',$builder_id,array('styling'=>$fields_args,'mod_name'=>$mod_name),$element_id,'module');
	?>
	<?php if ( $fields_args['mod_title'] !== '' ) : ?>
		<?php echo $fields_args['before_title'] . apply_filters( 'themify_builder_module_title', $fields_args['mod_title'], $fields_args ) . $fields_args['after_title']; ?>
	<?php endif; ?>
    <form class="tb_signup_form" name="tb_signup_form">
        <div class="tb_signup_messages"></div>
		<?php if ( 'm' === $fields_args['success_action'] && !empty( $fields_args['msg_success'] ) ): ?>
            <div class="tb_signup_messages tb_signup_success"><?php echo esc_html( $fields_args['msg_success'] ) ?></div>
		<?php endif; ?>
        <div>
            <label>
                <span class="tb_signup_label"><?php echo esc_html( $fields_args['l_name'] ) ?></span>
            </label>
            <div class="row_inner">
                <div class="col2-1 first">
                    <label>
                        <input type="text" name="first_n"/>
                        <span><?php echo esc_html( $fields_args['l_firstname'] ) ?></span>
                    </label>
                </div>
                <div class="col2-1 last">
                    <label>
                        <input type="text" name="last_n"/>
                        <span><?php echo esc_html( $fields_args['l_lastname'] ) ?></span>
                    </label>
                </div>
            </div>
        </div>
        <div>
            <label>
                <span class="tb_signup_label" data-required="yes"><?php echo esc_html( $fields_args['l_username'] ) ?></span>
                <input type="text" name="usr" />
            </label>
        </div>
        <div>
            <label>
                <span class="tb_signup_label" data-required="yes"><?php echo esc_html( $fields_args['l_email'] ) ?></span>
                <input type="email" name="email" />
            </label>
        </div>
        <div>
            <label>
                <span class="tb_signup_label" data-required="yes"><?php echo esc_html( $fields_args['l_password'] ) ?></span>
                <input type="password" name="pwd" />
            </label>
        </div>
        <div>
            <label>
                <span class="tb_signup_label"><?php echo esc_html( $fields_args['l_bio'] ) ?></span>
                <textarea name="bio"></textarea>
            </label>
			<?php if ( $fields_args['desc'] !== '' ): ?>
                <p><?php echo esc_html( $fields_args['desc'] ) ?></p>
			<?php endif; ?>
        </div>

		<?php if ( $fields_args['optin'] === 'yes' ) : ?>
			<?php
			if ( ! class_exists( 'Builder_Optin_Services_Container' ) )
				include_once( THEMIFY_BUILDER_INCLUDES_DIR. '/optin-services/base.php' );
			$optin_instance = Builder_Optin_Services_Container::get_instance()->get_provider( $fields_args['provider'] );
			if ( $optin_instance ) : ?>
				<div>
					<label>
						<input type="hidden" name="optin-provider" value="<?php echo esc_attr( $fields_args['provider'] ); ?>" />
						<?php
						foreach ( $optin_instance->get_options() as $provider_field ) :
							if ( isset( $provider_field['id'] ) && isset( $fields_args[ $provider_field['id'] ] ) ) : ?>
								<input type="hidden" name="optin-<?php echo $provider_field['id']; ?>" value="<?php echo esc_attr( $fields_args[ $provider_field['id'] ] ); ?>" />
							<?php endif;
						endforeach;
						?>
						<input type="checkbox" name="optin" value="1" /> 
						<span class="tb_signup_optin"><?php echo esc_html( $fields_args['optin_label'] ) ?></span>
					</label>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( $fields_args['gdpr'] === 'on' ) : ?>
			<div>
				<label>
					<input type="checkbox" name="gdpr" required="required" /> 
					<span class="tb_signup_gdpr"><?php echo esc_html( $fields_args['gdpr_label'] ); ?></span>
				</label>
			</div>
		<?php endif; ?>

        <button name="tb_submit"
                type="submit"><?php echo esc_html( $fields_args['l_submit'] ) ?></button>
        <?php if('c' === $fields_args['success_action']): ?>
            <input type="hidden" name="redirect" value="<?php echo esc_url( $fields_args['redirect_to'] ); ?>"/>
        <?php endif; ?>
        <input type="hidden" name="nonce" value="<?php echo $nonce ?>"/>
        <input type="hidden" name="tb_e_admin" value="<?php echo $fields_args['e_admin']; ?>"/>
        <input type="hidden" name="tb_e_user" value="<?php echo $fields_args['e_user']; ?>"/>
    </form>

</div><!-- /module signup form -->
