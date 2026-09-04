<?php
/**
 * DEJOIY Contact Experience — personalised /contact-us with Resend-powered form.
 *
 * Additive layer. Replaces Elementor contact blocks on the contact page only.
 * Disable: define( 'DEJOIY_CONTACT_XP_DISABLED', true );
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'DEJOIY_CONTACT_XP_VERSION' ) ) {
	define( 'DEJOIY_CONTACT_XP_VERSION', '1.0.4' );
}

if ( ! defined( 'DEJOIY_CONTACT_XP_FROM_EMAIL' ) ) {
	define( 'DEJOIY_CONTACT_XP_FROM_EMAIL', 'no-reply.notifications@dejoiy.tech' );
}

if ( ! defined( 'DEJOIY_CONTACT_XP_ADMIN_EMAIL' ) ) {
	define( 'DEJOIY_CONTACT_XP_ADMIN_EMAIL', 'forms@dejoiy.tech' );
}

if ( ! defined( 'DEJOIY_CONTACT_XP_FORMS_WEBHOOK' ) ) {
	define( 'DEJOIY_CONTACT_XP_FORMS_WEBHOOK', 'https://forms.dejoiy.tech/api/submissions' );
}

/**
 * @return bool
 */
function dejoiy_contact_xp_enabled() {
	if ( defined( 'DEJOIY_CONTACT_XP_DISABLED' ) && DEJOIY_CONTACT_XP_DISABLED ) {
		return false;
	}
	if ( ! function_exists( 'dejoiy_evolution_is_enabled' ) || ! dejoiy_evolution_is_enabled() ) {
		return false;
	}
	return (bool) apply_filters( 'dejoiy_contact_xp_enabled', true );
}

/**
 * @return bool
 */
function dejoiy_contact_xp_is_contact_page() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return false;
	}
	if ( is_page( 'contact-us' ) ) {
		return true;
	}
	$uri = strtolower( (string) wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
	return '' !== $uri && (bool) preg_match( '~/contact-us(/|\?|#|$)~', $uri );
}

/**
 * @return bool
 */
function dejoiy_contact_xp_is_active() {
	return dejoiy_contact_xp_enabled() && dejoiy_contact_xp_is_contact_page();
}

/**
 * Resend API key — wp-config constant or encrypted option (never in theme files).
 *
 * @return string
 */
function dejoiy_contact_xp_get_resend_api_key() {
	if ( defined( 'DEJOIY_RESEND_API_KEY' ) && DEJOIY_RESEND_API_KEY ) {
		return (string) DEJOIY_RESEND_API_KEY;
	}
	$key = get_option( 'dejoiy_resend_api_key', '' );
	return is_string( $key ) ? trim( $key ) : '';
}

/**
 * @return array<string, string>
 */
function dejoiy_contact_xp_subjects() {
	return array(
		'technical-help'    => __( 'Technical Help', 'dejoiy' ),
		'pre-sale'          => __( 'Pre-Sale Questions', 'dejoiy' ),
		'partnership'       => __( 'Partnership', 'dejoiy' ),
		'order-support'     => __( 'Order Support', 'dejoiy' ),
		'legal'             => __( 'Legal & Compliance', 'dejoiy' ),
	);
}

/**
 * DEJOIY favicon for form email field.
 *
 * @return string
 */
function dejoiy_contact_xp_brand_favicon_url() {
	$icon = get_site_icon_url( 96 );
	if ( $icon ) {
		return set_url_scheme( $icon, 'https' );
	}
	return 'https://dejoiy.tech/wp-content/uploads/2026/05/DEJOIY-FAVICON-100x100.png';
}

/**
 * Title-case customer name for emails (e.g. deepak → Deepak).
 *
 * @param string $name Raw name.
 * @return string
 */
function dejoiy_contact_xp_format_display_name( $name ) {
	$name = trim( (string) $name );
	if ( '' === $name ) {
		return $name;
	}
	if ( function_exists( 'mb_strtolower' ) && function_exists( 'mb_convert_case' ) ) {
		return mb_convert_case( mb_strtolower( $name, 'UTF-8' ), MB_CASE_TITLE, 'UTF-8' );
	}
	return ucwords( strtolower( $name ) );
}

