class DejoiyTermsHandler extends elementorModules.frontend.handlers.Base {
	getDefaultSettings() {
		return {
			selectors: {
				wrapper: '.dejoiy-terms-wrapper',
				canvas: '#dejoiy-canvas-hero',
				accordions: '.dejoiy-accordion',
				headers: '.dejoiy-accordion-header',
				navLinks: '.dejoiy-nav-link',
				searchInput: '#dejoiy-search',
				progressBar: '.dejoiy-progress-fill',
				backToTop: '#dejoiy-back-to-top'
			}
		};
	}

	getDefaultElements() {
		const selectors = this.getSettings('selectors');
		return {
			$wrapper: this.$element.find(selectors.wrapper),
			$canvas: this.$element.find(selectors.canvas),
			$accordions: this.$element.find(selectors.accordions),
			$headers: this.$element.find(selectors.headers),
			$navLinks: this.$element.find(selectors.navLinks),
			$searchInput: this.$element.find(selectors.searchInput),
			$progressBar: this.$element.find(selectors.progressBar),
			$backToTop: this.$element.find(selectors.backToTop)
		};
	}

	bindEvents() {
		this.initCanvas();
		this.initAccordions();
		this.initScrollSpy();
		this.initSearch();
		this.initProgressAndTopBtn();
		
		// Handle hash on load
		if (window.location.hash) {
			setTimeout(() => {
				this.openSectionByHash(window.location.hash);
			}, 500);
		}
	}

	initCanvas() {
		const canvas = this.elements.$canvas[0];
		if (!canvas) return;
		const ctx = canvas.getContext('2d');
		
		let width = canvas.width = canvas.parentElement.offsetWidth;
		let height = canvas.height = canvas.parentElement.offsetHeight;
		
		const particles = [];
		const particleCount = window.innerWidth > 768 ? 60 : 30;
		const colors = ['#0A66C2', '#FF2D9A'];

		let mouse = { x: width/2, y: height/2 };

		canvas.parentElement.addEventListener('mousemove', (e) => {
			const rect = canvas.getBoundingClientRect();
			mouse.x = e.clientX - rect.left;
			mouse.y = e.clientY - rect.top;
		});

		class Particle {
			constructor() {
				this.x = Math.random() * width;
				this.y = Math.random() * height;
				this.vx = (Math.random() - 0.5) * 0.5;
				this.vy = (Math.random() - 0.5) * 0.5;
				this.radius = Math.random() * 2 + 1;
				this.color = colors[Math.floor(Math.random() * colors.length)];
			}
			update() {
				// Parallax effect
				const dx = mouse.x - width/2;
				const dy = mouse.y - height/2;
				
				this.x += this.vx - dx * 0.0001;
				this.y += this.vy - dy * 0.0001;

				if (this.x < 0 || this.x > width) this.vx = -this.vx;
				if (this.y < 0 || this.y > height) this.vy = -this.vy;
			}
			draw() {
				ctx.beginPath();
				ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
				ctx.fillStyle = this.color;
				ctx.fill();
			}
		}

		for (let i = 0; i < particleCount; i++) {
			particles.push(new Particle());
		}

		function animate() {
			ctx.clearRect(0, 0, width, height);
			
			for (let i = 0; i < particles.length; i++) {
				particles[i].update();
				particles[i].draw();
				
				for (let j = i + 1; j < particles.length; j++) {
					const dx = particles[i].x - particles[j].x;
					const dy = particles[i].y - particles[j].y;
					const dist = Math.sqrt(dx * dx + dy * dy);
					
					if (dist < 100) {
						ctx.beginPath();
						ctx.strokeStyle = `rgba(10, 102, 194, ${0.1 - dist/1000})`;
						ctx.lineWidth = 0.5;
						ctx.moveTo(particles[i].x, particles[i].y);
						ctx.lineTo(particles[j].x, particles[j].y);
						ctx.stroke();
					}
				}
			}
			requestAnimationFrame(animate);
		}

		animate();

		window.addEventListener('resize', () => {
			if(canvas.parentElement) {
				width = canvas.width = canvas.parentElement.offsetWidth;
				height = canvas.height = canvas.parentElement.offsetHeight;
			}
		});
	}

