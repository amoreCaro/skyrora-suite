<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTML листа з контенту mailing-посту.
 *
 * @param int|WP_Post $post Post ID або об'єкт.
 * @return string|WP_Error
 */
function sk_get_mailing_email_html( $post ) {
	$post = get_post( $post );
	if ( ! $post || 'mailing' !== $post->post_type ) {
		return new WP_Error( 'invalid_post', __( 'Invalid mailing template.', 'skyrora-mailing' ) );
	}

	$raw = (string) $post->post_content;
	if ( '' === trim( $raw ) ) {
		return new WP_Error( 'empty_content', __( 'Template content is empty.', 'skyrora-mailing' ) );
	}

	$html  = '<html lang="en"><head>';
	$html .= '<meta charset="UTF-8">';
	$html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
	$html .= '<style>*{margin:0;padding:0;box-sizing:border-box;}</style>';
	$html .= '</head><body>';
	$html .= '<div id="wrapper" style="max-width:100%;margin:0 auto;background-color:rgb(24,27,36);padding:0 20px;">';
	$html .= '<div class="container" style="max-width:640px;width:100%;background-color:#fff;margin:0 auto;min-height:50vh;box-sizing:border-box;padding:0;">';
	$html .= $raw;
	$html .= '</div></div></body></html>';

	return $html;
}

/**
 * URL сторінки Send для шаблону.
 *
 * @param int $post_id Mailing post ID.
 * @return string
 */
function sk_get_send_page_url( $post_id ) {
	return add_query_arg(
		[
			'page'    => 'skyrora-mailing-send',
			'post_id' => (int) $post_id,
		],
		admin_url( 'admin.php' )
	);
}

/**
 * Верхній stepper: Template → Send.
 *
 * @param string $current 'template'|'design'|'send'.
 * @param int    $post_id Mailing post ID.
 */
function sk_render_mailing_steps( $current, $post_id ) {
	$post_id       = (int) $post_id;
	$templates_url = admin_url( 'edit.php?post_type=mailing' );
	$send_url      = $post_id ? sk_get_send_page_url( $post_id ) : '';

	// Editor ('design') is still the Template step in the nav.
	$order     = [ 'template' => 1, 'design' => 1, 'send' => 2 ];
	$current_n = isset( $order[ $current ] ) ? $order[ $current ] : 1;

	$steps = [
		[
			'key'    => 'template',
			'label'  => __( 'Template', 'skyrora-mailing' ),
			'number' => 1,
			'done'   => $current_n > 1,
			'active' => ( 'template' === $current || 'design' === $current ),
			'url'    => $templates_url,
		],
		[
			'key'    => 'send',
			'label'  => __( 'Send', 'skyrora-mailing' ),
			'number' => 2,
			'done'   => false,
			'active' => ( 'send' === $current ),
			'url'    => $send_url,
		],
	];
	?>
	<nav class="sk-mailing-steps" aria-label="<?php esc_attr_e( 'Mailing steps', 'skyrora-mailing' ); ?>">
		<ol class="sk-mailing-steps__list">
			<?php foreach ( $steps as $index => $step ) : ?>
				<?php if ( $index > 0 ) : ?>
					<li class="sk-mailing-steps__sep" aria-hidden="true"></li>
				<?php endif; ?>
				<?php
				// Назад можна йти на пройдені кроки (і завжди на список Templates).
				$can_link = $step['url'] && ! $step['active'] && ( 'template' === $step['key'] || $step['done'] );
				?>
				<li class="sk-mailing-steps__item<?php echo $step['active'] ? ' is-active' : ''; ?><?php echo $step['done'] ? ' is-done' : ''; ?>">
					<?php if ( $can_link ) : ?>
						<a class="sk-mailing-steps__link" href="<?php echo esc_url( $step['url'] ); ?>">
					<?php else : ?>
						<span class="sk-mailing-steps__link">
					<?php endif; ?>

						<?php if ( $step['done'] ) : ?>
							<span class="sk-mailing-steps__badge sk-mailing-steps__badge--done" aria-hidden="true">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 12.5l5 5L19 7" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
							</span>
						<?php else : ?>
							<span class="sk-mailing-steps__badge"><?php echo esc_html( (string) $step['number'] ); ?></span>
						<?php endif; ?>
						<span class="sk-mailing-steps__label"><?php echo esc_html( $step['label'] ); ?></span>

					<?php if ( $can_link ) : ?>
						</a>
					<?php else : ?>
						</span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
	</nav>
	<?php
}

