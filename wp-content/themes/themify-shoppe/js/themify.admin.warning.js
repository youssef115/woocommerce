/* Themify Admin Warning Removal
 * Dismiss warnings shown in WP admin and sets a WP option so they're never shown again.
 */

(function($){

    $(document).ready(function(){

        $('body').on('click', '.themify-close-warning', function(){
            var $self = $(this),
                data = {
                    action: 'themify_dismiss_warning',
                    nonce: $self.data('nonce'),
                    warning: $self.data('warning')
                };
            $.post(ajaxurl, data, function(response) {
                if ( response ) {
                    $self.parent().fadeOut().remove();
                }
            });
        });

    });

})(jQuery);