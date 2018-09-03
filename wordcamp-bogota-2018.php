<?php
/**
 * Plugin Name: WordCamp Bogotá 2018
 * Plugin URI: https://github.com/smilingrobots/wordcamp-bogota-2018
 * Description: Plugin de prueba para el Taller de introducción al desarrollo de plugins para WordPress.
 * Version: 1.0.0
 */

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
            'group_label' => __( 'Publicaciones Favoritas', 'wcbog20182018' ),
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

        $message = __( 'Ocurrió un error intentando eliminar información personal asociada a la publicación {permalink}.', 'another-wordpress-classifieds-plugin' );
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
