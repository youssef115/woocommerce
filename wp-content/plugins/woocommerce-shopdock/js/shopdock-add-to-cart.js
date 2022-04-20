(function($){
    'use strict';
     $(function() {
	/////////////////////////////////////////////
	// Cart slider
	/////////////////////////////////////////////
	cart_slider();

	function cart_slider() {
		var carousel = $('.cart-slides');
		carousel.fadeIn( 150 );
		// Display more items on larger displays
                var visibleItems,scrollItems;
		if ( $(window).width() <= 1366 ) {
			visibleItems = 6;
			scrollItems = 6;
		} else {
			visibleItems = 10;
			scrollItems = 10;
		}
		carousel.jcarousel({
			visible: visibleItems,
			scroll: scrollItems,
			animation: 500
			//auto: 0,
			//initCallback: carousel_callback
		});
	}

	$(window).on("debouncedresize", cart_slider);

	/////////////////////////////////////////////
	// Add to cart ajax
	/////////////////////////////////////////////

	$('body').on('adding_to_cart', function() {
		$('#cart-loader').removeClass('hide');
	}).on('added_to_cart', function() {
		var shopdock = $('#addon-shopdock');
		shopdock.slideDown();
		if (shopdock.size() > 0) {
			$.ajax( {
				url: wc_add_to_cart_params.ajax_url,
				type : 'POST',
				data : {action: 'shopdock_dock_bar'},
				success: function(data){
					shopdock.empty().append( $( data ).unwrap() );
					// remove class dock-on
					$('body').removeClass('dock-on');
					$('#cart-loader').addClass('hide');
					cart_slider();
				}
			} );
		}
	});
    });

}(jQuery));