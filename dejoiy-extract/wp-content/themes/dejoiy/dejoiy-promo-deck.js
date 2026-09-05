/**
 * DEJOIY Marketplace Promo Deck — fullscreen slide navigation.
 */
(function () {
	'use strict';

	var deck = document.querySelector('[data-dpd]');
	if (!deck) { return; }

	var track    = deck.querySelector('[data-dpd-track]');
	var slides   = Array.prototype.slice.call(deck.querySelectorAll('[data-dpd-slide]'));
	var dots     = Array.prototype.slice.call(deck.querySelectorAll('[data-dpd-dot]'));
	var countEl  = deck.querySelector('[data-dpd-count]');
	var barEl    = deck.querySelector('[data-dpd-progressbar]');
	var btnPrev  = deck.querySelector('[data-dpd-prev]');
	var btnNext  = deck.querySelector('[data-dpd-next]');
	var btnPlay  = deck.querySelector('[data-dpd-play]');
	var btnFull  = deck.querySelector('[data-dpd-full]');
	var playing  = false;
	var autoplayTimer = null;
	var cur = 0;

	/**
	 * Build a latent image in the background of a slide for a smooth
	 * cross-fade: render slide N +1 off-screen, force a layout, then jump.
	 */
	function show(n, instant) {
		var total = slides.length;
		cur = ((n % total) + total) % total;

		slides.forEach(function (s, i) {
			if (i === cur) { s.classList.add('is-active'); } else { s.classList.remove('is-active'); }
		});
		dots.forEach(function (d, i) {
			d.classList.toggle('is-on', i === cur);
		});

		if (countEl) {
			countEl.textContent = String(cur + 1).padStart(2, '0');
		}
		if (barEl && total) {
			barEl.style.width = ((cur + 1) / total * 100) + '%';
		}
	}

	function next(instant) { show(cur + 1, instant); }
	function prev(instant) { show(cur - 1, instant); }

	function startPlay() {
		playing = true;
		if (btnPlay) { btnPlay.textContent = '❚❚'; btnPlay.classList.add('is-playing'); }
		clearInterval(autoplayTimer);
		autoplayTimer = setInterval(function () { next(); }, 7000);
	}

	function stopPlay(keepIcon) {
		playing = false;
		clearInterval(autoplayTimer);
		autoplayTimer = null;
		if (btnPlay && !keepIcon) { btnPlay.textContent = '▶'; btnPlay.classList.remove('is-playing'); }
	}

	function togglePlay() {
		if (playing) { stopPlay(); } else { startPlay(); }
	}

	/* ---------- events ---------- */

	btnNext && btnNext.addEventListener('click', function () { stopPlay(); next(); });
	btnPrev && btnPrev.addEventListener('click', function () { stopPlay(); prev(); });

	dots.forEach(function (d) {
		d.addEventListener('click', function () {
			stopPlay();
			show(parseInt(d.getAttribute('data-dpd-dot'), 10));
		});
	});

	btnPlay && btnPlay.addEventListener('click', togglePlay);
	btnFull && btnFull.addEventListener('click', function () {
		if (document.fullscreenElement) {
			document.exitFullscreen();
		} else if (deck.requestFullscreen) {
			deck.requestFullscreen();
		}
	});

	document.addEventListener('keydown', function (e) {
		if (e.key === 'ArrowRight' || e.key === 'PageDown' || e.key === ' ') {
			e.preventDefault(); stopPlay(); next();
		} else if (e.key === 'ArrowLeft' || e.key === 'PageUp') {
			e.preventDefault(); stopPlay(); prev();
		} else if (e.key === 'Home') {
			e.preventDefault(); stopPlay(); show(0);
		} else if (e.key === 'End') {
			e.preventDefault(); stopPlay(); show(slides.length - 1);
		} else if (e.key === 'f' || e.key === 'F') {
			btnFull && btnFull.click();
		}
	});

	/* swipe */
	var startX = null;
	deck.addEventListener('touchstart', function (e) {
		startX = e.touches[0].clientX;
	}, { passive: true });
	deck.addEventListener('touchend', function (e) {
		if (startX === null) { return; }
		var dx = e.changedTouches[0].clientX - startX;
		if (Math.abs(dx) > 48) {
			stopPlay();
			dx < 0 ? next() : prev();
		}
		startX = null;
	}, { passive: true });

	var hashTimer = null;
	function updateHash() {
		clearTimeout(hashTimer);
		hashTimer = setTimeout(function () {
			try { history.replaceState(null, '', '#/' + (cur + 1)); } catch (e) {}
		}, 200);
	}
	window.addEventListener('hashchange', function () {
		var m = /#\/(\d+)/.exec(location.hash);
		if (m) { show(parseInt(m[1], 10) - 1); }
	});

	/* ---------- boot ---------- */
	var m = /#\/(\d+)/.exec(location.hash);
	show(m ? (parseInt(m[1], 10) - 1) : 0);
	updateHash();
	startPlay();
})();