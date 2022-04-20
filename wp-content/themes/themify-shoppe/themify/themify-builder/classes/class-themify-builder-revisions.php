<?php
/**
 * The file that enable builder revisions.
 *
 * Themify_Builder_Revisions class provide hooks and filter to WP Revisions API
 * This enable builder being tracked by WP Revisions and able to restore
 * the revision for builder.
 * 
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

/**
 * The Builder Revision class.
 *
 * This is used to handle all revisions operation and method.
 *
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 * @author     Themify
 */
class Themify_Builder_Revisions {
        
	/**
	 * Builder meta key.
	 * 
	 * @access private
	 * @var string $builder_meta_key
	 */
	private $builder_meta_key;

	/**
	 * Constructor.
	 * 
	 * @param object Themify_Builder $builder 
	 */
	public function __construct() {  
		$this->builder_meta_key = $GLOBALS['ThemifyBuilder_Data_Manager']->meta_key;

		$ajax_events = array(
			'load_revision_lists',
			'save_revision' ,
			'restore_revision_page',
			'delete_revision'
		);

		foreach ( $ajax_events as $ajax_event ) {  
		    add_action( 'wp_ajax_tb_' . $ajax_event, array( $this, $ajax_event ) );
		}
	
		add_filter('_wp_post_revision_fields', array( $this, 'post_revision_fields'), 10, 2);
		
		add_action( 'wp_restore_post_revision', array( $this, 'restore_revision' ), 10, 2 );
	}

	/**
	 * Ajax Get all post revisions list.
	 * 
	 * @access public
	 */
	public function load_revision_lists() {
	
		check_ajax_referer('tb_load_nonce', 'tb_load_nonce');

		$post_id = (int) $_POST['postid'];
		$revisions = wp_get_post_revisions( $post_id, array(
			'posts_per_page' => 50,
		) );
		$can_edit_post = $post_id===0?true:current_user_can( 'edit_post', $post_id );
		include THEMIFY_BUILDER_INCLUDES_DIR . '/themify-builder-revision-list.php';
		wp_die();
	}
	/**
	 * Hook themify builder field to revisions fields.
	 * 
	 * @access public
	 * @param array $fields 
	 * @return array
	 */
	public function post_revision_fields( $fields,$post) {
		if(function_exists('wp_print_revision_templates')){
			$fields[ $this->builder_meta_key ] = esc_html__( 'Themify Builder', 'themify' );
			add_filter('_wp_post_revision_field__themify_builder_settings_json', array( $this, 'post_revision_field'), 10, 4 );
		}
		return $fields;
	}
	/**
	 * Render the builder output in revision compare slider.
	 * 
	 * @access public
	 * @param string $value 
	 * @param string $field 
	 * @return string
	 */
	public function post_revision_field( $value, $field,$revision,$type ) {
		if( is_object( $revision ) ) {
			global $ThemifyBuilder;
			$builder_data  = $ThemifyBuilder->get_builder_data($revision->ID);
		
			if (!empty( $builder_data ) && is_array( $builder_data ) ) {
				return Themify_Builder_Component_Base::retrieve_template('builder-output.php', array('builder_output' => $builder_data, 'builder_id' => $revision->ID), '', '', false);
			}
		}
	}

	/**
	 * Ajax save revision.
	 * 
	 * @access public
	 */
	public function save_revision() {
		check_ajax_referer('tb_load_nonce', 'tb_load_nonce');
		if(empty($_POST['data'])){
			wp_send_json_error( esc_html__( 'Cannot save revision, please try again.', 'themify' ) );
		}
		else{
			$post_id = (int) $_POST['postid'];
			$post = get_post( $post_id);
			$rev_comment = !empty($_POST['rev_comment'])?sanitize_text_field( $_POST['rev_comment'] ):'';

			if ( ! current_user_can( 'edit_post', $post_id ) )
				wp_send_json_error( esc_html__( 'Error. You do not have access to save revision.', 'themify' ) );

			if ( ! wp_revisions_enabled( $post ) || !post_type_supports( $post->post_type, 'revisions' ) ) 
				wp_send_json_error( esc_html__( 'Error. The revision is not enable in this post or has been reach the revision post limit.', 'themify' ) );

			if ( is_object( $post ) ){
				$post = get_object_vars( $post );
			}
			unset( $post['post_modified'],$post['post_modified_gmt'] );
			$new_revision_id = _wp_put_post_revision( $post );
			if ( ! is_wp_error( $new_revision_id ) ) {
				if(!empty($rev_comment)){
					update_metadata( 'post', $new_revision_id, '_builder_custom_rev_comment', $rev_comment );
				}
				$builder_data = json_decode(stripslashes_deep($_POST['data']), true);
				if(empty($builder_data)){
					$builder_data=array();
				}
				$GLOBALS['ThemifyBuilder_Data_Manager']->save_data( $builder_data, $new_revision_id,'main', $_POST['sourceEditor']);
				wp_send_json_success( $new_revision_id );
			} else {
				wp_send_json_error( esc_html__( 'Cannot save revision, please try again.', 'themify' ) );
			}
		}
		wp_die();
	}



