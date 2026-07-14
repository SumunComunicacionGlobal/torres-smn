<?php

// add_filter( 'wpcf7_autop_or_not', '__return_false' );

// Agrega un filtro para el bloque de consulta de WordPress
// que muestra los posts relacionados en la página de un post y los filtra por categorías
add_filter('render_block_data', function ($parsed_block) {
    if (
        is_single() &&
        isset($parsed_block['blockName']) &&
        $parsed_block['blockName'] === 'core/query' &&
        isset($parsed_block['attrs']['className']) &&
        strpos($parsed_block['attrs']['className'], 'is-style-is-related-posts') !== false
    ) {
        $category_ids = wp_get_post_categories(get_the_ID());

        if (!empty($category_ids)) {
            $parsed_block['attrs']['query']['categoryIds'] = $category_ids;
            $parsed_block['attrs']['query']['exclude'] = [get_the_ID()];
            $parsed_block['attrs']['query']['sticky'] = '';
            $parsed_block['attrs']['query']['perPage'] = 6;
        }
    }

    return $parsed_block;
});

// add_filter( 'render_block_data', 'sumun_full_excerpt_for_podcast_and_pages', 20 );
// function sumun_full_excerpt_for_podcast_and_pages( $parsed_block ) {
//     if ( empty( $parsed_block['blockName'] ) || 'core/post-excerpt' !== $parsed_block['blockName'] ) {
//         return $parsed_block;
//     }

//     $post_id = get_the_ID();
//     if ( ! $post_id ) {
//         return $parsed_block;
//     }

//     $post_type = get_post_type( $post_id );
//     if ( ! in_array( $post_type, array( 'podcast', 'page' ), true ) ) {
//         return $parsed_block;
//     }

//     if ( empty( $parsed_block['attrs'] ) || ! is_array( $parsed_block['attrs'] ) ) {
//         $parsed_block['attrs'] = array();
//     }

//     // Use a high limit so core/post-excerpt does not trim user-provided excerpts.
//     $parsed_block['attrs']['excerptLength'] = 9999;

//     return $parsed_block;
// }

add_filter( 'render_block', 'sumun_hide_empty_post_excerpt_block', 9, 2 );
function sumun_hide_empty_post_excerpt_block( $block_content, $block ) {
    if ( empty( $block['blockName'] ) || 'core/post-excerpt' !== $block['blockName'] ) {
        return $block_content;
    }

    $post = get_post();
    if ( ! $post || empty( $post->post_excerpt ) ) {
        return '';
    }

    if ( '' === trim( wp_strip_all_tags( (string) $post->post_excerpt ) ) ) {
        return '';
    }

    return $block_content;
}



// Do not inject HTML into `the_title` globally.
// Some contexts escape titles and would print tags as text.
// add_filter( 'the_title', 'sumun_split_title_by_colon', 10, 2 );
function sumun_split_title_by_colon( $title, $post_id ) {
    if ( is_admin() ) {
        return $title;
    }

    return sumun_get_formatted_podcast_title( $title, $post_id );
}

function sumun_get_formatted_podcast_title( $title, $post_id ) {
    if ( strpos( $title, ':' ) === false ) {
        return $title;
    }

    $post_type = get_post_type( $post_id );
    if ( 'podcast' !== $post_type ) {
        return $title;
    }

    // --- 1) Dividir en parte principal y colaborador ---
    // Tolerante: permite "|", " |", "| ", " | "
    $parts = preg_split('/\s*\|\s*/', $title);

    $title_without_colab = trim( $parts[0] );
    $colaborador = isset( $parts[1] ) ? trim( $parts[1] ) : '';

    // --- 2) Dividir la primera parte por el primer ":" ---
    $pos = strpos( $title_without_colab, ':' );
    $first_part = substr( $title_without_colab, 0, $pos + 1 );
    $second_part = substr( $title_without_colab, $pos + 1 );

    // --- 3) Montar HTML final ---
    $new_title = '<span class="numero-episodio">' . esc_html( $first_part ) . '</span>'
               . esc_html( $second_part );

    if ( $colaborador ) {
        $new_title .= ' <span class="colaborador">' . esc_html( $colaborador ) . '</span>';
    }

    return $new_title;
}

