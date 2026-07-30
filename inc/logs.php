<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Labels for send request modes.
 *
 * @return array<string,string>
 */
function sk_get_send_modes() {
	return [
		'list'      => __( 'List', 'skyrora-mailing' ),
		'single'    => __( 'Single', 'skyrora-mailing' ),
		'test'      => __( 'Test', 'skyrora-mailing' ),
		'scheduled' => __( 'Scheduled', 'skyrora-mailing' ),
	];
}

function sk_log_mailing_request( $args ) {
	$post_id = isset( $args['post_id'] ) ? absint( $args['post_id'] ) : 0;
	$subject = isset( $args['subject'] ) ? (string) $args['subject'] : '';
	$html    = isset( $args['html'] ) ? (string) $args['html'] : '';
	$emails  = isset( $args['emails'] ) && is_array( $args['emails'] ) ? $args['emails'] : [];
	$emails  = array_values( array_unique( array_filter( array_map( 'sanitize_email', $emails ), 'is_email' ) ) );
	$mode    = isset( $args['mode'] ) ? sanitize_key( $args['mode'] ) : '';
	$status  = isset( $args['status'] ) ? sanitize_key( $args['status'] ) : 'sent';
	$modes   = sk_get_send_modes();

	if ( ! $post_id || 'mailing' !== get_post_type( $post_id ) ) {
		return new WP_Error( 'invalid_post', __( 'Invalid mailing template.', 'skyrora-mailing' ) );
	}
	if ( '' === $subject ) {
		return new WP_Error( 'empty_subject', __( 'Please enter a subject.', 'skyrora-mailing' ) );
	}
	if ( ! array_key_exists( $mode, $modes ) ) {
		return new WP_Error( 'invalid_mode', __( 'Invalid send mode.', 'skyrora-mailing' ) );
	}

	$statuses = function_exists( 'sk_get_campaign_statuses' ) ? sk_get_campaign_statuses() : [];
	if ( ! array_key_exists( $status, $statuses ) ) {
		$status = 'sent';
	}

	$now          = time();
	$scheduled_at = isset( $args['scheduled_at'] ) ? absint( $args['scheduled_at'] ) : $now;
	$completed_at = array_key_exists( 'completed_at', $args ) ? $args['completed_at'] : $now;
	$sent         = isset( $args['sent'] ) ? absint( $args['sent'] ) : 0;
	$failed       = isset( $args['failed'] ) ? absint( $args['failed'] ) : 0;
	$errors       = isset( $args['errors'] ) && is_array( $args['errors'] ) ? $args['errors'] : [];
	$list_ids     = isset( $args['list_ids'] ) && is_array( $args['list_ids'] )
		? array_values( array_unique( array_filter( array_map( 'absint', $args['list_ids'] ) ) ) )
		: [];

	$job_id = wp_insert_post(
		[
			'post_type'    => 'subscription',
			'post_status'  => 'publish',
			'post_title'   => wp_slash( $subject ),
			'post_content' => wp_slash( $html ),
			'post_parent'  => $post_id,
		],
		true
	);

	if ( is_wp_error( $job_id ) ) {
		return $job_id;
	}

	update_post_meta( $job_id, '_sk_recipients', $emails );
	update_post_meta( $job_id, '_sk_scheduled_at', $scheduled_at );
	update_post_meta( $job_id, '_sk_job_status', $status );
	update_post_meta( $job_id, '_sk_campaign_status', $status );
	update_post_meta( $job_id, '_sk_send_mode', $mode );
	update_post_meta( $job_id, '_sk_sent_count', $sent );
	update_post_meta( $job_id, '_sk_failed_count', $failed );
	update_post_meta( $job_id, '_sk_send_errors', $errors );
	update_post_meta( $job_id, '_sk_list_ids', $list_ids );

	if ( null !== $completed_at && '' !== $completed_at ) {
		update_post_meta( $job_id, '_sk_completed_at', absint( $completed_at ) );
	}

	return $job_id;
}

