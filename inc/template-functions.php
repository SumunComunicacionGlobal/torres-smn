<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package _sumun
 */

if (!defined('ABSPATH')) {
    exit;
}

if ( ! function_exists( 'smn_support' ) ) :

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 *
	 * @return void
	 */
	function smn_support() {

		// Add support for block styles.
		add_theme_support( 'wp-block-styles' );

		// Add support for editor styles.
		add_theme_support( 'editor-styles' );

		// Enqueue editor styles.
		add_editor_style( 'style.css' );
		add_editor_style( 'style-editor.css' );

		// Add support for excerpts in pages.
		add_post_type_support( 'page', 'excerpt' );

		// Explicit support for block-based templates.
		add_theme_support( 'block-templates' );

		// To use your template part inside your theme’s create a .html in /parts
		// and then put the php function "block_template_part( 'part-name' );" where you want to call it.
		// You can also create a template like page.html in /templates. And insert a part inside it: <!-- wp:template-part {"slug":"part-name"} /-->
		add_theme_support( 'block-template-parts' );

	}

endif;

add_action( 'after_setup_theme', 'smn_support' );


/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function smn_hybrid_body_classes( $classes ) {
	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	// Adds a class of no-sidebar when there is no sidebar present.
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}

	if ( is_singular( 'page' ) ) {
		$post_id   = get_queried_object_id();
		$post_type = 'page';

		if ( ! $post_id ) {
			$queried_object = get_queried_object();
			if ( $queried_object instanceof WP_Post ) {
				$post_id = (int) $queried_object->ID;
			}
		}

		if ( ! $post_id && isset( $GLOBALS['post']->ID ) ) {
			$post_id = (int) $GLOBALS['post']->ID;
		}

		if ( $post_id && $post_type ) {
			$taxonomies = array( 'grupo-paginas' );

			foreach ( $taxonomies as $taxonomy ) {
				$terms = wp_get_post_terms( $post_id, $taxonomy );

				if ( is_wp_error( $terms ) || empty( $terms ) ) {
					continue;
				}

				foreach ( $terms as $term ) {
					$term_slug = sanitize_html_class( $term->slug );
					$tax_slug  = sanitize_html_class( $taxonomy );
					$pt_slug   = sanitize_html_class( $post_type );

					$classes[] = $tax_slug . '-' . $term_slug;
					$classes[] = $pt_slug . '-' . $tax_slug . '-' . $term_slug;
				}
			}
		}
	}

	return array_values( array_unique( $classes ) );
}
add_filter( 'body_class', 'smn_hybrid_body_classes' );

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function smn_hybrid_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'smn_hybrid_pingback_header' );

/**
 * Usa la página definida en 404_ID como contenido de error 404.
 *
 * @param string $template Ruta de la plantilla actual.
 * @return string
 */
function smn_use_page_as_404( $template ) {
	if ( ! is_404() || ! defined( 'PAGE_404_ID' ) ) {
		return $template;
	}

	$page_id = (int) PAGE_404_ID;
	if ( $page_id <= 0 ) {
		return $template;
	}

	$page = get_post( $page_id );
	if ( ! $page || 'page' !== $page->post_type || 'publish' !== $page->post_status ) {
		return $template;
	}

	global $post, $wp_query;
	$post = $page;
	$wp_query->post      = $post;
	$wp_query->posts     = array( $post );
	$wp_query->post_count = 1;
	setup_postdata( $post );

	$page_template = get_page_template();

	return $page_template ? $page_template : $template;
}
add_filter( 'template_include', 'smn_use_page_as_404', 99 );

