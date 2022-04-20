/* Themify Theme Scripts */
(function ($) {
    'use strict';
    var FixedHeader = {
        headerHeight: 0,
        hasHeaderSlider: false,
        headerSlider: false,
        $headerWrap: $('#headerwrap'),
        $pageWrap: $('#pagewrap'),
        init: function () {

            if (Themify.body.hasClass('revealing-header')) {
                this.headerRevealing();
            }
            if (Themify.body.hasClass('fixed-header')) {
                this.headerHeight = this.$headerWrap.outerHeight(true);
                this.activate();
                $(window).on('scroll touchstart.touchScroll touchmove.touchScroll', this.activate);
            }
            $(window).on('tfsmartresize', function () {
                FixedHeader.$pageWrap.css('paddingTop', Math.floor(FixedHeader.$headerWrap.outerHeight(true)));
            });
            if ($('#gallery-controller').length > 0) {
                this.hasHeaderSlider = true;
            }

            // Sticky header logo customizer
            if (themifyScript.sticky_header) {
                var img = '<img id="sticky_header_logo" src="' + themifyScript.sticky_header.src + '"';
                if (themifyScript.sticky_header.imgwidth) {
                    img += ' width="' + themifyScript.sticky_header.imgwidth + '"';
                }
                if (themifyScript.sticky_header.imgheight) {
                    img += ' height="' + themifyScript.sticky_header.imgheight + '"';
                }
                img += '/>';
                $('#site-logo a').prepend(img);
            }
        },
        activate: function () {
            if ($(window).scrollTop() >= FixedHeader.headerHeight) {
                if (!FixedHeader.$headerWrap.hasClass('fixed-header')) {
                    FixedHeader.scrollEnabled();
                }
            } else if (FixedHeader.$headerWrap.hasClass('fixed-header')) {
                FixedHeader.scrollDisabled();
            }
        },
        headerRevealing:function(){
            var direction = 'down',
                previousY = 0,
                _this = this,
                onScroll = function(){
                    if(previousY === window.scrollY){
                        return;
                    }
                    direction = previousY < window.scrollY?'down':'up';
                    previousY = window.scrollY;
                    if('up' === direction || 0 === previousY){
                        if(_this.$headerWrap.hasClass('hidden')){
                            _this.$headerWrap.css('top','').removeClass('hidden');
                        }
                    }else if(0 < previousY && ! _this.$headerWrap.hasClass('hidden')){
                        _this.$headerWrap.css('top',- _this.$headerWrap.outerHeight()).addClass('hidden');
                    }
                };
            $(window).on('scroll touchstart.touchScroll touchmove.touchScroll',onScroll);
            onScroll();
        },
        scrollDisabled: function () {
            var _this = FixedHeader;
            _this.$headerWrap.removeClass('fixed-header');
            $('#header').removeClass('header-on-scroll');
            Themify.body.removeClass('fixed-header-on');
            /**
             * force redraw the header
             * required in order to calculate header height properly after removing fixed-header classes
             */
            _this.$headerWrap.hide();
            _this.$headerWrap[0].offsetHeight;
            _this.$headerWrap.show();

            _this.headerHeight = _this.$headerWrap.outerHeight(true);
            _this.$pageWrap.css('paddingTop', Math.floor(_this.headerHeight));
        },
        scrollEnabled: function () {
            FixedHeader.$headerWrap.addClass('fixed-header');
            $('#header').addClass('header-on-scroll');
            Themify.body.addClass('fixed-header-on');
        }
    };

    // Infinite Scroll ///////////////////////////////
    function doInfinite($container, selector, wpf) {
        Themify.infinity($container, {
            append: selector, // selector for all items you'll retrieve
            scrollToNewOnLoad: themifyScript.scrollToNewOnLoad,
            scrollThreshold: 'auto' !== themifyScript.autoInfinite ? false : ((Themify.w < 680 && $('#sidebar').length > 0) ? ($('#sidebar').height() + $('#footerwrap').height()) : $('#footerwrap').height()),
            button: $('#load-more a')[0],
            history: wpf || !themifyScript.infiniteURL ? false : 'replace'
        });
    }
   $(function() {

        var $body = Themify.body;
        FixedHeader.init();
        if (document.querySelector('.has-mega-sub-menu') !== null) {
            Themify.LoadAsync(themifyScript.theme_url + '/themify/megamenu/js/themify.mega-menu.js', null,
                    null,
                    null,
                    function () {
                        return ('undefined' !== typeof $.fn.ThemifyMegaMenu);
                    });
        }
        /////////////////////////////////////////////
        // Entry Filter Layout
        /////////////////////////////////////////////
        Themify.isoTop('.masonry.loops-wrapper', {itemSelector: '.loops-wrapper > .post,.loops-wrapper > .product'});



        ///////////////////////////////////////////
        // Initialize infinite scroll
        ///////////////////////////////////////////


        if (document.querySelector('.wpf_form') !== null) {
            var infinityContainer = $('.infinite.loops-wrapper.products')[0];
            doInfinite(infinityContainer, '.infinite.loops-wrapper .product', true);
            /**
			 * compatibility with two plugins:
			 *   Themify Product Filter (https://themify.me/themify-product-filter)
			 *   YITH WooCommerce Ajax Product Filter (https://yithemes.com/themes/plugins/yith-woocommerce-ajax-product-filter/)
			 */
			$(document).on('wpf_ajax_success yith-wcan-ajax-filtered', function () {
                var el = $('.infinite.loops-wrapper.products').first();
				if ( ! el.length ) {
					return;
				}
                if (window['Isotope'] !== undefined) {
                    var instance = window['Isotope'].data(infinityContainer);
                    if (instance) {
                        delete instance['options'][instance['options']['layoutMode']];
                        if (el[0].classList.contains('masonry-done')) {
                            el[0].classList.remove('masonry-done');
                            window['Isotope'].data(el[0]).destroy();
                        }
                        Themify.isoTop(el[0], instance['options']);

                    }
                }
                Themify.media(el[0].querySelectorAll('.wp-audio-shortcode, .wp-video-shortcode'));
                if (el[0].classList.contains('auto_tiles')) {
                    Themify.autoTiles(el[0]);
                }
                el.triggerHandler('infiniteloaded.themify', [el]);
                Themify.body.triggerHandler('infiniteloaded.themify', [el, el]);
                Themify.InitGallery(el);
                Themify.body.triggerHandler('builder_load_module_partial', [el]);

            });
        }
        else {
            doInfinite($('.infinite.loops-wrapper'), '.infinite.loops-wrapper .post, .infinite.loops-wrapper .product');
        }

        var InitBuilderModuleInit = function (el) {
            var ShopdockPlusIconMove = function (el) {
                // Shopdock Plugin Plus Icon
                if ($('#addon-shopdock').length > 0) {
                    var plusicon = $('.overlay.loops-wrapper, .polaroid.loops-wrapper, .auto_tiles.loops-wrapper', el);
                    if (plusicon.length > 0) {
                        $('li.product').each(function () {
                            $(this).append($(this).find('.add_to_cart_button'));
                        });
                    }
                }
            };
            ShopdockPlusIconMove(el);
        };
        if (Themify.is_builder_active) {
            if (Themify.is_builder_loaded) {
                InitBuilderModuleInit();
            }
            else {
                window.top.jQuery('body').one('themify_builder_ready', function () {
                    InitBuilderModuleInit();
                });
            }
        }
        else {
            InitBuilderModuleInit();
        }


        /////////////////////////////////////////////
        // Search Form							
        /////////////////////////////////////////////
        var $search = $('#search-lightbox-wrap');
        if ($search.length > 0) {
            var cache = [],
                    xhr,
                    $input = $search.find('#searchform input'),
                    $result_wrapper = $search.find('.search-results-wrap');
            $('.search-button, #close-search-box').on('click', function (e) {
                e.preventDefault();
                if ($input.val().length) {
                    $search.addClass('search-active');
                } else {
                    $search.removeClass('search-active')
                }
                if ($(this).hasClass('search-button')) {
                    $search.fadeIn(function () {
                        $input.focus();
                        $body.css('overflow-y', 'hidden');
                    });
                    $body.addClass('searchform-slidedown');
                }
                else {
                    if (xhr) {
                        xhr.abort();
                    }
                    $search.fadeOut();
                    $body.css('overflow-y', 'visible').removeClass('searchform-slidedown');
                }
            });

            $result_wrapper.on('click', '.search-option-tab a', function (e) {
                e.preventDefault();
                var $href = $(this).attr('href').replace('#', '');
                if ($href === 'all') {
                    $href = 'item';
                }
                else {
                    $result_wrapper.find('.result-item').stop().fadeOut();
                }
                if ($('#result-link-' + $href).length > 0) {
                    $('.view-all-button').hide();
                    $('#result-link-' + $href).show();
                }
                $result_wrapper.find('.result-' + $href).stop().fadeIn();
                $(this).closest('li').addClass('active').siblings('li').removeClass('active');
            });

            $input.prop('autocomplete', 'off').on('keyup', function (e) {
                if ($input.val().length > 0) {
                    $search.addClass('search-active');
                } else {
                    $search.removeClass('search-active');
                }
                function set_active_tab(index) {
                    if (index < 0) {
                        index = 0;
                    }
                    $result_wrapper.find('.search-option-tab li').eq(index).children('a').click();
                    $result_wrapper.show();
                }
                if ((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 65 && e.keyCode <= 90) || e.keyCode === 8) {
                    var $v = $.trim($(this).val());
                    if ($v) {
                        if (cache[$v]) {
                            var $tab = $result_wrapper.find('.search-option-tab li.active').index();
                            $result_wrapper.hide().html(cache[$v]);
                            set_active_tab($tab);
                            return;
                        }
                        setTimeout(function () {
                            $v = $.trim($input.val());
                            if (xhr) {
                                xhr.abort();
                            }
                            if (!$v) {
                                $result_wrapper.html('');
                                return;
                            }

                            xhr = $.ajax({
                                url: themifyScript.ajax_url,
                                type: 'POST',
                                data: {'action': 'themify_search_autocomplete', 'term': $v},
                                beforeSend: function () {
                                    $search.addClass('themify-loading');
                                    $result_wrapper.html('<span class="themify_spinner"></span>');
                                },
                                complete: function () {
                                    $search.removeClass('themify-loading');
                                },
                                success: function (resp) {
                                    if (!$v) {
                                        $result_wrapper.html('');
                                    }
                                    else if (resp) {
                                        var $tab = $result_wrapper.find('.search-option-tab li.active').index();
                                        $result_wrapper.hide().html(resp);
                                        set_active_tab($tab);
                                        $result_wrapper.find('.search-option-tab li.active')
                                        cache[$v] = resp;
                                    }
                                }
                            });
                        }, 100);
                    }
                    else {
                        $result_wrapper.html('');
                    }
                }
            });
        }
        /////////////////////////////////////////////
        // Scroll to top 							
        /////////////////////////////////////////////
        $('.back-top a').on('click', function (e) {
            Themify.scrollTo();
            e.preventDefault();
        });

        var $back_top = $('.back-top');
        if ($back_top.length > 0) {
            if ($back_top.hasClass('back-top-float')) {
                $(window).on('scroll touchstart.touchScroll touchmove.touchScroll', function () {
                    if (window.scrollY < 10) {
                        $back_top.addClass('back-top-hide');
                    } else {
                        $back_top.removeClass('back-top-hide');
                    }
                });

            }
        }

        function toggleMobileSidebar() {
            var item = $('.toggle-sticky-sidebar'),
                    sidebar = $("#sidebar");
            item.on('click', function () {
                if (item.hasClass('open-toggle-sticky-sidebar')) {
                    item.removeClass('open-toggle-sticky-sidebar').addClass('close-toggle-sticky-sidebar');
                    sidebar.addClass('open-mobile-sticky-sidebar');
                } else {
                    item.removeClass('close-toggle-sticky-sidebar').addClass('open-toggle-sticky-sidebar');
                    sidebar.removeClass('open-mobile-sticky-sidebar');
                }
            });
        }
        toggleMobileSidebar();

        /////////////////////////////////////////////
        // Toggle main nav on mobile 							
        /////////////////////////////////////////////

        // Set Slide Menu /////////////////////////
        $('#menu-icon').themifySideMenu({
            close: '#menu-icon-close',
            side: ($body.hasClass('header-minbar-left') || $body.hasClass('header-left-pane') || $body.hasClass('header-slide-left')) ? 'left' : 'right'
        });
        // Set Dropdown Arrow /////////////////////////
        Themify.LoadAsync(themify_vars.url + '/js/themify.dropdown.js', function () {
            $('#main-nav').themifyDropdown();
        },
        null,
        null,
        function () {
            return ('undefined' !== typeof $.fn.themifyDropdown);
        });
        
        if (!Themify.isTouch) {
            var $niceScrollTarget = $('.top-icon-wrap #cart-list'),
                    $niceScrollMenu = $body.is('.header-minbar-left,.header-minbar-right,.header-overlay,.header-slide-right,.header-slide-left') ?
                    $('#mobile-menu') : ($body.is('.header-left-pane,.header-right-pane') ? $('#headerwrap') : false);
            if (($niceScrollMenu && $niceScrollMenu.length > 0) || $niceScrollTarget.length > 0) {
                Themify.LoadAsync(themifyScript.theme_url + '/js/jquery.nicescroll.min.js', function () {
                    if ($niceScrollTarget.length > 0) {
                        $niceScrollTarget.niceScroll();
                        setTimeout(function () {
                            $niceScrollTarget.getNiceScroll().resize();
                        }, 200);
                    }

                    if ($niceScrollMenu) {
                        $niceScrollMenu.niceScroll();
                        $body.on('sidemenushow.themify', function () {
                            setTimeout(function () {
                                $niceScrollMenu.getNiceScroll().resize();
                            }, 200);
                        });
                    }
                },
                        null,
                        null,
                        function () {
                            return ('undefined' !== typeof $.fn.niceScroll);
                        });
            }
        }

       var expand=undefined!=themifyScript.m_m_expand?' toggle-on':'';
        $("#main-nav > li.menu-item-has-children > a, #main-nav > li.page_item_has_children > a").after("<span class='child-arrow"+ expand +"'></span>");
        $("#main-nav ul li.menu-item-has-children > a, #main-nav ul li.page_item_has_children > a").after("<span class='child-arrow'></span>");
        $('#main-nav .child-arrow,#main-nav a').on('click', function (e) {
            var toggle = true,
                    item = $(this);
            if (this.tagName === 'A') {
                if ((item.attr('href') === '#' || item.parent('.themify_toggle_dropdown').length>0)  && item.next('.child-arrow').length > 0) {
                    item = item.next('.child-arrow');
                }
                else {
                    toggle = false;
                }
            }
            if (toggle) {
                e.preventDefault();
                if(undefined!=themifyScript.m_m_toggle){
                    item.closest('li').siblings().find('.toggle-on').toggleClass('toggle-on');
                }
                item.toggleClass('toggle-on');
            }
        });
        if ($body.hasClass('header-left-pane') || $body.hasClass('header-right-pane')) {
            var $HLicons = $('.top-icon-wrap, .search-button'),
                    $HLiconswrapper = $('#mobile-menu');
            $($HLiconswrapper).prepend($('<div class="header-icons"></div>'));
            $('.header-icons').append($HLicons);
        }
        if ($body.hasClass('header-overlay')) {
            $('.search-button').appendTo('.top-icon-wrap');
            $('#mobile-menu').wrapInner('<div class="overlay-menu-sticky"><div class="overlay-menu-sticky-inner"></div></div>');
        }

        // Mobile cart
        (function ($cart, $mobMenu) {
            if ($cart.length && $mobMenu.length && !$body.is('.header-left-pane, .header-right-pane, .header-minbar-left, .header-minbar-right')) {
                var $cartIcon = $cart.clone(),
                        $cartMenu = $('#shopdock'),
                        $cartMenuClone = $cartMenu.clone(),
                        isSlideCart = $('#slide-cart').length,
                        id = $cartMenu.attr('id'),
                        fakeId = id + '_',
                        toggleId = function () {
                            if ($cartIcon.is(':visible')) {
                                $cartMenu.attr('id', fakeId).hide();
                                $cartMenuClone.attr('id', id).show();
                            } else {
                                $cartMenu.attr('id', id).show();
                                $cartMenuClone.attr('id', fakeId).hide();
                            }
                        };

                $cartIcon
                        .addClass('icon-menu')
                        .find('.tooltip')
                        .remove()
                        .end()
                        .insertBefore($mobMenu)
                        .wrap('<div id="cart-link-mobile" />');

                !isSlideCart && $('#cart-link-mobile').append($cartMenuClone);

                $cartIcon.on('click', function (e) {

                    if ($body.hasClass('cart-style-link_to_cart'))
                        return;
                    e.preventDefault();
                    $cart.is('#cart-link') && $cart.trigger('click');
                });

                if (!isSlideCart) {
                    toggleId();
                    $(window).on('tfsmartresize', toggleId);
                }
            }

        })($('#cart-icon-count > a'), $('#menu-icon'));

        if ($body.hasClass('header-bottom')) {
            $("#footer").after("<a class='footer-tab' href='#'></a>");
            $(".footer-tab").click(function (e) {
                e.preventDefault();
                $('#footerwrap').toggleClass('expanded');
            });
            $("#footer .back-top").detach().appendTo('#pagewrap');
            $('.back-top').addClass('back-top-float back-top-hide');

            var $back_top = $('.back-top');
            if ($back_top.length > 0) {
                if ($back_top.hasClass('back-top-float')) {
                    $(window).on('scroll touchstart.touchScroll touchmove.touchScroll', function () {
                        if (window.scrollY < 10) {
                            $back_top.addClass('back-top-hide');
                        } else {
                            $back_top.removeClass('back-top-hide');
                        }
                    });

                }
            }

        }

        /* COMMENT FORM ANIMATION */
        $('input, textarea').on('focus', function () {
            $(this).parents('#commentform p').addClass('focused');
        }).on('blur', function () {
            var inputValue = $(this).val();
            if (inputValue == "") {
                $(this).removeClass('filled').parents('#commentform p').removeClass('focused');
            } else {
                $(this).addClass('filled');
            }
        });

        
        // Set Body Overlay Show/Hide /////////////////////////
        var $overlay = $('<div class="body-overlay">');
        $body.append($overlay).on('sidemenushow.themify', function () {
            $overlay.addClass('body-overlay-on');
        }).on('sidemenuhide.themify', function () {
            $overlay.removeClass('body-overlay-on');
        }).on('click.themify touchend.themify', '.body-overlay', function () {
            $('#menu-icon').themifySideMenu('hide');
            $('.top-icon-wrap #cart-link').themifySideMenu('hide');
        });
        
        // Mega menu width
        var MegaMenuWidth = function () {
            var items = $('#main-nav li.has-mega-column > ul, #main-nav li.has-mega-sub-menu > .mega-sub-menu');
            if ($(window).width() > tf_mobile_menu_trigger_point) {
                items.css('width', $('#header').width());
            } else {
                items.removeAttr("style");
            }
        };
        MegaMenuWidth();
        // Top Bar Widget
        $('.top-bar-widgets').clone().insertBefore('#menu-icon-close');
        $(window).on('tfsmartresize', function () {
            // Set Body Overlay Resize /////////////////////////
            if ($('#mobile-menu').hasClass('sidemenu-on') && $('#menu-icon').is(':visible')) {
                $overlay.addClass('body-overlay-on');
            }
            else {
                $overlay.removeClass('body-overlay-on');
            }
            
            if ($('.top-bar-widgets').height() > 0) {
                if ($body.hasClass('header-minbar-left')) {
                    $('#headerwrap, .search-button, .top-icon-wrap, .logo-wrap').css('left', $('.top-bar-widgets').height());
                    $('#menu-icon').css('left', $('.top-bar-widgets').height() + (($('.logo-wrap').height() - $('#menu-icon').width()) / 2));
                    $body.css('marginLeft', $('.top-bar-widgets').height());
                }
                if ($body.hasClass('header-minbar-right')) {
                    $('#headerwrap, .search-button, .top-icon-wrap').css('right', $('.top-bar-widgets').height());
                    $('#menu-icon').css('right', $('.top-bar-widgets').height() + (($('.logo-wrap').height() - $('#menu-icon').width()) / 2));
                    $body.css('marginRight', $('.top-bar-widgets').height());
                }
            }
            MegaMenuWidth();
        });
        if ($body.hasClass('revealing-footer')) {
            // Revealing footer
            var currentColor, contentParents, isSticky,
                    $footer = $('#footerwrap'),
                    $footerInner = $footer.find('#footer'),
                    footerHeight = $footer.innerHeight(),
                    $content = $('#body'),
                    resizeCallback = function () {
                        footerHeight = $footer.innerHeight();
                        !isSticky && $footer.parent().css('padding-bottom', footerHeight);
                    },
                    scrollCallback = function () {
                        var contentPosition = $content.get(0).getBoundingClientRect(),
                                footerVisibility = window.innerHeight - contentPosition.bottom;

                        $footer.toggleClass('active-revealing', contentPosition.top < 0);

                        if (footerVisibility >= 0 && footerVisibility <= footerHeight) {
                            $footerInner.css('opacity', footerVisibility / footerHeight + 0.2);
                        } else if (footerVisibility > footerHeight) {
                            $footerInner.css('opacity', 1);
                        }
                    };

            if (!$footer.length && !$content.length)
                return;

            // Check for content background
            contentParents = $content.parents();

            if (contentParents.length) {
                $content.add(contentParents).each(function () {
                    if (!currentColor) {
                        var elColor = $(this).css('background-color');
                        if (elColor && elColor !== 'transparent' && elColor !== 'rgba(0, 0, 0, 0)') {
                            currentColor = elColor;
                        }
                    }
                });
            }

            $content.css('background-color', currentColor || '#ffffff');

            // Sticky Check
            isSticky = $footer.css('position') === 'sticky';
            $body.toggleClass('no-css-sticky', !isSticky);

            resizeCallback();
            scrollCallback();
            $(window).on('tfsmartresize', resizeCallback).on('scroll', scrollCallback);
        }
    });

// WINDOW LOAD /////////////////////////
    $(window).one('load', function () {
        // Lightbox / Fullscreen initialization ///////////
        if (typeof ThemifyGallery !== 'undefined') {
            ThemifyGallery.init({'context': $(themifyScript.lightboxContext)});
        }
        ///////////////////////////////////////////
        // Header Video
        ///////////////////////////////////////////
        var $header = $('#headerwrap'),
                $videos = $header.find('[data-fullwidthvideo]');

        if ($header.data('fullwidthvideo')) {
            $videos = $videos.add($header);
        }
        function ThemifyBideo() {

            var init = true,
                    $fixed = $header.hasClass('fixed-header');

            if ($fixed) {
                $header.removeClass('fixed-header');
            }

            $videos.each(function (i) {
                var url = $(this).data('fullwidthvideo');
                if (url) {
                    var options = {
                        url: url,
                        doLoop: true,
                        ambient: true,
                        id: i
                    };
                    if (init && $fixed) {
                        init = false;
                        options['onload'] = function () {
                            $header.addClass('fixed-header');
                        };
                    }
                    $(this).ThemifyBgVideo(options);
                }
            });
        }
        if ($videos.length > 0) {
            if (!Themify.isTouch) {
                if (typeof $.fn.ThemifyBgVideo === 'undefined') {
                    Themify.LoadAsync(
                            themify_vars.url + '/js/bigvideo.js',
                            ThemifyBideo,
                            null,
                            null,
                            function () {
                                return ('undefined' !== typeof $.fn.ThemifyBgVideo);
                            }
                    );
                }
                else {
                    ThemifyBideo();
                }
            }
            else {
                $videos.each(function (key) {
                    var videoSrc = $(this).data('fullwidthvideo'),
                            videoEl;

                    if (videoSrc) {

                        if (videoSrc.indexOf('.mp4') >= 0 && videoSrc.indexOf(window.location.hostname) >= 0) {

                            $(this).addClass('themify-responsive-video-background');
                            videoEl = $('<div class="header-video-wrap">'
                                    + '<video class="responsive-video header-video video-' + key + '" muted="true" autoplay="true" loop="true" playsinline="true" >' +
                                    '<source src="' + videoSrc + '" type="video/mp4">' +
                                    '</video></div>')
                            videoEl.prependTo($(this));
                        }
                    }
                });
            }
        }

        // EDGE MENU /////////////////////////
        $("#main-nav li").on('mouseenter mouseleave', function (e) {
            if ($('ul', this).length) {
                var elm = $('ul:first', this);
                if (!(elm.offset().left + elm.width() <= $(window).width())) {
                    $(this).addClass('edge');
                } else {
                    $(this).removeClass('edge');
                }

            }
        });

    }).on('load tfsmartresize', function (e) {
        var viewport = $(window).width(),
                $body = Themify.body;
        if ($body.hasClass('header-logo-center')) {
            if (viewport > 1183) {
                var $HalfWidth = $(window).width() / 2 - $('#site-logo').width() / 2;
                $('#main-nav').css('max-width', $HalfWidth);
            }
            else {
                $('#main-nav').removeAttr('style');
            }
        }
        else if ($body.hasClass('header-slide-right') || $body.hasClass('header-slide-left')) {

            var $swapWrap = $('.top-icon-wrap, .search-button'),
                    $sidePanel = $('#mobile-menu'),
                    $insertWrapper = $('#main-nav-wrap');

            // Move menu into side panel on small screens /////////////////////////
            if (viewport > tf_mobile_menu_trigger_point) {
                $sidePanel.before($swapWrap);
            } else {
                $insertWrapper.before($swapWrap);
            }
        }
    });

})(jQuery);