/**
 * Query send request logs.
 *
 * @param int $page Page number.
 * @param int $per_page Items per page.
 * @return WP_Query
 */
function sk_get_mailing_logs_query( $page = 1, $per_page = 20 ) {
	return new WP_Query(
		[
			'post_type'      => 'subscription',
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, absint( $per_page ) ),
			'paged'          => max( 1, absint( $page ) ),
			'meta_key'       => '_sk_scheduled_at',
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
		]
	);
}

/**
 * Human-readable summary for a log job.
 *
 * @param int $job_id Job post ID.
 * @return string
 */
function sk_get_log_result_summary( $job_id ) {
	$sent   = (int) get_post_meta( $job_id, '_sk_sent_count', true );
	$failed = (int) get_post_meta( $job_id, '_sk_failed_count', true );
	$status = function_exists( 'sk_get_campaign_status' ) ? sk_get_campaign_status( $job_id ) : '';

	if ( in_array( $status, [ 'scheduled', 'sending', 'paused', 'not_sent_yet' ], true ) && ( $sent + $failed ) < 1 ) {
		return '';
	}

	return sprintf(
		/* translators: 1: sent email count, 2: failed email count */
		__( 'Sent: %1$d; failed: %2$d.', 'skyrora-mailing' ),
		$sent,
		$failed
	);
}

/**
 * Render the Logs admin page.
 */
function sk_render_logs_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'skyrora-mailing' ) );
	}

	$job_id = isset( $_GET['job_id'] ) ? absint( $_GET['job_id'] ) : 0;
	if ( $job_id ) {
		sk_render_log_detail( $job_id );
		return;
	}

	$page      = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
	$logs      = sk_get_mailing_logs_query( $page, 20 );
	$modes     = sk_get_send_modes();
	$statuses  = function_exists( 'sk_get_campaign_statuses' ) ? sk_get_campaign_statuses() : [];
	$logs_url  = admin_url( 'admin.php?page=skyrora-mailing-logs' );
	?>
	<div class="wrap sk-logs-page">
		<h1><?php esc_html_e( 'Logs', 'skyrora-mailing' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'All mailing send requests — immediate, test, and scheduled.', 'skyrora-mailing' ); ?>
		</p>

		<table class="widefat striped sk-logs-table">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Date', 'skyrora-mailing' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Template', 'skyrora-mailing' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Subject', 'skyrora-mailing' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Type', 'skyrora-mailing' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Recipients', 'skyrora-mailing' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Status', 'skyrora-mailing' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Result', 'skyrora-mailing' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( $logs->have_posts() ) : ?>
					<?php while ( $logs->have_posts() ) : ?>
						<?php
						$logs->the_post();
						$current_job_id = get_the_ID();
						$template_id    = (int) wp_get_post_parent_id( $current_job_id );
						$template       = $template_id ? get_post( $template_id ) : null;
						$scheduled_at   = (int) get_post_meta( $current_job_id, '_sk_scheduled_at', true );
						$completed_at   = (int) get_post_meta( $current_job_id, '_sk_completed_at', true );
						$display_at     = $completed_at ?: $scheduled_at;
						$status         = function_exists( 'sk_get_campaign_status' ) ? sk_get_campaign_status( $current_job_id ) : '';
						$status_label   = isset( $statuses[ $status ] ) ? $statuses[ $status ] : $status;
						$mode           = (string) get_post_meta( $current_job_id, '_sk_send_mode', true );
						if ( '' === $mode ) {
							$mode = ( 'scheduled' === $status || $scheduled_at > time() ) ? 'scheduled' : 'list';
						}
						$mode_label = isset( $modes[ $mode ] ) ? $modes[ $mode ] : $mode;
						$recipients = get_post_meta( $current_job_id, '_sk_recipients', true );
						$recipients = is_array( $recipients ) ? $recipients : [];
						$result     = sk_get_log_result_summary( $current_job_id );
						$detail_url = add_query_arg( 'job_id', $current_job_id, $logs_url );
						?>
						<tr>
							<td>
								<?php
								echo $display_at
									? esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $display_at ) )
									: '&mdash;';
								?>
							</td>
							<td>
								<?php if ( $template ) : ?>
									<a href="<?php echo esc_url( get_edit_post_link( $template_id, 'raw' ) ); ?>">
										<?php echo esc_html( get_the_title( $template ) ?: __( '(no title)', 'skyrora-mailing' ) ); ?>
									</a>
								<?php else : ?>
									<?php esc_html_e( 'Deleted template', 'skyrora-mailing' ); ?>
								<?php endif; ?>
							</td>
							<td>
								<a href="<?php echo esc_url( $detail_url ); ?>">
									<?php echo esc_html( get_the_title( $current_job_id ) ?: __( '(no subject)', 'skyrora-mailing' ) ); ?>
								</a>
							</td>
							<td><?php echo esc_html( $mode_label ); ?></td>
							<td><?php echo esc_html( (string) count( $recipients ) ); ?></td>
							<td>
								<span class="sk-settings-status is-<?php echo esc_attr( $status ); ?>">
									<?php echo esc_html( $status_label ); ?>
								</span>
							</td>
							<td>
								<?php echo $result ? esc_html( $result ) : '&mdash;'; ?>
							</td>
						</tr>
					<?php endwhile; ?>
				<?php else : ?>
					<tr>
						<td colspan="7"><?php esc_html_e( 'No send requests found yet.', 'skyrora-mailing' ); ?></td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>

		<?php if ( $logs->max_num_pages > 1 ) : ?>
			<div class="tablenav bottom">
				<div class="tablenav-pages">
					<?php
					echo wp_kses_post(
						paginate_links(
							[
								'base'      => str_replace(
									'999999999',
									'%#%',
									add_query_arg( 'paged', '999999999', $logs_url )
								),
								'format'    => '',
								'current'   => $page,
								'total'     => (int) $logs->max_num_pages,
								'prev_text' => __( '&laquo;', 'skyrora-mailing' ),
								'next_text' => __( '&raquo;', 'skyrora-mailing' ),
							]
						)
					);
					?>
				</div>
			</div>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
	</div>
	<?php
}