add_filter( 'render_block', 'sumun_format_post_title_block', 10, 2 );
function sumun_format_post_title_block( $block_content, $block ) {

    if ( is_admin() || empty( $block['blockName'] ) || 'core/post-title' !== $block['blockName'] ) {
        return $block_content;
    }

    

    $post_id = ! empty( $block['attrs']['postId'] ) ? (int) $block['attrs']['postId'] : get_the_ID();
    if ( ! $post_id ) {
        return $block_content;
    }

    $has_card_title_class = false;
    if ( ! empty( $block['attrs']['className'] ) && preg_match( '/(?:^|\s)card-title(?:\s|$)/', $block['attrs']['className'] ) ) {
        $has_card_title_class = true;
    } elseif ( strpos( $block_content, 'card-title' ) !== false ) {
        $has_card_title_class = true;
    }

    if ( 'page' === get_post_type( $post_id ) && $has_card_title_class ) {
        $card_title = trim( (string) get_post_meta( $post_id, 'card_title', true ) );

        if ( '' !== $card_title ) {
            $card_title = esc_html( $card_title );

            if ( preg_match( '/<a\b[^>]*>.*?<\/a>/is', $block_content ) ) {
                return preg_replace( '/(<a\b[^>]*>)(.*?)(<\/a>)/is', '$1' . $card_title . '$3', $block_content, 1 );
            }

            return preg_replace( '/(^\s*<[^>]+>)(.*?)(<\/[^>]+>\s*$)/is', '$1' . $card_title . '$3', $block_content, 1 );
        }
    }

    $formatted_title = sumun_get_formatted_podcast_title( get_the_title( $post_id ), $post_id );
    if ( $formatted_title === get_the_title( $post_id ) ) {
        return $block_content;
    }

    if ( preg_match( '/<a\b[^>]*>.*?<\/a>/is', $block_content ) ) {
        return preg_replace( '/(<a\b[^>]*>)(.*?)(<\/a>)/is', '$1' . $formatted_title . '$3', $block_content, 1 );
    }

    return preg_replace( '/(^\s*<[^>]+>)(.*?)(<\/[^>]+>\s*$)/is', '$1' . $formatted_title . '$3', $block_content, 1 );
}

add_filter( 'render_block', 'sumun_replace_cover_card_bg_img_with_card_img', 13, 3 );
function sumun_replace_cover_card_bg_img_with_card_img( $block_content, $block, $instance ) {
    if ( is_admin() || empty( $block['blockName'] ) || 'core/cover' !== $block['blockName'] ) {
        return $block_content;
    }

    $has_card_bg_class = false;
    if ( ! empty( $block['attrs']['className'] ) && is_string( $block['attrs']['className'] ) ) {
        $has_card_bg_class = (bool) preg_match( '/(^|\s)card-bg-img(\s|$)/', $block['attrs']['className'] );
    }

    if ( ! $has_card_bg_class && false !== strpos( $block_content, 'card-bg-img' ) ) {
        $has_card_bg_class = true;
    }

    if ( ! $has_card_bg_class ) {
        return $block_content;
    }

    $post_id = 0;
    if ( is_object( $instance ) && ! empty( $instance->context['postId'] ) ) {
        $post_id = (int) $instance->context['postId'];
    }

    if ( ! $post_id ) {
        $post_id = (int) get_the_ID();
    }

    if ( ! $post_id ) {
        return $block_content;
    }

    $card_img_id = (int) get_post_meta( $post_id, 'card_img', true );
    if ( $card_img_id <= 0 ) {
        return $block_content;
    }

    $card_src_data = wp_get_attachment_image_src( $card_img_id, 'full' );
    if ( empty( $card_src_data[0] ) ) {
        return $block_content;
    }

    $card_src = (string) $card_src_data[0];
    $card_srcset = wp_get_attachment_image_srcset( $card_img_id, 'full' );
    $card_sizes = wp_get_attachment_image_sizes( $card_img_id, 'full' );

    $processor = new WP_HTML_Tag_Processor( $block_content );
    $updated = false;

    if ( $processor->next_tag( 'img' ) ) {
        do {
            $img_class = (string) $processor->get_attribute( 'class' );
            if ( false === strpos( $img_class, 'wp-block-cover__image-background' ) ) {
                continue;
            }

            $processor->set_attribute( 'src', esc_url( $card_src ) );

            if ( ! empty( $card_srcset ) ) {
                $processor->set_attribute( 'srcset', esc_attr( $card_srcset ) );
            } else {
                $processor->remove_attribute( 'srcset' );
            }

            if ( ! empty( $card_sizes ) ) {
                $processor->set_attribute( 'sizes', esc_attr( $card_sizes ) );
            }

            $img_class = preg_replace( '/\bwp-image-\d+\b/', 'wp-image-' . $card_img_id, $img_class );
            if ( false === strpos( $img_class, 'wp-image-' ) ) {
                $img_class = trim( $img_class . ' wp-image-' . $card_img_id );
            }
            $processor->set_attribute( 'class', trim( $img_class ) );

            $updated = true;
            break;
        } while ( $processor->next_tag( 'img' ) );
    }

    if ( ! $updated ) {
        return $block_content;
    }

    return $processor->get_updated_html();
}

