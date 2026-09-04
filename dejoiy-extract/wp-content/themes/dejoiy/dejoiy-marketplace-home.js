/**
 * DEJOIY Marketplace Home — front-page interactivity.
 * Progressive enhancement; every block works without JS.
 */
(function () {
  'use strict';

  var cfg = window.dejoiyMph || { ajaxUrl: '', nonce: '' };

  function onReady(fn) {
    if (document.readyState !== 'loading') { fn(); } else { document.addEventListener('DOMContentLoaded', fn); }
  }

  /* ---------------- Hero slider ---------------- */

  function initHero() {
    var root = document.querySelector('[data-mph-hero]');
    if (!root) { return; }
    var track = root.querySelector('[data-mph-hero-track]');
    var slides = track ? Array.prototype.slice.call(track.children) : [];
    if (slides.length <= 1) { return; }

    var dotsWrap = root.querySelector('[data-mph-hero-dots]');
    var index = 0;
    var timer = null;
    var startX = 0;
    var drag = false;

    var dots = [];
    if (dotsWrap) {
      slides.forEach(function (_, i) {
        var d = document.createElement('button');
        d.type = 'button';
        d.className = 'mph-hero__dot' + (i === 0 ? ' is-active' : '');
        d.setAttribute('aria-label', 'Slide ' + (i + 1));
        d.addEventListener('click', function () { go(i); restart(); });
        dotsWrap.appendChild(d);
        dots.push(d);
      });
    }

    function go(i) {
      index = (i + slides.length) % slides.length;
      track.style.transform = 'translateX(-' + (index * 100) + '%)';
      dots.forEach(function (d, k) { d.classList.toggle('is-active', k === index); });
    }

    function next() { go(index + 1); }
    function restart() {
      if (timer) { clearInterval(timer); }
      timer = setInterval(next, 6000);
    }

    var prevBtn = root.querySelector('[data-mph-hero-prev]');
    var nextBtn = root.querySelector('[data-mph-hero-next]');
    if (prevBtn) { prevBtn.addEventListener('click', function () { go(index - 1); restart(); }); }
    if (nextBtn) { nextBtn.addEventListener('click', function () { go(index + 1); restart(); }); }

    /* swipe */
    track.addEventListener('pointerdown', function (e) { startX = e.clientX; drag = true; });
    window.addEventListener('pointerup', function (e) {
      if (!drag) { return; }
      drag = false;
      var dx = e.clientX - startX;
      if (Math.abs(dx) > 40) { dx < 0 ? go(index + 1) : go(index - 1); restart(); }
    });

    root.addEventListener('mouseenter', function () { if (timer) { clearInterval(timer); timer = null; } });
    root.addEventListener('mouseleave', restart);

    go(0);
    restart();
  }

  /* ---------------- Product rails ---------------- */

  function initRails() {
    Array.prototype.forEach.call(document.querySelectorAll('[data-mph-rail]'), function (rail) {
      var track = rail.querySelector('[data-mph-rail-track]');
      var btnL = rail.querySelector('[data-mph-raila-l]');
      var btnR = rail.querySelector('[data-mph-raila-r]');
      if (!track) { return; }

      function step() {
        var item = track.querySelector('.mph-rail__item');
        var w = item ? item.getBoundingClientRect().width + 12 : 220;
        return Math.max(120, Math.round(w));
      }

      function update() {
        if (!btnL || !btnR) { return; }
        var max = track.scrollWidth - track.clientWidth - 4;
        btnL.disabled = track.scrollLeft <= 4;
        btnR.disabled = track.scrollLeft >= max;
      }

      if (btnL) { btnL.addEventListener('click', function () { track.scrollBy({ left: -step() * 2.5, behavior: 'smooth' }); }); }
      if (btnR) { btnR.addEventListener('click', function () { track.scrollBy({ left: step() * 2.5, behavior: 'smooth' }); }); }

      track.addEventListener('scroll', update, { passive: true });
      window.addEventListener('resize', update, { passive: true });
      update();
    });
  }

  /* ---------------- Countdown ---------------- */

  function initCountdown() {
    var root = document.querySelector('[data-mph-count]');
    if (!root) { return; }
    var h = root.querySelector('[data-mph-count-h]');
    var m = root.querySelector('[data-mph-count-m]');
    var s = root.querySelector('[data-mph-count-s]');
    if (!h || !m || !s) { return; }

    function pad(n) { return n < 10 ? '0' + n : '' + n; }

    function tick() {
      var now = new Date();
      var end = new Date(now);
      end.setHours(23, 59, 59, 999);
      var ms = Math.max(0, end - now);
      var hrs = Math.floor(ms / 3600000);
      var mins = Math.floor((ms % 3600000) / 60000);
      var secs = Math.floor((ms % 60000) / 1000);
      h.textContent = pad(hrs);
      m.textContent = pad(mins);
      s.textContent = pad(secs);
    }

    tick();
    setInterval(tick, 1000);
  }

  /* ---------------- Add to cart ---------------- */

  function updateBadges(count) {
    var label = count > 99 ? '99+' : String(count);
    var shown = count > 0;
    var sel = '[data-gh-cart-badge],[data-gh-m-cart-badge],.gh-badge,.dm-bottom__badge-slot [data-dm-badge]';
    Array.prototype.forEach.call(document.querySelectorAll(sel), function (el) {
      el.textContent = label;
      if (shown) { el.removeAttribute('hidden'); } else { el.setAttribute('hidden', ''); }
    });
    document.body.dispatchEvent(new CustomEvent('added_to_cart', { detail: { qty: count } }));
  }

  function toast(message, opts) {
    var root = document.querySelector('[data-mph-toast]');
    if (!root) { return; }
    opts = opts || {};
    root.innerHTML = '';
    if (opts.check !== false) {
      var ic = document.createElement('span');
      ic.className = 'mph-toast__icon';
      ic.textContent = '✓';
      root.appendChild(ic);
    }
    var tx = document.createElement('span');
    tx.textContent = message;
    root.appendChild(tx);
    if (opts.url) {
      var a = document.createElement('a');
      a.className = 'mph-toast__view';
      a.href = opts.url;
      a.textContent = cfg.i18n && cfg.i18n.viewCart ? cfg.i18n.viewCart : 'View Cart';
      root.appendChild(a);
    }
    root.classList.add('is-visible');
    clearTimeout(root._mph);
    root._mph = setTimeout(function () { root.classList.remove('is-visible'); }, 2600);
  }

  function initAddToCart() {
    if (!cfg.ajaxUrl || !cfg.nonce) { return; }
    Array.prototype.forEach.call(document.querySelectorAll('[data-add]'), function (btn) {
      btn.addEventListener('click', function () {
        var id = btn.getAttribute('data-add');
        if (!id || btn.classList.contains('is-loading')) { return; }

        btn.classList.add('is-loading');
        var old = btn.innerHTML;
        btn.textContent = '…';

        var body = new FormData();
        body.append('action', 'dejoiy_mph_add_to_cart');
        body.append('nonce', cfg.nonce);
        body.append('product_id', id);

        fetch(cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body })
          .then(function (r) { return r.json(); })
          .then(function (res) {
            if (!res || !res.success) {
              if (res && res.data && res.data.url) {
                window.location.href = res.data.url;
                return;
              }
              throw new Error('failed');
            }
            btn.classList.add('is-done');
            btn.textContent = '✓';
            updateBadges(parseInt(res.data.count, 10) || 0);
            toast(
              cfg.i18n && cfg.i18n.added ? cfg.i18n.added : 'Added to cart',
              { url: res.data.cartUrl || cfg.cartUrl }
            );
            setTimeout(function () {
              btn.classList.remove('is-done', 'is-loading');
              btn.innerHTML = old;
            }, 1400);
          })
          .catch(function () {
            btn.classList.remove('is-loading');
            btn.innerHTML = old;
            toast(cfg.i18n && cfg.i18n.error ? cfg.i18n.error : 'Could not add to cart', { check: false });
          });
      });
    });
  }

  onReady(function () {
    initHero();
    initRails();
    initCountdown();
    initAddToCart();
  });
})();