/**
 * DEJOIY Marketplace OS — micro-interactions (lightweight).
 */
(function () {
  'use strict';

  if (!document.querySelector('.dejoiy-os-home')) {
    return;
  }

  var cards = document.querySelectorAll('[data-dejoiy-reveal]');
  if (!cards.length || !('IntersectionObserver' in window)) {
    return;
  }

  var io = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    },
    { rootMargin: '0px 0px -40px 0px', threshold: 0.08 }
  );

  cards.forEach(function (el, i) {
    el.style.setProperty('--dejoiy-reveal-delay', String(i * 40) + 'ms');
    io.observe(el);
  });
})();
