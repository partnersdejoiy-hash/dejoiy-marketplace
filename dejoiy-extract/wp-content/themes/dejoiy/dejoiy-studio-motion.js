/**
 * DEJOIY Custom Studio — Elementor 3D motion layer (all viewports).
 */
(function () {
  'use strict';

  if (!document.body.classList.contains('elementor-page-4399')) {
    return;
  }

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var selectors =
    '.dejoiy-studio-stats .elementor-widget, .dejoiy-studio-features .elementor-widget-icon-box, .dejoiy-studio-process .e-con, .dejoiy-studio-cta .elementor-widget, .dejoiy-studio-products .product';

  function initTilt() {
    if (reduceMotion) return;
    document.querySelectorAll(selectors).forEach(function (el) {
      if (el.dataset.djTilt === '1') return;
      el.dataset.djTilt = '1';
      el.classList.add('dejoiy-studio-tilt');
      el.addEventListener('pointermove', onMove);
      el.addEventListener('pointerleave', onLeave);
    });
  }

  function onMove(e) {
    var el = e.currentTarget;
    var r = el.getBoundingClientRect();
    var x = (e.clientX - r.left) / r.width - 0.5;
    var y = (e.clientY - r.top) / r.height - 0.5;
    var rx = (-y * 10).toFixed(2);
    var ry = (x * 12).toFixed(2);
    el.classList.add('is-hover');
    el.style.transform =
      'perspective(1200px) rotateX(' + rx + 'deg) rotateY(' + ry + 'deg) translateZ(8px)';
  }

  function onLeave(e) {
    var el = e.currentTarget;
    el.classList.remove('is-hover');
    el.style.transform = '';
  }

  function initParallax() {
    if (reduceMotion || !window.gsap || !window.ScrollTrigger) return;
    window.gsap.registerPlugin(window.ScrollTrigger);
    var hero = document.querySelector('.dejoiy-studio-hero');
    if (hero) {
      window.gsap.to(hero, {
        yPercent: 8,
        ease: 'none',
        scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: true },
      });
    }
  }

  function loadGsap(cb) {
    if (window.gsap && window.ScrollTrigger) {
      cb();
      return;
    }
    var s1 = document.createElement('script');
    s1.src = 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js';
    s1.onload = function () {
      var s2 = document.createElement('script');
      s2.src = 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js';
      s2.onload = cb;
      document.head.appendChild(s2);
    };
    document.head.appendChild(s1);
  }

  function boot() {
    initTilt();
    loadGsap(initParallax);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }

  window.addEventListener('resize', initTilt);
})();
