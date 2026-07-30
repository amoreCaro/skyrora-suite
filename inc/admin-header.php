<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the shared Skyrora brand.
 */
function sk_render_admin_brand() {
	$logo_url = SK_PLUGIN_DIR_URL . 'assets/images/skyrora-logo.svg';
	?>
	<div class="sk-mailing-topbar__brand">
		<img
			class="icon icon-logo"
			src="<?php echo esc_url( $logo_url ); ?>"
			width="75"
			height="41"
			alt="<?php esc_attr_e( 'Skyrora', SK_TEXT_DOMAIN ); ?>"
		>
	</div>
	<?php
}

/**
 * Render the shared header on classic plugin admin screens.
 */
function sk_render_plugin_admin_header() {
	if ( ! sk_is_plugin_admin_screen() ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen ) {
		return;
	}

	// These screens already render the header in their own layout.
	if (
		'skyrora-mailing_page_skyrora-mailing-send' === $screen->id
		|| ( 'mailing' === $screen->post_type && 'post' === $screen->base )
	) {
		return;
	}

	$current_step = 'mailing' === $screen->post_type ? 'template' : '';
	$show_steps   = 'subscriber' !== $screen->post_type
		&& 'list' !== $screen->taxonomy
		&& ! in_array(
			$screen->id,
			[
				'skyrora-mailing_page_skyrora-mailing-add-list',
				'skyrora-mailing_page_skyrora-mailing-statistics',
				'skyrora-mailing_page_skyrora-mailing-settings',
			],
			true
		);
	?>
	<header class="sk-mailing-topbar sk-mailing-topbar--global">
		<?php sk_render_admin_brand(); ?>
		<?php if ( $show_steps ) : ?>
			<?php sk_render_mailing_steps( $current_step, 0 ); ?>
		<?php endif; ?>
	</header>
	<?php
}
add_action( 'in_admin_header', 'sk_render_plugin_admin_header' );