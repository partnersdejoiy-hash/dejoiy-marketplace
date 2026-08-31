/**
 * DEJOIY Motion Adapters
 * 
 * Premium motion system powered by ThreeUI Community concepts.
 * Lightweight vanilla JS — no React dependency.
 * Respects prefers-reduced-motion.
 * 
 * ThreeUI Community: https://github.com/MengTo/threeui (MIT License)
 * DEJOIY wrapper layer — not raw ThreeUI exposure.
 * 
 * @version 1.0.0
 */

(function () {
    'use strict';

    /* ── Config ── */
    const CONFIG = {
        particleCount: 8,
        constellationDots: 12,
        scrollRevealThreshold: 0.15,
        scrollRevealRootMargin: '0px 0px -60px 0px',
        joiOrbScale: 1.08,
        ambientOrbs: true,
        particles: true,
        constellation: true,
        scrollReveal: true,
    };

    /* ── State ── */
    const state = {
        reducedMotion: false,
        isMobile: false,
        isTablet: false,
        initialized: false,
        observers: [],
        scrollY: 0,
        ticking: false,
    };

    /* ── Detect capabilities ── */
    function detectCapabilities() {
        state.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        state.isMobile = window.innerWidth <= 767;
        state.isTablet = window.innerWidth >= 768 && window.innerWidth <= 1024;

        // Listen for changes
        window.matchMedia('(prefers-reduced-motion: reduce)').addEventListener('change', function (e) {
            state.reducedMotion = e.matches;
            if (state.reducedMotion) {
                destroyAll();
            }
        });
    }

    /* ============================================================
       AMBIENT ORBS — Soft floating background
       ============================================================ */

    function createAmbientOrbs() {
        if (state.reducedMotion || state.isMobile) return;

        const root = document.getElementById('dejoiy-motion-root');
        if (!root) return;

        const orbs = ['blue', 'pink', 'violet'];
        orbs.forEach(function (color, i) {
            const orb = document.createElement('div');
            orb.className = 'dm-ambient-orb dm-ambient-orb--' + color;
            orb.style.animationDelay = (i * 3) + 's';
            root.appendChild(orb);

            // Fade in after short delay
            setTimeout(function () {
                orb.classList.add('dm-ambient-orb--visible');
            }, 800 + (i * 400));
        });
    }

    /* ============================================================
       PARTICLES — Subtle floating dots
       ============================================================ */

    function createParticles() {
        if (state.reducedMotion || state.isMobile) return;

        const root = document.getElementById('dejoiy-motion-root');
        if (!root) return;

        const colors = ['', '--pink', '--violet', '--teal'];

        for (var i = 0; i < CONFIG.particleCount; i++) {
            var particle = document.createElement('div');
            particle.className = 'dm-particle dm-particle' + (colors[i % colors.length]);
            particle.style.left = (Math.random() * 100) + '%';
            particle.style.animationDelay = (Math.random() * 12) + 's';
            particle.style.animationDuration = (10 + Math.random() * 8) + 's';
            particle.style.width = (2 + Math.random() * 4) + 'px';
            particle.style.height = particle.style.width;
            root.appendChild(particle);
        }
    }

    /* ============================================================
       CONSTELLATION FIELD — Network dots
       ============================================================ */

    function createConstellation() {
        if (state.reducedMotion || state.isMobile) return;

        var heroSection = document.querySelector('.du-hero, .wpb_wrapper, [class*="hero"]');
        if (!heroSection) return;

        var container = document.createElement('div');
        container.className = 'dm-constellation';

        for (var i = 0; i < CONFIG.constellationDots; i++) {
            var dot = document.createElement('div');
            dot.className = 'dm-constellation__dot';
            dot.style.left = (5 + Math.random() * 90) + '%';
            dot.style.top = (5 + Math.random() * 90) + '%';
            dot.style.animationDelay = (Math.random() * 4) + 's';
            dot.style.animationDuration = (3 + Math.random() * 3) + 's';
            container.appendChild(dot);
        }

        heroSection.style.position = 'relative';
        heroSection.appendChild(container);
    }

    /* ============================================================
       SCROLL REVEAL — Intersection Observer
       ============================================================ */

    function initScrollReveal() {
        if (state.reducedMotion || !CONFIG.scrollReveal) return;

        var elements = document.querySelectorAll('.dm-reveal');
        if (!elements.length) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('dm-reveal--visible');
                    // Unobserve after reveal (one-time animation)
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: CONFIG.scrollRevealThreshold,
            rootMargin: CONFIG.scrollRevealRootMargin,
        });

        elements.forEach(function (el) {
            observer.observe(el);
        });

        state.observers.push(observer);
    }

    /* ============================================================
       JOI ORB — Interactive hover effects
       ============================================================ */

    function initJoiOrb() {
        var orbs = document.querySelectorAll('.dm-joi-orb');
        orbs.forEach(function (orb) {
            // 3D tilt on mouse move
            orb.addEventListener('mousemove', function (e) {
                if (state.reducedMotion) return;
                var rect = orb.getBoundingClientRect();
                var x = (e.clientX - rect.left) / rect.width - 0.5;
                var y = (e.clientY - rect.top) / rect.height - 0.5;
                var core = orb.querySelector('.dm-joi-orb__core');
                if (core) {
                    core.style.transform = 'rotateY(' + (x * 20) + 'deg) rotateX(' + (-y * 20) + 'deg)';
                }
            });

            orb.addEventListener('mouseleave', function () {
                var core = orb.querySelector('.dm-joi-orb__core');
                if (core) {
                    core.style.transform = 'rotateY(0) rotateX(0)';
                }
            });
        });
    }

    /* ============================================================
       ECOSYSTEM CARDS — Hover depth effect
       ============================================================ */

    function initEcosystemCards() {
        if (state.reducedMotion || state.isMobile) return;

        var cards = document.querySelectorAll('.dm-ecosystem-card');
        cards.forEach(function (card) {
            card.addEventListener('mousemove', function (e) {
                var rect = card.getBoundingClientRect();
                var x = (e.clientX - rect.left) / rect.width - 0.5;
                var y = (e.clientY - rect.top) / rect.height - 0.5;
                card.style.transform = 'translateY(-4px) rotateX(' + (-y * 6) + 'deg) rotateY(' + (x * 6) + 'deg)';
            });

            card.addEventListener('mouseleave', function () {
                card.style.transform = '';
            });
        });
    }

    /* ============================================================
       PARALLAX — Subtle depth on scroll
       ============================================================ */

    function initParallax() {
        if (state.reducedMotion || state.isMobile) return;

        var parallaxElements = document.querySelectorAll('[data-dm-parallax]');
        if (!parallaxElements.length) return;

        function onScroll() {
            state.scrollY = window.pageYOffset;
            if (!state.ticking) {
                requestAnimationFrame(function () {
                    parallaxElements.forEach(function (el) {
                        var speed = parseFloat(el.getAttribute('data-dm-parallax')) || 0.1;
                        var rect = el.getBoundingClientRect();
                        var visible = rect.top < window.innerHeight && rect.bottom > 0;
                        if (visible) {
                            var offset = state.scrollY * speed;
                            el.style.transform = 'translateY(' + (-offset) + 'px)';
                        }
                    });
                    state.ticking = false;
                });
                state.ticking = true;
            }
        }

        window.addEventListener('scroll', onScroll, { passive: true });
    }

    /* ============================================================
       LIQUID BACKGROUND — Hero gradient
       ============================================================ */

    function initLiquidBackground() {
        var hero = document.querySelector('.du-hero');
        if (!hero || state.reducedMotion) return;

        var bg = document.createElement('div');
        bg.className = 'dm-liquid-bg';

        for (var i = 1; i <= 3; i++) {
            var blob = document.createElement('div');
            blob.className = 'dm-liquid-bg__blob dm-liquid-bg__blob--' + i;
            bg.appendChild(blob);
        }

        hero.style.position = 'relative';
        hero.insertBefore(bg, hero.firstChild);
    }

    /* ============================================================
       CLEANUP
       ============================================================ */

    function destroyAll() {
        state.observers.forEach(function (obs) { obs.disconnect(); });
        state.observers = [];

        var root = document.getElementById('dejoiy-motion-root');
        if (root) root.innerHTML = '';

        var liquidBgs = document.querySelectorAll('.dm-liquid-bg');
        liquidBgs.forEach(function (bg) { bg.remove(); });

        var constellations = document.querySelectorAll('.dm-constellation');
        constellations.forEach(function (c) { c.remove(); });
    }

    /* ============================================================
       INIT
       ============================================================ */

    function init() {
        if (state.initialized) return;
        state.initialized = true;

        detectCapabilities();

        if (state.reducedMotion) return;

        createAmbientOrbs();
        createParticles();
        createConstellation();
        initScrollReveal();
        initJoiOrb();
        initEcosystemCards();
        initParallax();
        initLiquidBackground();
    }

    /* ── Boot ── */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    /* ── Expose for WordPress dynamic loading ── */
    window.dejoiyMotion = {
        init: init,
        destroy: destroyAll,
        state: state,
    };

})();