/**
 * Admin-сторінка Send (прихована в меню CSS, доступ через Next).
 */
function sk_register_send_page() {
	add_submenu_page(
		'skyrora-mailing',
		__( 'Send', 'skyrora-mailing' ),
		__( 'Send', 'skyrora-mailing' ),
		'manage_options',
		'skyrora-mailing-send',
		'sk_render_send_page'
	);
}
add_action( 'admin_menu', 'sk_register_send_page', 20 );

/**
 * Не показувати Send у підменю (remove_submenu_page ламає доступ до сторінки).
 */
function sk_hide_send_submenu_css() {
	echo '<style>#toplevel_page_skyrora-mailing .wp-submenu a[href*="page=skyrora-mailing-send"]{display:none!important;}</style>';
}
add_action( 'admin_head', 'sk_hide_send_submenu_css' );

/**
 * Рендер сторінки Send.
 */
function sk_render_send_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'skyrora-mailing' ) );
	}

	$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
	$post    = $post_id ? get_post( $post_id ) : null;

	if ( ! $post || 'mailing' !== $post->post_type ) {
		echo '<div class="wrap"><h1>' . esc_html__( 'Send', 'skyrora-mailing' ) . '</h1>';
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Valid mailing template is required. Open a template and click Next.', 'skyrora-mailing' ) . '</p></div></div>';
		return;
	}

	$edit_url = get_edit_post_link( $post_id, 'raw' );
	$lists    = get_terms(
		[
			'taxonomy'   => 'list',
			'hide_empty' => false,
		]
	);
	if ( is_wp_error( $lists ) ) {
		$lists = [];
	}

	?>
	<div class="sk-send-shell">
		<header class="sk-mailing-topbar">
			<?php sk_render_admin_brand(); ?>
			<?php sk_render_mailing_steps( 'send', $post_id ); ?>
		</header>

		<div class="wrap sk-send-page" id="sk-send-page" data-post-id="<?php echo esc_attr( (string) $post_id ); ?>" data-mode="test">
			<div class="sk-send-page__header">
				<h1 class="sk-send-page__title">
					<?php echo esc_html( get_the_title( $post ) ); ?>
				</h1>
			</div>

			<div class="sk-send-page__grid">
				<div class="sk-send-page__main">
					<section class="sk-send-card">
						<label class="sk-send-card__label" for="sk_send_subject">
							<?php esc_html_e( 'Subject', 'skyrora-mailing' ); ?>
							<span class="sk-send-required" aria-hidden="true">*</span>
						</label>
						<input
							type="text"
							class="regular-text sk-send-page__subject"
							id="sk_send_subject"
							value=""
							required
							aria-required="true"
							placeholder="<?php esc_attr_e( 'Enter email subject…', 'skyrora-mailing' ); ?>"
							aria-describedby="sk_send_subject_hint"
						>
						<p class="sk-send-hint" id="sk_send_subject_hint">
							<span class="sk-send-hint__icon" aria-hidden="true" title="<?php esc_attr_e( 'This is the subject line recipients see in their inbox.', 'skyrora-mailing' ); ?>">?</span>
							<?php esc_html_e( 'This is the subject line of the email (what recipients see in their inbox).', 'skyrora-mailing' ); ?>
						</p>
					</section>

					<section class="sk-send-card">
						<div class="sk-send-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Send mode', 'skyrora-mailing' ); ?>">
							<button type="button" class="sk-send-tabs__tab is-active" role="tab" aria-selected="true" data-mode="test" id="sk_tab_test">
								<?php esc_html_e( 'Test sending', 'skyrora-mailing' ); ?>
							</button>
							<button type="button" class="sk-send-tabs__tab" role="tab" aria-selected="false" data-mode="list" id="sk_tab_list">
								<?php esc_html_e( 'Mailing list', 'skyrora-mailing' ); ?>
							</button>
							<button type="button" class="sk-send-tabs__tab" role="tab" aria-selected="false" data-mode="single" id="sk_tab_single">
								<?php esc_html_e( 'Single email', 'skyrora-mailing' ); ?>
							</button>
						</div>

						<div class="sk-send-page__panel" data-mode-panel="test" role="tabpanel" aria-labelledby="sk_tab_test">
							<label for="sk_send_test_email"><?php esc_html_e( 'Test email', 'skyrora-mailing' ); ?></label>
							<input
								type="email"
								id="sk_send_test_email"
								class="regular-text"
								placeholder="you@example.com"
								autocomplete="email"
								value="<?php echo esc_attr( wp_get_current_user()->user_email ); ?>"
							>
							<p class="description"><?php esc_html_e( 'Sends one test copy of this template.', 'skyrora-mailing' ); ?></p>
						</div>

						<div class="sk-send-page__panel" data-mode-panel="list" role="tabpanel" aria-labelledby="sk_tab_list" hidden>
							<div class="sk-send-to">
								<h3 class="sk-send-to__title"><?php esc_html_e( 'Send to', 'skyrora-mailing' ); ?></h3>
								<p class="sk-send-to__hint">
									<?php esc_html_e( 'Subscribers in multiple lists will only receive one email.', 'skyrora-mailing' ); ?>
								</p>

								<div
									class="sk-multiselect"
									id="sk_send_lists"
									data-placeholder="<?php esc_attr_e( 'Search lists…', 'skyrora-mailing' ); ?>"
								>
									<div class="sk-multiselect__control" tabindex="0">
										<div class="sk-multiselect__chips" id="sk_send_lists_chips"></div>
										<input
											type="text"
											class="sk-multiselect__search"
											id="sk_send_lists_search"
											autocomplete="off"
											aria-autocomplete="list"
											aria-controls="sk_send_lists_dropdown"
											aria-expanded="false"
											placeholder="<?php esc_attr_e( 'Search lists…', 'skyrora-mailing' ); ?>"
										>
										<span class="sk-multiselect__chevron" aria-hidden="true"></span>
									</div>
									<ul
										class="sk-multiselect__dropdown"
										id="sk_send_lists_dropdown"
										role="listbox"
										aria-multiselectable="true"
										hidden
									>
										<?php foreach ( $lists as $term ) : ?>
											<?php $count = count( sk_get_list_subscriber_ids( $term->term_id ) ); ?>
											<li
												class="sk-multiselect__option"
												role="option"
												aria-selected="false"
												data-id="<?php echo esc_attr( (string) $term->term_id ); ?>"
												data-name="<?php echo esc_attr( $term->name ); ?>"
												data-count="<?php echo esc_attr( (string) $count ); ?>"
												tabindex="-1"
											>
												<span class="sk-multiselect__option-label"><?php echo esc_html( $term->name ); ?></span>
												<span class="sk-multiselect__count"><?php echo esc_html( (string) $count ); ?></span>
											</li>
										<?php endforeach; ?>
									</ul>
									<div class="sk-multiselect__values" id="sk_send_lists_values" hidden></div>
								</div>
							</div>
						</div>

						<div class="sk-send-page__panel" data-mode-panel="single" role="tabpanel" aria-labelledby="sk_tab_single" hidden>
							<label for="sk_send_email"><?php esc_html_e( 'Email', 'skyrora-mailing' ); ?></label>
							<input
								type="email"
								id="sk_send_email"
								class="regular-text"
								placeholder="name@example.com"
								autocomplete="email"
							>
						</div>
					</section>

					<?php
					$site_time   = wp_date( get_option( 'time_format' ) ?: 'g:i a', current_time( 'timestamp' ) );
					$default_day = wp_date( 'Y-m-d', strtotime( '+1 day', current_time( 'timestamp' ) ) );
					?>
					<section class="sk-send-card sk-send-schedule" id="sk_send_schedule" hidden>
						<h3 class="sk-send-schedule__title"><?php esc_html_e( 'Schedule it', 'skyrora-mailing' ); ?></h3>
						<div class="sk-send-schedule__toggle-row">
							<label class="sk-toggle" for="sk_schedule_enabled">
								<input type="checkbox" id="sk_schedule_enabled" class="sk-toggle__input" value="1">
								<span class="sk-toggle__track" aria-hidden="true">
									<span class="sk-toggle__thumb"></span>
								</span>
								<span class="sk-toggle__label"><?php esc_html_e( 'Enable scheduling', 'skyrora-mailing' ); ?></span>
							</label>
							<span class="sk-send-schedule__site-time">
								<?php
								echo esc_html(
									sprintf(
										/* translators: %s: current site time */
										__( "Your website's time is %s", 'skyrora-mailing' ),
										$site_time
									)
								);
								?>
							</span>
						</div>
						<div class="sk-send-schedule__fields" id="sk_schedule_fields" hidden>
							<input
								type="date"
								id="sk_schedule_date"
								class="sk-send-schedule__date"
								value="<?php echo esc_attr( $default_day ); ?>"
								min="<?php echo esc_attr( wp_date( 'Y-m-d', current_time( 'timestamp' ) ) ); ?>"
							>
							<select id="sk_schedule_time" class="sk-send-schedule__time">
								<?php
								for ( $h = 0; $h < 24; $h++ ) {
									foreach ( [ 0, 30 ] as $m ) {
										$val   = sprintf( '%02d:%02d', $h, $m );
										$label = wp_date( get_option( 'time_format' ) ?: 'g:i a', strtotime( '1970-01-01 ' . $val . ':00' ) );
										printf(
											'<option value="%s"%s>%s</option>',
											esc_attr( $val ),
											selected( $val, '08:00', false ),
											esc_html( $label )
										);
									}
								}
								?>
							</select>
						</div>
					</section>
				</div>

				<aside class="sk-send-page__aside">
					<section class="sk-send-card sk-send-card--actions">
						<button type="button" class="button button-primary button-hero" id="sk_send_submit">
							<?php esc_html_e( 'Send test', 'skyrora-mailing' ); ?>
						</button>
						<a class="sk-send-page__draft-link" href="<?php echo esc_url( $edit_url ); ?>">
							<?php esc_html_e( 'Go back to Template', 'skyrora-mailing' ); ?>
						</a>
						<div class="sk-send-page__response" id="sk_send_response" aria-live="polite"></div>
					</section>
				</aside>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Store a mailing job and register an exact WP-Cron event.
 *
 * @param int      $post_id  Template ID.
 * @param string   $subject  Email subject.
 * @param string   $html     Email body.
 * @param string[] $emails   Recipients.
 * @param int      $timestamp UTC Unix timestamp.
 * @return int|WP_Error
 */
