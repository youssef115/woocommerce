(function($){
    'use strict';
    $(function() {
    /////////////////////////////////////////////
    // Check is_mobile
	/////////////////////////////////////////////
	$('body').addClass( (document.body.clientWidth < 600 ) ? 'is_mobile' : 'is_desktop');
    });
}(jQuery));