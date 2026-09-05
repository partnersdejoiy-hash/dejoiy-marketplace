<?php
/**
 * DEJOIY Unified Marketplace Archive Hero — shop + every product category.
 *
 * Renders a single branded header band above the Elementor archive template
 * and hides the old per-category promo/sale banner. Keeps one merged source
 * of category navigation (all WooCommerce categories as a chip scroller).
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dejoiy_archive_hero_is_product_archive() {
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return false;
	}
	return is_shop() || is_product_taxonomy();
}

function dejoiy_archive_hero_categories() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'number'     => 0,
		)
	);
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}
	$out = array();
	foreach ( $terms as $t ) {
		$link = get_term_link( $t );
		if ( is_wp_error( $link ) ) {
			continue;
		}
		$out[] = array(
			'name' => $t->name,
			'url'  => $link,
			'term' => $t->term_id,
		);
	}
	usort(
		$out,
		function ( $a, $b ) {
			return strcasecmp( $a['name'], $b['name'] );
		}
	);
	return $out;
}

function dejoiy_archive_hero_data() {
	if ( is_product_taxonomy() ) {
		$term = get_queried_object();
		if ( $term && isset( $term->term_id, $term->name ) ) {
			$title       = $term->name;
			$description = (string) term_description( $term->term_id, $term->taxonomy );
			$count       = (int) $term->count;
			if ( $count <= 0 ) {
				$query = new WP_Query(
					array(
						'post_type'      => 'product',
						'post_status'    => 'publish',
						'fields'         => 'ids',
						'posts_per_page' => 1,
						'no_found_rows'  => false,
						'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery
							array(
								'taxonomy'         => 'product_cat',
								'field'            => 'term_id',
								'terms'            => $term->term_id,
								'include_children' => true,
							),
						),
					)
				);
				$count = (int) $query->found_posts;
			}
			$current = $term->term_id;
			return compact( 'title', 'description', 'count', 'current' );
		}
	}
	$count   = (int) wp_count_posts( 'product' )->publish;
	$current = 0;
	return array(
		'title'       => __( 'Shop', 'dejoiy' ),
		'description' => __( 'Every world of DEJOIY — products, creators and categories — under one roof.', 'dejoiy' ),
		'count'       => $count,
		'current'     => $current,
	);
}

function dejoiy_archive_hero_output() {
	if ( ! dejoiy_archive_hero_is_product_archive() ) {
		return;
	}

	$data   = dejoiy_archive_hero_data();
	$cats   = dejoiy_archive_hero_categories();
	$title  = isset( $data['title'] ) ? $data['title'] : '';
	$sub    = isset( $data['description'] ) && '' !== $data['description'] ? $data['description'] : sprintf( __( 'Explore every product in %s — shop with care, grow bigger.', 'dejoiy' ), esc_html( $title ) );
	$count  = isset( $data['count'] ) ? (int) $data['count'] : 0;
	$label  = sprintf( _n( '%s product', '%s products', $count, 'dejoiy' ), number_format_i18n( $count ) );
	?>
	<section class="dah" data-dejoiy-archive-hero>
		<div class="dah__in">
			<div class="dah__copy">
				<p class="dah__kicker">DEJOIY &middot; <?php echo is_product_taxonomy() ? esc_html__( 'Category', 'dejoiy' ) : esc_html__( 'Marketplace', 'dejoiy' ); ?></p>
				<h2 class="dah__title"><?php echo esc_html( $title ); ?></h2>
				<p class="dah__sub"><?php echo esc_html( wp_strip_all_tags( $sub ) ); ?></p>
				<div class="dah__meta">
					<?php if ( $count > 0 ) : ?>
						<span class="dah__count"><?php echo esc_html( $label ); ?></span>
					<?php endif; ?>
					<?php if ( is_product_taxonomy() ) : ?>
						<a class="dah__all" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) ); ?>"><?php esc_html_e( 'Browse all shops', 'dejoiy' ); ?> &rarr;</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php if ( ! empty( $cats ) ) : ?>
			<nav class="dah__chips" aria-label="<?php esc_attr_e( 'All product categories', 'dejoiy' ); ?>">
				<?php foreach ( $cats as $c ) : ?>
					<a class="dah__chip<?php echo isset( $data['current'] ) && (int) $data['current'] === (int) ( $c['term'] ?? 0 ) ? ' dah__chip--on' : ''; ?>" href="<?php echo esc_url( $c['url'] ); ?>"><?php echo esc_html( $c['name'] ); ?></a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>
	</section>
	<?php
}

function dejoiy_archive_hero_before_render( $manager ) {
	if ( ! dejoiy_archive_hero_is_product_archive() ) {
		return;
	}
	dejoiy_archive_hero_output();
}
add_action( 'elementor/theme/before_do_archive', 'dejoiy_archive_hero_before_render', 5, 1 );

function dejoiy_archive_hero_assets() {
	if ( ! dejoiy_archive_hero_is_product_archive() ) {
		return;
	}
	$file = get_stylesheet_directory() . '/dejoiy-archive-hero.css';
	wp_enqueue_style(
		'dejoiy-archive-hero',
		get_stylesheet_directory_uri() . '/dejoiy-archive-hero.css',
		array(),
		(string) ( is_readable( $file ) ? filemtime( $file ) : '1.0.0' )
	);
}
add_action( 'wp_enqueue_scripts', 'dejoiy_archive_hero_assets', 1030 );