add_filter( 'render_block', 'sumun_remove_post_terms_links', 11, 2 );
function sumun_remove_post_terms_links( $block_content, $block ) {
    if ( empty( $block['blockName'] ) || 'core/post-terms' !== $block['blockName'] ) {
        return $block_content;
    }

    $taxonomy = '';
    if ( ! empty( $block['attrs']['term'] ) ) {
        $taxonomy = $block['attrs']['term'];
    } elseif ( ! empty( $block['attrs']['taxonomy'] ) ) {
        $taxonomy = $block['attrs']['taxonomy'];
    }

    if ( 'temporada' !== $taxonomy ) {
        return $block_content;
    }

    // Keep terms text, remove only anchor tags.
    return preg_replace( '/<a\b[^>]*>(.*?)<\/a>/is', '$1', $block_content );
}

function sumun_get_current_post_id() {
    $post_id = get_the_ID();
    if ( ! $post_id ) {
        $post_id = get_queried_object_id();
    }

    return (int) $post_id;
}

function sumun_get_pdf_meta_url( $post_id ) {
    $pdf_meta = get_post_meta( $post_id, 'pdf', true );

    if ( empty( $pdf_meta ) ) {
        return '';
    }

    if ( is_numeric( $pdf_meta ) ) {
        $attachment_url = wp_get_attachment_url( (int) $pdf_meta );
        return $attachment_url ? $attachment_url : '';
    }

    if ( is_array( $pdf_meta ) ) {
        if ( ! empty( $pdf_meta['url'] ) ) {
            return esc_url_raw( $pdf_meta['url'] );
        }

        return '';
    }

    if ( is_string( $pdf_meta ) ) {
        $pdf_meta = trim( $pdf_meta );

        if ( is_numeric( $pdf_meta ) ) {
            $attachment_url = wp_get_attachment_url( (int) $pdf_meta );
            return $attachment_url ? $attachment_url : '';
        }

        return esc_url_raw( $pdf_meta );
    }

    return '';
}

add_filter( 'render_block', 'sumun_handle_base_cientifica_and_pdf_button', 12, 2 );
function sumun_handle_base_cientifica_and_pdf_button( $block_content, $block ) {
    if ( empty( $block['blockName'] ) ) {
        return $block_content;
    }

    $post_id = sumun_get_current_post_id();
    if ( ! $post_id ) {
        return $block_content;
    }

    $block_name = $block['blockName'];

    if ( 'core/block' === $block_name ) {
        if ( is_admin() ) {
            return $block_content;
        }

        $ref = isset( $block['attrs']['ref'] ) ? (int) $block['attrs']['ref'] : 0;
        if ( ! defined( 'BASE_CIENTIFICA_BLOCK_ID' ) || BASE_CIENTIFICA_BLOCK_ID !== $ref ) {
            return $block_content;
        }

        $pdf_url = sumun_get_pdf_meta_url( $post_id );
        if ( empty( $pdf_url ) ) {
            return '';
        }

        return $block_content;
    }

    if ( 'core/button' === $block_name ) {
        $class_name = ! empty( $block['attrs']['className'] ) ? $block['attrs']['className'] : '';
        if ( ! is_string( $class_name ) || ! preg_match( '/(^|\s)descarga-pdf(\s|$)/', $class_name ) ) {
            return $block_content;
        }

        $pdf_url = sumun_get_pdf_meta_url( $post_id );
        if ( empty( $pdf_url ) ) {
            return $block_content;
        }

        $processor = new WP_HTML_Tag_Processor( $block_content );
        while ( $processor->next_tag( 'a' ) ) {
            $link_class = (string) $processor->get_attribute( 'class' );
            if ( false === strpos( $link_class, 'wp-block-button__link' ) ) {
                continue;
            }

            $processor->set_attribute( 'href', esc_url( $pdf_url ) );
            break;
        }

        return $processor->get_updated_html();
    }

    return $block_content;
}

