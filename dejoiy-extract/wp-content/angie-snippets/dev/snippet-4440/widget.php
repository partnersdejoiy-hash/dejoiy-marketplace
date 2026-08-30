<?php
namespace AngieSnippets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Hero_Banner_5d75b94f extends \Elementor\Widget_Base {

	public function get_name() {
		return 'hero_banner_5d75b94f';
	}

	public function get_title() {
		return 'Hero Banner Studio';
	}

	public function get_icon() {
		return 'eicon-banner';
	}

	public function get_categories() {
		return [ 'general' ];
	}

	public function get_style_depends() {
		return [ 'hero-banner-style-5d75b94f' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => 'Content',
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'video_url',
			[
				'label' => 'Video URL',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'your-video-link.mp4',
			]
		);

		$this->add_control(
			'title',
			[
				'label' => 'Title',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '1. Introduction',
			]
		);

		$this->add_control(
			'content',
			[
				'label' => 'Content',
				'type' => \Elementor\Controls_Manager::WYSIWYG,
				'default' => '<p>Welcome to DEJOIY.</p><p>DEJOIY is a modern digital commerce and services marketplace offering products, custom-made items, refurbished products, digital products, seller services, business services, and other commerce-related solutions.</p><p>These Terms form a legally binding agreement between:</p><p>DEJOIY India Private Limited (“DEJOIY”, “we”, “us”, “our”)<br>and<br>Any user, visitor, customer, seller, vendor, creator, partner, or business accessing the Platform (“you”, “user”).</p><p>If you do not agree with these Terms, you must discontinue use of the Platform immediately.</p>',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<section class="hero-banner">
			<div class="video-container">
				<video autoplay muted loop playsinline class="banner-video">
					<source src="<?php echo esc_url( $settings['video_url'] ); ?>" type="video/mp4">
				</video>
			</div>
			
			<div class="hero-content">
				<div class="glass-card">
					<h1><?php echo esc_html( $settings['title'] ); ?></h1>
					<div class="intro-content">
						<?php echo wp_kses_post( $settings['content'] ); ?>
					</div>
					
					<div class="search-container">
						<form action="/" method="get" class="hero-search-form">
							<input type="text" name="s" placeholder="Search for products..." required>
							<button type="submit" class="btn-search">Search</button>
						</form>
					</div>

					<div class="cta-group">
						<a href="#" class="btn-primary">Start Designing</a>
						<a href="#" class="btn-primary btn-outline">Explore Universe</a>
					</div>
				</div>
			</div>
		</section>
		<?php
	}
}
