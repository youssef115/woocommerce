<?php

class Themify_Enqueue_Assets {

    private static $css = array();
    public static $isInTop = true;
    public static $isInline = false;
    public static $isHeader=false;
    public static $isFooter=false;

    public function __construct() {
	add_action( 'wp_enqueue_scripts', array(__CLASS__,'enqueue'),7);
	if(!is_admin()){
	    if(themify_is_themify_theme()){
		add_action( 'wp_enqueue_scripts', 'themify_enqueue_framework_assets', 12 );
	    }
	    if(!class_exists('Themify_Builder') || (Themify_Builder::$frontedit_active===false && !Themify_Builder_Model::is_front_builder_activate())){
		self::$isInTop = themify_builder_get('setting-css')!=='bottom';
		if(self::$isInTop===true){
		    self::$isInline = themify_builder_get('setting-css-inline');
		    self::$isInline  = !empty(self::$isInline);
		}

		if (self::$isInTop === false || self::$isInline === true) {
		    add_filter('style_loader_tag', array(__CLASS__, 'style_header_tag'), 10, 4);
		}
	    }
	    add_action('wp_head', array(__CLASS__, 'wp_head'));
	    add_action( 'wp_head', array( __CLASS__, 'wp_head_late' ), 1000 );
	    add_filter('wp_title', array(__CLASS__, 'wp_title'), 10, 2);
	    add_action('wp_footer', array(__CLASS__, 'wp_footer'), 1);
	}
    }
    
    public static function enqueue(){
	    themify_enque_style( 'themify-common', THEMIFY_URI . '/css/themify.common.css',null,THEMIFY_VERSION );
	    wp_enqueue_style( 'themify-framework', themify_enque(THEMIFY_URI . '/css/themify.framework.css'),null,THEMIFY_VERSION );
	    add_filter('style_loader_tag', array(__CLASS__, 'style_framework_tag'), 8, 4);
	    themify_load_main_script();
    }

    public static function add_css($handle, $src, $deps, $ver, $media) {
	if (!isset(self::$css[$handle])) {
	    if($handle!=='themify-google-fonts' && $handle!=='google-fonts'){
		$src = themify_enque($src);
	    }
	    if (self::$isInTop === false || self::$isInline === true) {
		self::$css[$handle] = true;
	    }
	    if(self::$isInline === true && self::$isHeader===true && self::$isFooter===false){
		self::style_header_tag('', $handle, $src.'?ver='.$ver, $media);
	    }
	    else{
		wp_enqueue_style($handle, $src, $deps, $ver, $media);
	    }
	}
    }


    public static function wp_head() {
	if(themify_is_themify_theme()){
	    themify_favicon_action();
		echo "\n\n";
	    /* Outputs the module styling and then the Custom CSS module content. */
	    themify_get_css();
	    // Custom CSS
	    if ($custom_css = themify_get('setting-custom_css')) {
		echo "\n\n<!-- custom css -->\n\n<style type='text/css'>$custom_css</style>";
	    }
	}
	/* Adds the global variable in JS that controls the mobile menu breakpoint */
	?>

	<?php if ( ! ( defined( 'THEMIFY_GOOGLE_FONTS' ) && ! THEMIFY_GOOGLE_FONTS ) ) : ?>
	<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin/>
	<?php endif; ?>

	<script type="text/javascript">
	    var tf_mobile_menu_trigger_point = <?php echo themify_get('setting-mobile_menu_trigger_point', 1200, true); ?>;
	</script>
	<?php
	if (self::$isInTop === false || self::$isInline === true) {
	    remove_filter('style_loader_tag', array(__CLASS__, 'style_header_tag', 150, 4));
	    global $wp_styles;
	    foreach ($wp_styles->done as $i => $p) {
		if (isset(self::$css[$p])) {
		    unset($wp_styles->done[$i]);
		}
	    }
	}
	self::$isHeader=true;
    }

	/**
	 * Output just before the </head> tag
	 *
	 * @return void
	 */
	public static function wp_head_late() {
		echo themify_get('setting-header_html'); /* Header HTML Module - Action */
	}

    public static function style_framework_tag($tag, $handle, $href, $media) {
	if( 'themify-framework' === $handle ) {
	    unset(self::$css[$handle]);
	    $tag = '<meta name="'.$handle.'-css" data-href="'.$href.'" content="'.$handle.'-css" id="'.$handle.'-css" />';
	    remove_filter('style_loader_tag', array(__CLASS__, 'style_framework_tag'), 8, 4);
	}
	return $tag;
    }
    
    public static function style_header_tag($tag, $handle, $href, $media) {
	if (isset(self::$css[$handle])) {
	    if (self::$isInline === true) {
		unset(self::$css[$handle]);
		global $wp_filesystem;
		if($handle!=='themify-google-fonts'){
		    $f = pathinfo($href);
		    $absolute = str_replace(WP_CONTENT_URL, '', $f['dirname']);
		    $name = explode('?', $f['basename']);
		    $dir = trailingslashit(WP_CONTENT_DIR) . trailingslashit(trim($absolute,'/')) . $name[0];
		    $f = $absolute = $name = null;
		}
		else{
		    $dir=$href;
		}
		$css = $wp_filesystem->get_contents($dir);
		if(!empty($css)){
		    echo '<style id="' . $handle . '" type="text/css">', $css, '</style>';
		    $tag = '';
		}
	    } else {
		$tag = '<link rel="preload" href="' . $href . '" as="style" />';
	    }
	}
	return $tag;
    }