add_filter( 'query_loop_block_query_vars', 'sumun_query_loop_post_status', 10, 3 );
function sumun_query_loop_post_status( $query, $block, $page = 1 ) {
    $attrs   = array();
    $context = array();

    if ( is_object( $block ) ) {
        if ( ! empty( $block->parsed_block['attrs'] ) ) {
            $attrs = $block->parsed_block['attrs'];
        }

        if ( ! empty( $block->context['query'] ) && is_array( $block->context['query'] ) ) {
            $context = $block->context['query'];
        }
    } elseif ( is_array( $block ) ) {
        if ( ! empty( $block['attrs'] ) ) {
            $attrs = $block['attrs'];
        }

        if ( ! empty( $block['context']['query'] ) && is_array( $block['context']['query'] ) ) {
            $context = $block['context']['query'];
        }
    }

    $status = '';

    if ( ! empty( $context['status'] ) ) {
        $status = $context['status'];
    } elseif ( ! empty( $context['postStatus'] ) ) {
        $status = $context['postStatus'];
    } elseif ( ! empty( $attrs['smnPostStatus'] ) ) {
        $status = $attrs['smnPostStatus'];
    } elseif ( ! empty( $attrs['query']['postStatus'] ) ) {
        $status = $attrs['query']['postStatus'];
    } elseif ( ! empty( $attrs['query']['status'] ) ) {
        $status = $attrs['query']['status'];
    }

    $current_post_id = (int) get_queried_object_id();
    if ( $current_post_id > 0 ) {
        if ( empty( $query['post__not_in'] ) || ! is_array( $query['post__not_in'] ) ) {
            $query['post__not_in'] = array();
        }

        if ( ! in_array( $current_post_id, $query['post__not_in'], true ) ) {
            $query['post__not_in'][] = $current_post_id;
        }
    }

    if ( empty( $status ) ) {
        return $query;
    }

    $status = sanitize_key( $status );
    $allowed = array( 'publish', 'draft', 'pending', 'future', 'private', 'any' );

    if ( ! in_array( $status, $allowed, true ) ) {
        return $query;
    }

    $query['post_status'] = $status;

    if ( 'any' === $status ) {
        $query['ignore_sticky_posts'] = true;
    } elseif ( 'publish' !== $status ) {
        $query['post_status']         = array( $status );
        $query['ignore_sticky_posts'] = true;
    }

    return $query;
}

// replace breadcrumb block with rank math breadcrumb block
add_filter( 'render_block', 'sumun_replace_breadcrumb_with_rank_math', 10, 2 );
function sumun_replace_breadcrumb_with_rank_math( $block_content, $block ) {
    if ( is_admin() || empty( $block['blockName'] ) || 'core/breadcrumbs' !== $block['blockName'] ) {
        return $block_content;
    }

    if ( ! function_exists( 'rank_math_the_breadcrumbs' ) ) {
        return $block_content;
    }

    return do_shortcode( '[rank_math_breadcrumb]' );
}

add_filter( 'render_block', 'sumun_wrap_rounded_eyebrow_inner', 14, 2 );
function sumun_wrap_rounded_eyebrow_inner( $block_content, $block ) {
    if ( empty( $block['blockName'] ) ) {
        return $block_content;
    }

    if ( ! in_array( $block['blockName'], array( 'core/paragraph', 'core/heading' ), true ) ) {
        return $block_content;
    }

    $class_name = ! empty( $block['attrs']['className'] ) ? $block['attrs']['className'] : '';
    if ( ! is_string( $class_name ) || false === strpos( $class_name, 'is-style-rounded-eyebrow' ) ) {
        return $block_content;
    }

    if ( false !== strpos( $block_content, 'rounded-eyebrow__inner' ) ) {
        return $block_content;
    }

    $pattern = '/^(\s*<(p|h[1-6])\b[^>]*>)(.*?)(<\/\2>\s*)$/is';

    if ( 1 !== preg_match( $pattern, $block_content ) ) {
        return $block_content;
    }

    return preg_replace(
        $pattern,
        '$1<span class="rounded-eyebrow__inner">$3</span>$4',
        $block_content,
        1
    );
}

