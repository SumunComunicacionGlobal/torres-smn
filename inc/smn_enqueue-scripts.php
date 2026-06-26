<?php
/**
 * Enqueue scripts
 */

 function smn_scripts() {

	$theme_version = wp_get_theme()->get( 'Version' );

	wp_enqueue_script( 'jquery' );
	wp_enqueue_script(
		'beforeafter',
		get_template_directory_uri() . '/assets/js/beforeafter.jquery.min.js',
		array(),
		'2.0.0',
		true
	);
	wp_enqueue_script( '_sumun-js', get_template_directory_uri() . '/assets/js/_sumun.js', array( 'jquery' ), $theme_version, true );
	wp_enqueue_script( 'smn_sumun-navigation', get_template_directory_uri() . '/assets/js/navigation.js', array(), $theme_version, true );

	// if ( has_block( 'cb/carousel' ) ) {
        wp_enqueue_style( 'slick-css', get_template_directory_uri() . '/assets/slick/slick.min.css' );
        wp_enqueue_script( 'slick-js', get_template_directory_uri() . '/assets/slick/slick.min.js', array('jquery'), null, true );
	wp_enqueue_script( 'slick-init-js', get_template_directory_uri() . '/assets/slick/init.js', array( 'jquery', 'slick-js' ), null, true );
	wp_enqueue_script(
		'smn-grid-slick-mobile',
		get_template_directory_uri() . '/assets/js/grid-slick-mobile.js',
		array( 'jquery', 'slick-js' ),
		filemtime( get_template_directory() . '/assets/js/grid-slick-mobile.js' ),
		true
	);
    // }

	if ( is_singular( 'equipo' ) ) {
		wp_enqueue_script(
			'smn-equipo-accordion',
			get_template_directory_uri() . '/assets/js/equipo-accordion.js',
			array(),
			filemtime( get_template_directory() . '/assets/js/equipo-accordion.js' ),
			true
		);
	}

}
add_action( 'wp_enqueue_scripts', 'smn_scripts' );

/** 
* Gutenberg scripts
*/
function smn_register_editor_block_styles() {
	wp_enqueue_script(
		'be-editor-block-styles',
		get_stylesheet_directory_uri() . '/assets/js/editor-block-styles.js',
		array( 'wp-blocks', 'wp-dom-ready' ),
		filemtime( get_stylesheet_directory() . '/assets/js/editor-block-styles.js' ),
		true
	);

	wp_enqueue_script(
		'be-editor-query-post-status',
		get_stylesheet_directory_uri() . '/assets/js/editor-query-post-status.js',
		array( 'wp-blocks', 'wp-dom-ready', 'wp-hooks', 'wp-compose', 'wp-element', 'wp-components', 'wp-block-editor' ),
		filemtime( get_stylesheet_directory() . '/assets/js/editor-query-post-status.js' ),
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'smn_register_editor_block_styles' );

function smn_gutenberg_scripts() {
	global $pagenow;

	if ( function_exists( 'get_current_screen' ) ) {
		$screen = get_current_screen();
		$is_site_editor_screen = $screen && (
			false !== strpos( (string) $screen->base, 'site-editor' ) ||
			false !== strpos( (string) $screen->id, 'site-editor' ) ||
			false !== strpos( (string) $screen->id, 'gutenberg-edit-site' )
		);

		if ( $is_site_editor_screen ) {
			return;
		}
	}

	if ( 'site-editor.php' === $pagenow ) {
		return;
	}

	wp_enqueue_script(
		'be-editor', 
		get_stylesheet_directory_uri() . '/assets/js/editor.js', 
		array( 'wp-blocks', 'wp-dom', 'wp-dom-ready', 'wp-edit-post' ), 
		filemtime( get_stylesheet_directory() . '/assets/js/editor.js' ),
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'smn_gutenberg_scripts' );

/**
 * GSAP script in WordPress
*/
// wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
function theme_gsap_script(){
    // The core GSAP library
    wp_enqueue_script( 'gsap-js', 'https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js', array(), false, true );
    // ScrollTrigger - with gsap.js passed as a dependency
    wp_enqueue_script( 'gsap-st', 'https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/ScrollTrigger.min.js', array('gsap-js'), false, true );
    // Your animation code file - with gsap.js and gsap-st passed as a dependency
    wp_enqueue_script( 'gsap-js2', get_template_directory_uri() . '/assets/js/gsap.js', array('gsap-js', 'gsap-st'), false, true );
}

add_action( 'wp_enqueue_scripts', 'theme_gsap_script' );


/**
 * Enqueue scripts and styles.
 
function smn_hybrid_scripts() {
	wp_enqueue_style( '_sumun-style', get_stylesheet_uri(), array(), true );
	wp_style_add_data( '_sumun-style', 'rtl', 'replace' );

	

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'smn_hybrid_scripts' );
*/

