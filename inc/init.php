<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

function sk_plugin_menu() {

    // Додати основний пункт меню плагіна
    add_menu_page(
        __( 'Skyrora Mailing', SK_TEXT_DOMAIN ), // Назва меню
        __( 'Skyrora Mailing', SK_TEXT_DOMAIN ), // Текст в меню
        'manage_options',              // Рівень доступу
        'skyrora-mailing',             // Slug меню
        'skyrora_mailing_dashbord',    // Callback (якщо потрібна сторінка для головного меню)
        'dashicons-admin-plugins',     // Іконка
        25                             // Позиція в меню
    );

    // Підменю: Статистика
    add_submenu_page(
        'skyrora-mailing',                   // Slug батьківського меню
        __( 'Statistics', 'skyrora-mailing' ), // Назва сторінки
        __( 'Statistics', 'skyrora-mailing' ), // Назва в меню
        'manage_options',              // Рівень доступу
        'skyrora-mailing-statistics',        // Slug сторінки
        'sk_statistics_page_callback'  // Callback-функція
    );

    // Підменю: Налаштування
    add_submenu_page(
        'skyrora-mailing',
        __( 'Settings', 'skyrora-mailing' ),
        __( 'Settings', 'skyrora-mailing' ),
        'manage_options',
        'skyrora-mailing-settings',
        'sk_settings_page_callback'
    );
}
add_action( 'admin_menu', 'sk_plugin_menu' );


function skyrora_mailing_dashbord(){ ?>
    <h2>Skyrora Mailing Dashbord</h2>

    <?php
}

function sk_statistics_page_callback() { ?>
    <h2>Statistics</h2>

    <?php
}

function sk_settings_page_callback(){ ?>
<h2>Settings</h2>

<?php
}


function sk_post_types() {
    $common_args = [
        "public" => true,
        "publicly_queryable" => true,
        "show_ui" => true,
        "show_in_rest" => true,
        "rest_controller_class" => "WP_REST_Posts_Controller",
        "has_archive" => false,
        "delete_with_user" => false,
        "exclude_from_search" => false,
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "rewrite" => [ 'slug' => false, 'with_front' => false ],
        "query_var" => true,
        "supports" => [ "title", "editor" ],
    ];

    // Тип запису Mailing
    $mailing_labels = [
        "name" => __( "Templates", "skyrora-mailing" ),
        "singular_name" => __( "Mailing", "skyrora-mailing" ),
    ];
    $mailing_args = array_merge($common_args, [
        "public" => false,
        "label" => __( "Template", "skyrora-mailing" ),
        "labels" => $mailing_labels,
        "show_in_menu" => 'skyrora-mailing',
        "show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller"
    ]);
    register_post_type("mailing", $mailing_args);

    // Тип запису Subscription
    $subscription_labels = [
        "name" => __( "Subscriptions", "skyrora-mailing" ),
        "singular_name" => __( "Subscription", "skyrora-mailing" ),
    ];
    $subscription_args = array_merge($common_args, [
        "public" => false,
        "label" => __( "Subscriptions", "skyrora-mailing" ),
        "labels" => $subscription_labels,
        "show_in_menu" => 'skyrora-mailing',
    ]);
    register_post_type("subscription", $subscription_args);
}

add_action('init', 'sk_post_types');



add_filter( 'allowed_block_types_all', 'sk_allowed_blocks', 10, 2 );

function sk_allowed_blocks( $allowed_blocks, $block_editor_context ) {

    if (isset($block_editor_context->post) && $block_editor_context->post->post_type === 'mailing') {
    
        return array(
            'skyrora/hero',
            'skyrora/text',
            'skyrora/image',
            'skyrora/button',
            'skyrora/header',
            'skyrora/footer',
            'skyrora/cards'
        );
    } else {
        return $allowed_blocks;
    }
    
}

add_action('template_redirect', function () {
    if (is_singular('mailing')) {
        include SK_PLUGIN_DIR . '/single-mailing.php';
        exit;
    }
});