add_filter( 'render_block', 'sumun_convert_featured_lines_paragraph_to_list', 14, 2 );
function sumun_convert_featured_lines_paragraph_to_list( $block_content, $block ) {
    if ( empty( $block['blockName'] ) || 'core/paragraph' !== $block['blockName'] ) {
        return $block_content;
    }

    $class_name = ! empty( $block['attrs']['className'] ) ? $block['attrs']['className'] : '';
    $has_target_class = is_string( $class_name )
        && preg_match( '/(^|\s)parrafo-lista-destacados(\s|$)/', $class_name );

    if ( ! $has_target_class && false === strpos( $block_content, 'parrafo-lista-destacados' ) ) {
        return $block_content;
    }

    if ( false !== stripos( $block_content, '<ul' ) ) {
        return $block_content;
    }

    if ( 1 !== preg_match( '/^\s*<p\b[^>]*>(.*?)<\/p>\s*$/is', $block_content, $matches ) ) {
        return $block_content;
    }

    $inner_html = (string) $matches[1];
    $lines = preg_split( '/\s*(?:<br\b[^>]*>\s*|\r\n|\r|\n)\s*/i', $inner_html );

    if ( ! is_array( $lines ) || empty( $lines ) ) {
        return $block_content;
    }

    $items = array();
    foreach ( $lines as $line ) {
        $line = trim( (string) $line );
        if ( '' === $line ) {
            continue;
        }

        $items[] = '<li>' . $line . '</li>';
    }

    if ( empty( $items ) ) {
        return $block_content;
    }

    return '<ul class="is-style-check-list hero-check-list">' . implode( '', $items ) . '</ul>';
}

add_filter( 'render_block', 'sumun_unwrap_before_after_group_images', 15, 2 );
function sumun_unwrap_before_after_group_images( $block_content, $block ) {
    if ( empty( $block['blockName'] ) || 'core/group' !== $block['blockName'] ) {
        return $block_content;
    }

    $class_name = '';
    if ( ! empty( $block['attrs']['className'] ) && is_string( $block['attrs']['className'] ) ) {
        $class_name = $block['attrs']['className'];
    }

    $is_before_after = is_string( $class_name )
        && preg_match( '/(^|\s)is-style-before-after(\s|$)/', $class_name );

    if ( ! $is_before_after ) {
        $processor = new WP_HTML_Tag_Processor( $block_content );

        if ( $processor->next_tag() ) {
            $root_class = (string) $processor->get_attribute( 'class' );
            $is_before_after = preg_match( '/(^|\s)is-style-before-after(\s|$)/', $root_class );
        }
    }

    if ( ! $is_before_after ) {
        return $block_content;
    }

    // Replace <figure class="wp-block-image">...<img ...>...</figure> with just <img ...>.
    $pattern = '/<figure\b[^>]*class=("|\")[^"\']*wp-block-image[^"\']*\1[^>]*>\s*(<img\b[^>]*>)\s*(?:<figcaption\b[^>]*>.*?<\/figcaption>\s*)?<\/figure>/is';

    return preg_replace( $pattern, '$2', $block_content );
}

