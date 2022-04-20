var ThemifyBuilderModuleJs;
(function ($) {
    'use strict';

	/* polyfill for obj.forEach, required in IE11 */
	if ( typeof NodeList.prototype.forEach !== 'function' )  {
		NodeList.prototype.forEach = Array.prototype.forEach;
	}

    var slice = Array.prototype.slice, // save ref to original slice()
            splice = Array.prototype.splice, // save ref to original slice()
            defaults = {
                topSpacing: 0,
                bottomSpacing: 0,
                className: 'is-sticky',
                wrapperClassName: 'sticky-wrapper',
                center: false,
                getWidthFrom: '',
                widthFromWrapper: true, // works only when .getWidthFrom is empty
                responsiveWidth: false
            },
    $window = $(window),
            $document = $(document),
            sticked = [],
            windowHeight = $window.height(),
            mutationObserver,
            scroller = function () {
                var l=sticked.length;
                if(Themify.is_builder_active || l===0){
                   window.addEventListener('scroll',scroller,{passive:true,capture: true});
                   $window.on('tfsmartresize.sticky', resizer);
                   if (window.MutationObserver && mutationObserver) {
                        mutationObserver.disconnect();
                   }
                   sticked=[];
                   return;
                }
                var scrollTop = $window.scrollTop(),
                    documentHeight = $document.height(),
                    dwh = documentHeight - windowHeight,
                    extra = (scrollTop > dwh) ? dwh - scrollTop : 0;
               
                    for (var i = 0;i < l; ++i) {
                        var s = sticked[i],
                                elementTop = s.stickyWrapper.offset().top,
                                height = s.stickyElement.outerHeight(),
                                etse = elementTop - s.topSpacing - extra;

                        //update height in case of dynamic content
                        s.stickyWrapper.css('height', height);

                        if (scrollTop <= etse) {
                            if (s.currentTop !== null) {
                                s.stickyElement
                                        .css({
                                            'width': '',
                                            'position': '',
                                            'top': ''
                                        }).parent().removeClass(s.className);
                                s.stickyElement.trigger('sticky-end', [s]);
                                s.currentTop = null;
                            }
                        }
                        else {
                            var newTop = documentHeight - height - s.topSpacing - s.bottomSpacing - scrollTop - extra;
                            if (newTop < 0) {
                                newTop = newTop + s.topSpacing;
                            } else {
                                newTop = s.topSpacing;
                            }
                            if (s.currentTop !== newTop) {
                                var newWidth=s.stickyElement.width();
                                if (s.getWidthFrom) {
                                    var padding = s.stickyElement.innerWidth() - newWidth;
                                    newWidth = ($(s.getWidthFrom).width() - padding) || newWidth;
                                } else if (s.widthFromWrapper) {
                                    newWidth = s.stickyWrapper.width();
                                }
                                s.stickyElement.css({'width':newWidth,'position':'fixed','top':newTop}).parent().addClass(s.className);

                                if (s.currentTop === null) {
                                    s.stickyElement.trigger('sticky-start', [s]);
                                } else {
                                    // sticky is started but it have to be repositioned
                                    s.stickyElement.trigger('sticky-update', [s]);
                                }

                                if (s.currentTop === s.topSpacing && s.currentTop > newTop || s.currentTop === null && newTop < s.topSpacing) {
                                    // just reached bottom || just started to stick but bottom is already reached
                                    s.stickyElement.trigger('sticky-bottom-reached', [s]);
                                } else if (s.currentTop !== null && newTop === s.topSpacing && s.currentTop < newTop) {
                                    // sticky is started && sticked at topSpacing && overflowing from top just finished
                                    s.stickyElement.trigger('sticky-bottom-unreached', [s]);
                                }

                                s.currentTop = newTop;
                            }

                            // Check if sticky has reached end of container and stop sticking
                            var stickyWrapperContainer = s.stickyWrapper.parent(),
                                    top = s.stickyElement.offset().top,
                                    unstick = (top + height >= stickyWrapperContainer.offset().top + stickyWrapperContainer.outerHeight()) && (top <= s.topSpacing);

                            if (unstick) {
                                s.stickyElement.css({'position': 'absolute', top: '', bottom: 0});
                            } else {
                                s.stickyElement.css({'position': 'fixed', top: newTop, bottom: ''});
                            }
                        }
                    }
            },
            resizer = function (e) {
                var l = sticked.length;
                if(l>0){
                    windowHeight = e.h;
                    for (var i = 0; i < l; i++) {
                        var s = sticked[i],
                            newWidth = null;
                        if (s.getWidthFrom && s.responsiveWidth) {
                            newWidth = $(s.getWidthFrom).width();
                        } else if (s.widthFromWrapper) {
                            newWidth = s.stickyWrapper.width();
                        }
                        if (newWidth !== null) {
                            s.stickyElement.css('width', newWidth);
                        }
                    }
                }
            },
            methods = {
                init: function (options) {
                    return this.each(function () {
                        var o = $.extend({}, defaults, options),
                                stickyElement = $(this),
                                stickyId = stickyElement.prop('id'),
                                wrapperId = stickyId ? stickyId + '-' + defaults.wrapperClassName : defaults.wrapperClassName,
                                wrapper = $('<div></div>')
                                .prop('id', wrapperId)
                                .addClass(o.wrapperClassName);

                        stickyElement.wrapAll(function () {
                            if ($(this).parent("#" + wrapperId).length === 0) {
                                return wrapper;
                            }
                        });

                        var stickyWrapper = stickyElement.parent();

                        if (o.center) {
                            stickyWrapper.css({width: stickyElement.outerWidth(), marginLeft: "auto", marginRight: "auto"});
                        }

                        if (stickyElement.css("float") === "right") {
                            stickyElement.css({"float": "none"}).parent().css({"float": "right"});
                        }

                        o.stickyElement = stickyElement;
                        o.stickyWrapper = stickyWrapper;
                        o.currentTop = null;

                        sticked.push(o);

                        methods.setWrapperHeight(this);
                        methods.setupChangeListeners(this);
                    });
                },
                setWrapperHeight: function (stickyElement) {
                    var element = $(stickyElement),
                            stickyWrapper = element.parent();
                    if (stickyWrapper) {
                        stickyWrapper.css('height', element.outerHeight());
                    }
                },
                setupChangeListeners: function (stickyElement) {
                    if (window.MutationObserver) {
                        mutationObserver = new window.MutationObserver(function (mutations) {
                            if (mutations[0].addedNodes.length || mutations[0].removedNodes.length) {
                                methods.setWrapperHeight(stickyElement);
                            }
                        });
                        mutationObserver.observe(stickyElement, {subtree: true, childList: true});
                    } else {
                        stickyElement.addEventListener('DOMNodeInserted', function () {
                            methods.setWrapperHeight(stickyElement);
                        }, {passive:true});
                        stickyElement.addEventListener('DOMNodeRemoved', function () {
                            methods.setWrapperHeight(stickyElement);
                        }, {passive:true});
                    }
                },
                update: scroller,
                unstick: function (options) {
                    return this.each(function () {
                        var that = this,
                                unstickyElement = $(that),
                                removeIdx = -1,
                                i = sticked.length;
                        while (i-- > 0) {
                            if (sticked[i].stickyElement.get(0) === that) {
                                splice.call(sticked, i, 1);
                                removeIdx = i;
                            }
                        }
                        if (removeIdx !== -1) {
                            unstickyElement.unwrap()
                                    .css({
                                        'width': '',
                                        'position': '',
                                        'top': '',
                                        'float': ''
                                    });
                        }
                    });
                }
            };


    $.fn.sticky = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.sticky');
        }
    };

    $.fn.unstick = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.unstick.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.sticky');
        }
    };
    $(function () {
        if (!Themify.is_builder_active) {
            setTimeout(scroller, 0);
            window.addEventListener('scroll',scroller,{passive:true,capture: true});
            $window.on('tfsmartresize.sticky', resizer);
        }
    });
})(jQuery);

/**
 * Tabify
 */
;
(function ($) {

    'use strict';

    $.fn.tabify = function () {
        return this.each(function () {
            
            if (!this.getAttribute('data-tabify')) {
                this.setAttribute('data-tabify', true);
                var tabs = this.getElementsByClassName('tab-nav');
                    if(tabs[0]!==undefined){
                    var items = tabs[0].getElementsByTagName('li'),
                    tab =this;
                    items[0].classList.add('current');
                    for(var i=items.length-1;i>-1;--i){
                        $(items[i]).on('click',function () {
                            $(this).addClass('current').attr('aria-expanded', 'true').siblings().removeClass('current').attr('aria-expanded', 'false');
                            $(this).closest('.module-tab').find('.tab-nav-current-active').text($(this).text());
                            var activeTab = $(this).find('a').attr('href');
                            $(activeTab).attr('aria-hidden', 'false').trigger('resize').siblings('.tab-content').attr('aria-hidden', 'true');
                            Themify.body.triggerHandler('tb_tabs_switch', [activeTab, tab]);
                            if (!Themify.is_builder_active) {
                               $(window).triggerHandler( 'resize' );
                            }
                            $(this).closest('.module-tab').find('.tab-nav-current-active').click();
                            return false;
                        });
                    }
                    items = null;
                    for(var i=tabs.length-1;i>-1;--i){
                        var sub = tabs[i].querySelectorAll('a[href^="#tab-"]');
                        for(var j=sub.length-1;j>-1;--j){
                            $(sub[j]).on('click', function (e) {
                                e.preventDefault();
                                var dest = $(this).prop('hash').replace('#tab-', ''),
                                    contentID = $('.tab-nav:first', tab).siblings('.tab-content').eq(dest - 1).prop('id');
                                $('a[href^="#' + contentID + '"]').trigger('click');
                            });
                        }
                    }
                    tabs = null;
                    $('.tab-nav-current-active', tab).click(function () {
                        var $this = $(this);
                        if ($this.hasClass('clicked')) {
                            $this.removeClass('clicked');
                        } else {
                            if (($this.position().left > 0) && ($this.position().left <= $this.closest('.module-tab').width() / 2)) {
                                $this.next('.tab-nav').removeClass('right-align').addClass('center-align');
                            } else if ($this.position().left > $this.closest('.module-tab').width() / 2) {
                                $this.next('.tab-nav').removeClass('center-align').addClass('right-align');
                            } else {
                                $this.next('.tab-nav').removeClass('center-align right-align');
                            }
                            $this.addClass('clicked');

                        }
                    });
                }

            }
        });
    };
})(jQuery);

/*
 * Parallax Scrolling Builder
 */
