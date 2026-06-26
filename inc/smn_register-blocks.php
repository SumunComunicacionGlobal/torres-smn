<?php
/**
 * Register ACF Blocks
 *
 * @link https://www.advancedcustomfields.com/resources/acf_register_block_type/
 *
 * @return void
 */

/**
 * Register custom blocks script
 */

function smn_register_blocks() {
    
    // Partent title Block
    require_once get_stylesheet_directory() . '/custom-blocks/parent-title/parent-title.php';
    register_block_type( get_stylesheet_directory() . '/custom-blocks/parent-title', [ 
        'render_callback' => 'smn_render_parent_title_block'] );
    
    // Carousel Block
    register_block_type( get_stylesheet_directory() . '/custom-blocks/carousel', [
        'render_callback' => ['Carousel_Slider_Block', 'render_carousel']
    ]);
    register_block_type( get_stylesheet_directory() . '/custom-blocks/slide');

    // Tabs Block
    register_block_type( get_stylesheet_directory() . '/custom-blocks/tabs');
    register_block_type( get_stylesheet_directory() . '/custom-blocks/tab');
}

add_action( 'init', 'smn_register_blocks' );