add_filter( 'render_block', 'sumun_add_vimeo_params_to_embed', 10, 2 );
function sumun_add_vimeo_params_to_embed( $block_content, $block ) {
    if ( empty( $block['blockName'] ) ) {
        return $block_content;
    }

    $is_vimeo_block = 'core-embed/vimeo' === $block['blockName'];

    if ( ! $is_vimeo_block && 'core/embed' === $block['blockName'] ) {
        $provider = isset( $block['attrs']['providerNameSlug'] ) ? (string) $block['attrs']['providerNameSlug'] : '';
        $is_vimeo_block = 'vimeo' === $provider;
    }

    if ( ! $is_vimeo_block || false === strpos( (string) $block_content, 'player.vimeo.com/video/' ) ) {
        return $block_content;
    }

    return preg_replace_callback(
        '/(<iframe\b[^>]*\ssrc=("|\'))([^"\']+)(\2[^>]*>)/i',
        function( $matches ) {
            $src = (string) $matches[3];

            if ( false === strpos( $src, 'player.vimeo.com/video/' ) ) {
                return $matches[0];
            }

            $src = add_query_arg(
                array(
                    'title'  => '0',
                    'byline' => '0',
                    'portrait' => '0',
                    // 'controls' => '0',
                    'dnt' => '1',
                    'vimeo_logo' => '0',
                    'interactive_markers' => '0',
                    'fullscreen' => '0',
                    'color' => '0e4f6d',
                    'colors' => '226486,96D8D8,ffffff,142f42',
                    'chromecast' => '0',
                    'airplay' => '0',
                    'chapters' => '0',
                    'cc' => '0',
                    'badge' => '0',
                    'audio_track' => '0',
                    'ask_ai' => '0',
                    'speed' => '0',
                    'pip' => '0',
                    'transcript' => '0',
                    'loop' => '1',
                ),
                $src
            );

            return $matches[1] . esc_url( $src ) . $matches[4];
        },
        $block_content
    );
}

add_filter( 'render_block', 'sumun_force_full_image_in_media_text', 12, 2 );
function sumun_force_full_image_in_media_text( $block_content, $block ) {
    if ( empty( $block['blockName'] ) || 'core/media-text' !== $block['blockName'] ) {
        return $block_content;
    }

    $default_media_id = 0;
    if ( ! empty( $block['attrs']['mediaId'] ) ) {
        $default_media_id = (int) $block['attrs']['mediaId'];
    }

    $processor = new WP_HTML_Tag_Processor( $block_content );
    $updated = false;

    while ( $processor->next_tag( 'img' ) ) {
        $img_class = (string) $processor->get_attribute( 'class' );
        $image_id = $default_media_id;

        if ( preg_match( '/\bwp-image-(\d+)\b/', $img_class, $matches ) ) {
            $image_id = (int) $matches[1];
        }

        if ( $image_id > 0 ) {
            $full_src = wp_get_attachment_image_url( $image_id, 'full' );
            if ( ! empty( $full_src ) ) {
                $processor->set_attribute( 'src', esc_url( $full_src ) );
            }
        }

        $processor->remove_attribute( 'srcset' );
        $processor->remove_attribute( 'sizes' );
        $updated = true;
    }

    if ( ! $updated ) {
        return $block_content;
    }

    return $processor->get_updated_html();
}

function grunwell_video_embed( $attr, $content='' ) {
  if ( ! isset( $attr['poster'] ) && has_post_thumbnail() ) {
    /*
     * This uses a custom, 16:9 image size named 'poster'
     * but could be any size.
     */
    $poster = wp_get_attachment_image_src(
      get_post_thumbnail_id(),
      'poster'
    );
    $attr['poster'] = $poster['0'];
  }
 
  return wp_video_shortcode( $attr, $content );
}
add_shortcode( 'video', 'grunwell_video_embed' );



function smn_add_icon_class_to_button( $block_content, $block ) {
    if ( empty( $block['blockName'] ) || 'core/button' !== $block['blockName'] ) {
        return $block_content;
    }

    $text_content = wp_strip_all_tags( (string) $block_content );
    $has_cita = false !== mb_stripos( $text_content, ' cita' );
    $has_whatsapp = 1 === preg_match( '/<a\b[^>]*href=("|\')[^"\']*whatsapp[^"\']*\1/i', (string) $block_content );

    if ( ! $has_cita && ! $has_whatsapp ) {
        return $block_content;
    }

    $processor = new WP_HTML_Tag_Processor( $block_content );
    if ( $processor->next_tag( array( 'class_name' => 'wp-block-button' ) ) ) {
        if ( $has_cita ) {
            $processor->add_class( 'has-icon-calendar' );
        }

        if ( $has_whatsapp ) {
            $processor->add_class( 'has-icon-whatsapp' );
        }

        return $processor->get_updated_html();
    }

    return $block_content;
}
add_filter( 'render_block', 'smn_add_icon_class_to_button', 10, 2 );

function smn_render_scroll_to_top_button() {
    ?>
    <button
        type="button"
        class="smn-scroll-top"
        aria-label="Volver arriba"
        title="Volver arriba"
    >
        <span aria-hidden="true">↑</span>
    </button>
    <?php
}
add_action( 'wp_footer', 'smn_render_scroll_to_top_button', 5 );

