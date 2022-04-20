<?php
/**
 * Handles WPML compatibility.
 *
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

if ( ! class_exists( 'Themify_Builder_WPML' ) ) :
/**
 * WPML plugin compatibility.
 *
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 * @author     Themify
 */
class Themify_Builder_WPML {

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return    A single instance of this class.
	 */
	public static function get_instance() {
		static $instance = null;
		if ( $instance === null ) {
			$instance = new self;
		}

		return $instance;
	}

	private function __construct() {
		add_action( 'themify_builder_save_data', array( $this, 'reset_stylesheets' ), 10, 2 );
	}

	public function reset_stylesheets( $builder_data, $post_id ) {
		$translations = $this->get_translations( $post_id );
		foreach ( $translations as $lang => $translated_id ) {

			/* remove the builder-generated.css file */
			$filesystem = Themify_Filesystem::get_instance();
			$css_file = Themify_Builder_Stylesheet::get_stylesheet( 'bydir', (int) $translated_id )['url'];
			if ( $filesystem->execute->is_file( $css_file ) ) {
				$filesystem->execute->delete( $css_file );
			}
			$tmp_file = Themify_Builder_Stylesheet::getTmpPath( $css_file );
			if ( $filesystem->execute->is_file( $tmp_file ) ) {
				$filesystem->execute->delete( $tmp_file );
			}
		}
	}

	/**
	 * Gets a post ID and returns all translations of that post in form of {lang_key} => {post_id}
	 *
	 * @param $post_id int post ID to get the translations
	 * @return array
	 */
	public function get_translations( $post_id ) {
		global $sitepress;

		$default_language = $sitepress->get_default_language();
		$trid = $sitepress->get_element_trid( $post_id, 'post_page' );
		$translations = $sitepress->get_element_translations( $trid, 'post_page' );
		if ( is_array( $translations ) ) {
			unset( $translations[ $default_language ] );
			return wp_list_pluck( $translations, 'element_id' );
		}

		return array();
	}
}
endif;