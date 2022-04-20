// Themify Lightbox and Fullscreen /////////////////////////
var ThemifyGallery = {};

(function($){

	'use strict';

ThemifyGallery = {
	
	origHash : null,
	config: {
		fullscreen: themifyScript.lightbox.fullscreenSelector,
		lightbox: themifyScript.lightbox.lightboxSelector,
		lightboxGallery: themifyScript.lightbox.gallerySelector,
		lightboxContentImages: themifyScript.lightbox.lightboxContentImagesSelector,
		is_touch:Themify.isTouch,
		context: document
	},
	
	init: function(config){
                if(typeof config === 'object'){
                    if (config) {
                            $.extend(ThemifyGallery.config, config);
                    }
                    if (config.extraLightboxArgs) {
                        for (var attrname in config.extraLightboxArgs) {
                            themifyScript.lightbox[attrname] = config.extraLightboxArgs[attrname];
                        }
                    }
                }
		this.parseArgs();
		this.doLightbox();
		this.openAnchor();
	},
	parseArgs: function(){
		$.each(themifyScript.lightbox, function(index, value){
			if( 'false' == value || 'true' == value ){
				themifyScript.lightbox[index] = 'false'!=value;
			} else if( parseInt(value) ){
				themifyScript.lightbox[index] = parseInt(value);
			} else if( parseFloat(value) ){
				themifyScript.lightbox[index] = parseFloat(value);
			}
		});
	},
	
	doLightbox: function(){
		var context = this.config.context,
			patterns = {},
			socialSharing = '<div class="tb_social_sharing">' +
				'<a href="#" class="ti-facebook" data-type="facebook"></a>' +
				'<a href="#" class="ti-twitter-alt" data-type="twitter"></a>' +
				'<a href="#" class="ti-email" data-type="email"></a>' +
				'</div>';
		
		if(typeof $.fn.magnificPopup !== 'undefined' && typeof themifyScript.lightbox.lightboxOn !== 'undefined') {
			// Lightbox Link
			$(context).on('click', ThemifyGallery.config.lightbox, function(event){
				event.preventDefault();
				if ( $('.mfp-wrap.mfp-gallery').length ) return;

				var $self = $(this),
					targetItems,
					$link = ( $self.find( '> a' ).length > 0 ) ? $self.find( '> a' ).attr( 'href' ) : $self.attr('href'),
					$type = ThemifyGallery.getFileType($link),
					$is_video = ThemifyGallery.isVideo($link),
					$groupItems = $type === 'inline' || $type === 'iframe' ? [] : ($self.data('rel')?$('a[data-rel="'+$self.data('rel')+'"]'):$self.closest( '.module_row, .loops-wrapper' ).find( '.themify_lightbox > img' ).parent()),
					index = $groupItems.length > 1 ? $groupItems.index( this ) : 0,
					$title = (typeof $(this).children('img').prop('alt') !== 'undefined') ? $(this).children('img').prop('alt') : $(this).prop('title'),
					$iframe_width = $is_video ? '100%' : (ThemifyGallery.getParam('width', $link)) ? ThemifyGallery.getParam('width', $link) : '94%',
					$iframe_height = $is_video ? '100%' : (ThemifyGallery.getParam('height', $link)) ? ThemifyGallery.getParam('height', $link) : '100%';
				if($iframe_width.indexOf('%') === -1) $iframe_width += 'px';
				if($iframe_height.indexOf('%') === -1) $iframe_height += 'px';

				if($is_video){
					if( ThemifyGallery.isYoutube( $link ) ) {
						var params = ThemifyGallery.getCustomParams( $link );

						// YouTube URL pattern
						if( params ) {
							patterns.youtube = {
								id: 'v=',
								index: 'youtube.com/',
								src: '//www.youtube.com/embed/%id%' + params
							};
						}

						// YouTube sanitize the URL properly
						$link = ThemifyGallery.getYoutubePath( $link );
					} else if( ThemifyGallery.isVimeo( $link ) ) {
						var params = ThemifyGallery.getCustomParams( $link );

						// Vimeo URL pattern
						if( params ) {
							patterns.vimeo = {
								id: '/',
								index: 'vimeo.com/',
								src: '//player.vimeo.com/video/%id%' + params
							};
						}

						$link = $link.split('?')[0];
					}
				}
				if( $groupItems.length > 1 && index !== -1 ) {
					targetItems = [];

					$groupItems.each( function( i, el ) {
						targetItems.push( {
							src: ThemifyGallery.getiFrameLink( $(el).prop( 'href' ) ),
							title: (typeof $(el).find('img').prop('alt') !== 'undefined') ? $(el).find('img').prop('alt') : '',
							type: ThemifyGallery.getFileType( $(el).prop( 'href' ) )
						} );
					} );

					// remove duplicate items (same "src" attr) from the lightbox group
					var targetItems = targetItems.reduce( function( memo, e1 ) {
						var matches = memo.filter( function( e2 ) {
							return e1.src === e2.src;
						} );
						if ( matches.length === 0 )
							memo.push( e1 );
						return memo;
					}, [] );

				} else {
					index = 0; // ensure index is set to 0 so the proper popup shows
					targetItems = {
						src: ThemifyGallery.getiFrameLink( $link ),
						title: $title,
					};
				}

				var iOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream,
                                    iOSScrolling = iOS ? 'scrolling="no" ' : '',
                                    titleWrap = 'no' !== this.dataset.title ? '<div class="mfp-title"></div>':'',
                                    $args = {
					items: targetItems,
					type: $type,
					image: {
						markup: '<div class="mfp-figure">'+
							'<div class="mfp-close"></div>'+
							'<div class="mfp-counter"></div>'+
							'<div class="mfp-img"></div>'+
							'<div class="mfp-bottom-bar">'+
							titleWrap+
							socialSharing+
							'</div>'+
							'</div>',
					},
					iframe: {
						markup: '<div class="mfp-iframe-scaler" style="max-width: '+$iframe_width+' !important; height: '+$iframe_height+';">'+
						'<div class="mfp-close"></div>'+
						'<div class="mfp-iframe-wrapper">'+
						'<iframe class="mfp-iframe" '+'noresize="noresize" frameborder="0" allowfullscreen></iframe>'+
						'</div>'+
						socialSharing +
						'</div>',
						patterns: patterns
					},
					callbacks: {
						open: function() {
							ThemifyGallery.updateHash('open',this);
							ThemifyGallery.openSharing(this);
							var zoomConfig = $self.data( 'zoom-config' ),
								cssRules = {};
							if( !zoomConfig ) { return; }
							zoomConfig = zoomConfig.split( '|' );

							if( typeof zoomConfig[0] !== 'undefined' ) {
								cssRules.width = zoomConfig[0];
							}

							if( typeof zoomConfig[1] !== 'undefined' ) {
								cssRules.height = zoomConfig[1];
							}
							
							$(this.content).parent().css( cssRules );

						},
						change:function () {
							ThemifyGallery.updateHash('open',this);
						},
						close:function () {
							ThemifyGallery.updateHash('close');
						}
					}
				};

				if( $groupItems.length > 1 ) {
					$.extend( $args, {
						gallery: {
							enabled: true
						}
					} );
				}

				if($self.find('img').length > 0) {
					$.extend( $args, {
						mainClass: 'mfp-with-zoom',
						zoom: {
							enabled: !ThemifyGallery.config.is_touch,
							duration: 300,
							easing: 'ease-in-out',
							opener: function() {
								return $self.find('img');
							}
						}
					});
				}

				$args['mainClass'] += $is_video?' video-frame':' standard-frame';
				if(ThemifyGallery.isInIframe()) {
					window.parent.jQuery.magnificPopup.open($args);
				} else {
					$.magnificPopup.open($args, index);
				}
			});
			
			// Images in post content
			$(themifyScript.lightbox.contentImagesAreas, context).each(function() {
				var images = [],
					links = [];
				if(themifyScript.lightbox.lightboxContentImages && themifyScript.lightbox.lightboxGalleryOn){
					$(ThemifyGallery.config.lightboxContentImages, $(this)).filter( function(){
						if(!$(this).parent().hasClass('gallery-icon') && !$(this).hasClass('themify_lightbox')){
							links.push($(this));
							var description = $(this).prop('title');
							if($(this).next('.wp-caption-text').length > 0){
								// If there's a caption set for the image, use it
								description = $(this).next('.wp-caption-text').html();
							} else {
								// Otherwise, see if there's an alt attribute set
								description = $(this).children('img').prop('alt');
							}
							images.push({ src: $(this).prop('href'), title: description, type: 'image' });
							return $(this);
						}
					}).each(function(index) {
						if (links.length > 0) {
							$(this).on('click', function(event){
								event.preventDefault();
								var $self = $(this),
                                                                    titleWrap = 'no' !== this.dataset.title ? '<div class="mfp-title"></div>':'',
                                                                    $args = {
									items: {
										src: images[index].src,
										title: images[index].title
									},
									image: {
										markup: '<div class="mfp-figure">'+
											'<div class="mfp-close"></div>'+
											'<div class="mfp-counter"></div>'+
											'<div class="mfp-img"></div>'+
											'<div class="mfp-bottom-bar">'+
											titleWrap+
											socialSharing+
											'</div>'+
											'</div>',
									},
									type: 'image',
									callbacks: {
										open: function() {
											ThemifyGallery.updateHash('open',this);
											ThemifyGallery.openSharing(this);
										},
										change:function () {
											ThemifyGallery.updateHash('open',this);
										},
										close:function () {
											ThemifyGallery.updateHash('close');
										}
									}
								};
								if($self.find('img').length > 0) {
									$.extend( $args, {
										mainClass: 'mfp-with-zoom',
										zoom: {
											enabled: !ThemifyGallery.config.is_touch,
											duration: 300,
											easing: 'ease-in-out',
											opener: function() {
												return $self.find('img');
											}
										}
									});
								}
								if(ThemifyGallery.isInIframe()) {
									window.parent.jQuery.magnificPopup.open($args);
								} else {
									$.magnificPopup.open($args);
								}
							});
						}
					});
				}
			});
			
			// Images in WP Gallery
			if(themifyScript.lightbox.lightboxGalleryOn){
				$('body').on('click', ThemifyGallery.config.lightboxGallery, function(event){
					if( 'image' !== ThemifyGallery.getFileType( $(this).prop( 'href' ) ) ) {
						return;
					}
					event.preventDefault();
                    var $gallery = $( ThemifyGallery.config.lightboxGallery, $( this ).closest( '.module, .gallery' ) ),
						images = [];
					$gallery.each(function() {
						var description = $(this).prop('title');
						description = '' !== description ? description : (typeof $(this).children('img').prop('alt') !== 'undefined') ? $(this).children('img').prop('alt') : '';
						if($(this).parent().next('.gallery-caption').length > 0){
							// If there's a caption set for the image, use it
							description = $(this).parent().next('.wp-caption-text').html();
						} else if ( $(this).find('.gallery-caption').find('.entry-content').length > 0 ) {
							description = $(this).find('.gallery-caption').find('.entry-content').text();
						}
						images.push({ src: $(this).prop('href'), title: description, type: 'image' });
					});
					var titleWrap = 'no' !== this.dataset.title ? '<div class="mfp-title"></div>':'',
                                            $args = {
						gallery: {
							enabled: true
						},
						image: {
							markup: '<div class="mfp-figure">'+
										'<div class="mfp-close"></div>'+
										'<div class="mfp-counter"></div>'+
										'<div class="mfp-img"></div>'+
										'<div class="mfp-bottom-bar">'+
								titleWrap+
											socialSharing+
										'</div>'+
									'</div>',
						},
						items: images,
						mainClass: 'mfp-with-zoom',
						zoom: {
							enabled: !ThemifyGallery.config.is_touch,
							duration: 300,
							easing: 'ease-in-out',
							opener: function(openerElement) {
								var imageEl = $($gallery[openerElement.index]);
								return imageEl.is('img') ? imageEl : imageEl.find('img');
							}
						},
						callbacks: {
							open: function() {
								ThemifyGallery.updateHash('open',this);
								ThemifyGallery.openSharing(this);
							},
							change:function () {
								ThemifyGallery.updateHash('open',this);
							},
							close:function () {
								ThemifyGallery.updateHash('close');
							}
						}
					};
					if(ThemifyGallery.isInIframe()){
						window.parent.jQuery.magnificPopup.open($args, $gallery.index(this));
					} else {
						$.magnificPopup.open($args, $gallery.index(this));
					}
				});
			}
		}
	},
	
	countItems : function(type){
		var context = this.config.context;
		if('lightbox' === type) return $(this.config.lightbox, context).length + $(this.config.lightboxGallery, context).length + $(ThemifyGallery.config.lightboxContentImages, context).length;
		else return $(this.config.fullscreen, context).length + $(ThemifyGallery.config.lightboxContentImages, context).length;
	},

	isInIframe: function(){
		if( typeof ThemifyGallery.config.extraLightboxArgs !== 'undefined' ) {
			return typeof ThemifyGallery.config.extraLightboxArgs.displayIframeContentsInParent !== 'undefined' && true == ThemifyGallery.config.extraLightboxArgs.displayIframeContentsInParent;
		} else {
			return false;
		}
	},
	
	getFileType: function( itemSrc ) {
		if ( itemSrc.match( /\.(gif|jpg|jpeg|tiff|png)(\?fit=\d+(,|%2C)\d+)?(\&ssl=\d+)?$/i ) ) { // ?fit and &ssl is added by JetPack
			return 'image';
		} else if(itemSrc.match(/\bajax=true\b/i)) {
			return 'ajax';
		} else if(itemSrc.substr(0,1) === '#') {
			return 'inline';
		} else {
			return 'iframe';
		}
	},
	isVideo: function( itemSrc ) {
		return ThemifyGallery.isYoutube( itemSrc )
			|| ThemifyGallery.isVimeo(itemSrc) || itemSrc.match(/\b.mov\b/i)
			|| itemSrc.match(/\b.swf\b/i);
	},
	isYoutube : function( itemSrc ) {
		return itemSrc.match( /youtube\.com\/watch/i ) || itemSrc.match( /youtu\.be/i );
	},
	isVimeo : function( itemSrc ) {
		return itemSrc.match(/vimeo\.com/i);
	},
	getYoutubePath : function( url ) {
		if( url.match( /youtu\.be/i ) ) {
			// convert youtu.be/ urls to youtube.com
			return '//youtube.com/watch?v=' + url.match( /youtu\.be\/([^\?]*)/i )[1];
		} else {
			return '//youtube.com/watch?v=' + ThemifyGallery.getParam( 'v', url );
		}
	},
	/**
	 * Add ?iframe=true to the URL if the lightbox is showing external page
	 * this enables us to detect the page is in an iframe in the server
	 */
	getiFrameLink : function( link ) {
		if( ThemifyGallery.getFileType( link ) === 'iframe' && ThemifyGallery.isVideo( link ) === null ) {
			link = Themify.UpdateQueryString( 'iframe', 'true', link )
		}
		return link;
	},
	getParam: function( name, url ) {
		name = name.replace( /[\[]/, "\\\[" ).replace( /[\]]/, "\\\]" );
		var regexS = "[\\?&]" + name + "=([^&#]*)",
			regex = new RegExp( regexS ),
			results = regex.exec( url );
		return results == null ? "" : results[1];
	},
	getCustomParams: function( url ) {
		var params = url.split( '?' )[1];
		params = params ? '&' + params.replace( /[\\?&]?(v|autoplay)=[^&#]*/g, '' ).replace( /^&/g, '' ) : '';
		
		return '?autoplay=1' + params;
	},
	openSharing: function(self){
		// Load Themify Icons for social sharing icons
		Themify.LoadCss(themify_vars.url + '/themify-icons/themify-icons.css', themify_vars.version);
		var el = self.content[0].getElementsByClassName('tb_social_sharing')[0];
		if ( el !== undefined) {
			el.addEventListener('click',function(e){
				Themify.sharer(e.target.dataset['type'],self.currItem.data.src.replace('?iframe=true',''),self.currItem.data.title)
				return false;
			});
		}
	},
	updateHash: function( action, instance ) {
		if( 'open' === action ) {
			// cache the current location.hash
			if ( ThemifyGallery.origHash === null ) {
				ThemifyGallery.origHash = window.location.hash;
			}
			var hash = instance.currItem.data.title,
			// Escape HTML
                        div = document.createElement( 'div' );
			div.innerHTML = hash;
			hash = div.textContent.trim();
			div = null;
			if ( '' !== hash ) {
				this._updateHash( hash );
			}
		} else {
			// when closing the lightbox, restore the cached hashtag
			var hash = ThemifyGallery.origHash;
			this._updateHash( hash );
			ThemifyGallery.origHash = null;
		}
	},
	/**
	 * Backwards-compatible function to change the hashtag in browser's address bar
	 * Note: this does not trigger 'hashchange' event.
	 */
	_updateHash : function( newhash ) {
            if ( ( '' + newhash ).charAt(0) !== '#' )
            newhash = '#' + newhash;
            history.replaceState( '', '', newhash );
	},
	openAnchor: function(){
		if('' !== window.location.hash){
			var hash =  decodeURI(window.location.hash.substring(1)),
				el = document.querySelector('[alt="'+hash+'"]');
			el = null === el ? document.querySelector('[title="'+hash+'"]') : el;
			if(null !== el){
				el.click();
			}
		}
	}
};

}(jQuery));
