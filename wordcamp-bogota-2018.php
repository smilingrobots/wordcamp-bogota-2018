<?php
/**
 * Plugin Name: WordCamp Bogotá 2018
 * Plugin URI: https://github.com/smilingrobots/wordcamp-bogota-2018
 * Description: Plugin de prueba para el Taller de introducción al desarrollo de plugins para WordPress.
 * Version: 1.0.0
 */

function wcbog2018_generate_button_for_post( $post_id ) {
    if ( ! is_user_logged_in() ) {
        return '';
    }

    $user_id = get_current_user_id();
    $likes   = get_post_meta( $post_id, '_wcbog2018_liked_by' );

    $button  = '';
    $button .= '<span class="dashicons dashicons-heart"></span>';
    $button .= ' ';

    if ( in_array( $user_id, $likes ) ) {
        $button .= __( 'Ya diste like a este post', 'wcbog2018' );
    } else {
        $button .= '<a href="' . add_query_arg( 'wcbog2018-like', '1' ) . '">' . __( 'Like', 'wcbog2018' ) . '</a>';
    }

    $button .= ' / ';

    return $button;
}

function wcbog2018_generate_like_count_for_post( $post_id ) {
    $likes = get_post_meta( $post_id, '_wcbog2018_liked_by' );

    return sprintf(
        _n(
            'Este post tiene <b>%d</b> like',
            'Este post tiene <b>%d</b> likes',
            count( $likes ),
            'wcbog2018'
        ),
        count( $likes )
    );
}

function wcbog2018_add_like_box_to_content( $content ) {
    $like_box  = '';
    $like_box .= '<p>';
    $like_box .= wcbog2018_generate_button_for_post( get_the_ID() );
    $like_box .= wcbog2018_generate_like_count_for_post( get_the_ID() );
    $like_box .= '</p>';

    return $like_box . $content;
}
add_filter( 'the_content', 'wcbog2018_add_like_box_to_content' );


function wcbog2018_maybe_like_post() {
    if ( ! is_singular() ) {
        return;
    }

    if ( ! is_user_logged_in() ) {
        return;
    }

    if ( empty( $_GET['wcbog2018-like'] ) ) {
        return;
    }

    $post_id = get_the_ID();
    $user_id = get_current_user_id();
    $likes   = get_post_meta( $post_id, '_wcbog2018_liked_by' );

    if ( ! in_array( $user_id, $likes ) ) {
        add_post_meta( $post_id, '_wcbog2018_liked_by', $user_id );
    }
}
add_action( 'wp', 'wcbog2018_maybe_like_post' );

add_filter( 'wp_privacy_personal_data_exporters',  'wcbog2018_register_personal_data_exporters' );
add_filter( 'wp_privacy_personal_data_erasers', 'wcbog2018_register_personal_data_erasers' );

function wcbog2018_register_personal_data_exporters( $exporters ) {
    $exporters['wordcamp-bogota-2018'] = array(
        'exporter_friendly_name' => __( 'WordCamp Bogotá 2018', 'wcbog2018' ),
        'callback'               => 'wcbog2018_export_personal_data',
    );

    return $exporters;
}

function wcbog2018_register_personal_data_erasers( $erasers ) {
    $erasers['wordcamp-bogota-2018'] = array(
        'eraser_friendly_name' => __( 'WordCamp Bogotá 2018', 'wcbog2018' ),
        'callback'             => 'wcbog2018_erase_personal_data',
    );

    return $erasers;
}

function wcbog2018_export_personal_data( $email_address, $page = 1 ) {
    $user           = get_user_by( 'email', $email_address );
    $posts_per_page = 50;
    $posts          = array();

    if ( ! is_null( $user ) ) {
        $posts = wcbog2018_get_posts_liked_by( $user, $posts_per_page, $page );
    }

    $exported_items = array();

    foreach ( $posts as $post ) {
        $exported_items[] = array(
            'group_id'    => "wcbog2018-liked-entries",
            'group_label' => __( 'Publicaciones Favoritas', 'wcbog2018' ),
            'item_id'     => $post->ID,
            'data'        => array(
                array(
                    'name'  => __( 'Título', 'wcbog2018' ),
                    'value' => $post->post_title,
                ),
                array(
                    'name'  => __( 'URL', 'wcbog2018' ),
                    'value' => get_permalink( $post ),
                ),
            ),
        );
    }

    return array(
        'data' => $exported_items,
        'done' => count( $posts ) < $posts_per_page,
    );
}

function wcbog2018_get_posts_liked_by( $user, $posts_per_page, $page ) {
    if ( is_null( $user ) ) {
        return array();
    }

    return get_posts( array(
        'post_type'      => array(
            'post',
            'page',
        ),
        'meta_key'       => '_wcbog2018_liked_by',
        'meta_value'     => $user->ID,
        'posts_per_page' => $posts_per_page,
        'offset'         => $posts_per_page * ( $page - 1 ),
    ) );
}

function wcbog2018_erase_personal_data( $email_address, $page = 1 ) {
    $user           = get_user_by( 'email', $email_address );
    $posts_per_page = 50;
    $posts          = array();

    if ( ! is_null( $user ) ) {
        $posts = wcbog2018_get_posts_liked_by( $user, $posts_per_page, $page );
    }

    $items_removed  = false;
    $items_retained = false;
    $messages       = array();

    foreach ( $posts as $post ) {
        if ( delete_post_meta( $post->ID, '_wcbog2018_liked_by', $user->ID ) ) {
            $items_removed = true;
            continue;
        }

        $items_retained = true;

        $message = __( 'Ocurrió un error intentando eliminar información personal asociada a la publicación {permalink}.', 'wcbog2018' );
        $message = str_replace( '{permalink}', '<a href="' . get_permalink( $post ) . '">' . esc_html( $post->post_title ) . '</a>', $message );

        $messages[] = $message;
    }

    return array(
        'items_removed'  => $items_removed,
        'items_retained' => $items_retained,
        'messages'       => $messages,
        'done'           => count( $posts ) < $posts_per_page,
    );
}