<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Campaign statuses and their admin labels.
 *
 * @return array<string,string>
 */
function sk_get_campaign_statuses() {
	return [
		'draft'        => __( 'Draft', 'skyrora-mailing' ),
		'scheduled'    => __( 'Scheduled', 'skyrora-mailing' ),
		'not_sent_yet' => __( 'Not sent yet!', 'skyrora-mailing' ),
		'sending'      => __( 'Sending...', 'skyrora-mailing' ),
		'sent'         => __( 'Sent', 'skyrora-mailing' ),
		'paused'       => __( 'Paused', 'skyrora-mailing' ),
		'failed'       => __( 'Failed', 'skyrora-mailing' ),
	];
}

/**
 * Present the mailing model as Emails in the WordPress admin.
 *
 * @param array<string,mixed> $args      Post type arguments.
 * @param string              $post_type Post type key.
 * @return array<string,mixed>
 */
function sk_campaign_post_type_args( $args, $post_type ) {
	if ( 'mailing' !== $post_type ) {
		return $args;
	}

	$args['label']                   = __( 'Emails', 'skyrora-mailing' );
	$args['labels']['name']          = __( 'Emails', 'skyrora-mailing' );
	$args['labels']['singular_name'] = __( 'Email', 'skyrora-mailing' );
	$args['labels']['menu_name']     = __( 'Emails', 'skyrora-mailing' );
	$args['labels']['all_items']     = __( 'Emails', 'skyrora-mailing' );
	$args['labels']['add_new_item']  = __( 'Add New Email', 'skyrora-mailing' );

	return $args;
}
add_filter( 'register_post_type_args', 'sk_campaign_post_type_args', 10, 2 );

/**
 * Normalize statuses stored by older plugin versions.
 *
 * @param string $status Stored status.
 * @return string
 */
function sk_normalize_campaign_status( $status ) {
	$legacy_statuses = [
		'queued'           => 'scheduled',
		'processing'       => 'sending',
		'completed'        => 'sent',
		'partially_failed' => 'failed',
	];

	return $legacy_statuses[ $status ] ?? $status;
}

/**
 * Sanitize a status supplied through the REST API.
 *
 * @param string $status Requested status.
 * @return string
 */
function sk_sanitize_campaign_status( $status ) {
	$status = sanitize_key( $status );
	return isset( sk_get_campaign_statuses()[ $status ] ) ? $status : 'draft';
}

/**
 * Get a campaign or campaign-job status.
 *
 * @param int $post_id Campaign or job post ID.
 * @return string
 */
function sk_get_campaign_status( $post_id ) {
	$status = '';
	if ( metadata_exists( 'post', $post_id, '_sk_campaign_status' ) ) {
		$status = sk_normalize_campaign_status( (string) get_post_meta( $post_id, '_sk_campaign_status', true ) );
	}

	if ( ! $status ) {
		$status = sk_normalize_campaign_status( (string) get_post_meta( $post_id, '_sk_job_status', true ) );
	}

	return isset( sk_get_campaign_statuses()[ $status ] ) ? $status : 'draft';
}

/**
 * Set a valid status and keep a send job's parent campaign in sync.
 *
 * @param int    $post_id Campaign or job post ID.
 * @param string $status Campaign status.
 * @return bool
 */
function sk_set_campaign_status( $post_id, $status ) {
	$post_id = absint( $post_id );
	$status  = sanitize_key( $status );
	$post    = get_post( $post_id );

	if ( ! $post || ! isset( sk_get_campaign_statuses()[ $status ] ) ) {
		return false;
	}

	update_post_meta( $post_id, '_sk_campaign_status', $status );

	if ( 'subscription' === $post->post_type ) {
		update_post_meta( $post_id, '_sk_job_status', $status );
		if ( $post->post_parent ) {
			update_post_meta( $post->post_parent, '_sk_campaign_status', $status );
		}
	}

	return true;
}

/**
 * Register the campaign status field for campaigns and their send jobs.
 */
function sk_register_campaign_status_meta() {
	foreach ( [ 'mailing', 'subscription' ] as $post_type ) {
		register_post_meta(
			$post_type,
			'_sk_campaign_status',
			[
				'type'              => 'string',
				'single'            => true,
				'default'           => 'draft',
				'sanitize_callback' => 'sk_sanitize_campaign_status',
				'auth_callback'     => static function() {
					return current_user_can( 'edit_posts' );
				},
				'show_in_rest'      => true,
			]
		);
	}
}
add_action( 'init', 'sk_register_campaign_status_meta', 20 );

/**
 * New campaigns always begin as drafts.
 *
 * @param int $post_id Mailing post ID.
 */
function sk_initialize_campaign_status( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( ! metadata_exists( 'post', $post_id, '_sk_campaign_status' ) ) {
		sk_set_campaign_status( $post_id, 'draft' );
	}
}
add_action( 'save_post_mailing', 'sk_initialize_campaign_status' );

/**
 * Add status to the Campaigns/Templates list table.
 *
 * @param array<string,string> $columns Existing columns.
 * @return array<string,string>
 */
