<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( is_page() ) return false;

$title = '';
$description = '';
$cta = false;
$thumb_id = false;
$title_class = '';
$thumb_width = 0;
$side_image = false;
$formulario_hero = false;


if ( is_singular() ) {
    $post = get_queried_object();
    $title = get_the_title();
    if ( $post->post_excerpt ) {
        $description = wpautop( $post->post_excerpt );
    }
    if ( is_singular( 'post' ) ) {
        $title_class = 'has-heading-3-font-size';
    } else {
        $thumb_id = get_post_thumbnail_id();
    }

} elseif ( is_archive() ) {
    $title = get_the_archive_title();
    $description = wpautop( get_the_archive_description() );
    $cta = true;
    if ( is_tax() ) {
        $term = get_queried_object();
        // $thumb_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
        $thumb_id = smn_get_term_thumbnail_id( $term );

        if ( !$description ) {
            $card_description = get_field( 'term_description_pt_archive', $term );
            if ( $card_description ) {
                $description = wpautop( $card_description );
            }
        }
    } elseif ( is_post_type_archive() ) {
        $post_type = get_post_type();
        $thumb_id = get_field( $post_type . '_post_type_archive_thumbnail', 'option' );
    }
} elseif ( is_search() ) {
    $title = sprintf( __( 'Resultados de búsqueda para: %s', 'sorribes' ), '<span>' . get_search_query() . '</span>' );
} elseif( is_home() ) {
    $page_for_posts_id = get_option( 'page_for_posts' );
    if ( $page_for_posts_id ) {
        $page = get_post( $page_for_posts_id );
        $title = get_the_title( $page_for_posts_id );
        if ( $page->post_excerpt ) {
            $description = wpautop( $page->post_excerpt );
        }
        $thumb_id = get_post_thumbnail_id( $page_for_posts_id );
    }
} else {
    return false;
}

if ( is_singular( 'solucion' ) ) {
    $formulario_hero = true;
}

if ( $thumb_id ) {
    $image = wp_get_attachment_metadata( $thumb_id );
    if ( isset( $image['width'] ) ) {
        $thumb_width = $image['width'];
        if ( $thumb_width < 760 ) {
            $side_image = true;
        }
    }
}

if ( ! $thumb_id || $thumb_width < 760 ) {
    $thumb_id = THUMBNAIL_ID;
}

if ( $description ) {
    if ( preg_match( '/-{3,}/', $description ) ) {
        // Split description into two parts: before and after the separator
        list( $desc_before, $desc_after ) = preg_split( '/-{3,}/', $description, 2 );
        // Clean up and split lines after the separator
        $desc_after = trim( strip_tags( $desc_after ) );
        $lines = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $desc_after ) ) );
        if ( $lines ) {
            $description = $desc_before;
            $description .= '<ul class="is-style-check-list">';
            foreach ( $lines as $line ) {
                if ( $line !== '' ) {
                    $description .= '<li>' . esc_html( $line ) . '</li>';
                }
            }
            $description .= '</ul>';
        }
    }
}
?>

<div id="hero" class="wp-block-cover alignfull bisel-abajo-derecha">
    <?php if ( $thumb_id ) : ?>
        <img class="wp-block-cover__image-background" alt="<?php echo esc_attr( $title ); ?>" src="<?php echo esc_url( wp_get_attachment_url( $thumb_id ) ); ?>" />
    <?php endif; ?>
    <span aria-hidden="true" class="wp-block-cover__background has-background-dim-80 has-background-dim has-background has-neutral-100-background"></span>
    <div class="wp-block-cover__inner-container has-global-padding is-layout-constrained">
        <div class="wp-block-group">
            <div class="wp-block-columns is-layout-flex hero-columns">
                <div class="wp-block-column hero-column-first">
        
                    <?php smn_breadcrumb(); ?>
                    
                    <header class="page-header">
                        <h1 class="page-title <?php echo $title_class; ?>"><?php echo $title; ?></h1>
                        <?php if ( $description ) : ?>
                            <div class="page-description"><?php echo $description; ?></div>
                        <?php endif; ?>
                        <?php if ( $cta ) : ?>
                            
                            <div class="wp-block-buttons d-flex justify-content-start">
                                <div class="wp-block-button">
                                    <a class="wp-block-button__link" href="<?php echo get_permalink( PRESUPUESTO_ID ); ?>" title="<?php echo __( 'Pedir presupuesto', 'sorribes' ); ?>"><?php echo __( 'Pedir presupuesto', 'sorribes' ); ?></a>
                                </div>
                            </div>

                        <?php endif; ?>

                    </header><!-- .page-header -->

                </div>
                <div class="wp-block-column is-vertically-aligned-center hero-column-second">
                    
                    <?php if ( $formulario_hero) :
                        block_template_part( 'hero-form' );
                    elseif ( $side_image ) : ?>
                        <figure class="wp-block-image">
                            <?php the_post_thumbnail( 'large', [ 'class' => 'hero-side-image aligncenter' ] ); ?>
                        </figure>
                    <?php endif; ?>
                
                </div>
            </div>
        </div>
    </div>
</div>
