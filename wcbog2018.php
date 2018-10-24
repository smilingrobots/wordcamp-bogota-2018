<?php
/**
 * Plugin Name: WordCamp Bogotá 2018
 * Plugin URI: https://github.com/smilingrobots/wordcamp-bogota-2018
 * Description: Plugin de prueba para el Taller de introducción al desarrollo de plugins para WordPress.
 * Version: 1.0
 */

function wcbog2018_add_like_box_to_content( $content ) {
    if ( ! is_singular() ) {
        return $content;
    }

    $like_box  = '';
    $like_box .= '<p>';
    $like_box .= '-- Mi like box --';
    $like_box .= '</p>';

    return $like_box . $content;
}
add_filter( 'the_content', 'wcbog2018_add_like_box_to_content' );