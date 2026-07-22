<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const SK_MAILING_SETTINGS_OPTION = 'sk_mailing_settings';
const SK_MAILING_CRON_HOOK       = 'sk_mailing_cron_tick';

/**
 * Default plugin settings.
 *
 * @return array<string, mixed>
 */
function sk_mailing_settings_defaults() {
	return [
		'smtp_enabled'    => 0,
		'smtp_host'       => '',
		'smtp_port'       => 587,
		'smtp_encryption' => 'tls',
		'smtp_auth'       => 1,
		'smtp_username'   => '',
		'smtp_password'   => '',
		'from_email'      => '',
		'from_name'       => '',
		'cron_enabled'    => 0,
		'cron_interval'   => 5,
	];
}

/**
 * Get normalized plugin settings.
 *
 * @return array<string, mixed>
 */
function sk_get_mailing_settings() {
	$settings = get_option( SK_MAILING_SETTINGS_OPTION, [] );

	return wp_parse_args( is_array( $settings ) ? $settings : [], sk_mailing_settings_defaults() );
}

/**
 * Register the settings form.
 */
function sk_register_mailing_settings() {
	register_setting(
		'sk_mailing_settings_group',
		SK_MAILING_SETTINGS_OPTION,
		[
			'type'              => 'array',
			'sanitize_callback' => 'sk_sanitize_mailing_settings',
			'default'           => sk_mailing_settings_defaults(),
		]
	);
}
add_action( 'admin_init', 'sk_register_mailing_settings' );

/**
 * Validate settings submitted by an administrator.
 *
 * @param mixed $input Submitted value.
 * @return array<string, mixed>
 */
function sk_sanitize_mailing_settings( $input ) {
	$input    = is_array( $input ) ? $input : [];
	$current  = sk_get_mailing_settings();
	$allowed  = [ 1, 5, 10, 15, 30, 60 ];
	$interval = isset( $input['cron_interval'] ) ? absint( $input['cron_interval'] ) : (int) $current['cron_interval'];
	$port     = isset( $input['smtp_port'] ) ? absint( $input['smtp_port'] ) : 587;
	$password = isset( $input['smtp_password'] ) ? (string) $input['smtp_password'] : '';

	if ( '' === $password ) {
		$password = (string) $current['smtp_password'];
	} else {
		$password = preg_replace( '/[\x00-\x1F\x7F]/', '', $password );
	}

	$encryption = isset( $input['smtp_encryption'] ) ? sanitize_key( $input['smtp_encryption'] ) : 'tls';
	if ( ! in_array( $encryption, [ 'none', 'tls', 'ssl' ], true ) ) {
		$encryption = 'tls';
	}

	return [
		'smtp_enabled'    => empty( $input['smtp_enabled'] ) ? 0 : 1,
		'smtp_host'       => sanitize_text_field( $input['smtp_host'] ?? '' ),
		'smtp_port'       => $port >= 1 && $port <= 65535 ? $port : 587,
		'smtp_encryption' => $encryption,
		'smtp_auth'       => empty( $input['smtp_auth'] ) ? 0 : 1,
		'smtp_username'   => sanitize_text_field( $input['smtp_username'] ?? '' ),
		'smtp_password'   => $password,
		'from_email'      => sanitize_email( $input['from_email'] ?? '' ),
		'from_name'       => sanitize_text_field( $input['from_name'] ?? '' ),
		'cron_enabled'    => empty( $input['cron_enabled'] ) ? 0 : 1,
		'cron_interval'   => in_array( $interval, $allowed, true ) ? $interval : 5,
	];
}

/**
 * Apply the configured SMTP transport to WordPress mail.
 *
 * @param PHPMailer\PHPMailer\PHPMailer $phpmailer WordPress mailer.
 */