    /**
     * Set a default title for the front page
     *
     * @return string
     * @since 1.7.6
     */
    public static function wp_title($title, $sep) {
	if (empty($title) && ( is_home() || is_front_page() )) {
	    global $aioseop_options;
	    if (class_exists('All_in_One_SEO_Pack') && '' != $aioseop_options['aiosp_home_title']) {
		return $aioseop_options['aiosp_home_title'];
	    }
	    return get_bloginfo('name');
	}

	return str_replace($sep, '', $title);
    }

    public static function wp_footer() {
	self::$isFooter=true;
	if(themify_is_themify_theme()){
	    echo "\n\n", themify_get('setting-footer_html');
	}
    }
    
    private static function get_gzip_htaccess(){
	return PHP_EOL.'#BEGIN_GZIP_OUTPUT_BY_THEMIFY
	    <IfModule mod_rewrite.c>
		    <Files *.js.gz>
			AddType "text/javascript" .gz
			AddEncoding gzip .gz
		    </Files>
		    <Files *.css.gz>
			AddType "text/css" .gz
			AddEncoding gzip .gz
		    </Files>
		    <Files *.svg.gz>
			AddType "image/svg+xml" .gz
			AddEncoding gzip .gz
		    </Files>
		    <Files *.json.gz>
			AddType "application/json" .gz
			AddEncoding gzip .gz
		    </Files>
		    # Serve pre-compressed gzip assets
		    RewriteCond %{HTTP:Accept-Encoding} gzip
		    RewriteCond %{REQUEST_FILENAME}.gz -f
		    RewriteRule ^(.*)$ $1.gz [QSA,L]
	    </IfModule>
	    #END_GZIP_OUTPUT_BY_THEMIFY
	    ' . PHP_EOL;
    }

    private static function get_mod_rewrite() {

	return PHP_EOL . '#BEGIN_GZIP_COMPRESSION_BY_THEMIFY
                <IfModule mod_deflate.c>
                        #add content typing
                        AddType application/x-gzip .gz .tgz
                        AddEncoding x-gzip .gz .tgz
                        # Insert filters
                        AddOutputFilterByType DEFLATE text/plain
                        AddOutputFilterByType DEFLATE text/html
                        AddOutputFilterByType DEFLATE text/xml
                        AddOutputFilterByType DEFLATE text/css
                        AddOutputFilterByType DEFLATE application/xml
                        AddOutputFilterByType DEFLATE application/xhtml+xml
                        AddOutputFilterByType DEFLATE application/rss+xml
                        AddOutputFilterByType DEFLATE application/javascript
                        AddOutputFilterByType DEFLATE application/x-javascript
                        AddOutputFilterByType DEFLATE application/x-httpd-php
                        AddOutputFilterByType DEFLATE application/x-httpd-fastphp
                        AddOutputFilterByType DEFLATE image/svg+xml
                        # Drop problematic browsers
                        BrowserMatch ^Mozilla/4 gzip-only-text/html
                        BrowserMatch ^Mozilla/4\.0[678] no-gzip
                        BrowserMatch \bMSI[E] !no-gzip !gzip-only-text/html
                        <IfModule mod_headers.c>
                            # Make sure proxies don\'t deliver the wrong content
                            Header append Vary User-Agent env=!dont-vary
                        </IfModule>
                </IfModule>
                # END GZIP COMPRESSION
                ## EXPIRES CACHING ##
		
                <IfModule mod_expires.c>
                    ExpiresActive On
                    ExpiresDefault "access plus 1 week"
                    ExpiresByType image/jpg "access plus 1 year"
                    ExpiresByType image/jpeg "access plus 1 year"
                    ExpiresByType image/gif "access plus 1 year"
                    ExpiresByType image/png "access plus 1 year"
                    ExpiresByType image/svg+xml "access plus 1 month"
                    ExpiresByType text/css "access plus 1 month"
                    ExpiresByType text/html "access plus 1 minute"
                    ExpiresByType text/plain "access plus 1 month"
                    ExpiresByType text/x-component "access plus 1 month"
                    ExpiresByType text/javascript "access plus 1 month"
                    ExpiresByType text/x-javascript "access plus 1 month"
                    ExpiresByType application/pdf "access plus 1 month"
                    ExpiresByType application/javascript "access plus 1 months"
                    ExpiresByType application/x-javascript "access plus 1 months"
                    ExpiresByType application/x-shockwave-flash "access plus 1 month"
                    ExpiresByType image/x-icon "access plus 1 year"
                    ExpiresByType application/xml "access plus 0 seconds"
                    ExpiresByType application/json "access plus 0 seconds"
                    ExpiresByType application/ld+json "access plus 0 seconds"
                    ExpiresByType application/xml "access plus 0 seconds"
                    ExpiresByType text/xml "access plus 0 seconds"
                    ExpiresByType application/x-web-app-manifest+json "access plus 0 seconds"
                    ExpiresByType text/cache-manifest "access plus 0 seconds"
                    ExpiresByType audio/ogg "access plus 1 month"
                    ExpiresByType video/mp4 "access plus 1 month"
                    ExpiresByType video/ogg "access plus 1 month"
                    ExpiresByType video/webm "access plus 1 month"
                    ExpiresByType application/atom+xml "access plus 1 hour"
                    ExpiresByType application/rss+xml "access plus 1 hour"
                    ExpiresByType application/font-woff "access plus 1 month"
                    ExpiresByType application/vnd.ms-fontobject "access plus 1 month"
                    ExpiresByType application/x-font-ttf "access plus 1 month"
                    ExpiresByType font/opentype "access plus 1 month"
                    </IfModule>
                    #Alternative caching using Apache`s "mod_headers", if it`s installed.
                    #Caching of common files - ENABLED
                    <IfModule mod_headers.c>
                    <FilesMatch "\.(ico|pdf|flv|swf|js|css|gif|png|jpg|jpeg|ico|txt|html|htm)$">
                    Header set Cache-Control "max-age=2592000, public"
                    </FilesMatch>
                    </IfModule>
		    
                    <IfModule mod_headers.c>
                      <FilesMatch "\.(js|css|xml|gz)$">
                        Header append Vary Accept-Encoding
                      </FilesMatch>
		      # Set Keep Alive Header
		      Header set Connection keep-alive
                    </IfModule>

                    <IfModule mod_gzip.c>
                      mod_gzip_on Yes
                      mod_gzip_dechunk Yes
                      mod_gzip_item_include file \.(html?|txt|css|js|php|pl)$
                      mod_gzip_item_include handler ^cgi-script$
                      mod_gzip_item_include mime ^text/.*
                      mod_gzip_item_include mime ^application/x-javascript.*
                      mod_gzip_item_exclude mime ^image/.*
                      mod_gzip_item_exclude rspheader ^Content-Encoding:.*gzip.*
                    </IfModule>

                    # If your server don`t support ETags deactivate with "None" (and remove header)
                    <IfModule mod_expires.c>
                      <IfModule mod_headers.c>
                        Header unset ETag
                      </IfModule>
                      FileETag None
                    </IfModule>
                    ## EXPIRES CACHING ##
                    #END_GZIP_COMPRESSION_BY_THEMIFY
                ' . PHP_EOL;
    }

