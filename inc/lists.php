<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Реєструємо окремий екран створення списку.
 */
function sk_register_add_list_page() {
	add_submenu_page(
		'skyrora-mailing',
		__( 'Add new list', 'skyrora-mailing' ),
		__( 'Add new list', 'skyrora-mailing' ),
		'manage_categories',
		'skyrora-mailing-add-list',
		'sk_render_add_list_page'
	);
}
add_action( 'admin_menu', 'sk_register_add_list_page', 20 );

/**
 * Окремий екран створення списку.
 */
function sk_render_add_list_page() {
	if ( ! current_user_can( 'manage_categories' ) ) {
		wp_die( esc_html__( 'You are not allowed to create lists.', 'skyrora-mailing' ) );
	}

	$error     = isset( $_GET['sk_list_error'] ) ? sanitize_key( wp_unslash( $_GET['sk_list_error'] ) ) : '';
	$templates = get_posts( [
		'post_type'      => 'mailing',
		'post_status'    => [ 'publish', 'draft', 'pending', 'private', 'future' ],
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	] );
	$pages = get_pages( [
		'sort_column' => 'post_title',
		'sort_order'  => 'ASC',
	] );
	?>
	<div class="wrap sk-add-list-page">
		<div class="sk-add-list-page__heading">
			<a class="sk-add-list-page__back" href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=list&post_type=subscriber' ) ); ?>" aria-label="<?php esc_attr_e( 'Back to lists', 'skyrora-mailing' ); ?>">
				<span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span>
			</a>
			<h1><?php esc_html_e( 'Add new list', 'skyrora-mailing' ); ?></h1>
		</div>

		<?php if ( 'missing_name' === $error ) : ?>
			<div class="notice notice-error inline"><p><?php esc_html_e( 'Please enter a public list name.', 'skyrora-mailing' ); ?></p></div>
		<?php elseif ( 'create_failed' === $error ) : ?>
			<div class="notice notice-error inline"><p><?php esc_html_e( 'The list could not be created. Please try again.', 'skyrora-mailing' ); ?></p></div>
		<?php endif; ?>

		<form class="sk-add-list-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="sk_create_list">
			<?php wp_nonce_field( 'sk_create_list', 'sk_create_list_nonce' ); ?>

			<div class="sk-add-list-form__grid">
				<div class="sk-add-list-form__field">
					<label for="sk_public_list_name"><?php esc_html_e( 'Public list name', 'skyrora-mailing' ); ?></label>
					<p class="description"><?php esc_html_e( 'Subscribers may see this name when managing their subscriptions.', 'skyrora-mailing' ); ?></p>
					<input type="text" id="sk_public_list_name" name="sk_public_list_name" required>
				</div>

				<div class="sk-add-list-form__field">
					<label><?php esc_html_e( 'List visibility', 'skyrora-mailing' ); ?></label>
					<label class="sk-add-list-form__checkbox" for="sk_list_visible">
						<input type="checkbox" id="sk_list_visible" name="sk_list_visible" value="1" checked>
						<?php esc_html_e( 'Show this list on the “Manage Subscription” page', 'skyrora-mailing' ); ?>
					</label>
				</div>

				<div class="sk-add-list-form__field">
					<label for="sk_public_description"><?php esc_html_e( 'Public description', 'skyrora-mailing' ); ?></label>
					<p class="description"><?php esc_html_e( 'Appears below the list name on the Manage Subscription page.', 'skyrora-mailing' ); ?></p>
					<textarea id="sk_public_description" name="sk_public_description" rows="8"></textarea>
				</div>

				<div class="sk-add-list-form__field">
					<label for="sk_internal_description"><?php esc_html_e( 'Description', 'skyrora-mailing' ); ?></label>
					<p class="description"><?php esc_html_e( 'Use this description for your own notes and integrations.', 'skyrora-mailing' ); ?></p>
					<textarea id="sk_internal_description" name="sk_internal_description" rows="8"></textarea>
				</div>

				<div class="sk-add-list-form__field">
					<label for="sk_confirmation_email"><?php esc_html_e( 'Confirmation email', 'skyrora-mailing' ); ?></label>
					<p class="description"><?php esc_html_e( 'Choose a custom confirmation email for subscribers joining this list. If not set, the global default is used.', 'skyrora-mailing' ); ?></p>
					<select id="sk_confirmation_email" name="sk_confirmation_email">
						<option value="0"><?php esc_html_e( 'Use global default', 'skyrora-mailing' ); ?></option>
						<?php foreach ( $templates as $template ) : ?>
							<option value="<?php echo esc_attr( $template->ID ); ?>"><?php echo esc_html( $template->post_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="sk-add-list-form__field">
					<label for="sk_confirmation_page"><?php esc_html_e( 'Confirmation page', 'skyrora-mailing' ); ?></label>
					<p class="description"><?php esc_html_e( 'Choose a custom confirmation page for subscribers joining this list. If not set, the global default is used.', 'skyrora-mailing' ); ?></p>
					<select id="sk_confirmation_page" name="sk_confirmation_page">
						<option value="0"><?php esc_html_e( 'Use global default', 'skyrora-mailing' ); ?></option>
						<?php foreach ( $pages as $page ) : ?>
							<option value="<?php echo esc_attr( $page->ID ); ?>"><?php echo esc_html( $page->post_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<div class="sk-add-list-form__actions">
				<a class="button sk-add-list-form__create" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=mailing' ) ); ?>"><?php esc_html_e( 'Create new', 'skyrora-mailing' ); ?></a>
				<?php submit_button( __( 'Save', 'skyrora-mailing' ), 'primary', 'submit', false ); ?>
			</div>
		</form>
	</div>
	<?php
}

/**
 * Створюємо list з окремого admin-екрана.
 */
function sk_handle_create_list() {
	if ( ! current_user_can( 'manage_categories' ) ) {
		wp_die( esc_html__( 'You are not allowed to create lists.', 'skyrora-mailing' ) );
	}

	check_admin_referer( 'sk_create_list', 'sk_create_list_nonce' );

	$name = isset( $_POST['sk_public_list_name'] ) ? sanitize_text_field( wp_unslash( $_POST['sk_public_list_name'] ) ) : '';
	if ( '' === $name ) {
		wp_safe_redirect( add_query_arg( 'sk_list_error', 'missing_name', admin_url( 'admin.php?page=skyrora-mailing-add-list' ) ) );
		exit;
	}

	$public_description = isset( $_POST['sk_public_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['sk_public_description'] ) ) : '';
	$result = wp_insert_term( $name, 'list', [
		'description' => $public_description,
	] );

	if ( is_wp_error( $result ) ) {
		wp_safe_redirect( add_query_arg( 'sk_list_error', 'create_failed', admin_url( 'admin.php?page=skyrora-mailing-add-list' ) ) );
		exit;
	}

	$term_id = (int) $result['term_id'];
	update_term_meta( $term_id, '_sk_list_visible', isset( $_POST['sk_list_visible'] ) ? '1' : '0' );
	update_term_meta( $term_id, '_sk_list_created_at', current_time( 'mysql' ) );
	update_term_meta(
		$term_id,
		'_sk_list_internal_description',
		isset( $_POST['sk_internal_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['sk_internal_description'] ) ) : ''
	);
	update_term_meta( $term_id, '_sk_list_confirmation_email', isset( $_POST['sk_confirmation_email'] ) ? absint( $_POST['sk_confirmation_email'] ) : 0 );
	update_term_meta( $term_id, '_sk_list_confirmation_page', isset( $_POST['sk_confirmation_page'] ) ? absint( $_POST['sk_confirmation_page'] ) : 0 );

	wp_safe_redirect( admin_url( 'term.php?taxonomy=list&tag_ID=' . $term_id . '&post_type=subscriber' ) );
	exit;
}
add_action( 'admin_post_sk_create_list', 'sk_handle_create_list' );

/**
 * Підписники, прив’язані до терміна list.
 *
 * @param int $term_id Term ID.
 * @return int[]
 */
function sk_get_list_subscriber_ids( $term_id ) {
	$term_id = (int) $term_id;
	if ( ! $term_id ) {
		return [];
	}

	$ids = get_objects_in_term( $term_id, 'list' );
	if ( is_wp_error( $ids ) || ! is_array( $ids ) ) {
		return [];
	}

	return array_values( array_unique( array_map( 'intval', $ids ) ) );
}

/**
 * Дані підписника для UI вибору.
 *
 * @param int|WP_Post $post Post ID або об’єкт.
 * @return array{id:int,email:string,name:string}|null
 */
function sk_format_subscriber_option( $post ) {
	$post = get_post( $post );
	if ( ! $post || 'subscriber' !== $post->post_type ) {
		return null;
	}

	$email      = sk_get_subscriber_email( $post );
	$first_name = (string) get_post_meta( $post->ID, 'sk_first_name', true );
	$last_name  = (string) get_post_meta( $post->ID, 'sk_last_name', true );
	$name       = trim( $first_name . ' ' . $last_name );

	return [
		'id'    => (int) $post->ID,
		'email' => $email ? $email : (string) $post->post_title,
		'name'  => $name,
	];
}

/**
 * Поле «Subscribers» на екрані редагування list.
 *
 * @param WP_Term $term Term.
 */
function sk_list_edit_form_fields( $term ) {
	if ( ! $term || empty( $term->term_id ) ) {
		return;
	}

	wp_nonce_field( 'sk_save_list_subscribers', 'sk_list_subscribers_nonce' );

	$selected_ids = sk_get_list_subscriber_ids( $term->term_id );
	$selected     = [];

	foreach ( $selected_ids as $post_id ) {
		$item = sk_format_subscriber_option( $post_id );
		if ( $item ) {
			$selected[] = $item;
		}
	}
	?>
	<tr class="form-field term-subscribers-wrap">
		<th scope="row">
			<label for="sk_list_subscriber_search"><?php esc_html_e( 'Subscribers', 'skyrora-mailing' ); ?></label>
		</th>
		<td>
			<div
				class="sk-list-subscribers"
				id="sk-list-subscribers"
				data-selected="<?php echo esc_attr( wp_json_encode( $selected ) ); ?>"
			>
				<div class="sk-list-subscribers__search-wrap">
					<input
						type="search"
						class="sk-list-subscribers__search"
						id="sk_list_subscriber_search"
						placeholder="<?php esc_attr_e( 'Search by email or name…', 'skyrora-mailing' ); ?>"
						autocomplete="off"
					>
					<ul class="sk-list-subscribers__results" id="sk_list_subscriber_results" hidden></ul>
				</div>

				<p class="sk-list-subscribers__hint description">
					<?php esc_html_e( 'Search and add subscribers to this list. Changes apply when you update the list.', 'skyrora-mailing' ); ?>
				</p>

				<div class="sk-list-subscribers__selected-head">
					<span class="sk-list-subscribers__count" id="sk_list_subscriber_count">
						<?php
						printf(
							/* translators: %d: number of selected subscribers */
							esc_html( _n( '%d subscriber', '%d subscribers', count( $selected ), 'skyrora-mailing' ) ),
							count( $selected )
						);
						?>
					</span>
				</div>

				<ul class="sk-list-subscribers__selected" id="sk_list_subscriber_selected"></ul>

				<div class="sk-list-subscribers__inputs" id="sk_list_subscriber_inputs">
					<?php foreach ( $selected as $item ) : ?>
						<input type="hidden" name="sk_list_subscribers[]" value="<?php echo esc_attr( $item['id'] ); ?>">
					<?php endforeach; ?>
				</div>
			</div>
		</td>
	</tr>
	<?php
}
add_action( 'list_edit_form_fields', 'sk_list_edit_form_fields' );

/**
 * Підказка на формі створення list: підписників можна додати після створення.
 */
function sk_list_add_form_fields() {
	?>
	<div class="form-field term-subscribers-wrap">
		<p class="description">
			<?php esc_html_e( 'After creating the list, open it to select subscribers.', 'skyrora-mailing' ); ?>
		</p>
	</div>
	<?php
}
add_action( 'list_add_form_fields', 'sk_list_add_form_fields' );

/**
 * Колонки огляду списків.
 *
 * @param array<string,string> $columns Columns.
 * @return array<string,string>
 */
function sk_list_admin_columns( $columns ) {
	return [
		'cb'              => isset( $columns['cb'] ) ? $columns['cb'] : '<input type="checkbox" />',
		'name'            => __( 'Name', 'skyrora-mailing' ),
		'description'     => __( 'Description', 'skyrora-mailing' ),
		'sk_list_score'   => __( 'List score', 'skyrora-mailing' ),
		'sk_subscribed'   => __( 'Subscribed', 'skyrora-mailing' ),
		'sk_unconfirmed'  => __( 'Unconfirmed', 'skyrora-mailing' ),
		'sk_unsubscribed' => __( 'Unsubscribed', 'skyrora-mailing' ),
		'sk_inactive'     => __( 'Inactive', 'skyrora-mailing' ),
		'sk_bounced'      => __( 'Bounced', 'skyrora-mailing' ),
		'sk_created'      => __( 'Created on', 'skyrora-mailing' ),
	];
}
add_filter( 'manage_edit-list_columns', 'sk_list_admin_columns' );

/**
 * Підрахунок підписників у списку за статусами.
 *
 * @param int $term_id List term ID.
 * @return array<string,int>
 */
function sk_get_list_status_counts( $term_id ) {
	static $cache = [];

	$term_id = (int) $term_id;
	if ( isset( $cache[ $term_id ] ) ) {
		return $cache[ $term_id ];
	}

	$counts = [
		'subscribed'   => 0,
		'unconfirmed'  => 0,
		'unsubscribed' => 0,
		'inactive'     => 0,
		'bounced'      => 0,
	];

	foreach ( sk_get_list_subscriber_ids( $term_id ) as $subscriber_id ) {
		$status = sk_get_subscriber_status( $subscriber_id );
		if ( isset( $counts[ $status ] ) ) {
			$counts[ $status ]++;
		}
	}

	$cache[ $term_id ] = $counts;
	return $counts;
}

/**
 * Значення кастомних колонок огляду Lists.
 *
 * @param string $content     Existing content.
 * @param string $column_name Column key.
 * @param int    $term_id     List term ID.
 * @return string
 */
function sk_list_admin_column_content( $content, $column_name, $term_id ) {
	$status_columns = [
		'sk_subscribed'   => 'subscribed',
		'sk_unconfirmed'  => 'unconfirmed',
		'sk_unsubscribed' => 'unsubscribed',
		'sk_inactive'     => 'inactive',
		'sk_bounced'      => 'bounced',
	];

	if ( isset( $status_columns[ $column_name ] ) ) {
		$counts = sk_get_list_status_counts( $term_id );
		return esc_html( number_format_i18n( $counts[ $status_columns[ $column_name ] ] ) );
	}

	if ( 'sk_list_score' === $column_name ) {
		return '<span class="sk-list-score sk-list-score--unknown">' . esc_html__( 'Unknown', 'skyrora-mailing' ) . '</span>';
	}

	if ( 'sk_created' === $column_name ) {
		$created_at = (string) get_term_meta( $term_id, '_sk_list_created_at', true );
		if ( ! $created_at ) {
			return '<span class="sk-list-created-empty">&mdash;</span>';
		}

		$timestamp = mysql2date( 'U', $created_at, false );
		if ( ! $timestamp ) {
			return '<span class="sk-list-created-empty">&mdash;</span>';
		}

		return sprintf(
			'<time datetime="%1$s">%2$s<br><span>%3$s</span></time>',
			esc_attr( mysql2date( 'c', $created_at, false ) ),
			esc_html( date_i18n( get_option( 'date_format' ), $timestamp ) ),
			esc_html( date_i18n( get_option( 'time_format' ), $timestamp ) )
		);
	}

	return $content;
}
add_filter( 'manage_list_custom_column', 'sk_list_admin_column_content', 10, 3 );

/**
 * Додаємо "(private)" до непублічних списків.
 *
 * @param string  $name Term name.
 * @param WP_Term $term Term object.
 * @return string
 */
function sk_list_private_name_label( $name, $term ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! is_admin() || ! $screen || 'edit-list' !== $screen->id || ! $term instanceof WP_Term || 'list' !== $term->taxonomy ) {
		return $name;
	}

	if ( '1' !== (string) get_term_meta( $term->term_id, '_sk_list_visible', true ) ) {
		$name .= ' (' . esc_html__( 'private', 'skyrora-mailing' ) . ')';
	}

	return $name;
}
add_filter( 'term_name', 'sk_list_private_name_label', 10, 2 );

/**
 * Сортування колонок Lists.
 *
 * @param array<string,string> $columns Sortable columns.
 * @return array<string,string>
 */
function sk_list_sortable_columns( $columns ) {
	$columns['sk_created'] = 'term_id';
	return $columns;
}
add_filter( 'manage_edit-list_sortable_columns', 'sk_list_sortable_columns' );

/**
 * Кількість списків на сторінці для кастомного Appearance-перемикача.
 *
 * @param int $per_page Current value.
 * @return int
 */
function sk_list_items_per_page( $per_page ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'edit-list' !== $screen->id ) {
		return $per_page;
	}

	$requested = isset( $_GET['sk_per_page'] ) ? absint( $_GET['sk_per_page'] ) : 10;
	return in_array( $requested, [ 10, 20, 50, 100 ], true ) ? $requested : 10;
}
add_filter( 'edit_list_per_page', 'sk_list_items_per_page' );

/**
 * Поточний статус на екрані Lists.
 *
 * @return string
 */
function sk_get_list_admin_status() {
	$status = isset( $_GET['sk_list_status'] ) ? sanitize_key( wp_unslash( $_GET['sk_list_status'] ) ) : 'all';
	return 'trash' === $status ? 'trash' : 'all';
}

/**
 * Фільтруємо таблицю Lists між активними списками та кошиком.
 *
 * @param WP_Term_Query $query Term query.
 */
function sk_filter_list_admin_terms( $query ) {
	if ( ! is_admin() ) {
		return;
	}

	$screen     = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	$taxonomies = isset( $query->query_vars['taxonomy'] ) ? (array) $query->query_vars['taxonomy'] : [];

	if ( ! $screen || 'edit-list' !== $screen->id || ! in_array( 'list', $taxonomies, true ) ) {
		return;
	}

	// WordPress disables pagination for hierarchical taxonomies unless orderby is set.
	if ( 'all' === $query->query_vars['fields'] && empty( $query->query_vars['number'] ) ) {
		$per_page = sk_list_items_per_page( 10 );
		$page     = isset( $query->query_vars['page'] ) ? max( 1, absint( $query->query_vars['page'] ) ) : 1;

		$query->query_vars['orderby'] = 'name';
		$query->query_vars['order']   = 'ASC';
		$query->query_vars['number']  = $per_page;
		$query->query_vars['offset']  = ( $page - 1 ) * $per_page;
	}

	if ( 'trash' === sk_get_list_admin_status() ) {
		$query->query_vars['meta_query'] = [
			[
				'key'   => '_sk_list_trashed',
				'value' => '1',
			],
		];
		return;
	}

	$query->query_vars['meta_query'] = [
		'relation' => 'OR',
		[
			'key'     => '_sk_list_trashed',
			'compare' => 'NOT EXISTS',
		],
		[
			'key'     => '_sk_list_trashed',
			'value'   => '1',
			'compare' => '!=',
		],
	];
}
add_action( 'pre_get_terms', 'sk_filter_list_admin_terms' );

/**
 * Виводимо таби All / Trash над таблицею Lists.
 */
function sk_list_status_tabs() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'edit-list' !== $screen->id || 'edit-tags' !== $screen->base ) {
		return;
	}

	// Підрахунок має включати обидва стани, тому тимчасово вимикаємо admin-фільтр.
	remove_action( 'pre_get_terms', 'sk_filter_list_admin_terms' );
	$total_count = (int) wp_count_terms( [
		'taxonomy'   => 'list',
		'hide_empty' => false,
	] );
	$trash_count = (int) get_terms( [
		'taxonomy'   => 'list',
		'hide_empty' => false,
		'fields'     => 'count',
		'meta_query' => [
			[
				'key'   => '_sk_list_trashed',
				'value' => '1',
			],
		],
	] );
	add_action( 'pre_get_terms', 'sk_filter_list_admin_terms' );

	$current   = sk_get_list_admin_status();
	$all_count = max( 0, $total_count - $trash_count );
	$base_url  = admin_url( 'edit-tags.php?taxonomy=list&post_type=subscriber' );
	?>
	<ul class="subsubsub sk-list-status-tabs">
		<li class="all">
			<a href="<?php echo esc_url( $base_url ); ?>"<?php echo 'all' === $current ? ' class="current" aria-current="page"' : ''; ?>>
				<?php esc_html_e( 'All', 'skyrora-mailing' ); ?>
				<span class="count"><?php echo esc_html( number_format_i18n( $all_count ) ); ?></span>
			</a>
		</li>
		<li class="trash">
			<a href="<?php echo esc_url( add_query_arg( 'sk_list_status', 'trash', $base_url ) ); ?>"<?php echo 'trash' === $current ? ' class="current" aria-current="page"' : ''; ?>>
				<?php esc_html_e( 'Trash', 'skyrora-mailing' ); ?>
				<span class="count"><?php echo esc_html( number_format_i18n( $trash_count ) ); ?></span>
			</a>
		</li>
	</ul>
	<?php
}
add_action( 'admin_notices', 'sk_list_status_tabs' );

/**
 * Замінюємо видалення списку на переміщення в кошик.
 *
 * @param array<string,string> $actions Row actions.
 * @param WP_Term             $term    List term.
 * @return array<string,string>
 */
function sk_list_row_actions( $actions, $term ) {
	if ( ! $term || 'list' !== $term->taxonomy ) {
		return $actions;
	}

	$is_trash = '1' === (string) get_term_meta( $term->term_id, '_sk_list_trashed', true );
	unset( $actions['delete'] );

	if ( $is_trash ) {
		unset( $actions['edit'], $actions['inline hide-if-no-js'] );
		$actions['restore'] = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( wp_nonce_url( admin_url( 'admin.php?action=sk_restore_list&term_id=' . $term->term_id ), 'sk_restore_list_' . $term->term_id ) ),
			esc_html__( 'Restore', 'skyrora-mailing' )
		);
		$actions['delete'] = sprintf(
			'<a class="delete" href="%1$s">%2$s</a>',
			esc_url( wp_nonce_url( admin_url( 'admin.php?action=sk_delete_list&term_id=' . $term->term_id ), 'sk_delete_list_' . $term->term_id ) ),
			esc_html__( 'Delete Permanently', 'skyrora-mailing' )
		);
	} else {
		$actions['trash'] = sprintf(
			'<a class="submitdelete" href="%1$s">%2$s</a>',
			esc_url( wp_nonce_url( admin_url( 'admin.php?action=sk_trash_list&term_id=' . $term->term_id ), 'sk_trash_list_' . $term->term_id ) ),
			esc_html__( 'Trash', 'skyrora-mailing' )
		);
	}

	return $actions;
}
add_filter( 'list_row_actions', 'sk_list_row_actions', 10, 2 );

/**
 * Обробляємо Trash, Restore і Delete Permanently для Lists.
 */
function sk_handle_list_status_action() {
	$action  = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
	$term_id = isset( $_GET['term_id'] ) ? absint( $_GET['term_id'] ) : 0;
	$term    = $term_id ? get_term( $term_id, 'list' ) : null;

	if ( ! $term || is_wp_error( $term ) ) {
		wp_die( esc_html__( 'List not found.', 'skyrora-mailing' ) );
	}

	$taxonomy = get_taxonomy( 'list' );
	if ( ! $taxonomy || ! current_user_can( $taxonomy->cap->delete_terms ) ) {
		wp_die( esc_html__( 'You are not allowed to manage this list.', 'skyrora-mailing' ) );
	}

	$redirect_status = 'all';
	if ( 'sk_trash_list' === $action ) {
		check_admin_referer( 'sk_trash_list_' . $term_id );
		update_term_meta( $term_id, '_sk_list_trashed', '1' );
	} elseif ( 'sk_restore_list' === $action ) {
		check_admin_referer( 'sk_restore_list_' . $term_id );
		delete_term_meta( $term_id, '_sk_list_trashed' );
		$redirect_status = 'trash';
	} elseif ( 'sk_delete_list' === $action ) {
		check_admin_referer( 'sk_delete_list_' . $term_id );
		wp_delete_term( $term_id, 'list' );
		$redirect_status = 'trash';
	}

	$url = admin_url( 'edit-tags.php?taxonomy=list&post_type=subscriber' );
	if ( 'trash' === $redirect_status ) {
		$url = add_query_arg( 'sk_list_status', 'trash', $url );
	}
	wp_safe_redirect( $url );
	exit;
}
add_action( 'admin_action_sk_trash_list', 'sk_handle_list_status_action' );
add_action( 'admin_action_sk_restore_list', 'sk_handle_list_status_action' );
add_action( 'admin_action_sk_delete_list', 'sk_handle_list_status_action' );

/**
 * Синхронізація підписників зі списком (додати / прибрати лише цей термін).
 *
 * @param int   $term_id Term ID.
 * @param int[] $new_ids Subscriber post IDs.
 */
function sk_sync_list_subscribers( $term_id, $new_ids ) {
	$term_id = (int) $term_id;
	$new_ids = array_values( array_unique( array_filter( array_map( 'intval', (array) $new_ids ) ) ) );

	$current = sk_get_list_subscriber_ids( $term_id );
	$to_add  = array_diff( $new_ids, $current );
	$to_remove = array_diff( $current, $new_ids );

	foreach ( $to_add as $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || 'subscriber' !== $post->post_type ) {
			continue;
		}
		wp_set_object_terms( $post_id, [ $term_id ], 'list', true );
	}

	foreach ( $to_remove as $post_id ) {
		wp_remove_object_terms( $post_id, $term_id, 'list' );
	}
}

