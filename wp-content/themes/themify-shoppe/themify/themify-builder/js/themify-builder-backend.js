(function ($,api) {

    'use strict';
    var $body = $('body');
    if ( $('#page-builder.themify_write_panel').length>0){ 
        themifyBuilder.is_gutenberg_editor = false;
        $body[0].classList.remove('themify-gutenberg-editor');
    }
    else if(! themifyBuilder.is_gutenberg_editor){
        return;
    }
        
    api.redirectFrontend = false;
    var saved = false,
    initScroolbar = function(){
        var $panel = api.toolbar.$el,
            tb_sticky_pos = function () {
                var isFixed,
                    pageBuilder = $('#page-builder'),
                    wrapper = $('#tb_row_wrapper'),
                    panelTop,
                    $wpadminbar = $('#wpadminbar'),
                    top,
                    handleScroll = function () {
                        var scroll=window.pageYOffset;
                        isFixed =  scroll >= top && scroll < (panelTop+wrapper.height());
                        if(isFixed===true){
                            $panel[0].classList.add('tb_toolbar_fixed');
                        }
                        else{
                            $panel[0].classList.remove('tb_toolbar_fixed');
                        }
                    },
                setPosition = function(){
                    var wheight= $wpadminbar.outerHeight(true);
                    panelTop = Math.round( $panel.offset().top);
                    top=panelTop-wheight;
                    $panel.css({'width':pageBuilder.width()+'px','left':pageBuilder.offset().left,'top':wheight});
                };
                setPosition();
                window.addEventListener('scroll',handleScroll,{passive:true});
                $(window).on('tfsmartresize.tfsticky', function(){
                    setPosition();
                    handleScroll();
                });
                handleScroll();
        };
        if($panel.is(':visible')){
            tb_sticky_pos();
        }
        else{
            $('.themify-meta-box-tabs a').on('click', function _click() {
                if (this.id === 'page-buildert') {
                    tb_sticky_pos();
                    $('.themify-meta-box-tabs a').off('click',_click);
                }
            });
        }
    };
    api.toolbarCallback = function(){
        api.undoManager.btnUndo = api.toolbar.el.getElementsByClassName('tb_undo_btn')[0];
        api.undoManager.btnRedo = api.toolbar.el.getElementsByClassName('tb_redo_btn')[0];
        api.undoManager.compactBtn = api.toolbar.el.getElementsByClassName('tb_compact_undo')[0]
    };
    api.render = function () {
        if ( themifyBuilder.is_gutenberg_editor && !document.getElementById('tb_canvas_block') ) return;
       
        $body[0].insertAdjacentHTML('afterbegin', '<div class="tb_fixed_scroll" id="tb_fixed_bottom_scroll"></div>');
        $body.append($('<div/>', {id: 'tb_alert'}));
        if (themifyBuilder.builder_data.length === 0) {
            themifyBuilder.builder_data = {};
        }

        if ( themifyBuilder.is_gutenberg_editor ) {
                var template = wp.template('builder_admin_canvas_block');
                document.getElementById('tb_canvas_block').innerHTML = template();

        }
        ThemifyBuilderCommon.setToolbar();
        api.toolbar = new api.Views.Toolbar({el:'#tb_toolbar'});
        api.toolbar.render();
        api.toolbarCallback();

        api.Instances.Builder[0] = new api.Views.Builder({el: '#tb_row_wrapper', collection: new api.Collections.Rows(themifyBuilder.builder_data)});
        api.Instances.Builder[0].render();
        api.toolbar.pageBreakModule.countModules();
        /* hook save to publish button */
        $('input#publish,input#save-post').one('click', function (e) {
            $('.themify_builder').find('input[required]').removeAttr('required');
            if (!saved) {
                var $this = $(this);
                $this.addClass('disabled');
                api.Utils.saveBuilder(function(){
                    // Clear undo history
                    api.undoManager.reset();
                    $this.removeClass('disabled').trigger('click');
                });
                e.preventDefault();
            }
        });
        // switch frontend
        var switchButton = $('<a href="#" id="tb_switch_frontend_button" class="button tb_switch_frontend">' + themifyBuilder.i18n.switchToFrontendLabel + '</a>'),
                editorPlaceholder = $( '.themify-wp-editor-holder' );

        if( editorPlaceholder.length ) {
                switchButton = editorPlaceholder.find( 'a' );
        } else {
                switchButton.appendTo( '#postdivrich #wp-content-media-buttons' );
        }

        switchButton.on('click', function (e) {
                e.preventDefault();
                $('#tb_switch_frontend').trigger('click');
        });

        $('input[name*="builder_switch_frontend"]').closest('.themify_field_row').remove(); // hide the switch to frontend field check

        api.Instances.Builder[api.builderIndex].$el.triggerHandler('tb_init');
        
        if( sessionStorage.getItem( 'focusBackendEditor' ) ) {
                api._backendBuilderFocus();
                sessionStorage.removeItem( 'focusBackendEditor' );
        }
        ThemifyStyles.init(ThemifyConstructor.data,ThemifyConstructor.breakpointsReverse,themifyBuilder.post_ID);
        if(!themifyBuilder.is_gutenberg_editor ){
            initScroolbar();
        }
    };

    api._backendSwitchFrontend = function(link){
        $('#builder_switch_frontend_noncename').val('ok');
        saved = true;
            if ( 'publish' === $('#original_post_status').val() ) {
                if ( themifyBuilder.is_gutenberg_editor ) {
                        if ( $('.editor-post-publish-button').length ) {
                                $('.editor-post-publish-button').trigger('click');
                        } else {
                                $('.editor-post-publish-panel__toggle').trigger('click');
                        }
                        api.redirectFrontend = link;
                        $('#tb_switch_frontend').trigger('click.frontend-btn');
                } else {
                $('#publish').trigger('click');
                }
            } else {
            if ( themifyBuilder.is_gutenberg_editor ) {
                    $('.editor-post-save-draft').trigger('click');
                    api.redirectFrontend = link;
            } else {
                $('#save-post').trigger('click');
            }
        }
    };
    api._backendBuilderFocus = function(){
        $( '#page-buildert' ).trigger( 'click' );
        if(themifyBuilder.is_gutenberg_editor){
            api.Utils.scrollToDropped(api.toolbar.el);
        }
        else{
            api.Utils.scrollTo(api.toolbar.$el.offset().top - $( '#wpadminbar' ).height());
        }
    };

    $(function () {
        if ( $body[0].classList.contains('post-php')) {
            var lock  = document.getElementById('post-lock-dialog');
            if(lock!==null && !lock.classList.contains('hidden')){
                Themify.LoadAsync( themifyBuilder.builder_url + '/js/themify-ticks.js', function() {
                        if ( $body[0].classList.contains( 'tb_restriction' ) ) {
                                TB_Ticks.init( themifyBuilder.ticks, window ).show();
                        } else {
                                TB_Ticks.init( themifyBuilder.ticks, window ).ticks();
                        }
                }, null, null, function() {
                        return typeof TB_Ticks !== 'undefined';
                } );
            }
        }
        // WPML compat
        if (typeof window.icl_copy_from_original === 'function') {
            var _original_icl_copy_from_original = window.icl_copy_from_original;
            // override "copy content" button action to load Builder modules as well
            window.icl_copy_from_original = function (lang, id) {
                _original_icl_copy_from_original(lang, id);
                $.ajax({
                    url: ajaxurl,
                    type: "POST",
                    data: {
                        action: 'themify_builder_icl_copy_from_original',
                        source_page_id: id,
                        source_page_lang: lang
                    },
                    success: function (data) {
                        if (data != '-1') {
                            $('#page-builder .themify_builder.themify_builder_admin').empty().append(data).contents().unwrap();

                            // redo module events
                            //ThemifyPageBuilder.moduleEvents();
                        }
                    }
                });
            };
        }

    });

    // Run on WINDOW load
    $(window).one('load',function () {
        // Init builder
        Themify.LoadAsync( themifyBuilder.builder_url + '/js/themify-constructor.js', function() {
                ThemifyConstructor.getForms(api.render);
        }, null, null, function() {
                return typeof ThemifyConstructor !== 'undefined';
        } );
    });
})(jQuery,tb_app);