function sk_schedule_mailing_job( $post_id, $subject, $html, $emails, $timestamp ) {
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

	update_post_meta( $job_id, '_sk_recipients', array_values( array_unique( $emails ) ) );
	update_post_meta( $job_id, '_sk_scheduled_at', $timestamp );
	update_post_meta( $job_id, '_sk_job_status', 'scheduled' );
	update_post_meta( $job_id, '_sk_campaign_status', 'scheduled' );

	$scheduled = wp_schedule_single_event( $timestamp, 'sk_send_scheduled_mailing', [ $job_id ] );
	if ( is_wp_error( $scheduled ) || ! $scheduled ) {
		wp_delete_post( $job_id, true );
		return new WP_Error( 'cron_schedule_failed', __( 'Could not register the scheduled mailing.', 'skyrora-mailing' ) );
	}

	sk_set_campaign_status( $job_id, 'scheduled' );
	return $job_id;
}

/**
 * Send one queued mailing job.
 *
 * @param int $job_id Job post ID.
 */
function sk_process_scheduled_mailing( $job_id ) {
	$job = get_post( $job_id );
	$stored_status = (string) get_post_meta( $job_id, '_sk_job_status', true );
	$status        = sk_normalize_campaign_status( $stored_status );

	if ( ! $job || 'subscription' !== $job->post_type || 'scheduled' !== $status ) {
		return;
	}

	if ( ! update_post_meta( $job_id, '_sk_job_status', 'sending', $stored_status ) ) {
		return;
	}
	sk_set_campaign_status( $job_id, 'sending' );

	$emails  = get_post_meta( $job_id, '_sk_recipients', true );
	$emails  = is_array( $emails ) ? array_filter( array_map( 'sanitize_email', $emails ), 'is_email' ) : [];
	$headers = [ 'Content-Type: text/html; charset=UTF-8' ];
	$sent    = 0;
	$failed  = 0;
	$errors  = [];

	foreach ( $emails as $email ) {
		if ( 'paused' === sk_get_campaign_status( $job_id ) ) {
			update_post_meta( $job_id, '_sk_sent_count', $sent );
			update_post_meta( $job_id, '_sk_failed_count', $failed );
			update_post_meta( $job_id, '_sk_send_errors', $errors );
			return;
		}

		try {
			$result = sk_send_mail_with_error( $email, $job->post_title, $job->post_content, $headers );
		} catch ( Throwable $error ) {
			$result = [
				'sent'  => false,
				'error' => $error->getMessage(),
			];
		}

		if ( $result['sent'] ) {
			$sent++;
		} else {
			$failed++;
			$errors[] = [
				'email' => $email,
				'error' => $result['error'],
			];
		}
	}

	update_post_meta( $job_id, '_sk_sent_count', $sent );
	update_post_meta( $job_id, '_sk_failed_count', $failed );
	update_post_meta( $job_id, '_sk_send_errors', $errors );
	if ( 'paused' === sk_get_campaign_status( $job_id ) ) {
		return;
	}
	update_post_meta( $job_id, '_sk_completed_at', time() );
	sk_set_campaign_status( $job_id, ( $failed > 0 || ! $emails ) ? 'failed' : 'sent' );
}
add_action( 'sk_send_scheduled_mailing', 'sk_process_scheduled_mailing' );