/**
 * Render a single log request detail.
 *
 * @param int $job_id Job post ID.
 */
function sk_render_log_detail( $job_id ) {
	$job = get_post( $job_id );
	if ( ! $job || 'subscription' !== $job->post_type ) {
		wp_die( esc_html__( 'Log entry not found.', 'skyrora-mailing' ) );
	}

	$logs_url     = admin_url( 'admin.php?page=skyrora-mailing-logs' );
	$template_id  = (int) $job->post_parent;
	$template     = $template_id ? get_post( $template_id ) : null;
	$scheduled_at = (int) get_post_meta( $job_id, '_sk_scheduled_at', true );
	$completed_at = (int) get_post_meta( $job_id, '_sk_completed_at', true );
	$status       = function_exists( 'sk_get_campaign_status' ) ? sk_get_campaign_status( $job_id ) : '';
	$statuses     = function_exists( 'sk_get_campaign_statuses' ) ? sk_get_campaign_statuses() : [];
	$status_label = isset( $statuses[ $status ] ) ? $statuses[ $status ] : $status;
	$mode         = (string) get_post_meta( $job_id, '_sk_send_mode', true );
	$modes        = sk_get_send_modes();
	if ( '' === $mode ) {
		$mode = 'scheduled';
	}
	$mode_label = isset( $modes[ $mode ] ) ? $modes[ $mode ] : $mode;
	$recipients = get_post_meta( $job_id, '_sk_recipients', true );
	$recipients = is_array( $recipients ) ? $recipients : [];
	$sent       = (int) get_post_meta( $job_id, '_sk_sent_count', true );
	$failed     = (int) get_post_meta( $job_id, '_sk_failed_count', true );
	$errors     = get_post_meta( $job_id, '_sk_send_errors', true );
	$errors     = is_array( $errors ) ? $errors : [];
	$list_ids   = get_post_meta( $job_id, '_sk_list_ids', true );
	$list_ids   = is_array( $list_ids ) ? $list_ids : [];
	?>
	<div class="wrap sk-logs-page">
		<h1>
			<?php esc_html_e( 'Request details', 'skyrora-mailing' ); ?>
		</h1>
		<p>
			<a href="<?php echo esc_url( $logs_url ); ?>">
				&larr; <?php esc_html_e( 'Back to Logs', 'skyrora-mailing' ); ?>
			</a>
		</p>

		<section class="sk-settings-card sk-logs-detail">
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Subject', 'skyrora-mailing' ); ?></th>
					<td><?php echo esc_html( $job->post_title ?: __( '(no subject)', 'skyrora-mailing' ) ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Template', 'skyrora-mailing' ); ?></th>
					<td>
						<?php if ( $template ) : ?>
							<a href="<?php echo esc_url( get_edit_post_link( $template_id, 'raw' ) ); ?>">
								<?php echo esc_html( get_the_title( $template ) ?: __( '(no title)', 'skyrora-mailing' ) ); ?>
							</a>
						<?php else : ?>
							<?php esc_html_e( 'Deleted template', 'skyrora-mailing' ); ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Type', 'skyrora-mailing' ); ?></th>
					<td><?php echo esc_html( $mode_label ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Status', 'skyrora-mailing' ); ?></th>
					<td>
						<span class="sk-settings-status is-<?php echo esc_attr( $status ); ?>">
							<?php echo esc_html( $status_label ); ?>
						</span>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Scheduled', 'skyrora-mailing' ); ?></th>
					<td>
						<?php
						echo $scheduled_at
							? esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $scheduled_at ) )
							: '&mdash;';
						?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Completed', 'skyrora-mailing' ); ?></th>
					<td>
						<?php
						echo $completed_at
							? esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $completed_at ) )
							: '&mdash;';
						?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Result', 'skyrora-mailing' ); ?></th>
					<td>
						<?php
						echo esc_html(
							sprintf(
								/* translators: 1: sent email count, 2: failed email count */
								__( 'Sent: %1$d; failed: %2$d.', 'skyrora-mailing' ),
								$sent,
								$failed
							)
						);
						?>
					</td>
				</tr>
				<?php if ( $list_ids ) : ?>
					<tr>
						<th scope="row"><?php esc_html_e( 'Lists', 'skyrora-mailing' ); ?></th>
						<td>
							<?php
							$names = [];
							foreach ( $list_ids as $list_id ) {
								$term = get_term( $list_id, 'list' );
								if ( $term && ! is_wp_error( $term ) ) {
									$names[] = $term->name;
								}
							}
							echo $names ? esc_html( implode( ', ', $names ) ) : '&mdash;';
							?>
						</td>
					</tr>
				<?php endif; ?>
			</table>

			<h2><?php esc_html_e( 'Recipients', 'skyrora-mailing' ); ?></h2>
			<?php if ( $recipients ) : ?>
				<ul class="sk-logs-recipients">
					<?php foreach ( $recipients as $email ) : ?>
						<li><?php echo esc_html( $email ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="description"><?php esc_html_e( 'No recipients stored.', 'skyrora-mailing' ); ?></p>
			<?php endif; ?>

			<h2><?php esc_html_e( 'Errors', 'skyrora-mailing' ); ?></h2>
			<?php if ( $errors ) : ?>
				<table class="widefat striped sk-logs-errors">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Email', 'skyrora-mailing' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Error', 'skyrora-mailing' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $errors as $row ) : ?>
							<?php
							$email = is_array( $row ) && isset( $row['email'] ) ? (string) $row['email'] : '';
							$error = is_array( $row ) && isset( $row['error'] ) ? (string) $row['error'] : __( 'Unknown error', 'skyrora-mailing' );
							?>
							<tr>
								<td><?php echo esc_html( $email ?: '—' ); ?></td>
								<td><?php echo esc_html( $error ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p class="description"><?php esc_html_e( 'No errors for this request.', 'skyrora-mailing' ); ?></p>
			<?php endif; ?>
		</section>
	</div>
	<?php
}
