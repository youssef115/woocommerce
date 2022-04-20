(function ( $,window,document ) {
        'use strict';
	/**
	 * Themify Global Styles Manager
	 * The resources that manage Global Styles
	 *
	 * @since 4.5.0
	 */

	var themifyGS = function () {

		this.form = document.getElementById( 'tb_admin_new_gs' );
		this.loadAddNewForm();
		this.addNew();
		this.deleteStyle();
		this.restore();
		this.scalePreview();

	};

	/**
	 * Handle add new Global Style functionality
	 *
	 * @since 4.5.0
	 * @returns void
	 */
	themifyGS.prototype.addNew = function () {

		var addNew = document.getElementsByClassName( 'tb_admin_save_gs' );
		if ( addNew.length === 0 ) {
			return;
		}
		addNew = addNew[0];
		var globalStyle = this;
		addNew.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			if ( !globalStyle.validateForm() ) {
				alert( themifyGlobalStylesVars.messages.formValid );
				return;
			}
			e.target.text = themifyGlobalStylesVars.messages.creating;
			$.ajax( {
				type: 'POST',
				url: themifyGlobalStylesVars.ajaxurl,
				dataType: 'json',
				data: {
					action: 'tb_save_custom_global_style',
					nonce: themifyGlobalStylesVars.nonce,
					form_data: $( globalStyle.form ).serialize()
				},
				success: function ( resp ) {
					if ( 'failed' === resp.status ) {
						alert( resp.msg );
						e.target.text = themifyGlobalStylesVars.messages.create;
					} else if ( 'success' === resp.status ) {
						window.location = resp.url;
					} else {
						// Something went wrong with save Global Style response
						e.target.text = themifyGlobalStylesVars.messages.create;
					}
				}
			} );
		} );
	};

	/**
	 * Validate add new form
	 *
	 * @since 4.5.0
	 * @returns bool
	 */
	themifyGS.prototype.validateForm = function () {

		var valid = true,
			formData = $( this.form ).serializeArray();
		$.each( formData, function ( i, field ) {
			if ( '' == field.value ) {
				valid = false;
				return false;
			}
		} );
		return valid;
	};

	/**
	 * Load popup for to create new Global Style
	 *
	 * @since 4.5.0
	 * @returns void
	 */
	themifyGS.prototype.loadAddNewForm = function () {

		var $addNew = $( '.tb_add_new_gs' );
		if ( $addNew.length === 0 ) {
			return;
		}
		$addNew.magnificPopup( {
			type: 'inline',
			midClick: true,
			callbacks: {
				close: function () {
					document.getElementById( "tb_admin_new_gs" ).reset();
				}
			}
		} );
	};

	/**
	 * Handle delete Global Style functionality
	 *
	 * @since 4.5.0
	 * @returns void
	 */
	themifyGS.prototype.deleteStyle = function () {

		var $removeBtn = $( '.tb_remove_gs' );
		if ( $removeBtn.length === 0 ) {
			return;
		}
		$removeBtn.click( function ( e ) {
			e.preventDefault();
			var $this = $( this ),
				pageStatus = $this.parents('.tb_admin_gs_list').data('list'),
				msg = 'publish' === pageStatus ? themifyGlobalStylesVars.messages.deleteConfirm : themifyGlobalStylesVars.messages.deleteConfirm2;
			if ( !window.confirm( msg ) ) {
				return;
			}
			$this.parents( '.tb_gs_element' ).fadeOut();
			$.ajax( {
				type: 'POST',
				url: themifyGlobalStylesVars.ajaxurl,
				dataType: 'json',
				data: {
					action: 'tb_delete_global_style',
					nonce: themifyGlobalStylesVars.nonce,
					status: pageStatus,
					id: $this.attr( 'data-id' )
				},
				success: function ( resp ) {
					if ( 'failed' === resp.status ) {
						alert( resp.msg );
						$this.parents( '.tb_gs_element' ).fadeIn();
					}
				}
			} );
		} );

	};

	/**
	 * Handle restore Global Style functionality
	 *
	 * @since 4.5.7
	 * @returns void
	 */
	themifyGS.prototype.restore = function () {

		var $restoreBtn = $( '.tb_gs_restore' );
		if ( $restoreBtn.length === 0 ) {
			return;
		}
		$restoreBtn.click( function ( e ) {
			e.preventDefault();
			var $this = $( this );
			$this.parents( '.tb_gs_element' ).fadeOut();
			$.ajax( {
				type: 'POST',
				url: themifyGlobalStylesVars.ajaxurl,
				dataType: 'json',
				data: {
					action: 'tb_restore_global_style',
					nonce: themifyGlobalStylesVars.nonce,
					id: $this.attr( 'data-id' )
				},
				success: function ( resp ) {
					if ( 'failed' === resp.status ) {
						alert( resp.msg );
						$this.parents( '.tb_gs_element' ).fadeIn();
					}
				}
			} );
		} );

	};

	/**
	 * Scale the preview
	 *
	 * @since 4.5.0
	 * @returns void
	 */
	themifyGS.prototype.scalePreview = function () {

		$( ".themify_builder_content" ).each( function () {
			var $el = $( this ),
				$wrapper = $el.parent(),
				scale = Math.min( $wrapper.width() / $el.outerWidth(), $wrapper.height() / $el.outerHeight() );
			$el.css( {
				transform: "translate(-50%, -50%) " + "scale(" + scale + ")"
			} );
		} );


	};

	new themifyGS();

}( jQuery, window, document ));