(function ($, window) {

    'use strict';

    var $window = $(window),
            wH = null,
            is_mobile = false,
            isInitialized = false,
            className = 'builder-parallax-scrolling',
            defaults = {
                xpos: '50%',
                speedFactor: 0.1
            };
    function Plugin(element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);
        this.init();
    }
    Plugin.prototype = {
        items: [],
        top: 0,
        index: 0,
        scrollEvent:null,
        init: function () {
            this.top = this.element.offset().top;
            this.items.push(this);
            if (!isInitialized) {
                wH = $window.height();
                is_mobile = ThemifyBuilderModuleJs._isMobile();
                $window.on('tfsmartresize.builderParallax', this.resize.bind(this));
                this.scrollEvent=this.scroll.bind(this);
                window.addEventListener('scroll', this.scrollEvent,{passive:true,capture: true});
                isInitialized = true;
            }
            this.update();
        },
        scroll:function(){
            for (var i in this.items) {
                this.items[i].update(i);
            }
        },
        resize: function () {
            wH = $window.height();
            for (var i in this.items) {
                this.items[i].top = this.items[i].element.offset().top;
                this.items[i].update(i);
            }
        },
        destroy: function (index) {
            if (this.items[index] !== undefined) {
                this.items.splice(index, 1);
                if (this.items.length === 0) {
                    $window.off('tfsmartresize.builderParallax');
                    window.removeEventListener('scroll', this.scrollEvent,{passive:true,capture: true});
                    isInitialized = false;
                }
            }
        },
        update: function (i) {
            if (document.body.contains(this.element[0]) === false || this.element[0].className.indexOf(className) === -1) {
                this.destroy(i);
                return;
            }
            var pos = $window.scrollTop(),
                    top = this.element.offset().top,
                    outerHeight = this.element.outerHeight(true);
            // Check if totally above or totally below viewport
            if ((top + outerHeight) < pos || top > (pos + wH)) {
                return;
            }
            if (is_mobile) {
                /* #3699 = for mobile devices increase background-size-y in 30% (minimum 400px) and decrease background-position-y in 15% (minimum 200px) */
                var outerWidth = this.element.outerWidth(true),
                        dynamicDifference = outerHeight > outerWidth ? outerHeight : outerWidth;
                dynamicDifference = Math.round(dynamicDifference * 0.15);
                if (dynamicDifference < 200) {
                    dynamicDifference = 200;
                }
                this.element.css({
                    backgroundSize: 'auto ' + Math.round(outerHeight + (dynamicDifference * 2)) + 'px',
                    'background-position-y': Math.round(((this.top - pos) * this.options.speedFactor) - dynamicDifference) + 'px'
                });
            }
            else {
                this.element.css('background-position-y', Math.round((this.top - pos) * this.options.speedFactor) + 'px');
            }
        }
    };
    $.fn['builderParallax'] = function (options) {
        return this.each(function () {
            $.data(this, 'plugin_builderParallax', new Plugin($(this), options));

        });
    };
})(jQuery, window);