	initAccordions() {
		const _this = this;
		this.elements.$headers.on('click', function() {
			const $card = jQuery(this).closest('.dejoiy-accordion');
			const $content = $card.find('.dejoiy-accordion-content');
			const $inner = $card.find('.dejoiy-accordion-inner');
			
			if ($card.hasClass('is-open')) {
				$content.css('height', 0);
				$card.removeClass('is-open');
				jQuery(this).attr('aria-expanded', 'false');
			} else {
				// Optional: close others
				_this.elements.$accordions.removeClass('is-open').find('.dejoiy-accordion-content').css('height', 0);
				_this.elements.$headers.attr('aria-expanded', 'false');
				
				$card.addClass('is-open');
				$content.css('height', $inner.outerHeight() + 'px');
				jQuery(this).attr('aria-expanded', 'true');
				
				// Update hash without scrolling
				history.pushState(null, null, '#' + $card.attr('id'));
			}
		});

		// Initialize heights for already open
		this.elements.$accordions.filter('.is-open').each(function() {
			const $content = jQuery(this).find('.dejoiy-accordion-content');
			const $inner = jQuery(this).find('.dejoiy-accordion-inner');
			$content.css('height', $inner.outerHeight() + 'px');
		});
	}

	openSectionByHash(hash) {
		const $target = this.elements.$wrapper.find(hash);
		if ($target.length && $target.hasClass('dejoiy-accordion')) {
			const $header = $target.find('.dejoiy-accordion-header');
			if (!$target.hasClass('is-open')) {
				$header.trigger('click');
			}
			setTimeout(() => {
				jQuery('html, body').animate({
					scrollTop: $target.offset().top - 100
				}, 500);
			}, 300);
		}
	}

	initScrollSpy() {
		const _this = this;
		
		// Click on nav links
		this.elements.$navLinks.on('click', function(e) {
			e.preventDefault();
			const hash = jQuery(this).attr('href');
			_this.openSectionByHash(hash);
		});

		// Scroll spy
		jQuery(window).on('scroll', () => {
			const scrollPos = jQuery(window).scrollTop() + 150;
			
			this.elements.$accordions.each(function() {
				const $el = jQuery(this);
				const top = $el.offset().top;
				const bottom = top + $el.outerHeight();
				
				if (scrollPos >= top && scrollPos <= bottom) {
					const id = $el.attr('id');
					_this.elements.$navLinks.removeClass('active');
					_this.elements.$navLinks.filter(`[href="#${id}"]`).addClass('active');
				}
			});
		});
	}

	initSearch() {
		this.elements.$searchInput.on('input', (e) => {
			const term = e.target.value.toLowerCase();
			this.elements.$navLinks.each(function() {
				const text = jQuery(this).text().toLowerCase();
				if (text.includes(term)) {
					jQuery(this).show();
				} else {
					jQuery(this).hide();
				}
			});
		});
	}

	initProgressAndTopBtn() {
		jQuery(window).on('scroll', () => {
			// Progress
			const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
			const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
			const scrolled = (winScroll / height) * 100;
			this.elements.$progressBar.css('width', scrolled + '%');

			// Back to top
			if (winScroll > 500) {
				this.elements.$backToTop.addClass('show');
			} else {
				this.elements.$backToTop.removeClass('show');
			}
		});

		this.elements.$backToTop.on('click', () => {
			jQuery('html, body').animate({scrollTop: 0}, 500);
		});
	}
}

jQuery(window).on('elementor/frontend/init', () => {
	const addHandler = ($element) => {
		elementorFrontend.elementsHandler.addHandler(DejoiyTermsHandler, { $element });
	};
	elementorFrontend.hooks.addAction('frontend/element_ready/dejoiy_terms_8a155159.default', addHandler);
});