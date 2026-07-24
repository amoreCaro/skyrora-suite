<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Доступні статуси підписника.
 *
 * @return array<string,string>
 */
function sk_get_subscriber_statuses() {
	return [
		'subscribed'   => __( 'Subscribed', 'skyrora-mailing' ),
		'unconfirmed'  => __( 'Unconfirmed', 'skyrora-mailing' ),
		'unsubscribed' => __( 'Unsubscribed', 'skyrora-mailing' ),
		'inactive'     => __( 'Inactive', 'skyrora-mailing' ),
		'bounced'      => __( 'Bounced', 'skyrora-mailing' ),
		'trash'        => __( 'Trash', 'skyrora-mailing' ),
	];
}

/**
 * Отримати статус підписника. Legacy-записи без статусу вважаються підписаними.
 *
 * @param int|WP_Post $post Post ID або об'єкт.
 * @return string
 */
function sk_get_subscriber_status( $post ) {
	$post = get_post( $post );
	if ( ! $post || 'subscriber' !== $post->post_type ) {
		return '';
	}

	$status = (string) get_post_meta( $post->ID, 'sk_status', true );
	return array_key_exists( $status, sk_get_subscriber_statuses() ) ? $status : 'subscribed';
}

/**
 * Реєстрація мета-полів підписника.
 */
function sk_register_subscriber_meta() {
	$fields = [ 'email', 'first_name', 'last_name' ];

	foreach ( $fields as $field ) {
		register_post_meta( 'subscriber', 'sk_' . $field, [
			'type'          => 'string',
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		] );
	}

	register_post_meta( 'subscriber', 'sk_status', [
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'default'           => 'subscribed',
		'sanitize_callback' => function ( $value ) {
			$value = sanitize_key( $value );
			return array_key_exists( $value, sk_get_subscriber_statuses() ) ? $value : 'subscribed';
		},
		'auth_callback'     => function () {
			return current_user_can( 'edit_posts' );
		},
	] );
}
add_action( 'init', 'sk_register_subscriber_meta' );

/**
 * Placeholder для post_title (email).
 */
function sk_subscriber_enter_title_here( $text, $post ) {
	if ( $post && 'subscriber' === $post->post_type ) {
		return 'name@example.com';
	}
	return $text;
}
add_filter( 'enter_title_here', 'sk_subscriber_enter_title_here', 10, 2 );

/**
 * Отримати email підписника: post_title як джерело правди, fallback на meta.
 *
 * @param int|WP_Post $post Post ID або об'єкт.
 * @return string
 */
function sk_get_subscriber_email( $post ) {
	$post = get_post( $post );
	if ( ! $post || 'subscriber' !== $post->post_type ) {
		return '';
	}

	$title = trim( (string) $post->post_title );
	if ( '' !== $title && is_email( $title ) ) {
		return sanitize_email( $title );
	}

	$meta = get_post_meta( $post->ID, 'sk_email', true );
	return is_string( $meta ) ? sanitize_email( $meta ) : '';
}

/**
 * Для legacy-записів (title = ім'я) підставляємо email у полі title на екрані редагування.
 */
function sk_subscriber_prepare_title_for_edit( $post ) {
	if ( ! $post || 'subscriber' !== $post->post_type ) {
		return;
	}

	$email = get_post_meta( $post->ID, 'sk_email', true );
	if ( is_string( $email ) && '' !== $email && is_email( $email ) && ! is_email( $post->post_title ) ) {
		$post->post_title = $email;
	}
}
add_action( 'edit_form_top', 'sk_subscriber_prepare_title_for_edit' );

/**
 * Підпис Email над стандартним post_title.
 */
function sk_subscriber_email_label( $post ) {
	if ( ! $post || 'subscriber' !== $post->post_type ) {
		return;
	}
	?>
	<label class="sk-subscriber__email-label" for="title"><?php esc_html_e( 'Email', 'skyrora-mailing' ); ?></label>
	<?php
}
add_action( 'edit_form_after_title', 'sk_subscriber_email_label', 1 );

