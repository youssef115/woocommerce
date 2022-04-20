<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Login
 * 
 * Access original fields: $args['mod_settings']
 * @author Themify
 */

$fields_default = array(
	'mod_title' => '',
	'content_text' => '',
	'remember_me_display' => 'show',
	'redirect_to' => add_query_arg( 'login_success', 1 ),
	'fail_action' => 'r',
	'redirect_fail' => '',
	'msg_fail' => '',
	'label_username' => '',
	'label_password' => '',
	'label_remember' => '',
	'label_log_in' => '',
	'label_forgotten_password' => '',
	'css' => '',
	'lostpasswordform_redirect_to' => '',
	'lostpasswordform_label_username' => '',
	'lostpasswordform_label_reset' => '',
	'animation_effect' => ''
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

if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $fields_args['global_styles'];
}
$container_props = apply_filters( 'themify_builder_module_container_props', array(
	'class' => implode( ' ', $container_class),
), $fields_args, $mod_name, $element_id );
$args=null;
?>
<!-- module login -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
	<?php $container_props=$container_class=null;
	    do_action('themify_builder_background_styling',$builder_id,array('styling'=>$fields_args,'mod_name'=>$mod_name),$element_id,'module');
	?>
	<?php if ( $fields_args['mod_title'] !== '' ) : ?>
		<?php echo $fields_args['before_title'] . apply_filters( 'themify_builder_module_title', $fields_args['mod_title'], $fields_args ) . $fields_args['after_title']; ?>
	<?php endif; ?>

	<?php if ( is_user_logged_in() && ! Themify_Builder_Model::is_front_builder_activate() ) : ?>

		<?php echo apply_filters( 'themify_builder_module_content', $fields_args['content_text'] ); ?>

	<?php else : ?>
		<?php if ( $fields_args['fail_action'] === 'm' && isset( $_GET['login_error'] ) ) : ?>
			<div class="tb_login_error"><?php echo esc_html( $fields_args['msg_fail'] ) ?></div>
		<?php endif; ?>

		<form class="tb_login_form" name="loginform" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ) ?>" method="post">
			<p class="tb_login_username">
				<label>
					<span class="tb_login_username_text"><?php echo esc_html( $fields_args['label_username'] ) ?></span>
					<input type="text" name="log" class="" value="" size="20" />
				</label>
			</p>
			<p class="tb_login_password">
				<label>
					<span class="tb_login_password_text"><?php echo esc_html( $fields_args['label_password'] ) ?></span>
					<input type="password" name="pwd" class="" value="" size="20" />
				</label>
			</p>
			<div class="tb_login_links">
				<a href="<?php echo esc_url( network_site_url( 'wp-login.php?action=lostpassword', 'login_post' ) ); ?>"><?php echo esc_html( $fields_args['label_forgotten_password'] ); ?></a>
			</div>
			<?php if ( $fields_args['remember_me_display'] === 'show' ) : ?>
			<p class="tb_login_remember">
				<label>
					<input name="rememberme" type="checkbox" value="forever" /> 
					<span class="tb_login_remember_text"><?php echo esc_html( $fields_args['label_remember'] ); ?></span>
				</label>
			</p>
			<?php endif; ?>
			<p class="tb_login_submit">
				<button name="wp-submit"><?php echo esc_html( $fields_args['label_log_in'] ) ?></button>
				<input type="hidden" name="redirect_to" value="<?php echo esc_url( $fields_args['redirect_to'] ) ?>" />
				<input type="hidden" name="tb_login" value="1" />

				<?php if ( $fields_args['fail_action'] === 'c' && ! empty( $fields_args['redirect_fail'] ) ) : ?>
					<input type="hidden" name="tb_redirect_fail" value="<?php echo esc_url( $fields_args['redirect_fail'] ) ?>" />
				<?php elseif ( $fields_args['fail_action'] === 'm' ) : ?>
					<input type="hidden" name="tb_redirect_fail" value="<?php echo esc_url( add_query_arg( 'login_error', 1 ) ) ?>" />
				<?php endif; ?>
			</p>

		</form>

		<form class="tb_lostpassword_form" name="lostpasswordform" action="<?php echo esc_url( network_site_url( 'wp-login.php?action=lostpassword', 'login_post' ) ); ?>" method="post" style="display: none;">
			<p class="tb_lostpassword_username">
				<label>
					<span class="tb_lostpassword_username_text"><?php echo esc_html( $fields_args['lostpasswordform_label_username'] ) ?></span>
					<input type="text" name="user_login" class="" value="" size="20" />
				</label>
			</p>
			<?php if ( ! empty( $fields_args['lostpasswordform_redirect_to'] ) ) : ?>
				<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $fields_args['lostpasswordform_redirect_to'] ); ?>" />
			<?php endif; ?>
			<p class="tb_lostpassword_submit">
				<button><?php echo esc_html( $fields_args['lostpasswordform_label_reset'] ) ?></button>
			</p>

			<div class="tb_login_links">
				<a href="<?php echo esc_url( site_url( 'wp-login.php' ) ); ?>"><?php echo esc_html( $fields_args['label_log_in'] ); ?></a>
			</div>
		</form>

	<?php endif; ?>

</div><!-- /module login -->