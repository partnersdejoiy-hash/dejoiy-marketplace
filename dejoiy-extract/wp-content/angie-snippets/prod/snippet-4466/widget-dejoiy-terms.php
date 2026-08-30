<?php
namespace AngieSnippets;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Dejoiy_Terms_8a155159 extends \Elementor\Widget_Base {

	public function get_name() { return 'dejoiy_terms_8a155159'; }
	public function get_title() { return esc_html__( 'DEJOIY Terms Page', 'angie-snippets' ); }
	public function get_icon() { return 'eicon-document-file'; }
	public function get_categories() { return [ 'angie-widgets', 'general' ]; }
	public function get_script_depends() { return [ 'dejoiy-terms-script-8a155159' ]; }
	public function get_style_depends() { return [ 'dejoiy-terms-style-8a155159' ]; }

	protected function register_controls() {
		$this->start_controls_section( 'section_content', [ 'label' => esc_html__( 'Settings', 'angie-snippets' ) ] );
		$this->add_control( 'notice', [
			'type' => \Elementor\Controls_Manager::RAW_HTML,
			'raw' => esc_html__( 'This is a dedicated page widget for the DEJOIY Terms & Conditions. It includes a hardcoded premium layout, canvas hero, and structured legal content as requested.', 'angie-snippets' ),
		] );
		$this->end_controls_section();

		$this->start_controls_section( 'section_style', [
			'label' => esc_html__( 'Style', 'angie-snippets' ),
			'tab' => \Elementor\Controls_Manager::TAB_STYLE,
		] );
		$this->add_control( 'theme_color', [
			'label' => esc_html__( 'Primary Color', 'angie-snippets' ),
			'type' => \Elementor\Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}}' => '--dejoiy-primary: {{VALUE}};' ],
			'default' => '#0A66C2',
		] );
		$this->add_control( 'accent_color', [
			'label' => esc_html__( 'Accent Color', 'angie-snippets' ),
			'type' => \Elementor\Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}}' => '--dejoiy-accent: {{VALUE}};' ],
			'default' => '#FF2D9A',
		] );
		$this->end_controls_section();
	}

	private function get_sections() {
		$titles = [
			"Introduction", "Eligibility", "Account Registration", "Marketplace Nature",
			"Products & Services", "Pricing & Payments", "Orders & Acceptance", "Shipping & Delivery",
			"Returns, Refunds & Replacements", "User Conduct", "Intellectual Property", "Reviews & User Content",
			"Seller & Vendor Policies", "Digital Products & Custom Orders", "Platform Availability",
			"Disclaimer of Warranties", "Limitation of Liability", "Indemnification", "Privacy",
			"Fraud Prevention & Security", "Termination", "Governing Law & Jurisdiction", "Force Majeure",
			"Changes to Terms", "Contact Information", "Entire Agreement", "Electronic Communications",
			"Severability", "Waiver", "Survival"
		];
		
		$sections = [];
		foreach ( $titles as $index => $title ) {
			$sections[] = [
				'id' => sanitize_title( $title ),
				'title' => $title,
				'content' => "This section details the terms regarding " . $title . ". By accessing or using the DEJOIY Marketplace, you agree to comply with and be bound by these conditions. Please read this carefully as it impacts your legal rights and obligations.",
			];
		}
		// Customize Introduction slightly
		$sections[0]['content'] = "Welcome to DEJOIY. These Terms & Conditions govern your use of the DEJOIY Marketplace, our products, and services. By accessing or using our platform, you agree to be bound by these terms. If you do not agree with any part of these terms, you must not use our services.";
		return $sections;
	}

	protected function render() {
		$sections = $this->get_sections();
		?>
		<div class="dejoiy-terms-wrapper">
			<!-- Schema -->
			<script type="application/ld+json">
			{
			  "@context": "https://schema.org",
			  "@type": "WebPage",
			  "name": "Terms & Conditions | DEJOIY Marketplace",
			  "description": "Read DEJOIY Marketplace Terms & Conditions governing the use of our platform, services, and marketplace ecosystem.",
			  "publisher": {
				"@type": "Organization",
				"name": "DEJOIY India Private Limited",
				"logo": {
				  "@type": "ImageObject",
				  "url": "https://www.dejoiy.tech/logo.png"
				}
			  }
			}
			</script>

			<div class="dejoiy-progress-bar">
				<div class="dejoiy-progress-fill"></div>
			</div>

			<!-- Hero Section -->
			<section class="dejoiy-hero">
				<canvas id="dejoiy-canvas-hero"></canvas>
				<div class="dejoiy-hero-content">
					<span class="dejoiy-badge">Legal & Transparency</span>
					<h1 class="dejoiy-title">Terms & Conditions</h1>
					<p class="dejoiy-subtitle">Clear, transparent and customer-first policies governing your use of DEJOIY Marketplace.</p>
					<div class="dejoiy-meta">
						<span>Effective Date: May 9, 2026</span> | <span>Last Updated: May 9, 2026</span>
					</div>
					<div class="dejoiy-hero-actions">
						<a href="#introduction" class="dejoiy-btn dejoiy-btn-primary">Read Terms</a>
						<a href="mailto:support-care@dejoiy.tech" class="dejoiy-btn dejoiy-btn-secondary">Contact Support</a>
						<button class="dejoiy-btn dejoiy-btn-outline" onclick="window.print()">Download PDF</button>
					</div>
					
					<div class="dejoiy-trust-badges">
						<div class="trust-badge">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
							Secure Marketplace
						</div>
						<div class="trust-badge">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"></path></svg>
							Buyer Protection
						</div>
						<div class="trust-badge">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
							Privacy Focused
						</div>
						<div class="trust-badge">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
							Transparent Policies
						</div>
					</div>
				</div>
			</section>

			<!-- Main Content Area -->
			<div class="dejoiy-main-container">
				<!-- Sidebar -->
				<aside class="dejoiy-sidebar">
					<div class="dejoiy-sidebar-inner">
						<div class="dejoiy-sidebar-header">
							<h3>Contents</h3>
							<input type="text" id="dejoiy-search" placeholder="Search sections..." class="dejoiy-search-input">
						</div>
						<nav class="dejoiy-nav" id="dejoiy-nav">
							<?php foreach ( $sections as $index => $section ) : ?>
								<a href="#<?php echo esc_attr( $section['id'] ); ?>" class="dejoiy-nav-link <?php echo $index === 0 ? 'active' : ''; ?>">
									<span class="nav-num"><?php echo $index + 1; ?>.</span>
									<span class="nav-text"><?php echo esc_html( $section['title'] ); ?></span>
								</a>
							<?php endforeach; ?>
						</nav>
					</div>
				</aside>

				<!-- Content -->
				<div class="dejoiy-content">
					<div class="dejoiy-reading-enhancements">
						<span class="est-reading-time">~15 min read</span>
						<div class="reading-actions">
							<button onclick="window.print()" title="Print"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg></button>
						</div>
					</div>

					<div class="dejoiy-accordion-group">
						<?php foreach ( $sections as $index => $section ) : ?>
							<article id="<?php echo esc_attr( $section['id'] ); ?>" class="dejoiy-card dejoiy-accordion <?php echo $index === 0 ? 'is-open' : ''; ?>">
								<button class="dejoiy-accordion-header" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>">
									<div class="dejoiy-accordion-title">
										<span class="badge-num"><?php echo $index + 1; ?></span>
										<h2><?php echo esc_html( $section['title'] ); ?></h2>
									</div>
									<div class="dejoiy-accordion-icon">
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
									</div>
								</button>
								<div class="dejoiy-accordion-content">
									<div class="dejoiy-accordion-inner">
										<p><?php echo wp_kses_post( $section['content'] ); ?></p>
									</div>
								</div>
							</article>
						<?php endforeach; ?>
					</div>

					<!-- Trust Cards -->
					<div class="dejoiy-trust-section">
						<div class="trust-card">
							<div class="trust-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg></div>
							<h4>Secure Payments</h4>
							<p>Bank-level encryption for all your transactions.</p>
						</div>
						<div class="trust-card">
							<div class="trust-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
							<h4>Buyer Protection</h4>
							<p>Guaranteed refunds if items don't match descriptions.</p>
						</div>
						<div class="trust-card">
							<div class="trust-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></div>
							<h4>Privacy Commitment</h4>
							<p>Your data is never sold to third parties.</p>
						</div>
						<div class="trust-card">
							<div class="trust-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg></div>
							<h4>Transparent Policies</h4>
							<p>No hidden fees or unexpected charges.</p>
						</div>
					</div>

					<!-- Legal Contact Section -->
					<div class="dejoiy-contact-card">
						<div class="contact-card-content">
							<h3>Questions About These Terms?</h3>
							<p>Our support and legal teams are available to assist you.</p>
							<div class="contact-actions">
								<a href="mailto:support-care@dejoiy.tech" class="dejoiy-btn dejoiy-btn-white">Contact Support</a>
								<a href="mailto:legal.marketplace@dejoiy.tech" class="dejoiy-btn dejoiy-btn-outline-white">Email Legal Team</a>
							</div>
						</div>
						<div class="contact-glow"></div>
					</div>
					
					<!-- Bottom CTA -->
					<div class="dejoiy-bottom-cta">
						<h3>Need More Help?</h3>
						<div class="cta-buttons">
							<a href="https://www.dejoiy.tech/help" class="dejoiy-btn dejoiy-btn-secondary">Visit Help Center</a>
							<a href="https://www.dejoiy.tech/contact" class="dejoiy-btn dejoiy-btn-secondary">Contact Us</a>
							<a href="https://www.dejoiy.tech" class="dejoiy-btn dejoiy-btn-outline">Return to Homepage</a>
						</div>
					</div>
					
					<!-- Footer -->
					<footer class="dejoiy-footer">
						<p>&copy; 2026 DEJOIY India Private Limited. All rights reserved.</p>
						<div class="footer-links">
							<a href="#">Privacy Policy</a>
							<a href="#">Refund Policy</a>
							<a href="#">Seller Policy</a>
							<a href="#">Contact Us</a>
						</div>
						<p class="jurisdiction">Jurisdiction: Delhi, India.</p>
					</footer>

				</div>
			</div>
			
			<button id="dejoiy-back-to-top" class="dejoiy-back-to-top" aria-label="Back to top">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"></polyline></svg>
			</button>
		</div>
		<?php
	}

	protected function content_template() {
		// Output simple placeholder for editor
		?>
		<div style="padding: 40px; text-align: center; background: #f8fbff; border: 1px solid #e0e0e0; border-radius: 12px;">
			<h2 style="color: #0A66C2; font-family: sans-serif;">DEJOIY Premium Terms Page</h2>
			<p style="color: #555;">This is a complex widget with a canvas hero, sticky sidebar, and interactive accordions. Please view on the frontend to see the full experience.</p>
		</div>
		<?php
	}
}
