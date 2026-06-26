<?php 
// Shortcodes 

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode( 'extracto_literal', 'smn_extracto_literal_shortcode' );
function smn_extracto_literal_shortcode() {

    global $post;
    if ( ! $post ) {
        return '';
    }

    $excerpt = $post->post_excerpt;
    if ( $excerpt ) {
        return wpautop( $excerpt );
    }

    return '';

}