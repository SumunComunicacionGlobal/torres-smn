<?php
/**
 * _sumun functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package _sumun
 */

if (!defined('ABSPATH')) {
    exit;
}

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.0.0' );
}

add_action( 'init', 'smn_add_excerpts_to_pages' );
function smn_add_excerpts_to_pages() {
     add_post_type_support( 'page', 'excerpt' ); //change page with your post type slug.
}

/**
 * Permite ejecutar shortcodes dentro de template parts y plantillas de página.
 */
function smn_enable_shortcodes_in_template_parts( $block_content, $block ) {
	if ( empty( $block_content ) || false === strpos( $block_content, '[' ) ) {
		return $block_content;
	}

	return do_shortcode( $block_content );
}
add_filter( 'render_block_core/template-part', 'smn_enable_shortcodes_in_template_parts', 10, 2 );
add_filter( 'render_block_core/post-content', 'smn_enable_shortcodes_in_template_parts', 10, 2 );



/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function smn_hybrid_setup() {
	/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on _sumun, use a find and replace
		* to change '_sumun' to the name of your theme in all the template files.
		*/
	load_theme_textdomain( '_sumun', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
	// add_theme_support( 'title-tag' );

	/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		*/
	add_theme_support( 'post-thumbnails' );

	// Default thumbnail size
	add_image_size( 'img-header', 640, 480, true );
	add_image_size( 'img-card', 380, 192, true );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'primary-menu' => esc_html__( 'Primary', '_sumun' ),
		)
	);

	/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);

	// Add excerpt to pages
	add_post_type_support( 'page', 'excerpt' );

	// Enable wide and full align options in the block editor.
	add_theme_support( 'align-wide' );
	
	// Custom excerpt length
	function custom_excerpt_length( $length ) {
		return 50; // Cambia este número al número de palabras que desees
	}
	add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );
}

add_action( 'after_setup_theme', 'smn_hybrid_setup' );