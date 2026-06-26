<?php
/**
 * _sumun functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package _sumun
 */

define( 'BASE_CIENTIFICA_BLOCK_ID', 26809 );
define( 'EQUIPO_ID', 2013 );
define( 'PAGE_404_ID', 65 );

if ( ! function_exists( 'smn_styles' ) ) :

	/**
	 * Enqueue styles.
	 *
	 * @return void
	 */
	function smn_styles() {
		// Register theme stylesheet.
		$theme_version = wp_get_theme()->get( 'Version' );

		$version_string = is_string( $theme_version ) ? $theme_version : false;
		wp_register_style(
			'smn-style',
			get_template_directory_uri() . '/style.css',
			array(),
			$version_string
		);

		// Enqueue theme stylesheet.
		wp_enqueue_style( 'smn-style' );
	}

endif;

add_action( 'wp_enqueue_scripts', 'smn_styles' );



// Enqueue scripts
require get_template_directory() . '/inc/smn_enqueue-scripts.php';

/**
 * Implement the theme support features.
 */
require get_template_directory() . '/inc/smn_theme-support.php';

// Register blocks
require get_template_directory() . '/inc/smn_register-blocks.php';

// Shortcodes
require get_template_directory() . '/inc/smn_shortcodes.php';

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';
require get_template_directory() . '/inc/smn_hooks.php';

/**
 * Register widget area.
 */
require get_template_directory() . '/inc/smn_widget-area.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Security
 */
require get_template_directory() . '/inc/smn_security.php';

/* Fechas en CF7 */
require get_template_directory() . '/inc/smn_cf7_fechas_formularios.php';


// Testing block theme functionality in a classic theme
//include_once get_template_directory() . '/functions-hybrid.php';