	/**
	 * Hook to restore revision.
	 * 
	 * @access public
	 * @param int $post_id 
	 * @param int $rev_id 
	 */
	public function restore_revision( $post_id, $rev_id ) {
		global $ThemifyBuilder;
		$builder_data  = $ThemifyBuilder->get_builder_data($rev_id);
		if ( !empty($builder_data) ){
		    $GLOBALS['ThemifyBuilder_Data_Manager']->save_data( $builder_data, $post_id );
		}
	}

	/**
	 * Ajax restore revision.
	 * 
	 * @access public
	 */
	public function restore_revision_page() {
	    check_ajax_referer('tb_load_nonce', 'tb_load_nonce');
	    $rev_id = (int) $_POST['revid'];
	    $revision = wp_get_post_revision( $rev_id );

	    if ( ! current_user_can( 'edit_post', $revision->post_parent ) ){
			wp_send_json_error( esc_html__( 'Error. You do not have access to restore revision.', 'themify' ) );
		return;
	    }

	    if ( $revision ) {
		    global $ThemifyBuilder;
		    $builder_data  = $ThemifyBuilder->get_builder_data($rev_id);
		   if (is_array($builder_data)  ) {
			    $response=array('builder_data'=>$builder_data);
			    wp_send_json( $response );
		    } else {
			    wp_send_json_error( esc_html__( 'Cannot restore this revision. builder data not founded.', 'themify' ) );
		    }
	    } else {
		    wp_send_json_error( esc_html__( 'Revision post is not found or invalid ID', 'themify' ) );
	    }
	    wp_die();
	}

	/**
	 * Ajax delete revision.
	 * 
	 * @access public
	 * @return json
	 */
	public function delete_revision() {
	    check_ajax_referer('tb_load_nonce', 'tb_load_nonce');	
	    $rev_id = (int) $_POST['revid'];
	    $revision = wp_get_post_revision( $rev_id );
	    if ( ! current_user_can( 'edit_post', $revision->post_parent ) ){
		wp_send_json_error( esc_html__( 'Error. You do not have access to delete revision.', 'themify' ) );
		return;
	    }

	    $delete = wp_delete_post_revision( $rev_id );
	    if ( ! is_wp_error( $delete ) ) {
		    wp_send_json_success( $rev_id );
	    } else {
		    wp_send_json_error( esc_html__( 'Unable to delete this revision, please try again!', 'themify' ) );
	    }
	    wp_die();
	}
	
	/**
	 * create builder revision
	 * 
	 * @access public
	 * @param int $post_id 
	 * @param object $post 
	 */
	public static function create_revision($post_id, $builder_data,$source_editor){
		if( !wp_is_post_revision( $post_id )){
			$post = get_post($post_id);
			if(!empty($post) &&  'auto-draft' !== $post->post_status){
				if(wp_revisions_enabled($post) && post_type_supports( $post->post_type, 'revisions' )){
					unset( $post->post_modified,$post->post_modified_gmt );
					$rev_id = _wp_put_post_revision( $post );
					if ( ! is_wp_error( $rev_id ) ) {
						if(empty($builder_data)){
							$builder_data=array();
						}
						$GLOBALS['ThemifyBuilder_Data_Manager']->save_data( $builder_data, $rev_id,'main', $source_editor);
						return $rev_id;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Check if revision has builder data.
	 * 
	 * @access public
	 * @param int $post_id 
	 * @return boolean
	 */
	public function check_has_builder( $post_id ) {
		$builder_data  = get_metadata( 'post', $post_id, $this->builder_meta_key, true );
		return ! empty( $builder_data );
	}
}
