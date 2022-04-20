// IE11 Polyfill for object.forEach
if ( window.NodeList && ! NodeList.prototype.forEach ) {
    NodeList.prototype.forEach = Array.prototype.forEach;
}

// tfsmartresize helper
var Themify;
!function (e) {
    'use strict';
    var t, i, n = e.event;
    t = n.special.tfsmartresize = {
        setup: function () {
            e(this).on('resize', t.handler);
        }, teardown: function () {
            e(this).off('resize', t.handler);
        }, handler: function (e, r) {
			// fix Android bug where activating a text input triggers window.resize event
			if ( Themify.inputFocus ) {
				return;
			}
            var s = this,
                    a = arguments,
                    w = this.innerWidth,
                    h = this.innerHeight,
                    o = function () {
                        e.w = w;
                        e.h = h;
                        e.type = 'tfsmartresize', n.dispatch.apply(s, a);
                        Themify.w = w;
                        Themify.h = h;
                    };
            i && clearTimeout(i), r ? o() : i = setTimeout(o, t.threshold);
        }, threshold: 150};
}(jQuery);
(function ($, window, document, undefined) {
    'use strict';
    window.addEventListener('load', function () {
        window.loaded = true;
        if (!Themify.is_builder_active) {
            Themify.triggerEvent(window, 'resize');
        }
        Themify.body[0].classList.add('page-loaded');

		Themify.body.on( 'focus', 'input', function() {
			Themify.inputFocus = true;
		} ).on( 'blur', 'input', function() {
			Themify.inputFocus = false;
		} );
	}, {once: true, passive: true});
    var CustomEvent,
            mainCssLoaded = null;
    if (typeof window.CustomEvent !== 'function') {
        CustomEvent = function (event, params) {
            var evt = document.createEvent('CustomEvent'),
                    detail = params !== undefined ? params.detail : undefined;
            evt.initCustomEvent(event, false, false, detail);
            return evt;
        };
        CustomEvent.prototype = window.Event.prototype;

        window.CustomEvent = CustomEvent;
    }
    Themify = {
        fonts: [],
        loadedFonts:{},
        cssLazy: [],
        jsLazy: [],
        body: null,
        is_builder_active: false,
        is_builder_loaded: false,
        w: null,
        h: null,
        isTouch: false,
        isRTL: false,
        isoTopItems: [],
        isoTopSelectors:{},
        backstretchItems: [],
        isLoaded: null,
		inputFocus : false, // flag whether any <input> has focus
        triggerEvent: function (target, type, params) {
            var ev;
            try {
                ev = new window.CustomEvent(type, {detail: params});
            }
            catch (e) {
                ev = window.CustomEvent(type, {detail: params});
            }
            target.dispatchEvent(ev);
        },
        UpdateQueryString: function (a, b, c) {
            c || (c = window.location.href);
            var d = RegExp('([?|&])' + a + '=.*?(&|#|$)(.*)', 'gi');
            if (d.test(c))
                return b !== void 0 && null !== b ? c.replace(d, '$1' + a + '=' + b + '$2$3') : c.replace(d, '$1$3').replace(/(&|\?)$/, '');
            if (b !== void 0 && null !== b) {
                var e = -1 !== c.indexOf('?') ? '&' : '?', f = c.split('#');
                return c = f[0] + e + a + '=' + b, f[1] && (c += '#' + f[1]), c
            }
            ;
            return c;
        },
        Init: function () {
            this.body = $('body');//cache body, main.js is loading in the footer
            this.isRTL = this.body[0].classList.contains('rtl');
            this.isTouch = this.body[0].classList.contains('touch');
            this.w = window.innerWidth;
            this.h = window.innerHeight;
            if (window['themify_vars']!== undefined) {
                if (window['tbLocalScript']!== undefined && window['tbLocalScript'] !== null) {
                    var self = this;
                    this.is_builder_active = this.body[0].classList.contains('themify_builder_active');
                    if (this.is_builder_active===true) {
                        window.top.Themify.is_builder_active = true;
                    }
                    var loadBuilder = function (e, el) {
                        if (document.querySelector('.themify_builder_content div:not(.js-turn-on-builder)') !== null) {
                            if (!self.is_builder_active) {
                                if (self.isLoaded === null) {
                                    var st = document.getElementById('builder-styles-css');
                                    if (st !== null && document.getElementById('themify-builder-style') === null) {
                                        var link = document.createElement("link");
                                        link.id = 'themify-builder-style';
                                        link.rel = 'stylesheet';
                                        link.type = 'text/css';
                                        link.href = tbLocalScript.builder_url + '/css/themify-builder-style.css?ver=' + themify_vars.version;
                                        st.insertAdjacentElement('beforebegin', link);
                                        st = null;
                                    }
                                }
                                if (el) {
                                    st = el[0].getElementsByClassName('tb_style_generated');
                                    for (var i = st.length - 1; i > -1; --i) {
                                        self.LoadCss(st[i].getAttribute('data-url'), false);
                                        st[i].parentNode.removeChild(st[i]);
                                    }
                                    if (self.isLoaded === true) {
                                        $(window).triggerHandler('resize');
                                    }
                                }
                            }
                            if (self.isLoaded === null) {

                                self.LoadAsync(tbLocalScript.builder_url + '/js/themify.builder.script.js', function () {
                                    if (el) {
                                        $(window).triggerHandler('resize');
                                    }
                                    self.isLoaded = true;

                                }, null, null, function () {
                                    return window['ThemifyBuilderModuleJs'] !== undefined;
                                });
                            }
                            return true;
                        }
                        return false;
                    };
                    $(function() {
                        tbLocalScript.isTouch = self.isTouch;
                        if (!self.is_builder_active) {
                            if (loadBuilder() === false) {
                                self.body.on('infiniteloaded.themify', loadBuilder);
                            }
                        }
                    });
                }
                this.bindEvents();
            }
        },
        bindEvents: function () {
            var self = this;
            if (window.loaded) {
                self.domready();
                self.windowload();
            }
            else {
                $(window).one('load', self.windowload);
                $(function() {
                    self.domready();
                    if(self.is_builder_active===true){
                        self.body.on('builder_load_module_partial.tf_masonry', self.liveIsoTop);
                    }
                });
            }
        },
        domready: function (el,force) {
            if(!el){
                setTimeout(this.LazyLoad, 10);
            }
            if(this.is_builder_active===false || force===true){
                this.InitCarousel(el);
                this.InitMap(el);
                var autoTilesWrap = el;
                if(!autoTilesWrap){
                    autoTilesWrap=document;
                }
                else{
                    autoTilesWrap=autoTilesWrap[0];
                    if(autoTilesWrap.classList.contains('auto_tiles')){
                        this.autoTiles(autoTilesWrap);
                    }
                }
                this.autoTiles(autoTilesWrap.querySelectorAll('.auto_tiles.loops-wrapper'));
            }
        },
        windowload: function () {
            var items = document.getElementsByClassName('shortcode');
            for (var i = items.length - 1; i > -1; --i) {
                if (items[i].classList.contains('slider') || items[i].classList.contains('post-slider')) {
                    items[i].style['height'] = 'auto';
                    items[i].style['visibility'] = 'visible';
                }
            }
            items = document.getElementsByClassName('slideshow-wrap');
            for (i = items.length - 1; i > -1; --i) {
                items[i].style['height'] = 'auto';
                items[i].style['visibility'] = 'visible';
            }
            if (!Themify.is_builder_active) {
                Themify.InitGallery();
                Themify.parallaxScrollingInit();
            }
        },
        LazyLoad: function () {
            var self = Themify;
                if(self.loadedFonts['awesome']===undefined){
                    var is_fontawesome = self.is_builder_active || document.querySelector('.fa,.fas,.fab,.far') !== null;
                    if (is_fontawesome === false) {
                        is_fontawesome = self.checkFont('FontAwesome');
                    }
                    if (is_fontawesome === true) {
                        self.loadedFonts['awesome']=true;
                        self.LoadCss(themify_vars.url + '/fontawesome/css/font-awesome.min.css');
                    }
                }
                if(self.loadedFonts['tf_icons']===undefined){
                    var is_themify_icons = self.is_builder_active || document.querySelector('.shortcode,.module-menu[data-menu-breakpoint],.section_spinner[class*="ti-"]') !== null;
                    if (is_themify_icons === false) {
                        is_themify_icons = document.querySelector('span[class*="ti-"]') !== null;
                        if (is_themify_icons === false) {
                            is_themify_icons = document.querySelector('i[class*="ti-"]') !== null;
                            if (is_themify_icons === false) {
                                is_themify_icons = self.checkFont('Themify');
                            }
                        }
                    }
                    if (is_themify_icons === true) {
                        self.loadedFonts['tf_icons']=true;
                        self.LoadCss(themify_vars.url + '/themify-icons/themify-icons.css');
                    }
                }
                if(self.loadedFonts['fontello']===undefined){
                    if (document.querySelector('[class*="tf_fontello-"]') !== null) {
                        self.loadedFonts['fontello']=true;
                        if(typeof themify_vars.fontello_path === 'string'){
                            self.LoadCss(themify_vars.fontello_path);
                        }
                    }
                }
            if(self.loadedFonts['framework-css']===undefined){
                if (self.is_builder_active || document.getElementsByClassName('shortcode')[0] !== undefined) {
                    var el = document.getElementById('themify-framework-css');
                    if (el !== null) {
                        self.loadedFonts['framework-css']=true;
                        self.LoadCss(el.getAttribute('data-href'), false, el);
                    }
                }
            }
            if (themify_vars.commentUrl !== '') {
                self.loadComments();
            }
            self = null;
        },
        loadComments: function (callback) {
            if (window['addComment'] === undefined && document.getElementById('cancel-comment-reply-link') !== null) {
                this.LoadAsync(themify_vars.commentUrl, callback, themify_vars.wp, null, null, function () {
                    return window['addComment'] !== undefined;
                });
            }
        },
        InitCarousel: function (el) {

            var sliders = $('.slides[data-slider]', el),
                    self = this,
                    carouselCalback = function () {
                        sliders.each(function () {
                            if ($(this).closest('.carousel-ready').length > 0) {
                                return true;
                            }
                            $(this).find('> br, > p').remove();
                            var $this = $(this),
                                    data = JSON.parse(atob($(this).data('slider'))),
                                    height = typeof data.height === 'undefined' ? 'auto' : data.height,
                                    slideContainer = undefined !== data.custom_numsldr ? '#' + data.custom_numsldr : '#slider-' + data.numsldr,
                                    speed = data.speed >= 1000 ? data.speed : 1000 * data.speed,
                                    args = {
                                        responsive: true,
                                        swipe: true,
                                        circular: data.wrapvar,
                                        infinite: data.wrapvar,
                                        auto: {
                                            play: data.auto != 0,
                                            timeoutDuration: data.auto >= 1000 ? data.auto : 1000 * data.auto,
                                            duration: speed,
                                            pauseOnHover: data.pause_hover
                                        },
                                        scroll: {
                                            items: parseInt(data.scroll),
                                            duration: speed,
                                            fx: data.effect
                                        },
                                        items: {
                                            visible: {
                                                min: 1,
                                                max: parseInt(data.visible)
                                            },
                                            width: 120,
                                            height: height
                                        },
                                        onCreate: function () {
                                            $this.closest('.caroufredsel_wrapper').outerHeight($this.outerHeight(true));
                                            $(slideContainer).css({'visibility': 'visible', 'height': 'auto'});
                                            $this.closest('.carousel-wrap').addClass('carousel-ready');
											Themify.reLayoutIsoTop();
                                        }
                                    };

                            if (data.slider_nav) {
                                args.prev = slideContainer + ' .carousel-prev';
                                args.next = slideContainer + ' .carousel-next';
                            }
                            if (data.pager) {
                                args.pagination = slideContainer + ' .carousel-pager';
                            }
                            self.imagesLoad($this, function () {
                                $this.carouFredSel(args);
                            });
                        });

                        $(window).off('tfsmartresize.tfcarousel').on('tfsmartresize.tfcarousel', function () {
                            sliders.each(function () {
                                var heights = [],
                                        newHeight,
                                        $self = $(this);
                                $self.find('li').each(function () {
                                    heights.push($(this).outerHeight(true));
                                });
                                newHeight = Math.max.apply(Math, heights);
                                $self.outerHeight(newHeight).parent().outerHeight(newHeight);
                            });
                        });
                        self = null;
                    };
            if (sliders.length > 0) {
                this.imagesLoad(function () {
                    if ('undefined' === typeof $.fn.carouFredSel) {
                        self.LoadAsync(themify_vars.url + '/js/carousel.min.js', carouselCalback, null, null, function () {
                            return ('undefined' !== typeof $.fn.carouFredSel);
                        });
                    }
                    else {
                        carouselCalback();
                    }
                });
            }
        },
        InitMap: function (el) {
            var self = this;
            if ($('.themify_map', el).length > 0) {
                setTimeout(function () {
                    if (typeof google !== 'object' || typeof google.maps !== 'object' || themify_vars.isCached === 'enable') {
                        if (themify_vars.isCached === 'enable') {
                            google.maps = {
                                __gjsload__: function () {
                                    return;
                                }
                            };
                        } else if (!themify_vars.map_key) {
                            themify_vars.map_key = '';
                        }
                        self.LoadAsync('//maps.googleapis.com/maps/api/js', self.MapCallback, 'v=3.exp&callback=Themify.MapCallback&key=' + themify_vars.map_key, null, function () {
                            return typeof google === 'object' && typeof google.maps === 'object';
                        });
                    } else {
                        self.MapCallback(el);
                    }
                }, 500);
            }
            if ($('.themify_bing_map', el).length > 0) {
                if (typeof Microsoft !== 'object' || typeof Microsoft.Maps !== 'object' || themify_vars.isCached === 'enable') {
                    themify_vars.bing_map_key = !themify_vars.bing_map_key ? '' : themify_vars.bing_map_key;
                    self.LoadAsync('//www.bing.com/api/maps/mapcontrol', function () {
                        setTimeout(function () {
                            self.GetMap(el);
                        }, 1000);
                    }, '&key=' + themify_vars.bing_map_key, true, function () {
                        return typeof Microsoft === 'object' && typeof Microsoft.Maps === 'object';
                    });
                } else {
                    self.GetMap(el);
                }
            }
        },
        MapCallback: function (el) {
            $('.themify_map', el).each(function (i) {
                var $this = $(this),
                        address = $this.data('address'),
                        zoom = parseInt($this.data('zoom')),
                        type = $this.data('type'),
                        scroll = $this.data('scroll') === 1,
                        dragMe = $this.data('drag') === 1,
                        controls = $this.data('control') === 1,
                        delay = i * 1000;
                setTimeout(function () {
                    var geo = new google.maps.Geocoder(),
                            latlng = new google.maps.LatLng(-34.397, 150.644),
                            mapOptions = {
                                zoom: zoom,
                                center: latlng,
                                mapTypeId: google.maps.MapTypeId.ROADMAP,
                                scrollwheel: scroll,
                                draggable: dragMe,
                                disableDefaultUI: controls
                            };
                    switch (type.toUpperCase()) {
                        case 'ROADMAP':
                            mapOptions.mapTypeId = google.maps.MapTypeId.ROADMAP;
                            break;
                        case 'SATELLITE':
                            mapOptions.mapTypeId = google.maps.MapTypeId.SATELLITE;
                            break;
                        case 'HYBRID':
                            mapOptions.mapTypeId = google.maps.MapTypeId.HYBRID;
                            break;
                        case 'TERRAIN':
                            mapOptions.mapTypeId = google.maps.MapTypeId.TERRAIN;
                            break;
                    }

                    var map = new google.maps.Map($this[0], mapOptions),
                            revGeocoding = $this.data('reverse-geocoding') ? true : false;

                    google.maps.event.addListenerOnce(map, 'idle', function () {
                        Themify.body.trigger('themify_map_loaded', [$this, map]);
                    });

                    /* store a copy of the map object in the dom node, for future reference */
                    $this.data('gmap_object', map);

                    if (revGeocoding) {
                        var latlngStr = address.split(',', 2),
                                lat = parseFloat(latlngStr[0]),
                                lng = parseFloat(latlngStr[1]),
                                geolatlng = new google.maps.LatLng(lat, lng),
                                geoParams = {'latLng': geolatlng};
                    } else {
                        var geoParams = {'address': address};
                    }
                    geo.geocode(geoParams, function (results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            var position = revGeocoding ? geolatlng : results[0].geometry.location;
                            map.setCenter(position);
                            var marker = new google.maps.Marker({
                                map: map,
                                position: position
                            }),
                                    info = $this.data('info-window');
                            if (undefined !== info) {
                                var contentString = '<div class="themify_builder_map_info_window">' + info + '</div>',
                                        infowindow = new google.maps.InfoWindow({
                                            content: contentString
                                        });

                                google.maps.event.addListener(marker, 'click', function () {
                                    infowindow.open(map, marker);
                                });
                            }
                        }
                    });
                }, delay);
            });
        },
        GetMap: function (el) {
            $('.themify_bing_map', el).each(function (i) {

                var $this = $(this),
                        mapArgs = {},
                        address = $this.data('address'),
                        zoom = parseInt($this.data('zoom')),
                        scroll = $this.data('scroll') !== '1',
                        dragMe = $this.data('drag') !== '1',
                        type = $this.data('type'),
                        controls = $this.data('control') !== 1,
                        delay = i * 1000,
                        map, searchManager;

                address = address.split(',');
                setTimeout(function () {
                    mapArgs = {
                        disableBirdseye: true,
                        disableScrollWheelZoom: scroll,
                        showDashboard: controls,
                        credentials: themify_vars.bing_map_key,
                        disablePanning: dragMe,
                        mapTypeId: null,
                        zoom: zoom
                    };

                    try {
                        map = new Microsoft.Maps.Map($this[0], mapArgs);
                    }
                    catch (err) {
                        Themify.GetMap();
                        return;
                    }

                    function setMapID(mapOption) {
                        switch (type) {
                            case 'aerial' :
                                mapOption.mapTypeId = Microsoft.Maps.MapTypeId.aerial;
                                break;
                            case 'road' :
                                mapOption.mapTypeId = Microsoft.Maps.MapTypeId.road;
                                break;
                            case 'streetside':
                                mapOption.mapTypeId = Microsoft.Maps.MapTypeId.streetside;
                                break;
                            case 'canvasDark':
                                mapOption.mapTypeId = Microsoft.Maps.MapTypeId.canvasDark;
                                break;
                            case 'canvasLight':
                                mapOption.mapTypeId = Microsoft.Maps.MapTypeId.canvasLight;
                                break;
                            case 'birdseye' :
                                mapOption.mapTypeId = Microsoft.Maps.MapTypeId.birdseye;
                                break;
                            case 'ordnanceSurvey':
                                mapOption.mapTypeId = Microsoft.Maps.MapTypeId.ordnanceSurvey;
                                break;
                            case 'grayscale':
                                mapOption.mapTypeId = Microsoft.Maps.MapTypeId.grayscale;
                                break;
                        }
                        return mapOption;
                    }

                    //Make a request to geocode.
                    geocodeQuery(address);

                    function geocodeQuery(query) {
                        //If search manager is not defined, load the search module.
                        if (!searchManager) {
                            //Create an instance of the search manager and call the geocodeQuery function again.
                            Microsoft.Maps.loadModule('Microsoft.Maps.Search', function () {
                                searchManager = new Microsoft.Maps.Search.SearchManager(map);
                                geocodeQuery(query);
                            });
                        } else {
                            var searchRequest = {
                                where: query,
                                callback: function (r) {
                                    //Add the first result to the map and zoom into it.
                                    if (r && r.results && r.results.length > 0) {
                                        var args = {
                                            center: r.results[0].bestView.center
                                        }
                                        args = setMapID(args);
                                        map.setView(args);

                                        var pushpin = new Microsoft.Maps.Pushpin(map.getCenter(), null),
                                                info = $this.data('info-window');
                                        if (undefined !== info) {

                                            var infobox = new Microsoft.Maps.Infobox(map.getCenter(), {
                                                description: info,
                                                visible: false});
                                            infobox.setMap(map);
                                            //Add a click event handler to the pushpin.
                                            Microsoft.Maps.Events.addHandler(pushpin, 'click', function (e) {
                                                infobox.setOptions({visible: true});
                                            });

                                        }
                                        map.entities.push(pushpin);

                                    }
                                },
                                errorCallback: function (e) {
                                    //If there is an error, alert the user about it.
                                    'console' in window && console.log('No results found.');
                                }
                            };
                            //Make the geocode request.
                            searchManager.geocode(searchRequest);
                        }
                    }

                    address = encodeURI(address);

                }, delay);
            });

        },
        LoadAsync: function (src, callback, version, extra, test) {
            var id = this.hash(src), // Make script path as ID
                    exist = this.jsLazy[id]!==undefined,
                    existElemens = exist === true || document.getElementById(id);
            if (exist === false) {
                this.jsLazy[id]=true;
            }
            if (existElemens === true) {
                if (extra !== null && extra !== undefined && extra !== '') {
                    this.loadExtra(extra, document.getElementById(id));
                }
                if (callback) {
                    if (test) {
                        if (test.call() === true) {

                            callback.call();
                            return;
                        }
                        var callbackTimer = setInterval(function () {
                            if (test.call() === true) {
                                clearInterval(callbackTimer);
                                callbackTimer = null;
                                callback.call();
                            }
                        }, 20);
                    } else {
                        callback();
                    }
                }
                return;
            }
            else if (test && test.call() === true) {
                if (extra !== null && extra !== undefined && extra !== '') {
                    this.loadExtra(extra);
                }
                if (callback) {
                    callback.call();
                }
                return;
            }
            if (src.indexOf('.min.js') === -1 && typeof themify_vars !== 'undefined' && themify_vars !== null) {
                var name = src.match(/([^\/]+)(?=\.\w+$)/);
                if (name && name[0]) {
                    name = name[0];
                    if (themify_vars.minify.js[name]) {
                        src = src.replace(name + '.js', name + '.min.js');
                    }
                }
            }
            var r = false,
                    s = document.createElement('script');
            s.type = 'text/javascript';
            s.id = id;
            if (!version && version !== false && 'undefined' !== typeof tbLocalScript) {
                version = tbLocalScript.version;
            }
            s.src = version ? src + '?ver=' + version : src;
            s.async = true;
            s.onload = s.onreadystatechange = function () {
                if (!r && (!this.readyState || this.readyState === 'complete')) {
                    r = true;
                    if (callback) {
                        callback();
                    }
                }
            };
            document.head.appendChild(s);
            if (extra !== null && extra !== undefined && extra !== '') {
                this.loadExtra(extra, s);
            }
        },
        loadExtra: function (data, handler, inHead) {
            if (data) {
                if (typeof handler === 'string') {
                    handler = document.querySelector('script#' + handler);
                    if (handler === null) {
                        return;
                    }
                }
                var str = '';
                if (handler !== undefined && handler !== null) {
                    if (data['before'] !== undefined) {
                        if (typeof data['before'] !== 'string') {
                            for (var i in data['before']) {
                                if (data['before'][i]) {
                                    str += data['before'][i];
                                }
                            }
                        }
                        else {
                            str = data['before'];
                        }
                        if (str !== '') {
                            var before = document.createElement('script');
                            before.type = 'text/javascript';
                            before.text = str;
                            handler.parentNode.insertBefore(before, handler);
                        }
                    }
                }
                if (typeof data !== 'string') {
                    str = '';
                    for (var i in data) {
                        if (i !== 'before' && data[i]) {
                            str += data[i];
                        }
                    }
                }
                else {
                    str = data;
                }
                if (str !== '') {
                    var extra = document.createElement('script');
                    extra.type = 'text/javascript';
                    extra.text = str;
                    if (inHead === undefined || inHead === true) {
                        document.head.appendChild(extra);
                    }
                    else {
                        document.body.appendChild(extra);
                    }
                }
            }
        },
        LoadCss: function (href, version, before, media, callback) {
            if (href === null || href === undefined)
                return;
            if (!version && version !== false && 'undefined' !== typeof tbLocalScript) {
                version = tbLocalScript.version;
            }
            var id = this.hash(href),
                    exist = this.cssLazy.indexOf(id) !== -1,
                    existElemens = exist === true || document.getElementById(id),
                    fullHref = version ? href + '?ver=' + version : href;
            if (exist === false) {
                this.cssLazy.push(id);
            }
            if (existElemens === false) {
                var el = document.querySelector("link[href='" + fullHref + "']");
                existElemens = el !== null && el.getAttribute('rel') === 'stylesheet';
            }
            if (existElemens === true) {
                if (callback) {
                    callback();
                }
                return false;
            }
            if (href.indexOf('.min.css') === -1 && window['themify_vars']!== undefined && window['themify_vars'] !== null) {
                var name = href.match(/([^\/]+)(?=\.\w+$)/);
                if (name && name[0]) {
                    name = name[0].replace('.min', '');
                    if (themify_vars.minify.css[name]) {
                        fullHref = fullHref.replace(name + '.css', name + '.min.css');
                        el = document.querySelector("link[href='" + fullHref + "']");
                        if (el !== null && el.getAttribute('rel') === 'stylesheet') {
                            if (callback) {
                                callback();
                            }
                            return false;
                        }
                    }
                }
            }
            var doc = window.document,
                    ss = doc.createElement('link'),
                    ref;
            if (before) {
                ref = before;
            }
            else {
                var refs = (doc.body || doc.head).childNodes;
                ref = refs[ refs.length - 1];
            }

            var sheets = doc.styleSheets;
            ss.rel = 'stylesheet';
            if (fullHref.indexOf('http') === -1) {
                // convert protocol-relative url to absolute url
				var placeholder = document.createElement( 'a' );
				placeholder.href = fullHref;
				fullHref = placeholder.href;
            }
            ss.href = fullHref;
            // temporarily set media to something inapplicable to ensure it'll fetch without blocking render
            ss.media = 'only x';
            ss.id = id;

            // Inject link
            // Note: `insertBefore` is used instead of `appendChild`, for safety re: http://www.paulirish.com/2011/surefire-dom-element-insertion/
            ref.parentNode.insertBefore(ss, (before ? ref : ref.nextSibling));
            // A method (exposed on return object for external use) that mimics onload by polling document.styleSheets until it includes the new sheet.
            var onloadcssdefined = function (cb) {
                var resolvedHref = fullHref,
                        i = sheets.length;
                while (--i) {
                    if (sheets[ i ].href === resolvedHref) {
                        if (callback) {
                            callback();
                        }
                        fullHref = null;
                        return cb();
                    }
                }
                setTimeout(function () {
                    onloadcssdefined(cb);
                });
            };

            // once loaded, set link's media back to `all` so that the stylesheet applies once it loads
            ss.onloadcssdefined = onloadcssdefined;
            onloadcssdefined(function () {
                ss.media = media || 'all';
            });
            return ss;
        },
        checkFont: function (font) {
            // Maakt een lijst met de css van alle @font-face items.
            if (this.fonts.indexOf(font) !== -1) {
                return true;
            }
            if (this.fonts.length === 0) {
                var o = [],
                        sheets = document.styleSheets,
                        i = sheets.length;
                while (0 <= --i) {
                    if (sheets[i].hasOwnProperty('cssRules') || sheets[i].hasOwnProperty('rules')) {
                        var rules = sheets[i].cssRules || sheets[i].rules || [],
                                j = rules.length;

                        while (0 <= --j) {
                            if (rules[j].style) {
                                var fontFamily = '';
                                if (rules[j].style.fontFamily) {
                                    fontFamily = rules[j].style.fontFamily;
                                }
                                else {
                                    fontFamily = rules[j].style.cssText.match(/font-family\s*:\s*([^;\}]*)\s*[;}]/i);
                                    if (fontFamily) {
                                        fontFamily = fontFamily[1];
                                    }
                                }
                                if (fontFamily === font) {
                                    this.fonts.push(fontFamily);
                                    return true;
                                }
                                if (fontFamily) {
                                    o[fontFamily] = true;
                                }
                            }
                        }
                    }
                }
                this.fonts = Object.keys(o);
            }
            return false;
        },
        lightboxCallback: function (args) {
            this.LoadAsync(themify_vars.url + '/js/themify.gallery.js', function () {
                Themify.GalleryCallBack(args);
            }, null, null, function () {
                return window['ThemifyGallery']!==undefined;
            });
        },
        InitGallery: function (args) {
            if(this.jsLazy['lightbox']===true){
                return;
            }
            var lightboxConditions = false,
                    lbox = typeof themifyScript === 'object' && themifyScript.lightbox;

            if (!this.is_builder_active) {
                lightboxConditions = lbox && ((lbox.lightboxContentImages
                        && document.querySelector(lbox.contentImagesAreas) !== null) || (lbox.lightboxSelector && document.querySelector(lbox.lightboxSelector) !== null));

                if (!lightboxConditions) {
                    lightboxConditions = lbox && lbox.lightboxGalleryOn
                            && (
                                    (lbox.lightboxContentImagesSelector && document.querySelector(lbox.lightboxContentImagesSelector) !== null)
                                    || (lbox.gallerySelector && document.querySelector(lbox.gallerySelector) !== null)
                                    );
                }

                if (lightboxConditions===true) {
                    this.jsLazy['lightbox']=true;
                    this.LoadCss(themify_vars.url + '/css/lightbox.css', null);
                    this.LoadAsync(themify_vars.url + '/js/lightbox.min.js', function () {
                        Themify.lightboxCallback(args);
                    }, null, null, function () {
                        return ('undefined' !== typeof $.fn.magnificPopup);
                    });
                }
            }
            if (!lightboxConditions) {
                this.body[0].classList.add('themify_lightbox_loaded');
                this.body[0].classList.remove('themify_lightboxed_images');
            }
        },
        GalleryCallBack: function (args) {
            args = !args && themifyScript.extraLightboxArgs ? themifyScript.extraLightboxArgs : {};
            ThemifyGallery.init({'context': this.body, 'extraLightboxArgs': args});
            this.body[0].classList.add('themify_lightbox_loaded');
            this.body[0].classList.remove('themify_lightboxed_images');
        },
        parseVideo: function (url) {
            var m = url.match(/(http:|https:|)\/\/(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com))\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/i);
            return {
                type: m !== null ? (m[3].indexOf('youtu') > -1 ? 'youtube' : (m[3].indexOf('vimeo') > -1 ? 'vimeo' : false)) : false,
                id: m !== null ? m[6] : false
            };
        },
        hash: function (s) {
            var hash = 0;
            for (var i = s.length - 1; i > -1; --i) {
                hash = ((hash << 5) - hash) + s.charCodeAt(i);
                hash = hash & hash; // Convert to 32bit integer
            }
            return hash;
        },
        getVendorPrefix: function () {
            if (this.vendor === undefined) {
                var e = document.createElement('div'),
                        prefixes = ['Moz', 'O', 'ms', 'Webkit'];
                for (var i = prefixes.length - 1; i > -1; --i) {
                    if (typeof e.style[prefixes[i] + 'Transform'] !== 'undefined') {
                        this.vendor = prefixes[i].toLowerCase();
                        break;
                    }
                }
                e = null;
                this.vendor = '-' + this.vendor + '-';
            }
            return this.vendor;
        },
        scrollTo: function (val, speed, complete) {
            if (!speed) {
                speed = 800;
            }
            if (!val) {
                val = 0;
            }
            $('html, body').stop().animate({
                scrollTop: val
            }, {
                duration: speed,
                complete: complete
            });
        },
        imagesLoad: function (items, callback) {
            var init = function (items, callback) {
                if (callback === undefined && typeof items === 'function') {
                    items();
                }
                else if (items !== null) {
                    if (items instanceof jQuery) {
                        items.imagesLoaded().always(callback);
                    }
                    else {
                        imagesLoaded(items, callback);
                    }
                }
                else if (typeof callback === 'function') {
                    callback();
                }
                items = init = callback = null;
            };
            if (undefined === window['imagesLoaded']) {
                this.LoadAsync(themify_vars.url + '/js/jquery.imagesloaded.min.js', init.bind(null, items, callback), '4.1.0', null, function () {
                    return (undefined !== window['imagesLoaded']);
                });
            }
            else {
                init(items, callback);
            }
        },
        loadMainCss: function (callback) {
            if (mainCssLoaded === null) {
                var href = themify_vars.url + '/css/main.css',
                        hash = this.hash(href);
                this.LoadCss(href, null, document.getElementById('theme-style-css'), 'all', function (callback) {
                    var interval = setInterval(function () {
                        if (document.getElementById(hash) !== null) {
                            clearInterval(interval);
                            setTimeout(function () {
                                mainCssLoaded = true;
                                if (typeof callback === 'function') {
                                    callback();
                                }
                                callback = null;
                            }, 10);
                        }
                    }, 10);
                }.bind(null, callback));
            }
            else if (typeof callback === 'function') {
                callback();
            }
        },
        setImageAsBackstretch: function (items, options, callback) {
            if (items === null || items === undefined || items.length === 0) {
                return;
            }
            this.loadMainCss();
            this.imagesLoad(null);
            if (items instanceof jQuery) {
                items = items.get();
            }
            else if (items.length === undefined) {
                items = [items];
            }
            var self = this,
                    init = function (items, options, callback) {
                        var reInit = function (items, options, callback) {
                            for (var i = 0, len = items.length; i < len; ++i) {
                                self.imagesLoad(items[i], function (instance) {
                                    var el = instance.elements[0],
                                            posts = el.children;
                                    for (var j = 0, len2 = posts.length; j < len2; ++j) {
                                        var img = posts[j].getElementsByTagName('img')[0];
                                        if (img !== undefined) {
                                            var src = img.getAttribute('src');
                                            if (src) {
                                                var a = img.parentNode.tagName === 'A' ? img.parentNode : null,
                                                        wrap = a !== null ? a.parentNode : img.parentNode,
                                                        $wrap = $(wrap);
                                                if (!$wrap.data('tb_backstretch') && !wrap.classList.contains('tb_backstretch')) {
                                                    if (a !== null) {
                                                        $wrap.one('tb_backstretch.show', function (a) {
                                                            var link = a.cloneNode(false);
                                                            a.parentNode.removeChild(a);
                                                            $(this).find('img').wrap(link);
                                                            a = null;
                                                        }.bind($wrap, a));
                                                    }
                                                    if (!options) {
                                                        options = undefined;
                                                    }
                                                    self.backstretchItems.push($wrap.tb_backstretch(src, options));
                                                    $wrap[0].classList.add('tf_has_backstretch');
                                                }
                                            }
                                        }
                                    }
                                    if (callback) {
                                        callback(el);
                                    }
                                });
                            }
                        };
                        reInit(items, callback);
                        $(window).off('resize.tb_backstretch').off('tfsmartresize.tf_backstretch orientationchange.tf_backstretch').on('tfsmartresize.tf_backstretch', function (e) {
                            if (this.loaded === true && self.w !== e.w && e.w > 680) {
                                for (var i = self.backstretchItems.length - 1; i > -1; --i) {
                                    var instance = self.backstretchItems[i].data('tb_backstretch');
                                    if (instance) {
                                        if (document.body.contains(instance.$wrap[0])) {
                                            instance.resize();
                                        }
                                        else {
                                            self.backstretchItems.splice(i, 1);
                                            var container = instance.$container;
                                            instance.destroy();
                                            reInit([container], options, callback);
                                        }
                                    }
                                    else {
                                        self.backstretchItems.splice(i, 1);
                                    }

                                }
                            }
                        });
                    };
            if (typeof $.fn.tb_backstretch === 'undefined') {
                this.LoadAsync(themify_vars.url + '/js/backstretch.themify-version.js', init.bind(null, items, options, callback), null, null, function () {
                    return (undefined !== $.fn.tb_backstretch);
                });
            }
            else {
                init(items, options, callback);
            }
        },
        autoTiles: function (items, callback, useBackstretch) {
            if (items === null || items === undefined || items.length === 0) {
                return;
            }
            var self = this;
            this.loadMainCss(function (items) {
                for (var i = 0, len = items.length; i < len; ++i) {
                    if(null !== items[i].querySelector('.ptb_loops_wrapper')){
                        items[i].classList.remove('auto_tiles');
                        continue;
                    }
                    var children = items[i].children,
                            smaller = parseInt(window.getComputedStyle(items[i]).getPropertyValue('grid-auto-rows')),
                            length=children.length,
                            count=0;
                            if(length===5 || length===6){
                                items[i].classList.add('tf_tiles_'+length);
                            }
                            else{
                                items[i].classList.add('tf_tiles_more');
                            }
                    var reCalculate=function(){
                            for(var j=children.length;j>-1;--j){
                                if(children[j]!==undefined && children[j]!==null && (children[j].classList.contains('post') || children[j].classList.contains('product'))){
                                    ++count;
                                    if(!children[j].classList.contains('tb_auto_tiled')){
                                        var w=children[j].offsetWidth,
                                            h=children[j].offsetHeight;
                                            children[j].classList.add('tb_auto_tiled');
                                            if((w-10)<=smaller){
                                                if(w===h || (w>h && (w-h)<10) || (h>w && (h-w)<10)){
                                                    children[j].classList.add('tiled-square-small');
                                                }
                                                else{
                                                    children[j].classList.add('tiled-portrait');
                                                }
                                            }
                                            else{
                                                if(w>h){
                                                    children[j].classList.add('tiled-landscape');
                                                }
                                                else{
                                                    children[j].classList.add('tiled-square-large');
                                                }
                                            }
                                    }
                                }
                            }
                    };
                    reCalculate();
                    if(count>0){
                        if(length!==count){
                            var cl=items[i].classList;
                            if(!cl.contains('tf_tiles_more') || count<6){
                                    for(var j=cl.length-1;j>-1;--j){
                                        if(cl[j].indexOf('tf_tiles_')===0){
                                            cl.remove(cl[j]);
                                        }
                                    }
                                    if(count===5 || count===6){
                                        cl.remove('tb_tiles_more');
                                        cl.add('tf_tiles_'+count);
                                    }
                                    else{
                                        cl.add('tf_tiles_more');
                                    }
                            }
                            reCalculate();
                        }
                        if (items[i].previousElementSibling !== null && items[i].previousElementSibling.classList.contains('post-filter')) {
                                self.isoTopFilter(items[i].previousElementSibling);
                        }
                        items[i].classList.add('tf_auto_tiles_init');
                        if (useBackstretch !== true && callback) {
                                callback(items[i]);
                        }
                    }
                }
            }.bind(null, items));

            if (useBackstretch === true) {
                if (items instanceof jQuery) {
                    items = items.get();
                }
                else if (items.length === undefined) {
                    items = [items];
                }
                this.setImageAsBackstretch(items, undefined, callback);
            }
        },
        isoTopFilter: function (postFilter, hasIso, callback) {
            var children = postFilter.children,
                    len = children.length,
                    wrap = postFilter.nextElementSibling,
                    count = 0;
            for (var i = len - 1; i > -1; --i) {
                var cat = children[i].getAttribute('class').replace(/(current-cat)|(cat-item)|(-)|(active)/g, '').replace(' ', ''),
					post = wrap.querySelector('.cat-' + cat);
                if (post === null || post.parentNode !== wrap) {
                    children[i].style['display'] = 'none';
                    ++count;
                }
                else {
                    children[i].style['display'] = '';
                }
            }
            if ((len - count) > 1) {
                postFilter.classList.add('filter-visible');
                postFilter.style['display'] = '';
            }
            else {
                postFilter.style['display'] = 'none';
            }
            if (hasIso !== undefined || wrap.classList.contains('auto_tiles')) {
                var _filter = function (e) {
                    e.preventDefault();
                    var target = $(e.target).closest('.cat-item')[0];
                    if (target !== undefined) {
                        var value = '*';
                        if (!target.classList.contains('active')) {
                            $(target).addClass('active').siblings().removeClass('active');
                            value = target.getAttribute('class').replace(/(current-cat)|(cat-item)|(-)|(active)/g, '').replace(' ', '');
                            value = '.cat-' + value.trim();
                        }
                        else {
                            target.classList.remove('active');
                        }
                        var wrap = this.nextElementSibling;
                        if (wrap !== null) {
                            var iso = window['Isotope'].data(wrap);
                            if (wrap.classList.contains('auto_tiles')) {
                                var posts = wrap.children;
                                for (var i = posts.length - 1; i > -1; --i) {
                                    if (posts[i].classList.contains('post')) {
                                        if (!posts[i].style['width']) {
                                            posts[i].style['width'] = posts[i].offsetWidth + 'px';
                                            posts[i].style['height'] = posts[i].offsetHeight + 'px';
                                        }

                                    }
                                }
                                wrap.classList.add('masonry-done');
                                if (!iso) {
                                    var gutter;
                                    if (Themify.w < 680) {
                                        gutter = 0;
                                    }
                                    else {
                                        gutter = window.getComputedStyle(wrap).getPropertyValue('grid-row-gap');
                                        if (gutter) {
                                            gutter = parseFloat(gutter);
                                        }
                                        else if (gutter != '0') {
                                            gutter = 5;
                                        }
                                    }
                                    iso = new Isotope(wrap, {
                                        masonry: {
                                            'gutter': gutter
                                        },
                                        resize: false
                                    });
                                }
                                if (value === '*') {
                                    iso.once('arrangeComplete', function _arrange() {
                                        this.off('arrangeComplete', _arrange);
                                        setTimeout(function () {
                                            if (value === '*') {
                                                var posts = this.element.children;
                                                for (i = posts.length - 1; i > -1; --i) {
                                                    if (posts[i].classList.contains('post')) {
                                                        posts[i].style['width'] = posts[i].style['height'] = posts[i].style['position'] = posts[i].style['left'] = posts[i].style['top'] = '';
                                                    }
                                                }
                                                this.element.classList.remove('masonry-done');
                                                this.element.style['height'] = this.element.style['position'] = '';
                                                this.$element.trigger('resize.tb_backstretch');
                                            }
                                        }.bind(this), 20);
                                    });
                                }
                            }
                            if (iso) {
                                iso.arrange({filter:(value!=='*'?(value+',.cat-all'):value)});
                                if (callback !== undefined) {
                                    callback.call(this,target, value, iso);
                                }
                            }
                        }
                    }
                };
                $(postFilter).off('click.tf_isotop_filter').on('click.tf_isotop_filter', _filter);
            }
        },
        liveIsoTop:function(e,el,type){
                var self=Themify,
                    hasIso=window['Isotope']!==undefined;
                if(hasIso===true){
                    self.reLayoutIsoTop();
                }
                for(var sel in self.isoTopSelectors){
                    var items = $(sel,el).get(),
                        opt=self.isoTopSelectors[sel]['options'],
                        callback=self.isoTopSelectors[sel]['callback'];
                    for(var i=items.length-1;i>-1;--i){
                        if(hasIso===false || (hasIso===true && !window['Isotope'].data(items[i]))){
                            self.isoTop([items[i]],opt,callback);
                        }
                    }
                }
        },
        isoTop: function (selector, options, callback) {
			var items;
			if(typeof selector==='string'){
				try {
					items = document.querySelectorAll(selector);
				}
				catch(e) {
					items=$(selector).get();
				}
				if(this.is_builder_active===true){
					this.isoTopSelectors[selector]={'options':options,'callback':callback};
				}
			}
			else{
				items=selector;
                                if(items.length===undefined){
					items=[items];
				}
			}
			if (items === null || items === undefined || items.length===0) {
                return;
            }
            var self = this;
            this.imagesLoad(null);
            this.loadMainCss(function (items, options, callback) {
                if (options === undefined) {
                    options = {};
                }
                options['originLeft'] = !self.isRTL;
                options['resize'] = false;
                options['containerStyle'] = null;
                if (options['layoutMode'] === undefined) {
                    options['layoutMode'] = 'masonry';
                }
                var mode = options['layoutMode'];
                if (options[mode] === undefined || options[mode]['columnWidth'] === undefined || options[mode]['gutter'] === undefined) {
                    if (options[mode] === undefined) {
                        options[mode] = {};
                    }
                    if (options[mode]['columnWidth'] === undefined && options['columnWidth'] !== false) {
                        options[mode]['columnWidth'] = '.grid-sizer';
                    }
                    if (options[mode]['gutter'] === undefined && options['gutter'] !== false) {
                        options[mode]['gutter'] = '.gutter-sizer';
                    }
                }
                if (options['itemSelector'] === undefined) {
                    options['itemSelector'] = '.loops-wrapper > .post';
                }
                if (options['stagger'] === undefined) {
                    options['stagger'] = 30;
                }
                if (options['fitWidth'] === true && options[mode]['fitWidth'] === undefined) {
                    options[mode]['fitWidth'] = true;
                }
                options['percentPosition'] = options[mode]['fitWidth'] === true ? false : (options['percentPosition'] === undefined);
                self.imagesLoad(function () {
                    var init = function (items, options, callback) {
                        var finish = function (items, options, callback) {
                            for (var i = items.length - 1; i > -1; --i) {
                                self.imagesLoad(items[i], function (instance) {
                                    var wrap = instance.elements[0];
                                    if (!wrap.classList.contains('masonry-done')) {
                                        var postFilter = wrap.previousElementSibling;
                                        if (wrap.classList.contains('list-post') && (postFilter === null || !postFilter.classList.contains('post-filter'))) {
                                            return;
                                        }
                                        if (wrap.classList.contains('auto_tiles')) {
                                            if (postFilter !== null && postFilter.classList.contains('post-filter')) {
                                                self.isoTopFilter(postFilter, undefined, (options['iso_filter'] !== undefined ? options['iso_filter']['callback'] : undefined));
                                            }
                                            return;
                                        }
                                        mode = options['layoutMode'];
                                        var opt = $.extend(true, {}, options);
                                        if ((opt['gutter'] === false && opt[mode]['gutter'] === undefined) || opt[mode]['gutter'] === false || wrap.classList.contains('no-gutter') || wrap.classList.contains('list-post')) {
                                            opt[mode]['gutter'] = 0;
                                        }
                                        else if (typeof opt[mode]['gutter'] === 'string') {
                                            if(opt[mode]['gutter']!=='none'){
                                                    var gutter = wrap.querySelector(opt[mode]['gutter']);
                                                    if (gutter === null || gutter.parentNode !== wrap) {
                                                            gutter = document.createElement('div');
                                                            gutter.className = opt[mode]['gutter'].replace('.', '');
                                                            wrap.insertBefore(gutter, wrap.firstChild);
                                                            opt[mode]['gutter'] = gutter;
                                                    }
                                            }
                                            else{
                                                delete opt[mode]['gutter'];
                                            }
                                        }
                                        if ((opt['columnWidth'] === false && opt[mode]['columnWidth'] === undefined) || opt[mode]['columnWidth'] === false) {
                                            opt[mode]['columnWidth'] = $(opt['itemSelector'], wrap)[0];
                                        }
                                        else if (typeof opt[mode]['columnWidth'] === 'string') {
                                                if(opt[mode]['columnWidth']!=='none'){
                                                        var columnWidth = wrap.querySelector(opt[mode]['columnWidth']);
                                                        if (columnWidth === null || columnWidth.parentNode !== wrap) {
                                                                columnWidth = document.createElement('div');
                                                                columnWidth.className = opt[mode]['columnWidth'].replace('.', '');
                                                                wrap.insertBefore(columnWidth, wrap.firstChild);
                                                                opt[mode]['columnWidth'] = columnWidth;
                                                        }
                                                }
                                                else{
                                                        delete opt[mode]['columnWidth'];
                                                }
                                        }
                                        if(opt[mode]['columnWidth']===undefined && opt['columnWidth']!==false){
                                                wrap.className += ' tf_masonry_margin';
                                        }
                                        wrap.className += ' masonry-done';
                                        var iso = new Isotope(wrap, opt);
                                        if (postFilter !== null && postFilter.classList.contains('post-filter')) {
                                            self.isoTopFilter(postFilter, iso, (opt['iso_filter'] !== undefined ? opt['iso_filter']['callback'] : undefined));
                                        }
                                        iso.revealItemElements(iso.items);
                                        if (opt['onceLayoutComplete'] !== undefined) {
                                            iso.once('layoutComplete', opt['onceLayoutComplete']);
                                        }
                                        if (opt['layoutComplete'] !== undefined) {
                                            iso.on('layoutComplete', opt['layoutComplete']);
                                        }
                                        if (opt['arrangeComplete'] !== undefined) {
                                            iso.on('arrangeComplete', opt['arrangeComplete']);
                                        }
                                        if (opt['removeComplete'] !== undefined) {
                                            iso.on('removeComplete', opt['removeComplete']);
                                        }
                                        self.isoTopItems.push(iso);
                                        if (callback) {
                                            callback(iso);
                                        }
                                    }
                                    callback = null;
                                });
                            }
                            items = finish = init = null;
                        };
                        if (mode === 'packery' && window['Packery'] === undefined) {
                            self.LoadAsync(themify_vars.url + '/js/isotop-packery.min.js', finish.bind(null, items, options, callback), '2.0.1', null, function () {
                                return (undefined !== window['Packery']);
                            });
                        }
                        else {
                            finish(items, options, callback);
                        }
                    };
                    if (undefined === window['Isotope']) {
                        self.LoadAsync(themify_vars.url + '/js/jquery.isotope.min.js', init.bind(null, items, options, callback), '3.0.6', null, function () {
                            return (undefined !== window['Isotope']);
                        });
                    }
                    else {
                        init(items, options, callback);
                    }
                });
                $(window).off('tfsmartresize.tf_masonry orientationchange.tf_masonry').on('tfsmartresize.tf_masonry orientationchange.tf_masonry', function (e) {
                    if (this.loaded === true && self.w !== e.w) {
                       self.reLayoutIsoTop();
                    }
                });
            }.bind(null, items, options, callback));
        },
        reLayoutIsoTop:function(){
                if (undefined !== window['Isotope']) {
                        for (var i = Themify.isoTopItems.length - 1; i > -1; --i) {
                                try {
                                    Themify.isoTopItems[i].layout();
                                }
                                catch (er) {
                                    Themify.isoTopItems.splice(i, 1);
                                }
                        }
                }
        },
        infinity: function (container, options) {
            if (container === null || container === undefined || container.length === 0 || this.is_builder_active===true) {
                return;
            }
            if (((options['button'] === undefined || options['button']===null) && options.hasOwnProperty('button')) || (options['path'] !== undefined && typeof options['path'] === 'string' && document.querySelector(options['path']) === null)) {
                return;
            }
			// there are no elements to apply the Infinite effect on
			if ( options['append'] && ! $( options['append'] ).length ) {
				// show the Load More button, just in case.
				if ( options['button'] ) {
					options['button'].style.display = 'block';
				}
				return;
			}
            if (container instanceof jQuery) {
                container = container.get();
            }
            else if (container.length === undefined) {
                container = [container];
            }
            var self = this,
                getPathElement = function (container) {
                    var next = container.nextElementSibling;
                    while (next !== null && !next.classList.contains('load-more-button')) {
                        next = next.nextElementSibling;
                    }
                    if (next !== null && next.tagName !== 'A') {
                        next = next.firstChild;
                    }
                    return next;
                };
            this.loadMainCss();
            this.imagesLoad(null);
            var init = function (container, options) {
                for (var i = container.length - 1; i > -1; --i) {
                    var opt = $.extend(true, {}, options);
                    opt['checkLastPage'] = true;
                    opt['prefill'] = false;
                    var path = null;
                    if (opt['path'] === undefined) {
                        path = opt['button'] !== undefined ? opt['button'] : getPathElement(container[i]);
                        if (path !== null) {
                            opt['current'] = parseInt(path.getAttribute('data-current'));
                            opt['total'] = parseInt(path.getAttribute('data-total'));
                            if (opt['total'] < opt['current']) {
                                continue;
                            }
                            opt['path'] = function () {
                                var next = this.options['button'] !== undefined ? this.options['button'] : getPathElement(this.element);
                                if (next !== null) {
                                    if (this.options['total'] > this.pageIndex) {
                                        var href = next.getAttribute('href');
                                        if (href) {
                                            if (this.options['scroll'] === true || this.options['scrollThreshold'] === false) {
                                                var pageKey = 'page',
                                                        current = this.pageIndex,
                                                        nextHref = href;
                                                if (this.options['pageKey'] === undefined) {
                                                    if (nextHref.indexOf('/' + pageKey + '/' + current) === -1) {
                                                        pageKey = 'paged';
                                                    }
                                                }
                                                else {
                                                    pageKey = this.options['pageKey'];
                                                }
                                                if (nextHref.indexOf('/' + pageKey + '/' + current) !== -1) {
                                                    nextHref = nextHref.replace('/' + pageKey + '/' + current, '/' + pageKey + '/' + (current + 1));
                                                }
                                                else {
                                                    nextHref = nextHref.replace(pageKey + '=' + current, pageKey + '=' + (current + 1));
                                                }
                                                next.setAttribute('href', nextHref);
                                                next.setAttribute('data-current', current);
                                            }
                                            return href;
                                        }
                                    }
                                    else {
										if ( next.parentNode !== null ) {
											next.parentNode.removeChild(next);
										}
                                        return;
                                    }

                                }
                            };
                        }
                    }
                    if (opt['scrollThreshold'] === false) {
                        if (opt['button'] === undefined) {
                            opt['button'] = path !== null ? path : getPathElement(container[i]);
                        }
                        opt['button'].style['display'] = 'inline-block';
                    }
                    if (opt['status'] !== false) {
                        var loaderWrap = document.createElement('div'),
                                request = document.createElement('div'),
                                last = document.createElement('div'),
                                error = document.createElement('div');

                        loaderWrap.className = 'tf_load_status';
                        request.className = 'infinite-scroll-request';
                        last.className = 'infinite-scroll-last';
                        error.className = 'infinite-scroll-error';
                        for (var j = 4; j > -1; --j) {
                            var dot = document.createElement('span');
                            dot.className = 'tf_dot';
                            request.appendChild(dot);
                        }
                        loaderWrap.appendChild(request);
                        loaderWrap.appendChild(last);
                        loaderWrap.appendChild(error);
                        container[i].parentNode.insertBefore(loaderWrap, container[i].nextSibling);
                        opt['status'] = loaderWrap;
                    }
                    var infScroll = new InfiniteScroll(container[i], opt);
                    if (opt['current'] !== undefined) {
                        infScroll.pageIndex = opt['current'];
                    }
                    if (opt['scrollThreshold'] !== false) {
                        infScroll.on('scrollThreshold', function () {
                            this.options['scroll'] = true;
                            this.options['loadOnScroll'] = false;
                        });
                    }
                    infScroll.on('load', function (response, path) {
                        if (window['Isotope'] !== undefined) {
                            var isotop = window['Isotope'].data(this.element);
                            if (isotop) {
                                var postFilter = container[0].previousElementSibling;
                                if (postFilter !== null && postFilter.classList.contains('post-filter')) {
                                    var active = postFilter.querySelector('.active');
                                    if (active !== null) {
                                        $(active).trigger('click.tf_isotop_filter');
                                    }
                                }
                            }
                        }
                        this.$element.triggerHandler('infinitebeforeloaded.themify', response);
                        self.body.triggerHandler('infinitebeforeloaded.themify', [this.$element, response]);
                        setTimeout(Themify.reLayoutIsoTop,1500);
                    });
                    infScroll.on('append', function (response, path, items) {
                        var len = items.length,
                                container = this.$element,
                                opt = this.options,
                                isotop = window['Isotope'] !== undefined ? window['Isotope'].data(this.element) : null,
                            isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
                        items[0].className += ' tf_firstitem';
                        for (var i = 0, k = 0; i < len; ++i) {
                            items[i].style['opacity'] = 0;
                            self.imagesLoad(items[i], function (instance) {
                                var el = instance.elements[0];
                                // Fix Srcset in safari browser
                                if(isSafari){
                                    var imgSrcset = el.querySelector('img[srcset]');
                                    if(null!==imgSrcset){
                                        imgSrcset.outerHTML = imgSrcset.outerHTML;
                                    }
                                }
                                self.media(el.querySelectorAll('.wp-audio-shortcode, .wp-video-shortcode'));
                                ++k;
                                if (isotop) {
                                    isotop.appended(el);
                                }
                                el.style['opacity'] = '';
                                if (k === len) {
                                    if (isotop || container[0].classList.contains('auto_tiles')) {
                                        var postFilter = container[0].previousElementSibling;
                                        if (postFilter !== null && postFilter.classList.contains('post-filter')) {
                                            // If new elements with new categories were added enable them in filter bar
                                            self.isoTopFilter(postFilter);
                                        }
                                        isotop = null;
                                    }
                                    if (container[0].classList.contains('auto_tiles')) {
                                        self.autoTiles(container[0]);
                                    }
                                    self.LazyLoad();
                                    var $items = $(items);
                                    container.triggerHandler('infiniteloaded.themify', [$items]);
                                    self.body.triggerHandler('infiniteloaded.themify', [container, $items]);
                                    self.InitGallery();
                                    self.body.triggerHandler('builder_load_module_partial', [$items]);
                                    if ('scroll' === opt['scrollToNewOnLoad']) {
                                            var first = container[0].getElementsByClassName('tf_firstitem');
                                            first = first[first.length - 1];			
                                            var to = $(first).offset().top,
                                                speed = to >= 800 ? (800 + Math.abs((to / 1000) * 100)) : 800,
                                                header = document.getElementById('headerwrap');	
                                            if (header !== null && (header.classList.contains('fixed-header') || self.body[0].classList.contains('fixed-header'))) {
                                                    to -= $(header).outerHeight(true);
                                            }
                                            if (opt['scrollThreshold'] === false || (to - document.documentElement.scrollTop) > opt['scrollThreshold']) {
                                                    self.scrollTo(to, speed);
                                            }
                                    }
                                    opt['loadOnScroll'] = true;
                                    opt = null;
                                }
                            });
                        }
                        /*Google Analytics*/
                        if (window['ga'] !== undefined) {
                            var link = document.createElement('a');
                            link.href = path;
                            ga('set', 'page', link.pathname);
                            ga('send', 'pageview');
                        }
                    });
                }
            };
            if (undefined === window['InfiniteScroll']) {
                this.LoadAsync(themify_vars.url + '/js/jquery.infinitescroll.min.js', init.bind(null, container, options), '3.0.6', null, function () {
                    return (undefined !== window['InfiniteScroll']);
                });
            }
            else {
                init(container, options);
            }
        },
        parallaxScrollingInit: function (el, is_live) {
            if ('undefined' == typeof tbLocalScript || !tbLocalScript.isScrollEffectActive || (is_live !== true && this.is_builder_active)) {
                return;
            }
            var lax_els = document.querySelectorAll('div[data-lax]'),
                    len = lax_els.length;
            if (len > 0) {
                var inner_h = window.innerHeight,
                        top = document.body.getBoundingClientRect().top,
                        isAdded = null;
                for (var i = len - 1; i > -1; --i) {
                    var item = lax_els[i];

                    if (item.hasAttribute('data-box-position') && item.getAttribute('data-box-position').indexOf('%') !== -1) {
                        item.style.transformOrigin = item.getAttribute('data-box-position');
                    }
                    if (item.hasAttribute('data-lax-scale')) {
                        var entryContent = $(item).closest('.entry-content')[0];
                        if (entryContent !== undefined) {
                            entryContent.classList.add('themify-no-overflow-x');
                        }
                        if (isAdded === null) {
                            document.body.classList.add('themify-no-overflow-x');
                            top = document.body.getBoundingClientRect().top;
                            isAdded = true;
                        }
                    }
                    // item.style.animationFillMode = 'none';
                    var elTop = item.getBoundingClientRect().top - top;
                    if ((elTop + 130) < inner_h) {
                        elTop = elTop < 0 ? inner_h : Math.floor(elTop);

                        item.removeAttribute('data-lax-anchor');

                        // Vertical
                        if (item.hasAttribute('data-lax-translate-y')) {
                            var t_y = item.getAttribute('data-lax-translate-y').split(','),
                                    t_y_start = t_y[0].split(' '),
                                    t_y_end = t_y[1].split(' ');
                            item.setAttribute('data-lax-translate-y', t_y_end[0] + ' ' + t_y_start[1] + ',' + elTop + ' ' + t_y_end[1]);
                        }
                        // Horizontal
                        if (item.hasAttribute('data-lax-translate-x')) {
                            var t_x = item.getAttribute('data-lax-translate-x').split(','),
                                    t_x_start = t_x[0].split(' '),
                                    t_x_end = t_x[1].split(' ');
                            item.setAttribute('data-lax-translate-x', t_x_end[0] + ' ' + t_x_start[1] + ',' + elTop + ' ' + t_x_end[1]);
                        }
                        // Opacity
                        if (item.hasAttribute('data-lax-opacity')) {
                            var t_o = item.getAttribute('data-lax-opacity').split(','),
                                    t_o_start = t_o[0].split(' '),
                                    t_o_end = t_o[1].split(' ');
                            item.setAttribute('data-lax-opacity', t_o_end[0] + ' ' + t_o_start[1] + ',' + elTop + ' ' + t_o_end[1]);
                        }
                        // Blur
                        if (item.hasAttribute('data-lax-blur')) {
                            var t_b = item.getAttribute('data-lax-blur').split(','),
                                    t_b_start = t_b[0].split(' '),
                                    t_b_end = t_b[1].split(' ');
                            item.setAttribute('data-lax-blur', t_b_end[0] + ' ' + t_b_start[1] + ',' + elTop + ' ' + t_b_end[1]);
                        }
                        // Rotate
                        if (item.hasAttribute('data-lax-rotate')) {
                            var t_r = item.getAttribute('data-lax-rotate').split(','),
                                    t_r_start = t_r[0].split(' '),
                                    t_r_end = t_r[1].split(' ');
                            item.setAttribute('data-lax-rotate', t_r_end[0] + ' ' + t_r_start[1] + ',' + elTop + ' ' + t_r_end[1]);
                        }
                        // Scale
                        if (item.hasAttribute('data-lax-scale')) {
                            var t_s = item.getAttribute('data-lax-scale').split(','),
                                    t_s_start = t_s[0].split(' '),
                                    t_s_end = t_s[1].split(' ');
                            item.setAttribute('data-lax-scale', t_s_end[0] + ' ' + t_s_start[1] + ',' + elTop + ' ' + t_s_end[1]);
                        }
                    }
                }
                var parallaxScrollingCallback = function () {
                    var laxInit = function () {
                        window['lax'].setup({
                            selector: '[data-lax="true"]'
                        });
                        var update = function () {
                            window['lax'].update(window.scrollY);
                            window.requestAnimationFrame(update);
                        };
                        window.requestAnimationFrame(update);
                    },
                    resize = function () {
                        window['lax'].updateElements();
                    };
                    $(window).off('tfsmartresize', resize).on('tfsmartresize', resize);

                    if (!Themify.is_builder_active) {
                        $(document).ajaxComplete(function () {
                            // A small delay to load lax animation after Ajax request
                            setTimeout(laxInit,300);
                        });
                        Themify.body.on('infiniteloaded.themify',function(e,el){
                            laxInit();
                        });
                    }
                    laxInit();
                };
                if (window['lax'] === undefined) {
                    Themify.LoadAsync(tbLocalScript.builder_url + '/js/themify.lax.min.js', parallaxScrollingCallback, false, false, function () {
                        return window['lax'] !== undefined;
                    });
                } else {
                    parallaxScrollingCallback();
                }
            }
        },
        mediaCssLoad: function () {
            if (themify_vars['media'] !== undefined && themify_vars['media']['css'] !== undefined) {
                for (var i in themify_vars['media']['css']) {
                    if (document.querySelector('link#' + i + '-css') === null) {
                        this.LoadCss(themify_vars['media']['css'][i]['src'], (themify_vars['media']['css'][i]['v'] ? themify_vars['media']['css'][i]['v'] : themify_vars.wp));
                    }
                }
                delete themify_vars['media']['css'];
            }
        },
        media: function (items, callback) {
            if (items === undefined || items === null || items.length === 0 || themify_vars['media'] === undefined) {
                return false;
            }
            if (items instanceof jQuery) {
                items = items.get();
            }
            else if (items.length === undefined) {
                items = [items];
            }
            this.mediaCssLoad();
            var self = this,
                    init = function (items, callback) {
                        var settings = window['_wpmejsSettings'];
                        if (settings === undefined) {
                            if (themify_vars['media']['_wpmejsSettings'] !== undefined) {
                                self.loadExtra(themify_vars['media']['_wpmejsSettings']);
                                settings = window['_wpmejsSettings'];
                            }
                            else {
                                settings = {};
                            }
                        }
                        for (var i = items.length - 1; i > -1; --i) {
                            if (items[i].tagName !== 'DIV') {
                                new window['MediaElementPlayer'](items[i], settings);
                            }
                        }
                        if (callback) {
                            callback();
                        }
                        items = null;
                    };
            if (window['MediaElementPlayer'] === undefined) {
                var jsKeys = Object.keys(themify_vars['media']['js']),
                        currentIndex = 0,
                        len = jsKeys.length,
                        recurSiveLoad = function (index, items, callback) {
                            var key = jsKeys[index];
                            self.LoadAsync(themify_vars['media']['js'][key]['src'], function (items, callback) {
                                ++currentIndex;
                                if (currentIndex < len) {
                                    recurSiveLoad(currentIndex, items, callback);
                                }
                                else {
                                    init(items, callback);
                                }
                            }.bind(null, items, callback),
                                    (themify_vars['media']['js'][key]['v'] ? themify_vars['media']['js'][key]['v'] : themify_vars.wp),
                                    themify_vars['media']['js'][key]['extra'],
                                    (key === 'mediaelement-core' ? (function () {
                                        return window['MediaElementPlayer'] !== undefined;
                                    }) : null)
                                    );
                        };
                recurSiveLoad(currentIndex, items, callback);
            }
            else {
                init(items, callback);
            }
        },
        sharer:function(type,url,title){
            if(!title){
                title='';
            }else{
                // Strip HTML
                var tmp = document.createElement("DIV");
                tmp.innerHTML = title;
                title = tmp.textContent || tmp.innerText || "";
                title = title.trim();
                tmp = null;
            }
            var width = 550,
                height = 300,
                leftPosition,
                topPosition;
            leftPosition = (window.screen.width / 2) - ( ( width / 2 ) + 10 );
            topPosition = (window.screen.height / 2) - (( height / 2 ) + 50);
            var windowFeatures = 'status=no,height='+height+',width='+width+',resizable=yes,left='+leftPosition+',top='+topPosition+',screenX=#{left},screenY=#{top},toolbar=no,menubar=no,scrollbars=no,location=no,directories=no';
            if('facebook' === type){
                url = 'https://www.facebook.com/sharer.php?u='+url;
            } else if ( 'twitter' === type) {
                url = 'http://twitter.com/share?url='+url+'&text='+title;
            } else if ( 'linkedin' === type) {
                url = 'https://www.linkedin.com/shareArticle?mini=true&url='+url;
            } else if ( 'pinterest' === type) {
                url = '//pinterest.com/pin/create/button/?url='+url+'&description='+title;
            } else if ( 'email' === type && window['tbLocalScript']!==undefined) {
                title = '' !== title ? title + "%0D%0A" : '';
                url = 'mailto:?subject='+encodeURIComponent(tbLocalScript.emailSub)+'&body=' + title + url;
            }
            var win = window.open(encodeURI(url), 'sharer', windowFeatures);
            win.moveTo(leftPosition, topPosition);
        }
    };
    Themify.Init();

}(jQuery, window, document,undefined));
