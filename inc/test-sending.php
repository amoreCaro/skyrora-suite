<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stepper Design у футері редактора (JS перенесе в шапку Gutenberg).
 */
function sk_mailing_editor_steps_source() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'mailing' !== $screen->post_type ) {
		return;
	}

	$post_id = 0;
	if ( isset( $_GET['post'] ) ) {
		$post_id = absint( $_GET['post'] );
	} elseif ( isset( $GLOBALS['post'] ) && $GLOBALS['post'] instanceof WP_Post ) {
		$post_id = (int) $GLOBALS['post']->ID;
	}

	?>
	<div id="sk-mailing-steps-source" hidden>
		<?php sk_render_admin_brand(); ?>
		<?php sk_render_mailing_steps( 'design', $post_id ); ?>
	</div>
	<?php
}
add_action( 'admin_footer', 'sk_mailing_editor_steps_source' );

/**
 * AJAX: тестовий лист (legacy з metabox / сумісність).
 */
add_action( 'wp_ajax_sk_send_test_email', 'sk_send_test_email_ajax' );
function sk_send_test_email_ajax() {
	check_ajax_referer( 'sk_send_test_email_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Permission denied.', 403 );
	}

	$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$subject = sanitize_text_field( wp_unslash( $_POST['subject'] ?? '' ) );
	$post_id = absint( $_POST['post_id'] ?? 0 );

	if ( ! is_email( $email ) ) {
		wp_send_json_error( 'Invalid email address.' );
	}
	if ( '' === $subject ) {
		wp_send_json_error( 'Invalid subject.' );
	}
	if ( ! $post_id || get_post_type( $post_id ) !== 'mailing' ) {
		wp_send_json_error( 'Invalid post ID.' );
	}

	$message = sk_get_mailing_email_html( $post_id );
	if ( is_wp_error( $message ) ) {
		wp_send_json_error( $message->get_error_message() );
	}

	$headers = [ 'Content-Type: text/html; charset=UTF-8' ];
	$result  = sk_send_mail_with_error( $email, $subject, $message, $headers );

	if ( $result['sent'] ) {
		wp_send_json_success();
	} else {
		wp_send_json_error( $result['error'] ?: 'Failed to send email. Please check your mail settings.' );
	}
}
