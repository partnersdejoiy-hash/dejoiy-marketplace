<?php
/**
 * DEJOIY Order Tracking UI — Amazon-Style Visualizer.
 *
 * Adds a visual tracking progress bar to the WooCommerce View Order page.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Display the order tracking UI on the view order page.
 *
 * @param int $order_id The Order ID.
 */
function dejoiy_order_tracking_ui( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	$status = $order->get_status();
	
	// Map WooCommerce statuses to tracking steps
	// Steps: 1: Ordered, 2: Shipped (Processing), 3: Out for Delivery (custom or near complete), 4: Delivered (Completed)
	$step = 1;
	$status_text = 'Ordered';
	$delivery_text = 'Preparing for Dispatch';
	
	if ( in_array( $status, array( 'processing' ), true ) ) {
		$step = 2;
		$status_text = 'Shipped';
		$delivery_text = 'On the way';
	} elseif ( in_array( $status, array( 'completed' ), true ) ) {
		$step = 4;
		$status_text = 'Delivered';
		$delivery_text = 'Delivered';
	} elseif ( in_array( $status, array( 'cancelled', 'refunded', 'failed' ), true ) ) {
		$step = 0;
		$status_text = ucfirst( $status );
		$delivery_text = 'Order ' . ucfirst( $status );
	}

	$date_format = get_option( 'date_format' );
	$ordered_date = wc_format_datetime( $order->get_date_created(), $date_format );
	
	// Calculate estimated delivery
	$estimated = '';
	if ( $step > 0 && $step < 4 ) {
		$created = $order->get_date_created();
		if ( $created ) {
			$est = clone $created;
			$est->modify( '+3 days' );
			$estimated = 'Arriving ' . $est->date_i18n( 'l, M j' );
		}
	} elseif ( $step === 4 ) {
		$completed = $order->get_date_completed();
		if ( $completed ) {
			$estimated = 'Delivered on ' . $completed->date_i18n( 'l, M j' );
		} else {
			$estimated = 'Delivered';
		}
	}
	?>
	<div class="djy-tracking-container">
		<div class="djy-tracking-header">
			<h2 class="djy-tracking-title"><?php echo esc_html( $estimated ? $estimated : $delivery_text ); ?></h2>
			<?php if ( $step > 0 && $step < 4 ) : ?>
				<p class="djy-tracking-sub"><?php esc_html_e( 'Track your package to see the latest updates.', 'dejoiy' ); ?></p>
			<?php endif; ?>
		</div>
		
		<?php if ( $step > 0 ) : ?>
		<div class="djy-tracking-progress">
			<div class="djy-track-step <?php echo $step >= 1 ? 'active' : ''; ?>">
				<div class="djy-track-icon">✓</div>
				<div class="djy-track-label"><?php esc_html_e( 'Ordered', 'dejoiy' ); ?></div>
				<div class="djy-track-date"><?php echo esc_html( $ordered_date ); ?></div>
			</div>
			
			<div class="djy-track-line <?php echo $step >= 2 ? 'active' : ''; ?>"></div>
			
			<div class="djy-track-step <?php echo $step >= 2 ? 'active' : ''; ?>">
				<div class="djy-track-icon">📦</div>
				<div class="djy-track-label"><?php esc_html_e( 'Shipped', 'dejoiy' ); ?></div>
			</div>
			
			<div class="djy-track-line <?php echo $step >= 3 ? 'active' : ''; ?>"></div>
			
			<div class="djy-track-step <?php echo $step >= 3 ? 'active' : ''; ?>">
				<div class="djy-track-icon">🚚</div>
				<div class="djy-track-label"><?php esc_html_e( 'Out for delivery', 'dejoiy' ); ?></div>
			</div>
			
			<div class="djy-track-line <?php echo $step >= 4 ? 'active' : ''; ?>"></div>
			
			<div class="djy-track-step <?php echo $step >= 4 ? 'active' : ''; ?>">
				<div class="djy-track-icon">📍</div>
				<div class="djy-track-label"><?php esc_html_e( 'Arriving', 'dejoiy' ); ?></div>
			</div>
		</div>
		<?php else : ?>
		<div class="djy-tracking-cancelled">
			<p><strong><?php echo esc_html( $status_text ); ?></strong></p>
			<p><?php esc_html_e( 'This order was cancelled or refunded and will not be delivered.', 'dejoiy' ); ?></p>
		</div>
		<?php endif; ?>
		
		<style>
			.djy-tracking-container {
				background: #fff;
				border: 1px solid #d5d9d9;
				border-radius: 8px;
				padding: 24px;
				margin-bottom: 30px;
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
			}
			.djy-tracking-header {
				margin-bottom: 24px;
			}
			.djy-tracking-title {
				margin: 0 0 4px 0;
				font-size: 1.5rem;
				font-weight: 700;
				color: #0f1111;
			}
			.djy-tracking-sub {
				margin: 0;
				color: #565959;
				font-size: 0.9rem;
			}
			.djy-tracking-progress {
				display: flex;
				align-items: flex-start;
				justify-content: space-between;
				position: relative;
				margin-top: 10px;
				margin-bottom: 20px;
			}
			.djy-track-step {
				display: flex;
				flex-direction: column;
				align-items: center;
				text-align: center;
				width: 80px;
				position: relative;
				z-index: 2;
			}
			.djy-track-icon {
				width: 32px;
				height: 32px;
				border-radius: 50%;
				background: #f0f2f2;
				border: 2px solid #d5d9d9;
				display: flex;
				align-items: center;
				justify-content: center;
				font-size: 14px;
				color: #565959;
				margin-bottom: 8px;
				transition: all 0.3s;
			}
			.djy-track-step.active .djy-track-icon {
				background: #007185;
				border-color: #007185;
				color: #fff;
			}
			.djy-track-label {
				font-size: 13px;
				font-weight: 600;
				color: #0f1111;
				line-height: 1.3;
			}
			.djy-track-date {
				font-size: 11px;
				color: #565959;
				margin-top: 4px;
			}
			.djy-track-line {
				flex-grow: 1;
				height: 4px;
				background: #d5d9d9;
				margin-top: 14px;
				z-index: 1;
			}
			.djy-track-line.active {
				background: #007185;
			}
			.djy-tracking-cancelled {
				padding: 16px;
				background: #fcf4f4;
				border-left: 4px solid #cc0c39;
				border-radius: 4px;
			}
			.djy-tracking-cancelled p {
				margin: 0 0 4px 0;
				color: #0f1111;
			}
			
			@media (max-width: 600px) {
				.djy-tracking-progress {
					flex-direction: column;
					align-items: flex-start;
				}
				.djy-track-step {
					flex-direction: row;
					width: 100%;
					text-align: left;
					align-items: center;
					margin-bottom: 24px;
				}
				.djy-track-icon {
					margin-bottom: 0;
					margin-right: 16px;
				}
				.djy-track-line {
					position: absolute;
					width: 4px;
					height: calc(100% - 32px);
					left: 14px;
					top: 32px;
					margin: 0;
				}
				.djy-track-label {
					font-size: 15px;
				}
				.djy-track-date {
					margin-top: 0;
					margin-left: 8px;
				}
			}
		</style>
	</div>
	<?php
}
add_action( 'woocommerce_view_order', 'dejoiy_order_tracking_ui', 5 );
