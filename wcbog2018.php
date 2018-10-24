<?php
/**
 * Plugin Name: WordCamp Bogotá 2018
 * Plugin URI: https://github.com/smilingrobots/wordcamp-bogota-2018
 * Description: Plugin de prueba para el Taller de introducción al desarrollo de plugins para WordPress.
 * Version: 1.0
 */

function wcbog2018_generate_button() {
    $button  = '';
    $button .= '<a href="' . add_query_arg( 'wcbog2018-like', '1' ) . '">Like</a>';

    return $button;
}

function wcbog2018_generate_like_count() {
    $likes = get_post_meta( get_the_ID(), '_wcbog2018_liked_by' );
    return 'Este post tiene <b>' . count( $likes ) . '</b> likes';
}

function wcbog2018_add_like_box_to_content( $content ) {
    if ( ! is_singular() ) {
        return $content;
    }

    $like_box  = '';
    $like_box .= '<p>';
    $like_box .= wcbog2018_generate_button();
    $like_box .= ' / ';
    $like_box .= wcbog2018_generate_like_count();
    $like_box .= '</p>';

    return $like_box . $content;
}
add_filter( 'the_content', 'wcbog2018_add_like_box_to_content' );