(function(){
	var n = document.getElementById('djNav-a7f40fe2');
	var s = document.getElementById('djScroll-a7f40fe2');
	var o = document.getElementById('djOverlay-a7f40fe2');
	if (!n) return;

	/* Swipe hint on first visit */
	if (!sessionStorage.getItem('dj_h_a7f40fe2')) {
		setTimeout(function(){
			s.classList.add('show-hint');
			sessionStorage.setItem('dj_h_a7f40fe2', '1');
		}, 800);
		s.addEventListener('animationend', function(){
			s.classList.remove('show-hint');
		});
	}

	/* Restore active state */
	var l = localStorage.getItem('dj_a_a7f40fe2');
	if (l) {
		var e = n.querySelector('[data-nav="' + l + '"]');
		if (e) e.classList.add('active');
	}

	/* Card click handler */
	var cards = n.querySelectorAll('.dejoiy-nav-card-a7f40fe2');
	cards.forEach(function(c){
		c.addEventListener('click', function(ev){
			ev.preventDefault();
			var h = c.getAttribute('data-href');
			var v = c.getAttribute('data-nav');
			if (!h) return;

			localStorage.setItem('dj_a_a7f40fe2', v);

			/* Remove active from all, add to clicked */
			cards.forEach(function(x){ x.classList.remove('active'); x.classList.remove('anim-active'); });
			c.classList.add('active');
			c.classList.add('anim-active');

			/* Show overlay and navigate */
			if (o) {
				o.classList.add('active');
			}
			setTimeout(function(){
				window.location.href = h;
			}, 1800);
		});

		/* Re-trigger animation on repeated taps without navigation */
		c.addEventListener('touchstart', function(){
			c.classList.remove('anim-active');
			void c.offsetWidth;
			c.classList.add('anim-active');
		}, {passive: true});
	});
})();
