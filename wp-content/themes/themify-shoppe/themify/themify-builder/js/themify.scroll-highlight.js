/**
 * Themify Scroll to element based on its class and highlight it when a menu item is clicked.
 * Copyright (c) Themify
 */
var themifyScrollHighlight = (function($, window, document) {

	'use strict';

	/* Deprecated jQuery plugin. This prevents error. */
	$.fn['themifyScrollHighlight'] = function(options) {};

	var isWorking = false,
		$window = $(window),
		isScrolling = false,
		activeSection = null,
		hederDiff = 0;

	return {

		defaults: {
			speed: parseInt(tbScrollHighlight.speed),
			prefix: '.tb_section-',
			navigation: tbScrollHighlight.navigation,
			context: 'body',
			element: '.module_row',
			scrollRate: 250,
			considerHeader: false,
			fixedHeaderHeight: 0,
			updateHash: true,
			scroll: 'internal' // can be 'external' so no scroll is done here but by the theme. Example: Fullpane.
		},
		scrolling: false,
		cleanupURL: function(url) {
			return url.replace(/#.*$/, '').replace(/\/$/, '');
		},
		requestInterval: function(fn, delay) {
			var start = new Date().getTime();
			window.requestAnimationFrame(function loop() {
				var current = new Date().getTime();
				if (current - start >= delay) {
					fn.call();
					start = current;
				}
				window.requestAnimationFrame(loop);
			});
		},
		cleanHash: function(hash) {
			return decodeURIComponent(hash instanceof $ ? hash.prop('hash') : hash);
		},
		updateOffset: function(topOffset) {
			var customOffset = parseInt( themify_vars.scrollTo );
			if ( customOffset ) {
				// option set in Builder settings page. Ignore fixed header and use provided value instead
				return Math.ceil( topOffset - customOffset );
			} else {
				return Math.ceil( topOffset - this.options.fixedHeaderHeight + hederDiff );
			}
		},
		setHeaderHeight: function() {
			if (Themify.body[0].classList.contains('fixed-header')) {
				var $headerWrap = $('#headerwrap'),
					$fixedheader;
				if ($headerWrap.length !== 0) {
					$fixedheader = $headerWrap.clone();
					$fixedheader.find('*').add($fixedheader).css('cssText', 'transition: all 0s ease 0s !important;');
					$fixedheader.removeClass('fixed-header')
						.css({
							visibility: 'hidden',
							left: '-10000px'
						})
						.appendTo('body');

					this.options.fixedHeaderHeight = $fixedheader.outerHeight(true);

					$fixedheader.addClass('fixed-header');

					// Check if header is transparent
					var bgImage = $fixedheader.css('background-image'),
						bgColor = $fixedheader.css('background-color');

					if (bgColor && bgColor.indexOf('rgba') > -1) {
						bgColor = bgColor.replace(/^.*,(.+)\)/, '$1').trim();
						bgColor = parseFloat(bgColor) === 0 ? 'transparent' : 1;
					}
					if (bgColor === 'transparent' && (!bgImage || bgImage === 'none')) {
						this.options.fixedHeaderHeight = 0;
						hederDiff = 0;
					} else {
						hederDiff = this.options.fixedHeaderHeight - $fixedheader.outerHeight(true);
					}
					$fixedheader.remove();
				}
			}
		},
		highlightLink: function(hash) {
			var self = this;
			this.dehighlightLinks();

			if ('' != hash) {
				var $linkHash = $(this.options.navigation).find('a[href*="' + hash + '"]');

				if ($linkHash.length) {
					$linkHash.each(function() {
						var $link = $(this);

						if (self.cleanHash($link) === hash) {
							$link.parent().addClass('current_page_item');
							/**
							 * Fires event scrollhighlight.themify
							 * Receives anchor with hash
							 */
							Themify.body.triggerHandler('scrollhighlight.themify', [hash]);
							return;
						}
					});
				}
			}
		},
		dehighlightLinks: function() {
			$(this.options.navigation).find('a[href*="#"]').each(function() {
				var p = this.parentNode;
				p.classList.remove('current_page_item');
				p.classList.remove('current-menu-item');
			});
		},
		isInViewport: function($t) {
			if (!($t instanceof $) || !('offset' in $t))
				return false;

			var windowTop = $window.scrollTop() + ( this.options.fixedHeaderHeight - hederDiff ), // include fixed header when calculating if element is visible
				// Divided by X to tell it's visible when the section is half way into viewport
				windowBottom = windowTop + ($window.height() / 4),
				eleTop = this.updateOffset($t.offset().top),
				eleBottom = eleTop + $t.height();

			return (eleTop <= windowBottom) && (eleBottom >= windowTop);
		},
		isHash: function(hash) {
			return hash && '#' !== hash;
		},
		removeHash: function() {
			if (this.isCorrectHash() && this.isHash(window.top.location.hash)) {
				window.top.history.replaceState('', document.title, window.top.location.pathname + window.top.location.search);
				this.dehighlightLinks();
			}
		},
		changeHash: function(href) {
			if (activeSection && ('#' === href || href === this.cleanHash(window.location.hash)))
				return;

			if (this.options.updateHash) {
				window.top.history.replaceState(null, null, href);
			}
			this.highlightLink(href);
			isWorking = false;
		},
		isCorrectHash: function() {
			var hash = location.hash.slice(1);
			// Compatiblity with Ecwid Plugin
			return !!(hash != '' && hash.indexOf('!') === -1);
		},
		linkScroll: function(obj, href) {
			var hash = obj.replace(this.options.prefix, '#'),
				to, el;

			obj = $(obj);

			if (obj.length > 1) {
				obj = obj.filter(':visible').first();
				if (obj.length === 0) {
					obj = obj.first();
					if (obj.length === 0) {
						isWorking = false;
						return;
					}
				}
			}

			// Set offset from top
			el = obj.get(0);


			/**
			 * Fires event scrollhighlightstart.themify before the scroll begins.
			 * Receives anchor with hash.
			 */
			Themify.body.triggerHandler('scrollhighlightstart.themify', [hash]);

			to = $( el ).offset().top;
			to=to<0?$(el.parentNode).offset().top:to;

			this.scrolling = false;
			isScrolling = true;

			if ('internal' === this.options.scroll) {
				var self = this,
					// Complete callback
					completeCallback = function() {
						isWorking = false;
						isScrolling = false;
					};
				if (Themify.body[0].classList.contains('fixed-header') && !Themify.body[0].classList.contains('header-bottom')) {
					to = this.updateOffset(to);
				}
				// Animate scroll
				Themify.scrollTo(to, +this.options.speed, completeCallback);
			} else {
				isWorking = false;
				this.changeHash(href);
			}
		},
		manualScroll: function(elementsToCheck) {

			this.scrolling = false;

			if ($window.scrollTop() < this.options.fixedHeaderHeight) {
				this.removeHash();
			} else {
				for (var i = 0, len = elementsToCheck.length; i < len; ++i) {
					var el = elementsToCheck[i];
					if (el.data('anchor') && this.isInViewport(el)) {
						if(!el.data('hideAnchor')){
							this.changeHash('#' + el.data('anchor'));
						}else{
							this.highlightLink('#' + el.data('anchor'));
						}
						activeSection = el;
						break;
					}
				}
				if (activeSection) {
					if (!this.isInViewport(activeSection)) {
						this.removeHash();
						activeSection = null;
					}
				} else {
					isWorking = false;
				}
			}
		},
		init: function(options) {
			var self = this,
				elementsToCheck = [];
			this.options = $.extend({}, self.defaults, options);

			if (!Themify.is_builder_active) {
				// Build list of elements to check visibility
				$('div[class*=' + self.options.prefix.replace('.', '') + ']:visible,.module[id]:visible').not(self.options.exclude).each(function() {
					elementsToCheck.push($(this));
				});

				if (!elementsToCheck.length)
					return;

			}
			self.setHeaderHeight();
			// Smooth Scroll and Link Highlight
			var startX,
				startY;

			function getCoord(e, c) {
				return /touch/.test(e.type) ? (e.originalEvent || e).changedTouches[0]['page' + c] : e['page' + c];
			}
			$(this.options.context).on('touchstart.themifyScroll', 'a[href*="#"], area[href*="#"]', function(e) {
				e.stopPropagation();
				startX = getCoord(e, 'X');
				startY = getCoord(e, 'Y');
			}).on('click.themifyScroll touchend.themifyScroll', 'a[href*="#"], area[href*="#"]', function(e) {
				/* on touch devices ensure visitor means to "tap" the link rather than sliding over it */
				if ( /touch/.test( e.type ) ) {
					if (!(Math.abs(getCoord(e, 'X') - startX) < 20 && Math.abs(getCoord(e, 'Y') - startY) < 20)) {
						return;
					}
				}
				/* ensure the link points to the current page */
				if ( ! ( this.host === window.location.host && this.pathname === window.location.pathname ) ) {
					return;
				}
				if (isWorking === false && !this.classList.contains('ab-item')) {
					// Build class to scroll to
					var href = self.cleanHash($(this));
					if (href !== '#' && '' !== href) {
						href = href.indexOf('/') != -1 ? href.substring(0, href.indexOf('/')) : href;
						var classToScroll = href.replace(/#/, self.options.prefix),
							$el = $(classToScroll);
						if($el.length<1 && this.closest('.tab-nav')==null){
							$el = $(href+'.module,.module-tab '+href).first();
							classToScroll = href;
						}
						// If the section exists in this page
						if ($el.length > 0) {
							e.preventDefault();
							e.stopPropagation();
							isWorking = true;
							self.linkScroll(classToScroll, href);
							if (Themify.is_builder_active) {
								activeSection = true;
								self.changeHash(href);
							}
						}
					}
				}
			});

			if (!Themify.is_builder_active) {

				// Setup scroll event
				$window.on('scroll', function() {
					self.scrolling = true;
				});

				this.requestInterval(function() {
					if ( !isScrolling && self.scrolling ) {
						self.manualScroll(elementsToCheck);
					}
				}, self.options.scrollRate);

				// Initial section visibility check and link highlight
				$window.on('load hashchange', function(e) {
					if (isWorking === false) {
						isWorking = true;
						if ( self.isHash(window.location.hash) ) {
							// If there's a hash, scroll to it
							var hash = self.cleanHash(window.location.hash),
								found = false,
								current_url = self.cleanupURL(window.location.href),
								$linkHash = $(self.options.context).find('a[href="' + hash + '"], a[href="' + current_url + hash + '"], a[href="' + current_url + '/' + hash + '"]');

							if ($linkHash.length) {
								$linkHash.each(function() {
									var $link = $(this);
									if (self.cleanHash($link) === hash) {
										found = true;
										setTimeout(function() {
											isWorking = false;
											$link.trigger('click.themifyScroll');
										}, 600);
										return;
									}
								});
								if (found === false) {
									isWorking = false;
								}
							} else {
								// Build class to scroll to
								var classToScroll = hash.replace(/#/, self.options.prefix);
								// If the section exists in this page
								if (-1 === classToScroll.search('/') && $(classToScroll).length) {
									setTimeout(function() {
										self.linkScroll(classToScroll, hash);
									}, 600);
								} else {
									isWorking = false;
								}
							}
						} else {
							isWorking = false;
							self.manualScroll(elementsToCheck);
						}
					}
				});
			}

			// after window.load wait an arbitrary amount of time, waiting
			// for other stuff to load and redo the header height calculation
			$( window ).load( function() {
				setTimeout( function() {
					self.setHeaderHeight();
				}, 10000 );
			} );
		}
	};
})(jQuery, window, document);
