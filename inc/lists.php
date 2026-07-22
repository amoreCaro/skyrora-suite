<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
 * Прибираємо колонку Slug у таблиці Lists.
 *
 * @param array $columns Columns.
 * @return array
 */
function sk_list_remove_slug_column( $columns ) {
	unset( $columns['slug'] );
	return $columns;
}
add_filter( 'manage_edit-list_columns', 'sk_list_remove_slug_column' );

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
