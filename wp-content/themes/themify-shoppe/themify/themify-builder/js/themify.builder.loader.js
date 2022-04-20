/*! Themify Builder - Asynchronous Script and Styles Loader */
(function ($, window, document,Themify,undefined,tbLoaderVars) {
    'use strict';
    $(function() {
        var isTouch = Themify.isTouch ? true : false;
        function remove_tinemce() {
            if (tinymce !== undefined && tinyMCE) {
                tinyMCEPreInit.mceInit['tb_lb_hidden_editor']['wp_autoresize_on'] = false;
                var content_css = tinyMCEPreInit.mceInit['tb_lb_hidden_editor']['content_css'].split(',');
                tinyMCEPreInit.mceInit['tb_lb_hidden_editor']['content_css'] = content_css[1] !== undefined ? content_css[1] : content_css[0];
                tinyMCEPreInit.mceInit['tb_lb_hidden_editor']['plugins'] = 'charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wpdialogs,wptextpattern,wpview,wplink';
                tinyMCEPreInit.mceInit['tb_lb_hidden_editor']['indent'] = 'simple';
                tinyMCEPreInit.mceInit['tb_lb_hidden_editor']['ie7_compat'] = false;
                tinyMCEPreInit.mceInit['tb_lb_hidden_editor']['root_name'] = 'div';
                tinyMCEPreInit.mceInit['tb_lb_hidden_editor']['relative_urls'] = true;
                tinyMCE.execCommand('mceRemoveEditor', true, 'tb_lb_hidden_editor');
                $('#wp-tb_lb_hidden_editor-editor-container,#wp-tb_lb_hidden_editor-editor-tools').remove();
            }
        }
        if (wp === undefined || wp.customize === undefined) {
            var builder = document.getElementsByClassName('themify_builder_content'),
                toogle = document.getElementsByClassName('toggle_tb_builder')[0];
                var pageId=toogle!==undefined?toogle.getElementsByClassName('tb_front_icon')[0].getAttribute('data-id'):false,
                found=false;
            for(var i=builder.length-1;i>-1;--i){
                var bid=builder[i].getAttribute('data-postid');
                    if(bid===pageId){
                        found=true;
                    }
                    var a = document.createElement('a'),
                        span = document.createElement('span');
                    a.href = 'javascript:void(0);';
                    a.className = 'tb_turn_on js-turn-on-builder';
                    span.className = 'dashicons dashicons-edit';
                    span.setAttribute('data-id',bid);
                    a.appendChild(span);
                    a.appendChild(document.createTextNode(builder[i].parentNode.classList.contains('tbp_template')?tbLoaderVars.editTemplate:tbLoaderVars.turnOnBuilder));
                    builder[i].insertAdjacentElement('afterEnd',a);
					if(builder[i].children.length===0){
						builder[i].classList.add('tb_builder_empty');
					}
            }
            if (toogle === undefined) {
                toogle = document.getElementsByClassName('js-turn-on-builder')[0];
                if (toogle !== undefined) {
                    pageId = toogle.getElementsByClassName('dashicons-edit')[0].getAttribute('data-id');
                }
            }
            if(found===false){
                pageId=null;
                toogle.classList.add('tb_disabled_turn_on');
            }
            else{
                toogle.classList.remove('tb_disabled_turn_on');
            }
            builder =toogle= null;
        }
        var responsiveSrc = window.location.href.indexOf('?') > 0 ? '&' : '?';
        responsiveSrc = window.location.href.replace(window.location.hash, '').replace('#', '') + responsiveSrc + 'tb-preview=1&ver=' + tbLocalScript.version;
        
        function init(){
            Themify.body.on('click.tbloader', '.toggle_tb_builder > a, a.js-turn-on-builder', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var is_locked = Themify.body.hasClass('tb_restriction');
                    Themify.LoadAsync(tbLocalScript.builder_url+'/js/themify-ticks.js', function(){
                    if(is_locked){
                        TB_Ticks.init(tbLocalScript.ticks).show();
                        init(); 
                    }
                    },null,null,function(){
                        return typeof TB_Ticks!=='undefined';
                    });
                if(is_locked){
                    return;
                }
                var post_id = !this.classList.contains('js-turn-on-builder')?pageId:this.childNodes[0].getAttribute('data-id');
                if(!post_id || this.parentNode.classList.contains('tb_disabled_turn_on')){
                    return;
                }
                Themify.body.off('click.tbloader');
                setTimeout(remove_tinemce, 1);
                //remove unused the css/js to make faster switch mode/window resize
                var $children = Themify.body.children(),
                    f = document.createDocumentFragment();
                    if(tbLoaderVars.styles!==null){
                        for(var i=tbLoaderVars.styles.length-1;i>-1;--i){
                            if(tbLoaderVars.styles[i]!==''){
                                var l = document.createElement('link');
                                    l.href =  tbLoaderVars.styles[i]+'?ver='+tbLocalScript.version;;
                                    l.rel = 'preload';
                                    l.setAttribute('as','style');
                                    f.appendChild(l);
                            }
                        }
                        document.head.appendChild(f);
                    }
                var builderLoader,
                    css_items = [],
                    scrollPos = $(document).scrollTop(),
                    css = Array.prototype.slice.call(document.head.getElementsByTagName('link')),
                    js_styles = Array.prototype.slice.call(document.head.getElementsByTagName('script')).concat(Array.prototype.slice.call(document.head.getElementsByTagName('style'))),
                    workspace = document.createElement('div'),
                    bar = document.createElement('div'),
                    leftBar = document.createElement('div'),
                    rightBar = document.createElement('div'),
                    verticalTooltip = document.createElement('div'),
                    iframe = document.createElement('iframe');
                    workspace.className = 'tb_workspace_container';
                    bar.className = 'tb_vertical_bars';
                    leftBar.id ='tb_left_bar';
                    rightBar.id ='tb_right_bar';
                    leftBar.className = rightBar.className = 'tb_middle_bar';
                    verticalTooltip.className = 'tb_vertical_change_tooltip';
                    iframe.className = 'tb_iframe';
                    iframe.id=iframe.name='tb_iframe';
                    iframe.scrolling = isTouch ? 'no' : 'yes';
                    iframe.src = responsiveSrc+'&tb-id='+post_id;
                    bar.appendChild(leftBar);
                    bar.appendChild(iframe);
                    bar.appendChild(rightBar);
                    bar.appendChild(verticalTooltip);
                    workspace.appendChild(bar);
                    document.body.appendChild(workspace);
                    if(tbLoaderVars.styles!==null){
                        for (i = 0, len = tbLoaderVars.styles.length; i < len; ++i) {
                            if(tbLoaderVars.styles[i]!==''){
                                Themify.LoadCss(tbLoaderVars.styles[i]);
                                css_items[tbLoaderVars.styles[i]+'?ver='+tbLocalScript.version] = 1;
                            }
                        }
                    }
                    tbLoaderVars.styles=null;
                    setTimeout(function () {
                        builderLoader = document.createElement('div');
                        var fixed = document.createElement('div'),
                            progress = document.createElement('div'),
                            icon = document.getElementsByClassName('tb_front_icon')[0];
                        builderLoader.id = 'tb_alert';
                        builderLoader.className = 'tb_busy';
                        
                        fixed.id='tb_fixed_bottom_scroll';
                        fixed.className='tb_fixed_scroll';
                        progress.id='builder_progress';
                        
                        progress.appendChild(document.createElement('div'));
                        document.body.insertAdjacentElement('afterbegin', fixed);
                        document.body.appendChild(builderLoader);
                        // Change text to indicate it's loading
                         if(icon!==undefined){
                            icon.parentNode.appendChild(progress); 
                         }
                         icon = fixed =progress= null;
                    }, 1);
                Themify.body.off('builder_load_module_partial themify_builder_ready');
                $(document).off('ajaxComplete');
                iframe.addEventListener('load', function () {
                        var _this = this, contentWindow, b;
                        Themify.body.one('themify_builder_ready', function (e) {
                            $(builderLoader).fadeOut(100, function () {
                                this.classList.remove('tb_busy');
                            });
							var isArchive = Themify.body[0].classList.contains( 'archive' );
                            Themify.body[0].className= 'themify_builder_active builder-breakpoint-desktop';
                            Themify.body[0].removeAttribute('style');
                            if(isTouch){
                                Themify.body[0].className+=' tb_touch';
                            }
                            if ( isArchive ) {
								// "archive" classname signifies whether current page being edited is a WP archive page
                                Themify.body[0].className += ' archive';
                            }
                            if('1' === tbLoaderVars.isGlobalStylePost){
                                Themify.body[0].className+=' gs_post';
                            }
                            workspace.style['display']='block';
                            var activeBuilderPost=contentWindow.tb_app.Instances.Builder[0].$el.offset().top;
                            if(activeBuilderPost>scrollPos){
                                    scrollPos=activeBuilderPost;
                            }
                            contentWindow.scrollTo(0, scrollPos);
                            Themify.iframe = iframe;
                            Themify.is_builder_active = true;
                            setTimeout(function(){
                                $children.hide();
                                for (var i = 0, len = js_styles.length; i < len; ++i) {
                                    if (js_styles[i] && js_styles[i].parentNode) {
                                      js_styles[i].parentNode.removeChild(js_styles[i]);
                                    }
                                }
                                js_styles = null;
                                for (var i =css.length-1; i>-1; --i) {
                                    if (css[i] && css[i].parentNode) {
                                        var href = css[i].href;
                                        if(css_items[href]===undefined && href.indexOf('wp-includes')===-1 && href.indexOf('admin-bar')===-1){
                                            css[i].setAttribute('disabled',true);
                                            css[i].parentNode.removeChild(css[i]);
                                        }
                                    }
                                }
                                css = css_items= tbLoaderVars  = builderLoader= null;
                                $('.themify_builder_content,#wpadminbar,header').remove();
                                $children.filter( 'ul,a,video,audio' ).filter( ':not(:has(link))' ).remove();
                                var events = ['scroll', 'tfsmartresize','debouncedresize','throttledresize' ,'resize', 'mouseenter', 'keydown','keyup','mousedown','assignVideo'],
                                    $window = $(window),
                                    $document=$(document);
                                for(var i=events.length-1;i>-1;--i){
                                    $window.off(events[i]);
                                    $document.off(events[i]);
                                    Themify.body.off(events[i]);
                                }
                                document.documentElement.removeAttribute('style');
                                document.documentElement.removeAttribute('class');
                                var ticks = tbLocalScript.ticks;
                                if(!b.hasClass('tb_restriction')){
                                    setTimeout(function(){
                                        TB_Ticks.init(ticks,contentWindow).ticks();
                                    },5000);
                                }
                                else{
                                    setTimeout(function(){
                                        document.body.appendChild(b.find('#tmpl-builder-restriction')[0]);
                                        TB_Ticks.init(ticks,contentWindow).show();
                                    },1000);
                                }
                                setTimeout(function(){
                                    if(typeof Rellax!=='undefined'){
                                        Rellax.disableCheckPosition();
                                        Rellax.destroy();
                                    }
                                    var globals = ['ThemifyBuilderModuleJs','c','_wpemojiSettings','twemoji','themifyScript','tbLocalScript','themify_vars','tbScrollHighlight','google','ThemifyGallery','Animation','$f','Froogaloop','SliderProSlide','SliderProUtils','ThemifySlider','FixedHeader','LayoutAndFilter','WOW','Waypoint','$slidernav','google','Microsoft','Rellax','module$contents$MapsEvent_MapsEvent','module$contents$mapsapi$overlay$OverlayView_OverlayView','wc_add_to_cart_params','woocommerce_params','wc_cart_fragments_params','wc_single_product_params','tf_mobile_menu_trigger_point','themifyMobileMenuTrigger'];

                                    for(var k=0,len=globals.length;k<len;++k){
                                        if(typeof window[globals[k]]!=='undefined'){
                                            window[globals[k]] = null;
                                        }
                                    }
                                    window['wp']['emoji']=null;
                                },3000);
                            },800);
                        });
                        // Cloudflare compatibility fix
                        if( '__rocketLoaderLoadProgressSimulator' in _this.contentWindow ) {
                                var rocketCheck = setInterval( function() {
                                        if( _this.contentWindow.__rocketLoaderLoadProgressSimulator.simulatedReadyState === 'complete' ) {
                                                clearInterval( rocketCheck );
                                                contentWindow = _this.contentWindow;
                                                b = contentWindow.jQuery('body');
                                                contentWindow.themifyBuilder.post_ID = post_id;
                                                b.trigger( 'builderiframeloaded.themify', _this );
                                        }
                                }, 10 );
                        } else {
                                contentWindow = _this.contentWindow;
                                b = contentWindow.jQuery('body');
                                contentWindow.themifyBuilder.post_ID = post_id;
                                b.trigger( 'builderiframeloaded.themify', _this );
                        }
                },{once:true});
            });
        }


        init();
        if(!document.body.classList.contains('tb_restriction')){  
            if (window.location.hash === '#builder_active') {
                $('.toggle_tb_builder > a').first().trigger('click');
                window.location.hash = '';
            }
            else {
                //cache iframe content in background and tincymce content_css
                var f = document.createDocumentFragment(),
                    link = document.createElement('link'),
                    tinemce = tinyMCEPreInit.mceInit['tb_lb_hidden_editor']['content_css'],
                    cache_suffix = tinyMCEPreInit.mceInit['tb_lb_hidden_editor']['cache_suffix'];
                    link.href = responsiveSrc+(pageId?'&tb-id='+pageId:'');
                    link.rel = 'prefetch';
                    link.setAttribute('as','document');
                    f.appendChild(link);
                if( tinemce ) {
                    tinemce = tinemce.split( ',' );
                    for (var i = 0, len = tinemce.length; i < len; ++i) {
                        tinemce[i] += (tinemce[i].indexOf('?') > -1 ? '&' : '?') + cache_suffix;
                        var l = document.createElement('link');
                            l.href = tinemce[i];
                            l.rel = 'prefetch';
                            l.setAttribute('crossorigin',true);
                            l.setAttribute('as','style');
                            f.appendChild(l);
                    }
                }
                document.head.appendChild(f);
            }
        }
    });
})(jQuery, window, document,Themify,undefined,tbLoaderVars);
