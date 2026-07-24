<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Перевіряє, чи належить поточний екран адмінці плагіна.
 *
 * @return bool
 */
function sk_is_plugin_admin_screen() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen ) {
		return false;
	}

	if ( in_array( $screen->post_type, [ 'mailing', 'subscriber' ], true ) ) {
		return true;
	}

	if ( 'list' === $screen->taxonomy ) {
		return true;
	}

	return 'toplevel_page_skyrora-mailing' === $screen->id
		|| 'toplevel_page_theme-settings' === $screen->id
		|| 0 === strpos( $screen->id, 'skyrora-mailing_page_' );
}

/**
 * Підключення CSS/JS лише в адмінці.
 */
function sk_enqueue_admin_assets( $hook ) {
	if ( ! is_admin() ) {
		return;
	}

	if ( sk_is_plugin_admin_screen() ) {
		sk_enqueue_admin_bundle();
	}

	sk_enqueue_subscriber_admin_assets( $hook );
	sk_enqueue_list_admin_assets( $hook );
	sk_enqueue_send_admin_assets( $hook );
	sk_enqueue_settings_admin_assets( $hook );
	sk_enqueue_mailing_editor_assets( $hook );
}
add_action( 'admin_enqueue_scripts', 'sk_enqueue_admin_assets' );

/**
 * Клас для fade-in ефекту лише на сторінках плагіна.
 *
 * @param string $classes Поточні класи body.
 * @return string
 */
function sk_plugin_admin_body_class( $classes ) {
	if ( sk_is_plugin_admin_screen() ) {
		$classes .= ' sk-plugin-page';
	}

	return $classes;
}
add_filter( 'admin_body_class', 'sk_plugin_admin_body_class' );

/**
 * Спільні dist-асети адмінки (CSS + JS bundle).
 *
 * @return bool Чи підключено скрипт.
 */
function sk_enqueue_admin_bundle() {
	$css_rel  = 'assets/dist/css/main.css';
	$js_rel   = 'assets/dist/js/main.js';
	$css_path = SK_PLUGIN_DIR . '/' . $css_rel;
	$js_path  = SK_PLUGIN_DIR . '/' . $js_rel;

	if ( file_exists( $css_path ) ) {
		wp_enqueue_style(
			'sk-admin',
			SK_PLUGIN_DIR_URL . $css_rel,
			[],
			filemtime( $css_path )
		);
	}

	if ( ! file_exists( $js_path ) ) {
		return false;
	}

	wp_enqueue_script(
		'sk-admin',
		SK_PLUGIN_DIR_URL . $js_rel,
		[],
		filemtime( $js_path ),
		true
	);

	return true;
}

/**
 * Асети екрана підписника (тільки post.php / post-new.php для CPT subscriber).
 */
function sk_enqueue_subscriber_admin_assets( $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'subscriber' !== $screen->post_type ) {
		return;
	}

	if ( ! sk_enqueue_admin_bundle() ) {
		return;
	}

	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;

	wp_localize_script( 'sk-admin', 'skSubscriberData', [
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'sk_check_subscriber_email' ),
		'postId'  => $post_id,
	] );
}

/**
 * Асети екранів list (edit-tags.php + term.php).
 */
function sk_enqueue_list_admin_assets( $hook ) {
	if ( 'term.php' !== $hook && 'edit-tags.php' !== $hook ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'list' !== $screen->taxonomy ) {
		return;
	}

	if ( ! sk_enqueue_admin_bundle() ) {
		return;
	}

	// JS picker лише на редагуванні терміна.
	if ( 'term.php' !== $hook ) {
		return;
	}

	$term_id = isset( $_GET['tag_ID'] ) ? absint( $_GET['tag_ID'] ) : 0;

	wp_localize_script( 'sk-admin', 'skListData', [
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'sk_list_subscribers' ),
		'termId'  => $term_id,
	] );
}

/**
 * Асети сторінки Send.
 *
 * @param string $hook Current admin hook.
 */
function sk_enqueue_send_admin_assets( $hook ) {
	if ( 'skyrora-mailing_page_skyrora-mailing-send' !== $hook ) {
		return;
	}

	if ( ! sk_enqueue_admin_bundle() ) {
		return;
	}

	$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;

	wp_localize_script( 'sk-admin', 'skSendData', [
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'sk_send_mailing' ),
		'postId'  => $post_id,
		'i18n'    => [
			'send'        => __( 'Send', 'skyrora-mailing' ),
			'sendTest'    => __( 'Send test', 'skyrora-mailing' ),
			'schedule'    => __( 'Schedule', 'skyrora-mailing' ),
			'sending'     => __( 'Sending…', 'skyrora-mailing' ),
			'needSubject' => __( 'Please enter a subject.', 'skyrora-mailing' ),
			'needList'    => __( 'Please select at least one list.', 'skyrora-mailing' ),
			'failed'      => __( 'Something went wrong. Please try again.', 'skyrora-mailing' ),
		],
	] );
}

/**
 * Assets for the plugin Settings screen.
 *
 * @param string $hook Current admin hook.
 */
function sk_enqueue_settings_admin_assets( $hook ) {
	if ( 'skyrora-mailing_page_skyrora-mailing-settings' !== $hook ) {
		return;
	}

	sk_enqueue_admin_bundle();
}

/**
 * Асети редактора Templates (кнопки Preview/Save/Next).
 *
 * @param string $hook Current admin hook.
 */
function sk_enqueue_mailing_editor_assets( $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'mailing' !== $screen->post_type ) {
		return;
	}

	if ( ! sk_enqueue_admin_bundle() ) {
		return;
	}

	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;

	wp_localize_script( 'sk-admin', 'skMailingNext', [
		'sendUrl'    => admin_url( 'admin.php?page=skyrora-mailing-send' ),
		'previewUrl' => $post_id ? get_preview_post_link( $post_id ) : '',
		'postId'     => $post_id,
		'i18n'       => [
			'preview'  => __( 'Preview', 'skyrora-mailing' ),
			'save'     => __( 'Save', 'skyrora-mailing' ),
			'next'     => __( 'Next', 'skyrora-mailing' ),
			'saving'   => __( 'Saving…', 'skyrora-mailing' ),
			'needSave' => __( 'Please save the template first.', 'skyrora-mailing' ),
		],
	] );
}