/**
 * Форма підписника під стандартним post_title (email).
 */
function sk_render_subscriber_form( $post ) {
	if ( ! isset( $post->post_type ) || 'subscriber' !== $post->post_type ) {
		return;
	}
	wp_nonce_field( 'sk_save_subscriber', 'sk_subscriber_nonce' );

	$first_name = get_post_meta( $post->ID, 'sk_first_name', true );
	$last_name  = get_post_meta( $post->ID, 'sk_last_name', true );
	$status     = sk_get_subscriber_status( $post );
	$statuses   = sk_get_subscriber_statuses();
	?>
	<div class="sk-subscriber__error" id="sk_email_error" role="alert"><?php esc_html_e( 'Please enter your email address', 'skyrora-mailing' ); ?></div>

	<div class="sk-subscriber" id="sk-subscriber">
		<div class="sk-subscriber__card">
			<section class="sk-subscriber__section">
				<div class="sk-subscriber__grid">
					<div class="sk-subscriber__field">
						<label class="sk-subscriber__field-label" for="sk_first_name"><?php esc_html_e( 'First name', 'skyrora-mailing' ); ?></label>
						<input class="sk-subscriber__input" type="text" id="sk_first_name" name="sk_first_name" value="<?php echo esc_attr( $first_name ); ?>" placeholder="<?php esc_attr_e( 'First name', 'skyrora-mailing' ); ?>" autocomplete="given-name">
					</div>
					<div class="sk-subscriber__field">
						<label class="sk-subscriber__field-label" for="sk_last_name"><?php esc_html_e( 'Last name', 'skyrora-mailing' ); ?></label>
						<input class="sk-subscriber__input" type="text" id="sk_last_name" name="sk_last_name" value="<?php echo esc_attr( $last_name ); ?>" placeholder="<?php esc_attr_e( 'Last name', 'skyrora-mailing' ); ?>" autocomplete="family-name">
					</div>
					<div class="sk-subscriber__field">
						<label class="sk-subscriber__field-label" for="sk_status"><?php esc_html_e( 'Status', 'skyrora-mailing' ); ?></label>
						<select class="sk-subscriber__input sk-subscriber__select" id="sk_status" name="sk_status">
							<?php foreach ( $statuses as $status_value => $status_label ) : ?>
								<option value="<?php echo esc_attr( $status_value ); ?>" <?php selected( $status, $status_value ); ?>>
									<?php echo esc_html( $status_label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</section>
		</div>
	</div>
	<?php
}
add_action( 'edit_form_after_title', 'sk_render_subscriber_form' );

/**
 * Перевірка, чи email уже зайнятий іншим підписником (по post_title і legacy meta).
 *
 * @param string $email           Email для перевірки.
 * @param int    $exclude_post_id ID поточного запису (ігноруємо себе).
 * @return bool
 */
function sk_subscriber_email_exists( $email, $exclude_post_id = 0 ) {
	$email = sanitize_email( $email );
	if ( '' === $email ) {
		return false;
	}

	global $wpdb;

	$exclude_post_id = (int) $exclude_post_id;

	// Exact match по post_title.
	$title_sql = "SELECT ID FROM {$wpdb->posts}
		WHERE post_type = 'subscriber'
		AND post_status IN ('publish','draft','pending','private','future')
		AND post_title = %s";
	$title_args = [ $email ];

	if ( $exclude_post_id ) {
		$title_sql   .= ' AND ID != %d';
		$title_args[] = $exclude_post_id;
	}

	$title_sql .= ' LIMIT 1';

	$found = $wpdb->get_var( $wpdb->prepare( $title_sql, $title_args ) );
	if ( $found ) {
		return true;
	}

	// Legacy: email ще лише в meta.
	$query = new WP_Query( [
		'post_type'      => 'subscriber',
		'post_status'    => [ 'publish', 'draft', 'pending', 'private', 'future' ],
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'post__not_in'   => $exclude_post_id ? [ $exclude_post_id ] : [],
		'meta_query'     => [
			[
				'key'     => 'sk_email',
				'value'   => $email,
				'compare' => '=',
			],
		],
		'no_found_rows'  => true,
	] );

	return $query->have_posts();
}

/**
 * AJAX: перевірка унікальності email перед збереженням.
 */
function sk_ajax_check_subscriber_email() {
	check_ajax_referer( 'sk_check_subscriber_email', 'nonce' );

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( [ 'message' => __( 'Permission denied.', 'skyrora-mailing' ) ], 403 );
	}

	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

	if ( '' === $email || ! is_email( $email ) ) {
		wp_send_json_error( [
			'message' => __( 'Please enter a valid email address', 'skyrora-mailing' ),
			'code'    => 'invalid',
		] );
	}

	if ( sk_subscriber_email_exists( $email, $post_id ) ) {
		wp_send_json_error( [
			'message' => __( 'This email already exists', 'skyrora-mailing' ),
			'code'    => 'exists',
		] );
	}

	wp_send_json_success();
}
add_action( 'wp_ajax_sk_check_subscriber_email', 'sk_ajax_check_subscriber_email' );