/**
 * Process due jobs as a recurring fallback for missed single events.
 */
function sk_process_due_mailing_jobs() {
	$job_ids = get_posts(
		[
			'post_type'      => 'subscription',
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'posts_per_page' => 5,
			'meta_key'       => '_sk_scheduled_at',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
			'meta_query'     => [
				[
					'key'     => '_sk_job_status',
					'value'   => [ 'scheduled', 'queued' ],
					'compare' => 'IN',
				],
				[
					'key'     => '_sk_scheduled_at',
					'value'   => time(),
					'type'    => 'NUMERIC',
					'compare' => '<=',
				],
			],
		]
	);

	foreach ( $job_ids as $job_id ) {
		sk_process_scheduled_mailing( $job_id );
	}
}
add_action( 'sk_mailing_process_queue', 'sk_process_due_mailing_jobs' );

/**
 * AJAX: відправка шаблону (list / single / test).
 */
function sk_ajax_send_mailing() {
	check_ajax_referer( 'sk_send_mailing', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( [ 'message' => __( 'Permission denied.', 'skyrora-mailing' ) ], 403 );
	}

	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
	$mode    = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : '';

	if ( ! $post_id || 'mailing' !== get_post_type( $post_id ) ) {
		wp_send_json_error( [ 'message' => __( 'Invalid template.', 'skyrora-mailing' ) ] );
	}
	if ( '' === $subject ) {
		wp_send_json_error( [ 'message' => __( 'Please enter a subject.', 'skyrora-mailing' ) ] );
	}
	if ( ! in_array( $mode, [ 'list', 'single', 'test' ], true ) ) {
		wp_send_json_error( [ 'message' => __( 'Invalid send mode.', 'skyrora-mailing' ) ] );
	}

	$html = sk_get_mailing_email_html( $post_id );
	if ( is_wp_error( $html ) ) {
		wp_send_json_error( [ 'message' => $html->get_error_message() ] );
	}

	$headers = [ 'Content-Type: text/html; charset=UTF-8' ];
	$emails  = [];

	if ( 'list' === $mode ) {
		$list_ids = [];
		if ( isset( $_POST['list_ids'] ) && is_array( $_POST['list_ids'] ) ) {
			$list_ids = array_filter( array_map( 'absint', wp_unslash( $_POST['list_ids'] ) ) );
		} elseif ( isset( $_POST['list_id'] ) ) {
			$list_ids = [ absint( $_POST['list_id'] ) ];
		}

		$list_ids = array_values( array_unique( array_filter( $list_ids ) ) );
		if ( ! $list_ids ) {
			wp_send_json_error( [ 'message' => __( 'Please select at least one list.', 'skyrora-mailing' ) ] );
		}

		foreach ( $list_ids as $list_id ) {
			if ( ! term_exists( $list_id, 'list' ) ) {
				wp_send_json_error( [ 'message' => __( 'Please select a valid list.', 'skyrora-mailing' ) ] );
			}
			foreach ( sk_get_list_subscriber_ids( $list_id ) as $subscriber_id ) {
				if ( function_exists( 'sk_get_subscriber_status' ) && 'subscribed' !== sk_get_subscriber_status( $subscriber_id ) ) {
					continue;
				}
				$email = sk_get_subscriber_email( $subscriber_id );
				if ( $email && is_email( $email ) ) {
					$emails[] = $email;
				}
			}
		}

		$emails = array_values( array_unique( $emails ) );
		if ( ! $emails ) {
			wp_send_json_error( [ 'message' => __( 'Selected lists have no subscribers with valid emails.', 'skyrora-mailing' ) ] );
		}
	} else {
		$field = ( 'test' === $mode ) ? 'test_email' : 'email';
		$email = isset( $_POST[ $field ] ) ? sanitize_email( wp_unslash( $_POST[ $field ] ) ) : '';
		if ( ! is_email( $email ) ) {
			wp_send_json_error( [ 'message' => __( 'Please enter a valid email address.', 'skyrora-mailing' ) ] );
		}
		$emails[] = $email;
	}

	if ( 'test' !== $mode && ! empty( $_POST['schedule'] ) ) {
		$settings = function_exists( 'sk_get_mailing_settings' ) ? sk_get_mailing_settings() : [];
		if ( empty( $settings['cron_enabled'] ) ) {
			wp_send_json_error( [ 'message' => __( 'Enable cron in Settings before scheduling a mailing.', 'skyrora-mailing' ) ] );
		}

		$date     = isset( $_POST['schedule_date'] ) ? sanitize_text_field( wp_unslash( $_POST['schedule_date'] ) ) : '';
		$time     = isset( $_POST['schedule_time'] ) ? sanitize_text_field( wp_unslash( $_POST['schedule_time'] ) ) : '';
		$datetime = DateTimeImmutable::createFromFormat( '!Y-m-d H:i', $date . ' ' . $time, wp_timezone() );

		if ( ! $datetime || $datetime->format( 'Y-m-d H:i' ) !== $date . ' ' . $time || $datetime->getTimestamp() <= time() ) {
			wp_send_json_error( [ 'message' => __( 'Please select a valid future date and time.', 'skyrora-mailing' ) ] );
		}

		$job_id = sk_schedule_mailing_job( $post_id, $subject, $html, $emails, $datetime->getTimestamp() );
		if ( is_wp_error( $job_id ) ) {
			wp_send_json_error( [ 'message' => $job_id->get_error_message() ] );
		}

		wp_send_json_success(
			[
				'message' => sprintf(
					/* translators: %s: scheduled site date and time */
					__( 'Mailing scheduled for %s.', 'skyrora-mailing' ),
					wp_date( 'Y-m-d H:i', $datetime->getTimestamp() )
				),
				'job_id'  => $job_id,
			]
		);
	}

	$sent       = 0;
	$failed     = 0;
	$last_error = '';
	$paused     = false;

	if ( 'test' !== $mode ) {
		sk_set_campaign_status( $post_id, 'not_sent_yet' );
		sk_set_campaign_status( $post_id, 'sending' );
	}

	foreach ( $emails as $email ) {
		if ( 'test' !== $mode && 'paused' === sk_get_campaign_status( $post_id ) ) {
			$paused = true;
			break;
		}

		try {
			$result = sk_send_mail_with_error( $email, $subject, $html, $headers );
		} catch ( Throwable $error ) {
			$result = [
				'sent'  => false,
				'error' => $error->getMessage(),
			];
		}

		if ( $result['sent'] ) {
			$sent++;
		} else {
			$failed++;
			$last_error = $result['error'];
		}
	}

	if ( $paused ) {
		wp_send_json_success(
			[
				'message' => sprintf(
					/* translators: %d: number of emails sent before pausing */
					__( 'Sending paused. Sent before pause: %d.', 'skyrora-mailing' ),
					$sent
				),
				'sent'    => $sent,
				'failed'  => $failed,
			]
		);
	}

	if ( $sent < 1 ) {
		if ( 'test' !== $mode ) {
			sk_set_campaign_status( $post_id, 'failed' );
		}
		wp_send_json_error(
			[
				'message' => $last_error ?: __( 'Failed to send email. Check mail settings.', 'skyrora-mailing' ),
			]
		);
	}

	if ( 'test' !== $mode ) {
		sk_set_campaign_status( $post_id, $failed > 0 ? 'failed' : 'sent' );
	}

	wp_send_json_success(
		[
			'message' => sprintf(
				/* translators: 1: sent count, 2: failed count */
				__( 'Sent: %1$d. Failed: %2$d.', 'skyrora-mailing' ),
				$sent,
				$failed
			),
			'sent'    => $sent,
			'failed'  => $failed,
		]
	);
}
add_action( 'wp_ajax_sk_send_mailing', 'sk_ajax_send_mailing' );