function sk_configure_smtp( $phpmailer ) {
	$settings = sk_get_mailing_settings();
	if ( empty( $settings['smtp_enabled'] ) || '' === $settings['smtp_host'] ) {
		return;
	}

	$phpmailer->isSMTP();
	$phpmailer->Host       = $settings['smtp_host'];
	$phpmailer->Port       = (int) $settings['smtp_port'];
	$phpmailer->SMTPAuth   = ! empty( $settings['smtp_auth'] );
	$phpmailer->Username   = $settings['smtp_username'];
	$phpmailer->Password   = $settings['smtp_password'];
	$phpmailer->SMTPSecure = 'none' === $settings['smtp_encryption'] ? '' : $settings['smtp_encryption'];
	$phpmailer->SMTPAutoTLS = 'none' !== $settings['smtp_encryption'];

	if ( is_email( $settings['from_email'] ) ) {
		$phpmailer->setFrom( $settings['from_email'], $settings['from_name'], false );
	}
}
add_action( 'phpmailer_init', 'sk_configure_smtp' );

/**
 * Add selectable mailing worker intervals to WP-Cron.
 *
 * @param array<string, array<string, mixed>> $schedules Existing schedules.
 * @return array<string, array<string, mixed>>
 */
function sk_add_mailing_cron_schedules( $schedules ) {
	foreach ( [ 1, 5, 10, 15, 30, 60 ] as $minutes ) {
		$schedules[ 'sk_mailing_every_' . $minutes . '_minutes' ] = [
			'interval' => $minutes * MINUTE_IN_SECONDS,
			'display'  => sprintf(
				/* translators: %d: number of minutes */
				_n( 'Every %d minute', 'Every %d minutes', $minutes, 'skyrora-mailing' ),
				$minutes
			),
		];
	}

	return $schedules;
}
add_filter( 'cron_schedules', 'sk_add_mailing_cron_schedules' );

/**
 * Ensure the recurring worker matches the saved settings.
 */
function sk_sync_mailing_cron() {
	$settings = sk_get_mailing_settings();
	$event    = wp_get_scheduled_event( SK_MAILING_CRON_HOOK );

	if ( empty( $settings['cron_enabled'] ) ) {
		if ( $event ) {
			wp_clear_scheduled_hook( SK_MAILING_CRON_HOOK );
		}
		return;
	}

	$schedule = 'sk_mailing_every_' . (int) $settings['cron_interval'] . '_minutes';
	if ( $event && $event->schedule === $schedule ) {
		return;
	}

	if ( $event ) {
		wp_clear_scheduled_hook( SK_MAILING_CRON_HOOK );
	}

	wp_schedule_event( time() + MINUTE_IN_SECONDS, $schedule, SK_MAILING_CRON_HOOK );
}
add_action( 'init', 'sk_sync_mailing_cron' );
add_action( 'update_option_' . SK_MAILING_SETTINGS_OPTION, 'sk_sync_mailing_cron', 10, 0 );

/**
 * Public worker hook used by the mailing queue.
 */
function sk_run_mailing_cron_worker() {
	do_action( 'sk_mailing_process_queue' );
}
add_action( SK_MAILING_CRON_HOOK, 'sk_run_mailing_cron_worker' );

/**
 * Remove the scheduled worker when the plugin is disabled.
 */
function sk_deactivate_mailing_cron() {
	wp_clear_scheduled_hook( SK_MAILING_CRON_HOOK );
}
register_deactivation_hook( SK_PLUGIN, 'sk_deactivate_mailing_cron' );

/**
 * Render the Settings admin screen.
 */