/**
 * Inline favicon attachment for Resend (CID: dejoiy-favicon).
 *
 * @return array<string, string>|null
 */
function dejoiy_contact_xp_favicon_inline_attachment() {
	$url      = dejoiy_contact_xp_brand_favicon_url();
	$response = wp_remote_get(
		$url,
		array(
			'timeout' => 15,
		)
	);
	if ( is_wp_error( $response ) ) {
		return null;
	}
	$body = wp_remote_retrieve_body( $response );
	if ( '' === $body ) {
		return null;
	}
	$type = (string) wp_remote_retrieve_header( $response, 'content-type' );
	$ext  = 'png';
	if ( false !== strpos( $type, 'jpeg' ) || false !== strpos( $type, 'jpg' ) ) {
		$ext = 'jpg';
	} elseif ( false !== strpos( $type, 'webp' ) ) {
		$ext = 'webp';
	}
	return array(
		'filename'   => 'dejoiy-favicon.' . $ext,
		'content'    => base64_encode( $body ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		'content_id' => 'dejoiy-favicon',
	);
}

/**
 * DEJOIY official logo for emails and hero.
 *
 * @return string
 */
function dejoiy_contact_xp_brand_logo_url() {
	$logo_id = (int) get_theme_mod( 'custom_logo' );
	if ( $logo_id > 0 ) {
		$url = wp_get_attachment_image_url( $logo_id, 'medium' );
		if ( $url ) {
			return $url;
		}
	}
	return home_url( '/wp-content/uploads/2026/05/DEJOIY-OFFICIAL-LOGO.png' );
}

/**
 * @param string               $to      Recipient.
 * @param string               $subject Subject.
 * @param string               $html    HTML body.
 * @param string               $reply_to Reply-to email.
 * @param array<int, array<string, string>>|null $attachments Resend attachments.
 * @return true|WP_Error
 */
function dejoiy_contact_xp_resend_send( $to, $subject, $html, $reply_to = '', $attachments = null ) {
	$key = dejoiy_contact_xp_get_resend_api_key();
	if ( '' === $key ) {
		return new WP_Error( 'dejoiy_resend_missing', __( 'Email service is not configured.', 'dejoiy' ) );
	}

	$payload = array(
		'from'    => 'DEJOIY <' . DEJOIY_CONTACT_XP_FROM_EMAIL . '>',
		'to'      => array( $to ),
		'subject' => $subject,
		'html'    => $html,
		'headers' => array(
			'X-Entity-Ref-ID' => 'dejoiy-contact-' . gmdate( 'Ymd' ),
		),
	);
	if ( '' !== $reply_to && is_email( $reply_to ) ) {
		$payload['reply_to'] = $reply_to;
	}
	if ( is_array( $attachments ) && ! empty( $attachments ) ) {
		$payload['attachments'] = $attachments;
	}

	$response = wp_remote_post(
		'https://api.resend.com/emails',
		array(
			'timeout' => 25,
			'headers' => array(
				'Authorization' => 'Bearer ' . $key,
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode( $payload ),
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );
	if ( $code < 200 || $code >= 300 ) {
		$msg = is_array( $body ) && ! empty( $body['message'] ) ? (string) $body['message'] : __( 'Unable to send email.', 'dejoiy' );
		return new WP_Error( 'dejoiy_resend_failed', $msg, array( 'status' => $code ) );
	}

	return true;
}

/**
 * Mirror submission to forms.dejoiy.tech (non-blocking; does not fail the form).
 *
 * @param array<string, string> $data Submission data.
 */
function dejoiy_contact_xp_forward_forms_hub( $data ) {
	$url = (string) apply_filters( 'dejoiy_contact_xp_forms_webhook', DEJOIY_CONTACT_XP_FORMS_WEBHOOK );
	if ( '' === $url ) {
		return;
	}

	wp_remote_post(
		$url,
		array(
			'timeout'  => 8,
			'blocking' => false,
			'headers'  => array(
				'Content-Type' => 'application/json',
				'X-Form-Source' => 'dejoiy.tech/contact-us',
			),
			'body'     => wp_json_encode(
				array(
					'source'    => 'dejoiy-contact-us',
					'domain'    => 'forms.dejoiy.tech',
					'timestamp' => gmdate( 'c' ),
					'payload'   => $data,
				)
			),
		)
	);
}

/**
 * @param string $name Customer name.
 * @param string $subject_label Subject label.
 * @return string
 */
function dejoiy_contact_xp_thank_you_html( $name, $subject_label ) {
	$safe_name = esc_html( dejoiy_contact_xp_format_display_name( $name ) );
	$safe_sub  = esc_html( $subject_label );
	$home      = esc_url( home_url( '/' ) );
	$shop      = esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) );
	$logo      = esc_url( dejoiy_contact_xp_brand_logo_url() );

	ob_start();
	?>
	<div style="font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;line-height:1.6;background:#0b1220;padding:24px 12px;margin:0;">
		<div style="max-width:560px;margin:0 auto;border-radius:18px;overflow:hidden;border:1px solid #1e293b;box-shadow:0 12px 40px rgba(0,0,0,0.35);">
			<div style="background:linear-gradient(135deg,#7c3aed 0%,#06b6d4 100%);padding:36px 24px 32px;text-align:center;">
				<img src="<?php echo $logo; ?>" alt="DEJOIY" width="148" style="display:block;max-width:148px;width:148px;height:auto;margin:0 auto 18px;border:0;">
				<h1 style="margin:0;color:#0f172a;font-size:26px;font-weight:800;line-height:1.25;letter-spacing:-0.02em;">Thank you, <?php echo $safe_name; ?>!</h1>
			</div>
			<div style="background:#111827;padding:28px 24px 30px;color:#d1d5db;">
				<p style="margin:0 0 16px;font-size:15px;line-height:1.65;color:#e5e7eb;">We received your message about <strong style="color:#ffffff;"><?php echo $safe_sub; ?></strong>. Our team at DEJOIY will review it and get back to you soon.</p>
				<p style="margin:0 0 22px;font-size:15px;line-height:1.65;color:#cbd5e1;">While you wait, explore India's next-generation marketplace — curated products, trusted sellers, and buyer-first support.</p>
				<p style="margin:0 0 24px;text-align:center;">
					<a href="<?php echo $shop; ?>" style="display:inline-block;background:#a78bfa;color:#0f172a;text-decoration:none;padding:14px 28px;border-radius:999px;font-weight:800;font-size:15px;">Continue Shopping</a>
				</p>
				<p style="margin:0;font-size:13px;line-height:1.6;color:#94a3b8;">Need urgent help? Reply to this email or write to <a href="mailto:support-care@dejoiy.tech" style="color:#c4b5fd;text-decoration:underline;">support-care@dejoiy.tech</a>.</p>
				<p style="margin:18px 0 0;font-size:12px;line-height:1.5;color:#64748b;">DEJOIY · Delhi, India · <a href="<?php echo $home; ?>" style="color:#c4b5fd;text-decoration:none;">dejoiy.tech</a></p>
			</div>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * @param array<string, string> $data Submission data.
 * @return string
 */
function dejoiy_contact_xp_admin_html( $data ) {
	$rows = '';
	foreach ( $data as $label => $value ) {
		$rows .= '<tr><td style="padding:10px 12px;border-bottom:1px solid #e2e8f0;font-weight:600;color:#475569;width:140px;">' . esc_html( $label ) . '</td>';
		$rows .= '<td style="padding:10px 12px;border-bottom:1px solid #e2e8f0;color:#0f172a;">' . esc_html( $value ) . '</td></tr>';
	}

	return '<div style="font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;max-width:640px;margin:0 auto;">'
		. '<h2 style="color:#7c3aed;margin:0 0 16px;">New contact form — dejoiy.tech/contact-us</h2>'
		. '<table style="width:100%;border-collapse:collapse;background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">'
		. $rows
		. '</table>'
		. '<p style="font-size:12px;color:#94a3b8;margin-top:16px;">Routed via forms.dejoiy.tech · Resend</p></div>';
}

/**
 * AJAX: handle contact form submission.
 */
function dejoiy_contact_xp_ajax_submit() {
	check_ajax_referer( 'dejoiy_contact_xp', 'nonce' );

	if ( ! dejoiy_contact_xp_enabled() ) {
		wp_send_json_error( array( 'message' => __( 'Contact form is unavailable.', 'dejoiy' ) ), 403 );
	}

	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	$rate_key = 'dejoiy_contact_rate_' . md5( $ip );
	if ( get_transient( $rate_key ) ) {
		wp_send_json_error( array( 'message' => __( 'Please wait a moment before sending another message.', 'dejoiy' ) ), 429 );
	}

	if ( ! empty( $_POST['company'] ) ) {
		wp_send_json_success( array( 'message' => __( 'Thank you! We will be in touch soon.', 'dejoiy' ) ) );
	}

	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$subject = isset( $_POST['subject'] ) ? sanitize_key( wp_unslash( $_POST['subject'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

	if ( '' === $name || '' === $email || '' === $message || ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please fill in your name, email, and message.', 'dejoiy' ) ), 400 );
	}

	$subjects = dejoiy_contact_xp_subjects();
	if ( ! isset( $subjects[ $subject ] ) ) {
		$subject = 'technical-help';
	}
	$subject_label = $subjects[ $subject ];
	$display_name  = dejoiy_contact_xp_format_display_name( $name );

	$payload = array(
		'Name'    => $display_name,
		'Email'   => $email,
		'Subject' => $subject_label,
		'Message' => $message,
		'Page'    => home_url( '/contact-us/' ),
		'IP'      => $ip,
		'Time'    => gmdate( 'Y-m-d H:i:s' ) . ' UTC',
	);

	dejoiy_contact_xp_forward_forms_hub( $payload );

	$admin_result = dejoiy_contact_xp_resend_send(
		DEJOIY_CONTACT_XP_ADMIN_EMAIL,
		sprintf( '[DEJOIY Contact] %s — %s', $subject_label, $display_name ),
		dejoiy_contact_xp_admin_html( $payload ),
		$email
	);
	if ( is_wp_error( $admin_result ) ) {
		wp_send_json_error( array( 'message' => $admin_result->get_error_message() ), 500 );
	}

	$thanks_result = dejoiy_contact_xp_resend_send(
		$email,
		__( 'Thank you for contacting DEJOIY', 'dejoiy' ),
		dejoiy_contact_xp_thank_you_html( $name, $subject_label )
	);
	if ( is_wp_error( $thanks_result ) ) {
		// Admin notified — still success for the visitor.
		set_transient( $rate_key, 1, MINUTE_IN_SECONDS * 2 );
		wp_send_json_success(
			array(
				'message' => __( 'Your message was received. If you do not see a confirmation email shortly, check spam or contact support-care@dejoiy.tech.', 'dejoiy' ),
			)
		);
	}

	set_transient( $rate_key, 1, MINUTE_IN_SECONDS * 2 );
	wp_send_json_success(
		array(
			'message' => __( 'Thank you! A confirmation email is on its way to your inbox.', 'dejoiy' ),
		)
	);
}
add_action( 'wp_ajax_dejoiy_contact_submit', 'dejoiy_contact_xp_ajax_submit' );
add_action( 'wp_ajax_nopriv_dejoiy_contact_submit', 'dejoiy_contact_xp_ajax_submit' );

/**
 * Delhi map embed URL.
 *
 * @return string
 */
function dejoiy_contact_xp_map_embed_url() {
	return (string) apply_filters(
		'dejoiy_contact_xp_map_embed_url',
		'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d28018.131622757!2d77.191949!3d28.613939!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390cfd5b347eb62d%3A0x52c2b7494cc4777!2sNew%20Delhi%2C%20Delhi!5e0!3m2!1sen!2sin!4v1717430400000!5m2!1sen!2sin'
	);
}

/**
 * @return string
 */
function dejoiy_contact_xp_html() {
	$subjects     = dejoiy_contact_xp_subjects();
	$map_url      = dejoiy_contact_xp_map_embed_url();
	$logo_url     = dejoiy_contact_xp_brand_logo_url();
	$favicon_url  = dejoiy_contact_xp_brand_favicon_url();

	ob_start();
	?>
	<div class="dcu-shell" id="dejoiy-contact-xp">
		<section class="dcu-hero" aria-labelledby="dcu-hero-title">
			<div class="dcu-hero__glow" aria-hidden="true"></div>
			<div class="dcu-hero__inner">
				<img class="dcu-hero__logo" src="<?php echo esc_url( $logo_url ); ?>" alt="<?php esc_attr_e( 'DEJOIY', 'dejoiy' ); ?>" width="132" height="44" loading="eager" decoding="async">
				<p class="dcu-hero__kicker"><?php esc_html_e( 'Support', 'dejoiy' ); ?></p>
				<h1 class="dcu-hero__title" id="dcu-hero-title"><?php esc_html_e( 'We are here to help you shop with confidence', 'dejoiy' ); ?></h1>
				<p class="dcu-hero__sub"><?php esc_html_e( 'Questions about products, orders, partnerships, or legal matters — reach our India team anytime.', 'dejoiy' ); ?></p>
			</div>
		</section>

		<div class="dcu-grid">
			<div class="dcu-grid__map">
				<iframe
					class="dcu-map"
					title="<?php esc_attr_e( 'DEJOIY location — Delhi, India', 'dejoiy' ); ?>"
					src="<?php echo esc_url( $map_url ); ?>"
					loading="lazy"
					referrerpolicy="no-referrer-when-downgrade"
					allowfullscreen
				></iframe>
			</div>

			<div class="dcu-grid__info">
				<div class="dcu-info-cards">
					<article class="dcu-info-card">
						<div class="dcu-info-card__icon" aria-hidden="true">📍</div>
						<div class="dcu-info-card__body">
							<h2 class="dcu-info-card__title"><?php esc_html_e( 'Our Place', 'dejoiy' ); ?></h2>
							<p class="dcu-info-card__text"><?php esc_html_e( 'Delhi, India', 'dejoiy' ); ?></p>
							<p class="dcu-info-card__text"><a href="tel:+01146594425">+011 46594425</a></p>
						</div>
					</article>
					<article class="dcu-info-card">
						<div class="dcu-info-card__icon" aria-hidden="true">💬</div>
						<div class="dcu-info-card__body">
							<h2 class="dcu-info-card__title"><?php esc_html_e( 'Quick Help', 'dejoiy' ); ?></h2>
							<p class="dcu-info-card__text"><?php esc_html_e( 'You can ask anything you want to know about our products', 'dejoiy' ); ?></p>
							<p class="dcu-info-card__text"><a href="mailto:support-care@dejoiy.tech">support-care@dejoiy.tech</a></p>
							<p class="dcu-info-card__text"><a href="mailto:legal@dejoiy.tech">legal@dejoiy.tech</a></p>
						</div>
					</article>
				</div>

				<section class="dcu-form-wrap" aria-labelledby="dcu-form-title">
					<h2 class="dcu-form__title" id="dcu-form-title"><?php esc_html_e( 'Send a Message', 'dejoiy' ); ?></h2>
					<form class="dcu-form" id="dejoiy-contact-form" novalidate>
						<input type="text" name="company" class="dcu-honeypot" tabindex="-1" autocomplete="off" aria-hidden="true">
						<div class="dcu-field">
							<input type="text" id="dcu-name" name="name" class="dcu-input" placeholder="<?php esc_attr_e( 'Your name', 'dejoiy' ); ?>" required autocomplete="name">
						</div>
						<div class="dcu-field dcu-field--icon" style="--dcu-favicon:url('<?php echo esc_url( $favicon_url ); ?>');">
							<input type="email" id="dcu-email" name="email" class="dcu-input dcu-input--icon" placeholder="<?php esc_attr_e( 'Your E-mail', 'dejoiy' ); ?>" required autocomplete="email">
						</div>
						<div class="dcu-field dcu-field--select">
							<select id="dcu-subject" name="subject" class="dcu-input dcu-select" required>
								<?php foreach ( $subjects as $key => $label ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( 'technical-help', $key ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="dcu-field">
							<textarea id="dcu-message" name="message" class="dcu-input dcu-textarea" rows="6" placeholder="<?php esc_attr_e( 'Message', 'dejoiy' ); ?>" required></textarea>
						</div>
						<div class="dcu-form__actions">
							<button type="submit" class="dcu-submit" id="dcu-submit">
								<span class="dcu-submit__label"><?php esc_html_e( 'Submit', 'dejoiy' ); ?></span>
								<span class="dcu-submit__spinner" hidden aria-hidden="true"></span>
							</button>
						</div>
						<p class="dcu-form__status" id="dcu-form-status" role="status" aria-live="polite"></p>
					</form>
				</section>
			</div>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * @param string $content Post content.
 * @return string
 */
function dejoiy_contact_xp_replace_content( $content ) {
	if ( ! dejoiy_contact_xp_is_active() || is_admin() ) {
		return $content;
	}

	static $done = false;
	if ( $done ) {
		return $content;
	}
	$done = true;

	return dejoiy_contact_xp_html();
}
add_filter( 'the_content', 'dejoiy_contact_xp_replace_content', 9999 );
add_filter( 'elementor/frontend/the_content', 'dejoiy_contact_xp_replace_content', 9999 );

/**
 * @param array<int, string> $classes Body classes.
 * @return array<int, string>
 */
function dejoiy_contact_xp_body_class( $classes ) {
	if ( dejoiy_contact_xp_is_active() ) {
		$classes[] = 'dejoiy-contact-xp';
	}
	return $classes;
}
add_filter( 'body_class', 'dejoiy_contact_xp_body_class', 24 );

/**
 * Enqueue assets.
 */
function dejoiy_contact_xp_assets() {
	if ( ! dejoiy_contact_xp_is_active() ) {
		return;
	}
	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();
	$css = $dir . '/dejoiy-contact-experience.css';
	$js  = $dir . '/dejoiy-contact-experience.js';
	$ver = DEJOIY_CONTACT_XP_VERSION;
	if ( is_readable( $css ) ) {
		wp_enqueue_style(
			'dejoiy-contact-experience',
			$uri . '/dejoiy-contact-experience.css',
			array(),
			$ver . '.' . (string) filemtime( $css )
		);
	}
	if ( is_readable( $js ) ) {
		wp_enqueue_script(
			'dejoiy-contact-experience',
			$uri . '/dejoiy-contact-experience.js',
			array(),
			$ver . '.' . (string) filemtime( $js ),
			true
		);
		wp_localize_script(
			'dejoiy-contact-experience',
			'dejoiyContactXp',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'dejoiy_contact_xp' ),
				'i18n'    => array(
					'sending'  => __( 'Sending…', 'dejoiy' ),
					'submit'   => __( 'Submit', 'dejoiy' ),
					'error'    => __( 'Something went wrong. Please try again or email support-care@dejoiy.tech.', 'dejoiy' ),
				),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'dejoiy_contact_xp_assets', 1016 );

/**
 * Hide legacy Elementor contact chrome + breadcrumbs.
 */
function dejoiy_contact_xp_hide_legacy() {
	if ( ! dejoiy_contact_xp_is_active() ) {
		return;
	}
	echo '<style id="dejoiy-contact-xp-guard">';
	echo 'body.dejoiy-contact-xp .page-title,body.dejoiy-contact-xp .woocommerce-breadcrumb,body.dejoiy-contact-xp .breadcrumbs{display:none!important;}';
	echo 'body.dejoiy-contact-xp .elementor-36 .elementor-element-0fff535{display:none!important;}';
	echo 'body.dejoiy-contact-xp .dcu-select{height:auto!important;max-height:none!important;text-indent:0!important;}';
	echo 'body.dejoiy-contact-xp .dcu-field--select,body.dejoiy-contact-xp .dcu-form-wrap{overflow:visible!important;}';
	echo '</style>';
}
add_action( 'wp_head', 'dejoiy_contact_xp_hide_legacy', 3 );

/**
 * Better meta description for contact page.
 *
 * @param string $description Description.
 * @return string
 */
function dejoiy_contact_xp_meta_description( $description ) {
	if ( ! dejoiy_contact_xp_is_active() ) {
		return $description;
	}
	return __( 'Contact DEJOIY in Delhi, India. Product help, order support, partnerships, and legal inquiries — support-care@dejoiy.tech', 'dejoiy' );
}
add_filter( 'aioseo_description', 'dejoiy_contact_xp_meta_description', 20 );