/**
 * Збереження полів підписника; email = post_title.
 * Lists зберігає стандартний метабокс таксономії.
 */
function sk_save_subscriber_meta( $post_id, $post ) {
	if ( ! isset( $_POST['sk_subscriber_nonce'] ) || ! wp_verify_nonce( $_POST['sk_subscriber_nonce'], 'sk_save_subscriber' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( 'subscriber' !== $post->post_type ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$email_raw  = isset( $_POST['post_title'] ) ? wp_unslash( $_POST['post_title'] ) : $post->post_title;
	$email      = sanitize_email( $email_raw );
	$first_name = isset( $_POST['sk_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['sk_first_name'] ) ) : '';
	$last_name  = isset( $_POST['sk_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['sk_last_name'] ) ) : '';
	$status     = isset( $_POST['sk_status'] ) ? sanitize_key( wp_unslash( $_POST['sk_status'] ) ) : 'subscribed';
	$status     = array_key_exists( $status, sk_get_subscriber_statuses() ) ? $status : 'subscribed';

	if ( '' !== $email && sk_subscriber_email_exists( $email, $post_id ) ) {
		set_transient( 'sk_subscriber_email_error_' . get_current_user_id(), __( 'This email already exists', 'skyrora-mailing' ), 45 );
		return;
	}

	update_post_meta( $post_id, 'sk_email', $email );
	update_post_meta( $post_id, 'sk_first_name', $first_name );
	update_post_meta( $post_id, 'sk_last_name', $last_name );
	update_post_meta( $post_id, 'sk_status', $status );

	// Нормалізуємо post_title до sanitize_email (якщо відрізняється від сирого вводу).
	if ( $email && $email !== $post->post_title ) {
		remove_action( 'save_post_subscriber', 'sk_save_subscriber_meta', 10 );
		wp_update_post( [
			'ID'         => $post_id,
			'post_title' => $email,
		] );
		add_action( 'save_post_subscriber', 'sk_save_subscriber_meta', 10, 2 );
	}
}
add_action( 'save_post_subscriber', 'sk_save_subscriber_meta', 10, 2 );

/**
 * Admin notice при дублікаті email (серверний fallback).
 */
function sk_subscriber_email_admin_notice() {
	$key     = 'sk_subscriber_email_error_' . get_current_user_id();
	$message = get_transient( $key );

	if ( ! $message ) {
		return;
	}

	delete_transient( $key );
	printf(
		'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
		esc_html( $message )
	);
}
add_action( 'admin_notices', 'sk_subscriber_email_admin_notice' );

/**
 * Кастомні колонки у списку підписників.
 */
function sk_subscriber_columns( $columns ) {
	$new = [];
	$new['cb'] = isset( $columns['cb'] ) ? $columns['cb'] : '<input type="checkbox" />';
	$new['title']         = __( 'Email', 'skyrora-mailing' );
	$new['sk_first_name'] = __( 'First Name', 'skyrora-mailing' );
	$new['sk_last_name']  = __( 'Last Name', 'skyrora-mailing' );
	$new['sk_status']     = __( 'Status', 'skyrora-mailing' );

	if ( isset( $columns['taxonomy-list'] ) ) {
		$new['taxonomy-list'] = $columns['taxonomy-list'];
	}
	if ( isset( $columns['date'] ) ) {
		$new['date'] = $columns['date'];
	}

	return $new;
}
add_filter( 'manage_subscriber_posts_columns', 'sk_subscriber_columns' );

/**
 * У списку показуємо email (post_title або legacy meta).
 */
function sk_subscriber_list_title( $title, $post_id ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'edit-subscriber' !== $screen->id ) {
		return $title;
	}

	$email = sk_get_subscriber_email( $post_id );
	return '' !== $email ? $email : $title;
}
add_filter( 'the_title', 'sk_subscriber_list_title', 10, 2 );

function sk_subscriber_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'sk_first_name':
			echo esc_html( get_post_meta( $post_id, 'sk_first_name', true ) );
			break;
		case 'sk_last_name':
			echo esc_html( get_post_meta( $post_id, 'sk_last_name', true ) );
			break;
		case 'sk_status':
			$status   = sk_get_subscriber_status( $post_id );
			$statuses = sk_get_subscriber_statuses();
			echo esc_html( isset( $statuses[ $status ] ) ? $statuses[ $status ] : $status );
			break;
	}
}
add_action( 'manage_subscriber_posts_custom_column', 'sk_subscriber_column_content', 10, 2 );

/**
 * Фільтр за статусом у списку підписників.
 */
function sk_subscriber_status_filter() {
	global $typenow;

	if ( 'subscriber' !== $typenow ) {
		return;
	}

	$current_status = isset( $_GET['sk_status'] ) ? sanitize_key( wp_unslash( $_GET['sk_status'] ) ) : '';
	?>
	<select name="sk_status">
		<option value=""><?php esc_html_e( 'All statuses', 'skyrora-mailing' ); ?></option>
		<?php foreach ( sk_get_subscriber_statuses() as $status => $label ) : ?>
			<option value="<?php echo esc_attr( $status ); ?>" <?php selected( $current_status, $status ); ?>>
				<?php echo esc_html( $label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php
}
add_action( 'restrict_manage_posts', 'sk_subscriber_status_filter' );

/**
 * Застосувати фільтр статусу до головного admin-запиту.
 *
 * @param WP_Query $query Поточний запит.
 */
function sk_filter_subscribers_by_status( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || 'subscriber' !== $query->get( 'post_type' ) ) {
		return;
	}

	$status = isset( $_GET['sk_status'] ) ? sanitize_key( wp_unslash( $_GET['sk_status'] ) ) : '';
	if ( ! array_key_exists( $status, sk_get_subscriber_statuses() ) ) {
		return;
	}

	if ( 'subscribed' === $status ) {
		$query->set( 'meta_query', [
			'relation' => 'OR',
			[
				'key'     => 'sk_status',
				'value'   => 'subscribed',
				'compare' => '=',
			],
			[
				'key'     => 'sk_status',
				'compare' => 'NOT EXISTS',
			],
			[
				'key'     => 'sk_status',
				'value'   => '',
				'compare' => '=',
			],
		] );
		return;
	}

	$query->set( 'meta_key', 'sk_status' );
	$query->set( 'meta_value', $status );
}
add_action( 'pre_get_posts', 'sk_filter_subscribers_by_status' );