function sk_campaign_status_column( $columns ) {
	$result = [];

	foreach ( $columns as $key => $label ) {
		$result[ $key ] = 'title' === $key ? __( 'Subject', 'skyrora-mailing' ) : $label;
		if ( 'title' === $key ) {
			$result['sk_campaign_status'] = __( 'Status', 'skyrora-mailing' );
		}
	}

	return $result;
}
add_filter( 'manage_mailing_posts_columns', 'sk_campaign_status_column' );

/**
 * Render a campaign status badge.
 *
 * @param string $column  Column key.
 * @param int    $post_id Mailing post ID.
 */
function sk_render_campaign_status_column( $column, $post_id ) {
	if ( 'sk_campaign_status' !== $column ) {
		return;
	}

	$status = sk_get_campaign_status( $post_id );
	$labels = sk_get_campaign_statuses();

	printf(
		'<span class="sk-campaign-status is-%1$s">%2$s</span>',
		esc_attr( $status ),
		esc_html( $labels[ $status ] )
	);
}
add_action( 'manage_mailing_posts_custom_column', 'sk_render_campaign_status_column', 10, 2 );

/**
 * Render campaign status tabs above the Emails table.
 *
 * @param array<string,string> $views Default WordPress views.
 * @return array<string,string>
 */
function sk_campaign_status_views( $views ) {
	global $wpdb;

	$statuses     = sk_get_campaign_statuses();
	$status_slugs = array_keys( $statuses );
	$placeholders = implode( ', ', array_fill( 0, count( $status_slugs ), '%s' ) );
	$query_args   = array_merge( $status_slugs, [ 'mailing' ] );

	$sql = "
		SELECT
			CASE
				WHEN p.post_status = 'trash' THEN 'trash'
				WHEN pm.meta_value = 'queued' THEN 'scheduled'
				WHEN pm.meta_value = 'processing' THEN 'sending'
				WHEN pm.meta_value = 'completed' THEN 'sent'
				WHEN pm.meta_value = 'partially_failed' THEN 'failed'
				WHEN pm.meta_value IN ({$placeholders}) THEN pm.meta_value
				ELSE 'draft'
			END AS campaign_status,
			COUNT(DISTINCT p.ID) AS campaign_count
		FROM {$wpdb->posts} p
		LEFT JOIN {$wpdb->postmeta} pm
			ON p.ID = pm.post_id
			AND pm.meta_key = '_sk_campaign_status'
		WHERE p.post_type = %s
			AND p.post_status IN ('publish', 'future', 'draft', 'pending', 'private', 'trash')
		GROUP BY campaign_status
	";

	$rows   = $wpdb->get_results( $wpdb->prepare( $sql, $query_args ), OBJECT_K );
	$counts = array_fill_keys( array_merge( $status_slugs, [ 'trash' ] ), 0 );

	foreach ( $rows as $status => $row ) {
		if ( isset( $counts[ $status ] ) ) {
			$counts[ $status ] = (int) $row->campaign_count;
		}
	}

	$current_status = isset( $_GET['sk_campaign_status'] ) ? sanitize_key( wp_unslash( $_GET['sk_campaign_status'] ) ) : '';
	$base_url       = admin_url( 'edit.php?post_type=mailing' );
	$custom_views   = [];
	$all_current    = ! isset( $counts[ $current_status ] );
	$all_count      = array_sum( $counts ) - $counts['trash'];

	$custom_views['all'] = sprintf(
		'<a href="%1$s"%2$s>%3$s <span class="count">%4$s</span></a>',
		esc_url( $base_url ),
		$all_current ? ' class="current" aria-current="page"' : '',
		esc_html__( 'All', 'skyrora-mailing' ),
		number_format_i18n( $all_count )
	);

	foreach ( $statuses as $status => $label ) {
		if ( 'sending' === $status ) {
			continue;
		}

		$custom_views[ $status ] = sprintf(
			'<a href="%1$s"%2$s>%3$s <span class="count">%4$s</span></a>',
			esc_url( add_query_arg( 'sk_campaign_status', $status, $base_url ) ),
			$current_status === $status ? ' class="current" aria-current="page"' : '',
			esc_html( $label ),
			number_format_i18n( $counts[ $status ] )
		);
	}

	$custom_views['trash'] = sprintf(
		'<a href="%1$s"%2$s>%3$s <span class="count">%4$s</span></a>',
		esc_url( add_query_arg( 'sk_campaign_status', 'trash', $base_url ) ),
		'trash' === $current_status ? ' class="current" aria-current="page"' : '',
		esc_html__( 'Trash', 'skyrora-mailing' ),
		number_format_i18n( $counts['trash'] )
	);

	return $custom_views;
}
add_filter( 'views_edit-mailing', 'sk_campaign_status_views' );

/**
 * Add campaign status filtering to the Emails toolbar.
 */