(function ($, window,Themify, document, undefined) {

    'use strict';

    ThemifyBuilderModuleJs = {
        wow: null,
        is_mobile: null,
        is_tablet: null,
		loaded:{},
        fwvideos: [], // make it accessible to public
        init: function () {
            if ('complete' !== document.readyState && 'interactive' !== document.readyState) {
                $(this.document_ready);
            } else {
                this.document_ready();
            }
            if (window.loaded) {
                this.window_load();
            } else {
                $(window).one('load', this.window_load);
            }

            if (window.onresize) {
                this.window_resize();
            }
            else {
                $(window).on('tfsmartresize.moduleTabs', this.window_resize);
            }
        },
        /**
         * Executed on jQuery's document.ready() event.
         */
        document_ready: function () {
            var self = ThemifyBuilderModuleJs;
            self.setupBodyClasses();
            Themify.body.triggerHandler('themify_builder_loaded');
            if (tbLocalScript.fullwidth_support === '') {
                $(window).on('tfsmartresize.tbfullwidth', function (e) {
                    self.setupFullwidthRows(null,true);
                });
            }
            self.tabsClick();
            if (!Themify.is_builder_active) {
                self.addonLoad();
                if (tbLocalScript.fullwidth_support === '') {
                    self.setupFullwidthRows();
                }
                self.GridBreakPoint();
                self.carousel();
                self.touchdropdown();
                self.tabs();
                self.onInfScr();
                self.menuModuleMobileStuff();
                self.playFocusedVideoBg();
                self.alertModule();
                self.pageBreakPagination();
                $(window).on('hashchange', this.tabsDeepLink);
                self.optinModuleInit();
                self.signupModuleInit();
                self.socialShareModule();
                self.testimonial();
            }
            self.showcaseGallery();
            $(window).on('tfsmartresize.tblink', function () {
                self.menuModuleMobileStuff(true);
            });
            self.InitScrollHighlight();
            self.readMoreLink();
            self.galleryPagination();
            self.loginModuleInit();
        },
        /**
         * Executed on JavaScript 'load' window event.
         */
        window_load: function () {
            var self = ThemifyBuilderModuleJs;
            window.loaded = true;

            if (!Themify.is_builder_active) {
                self.charts();
                self.fullwidthVideo();
                self.backgroundSlider();
                self.backgroundZoom();
                self.backgroundZooming();
                self.stickyElementInit();
                if (tbLocalScript.isParallaxActive) {
                    self.backgroundScrolling();
                }
                self.tabsDeepLink();
                self.gallery();
                if (tbLocalScript.isAnimationActive) {
                    self.wowInit();
                }
                Themify.body.on('infiniteloaded.themify',function(e,el){
                    if (typeof tbLocalScript !== 'undefined') {
                        var assets = el[0].getElementsByClassName('tb_async_module');
                        if (tbLocalScript.addons === null) {
                            tbLocalScript['addons'] = {};
                        }
                        for (var i = assets.length - 1; i > -1; --i) {
                            var asset = assets[i].getAttribute('data-data');
                            if (asset) {
                                asset = JSON.parse(asset);
                                var external = assets[i].getAttribute('data-external');
                                external = external ? JSON.parse(external) : null;
                                for (var k in asset) {
                                    if (self.loaded[k] === undefined) {
                                        self.loaded = true;
                                        if (external) {
                                            asset[k]['external'] = external;
                                        }
                                        tbLocalScript.addons[k] = asset[k];
                                        self.addonLoad(el, k);
                                    }
                                }

                            }
                            assets[i].parentNode.removeChild(assets[i]);
                        }

                        Themify.body.triggerHandler('builder_load_module_partial', [el]);
                        self.loadOnAjax(el);
                    }
                });
            }
            else {
                self.wowApplyOnHover();
            }
            self.videoPlay();
            self.accordion();
        },
        window_resize: function () {
            var items =document.querySelectorAll('.module-tab[data-tab-breakpoint]'),
                len=items.length;
            if (len> 0) {
                var windowWidth = window.innerWidth;
                for(var i=0;i<len;++i){
                    if (parseInt(items[i].getAttribute('data-tab-breakpoint')) >= windowWidth) {
                        items[i].classList.add('responsive-tab-style');
                    } else {
                        items[i].classList.remove('responsive-tab-style');
                        var nav = items[i].getElementsByClassName('tab-nav');
                        for(var j=nav.length-1;j>-1;--j){
                            nav[j].classList.remove('right-align'); 
                            nav[j].classList.remove('center-align'); 
                        }
                    }
                }
            }
        },
        wowInit: function (callback, resync) {
            var self = ThemifyBuilderModuleJs;
            if (resync && self.wow) {
                self.wow.doSync();
                self.wow.sync();
                return;
            }
            function wowCallback() {
                function wowDuckPunch() {
                    // duck-punching WOW to get delay and iteration from classnames
                    if (typeof self.wow.__proto__ !== 'undefined') {
                        self.wow.__proto__.applyStyle = function (box, hidden) {
                            var duration = box.getAttribute('data-wow-duration'),
                                    cl = box.getAttribute('class'),
                                    iteration = cl.match(/animation_effect_repeat_(\d*)/),
                                    delay = cl.match(/animation_effect_delay_((?:\d+\.?\d*|\.\d+))/);
                            if (null !== delay) {
                                delay = delay[1] + 's';
                            }
                            if (null !== iteration)
                                iteration = iteration[1];
                            return this.animate((function (_this) {
                                return function () {
                                    return _this.customStyle(box, hidden, duration, delay, iteration);
                                };
                            })(this));
                        };
                    }
                }
                self.animationOnScroll(resync);
                self.wow = new WOW({
                    live: true,
                    offset: typeof tbLocalScript !== 'undefined' && tbLocalScript ? parseInt(tbLocalScript.animationOffset) : 100
                });
                self.wow.init();
                wowDuckPunch();
                if (!Themify.is_builder_active) {
                    self.wowApplyOnHover();
                }
            }
            callback = callback || wowCallback;
            if (typeof tbLocalScript !== 'undefined'
                    && typeof tbLocalScript.animationInviewSelectors !== 'undefined'
                    && ($(tbLocalScript.animationInviewSelectors.toString()).length || $('.hover-wow').length)) {
                if (!self.wow) {
                    Themify.LoadCss(tbLocalScript.builder_url + '/css/animate.min.css', null, null, null, function () {
                        Themify.LoadAsync(themify_vars.url + '/js/wow.min.js', callback, null, null, function () {
                            return (self.wow);
                        });
                    });
                }
                else {
                    callback();
                    return (self.wow);
                }
            }
        },
        wowApplyOnHover: function () {
            var is_working = false;
            $(document).on('mouseenter', '.hover-wow', function () {
                if (is_working === false) {
                    is_working = true;
                    var hoverAnimation = this.getAttribute('class').match(/hover-animation-(\w*)/),
                        animation = this.style.animationName;
                    if ('' != animation) {
                        $(this).css('animation-name', '').removeClass(animation);
                    }
                    $(this).off('animationend').one('animationend', function (e) {
                        $(this).removeClass('animated tb_hover_animate ' + e.originalEvent.animationName);
                        is_working = false;
                    }).addClass('animated tb_hover_animate ' + hoverAnimation[1]);
                }
            });
        },
        setupFullwidthRows: function (el,isTrigger) {
            
            var isActive=Themify.is_builder_active;
            if (tbLocalScript.fullwidth_support !== '') {
                return;
            }
            if (!el) {
                if (!isActive && this.rows !== undefined) {
                    el = this.rows;
                }
                else {
                    el = document.querySelectorAll('.fullwidth.module_row,.fullwidth_row_container.module_row');
                    if (!isActive) {
                        this.rows = el;
                    }
                }
                if (el.length === 0) {
                    return;
                }
            }
            else if (!el[0].classList.contains('fullwidth') && !el[0].classList.contains('fullwidth_row_container')) {
                return;
            }
            else {
                el = el.get();
            }
            var container = $(tbLocalScript.fullwidth_container),
                    outherWith = container.outerWidth(),
                    outherLeft = container.offset().left;
            if (outherWith === 0) {
                return;
            }
            var styleId = 'tb-fulllwidth-styles',
                    style = '',
                    tablet = tbLocalScript.breakpoints.tablet,
                    tablet_landscape = tbLocalScript.breakpoints.tablet_landscape,
                    mobile = tbLocalScript.breakpoints.mobile,
                    arr = ['mobile', 'tablet', 'tablet_landscape', 'desktop'],
                    width = $(window).width(),
                    type = 'desktop';
            if (width <= mobile) {
                type = 'mobile';
            }
            else if (width <= tablet[1]) {
                type = 'tablet';
            }
            else if (width <= tablet_landscape[1]) {
                type = 'tablet_landscape';
            }
            function getCurrentValue(prop) {
                var val = $this.data(type + '-' + prop);
                if (val === undefined) {
                    if (type !== 'desktop') {
                        for (var i = arr.indexOf(type) + 1; i < 4; ++i) {
                            if (arr[i] !== undefined) {
                                val = $this.data(arr[i] + '-' + prop);
                                if (val !== undefined) {
                                    $this.data(type + '-' + prop, val);
                                    break;
                                }
                            }
                        }
                    }
                }
                return val !== undefined ? val.split(',') : [];
            }
            for (var i = 0, len = el.length; i < len; ++i) {
                 var $this = $(el[i]),
                        row = $this.closest('.themify_builder_content');
                        if(row.length===0 || row.closest('.slide-content').length!==0){
                                continue;
                        }
                    var left = row.offset().left - outherLeft,
                        right = outherWith - left - row.outerWidth();
                if(isActive){
                    // set to zero when zoom is enabled
                    if (row[0].classList.contains('tb_zooming_50') || row[0].classList.contains('tb_zooming_75')) {
                        left = 0;
                        right = 0;
                    }
                }
                else{
                    var index = $this.attr('class').match(/module_row_(\d+)/)[1];
                    style += '.themify_builder.themify_builder_content>.themify_builder_' + row.data('postid') + '_row.module_row_' + index + '.module_row{';
                }
                if (el[i].classList.contains('fullwidth')) {
                    var margin = getCurrentValue('margin'),
                            sum = '';
                    if (margin[0]) {
                        sum = margin[0];
                        style += 'margin-left:calc(' + margin[0] + ' - ' + Math.abs(left) + 'px);';
                    }
                    else {
                        style += 'margin-left:' + (-left) + 'px;';
                    }
                    if (margin[1]) {
                        if (sum !== '') {
                            sum += ' + ';
                        }
                        sum += margin[1];
                        style += 'margin-right:calc(' + margin[1] + ' - ' + Math.abs(right) + 'px);';
                    }
                    else {
                        style += 'margin-right:' + (-right) + 'px;';
                    }
                    style += sum !== '' ? 'width:calc(' + outherWith + 'px - (' + sum + '));' : 'width:' + outherWith + 'px;';
                }
                else {
                    style += 'margin-left:' + (-left) + 'px;margin-right:' + (-right) + 'px;width:' + outherWith + 'px;';
                    if (left || right) {
                        var padding = getCurrentValue('padding'),
                                sign = '+';
                        if (left) {
                            if (padding[0]) {
                                if (left < 0) {
                                    sign = '-';
                                }
                                style += 'padding-left:calc(' + padding[0] + ' ' + sign + ' ' + Math.abs(left) + 'px);';
                            }
                            else {
                                style += 'padding-left:' + Math.abs(left) + 'px;';
                            }
                        }
                        if (right) {
                            if (padding[1]) {
                                sign = right > 0 ? '+' : '-';
                                style += 'padding-right:calc(' + padding[1] + ' ' + sign + ' ' + Math.abs(right) + 'px);';
                            }
                            else {
                                style += 'padding-right:' + Math.abs(right) + 'px;';
                            }
                        }
                    }
                }

                if (isActive) {
                    el[i].style['paddingRight'] = el[i].style['paddingLeft'] = el[i].style['marginRight'] = el[i].style['marginLeft'] = '';
                    el[i].style.cssText += style;
                    style = '';
                }
                else {
                    style += '}';
                }
            }
            if (!isActive) {
                style = '<style id="' + styleId + '" type="text/css">' + style + '</style>';
                $('#' + styleId).remove();
                document.head.insertAdjacentHTML('beforeend', style);
                if(isTrigger!==true){
                    $(window).triggerHandler('resize');
                }
            }
        },
        addQueryArg: function (e, n, l) {
            l = l || window.location.href;
            var r, f = new RegExp("([?&])" + e + "=.*?(&|#|$)(.*)", "gi");
            if (f.test(l))
                return 'undefined' !== typeof n && null !== n ? l.replace(f, "$1" + e + "=" + n + "$2$3") : (r = l.split("#"), l = r[0].replace(f, "$1$3").replace(/(&|\?)$/, ""), 'undefined' !== typeof r[1] && null !== r[1] && (l += "#" + r[1]), l);
            if ('undefined' !== typeof n && null !== n) {
                var i = -1 !== l.indexOf("?") ? "&" : "?";
                return r = l.split("#"), l = r[0] + i + e + "=" + n, 'undefined' !== typeof r[1] && null !== r[1] && (l += "#" + r[1]), l
            }
            return l;
        },
        onInfScr: function () {
            var self = ThemifyBuilderModuleJs;
            $(document).ajaxSend(function (e, request, settings) {
                var page = settings.url.replace(/^(.*?)(\/page\/\d+\/)/i, '$2'),
                        regex = /^\/page\/\d+\//i,
                        match;

                if ((match = regex.exec(page)) !== null) {
                    if (match.index === regex.lastIndex) {
                        regex.lastIndex++;
                    }
                }

                if (null !== match) {
                    settings.url = self.addQueryArg('themify_builder_infinite_scroll', 'yes', settings.url);
                }
            });
        },
        InitScrollHighlight: function () {
            if (tbLocalScript.loadScrollHighlight == true && (Themify.is_builder_active || document.querySelectorAll('div[class*=tb_section-],.module[id]').length>0)) {
                Themify.LoadAsync(tbLocalScript.builder_url + '/js/themify.scroll-highlight.js', function () {
                    themifyScrollHighlight.init(tbScrollHighlight ? tbScrollHighlight : {});

                }, null, null, function () {
                    return window['themifyScrollHighlight']!==undefined;
                });
            }
        },
        // Row, col, sub-col, sub_row: Background Slider
        backgroundSlider: function (el) {
            var $bgSlider = $('.tb_slider',el);
            function callBack() {
                var themifySectionVars = {
                    autoplay: tbLocalScript.backgroundSlider.autoplay
                };
                // Parse injected vars
                themifySectionVars.autoplay = parseInt(themifySectionVars.autoplay, 10);
                if (themifySectionVars.autoplay <= 10) {
                    themifySectionVars.autoplay *= 1000;
                }
                // Initialize slider
                $bgSlider.each(function () {
                    var $thisRowSlider = $(this),
                            $backel = $thisRowSlider.parent(),
                            rsImages = [],
                            rsImagesAlt = [],
                            imagesCount,
                            bgMode = $thisRowSlider.data('bgmode'),
                            speed = $thisRowSlider.data('sliderspeed');

                    // Initialize images array with URLs
                    $thisRowSlider.find('li').each(function () {
                        rsImages.push($(this).attr('data-bg'));
                        rsImagesAlt.push($(this).attr('data-bg-alt'));
                    });

                    imagesCount = (rsImages.length > 4) ? 4 : rsImages.length;

                    // Call backstretch for the first time
                    $backel.tb_backstretch(rsImages, {
                        speed: parseInt(speed),
                        duration: themifySectionVars.autoplay,
                        mode: bgMode
                    });
                    rsImages = null;

                    // Cache Backstretch object
                    var thisBGS = $backel.data('tb_backstretch');

                    // Previous and Next arrows
                    $thisRowSlider.find('.row-slider-prev,.row-slider-next').on('click', function (e) {
                        e.preventDefault();
                        if ($(this).hasClass('row-slider-prev')) {
                            thisBGS.prev();
                        }
                        else {
                            thisBGS.next();
                        }
                    });

                    // Dots
                    $thisRowSlider.find('.row-slider-dot').on('click', function () {
                        thisBGS.show($(this).data('index'));
                    });

                    // Active Dot
                    var sliderDots = $thisRowSlider.find('.row-slider-slides > li'),
                            currentClass = 'row-slider-dot-active';

                    if (sliderDots.length) {
                        sliderDots.eq(0).addClass(currentClass);

                        $thisRowSlider.parent().on('tb_backstretch.show', function (e, data) {
                            var currentDot = sliderDots.eq(thisBGS.index);

                            if (currentDot.length) {
                                sliderDots.removeClass(currentClass);
                                currentDot.addClass(currentClass);
                            }
                        });
                    }

                    if ($thisRowSlider.attr('data-bgmode') === 'kenburns-effect') {

                        var lastIndex,
                                kenburnsActive = 0,
                                createKenburnIndex = function () {
                                    return (kenburnsActive + 1 > imagesCount) ? kenburnsActive = 1 : ++kenburnsActive;
                                };

                        $thisRowSlider.parent().on('tb_backstretch.before', function (e, data) {

                            setTimeout(function () {

                                if (lastIndex != data.index) {
                                    var $img = data.$wrap.find('img').last();
                                    $img.addClass('kenburns-effect' + createKenburnIndex());
                                    lastIndex = data.index;
                                }

                            }, 50);

                        }).on('tb_backstretch.after', function (e, data) {

                            var $img = data.$wrap.find('img').last(),
                                    expr = /kenburns-effect\d/;
                            if (!expr.test($img.attr('class'))) {
                                $img.addClass('kenburns-effect' + createKenburnIndex());
                                lastIndex = data.index;
                            }

                        });

                    }

                    // Add alt tag
                    $(window).on('backstretch.before backstretch.show', function (e, instance, index) {
                        // Needed for col styling icon and row grid menu to be above row and sub-row top bars.
                        if (Themify.is_builder_active) {
                            $backel.css('zIndex', 0);
                        }
                        if (rsImagesAlt[ index ] !== undefined) {
                            setTimeout(function () {
                                instance.$wrap.find('img:not(.deleteable)').attr('alt', rsImagesAlt[ index ]);
                            }, 1);
                        }
                    });
                });
            }
            if ($bgSlider.length > 0) {
                if('undefined' === typeof $.fn.tb_backstretch){
                    Themify.LoadAsync(
                            themify_vars.url + '/js/backstretch.themify-version.js',
                            callBack,
                            null,
                            null,
                            function () {
                                return ('undefined' !== typeof $.fn.tb_backstretch);
                            }
                    );
                }
                else{
                    callBack();
                }
            }
        },
        // Row: Fullwidth video background
        fullwidthVideo: function ($videoElm, parent) {
            if ($videoElm) {
                $videoElm = $videoElm.data('fullwidthvideo') ? $videoElm : $('[data-fullwidthvideo]', $videoElm);
            }

            parent = parent || $('.themify_builder');
            $videoElm = $videoElm || $('[data-fullwidthvideo]', parent);

            if ($videoElm.length > 0) {

                var self = this,
                        is_mobile = this._isMobile(),
                        is_youtube = [],
                        is_vimeo = [],
                        is_local = [];

                $videoElm.each(function (i) {
                    var $video = $(this),
                        url = $video.data('fullwidthvideo');
                    if (!url) {
                        return true;
                    }

                    $video.children('.big-video-wrap').remove();
                    var provider = Themify.parseVideo(url);
                    if (provider.type === 'youtube') {
                        if (!is_mobile && provider.id) {
                            var ytOpt = {'el': $video, 'id': provider.id},
                                params = url.split('?')[1];
                            if(undefined != params && params.indexOf('t=')>=0){
                                ytOpt['startAt'] = params.split('=')[1];
                            }
                            is_youtube.push(ytOpt);
                        }
                    }
                    else if (provider.type === 'vimeo') {
                        if (!is_mobile && provider.id) {
                            is_vimeo.push({'el': $video, 'id': provider.id});
                        }
                    } else {
                        is_local.push($video);
                    }
                });
                $videoElm = null;
                if (is_local.length > 0) {
                    if (!is_mobile) {
                        Themify.LoadAsync(
                                themify_vars.url + '/js/bigvideo.js',
                                function () {
                                    self.fullwidthVideoCallBack(is_local);
                                    is_local = null;
                                },
                                null,
                                null,
                                function () {
                                    return ('undefined' !== typeof $.fn.ThemifyBgVideo);
                                }
                        );
                    }
                    else {
                        for (var i = is_local.length-1; i>-1; --i) {
                            if ('play' === is_local[i].data('playonmobile')) {
                                var videoURL = is_local[i].data('fullwidthvideo'),
                                    loop = 'unloop' !== is_local[i].data('unloopvideo')?' loop':'',
                                    id = Themify.hash(i + '-' + videoURL),
                                        videoEl = '<div class="big-video-wrap">'
                                        + '<video class="video-' + id + '" muted="true" autoplay="true" playsinline="true"'+ loop +' >' +
                                        '<source src="' + videoURL + '" type="video/mp4">' +
                                        '</video></div>';
                                is_local[i][0].insertAdjacentHTML('afterbegin', videoEl);
                            }
                        }
                        is_local = null;
                    }
                }

                if (is_vimeo.length > 0) {
                    Themify.LoadAsync(
                            tbLocalScript.builder_url + '/js/froogaloop.min.js',
                            function () {
                                self.fullwidthVimeoCallBack(is_vimeo);
                                is_vimeo = null;
                            },
                            null,
                            null,
                            function () {
                                return ('undefined' !== typeof $f);
                            }
                    );
                }
                if (is_youtube.length > 0) {
                    if (!$.fn.ThemifyYTBPlayer) {
                        Themify.LoadAsync(
                                tbLocalScript.builder_url + '/js/themify-youtube-bg.js',
                                function () {
                                    self.fullwidthYoutobeCallBack(is_youtube);
                                    is_youtube = null;
                                },
                                null,
                                null,
                                function () {
                                    return typeof $.fn.ThemifyYTBPlayer !== 'undefined';
                                }
                        );
                    } else {
                        self.fullwidthYoutobeCallBack(is_youtube);
                    }
                }
            }
        },
        videoParams: function ($el) {
            var mute = 'unmute' !==$el[0].getAttribute('data-mutevideo'),
                loop = 'unloop' !==$el[0].getAttribute('data-unloopvideo');

            return {'mute': mute, 'loop': loop};
        },
        // Row: Fullwidth video background
        fullwidthVideoCallBack: function (videos) {
            for (var i =videos.length-1; i>-1; --i) {
                var videoURL = videos[i].data('fullwidthvideo'),
                        params = ThemifyBuilderModuleJs.videoParams(videos[i]);
                videos[i].ThemifyBgVideo({
                    url: videoURL,
                    doLoop: params.loop,
                    ambient: params.mute,
                    id: Themify.hash(i + '-' + videoURL)
                });
            }
        },
        fullwidthYoutobeCallBack: function (videos) {
            var self = this;
            if (window['YT'] === undefined || typeof YT.Player === 'undefined') {
                Themify.LoadAsync(
                        '//www.youtube.com/iframe_api',
                        function () {
                            window.onYouTubePlayerAPIReady = _each;
                        },
                        null,
                        null,
                        function () {
                            return window['YT'] !== undefined && typeof YT.Player !== 'undefined';
                        });
            }
            else {
                _each();
            }
            function _each() {
                for (var i =videos.length-1; i >-1; --i) {
                    var params = self.videoParams(videos[i].el);
                    videos[i].el.ThemifyYTBPlayer({
                        videoID: videos[i].id,
                        id: videos[i].el.closest('.themify_builder_content').data('postid') + '_' + i,
                        mute: params.mute,
                        loop: params.loop,
                        mobileFallbackImage: tbLocalScript.videoPoster,
                        startAt: undefined != videos[i].startAt ? parseInt(videos[i].startAt) : 0
                    });
                }
            }
        },
        fullwidthVimeoCallBack: function (videos) {
            var self = this;
            if (typeof self.fullwidthVimeoCallBack.counter === 'undefined') {
                self.fullwidthVimeoCallBack.counter = 1;
                $(window).on('tfsmartresize.tfVideo', function vimeoResize(e) {
                    for (var i in videos) {
                        if (videos[i]) {
                            var ch = videos[i].el.children('.themify-video-vmieo');
                            if (ch.length > 0) {
                                VimeoVideo(ch);
                            }
                            else {
                                delete videos[i];
                            }
                        }
                    }
                    if (videos.length === 0) {
                        self.fullwidthVimeoCallBack.counter = 'undefined';
                        $(window).off('tfsmartresize.tfVideo', vimeoResize);
                    }
                });

            }
            function VimeoVideo($video) {
                var width = $video.outerWidth(true),
                        height = $video.outerHeight(true),
                        pHeight = Math.ceil(width / 1.7), //1.7 ~ 16/9 aspectratio
                        iframe = $video.find('iframe');
                iframe.width(width).height(pHeight).css({
                    left: 0,
                    top: (height - pHeight) / 2
                });
            }
            var max_len = document.getElementsByClassName('themify-video-vmieo').length;
            for (var i in videos) {
                if (videos[i]) {
                    var $video = videos[i].el,
                        index = max_len+parseInt(i),
                        params = self.videoParams($video),
                        bigV = document.createElement('div'),
                        iframe=document.createElement('iframe');
                        bigV.className = 'big-video-wrap themify-video-vmieo';
                        iframe.id='themify-vimeo-'+index;
                        iframe.setAttribute('frameborder',0);
                        iframe.setAttribute('allowfullscreen',true);
                        iframe.setAttribute('webkitallowfullscreen',true);
                        iframe.setAttribute('mozallowfullscreen',true);
                        iframe.src='//player.vimeo.com/video/' + videos[i].id + '?api=1&portrait=0&title=0&badge=0&player_id=themify-vimeo-' + index;
                        bigV.appendChild(iframe);
                        $video[0].insertAdjacentElement('afterbegin',bigV);
                        var player = $f(iframe);
                        player.addEvent('ready', function () {
                            player.api('setLoop', params.loop);
                            player.api('setVolume', params.mute ? 0 : 1);
                            player.api('fullscreen', 0);
                            var v = $video.children('.themify-video-vmieo');
                            if (v.length > 1) {
                                v.slice(1).remove();
                            }
                            VimeoVideo(v);
                            player.api('play');
                        });
                }
            }
        },
        playFocusedVideoBg: function () {
            var self = ThemifyBuilderModuleJs,
                playOnFocus = function () {
                    if(Themify.is_builder_active){
                        window.removeEventListener('scroll', playOnFocus,{passive:true,capture: true});
                        window.removeEventListener('mouseenter', playOnFocus,{passive:true,capture: true});
                        window.removeEventListener('keydown', playOnFocus,{passive:true,capture: true});
                        $(window).off('assignVideo', playOnFocus);
                    }
                    if (!self.fwvideos.length > 0){
                        return;
                    }
                    var h = window.innerHeight;
                    for (var i in self.fwvideos) {
                        var el = self.fwvideos[i].getPlayer();
                        if (el.isPlaying || !el.source) {
                            return;
                        }
                        var rect = el.P.getBoundingClientRect();
                        if (rect.bottom >= 0 && rect.top <= h) {
                            el.show(el.source);
                            el.isPlaying = true;
                        }
                    }
                };
            window.addEventListener('scroll', playOnFocus,{passive:true,capture: true});
            window.addEventListener('mouseenter', playOnFocus,{passive:true,capture: true});
            window.addEventListener('keydown', playOnFocus,{passive:true,capture: true});
            $(window).on('assignVideo', playOnFocus);
        },
        charts: function (el) {
            var elements = $('.module-feature-chart-html5', el),
                    self = this;
            if (elements.length > 0) {
                if (this.charts_data === undefined) {
                    this.charts_data = {};
                }
                Themify.LoadAsync(themify_vars.url + '/js/waypoints.min.js', callback, null, null, function () {
                    return ('undefined' !== typeof $.fn.waypoint);
                });
            }
            function callback() {

                function chartsCSS(charts) {
                    var styleId = 'chart-html5-styles',
                        css = '',
                        style = document.createElement('style');
                        style.type = 'text/css';
                        style.id=styleId;
                    for (var i in charts) {
                        css += '.module-feature-chart-html5[data-progress="' + i + '"] .chart-html5-full,' +
                                '.module-feature-chart-html5[data-progress="' + i + '"] .chart-html5-fill {transform:rotate(' + charts[i] + 'deg);}';
                    }
                    if (style.styleSheet){
                        style.styleSheet.cssText = css;
                    } else {
                      style.appendChild(document.createTextNode(css));
                    }
                    $('#' + styleId).remove();
                    document.getElementsByTagName('head')[0].insertAdjacentElement('beforeend', style);
                }

                // this mess adjusts the size of the chart, to make it responsive
                var setChartSize = function ($this) {
                    var width = Math.min($this.data('size'), $this.closest('.module-feature').width()),
                            halfw = Math.ceil(width / 2);
                    $this.css({width: width, height: width}).find('.chart-html5-mask').css({
                        borderRadius: '0 ' + halfw + 'px ' + halfw + 'px 0',
                        clip: 'rect(0px, ' + width + 'px, ' + width + 'px, ' + halfw + 'px)'
                    });

                    $this.find('.chart-html5-fill').addClass('chart-loaded').css({
                        borderRadius: halfw + 'px 0 0 ' + halfw + 'px',
                        clip: 'rect(0px, ' + halfw + 'px, ' + width + 'px, 0px)'
                    });
                };
                var deg = parseFloat(180 / 100).toFixed(2),
                        reinit = false;
                elements.each(function () {
                    var progress = $(this).data('progress-end');
                    if (progress === undefined) {
                        progress = 100;
                    }
                    if (self.charts_data[progress] === undefined) {
                        self.charts_data[progress] = parseFloat(deg * progress).toFixed(2) - 0.1;
                        reinit = true;
                    }
                    setChartSize($(this));
                });
                if (reinit === true) {
                    chartsCSS(self.charts_data);
                }
                if (!Themify.body[0].classList.contains('full-section-scrolling-horizontal')) {
                    elements.each(function () {
                        var $this = $(this),
                                horizontal = ($this.closest('.module_row_slide').is(':not(:first-child)'));
                        if (horizontal) {
                            $this.waypoint(function () {
                                $this.attr('data-progress', $this.data('progress-end'));
                            }, {
                                horizontal: true,
                                offset: 'right-in-view'
                            });
                        } else {
                            $this.waypoint(function () {
                                $this.attr('data-progress', $this.data('progress-end'));
                            }, {
                                offset: '100%',
                                triggerOnce: true
                            });
                        }
                    });
                } else if (!Themify.is_builder_active) {
                    Themify.body.on('themify_onepage_afterload', function(event, $slide) {
                        $slide.find('.module-feature-chart-html5').each(function () {
                            $(this).attr('data-progress', $(this).data('progress-end'));
                        });
                    });
                }
                $(window).on('tfsmartresize.charts', function () {
                    elements.each(function () {
                        setChartSize($(this));
                    });
                });
            }
        },
        carousel: function (el) {
            var items = $('.themify_builder_slider', el);
            if (items.length > 0) {
                var self = this,
                    isMobile = function () {
                        return tbLocalScript && tbLocalScript.breakpoints
                                ? tbLocalScript.breakpoints.mobile > window.innerWidth : self._isMobile();
                    },
                    isTablet = function () {
                        return tbLocalScript && tbLocalScript.breakpoints
                            ? tbLocalScript.breakpoints.mobile < window.innerWidth && (tbLocalScript.breakpoints.tablet[1] >= window.innerWidth || tbLocalScript.breakpoints.tablet_landscape[1] > window.innerWidth) : self._isTablet();
                    },
                    carouselCalback = function () {
                        var sliderAutoHeight=function ($this) {
                            if ('video' === $this.data('type')) {
                                // Get all the possible height values from the slides
                                var heights = $this.children().map(function () {
                                    return $(this).height();
                                });
                                $this.parent().height(Math.max.apply(null, heights));
                            } else if ($this.closest('.module-slider').is('.slider-overlay, .slider-caption-overlay')) {
                                var sliderContent = $this.find('.slide-content'),
                                        originalOffset = 0;
                                if (sliderContent.eq(0).attr('style') === undefined) {
                                    originalOffset = parseFloat(sliderContent.eq(0).css('bottom'));
                                    $this.data('captionOffset', originalOffset);
                                } else if ($this.data('captionOffset')) {
                                    originalOffset = $this.data('captionOffset');
                                }

                                sliderContent.each(function () {
                                    var $el = $(this),
                                            captionOffset = $el.closest('.slide-inner-wrap').height() - $this.parent().height();
                                    $el.css('bottom', captionOffset + originalOffset);
                                });
                            }
                        },
                        carouselInitSwipe = function ($this, args) {
                           $this.carouFredSel(args);
                           if (args.auto && args.auto.timeoutDuration === 0 && args.next && args.prev && args.scroll) {
                               $([args.next, args.prev].join()).on('click',  function () {
                                   $this.trigger('finish').trigger($(this).is(args.next) ? 'next' : 'prev', [{duration: args.scroll.duration * 2}]);
                               });
                           }
                        };

                        items.each(function () {
                            if(this.classList.contains('carousel_init')){
                                return true;
                            }
                            this.classList.add('carousel_init');
                            var $this = $(this),
								randomId = 'tb_' + Math.random().toString(36).substr(2, 9), // instance ID, unique to each slider instance
                                img_length = $this.find('img').length,
                                wrap = $this.closest('.themify_builder_slider_wrap,.'+$this.data('id')),
                                $height = (typeof $this.data('height') === 'undefined') ? 'variable' : $this.data('height'),
                                device = isTablet() ? 'tab' : (isMobile() ? 'mob' : ''),
                                visibleItems = '' !== device && $this.data(device+'-visible') ? $this.data(device+'-visible') : {min: 1, max: $this.data('visible')},
                                isHorizontal = $this.data('horizontal') && $this.data('horizontal') === 'yes',
                                args = {
                                    responsive: true,
                                    circular: true,
                                    infinite: true,
                                    height: $height,
                                    items: {
                                        visible: visibleItems,
                                        width: 150,
                                        height: 'variable'
                                    },
                                    onCreate: function (items) {
                                        wrap.css({'visibility': 'visible', 'height': 'auto'});
                                        $this.trigger('updateSizes');
                                        $('.tb_slider_loader').remove();

                                        if ('auto' === $height) {
                                            sliderAutoHeight($this);
                                        }
                                        if (!isHorizontal && parseInt($this.data('auto-scroll')) > 0 && $this.data('play-controller') && $this.data('play-controller') === 'yes' && $this.closest('.module-slider')){
                                            var controller = document.createElement('a');
                                            controller.href = '#';
                                            controller.className = 'themify_slider_controller';
                                            controller.setAttribute('aria-label','slider controller');
                                            var car_wrap = wrap.find('.carousel-wrap'),
                                                nav_wrap = car_wrap.find('.carousel-nav-wrap');
                                            if(nav_wrap.length>0){
                                                nav_wrap[0].appendChild(controller);
                                            }else{
                                                nav_wrap = document.createElement('div');
                                                nav_wrap.className = 'carousel-nav-wrap';
                                                nav_wrap.appendChild(controller);
                                                car_wrap[0].appendChild(nav_wrap);
                                            }
                                        }
										Themify.reLayoutIsoTop();
                                        Themify.body.triggerHandler('tb_slider_created',items);
                                    } ,
                                    swipe: {
                                        onMouse: true,
                                        onTouch: true,
                                        options: {
                                                swipeLeft: function () {
                                                   $this.trigger('next', true);
                                                },
                                                swipeRight: function () {
                                                   $this.trigger('prev', true);
                                                },
                                                click: function(e, target) {
                                                        var $carousel = $this.closest('.carousel-wrap').prev().find('.themify_builder_slider').first();
                                                        $carousel.trigger( 'slideTo',$('li[data-index="' + $(target).closest('li').data('index') + '"]', $carousel));
                                                },
                                                excludedElements:"button, input, select, textarea, .noSwipe"
                                        }
                                    }
                                };

							wrap.addClass( randomId );
                            // fix the one slide problem
                            if ($this.children().length < 2) {
                                wrap.css({'visibility': 'visible', 'height': 'auto'});
                                $('.tb_slider_loader').remove();
                                $(window).triggerHandler( 'resize' );
                                return;
                            }

                            // Horizontal scrolling
                            if (isHorizontal) {
                                args.mousewheel = true;
                                args.swipe = {
                                    onMouse: true,
                                    onTouch: true
                                };
                                args.items = {
                                    visible: visibleItems
                                };
                                args.scroll = {
                                    items: 2,
                                    duration: 1000,
                                    timeoutDuration: 0,
                                    easing: 'linear'
                                };
                                args.auto = false;
                                args.circular = false;
                                args.infinite = false;
                            } else { // Standard scrolling
                            // Auto
                            if (parseInt($this.data('auto-scroll')) > 0) {
                                args.auto = {
                                    play: true,
                                    timeoutDuration: parseInt($this.data('auto-scroll') * 1000),
                                    button: '.' + randomId + ' .themify_slider_controller'
                                };
                                } else if ($this.data('effect') !== 'continuously' && (typeof $this.data('auto-scroll') !== 'undefined' || parseInt($this.data('auto-scroll')) === 0)) {
                                args.auto = false;
                            }

                            // Scroll
                            if ($this.data('effect') === 'continuously') {
                                var speed = $this.data('speed'), duration;
                                if (speed == .5) {
                                    duration = 0.10;
                                } else if (speed == 4) {
                                    duration = 0.04;
                                } else {
                                    duration = 0.07;
                                }
                                args.auto = {timeoutDuration: 0};
                                args.align = false;
                                args.scroll = {
                                    delay: 1000,
                                    easing: 'linear',
                                    items: $this.data('scroll'),
                                    duration: duration,
                                    pauseOnHover: $this.data('pause-on-hover')
                                };
                            } else {
                                args.scroll = {
                                    items: $this.data('scroll'),
                                    pauseOnHover: $this.data('pause-on-hover'),
                                    duration: parseInt($this.data('speed') * 1000),
                                    fx: $this.data('effect')
                                };
                            }
                            if ($this.data('arrow') === 'yes') {
                                args.prev = '.' + randomId + ' .carousel-prev';
                                args.next = '.' + randomId + ' .carousel-next';
                            }

                            if ($this.data('pagination') === 'yes') {
                                args.pagination = {
                                    container: '.' + randomId + ' .carousel-pager',
                                    items: $this.data('visible')
                                };
                            }
                            }

                            if ($this.data('wrap') === 'no') {
                                args.circular = false;
                                args.infinite = false;
                            }

                            if ($this.data('sync')) {
                                args.synchronise = [$this.data('sync'), false];
                            }

                            if (img_length > 0) {
                                Themify.imagesLoad($this,function () {
                                    carouselInitSwipe($this, args);
                                });
                            } else {
                                carouselInitSwipe($this, args);
                            }
                            args.scroll.onBefore = function(data){Themify.body.triggerHandler('tb_slider_before_scroll',data)};
                            args.scroll.onAfter = function(data){Themify.body.triggerHandler('tb_slider_after_scroll',data)};
                            $('.mejs__video').on('resize', function (e) {
                                e.stopPropagation();
                            });
                            var vMode;
                            $(window).on('tfsmartresize', function () {
                                $('.mejs__video').resize();
                                if ('auto' === $height) {
                                    sliderAutoHeight($this);
                                }
                                var device = isTablet() ? 'tab' : (isMobile() ? 'mob' : '');
                                if ((!vMode || vMode === 'desktop' || vMode === 'mobile') && '' !== device && $this.data(device + '-visible')) {
                                    vMode = 'mobile';
                                    $this.trigger('finish').trigger('configuration', {items: {visible: $this.data(device+'-visible')}});
                                } else if (!vMode || vMode === 'mobile' || $this.closest('.themify-popup').length>0) {
                                    vMode = 'desktop';
                                    $this.trigger('finish').trigger('configuration', {items: {visible: $this.data('visible')}});
                                }
                                $this.trigger('updateSizes');
                            });

                        });
                };
                Themify.imagesLoad(function () {
                    if ('undefined' === typeof $.fn.carouFredSel) {
                        Themify.LoadAsync(themify_vars.url + '/js/carousel.min.js', carouselCalback, null, null, function () {
                            return ('undefined' !== typeof $.fn.carouFredSel);
                        });
                    }
                    else {
                        carouselCalback();
                    }
                });
            }

        },
        addonLoad: function (el, slug) {
            if (window['tbLocalScript']!==undefined && window['tbLocalScript'].addons && Object.keys(window['tbLocalScript'].addons).length > 0) {
                var addons;
                if(slug){
                    if(tbLocalScript.addons[slug]===undefined){
                        return;
                    }
                    else {
                        addons = {};
                        addons[slug] = tbLocalScript.addons[slug];
                    }
                }
                else{
                    addons = tbLocalScript.addons;
                }
                for (var i in addons) {
                    if (document.querySelector(addons[i].selector)!==null) {
                        if (addons[i].css) {
                            Themify.LoadCss(addons[i].css, addons[i].ver);
                        }
                        if (addons[i].js) {
                            Themify.LoadAsync(addons[i]['js'], null, addons[i]['ver'],{'before':addons[i]['external']});
                        }
                        delete tbLocalScript.addons[i];
                        if (el) {
                            break;
                        }
                    }
                }
                
                if (!Themify.is_builder_active) {
                    tbLocalScript.addons = null;
                }
            }
        },
        loadOnAjax: function (el, type) {
            var self = ThemifyBuilderModuleJs;
            if (type === 'row') {
                self.setupFullwidthRows(el);
            }
            self.touchdropdown(el);
            self.tabs(el);
            self.carousel(el);
            self.charts(el);
            self.fullwidthVideo(el, null);
            self.backgroundSlider(el);
            self.testimonial(el);
            var zoomScrolling = null,
                    zoom = null,
                    bgscrolling = null;
            if (el) {
                zoomScrolling = el.find('.builder-zoom-scrolling');
                if (el[0].classList.contains('builder-zoom-scrolling')) {
                    zoomScrolling = zoomScrolling.add(el);
                }
                zoom = el.find('.builder-zooming');
                if (el[0].classList.contains('builder-zooming')) {
                    zoom = zoom.add(el);
                }
                if (tbLocalScript.isParallaxActive) {
                    bgscrolling = el.find('.builder-parallax-scrolling');
                    if (el[0].classList.contains('builder-parallax-scrolling')) {
                        bgscrolling = bgscrolling.add(el);
                    }
                }
            }
            if (zoomScrolling === null || zoomScrolling.length > 0) {
                self.backgroundZoom(zoomScrolling);
            }
            zoomScrolling = null;
            if (zoom === null || zoom.length > 0) {
                self.backgroundZooming(zoom);
            }
            zoom = null;
            if (tbLocalScript.isParallaxActive && (bgscrolling === null || bgscrolling.length > 0)) {
                self.backgroundScrolling(bgscrolling);
            }
            bgscrolling = null;
            self.menuModuleMobileStuff( false );
            if (tbLocalScript.isAnimationActive) {
                self.wowInit(null, el);
            }
            self.gallery(el);
            var slug = type === 'module' && tb_app.activeModel !== null ? tb_app.activeModel.get('mod_name') : false;
            self.addonLoad(el, slug);
            self.pageBreakPagination(el);
            Themify.domready(el,true);
            Themify.InitGallery();
            if(window['wc_single_product_params']!== undefined){
                self.initWC(el);
            }
			self.loadVideoPreview();
        },
        initWC:function(el){
            $( '.wc-tabs-wrapper, .woocommerce-tabs, #rating',el).trigger( 'init' );//for WC
            if(typeof $.fn.wc_product_gallery!=='undefined'){
                var args=window['wc_single_product_params'];
                $( '.woocommerce-product-gallery',el ).each( function() {
                        $( this ).trigger( 'wc-product-gallery-before-init', [ this, args ] )
                                .wc_product_gallery( args )
                                .trigger( 'wc-product-gallery-after-init', [ this, args ] );

                } );
            }
        },
        touchdropdown: function (el) {
            if (Themify.isTouch) {
                if (!$.fn.themifyDropdown) {
                    Themify.LoadAsync(themify_vars.url + '/js/themify.dropdown.js', function () {
                        $('.module-menu .nav', el).themifyDropdown();
                    },
                            null,
                            null,
                            function () {
                                return ('undefined' !== typeof $.fn.themifyDropdown);
                            });
                }
                else {
                    $('.module-menu .nav', el).themifyDropdown();
                }
            }
        },
        accordion: function () {
            Themify.body.off('click.tb_accordion').on('click.tb_accordion', '.accordion-title', function (e) {
                e.preventDefault();
                var $this = $(this),
                        $panel = $this.next(),
                        $item = $this.closest('li'),
                        $parent = $item.parent(),
                        type = $parent.closest('.module.module-accordion').data('behavior'),
                        def = $item.toggleClass('current').siblings().removeClass('current'); /* keep "current" classname for backward compatibility */

                if (!$parent.hasClass('tf-init-accordion')) {
                    $parent.addClass('tf-init-accordion');
                }

                if ('accordion' === type) {
                    def.find('.accordion-content').slideUp().closest('li').removeClass('builder-accordion-active').find('.accordion-title > a').attr('aria-expanded', 'false');
                }
                if ($item.hasClass('builder-accordion-active')) {
                    $panel.slideUp();
                    $item.removeClass('builder-accordion-active').find('.accordion-title > a').attr('aria-expanded', 'false');
                    $panel.attr('aria-hidden', 'true');
                } else {
                    $item.addClass('builder-accordion-active');
                    $panel.slideDown(function () {
                        if (type === 'accordion' && window.scrollY > $panel.offset().top) {
                            var fixed=tbScrollHighlight.fixedHeaderSelector != ''? $(tbScrollHighlight.fixedHeaderSelector):null;
                            Themify.scrollTo($this.offset().top,tbScrollHighlight.speed,function () {
                                if (fixed!==null && fixed.length > 0) {
                                    Themify.scrollTo(Math.ceil($this.offset().top - fixed.outerHeight(true)),300);
                                    fixed=null;
                                }
                            });
                        }
                    });
                    $item.find('.accordion-title > a').attr('aria-expanded','true');
                    $panel.attr('aria-hidden', 'false');

                    // Show map marker properly in the center when tab is opened
                    var existing_maps = $panel.hasClass('default-closed') ? $panel.find('.themify_map') : false;
                    if (existing_maps && existing_maps.length > 0) {
                        for (var i = 0,len=existing_maps.length; i <len ; ++i) { // use loop for multiple map instances in one tab
                            var current_map = $(existing_maps[i]).data('gmap_object'); // get the existing map object from saved in node
                            if (typeof current_map.already_centered !== 'undefined' && !current_map.already_centered)
                                current_map.already_centered = false;
                            if (!current_map.already_centered) { // prevent recentering
                                var currCenter = current_map.getCenter();
                                google.maps.event.trigger(current_map, 'resize');
                                current_map.setCenter(currCenter);
                                current_map.already_centered = true;
                            }
                        }
                    }
                }

                Themify.body.triggerHandler('tb_accordion_switch', [$panel]);
                if (!Themify.is_builder_active) {
                   $(window).triggerHandler( 'resize' );
                }
            });
        },
        tabs: function (el) {
            var items = $('.module.module-tab', el);
            if (el && el[0].classList.contains('module-tab')) {
                items = items.add(el);
            }
            items.each(function () {
                var tab =$(this.getElementsByClassName('tab-nav')[0]),
                    height = tab.outerHeight();
                if (height > 200) {
                    tab.siblings('.tab-content').css('min-height', height);
                }
                if (Themify.is_builder_active) {
                    this.removeAttribute('data-tabify');
                }
                $(this).tabify();
            });
        },
        tabsClick: function () {
            Themify.body.on('click touchend.themifyScroll', 'a[href*="#tab-"]', function (e) {

                if ($(this).closest('.tab-nav').length)
                    return;
                var hash = $(this.hash);
                if (hash.length && hash.closest('.module-tab').length) {
                    hash.closest('.module-tab').find('.tab-nav a[href="' + this.hash + '"]').click();
                    e.preventDefault();
                }
            });
        },
        tabsDeepLink: function () {
            var hash = decodeURIComponent(window.location.hash);
            hash = hash.replace('!/', ''); // fix conflict with section highlight
            if ('' !== hash && '#' !== hash && -1 === hash.search('/')) {
                var modules = ['accordion','tab'];
                for(var i = modules.length-1;i>=0;--i){
                    if(document.querySelector(hash + '.' + modules[i] +'-content')!==null){
                        var $module = $(hash).closest('.module-'+modules[i]);
                        if ($module.length > 0) {
                            var target = $('a[href="' + hash + '"]');
                            setTimeout(function(){
                                target.trigger('click');
                            },1);
                            Themify.scrollTo(target.offset().top - 100,1000);
                        }
                    }
                }
            }
        },
        backgroundScrolling: function (el) {
            if (!el) {
                el = $('.builder-parallax-scrolling');
            }
            el.builderParallax();
        },
        backgroundZoom: function (el) {
            var selector = '.themify_builder .builder-zoom-scrolling';
            if (!el) {
                el = $(selector);
            }
            function doZoom(e) {
                if (e !== null) {
                    el = $(selector);
                }
                if (el.length > 0) {
                    var height = window.innerHeight;
                    el.each(function () {
                        var rect = this.getBoundingClientRect();
                        if (rect.bottom >= 0 && rect.top <= height) {
                            var zoom = 140 - (rect.top + this.offsetHeight) / (height + this.offsetHeight) * 40;
                            $(this).css('background-size', zoom + '%');
                        }
                    });
                }
                else {
                    window.removeEventListener('scroll', doZoom,{passive:true,capture: true});
                }
            }
            if (el.length > 0) {
                doZoom(null);
                window.addEventListener('scroll', doZoom,{passive:true,capture: true});
            }
        },
        backgroundZooming: function (el) {
            var selector = '.themify_builder .builder-zooming';
            if (!el) {
                el = $(selector);
            }
            function isZoomingElementInViewport(item, innerHeight, clientHeight, bclientHeight) {
                var rect = item.getBoundingClientRect();
                return (
                        rect.top + item.clientHeight >= (innerHeight || clientHeight || bclientHeight) / 2 &&
                        rect.bottom - item.clientHeight <= (innerHeight || clientHeight || bclientHeight) / 3
                    );
            }

            function doZooming(e) {

                if (e !== null) {
                    el = $(selector);
                }
                if (el.length > 0) {
                    var height = window.innerHeight,
                        clientHeight = document.documentElement.clientHeight,
                        bclientHeight = document.body.clientHeight,
                        zoomingClass = 'active-zooming';

                    el.each(function () {
                        if (!this.classList.contains(zoomingClass) && isZoomingElementInViewport(this, height, clientHeight, bclientHeight)) {
                            $(this).addClass(zoomingClass);
                        }
                    });
                }
                else {
                    window.removeEventListener('scroll', doZooming,{passive:true,capture: true});
                }
            }
            if (el.length > 0) {
                doZooming(null);
                window.addEventListener('scroll', doZooming,{passive:true,capture: true});
            }
        },
        animationOnScroll: function (resync) {
            var self = ThemifyBuilderModuleJs,
                selectors = tbLocalScript.animationInviewSelectors;
            function doAnimation() {
                resync = resync || false;
                // On scrolling animation
                if ($(selectors).length > 0) {
                    if (!Themify.body[0].classList.contains('animation-running')) {
                        Themify.body[0].classList.add('animation-running');
                    }
                } else if (Themify.body[0].classList.contains('animation-running')) {
                    Themify.body[0].classList.add('animation-running');
                }

                // Core Builder Animation
                $.each(selectors, function (i, selector) {
                    $(selector).addClass('wow');
                });

                if (resync) {
                    if (self.wow) {
                        self.wow.doSync();
                    }
                    else {
                        var wow = self.wowInit();
                        if (wow) {
                            wow.doSync();
                        }
                    }
                }
            }
            Themify.body.addClass('animation-on');
            doAnimation();
        },
        setupBodyClasses: function () {
            var classes = [];
            if (this._isTouch()) {
                classes.push('builder-is-touch');
            }
            if (this._isMobile()) {
                classes.push('builder-is-mobile');
            }
            if (tbLocalScript.isParallaxActive) {
                classes.push('builder-parallax-scrolling-active');
            }
            if (!Themify.is_builder_active) {
                var builder = document.getElementsByClassName('themify_builder_content');
                for(var i=builder.length-1;i>-1;--i){
                    if (builder[i].querySelector('.module_row')!==null) {
                        classes.push('has-builder');
                        break;
                    }
                }
                builder = null;
            }
            for(var i=classes.length-1;i>-1;--i){
                Themify.body[0].classList.add(classes[i]);
            }
        },
        _isTouch: function () {
            var isTouchDevice = this._isMobile(),
                    isTouch = isTouchDevice || (('ontouchstart' in window) || (navigator.msMaxTouchPoints > 0) || (navigator.maxTouchPoints));
            return isTouch;
        },
        _isMobile: function () {
            if (this.is_mobile === null) {
                this.is_mobile = navigator.userAgent.match(/(iPhone|iPod|iPad|Android|playbook|silk|BlackBerry|BB10|Windows Phone|Tizen|Bada|webOS|IEMobile|Opera Mini)/);
            }
            return this.is_mobile;
        },
        _isTablet: function () {
            if (this.is_tablet === null) {
                this.is_tablet = navigator.userAgent.toLowerCase().match(/(ipad|tablet|(android(?!.*mobile))|(windows(?!.*phone)(.*touch))|kindle|playbook|silk|(puffin(?!.*(IP|AP|WP))))/);
            }
            return this.is_tablet;
        },
        galleryPagination: function () {
            Themify.body.on('click', '.module-gallery .pagenav a', function (e) {
                e.preventDefault();
                var $wrap = $(this).closest('.module-gallery');
                $.ajax({
                    url: this,
                    beforeSend: function () {
                        $wrap.addClass('builder_gallery_load');
                    },
                    complete: function () {
                        $wrap.removeClass('builder_gallery_load');
                    },
                    success: function (data) {
                        if (data) {
                            var id = $wrap[0].className.match( /tb_?.[^\s]+/ );
                            if ( null !== id && 'undefined' !== typeof id[0] ) {
                                $wrap.html($(data).find('.'+id[0]).first().html());
                            }else{
                                $wrap.html($(data).find('.module-gallery').first().html());
                            }
                            if($wrap.hasClass('masonry-done')){
                                $wrap.removeClass('masonry-done').addClass('masonry');
                            }
                            ThemifyBuilderModuleJs.gallery($wrap);
                        }
                    }
                });
            });
        },
        pageBreakPagination: function (el) {
            var $pagination = $('.post-pagination',el);
            if(el && el[0].classList.contains('post-pagination')){
                $pagination = $pagination.add(el);
            }
            if ($pagination.length === 0) {
                return;
            }
            $pagination.addClass('pagenav clearfix').contents().filter(function () {
                if (this.nodeValue !== null && this.nodeValue.replace(/ /g, '') !== '')
                    return this.nodeType !== 1;
            }).wrap("<span class='current number'></span>");
            $pagination.find('a').addClass('number');
            $pagination.css('display', 'block').find('strong').remove();
        },
        gallery: function (el) {
            var gallery = $('.module-gallery', el),
                    self = this,
                    masonry = [];
            if (el && el[0].classList.contains('module-gallery')) {
                gallery = gallery.add(el);
            }
            gallery.each(function () {
                if (this.classList.contains('gallery-masonry')) {
                    masonry.push(this);
                }
                else if (this.classList.contains('layout-slider')) {
                    var slides = $('.themify_builder_slider', this);
                    if (slides.length === 2) {
                        var items = slides.eq(1).children('li');
                        items.each(function () {
                            $(this).off('click.gallerySlider').on('click.gallerySlider', function (e) {
                                e.preventDefault();
                                $(this).closest('.themify_builder_slider').eq(0).trigger('slideTo', $(this).index());
                            });
                        });
                        items = null;
                    }
                }
                else if (this.classList.contains('layout-showcase')) {
                    self.showcaseGalleryCallback($(this).find('a').first());
                }
            });
            if (masonry.length > 0) {
                Themify.isoTop(masonry,{itemSelector: '.gallery-item',stamp: '.module-title','masonry':{gutter: '.module-gallery-gutter','columnWidth':false}});
            }
        },
        showcaseGallery: function () {
            var self = this;
            Themify.body.on('click', '.module-gallery.layout-showcase a', function (e) {
                e.preventDefault();
                self.showcaseGalleryCallback($(this));
            });
        },
        showcaseGalleryCallback: function (el) {
            var showcaseContainer = el.closest('.gallery').find('.gallery-showcase-image'),
                    titleBox = showcaseContainer.find('.gallery-showcase-title');
            titleBox.css({opacity: '', visibility: ''});
            showcaseContainer.addClass('builder_gallery_load').find('img').prop('src', el.data('image'));
            showcaseContainer.find('.gallery-showcase-title-text').html(el.prop('title'));
            showcaseContainer.find('.gallery-showcase-caption').html(el.data('caption'));
            !$.trim(titleBox.text()) && titleBox.css({opacity: 0, visibility: 'hidden'});

            showcaseContainer.find('img').one('load', function () {
                showcaseContainer.removeClass('builder_gallery_load');
            });
        },
        menuModuleMobileStuff: function (is_resize, el) {
            var menuModules = $('.module.module-menu', el);

            if (menuModules.length > 0) {
                var windowWidth = window.innerWidth,
                        closeMenu = function () {
                            var mobileMenu = $('.mobile-menu-module');
                            mobileMenu.prop('class', 'mobile-menu-module');
                            Themify.body.removeClass('menu-module-left menu-module-right').find('.module-menu.'+mobileMenu.attr('data-module')+' .body-overlay').removeClass('body-overlay-on');
                            setTimeout(function () {
                                if (Themify.body.hasClass('close-left-menu') || Themify.body.hasClass('close-right-menu')) {
                                    Themify.body.removeClass('close-left-menu close-right-menu');
                                    mobileMenu.empty();
                                }
                            }, 300);
                        };
                if ($('.mobile-menu-module').length === 0) {
                    Themify.body[0].insertAdjacentHTML('beforeend', '<div class="themify_builder themify_builder_content-'+Themify.body[0].getElementsByClassName('themify_builder_content')[0].dataset.postid+'"><div class="mobile-menu-module"></div></div>');
                }

                menuModules.each(function () {
                    var $this = $(this),
                            breakpoint = $this.data('menu-breakpoint');
					if (breakpoint) {
                        var menuContainer = $this.find('div[class*="-container"]'),
                                menuBurger = $this.find('.menu-module-burger');

						if (menuBurger.length === 0) {
							menuBurger = $( '<a class="menu-module-burger"><span class="menu-module-burger-inner"></span></a>' ).prependTo( $this );
						}

                        if (windowWidth >= breakpoint) {
                            menuContainer.show();
                            menuBurger.hide();
                        } else {
                            menuContainer.hide();
                            menuBurger.css('display', 'block');
                            $this.addClass('module-menu-mobile-active');
                        }

                        if (!is_resize) {
                            if ($this.next('style').length > 0) {
                                var styleContent = $this.next('style').html().replace(/\.[^{]+/g, function (match) {
                                    return match + ', .mobile-menu-module' + match.replace(/\.themify_builder\s|\.module-menu/g, '');
                                });

                                $this.next('style').html(styleContent);
                            }
							
                        }
                    }
                });

                if (!is_resize && !Themify.is_builder_active) {
                    var menuBurger = $('.menu-module-burger'),
                        breakpoint = menuBurger.parent().data('menu-breakpoint'),
                        style = menuBurger.parent().data('menu-style');
                    if (menuBurger.length && windowWidth < breakpoint && style === 'mobile-menu-dropdown') {
                        var menuContainer = $('.module-menu-container');
                        Themify.body.on('click', function (e) {
                            var $target = $(e.target);
                            if (!$target.closest('.module-menu-container').length && menuContainer.is(':visible') && !$target.closest('.menu-module-burger').length && menuBurger.is(':visible')) {
                                menuBurger.removeClass('is-open');
                                menuContainer.removeClass('is-open');
                            }
                        });
                    }

                    Themify.body.on('click', '.menu-module-burger', function (e) {
                        e.preventDefault();

                        var $self = $(this),
                            $parent = $self.parent(),
                            elStyle = $parent.data('menu-style');
                        if (elStyle === 'mobile-menu-dropdown') {
                            $self.siblings('.module-menu-container').toggleClass('is-open');
                            $self.toggleClass('is-open');
                            return;
                        }

                        var menuDirection = $parent.data('menu-direction'),
                            gs = $parent.data('gs'),
                            elID = $parent.data('element-id'),
                            menuContent = $parent.find('div[class*="-container"] > ul').clone(),
                            menuUI = menuContent.prop('class').replace(/nav|menu-bar|fullwidth|vertical|with-sub-arrow/g, ''),
                            customStyle = $parent.prop('class').match(/menu-[\d\-]+/g);

                        gs = undefined === gs ? '' : ' ' + gs;
                        customStyle = customStyle ? customStyle[0] : '';
                        menuContent = menuContent.removeAttr('id').removeAttr('class').addClass('nav');
                        if (menuContent.find('ul').length) {
                            menuContent.find('ul').prev('a').append('<i class="toggle-menu "></i>');
                        }
                        Themify.body.addClass('menu-module-' + menuDirection);

                        $('.mobile-menu-module').addClass(menuDirection + ' ' + menuUI + ' ' + customStyle + ' ' + elID + ' ' + elStyle + gs + ' module-menu')
                            .attr('data-module',elID)
                            .html(menuContent)
                                .prepend('<a class="menu-close"><span class="menu-close-inner"></span></a>');

                        $parent.find('.body-overlay').addClass('body-overlay-on');

                    })
                            .on('click', '.mobile-menu-module ul .toggle-menu', function (e) {

                                var $linkIcon = $(this),
                                        $this = $linkIcon.closest('a');
                                e.preventDefault();
                                $this.next('ul').toggle();
                                if (!$linkIcon.hasClass('menu-close')) {
                                    $linkIcon.addClass('menu-close');
                                } else {
                                    $linkIcon.removeClass('menu-close');
                                }

                            }).on('click', '.mobile-menu-module ul a[href="#"]', function (e) {
                        e.preventDefault();
                    })
                            .on('click', '.module-menu .body-overlay,.mobile-menu-module .menu-close,.mobile-menu-module .menu-item a', function ( e ) {
								if ( $( e.target ).hasClass( 'toggle-menu' ) ) {
									return;
								}

                                var closeClass = 'close-';
                                closeClass += $('.mobile-menu-module').hasClass('right') ? 'right' : 'left';
                                closeClass += '-menu';

                                Themify.body.addClass(closeClass);

                                closeMenu();
                            }).on( 'scrollhighlightstart.themify', function () {
                        if(Themify.body.find('.mobile-menu-slide,.mobile-menu-overlay').length){
                            closeMenu();
                        }
                    } );
                } else {
                    closeMenu();
                }
            }
        },
        GridBreakPoint: function () {
            var tablet_landscape = tbLocalScript.breakpoints.tablet_landscape,
                    tablet = tbLocalScript.breakpoints.tablet,
                    mobile = tbLocalScript.breakpoints.mobile,
                    rows = document.querySelectorAll('.row_inner,.subrow_inner'),
                    prev = false;

            function Breakpoints() {
                var width = $(window).width(),
                        type = 'desktop';

                if (width <= mobile) {
                    type = 'mobile';
                } else if (width <= tablet[1]) {
                    type = 'tablet';
                } else if (width <= tablet_landscape[1]) {
                    type = 'tablet_landscape';
                }

                if (type !== prev) {
                    var is_desktop = type === 'desktop',
                            set_custom_width = is_desktop || prev === 'desktop';

                    if (is_desktop) {
                        Themify.body[0].classList.remove('tb_responsive_mode');
                    } else {
                        Themify.body[0].classList.add('tb_responsive_mode');
                    }

                    for (var i =rows.length-1; i > -1; --i) {
                        var columns = rows[i].children,
                                grid = rows[i].getAttribute('data-col_' + type),
                                first = columns[0],
                                last = columns[columns.length - 1],
                                base = rows[i].getAttribute('data-basecol');

                        if (set_custom_width) {
                            for (var j =columns.length-1; j > -1; --j) {
                                var w = columns[j].getAttribute('data-w');
                                if (w) {
                                    if (is_desktop) {
                                        columns[j].style['width'] = w + '%';
                                    } else {
                                        columns[j].style['width'] = '';
                                    }
                                }
                            }
                        }
                        var dir = rows[i].getAttribute('data-'+type + '_dir');

                        if (first && last) {
                            if (dir === 'rtl') {
                                first.classList.remove('first');
                                first.classList.add('last');
                                last.classList.remove('last');
                                last.classList.add('first');
                                rows[i].classList.add('direction-rtl');
                            } else {
                                first.classList.remove('last');
                                first.classList.add('first');
                                last.classList.remove('first');
                                last.classList.add('last');
                                rows[i].classList.remove('direction-rtl');
                            }
                        }

                        if (base && !is_desktop) {
                            if (prev !== false && prev !== 'desktop') {
                                rows[i].classList.remove('tb_3col');
                                var prev_class = rows[i].getAttribute('data-col_' + prev);

                                if (prev_class) {
                                    rows[i].classList.remove($.trim(prev_class.replace('tb_3col', '').replace('mobile', 'column').replace('tablet', 'column')));
                                }
                            }

                            if (!grid || grid === '-auto'|| grid===type+'-auto') {
                                rows[i].classList.remove('tb_grid_classes');
                                rows[i].classList.remove('col-count-' + base);
                            } else {
                                var cl = rows[i].getAttribute('data-col_' + type);

                                if (cl) {
                                    rows[i].classList.add('tb_grid_classes');
                                    rows[i].classList.add('col-count-' + base);
                                    cl = cl.split(' ');
                                    for(var j=cl.length-1;j>-1;--j){
                                        rows[i].classList.add($.trim(cl[j].replace('mobile', 'column').replace('tablet', 'column')));
                                    }
                                }
                            }
                        }
                    }
                    prev = type;
                }
            }

            Breakpoints();
            $(window).on('tfsmartresize.themify_grid', function (e) {
                if (!e.isTrigger) {
                    Breakpoints();
                }
            });
        },
        readMoreLink: function () {
            Themify.body.on('click', '.module-text-more', function (e) {
                e.preventDefault();
                var more_link = $(this),
					more_text = more_link.parent().find( '.more-text' ),
                    callback = function() {
						// trigger resize so the module can re-adjust heights
						$( this ).closest( '.module' ).trigger( 'resize' );
                    };
                if ( more_link.hasClass( 'tb-text-more-link' ) ) {
                    more_link.removeClass( 'tb-text-more-link' ).addClass( 'tb-text-less-link' );
					more_text.slideDown( 400, 'linear', callback );
                } else {
                    more_link.removeClass( 'tb-text-less-link' ).addClass( 'tb-text-more-link' );
                    more_text.slideUp( 400, 'linear', callback );
                }
            });
        },
        stickyElementInit: function () {
            if (!tbLocalScript.isStickyScrollActive)
                return true;

            var stickyElementRun = function () {

                var body = document.body,
                        html = document.documentElement,
                        documentHeight = Math.max(body.scrollHeight, body.offsetHeight,
                                html.clientHeight, html.scrollHeight, html.offsetHeight),
                        wH = window.innerHeight || html.clientHeight || body.clientHeight;

                $('[data-sticky-active]').each(function () {
                    var $this = $(this),
                            opts = $this.data('sticky-active'),
                            stickVal = opts.stick.value ? parseInt(opts.stick.value) : 0,
                            topSpacing = 'px' === opts.stick.val_unit ? stickVal : ((stickVal / 100) * wH),
                            stickArgs = {topSpacing: topSpacing, zIndex: null, className: 'tb_sticky_scroll_active'};

                    if ('bottom' === opts.stick.position) {
                        stickArgs.topSpacing = wH - this.offsetHeight - topSpacing;
                    }

                    if (opts.unstick) {
                        if ('builder_end' === opts.unstick.el_type) {
                            var $builder = $this.closest('.themify_builder_content'),
                                tmp = $builder.closest('#tbp_header');
                                if(tmp.length!==0){
                                        tmp = document.getElementById('tbp_content');
                                        tmp = tmp!==null?tmp.getElementsByClassName('themify_builder_content')[0]:body.getElementsByClassName('themify_builder_content')[1];
                                        if(tmp!==undefined){
                                                $builder = $(tmp);
                                        }
                                }
                                var stopAt = $builder.offset().top + $builder.outerHeight(true);
						
                            stickArgs.bottomSpacing = documentHeight - stopAt;
                        } else {
                            var targetEl = 'row' === opts.unstick.el_type ? opts.unstick.el_row_target : opts.unstick.el_mod_target,
                                $target = $('.tb_'+targetEl).first(),
                                unstickVal = opts.unstick.value ? parseInt(opts.unstick.value) : 0,
                                targetTop;

                            if ('%' === opts.unstick.val_unit) {
                                unstickVal = ((unstickVal / 100) * wH);
                            }

                            if ($target.length) {
                                targetTop = documentHeight - ($target.offset().top + this.offsetHeight + topSpacing);

                                if ('bottom' === opts.unstick.current) {
                                    if ('hits' === opts.unstick.rule) {
                                        targetTop += wH - unstickVal;
                                    } else {
                                        targetTop += wH - ($target.outerHeight(true) + unstickVal);
                                    }
                                } else if ('this' === opts.unstick.current) {
                                    targetTop = documentHeight - $target.offset().top;

                                    if ('passes' === opts.unstick.rule) {
                                        targetTop -= this.offsetHeight;
                                    }
                                } else {
                                    if ('hits' === opts.unstick.rule) {
                                        targetTop += unstickVal;
                                    } else {
                                        targetTop -= unstickVal;
                                    }
                                }
                                stickArgs.bottomSpacing = targetTop;
                            }
                        }
                    }
                    $this.sticky(stickArgs);
                    $this.sticky('update');
                });
            };
            stickyElementRun();
            $(window).on('tfsmartresize.sticky', function () {
                $('[data-sticky-active]').each(function () {
                    $(this).unstick();
                });
                stickyElementRun();
            });
        },
        alertModule: function (el) {
            var alertBox = $('.module.module-alert', el);
            if (el && el[0].classList.contains('module-alert')) {
                alertBox = alertBox.add(el);
            }
            var isNumber = function (number) {
                return number && !isNaN(parseFloat(number)) && isFinite(number);
            },
                    setCookie = function (name, value, days) {
                        var date = new Date();
                        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));

                        document.cookie = name + "=" + value + ";expires=" + date.toUTCString() + ";path=/";
                    },
                    getCookie = function (name) {
                        name = name + '=';
                        var ca = document.cookie.split(';');

                        for (var i = 0, len = ca.length; i < len; ++i) {
                            var c = ca[i];
                            while (c.charAt(0) === ' ') {
                                c = c.substring(1);
                            }
                            if (c.indexOf(name) === 0) {
                                return c.substring(name.length, c.length);
                            }
                        }
                        return '';
                    },
                    closeAlert = function ($button) {

                        var buttonMessage, alertBox,
                                speed = 400;

                        if ($button) {
                            buttonMessage = $button.data('alert-message');
                            alertBox = buttonMessage ? $button.closest('.alert-inner') : $button.closest('.module-alert');
                        } else {
                            alertBox = $(this);
                        }

                        alertBox.slideUp(speed, function () {
                            if (buttonMessage && !alertBox.parent().find('.alert-message').length) {
                                var message = $('<div class="alert-message" />').html(buttonMessage + '<div class="alert-close ti-close" />');

                                alertBox.parent().html(message);
                                message.hide().slideDown(speed);
                            }
                        });
                    };

            alertBox.each(function () {
                var $this = $(this),
                        currentViews = 0,
                        currentLimit = 0,
                        alertID = $this.data('module-id'),
                        alertLimit = $this.data('alert-limit'),
                        autoClose = $this.data('auto-close');

                if (isNumber(alertLimit)) {
                    var cookies = getCookie(alertID);

                    if (cookies) {
                        cookies = cookies.split('|');

                        if (cookies[1]) {
                            currentLimit = +cookies[0];
                            currentViews = +cookies[1];
                        }
                    }

                    if (alertLimit !== currentLimit) {
                        setCookie(alertID, alertLimit + '|1', 365);
                    } else if (alertLimit > currentViews) {
                        ++currentViews;
                        setCookie(alertID, alertLimit + '|' + currentViews, 365);
                    }
                }

                if (isNumber(autoClose)) {
                    setTimeout(closeAlert.bind(this), autoClose * 1000);
                }
            });
            Themify.body.on('click', '.module-alert .alert-close', function (e) {
                e.preventDefault();
                closeAlert($(this));
            });
        },
        loginModuleInit: function () {
            Themify.body.on('click', '.module-login .tb_login_links a', function (e) {
                e.preventDefault();
                $(this).closest('.module').find('.tb_lostpassword_username input').val($(this).closest('.module').find('.tb_login_username input').val());
                $(this).closest('form').slideUp().siblings().slideDown();
            });
        },
        testimonial: function(){
            Themify.isoTop('.themify_builder_testimonial.masonry.loops-wrapper');
        },
		/**
		 *
		 */
        videoLoad:function( url, autoplay, mute) {
			var attr = Themify.parseVideo( url ),
				iframe;
			if ( attr.type === 'youtube' || attr.type=== 'vimeo' ) {
				iframe = document.createElement( 'iframe' );
				if ( attr.type === 'youtube' ) {
					iframe.src = 'https://www.youtube.com/embed/' + attr.id + '?autohide=1&border=0&wmode=opaque&enablejsapi=1';
                    var n = url.match(/list=(.+?)(?=&|$)/i);
                    if(null!==n && n[0]!=undefined){
                        iframe.src+='&'+n[0];
                    }
				} else {
					iframe.src='//player.vimeo.com/video/' + attr.id + '?api=1&portrait=0&title=0&badge=0';
				}
				if ( mute ) {
					iframe.src += '&mute=1';
				}
				if ( autoplay ) {
					iframe.src += '&autoplay=1';
				}
				iframe.setAttribute( 'allowfullscreen', '' );
				iframe.setAttribute( 'frameborder', '0' );
			} else {
				iframe = document.createElement( 'video' );
				iframe.src = url;
				iframe.autoplay = autoplay;
				iframe.controls =true;
				if ( mute ) {
					iframe.mute = true;
				}
			}
			return iframe;
		},
		/*
		 * In Video module, for videos that don't have a cover image, load the video itself
		 */
		loadVideoPreview : function () {
			Themify.body.find( '.module-video' ).each( function() {
				var $this = $( this );
				if ( ! ( $this.hasClass( 'tb_has_cover' ) && ! $this.hasClass( 'video-autoplay' ) ) ) {
					var $overlay = $this.find( '.tb_video_overlay' );
					var video = ThemifyBuilderModuleJs.videoLoad( $overlay.data( 'href' ), $overlay.data( 'autoplay' ), $overlay.data( 'muted' ) );
					$this.find( '.video-wrap' ).append( video );
					if ( $overlay.data( 'autoplay' ) ) {
						$overlay.remove();
					}
				}
			} );
		},
        videoPlay:function(){
			ThemifyBuilderModuleJs.loadVideoPreview();
            Themify.body.on('click','.tb_video_overlay',function(e){
                e.preventDefault();
                e.stopPropagation();
				var p = this.parentNode;
                var url = this.getAttribute( 'data-href' );
				var video = ThemifyBuilderModuleJs.videoLoad( url, true, this.hasAttribute( 'data-muted' ) );
				p.innerHTML = '';
				p.appendChild( video );
            });
        },
        optinModuleInit : function() {
                Themify.body.on( 'submit', '.tb_optin_form', function( e ) {
                        e.preventDefault();
                        var $this = $( this );
                        if ( $this.hasClass( 'processing' ) ) {
                                return;
                        }
                        $this.addClass( 'processing' );
                        $.ajax( {
                                url : $this.attr( 'action' ),
                                type : 'POST',
                                data : $this.serialize(),
                                success : function( resp ) {
                                        if ( resp.success ) {
                                                if ( $this.data( 'success' ) === 's1' ) {
                                                        window.location.href = resp.data.redirect;
                                                } else {
                                                        $this.fadeOut().closest( '.module' ).find( '.tb_optin_success_message' ).fadeIn();
                                                }
                                        } else {
                                                window.console && console.log( resp.data.error );
                                        }
                                },
                                complete : function() {
                                        $this.removeClass( 'processing' );
                                }
                        } );
                } );
        },
        signupModuleInit: function () {
            if ( document.querySelector( '.tb_signup_form' )===null) {
                return;
            }
            Themify.body.off( 'submit.tb_signup').on( 'submit.tb_signup', '.tb_signup_form', function ( e ) {
                e.preventDefault();
                var $this = $( this ),
                    $btn = $this.find('button');
                $btn.prop('disabled', true);
                $.ajax( {
                    type: 'POST',
                    url: tbLocalScript.ajaxurl,
                    data: {
                        dataType : 'json',
                        action : 'tb_signup_process',
                        nonce : $this.find('input[name="nonce"]').val(),
                        data: $this.serialize()
                    },
                    beforeSend:function(){
                        $this.find('.tb_signup_errors span').remove();
                        $this.find('.tb_signup_errors').removeClass('tb_signup_errors');
                        $this.find('.tb_signup_success').hide();
                    },
                    success: function ( resp ) {
                        if ( undefined !== resp.err ) {
                            var errWrapper = $this[0].getElementsByClassName('tb_signup_messages')[0];
                                errWrapper.className += ' tb_signup_errors';
                            for(var i = resp.err.length-1;i>-1;--i){
                                var err = document.createElement('span');
                                err.innerText = resp.err[i];
                                errWrapper.appendChild(err);
                            }
                        } else {
                            $this.find('.tb_signup_success').fadeIn();
                            var redirect = $this.find('input[name="redirect"]');
                            if(redirect.length>0){
                                var url = redirect.val();
                                if(''!== url){
                                    window.location.href = redirect.val();
                                }else{
                                    window.location.reload(true);
                                }
                            }else{
                                $this[0].reset();
                            }
                        }
                        Themify.scrollTo($this.offset().top-100);
                    },
                    complete: function () {
                        $btn.prop('disabled', false);
                    }
                } );
            } );
        },
        socialShareModule: function () {
            Themify.body.off('click.tb_share').on('click.tb_share', '.module-social-share a,.module-social-share a i', function (e) {
                e.preventDefault();
                var $this = $(this),
                    $el = $this.closest('.module-social-share');
                if($el.length>0){
                    var url = $el.attr('data-url'),
                        type = 'A' === $this[0].tagName ? $this[0].dataset.type : $this[0].parentNode.dataset.type;
                    url = '' !== url ? url : window.location.href;
                    Themify.sharer(type,url,$this.attr('data-title'));
                }
            });
        }
    };

    // Initialize
    ThemifyBuilderModuleJs.init();

}(jQuery, window,Themify, document,undefined));
