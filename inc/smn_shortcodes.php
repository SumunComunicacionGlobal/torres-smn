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

add_shortcode( 'youtube_video', 'smn_youtube_video_shortcode' );

function smn_get_youtube_video_id_from_url( $url ) {
    if ( ! is_string( $url ) || '' === trim( $url ) ) {
        return '';
    }

    $url = trim( $url );

    if ( preg_match( '~(?:youtube\.com/(?:watch\?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $matches ) ) {
        return $matches[1];
    }

    $parts = wp_parse_url( $url );
    if ( empty( $parts['query'] ) ) {
        return '';
    }

    parse_str( $parts['query'], $query_args );
    if ( ! empty( $query_args['v'] ) && preg_match( '/^[A-Za-z0-9_-]{11}$/', $query_args['v'] ) ) {
        return $query_args['v'];
    }

    return '';
}

function smn_youtube_video_shortcode( $atts = array() ) {
    if ( ! function_exists( 'get_field' ) ) {
        return '';
    }

    $atts = shortcode_atts(
        array(
            'field' => 'youtube_url',
            'post_id' => 0,
        ),
        $atts,
        'youtube_video'
    );

    $post_id = (int) $atts['post_id'];
    if ( $post_id <= 0 ) {
        $post_id = get_the_ID();
    }
    if ( $post_id <= 0 ) {
        $post_id = get_queried_object_id();
    }

    if ( $post_id <= 0 ) {
        return '';
    }

    $field_name = is_string( $atts['field'] ) && '' !== trim( $atts['field'] )
        ? trim( $atts['field'] )
        : 'youtube_url';

    $youtube_url = get_field( $field_name, $post_id );
    if ( ! is_string( $youtube_url ) || '' === trim( $youtube_url ) ) {
        return '';
    }

    $video_id = smn_get_youtube_video_id_from_url( $youtube_url );
    if ( '' === $video_id ) {
        return '';
    }

    $embed_url = 'https://www.youtube-nocookie.com/embed/' . rawurlencode( $video_id ) . '?rel=0';

    return sprintf(
        '<figure class="wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube"><div class="wp-block-embed__wrapper"><iframe title="YouTube video player" src="%1$s" frameborder="0" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></div></figure>',
        esc_url( $embed_url )
    );
}

