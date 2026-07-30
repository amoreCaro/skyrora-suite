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
}
add_action( 'admin_menu', 'sk_plugin_menu' );

/**
 * Lists — після CPT-підменю (Templates / Subscribers).
 */
function sk_lists_menu() {
    add_submenu_page(
        'skyrora-mailing',
        __( 'Lists', 'skyrora-mailing' ),
        __( 'Lists', 'skyrora-mailing' ),
        'manage_categories',
        'edit-tags.php?taxonomy=list&post_type=subscriber'
    );
}
add_action( 'admin_menu', 'sk_lists_menu', 11 );

/**
 * Statistics / Settings — в кінці меню.
 */
function sk_utility_menus() {
    add_submenu_page(
        'skyrora-mailing',
        __( 'Statistics', 'skyrora-mailing' ),
        __( 'Statistics', 'skyrora-mailing' ),
        'manage_options',
        'skyrora-mailing-statistics',
        'sk_statistics_page_callback'
    );

    add_submenu_page(
        'skyrora-mailing',
        __( 'Logs', 'skyrora-mailing' ),
        __( 'Logs', 'skyrora-mailing' ),
        'manage_options',
        'skyrora-mailing-logs',
        'sk_render_logs_page'
    );

    add_submenu_page(
        'skyrora-mailing',
        __( 'Settings', 'skyrora-mailing' ),
        __( 'Settings', 'skyrora-mailing' ),
        'manage_options',
        'skyrora-mailing-settings',
        'sk_settings_page_callback'
    );
}
add_action( 'admin_menu', 'sk_utility_menus', 12 );

/**
 * Підсвітка пункту Lists у меню Skyrora Mailing.
 */
function sk_list_taxonomy_parent_file( $parent_file ) {
    global $taxonomy;
    $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

    if ( 'list' === $taxonomy || 'skyrora-mailing-add-list' === $page ) {
        return 'skyrora-mailing';
    }

    return $parent_file;
}
add_filter( 'parent_file', 'sk_list_taxonomy_parent_file' );

function sk_list_taxonomy_submenu_file( $submenu_file ) {
    global $taxonomy;
    $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

    if ( 'list' === $taxonomy || 'skyrora-mailing-add-list' === $page ) {
        return 'edit-tags.php?taxonomy=list&post_type=subscriber';
    }

    return $submenu_file;
}
add_filter( 'submenu_file', 'sk_list_taxonomy_submenu_file' );

/**
 * Підсвітка Templates у меню, коли відкрита сторінка Send.
 */
function sk_send_page_parent_file( $parent_file ) {
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
	if ( 'skyrora-mailing-send' === $page ) {
		return 'skyrora-mailing';
	}
	return $parent_file;
}
add_filter( 'parent_file', 'sk_send_page_parent_file' );

function sk_send_page_submenu_file( $submenu_file ) {
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
	if ( 'skyrora-mailing-send' === $page ) {
		return 'edit.php?post_type=mailing';
	}
	return $submenu_file;
}
add_filter( 'submenu_file', 'sk_send_page_submenu_file' );


function skyrora_mailing_dashbord(){ ?>
    <h2>Skyrora Mailing Dashbord</h2>

    <?php
}

function sk_statistics_page_callback() { ?>
    <h2>Statistics</h2>

    <?php
}

function sk_settings_page_callback() {
	if ( function_exists( 'sk_render_settings_page' ) ) {
		sk_render_settings_page();
	}
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
        "rewrite" => false,
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
        "publicly_queryable" => true,
        "exclude_from_search" => true,
        "rewrite" => [
            "slug"       => "mailing",
            "with_front" => false,
        ],
        "label" => __( "Template", "skyrora-mailing" ),
        "labels" => $mailing_labels,
        "show_in_menu" => 'skyrora-mailing',
        "show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller"
    ]);
    register_post_type("mailing", $mailing_args);

    // Тип запису Subscription (без пункту в меню)
    $subscription_labels = [
        "name" => __( "Subscriptions", "skyrora-mailing" ),
        "singular_name" => __( "Subscription", "skyrora-mailing" ),
    ];
    $subscription_args = array_merge($common_args, [
        "public" => false,
        "label" => __( "Subscriptions", "skyrora-mailing" ),
        "labels" => $subscription_labels,
        "show_ui" => false,
        "show_in_menu" => false,
    ]);
    register_post_type("subscription", $subscription_args);

    // Тип запису Subscriber
    $subscriber_labels = [
        "name" => __( "Subscribers", "skyrora-mailing" ),
        "singular_name" => __( "Subscriber", "skyrora-mailing" ),
        "add_new_item" => __( "Add New Subscriber", "skyrora-mailing" ),
        "edit_item" => __( "Edit Subscriber", "skyrora-mailing" ),
        "search_items" => __( "Search Subscribers", "skyrora-mailing" ),
        "not_found" => __( "No subscribers found", "skyrora-mailing" ),
    ];
    $subscriber_args = array_merge($common_args, [
        "public" => false,
        "label" => __( "Subscribers", "skyrora-mailing" ),
        "labels" => $subscriber_labels,
        "show_in_menu" => 'skyrora-mailing',
        "supports" => [ "title" ],
    ]);
    register_post_type("subscriber", $subscriber_args);
}

add_action('init', 'sk_post_types');

/**
 * Flush rewrite rules once after CPT/rewrite changes (and on activation).
 */
function sk_maybe_flush_rewrite_rules() {
	$version = '1.0.1';
	if ( get_option( 'sk_rewrite_version' ) === $version ) {
		return;
	}

	flush_rewrite_rules( false );
	update_option( 'sk_rewrite_version', $version );
}
add_action( 'init', 'sk_maybe_flush_rewrite_rules', 99 );

/**
 * Register CPTs/taxonomies and flush permalinks on plugin activation.
 */
function sk_activate_plugin() {
	sk_post_types();
	sk_taxonomies();
	flush_rewrite_rules();
	update_option( 'sk_rewrite_version', '1.0.1' );
}

function sk_taxonomies() {
    // Таксономія List для підписників
    $list_labels = [
        "name" => __( "Lists", "skyrora-mailing" ),
        "singular_name" => __( "List", "skyrora-mailing" ),
        "menu_name" => __( "Lists", "skyrora-mailing" ),
        "all_items" => __( "All Lists", "skyrora-mailing" ),
        "edit_item" => __( "Edit List", "skyrora-mailing" ),
        "view_item" => __( "View List", "skyrora-mailing" ),
        "update_item" => __( "Update List", "skyrora-mailing" ),
        "add_new_item" => __( "Add New List", "skyrora-mailing" ),
        "new_item_name" => __( "New List Name", "skyrora-mailing" ),
        "search_items" => __( "Search Lists", "skyrora-mailing" ),
        "not_found" => __( "No lists found", "skyrora-mailing" ),
    ];

    register_taxonomy("list", "subscriber", [
        "labels" => $list_labels,
        "public" => false,
        "show_ui" => true,
        "show_in_menu" => false, // Пункт меню додаємо вручну в sk_plugin_menu().
        "show_in_rest" => true,
        "show_admin_column" => true,
        "hierarchical" => true,
        "meta_box_cb" => null, // Стандартний checklist-метабокс.
        "query_var" => true,
        "rewrite" => false,
    ]);
}

add_action('init', 'sk_taxonomies');



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
    if ( ! is_singular( 'mailing' ) ) {
        return;
    }

    include SK_PLUGIN_DIR . '/single-mailing.php';
    exit;
});