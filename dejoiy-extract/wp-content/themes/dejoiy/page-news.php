<?php
/**
 * News — real DEJOIY posts, not the Elementor XStore demo magazine.
 *
 * @package Dejoiy
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container content-page djy-news-page">
	<div class="djy-news-page__inner">
		<h1 class="djy-news-page__h"><?php esc_html_e( 'DEJOIY Updates', 'dejoiy' ); ?></h1>
		<?php
		if ( function_exists( 'dejoiy_amazon_compete_news_feed_html' ) ) {
			echo dejoiy_amazon_compete_news_feed_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>
	</div>
</div>
<?php
get_footer();
