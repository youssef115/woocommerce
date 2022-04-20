<?php

if( ! function_exists( 'themify_get_google_font_lists' ) ) :

/**
 * Get google font lists
 * @return array
 */
function themify_get_google_font_lists() {
	if( defined( 'THEMIFY_GOOGLE_FONTS' ) && THEMIFY_GOOGLE_FONTS != true ) {
		return array();
	}

	/* cache */
	static $fonts = null;
	if( null === $fonts ) {
		$fonts = themify_grab_remote_google_fonts();
	}

	return $fonts;
}

/**
 * Return file to use depending if user selected Recommended or Full list in theme settings.
 *
 * @since 2.1.7
 *
 * @return string
 */
function themify_get_google_fonts_file() {
	if( apply_filters( 'themify_google_fonts_full_list', false ) ) {
		$fonts = dirname( __FILE__ ) . '/google-fonts.php';
	} else {
		$fonts = dirname( __FILE__ ) . '/google-fonts-recommended.php';
	}

	/**
	 * Filters the file loaded.
	 * Useful for recovery in case user loaded Full List and their server can't manage it.
	 * @param string $fonts
	 */
	return apply_filters( 'themify_google_fonts_file', $fonts );
}

/**
 * Grab google fonts lists from api
 * @return array
 */
function themify_grab_remote_google_fonts() {
	$fonts_file_path = themify_get_google_fonts_file();
	$subsets = themify_get_font_subsets();
	$subsets_count = count( $subsets );

	$fonts = array();
	$results = include( $fonts_file_path );
	if ( $results !== false ) {
		foreach ( $results as $font ) {
			// If user specified additional subsets
			if ( $subsets_count > 1) {
				$font_subsets = $font['subsets'];
				$subsets_match = true;
				// Check that all specified subsets are available in this font
				foreach ( $subsets as $subset ) {
					if ( ! in_array( $subset, $font_subsets,true ) ) {
						$subsets_match = false;
						break;
					}
				}
				// Ok, this font supports all subsets requested by user, add it to the list
				if( $subsets_match===true ) {
					$fonts[] = array(
						'family' => $font['label'],
						'variant' => $font['variants']
					);
				}
			} else {
				$fonts[] = array(
					'family' => $font['label'],
					'variant' => $font['variants']
				);
			}
		}
	}

	return $fonts;
}

/**
 * Returns a list of font subsets enabled
 *
 * @return array
 */
function themify_get_font_subsets() {
	return apply_filters( 'themify_google_fonts_subsets', array( 'latin' ) );
}

/**
 * Check if given value is google fonts or web safe fonts
 * @param string $value
 * @return boolean
 */
function themify_is_google_fonts( $value ) {
	$found = false;
	$themify_gfonts = themify_get_google_font_lists();
	if ( !empty( $themify_gfonts )) {
		foreach ( $themify_gfonts as $font ) {
			if ( $font['family'] === $value ) {
			    $found = true;
			    break;
			}
		}
	}
	return $found;
}

/**
 * Get font default variant
 * @param $family
 * @return string
 */
function themify_get_gfont_variant( $family ) {
	$variant = 400;
	$themify_gfonts = themify_get_google_font_lists();
	if ( isset( $themify_gfonts ) && is_array( $themify_gfonts ) ) {
		foreach ($themify_gfonts as $v) {
			if ( $v['family'] === $family ) {
				$variant = $v['variant'];
				break;
			}
		}
	}

	return $variant;
}

/**
 * Returns a list of Google Web Fonts
 * @return array
 * @since 1.5.6
 */
function themify_get_google_web_fonts_list() {
	$google_fonts_list = array(
		array( 'value' => '', 'name' => '' ),
		array(
			'value' => '',
			'name' => '--- ' . __( 'Google Fonts', 'themify' ) . ' ---'
		)
	);
	$fonts = themify_get_google_font_lists();
	foreach ( $fonts as $font ) {
		if ( ! empty( $font['family'] ) ) {
			$google_fonts_list[] = array(
				'value' => $font['family'],
				'name' => $font['family'],
				'variant' => $font['variant']
			);
		}
	}

	return apply_filters( 'themify_get_google_web_fonts_list', $google_fonts_list );
}

/**
 * Returns a list of web safe fonts
 * @param bool $only_names Whether to return only the array keys or the values as well
 * @return mixed|void
 * @since 1.0.0
 */
function themify_get_web_safe_font_list( $only_names = false ) {
	$web_safe_font_names = array(
		"Arial, Helvetica, sans-serif",
		"Verdana, Geneva, sans-serif",
		"Georgia, 'Times New Roman', Times, serif",
		"'Times New Roman', Times, serif",
		"Tahoma, Geneva, sans-serif",
		"'Trebuchet MS', Arial, Helvetica, sans-serif",
		"Palatino, 'Palatino Linotype', 'Book Antiqua', serif",
		"'Lucida Sans Unicode', 'Lucida Grande', sans-serif"
	);

	if( ! $only_names ) {
		$web_safe_fonts = array(
			array( 'value' => 'default', 'name' => '', 'selected' => true ),
			array( 'value' => '', 'name' => '--- '.__( 'Web Safe Fonts', 'themify' ) . ' ---' )
		);

		foreach( $web_safe_font_names as $font ) {
			$web_safe_fonts[] = array(
				'value' => $font,
				'name' => $font
			);
		}
	} else {
		$web_safe_fonts = $web_safe_font_names;
	}

	return apply_filters( 'themify_get_web_safe_font_list', $web_safe_fonts );
}

/**
 * Enqueue Google fonts (if any) on the page
 *
 * @uses themify_google_fonts filter
 */
function themify_enqueue_google_fonts() {
	/* do not enqueue if google fonts are disabled */
	if ( defined( 'THEMIFY_GOOGLE_FONTS' ) && THEMIFY_GOOGLE_FONTS != true ) {
		return;
	}
	$path = themify_get_google_fonts_url(apply_filters( 'themify_google_fonts', array() ));
	if ( $path !== false ) {
		if ( function_exists( 'themify_enque_style' ) ) {
			themify_enque_style( 'themify-google-fonts', $path, null, null, null, true );
		} else {
			wp_enqueue_style( 'themify-google-fonts', $path );
		}
	}
}

function themify_get_google_fonts_url(array $fonts=array()){
    $res = array();
    foreach ( $fonts as $key => $font ) {
	    if ( ! empty( $font ) && preg_match( '/^\w/', $font ) ) {
		/* fix the delimiter with multiple weight variants, it should use `,` and not `:`
		    reset the delimiter between font name and first variant */
		$res[ $key ] = preg_replace( '/,/', ':', str_replace( ':', ',', $font ), 1 );
	    }
    }
    if ( ! empty( $res ) ) {
	$path = ( is_ssl() ? 'https' : 'http' ) . '://fonts.googleapis.com/css?family=' . implode( '|', $res );
	if( ($subsets = themify_get_font_subsets()) ) {
	    $path .= '&subset=' . str_replace( ' ', '', implode( ',', $subsets ) );
	}
	return $path;
    }
    return false;
}
add_action( 'wp_enqueue_scripts', 'themify_enqueue_google_fonts', 30 );
endif;