function sk_campaign_status_filter() {
	global $typenow;

	if ( 'mailing' !== $typenow ) {
		return;
	}

	$current_status = isset( $_GET['sk_campaign_status'] ) ? sanitize_key( wp_unslash( $_GET['sk_campaign_status'] ) ) : '';
	?>
	<select name="sk_campaign_status">
		<option value=""><?php esc_html_e( 'All statuses', 'skyrora-mailing' ); ?></option>
		<?php foreach ( sk_get_campaign_statuses() as $status => $label ) : ?>
			<option value="<?php echo esc_attr( $status ); ?>" <?php selected( $current_status, $status ); ?>>
				<?php echo esc_html( $label ); ?>
			</option>
		<?php endforeach; ?>
		<option value="trash" <?php selected( $current_status, 'trash' ); ?>><?php esc_html_e( 'Trash', 'skyrora-mailing' ); ?></option>
	</select>
	<?php
}
add_action( 'restrict_manage_posts', 'sk_campaign_status_filter' );

/**
 * Apply campaign status filtering to the Emails admin query.
 *
 * @param WP_Query $query Current query.
 */
function sk_filter_campaigns_by_status( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || 'mailing' !== $query->get( 'post_type' ) ) {
		return;
	}

	$status = isset( $_GET['sk_campaign_status'] ) ? sanitize_key( wp_unslash( $_GET['sk_campaign_status'] ) ) : '';
	if ( 'trash' === $status ) {
		$query->set( 'post_status', 'trash' );
		return;
	}

	if ( ! isset( sk_get_campaign_statuses()[ $status ] ) ) {
		return;
	}

	$legacy_statuses = [
		'scheduled' => 'queued',
		'sending'   => 'processing',
		'sent'      => 'completed',
		'failed'    => 'partially_failed',
	];

	if ( 'draft' === $status ) {
		$known_statuses = array_merge( array_keys( sk_get_campaign_statuses() ), array_values( $legacy_statuses ) );
		$query->set(
			'meta_query',
			[
				'relation' => 'OR',
				[
					'key'     => '_sk_campaign_status',
					'value'   => 'draft',
					'compare' => '=',
				],
				[
					'key'     => '_sk_campaign_status',
					'compare' => 'NOT EXISTS',
				],
				[
					'key'     => '_sk_campaign_status',
					'value'   => '',
					'compare' => '=',
				],
				[
					'key'     => '_sk_campaign_status',
					'value'   => $known_statuses,
					'compare' => 'NOT IN',
				],
			]
		);
		return;
	}

	$values = isset( $legacy_statuses[ $status ] ) ? [ $status, $legacy_statuses[ $status ] ] : [ $status ];
	$query->set(
		'meta_query',
		[
			[
				'key'     => '_sk_campaign_status',
				'value'   => $values,
				'compare' => 'IN',
			],
		]
	);
}
add_action( 'pre_get_posts', 'sk_filter_campaigns_by_status' );

/**
 * Add an administrator pause action for active campaigns.
 *
 * @param array<string,string> $actions Row actions.
 * @param WP_Post              $post    Current post.
 * @return array<string,string>
 */
function sk_campaign_row_actions( $actions, $post ) {
	if ( 'mailing' !== $post->post_type || ! in_array( sk_get_campaign_status( $post->ID ), [ 'scheduled', 'not_sent_yet', 'sending' ], true ) ) {
		return $actions;
	}

	$url = wp_nonce_url(
		add_query_arg(
			[
				'action'      => 'sk_pause_campaign',
				'campaign_id' => $post->ID,
			],
			admin_url( 'admin-post.php' )
		),
		'sk_pause_campaign_' . $post->ID
	);

	$actions['sk_pause_campaign'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Pause', 'skyrora-mailing' ) . '</a>';
	return $actions;
}
add_filter( 'post_row_actions', 'sk_campaign_row_actions', 10, 2 );

/**
 * Pause a campaign and any active queued job.
 */
function sk_handle_pause_campaign() {
	$campaign_id = isset( $_GET['campaign_id'] ) ? absint( $_GET['campaign_id'] ) : 0;

	check_admin_referer( 'sk_pause_campaign_' . $campaign_id );
	if ( ! $campaign_id || ! current_user_can( 'edit_post', $campaign_id ) || 'mailing' !== get_post_type( $campaign_id ) ) {
		wp_die( esc_html__( 'You do not have permission to pause this campaign.', 'skyrora-mailing' ) );
	}

	$job_ids = get_posts(
		[
			'post_type'      => 'subscription',
			'post_status'    => 'publish',
			'post_parent'    => $campaign_id,
			'fields'         => 'ids',
			'posts_per_page' => -1,
			'meta_query'     => [
				[
					'key'     => '_sk_job_status',
					'value'   => [ 'scheduled', 'not_sent_yet', 'sending', 'queued', 'processing' ],
					'compare' => 'IN',
				],
			],
		]
	);

	foreach ( $job_ids as $job_id ) {
		$timestamp = (int) get_post_meta( $job_id, '_sk_scheduled_at', true );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'sk_send_scheduled_mailing', [ $job_id ] );
		}
		sk_set_campaign_status( $job_id, 'paused' );
	}

	sk_set_campaign_status( $campaign_id, 'paused' );
	wp_safe_redirect( admin_url( 'edit.php?post_type=mailing' ) );
	exit;
}
add_action( 'admin_post_sk_pause_campaign', 'sk_handle_pause_campaign' );