/**
 * Збереження підписників при оновленні list.
 *
 * @param int $term_id Term ID.
 */
function sk_save_list_subscribers( $term_id ) {
	if ( ! isset( $_POST['sk_list_subscribers_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sk_list_subscribers_nonce'] ) ), 'sk_save_list_subscribers' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}

	$ids = isset( $_POST['sk_list_subscribers'] ) ? (array) wp_unslash( $_POST['sk_list_subscribers'] ) : [];
	sk_sync_list_subscribers( $term_id, $ids );
}
add_action( 'edited_list', 'sk_save_list_subscribers' );

/**
 * AJAX: пошук підписників для UI list.
 */
function sk_ajax_search_subscribers() {
	check_ajax_referer( 'sk_list_subscribers', 'nonce' );

	if ( ! current_user_can( 'manage_categories' ) && ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( [ 'message' => __( 'Permission denied.', 'skyrora-mailing' ) ], 403 );
	}

	$search     = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
	$exclude    = isset( $_GET['exclude'] ) ? (array) wp_unslash( $_GET['exclude'] ) : [];
	$exclude    = array_filter( array_map( 'absint', $exclude ) );
	$term_id    = isset( $_GET['term_id'] ) ? absint( $_GET['term_id'] ) : 0;

	$query_args = [
		'post_type'              => 'subscriber',
		'post_status'            => [ 'publish', 'draft', 'pending', 'private', 'future' ],
		'posts_per_page'         => 20,
		'orderby'                => 'title',
		'order'                  => 'ASC',
		'no_found_rows'          => true,
		'update_post_meta_cache' => true,
		'update_post_term_cache' => false,
	];

	if ( $exclude ) {
		$query_args['post__not_in'] = $exclude;
	}

	if ( '' !== $search ) {
		global $wpdb;

		$like = '%' . $wpdb->esc_like( $search ) . '%';

		// Title (email) OR first/last name meta.
		$meta_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT post_id FROM {$wpdb->postmeta}
				WHERE meta_key IN ('sk_first_name','sk_last_name','sk_email')
				AND meta_value LIKE %s
				LIMIT 100",
				$like
			)
		);

		$title_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts}
				WHERE post_type = 'subscriber'
				AND post_status IN ('publish','draft','pending','private','future')
				AND post_title LIKE %s
				LIMIT 100",
				$like
			)
		);

		$ids = array_values( array_unique( array_merge(
			array_map( 'intval', (array) $meta_ids ),
			array_map( 'intval', (array) $title_ids )
		) ) );

		if ( $exclude ) {
			$ids = array_values( array_diff( $ids, $exclude ) );
		}

		if ( ! $ids ) {
			wp_send_json_success( [ 'items' => [] ] );
		}

		$query_args['post__in'] = $ids;
		$query_args['orderby']  = 'post__in';
		unset( $query_args['post__not_in'] );
	}

	$query = new WP_Query( $query_args );
	$items = [];

	foreach ( $query->posts as $post ) {
		$item = sk_format_subscriber_option( $post );
		if ( $item ) {
			$items[] = $item;
		}
	}

	wp_send_json_success( [
		'items'   => $items,
		'term_id' => $term_id,
	] );
}
add_action( 'wp_ajax_sk_search_subscribers', 'sk_ajax_search_subscribers' );