function sk_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'skyrora-mailing' ) );
	}

	$settings  = sk_get_mailing_settings();
	$cron_page = isset( $_GET['cron_page'] ) ? max( 1, absint( $_GET['cron_page'] ) ) : 1;
	$cron_jobs = new WP_Query(
		[
			'post_type'      => 'subscription',
			'post_status'    => 'publish',
			'posts_per_page' => 10,
			'paged'          => $cron_page,
			'meta_key'       => '_sk_scheduled_at',
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
		]
	);
	?>
	<div class="wrap sk-settings-page">
		<h1><?php esc_html_e( 'Settings', 'skyrora-mailing' ); ?></h1>
		<?php settings_errors(); ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'sk_mailing_settings_group' ); ?>

			<section class="sk-settings-card">
				<h2><?php esc_html_e( 'SMTP settings', 'skyrora-mailing' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Send all WordPress emails through your SMTP server.', 'skyrora-mailing' ); ?></p>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Enable SMTP', 'skyrora-mailing' ); ?></th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( SK_MAILING_SETTINGS_OPTION ); ?>[smtp_enabled]" value="1" <?php checked( $settings['smtp_enabled'] ); ?>> <?php esc_html_e( 'Use these SMTP settings', 'skyrora-mailing' ); ?></label></td>
					</tr>
					<tr>
						<th scope="row"><label for="sk_smtp_host"><?php esc_html_e( 'SMTP host', 'skyrora-mailing' ); ?></label></th>
						<td><input class="regular-text" type="text" id="sk_smtp_host" name="<?php echo esc_attr( SK_MAILING_SETTINGS_OPTION ); ?>[smtp_host]" value="<?php echo esc_attr( $settings['smtp_host'] ); ?>" placeholder="smtp.example.com"></td>
					</tr>
					<tr>
						<th scope="row"><label for="sk_smtp_port"><?php esc_html_e( 'Port', 'skyrora-mailing' ); ?></label></th>
						<td><input class="small-text" type="number" min="1" max="65535" id="sk_smtp_port" name="<?php echo esc_attr( SK_MAILING_SETTINGS_OPTION ); ?>[smtp_port]" value="<?php echo esc_attr( (string) $settings['smtp_port'] ); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><label for="sk_smtp_encryption"><?php esc_html_e( 'Encryption', 'skyrora-mailing' ); ?></label></th>
						<td>
							<select id="sk_smtp_encryption" name="<?php echo esc_attr( SK_MAILING_SETTINGS_OPTION ); ?>[smtp_encryption]">
								<option value="none" <?php selected( $settings['smtp_encryption'], 'none' ); ?>><?php esc_html_e( 'None', 'skyrora-mailing' ); ?></option>
								<option value="tls" <?php selected( $settings['smtp_encryption'], 'tls' ); ?>>TLS</option>
								<option value="ssl" <?php selected( $settings['smtp_encryption'], 'ssl' ); ?>>SSL</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Authentication', 'skyrora-mailing' ); ?></th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( SK_MAILING_SETTINGS_OPTION ); ?>[smtp_auth]" value="1" <?php checked( $settings['smtp_auth'] ); ?>> <?php esc_html_e( 'SMTP server requires authentication', 'skyrora-mailing' ); ?></label></td>
					</tr>
					<tr>
						<th scope="row"><label for="sk_smtp_username"><?php esc_html_e( 'Username', 'skyrora-mailing' ); ?></label></th>
						<td><input class="regular-text" type="text" autocomplete="off" id="sk_smtp_username" name="<?php echo esc_attr( SK_MAILING_SETTINGS_OPTION ); ?>[smtp_username]" value="<?php echo esc_attr( $settings['smtp_username'] ); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><label for="sk_smtp_password"><?php esc_html_e( 'Password', 'skyrora-mailing' ); ?></label></th>
						<td>
							<input class="regular-text" type="password" autocomplete="new-password" id="sk_smtp_password" name="<?php echo esc_attr( SK_MAILING_SETTINGS_OPTION ); ?>[smtp_password]" value="">
							<?php if ( '' !== $settings['smtp_password'] ) : ?>
								<p class="description"><?php esc_html_e( 'A password is saved. Leave this field empty to keep it.', 'skyrora-mailing' ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sk_from_email"><?php esc_html_e( 'From email', 'skyrora-mailing' ); ?></label></th>
						<td><input class="regular-text" type="email" id="sk_from_email" name="<?php echo esc_attr( SK_MAILING_SETTINGS_OPTION ); ?>[from_email]" value="<?php echo esc_attr( $settings['from_email'] ); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><label for="sk_from_name"><?php esc_html_e( 'From name', 'skyrora-mailing' ); ?></label></th>
						<td><input class="regular-text" type="text" id="sk_from_name" name="<?php echo esc_attr( SK_MAILING_SETTINGS_OPTION ); ?>[from_name]" value="<?php echo esc_attr( $settings['from_name'] ); ?>"></td>
					</tr>
				</table>
			</section>

			<section class="sk-settings-card">
				<h2><?php esc_html_e( 'Cron configuration', 'skyrora-mailing' ); ?></h2>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Enable cron', 'skyrora-mailing' ); ?></th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( SK_MAILING_SETTINGS_OPTION ); ?>[cron_enabled]" value="1" <?php checked( $settings['cron_enabled'] ); ?>> <?php esc_html_e( 'Automatically run the mailing worker', 'skyrora-mailing' ); ?></label></td>
					</tr>
				</table>

				<h3 class="sk-settings-cron-title"><?php esc_html_e( 'Cron tasks', 'skyrora-mailing' ); ?></h3>
				<table class="widefat striped sk-settings-cron-table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Template (email)', 'skyrora-mailing' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Period', 'skyrora-mailing' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Status', 'skyrora-mailing' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Logs', 'skyrora-mailing' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( $cron_jobs->have_posts() ) : ?>
							<?php while ( $cron_jobs->have_posts() ) : ?>
								<?php
								$cron_jobs->the_post();
								$job_id       = get_the_ID();
								$template_id  = (int) wp_get_post_parent_id( $job_id );
								$template     = $template_id ? get_post( $template_id ) : null;
								$scheduled_at = (int) get_post_meta( $job_id, '_sk_scheduled_at', true );
								$status       = (string) get_post_meta( $job_id, '_sk_job_status', true );
								$status       = $status ?: 'queued';
								$sent         = (int) get_post_meta( $job_id, '_sk_sent_count', true );
								$failed       = (int) get_post_meta( $job_id, '_sk_failed_count', true );
								$completed_at = (int) get_post_meta( $job_id, '_sk_completed_at', true );
								$status_labels = [
									'queued'     => __( 'Queued', 'skyrora-mailing' ),
									'processing' => __( 'Processing', 'skyrora-mailing' ),
									'completed'  => __( 'Completed', 'skyrora-mailing' ),
									'failed'     => __( 'Failed', 'skyrora-mailing' ),
								];
								$status_label = $status_labels[ $status ] ?? ucfirst( $status );
								?>
								<tr>
									<td>
										<?php if ( $template ) : ?>
											<a href="<?php echo esc_url( get_edit_post_link( $template_id, 'raw' ) ); ?>">
												<?php echo esc_html( get_the_title( $template ) ?: __( '(no title)', 'skyrora-mailing' ) ); ?>
											</a>
											<div class="description"><?php echo esc_html( get_the_title( $job_id ) ); ?></div>
										<?php else : ?>
											<?php esc_html_e( 'Deleted template', 'skyrora-mailing' ); ?>
										<?php endif; ?>
									</td>
									<td>
										<?php
										echo $scheduled_at
											? esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $scheduled_at ) )
											: '&mdash;';
										?>
									</td>
									<td>
										<span class="sk-settings-status is-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $status_label ); ?></span>
									</td>
									<td>
										<?php if ( in_array( $status, [ 'completed', 'failed' ], true ) ) : ?>
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
											<?php if ( $completed_at ) : ?>
												<div class="description">
													<?php
													echo esc_html(
														sprintf(
															/* translators: %s: completion date and time */
															__( 'Completed: %s', 'skyrora-mailing' ),
															wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $completed_at )
														)
													);
													?>
												</div>
											<?php endif; ?>
										<?php elseif ( 'processing' === $status ) : ?>
											<?php esc_html_e( 'Sending is in progress.', 'skyrora-mailing' ); ?>
										<?php else : ?>
											<?php esc_html_e( 'No logs yet.', 'skyrora-mailing' ); ?>
										<?php endif; ?>
									</td>
								</tr>
							<?php endwhile; ?>
						<?php else : ?>
							<tr>
								<td colspan="4"><?php esc_html_e( 'No cron tasks found.', 'skyrora-mailing' ); ?></td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>

				<?php if ( $cron_jobs->max_num_pages > 1 ) : ?>
					<div class="tablenav">
						<div class="tablenav-pages">
							<?php
							echo wp_kses_post(
								paginate_links(
									[
										'base'      => str_replace(
											'999999999',
											'%#%',
											add_query_arg( 'cron_page', '999999999', admin_url( 'admin.php?page=skyrora-mailing-settings' ) )
										),
										'format'    => '',
										'current'   => $cron_page,
										'total'     => (int) $cron_jobs->max_num_pages,
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
			</section>

			<?php submit_button( __( 'Save settings', 'skyrora-mailing' ) ); ?>
		</form>
	</div>
	<?php
}