    public static function rewrite_htaccess($remove = false) {
	$wp_filesystem = Themify_Filesystem::get_instance();
	$htaccess_file = self::getHtaccessFile();
	if ($wp_filesystem->execute->exists($htaccess_file) && $wp_filesystem->execute->is_writable($htaccess_file)) {
	    $rules = $wp_filesystem->execute->get_contents($htaccess_file);
	    $startOutputTag = '#BEGIN_GZIP_OUTPUT_BY_THEMIFY';
	    $endOutputTag = '#END_GZIP_OUTPUT_BY_THEMIFY';
	    
	    $startGzipTag='#BEGIN_GZIP_COMPRESSION_BY_THEMIFY';
	    $endGzipTag='#END_GZIP_COMPRESSION_BY_THEMIFY';
	    $hasChange=false;
	    if(!$remove){
		$rules = trim($rules);
		if(strpos($rules,$startOutputTag) === false){
		    $rules.=self::get_gzip_htaccess();
		    $hasChange=true;
		}
		if(strpos($rules, 'mod_deflate.c') === false && strpos($rules, 'mod_gzip.c') === false){
		    $rules.= self::get_mod_rewrite();
		    $hasChange=true;
		}
	    }
	    else{	 
		if(strpos($rules, $startOutputTag) !== false){
		    $startsAt = strpos($rules,$startOutputTag);
		    $endsAt = strpos($rules,$endOutputTag, $startsAt);
		    $textToDelete = substr($rules, $startsAt, ($endsAt + strlen($endOutputTag)) - $startsAt);
		    $rules = str_replace($textToDelete, '', $rules);
		    $hasChange=true;
		}
		if(strpos($rules, $startGzipTag) !== false){
		    $startsAt = strpos($rules,$startGzipTag);
		    $endsAt = strpos($rules,$endGzipTag, $startsAt);
		    $textToDelete = substr($rules, $startsAt, ($endsAt + strlen($endGzipTag)) - $startsAt);
		    $rules = str_replace($textToDelete, '', $rules);
		    $hasChange=true;
		}
	    }
	    if($hasChange===true){
		return $wp_filesystem->execute->put_contents($htaccess_file, $rules);
	    }
	}
    }
    
    public static function getHtaccessFile(){
	return get_home_path() . '.htaccess';
    }

}

new Themify_Enqueue_Assets();
