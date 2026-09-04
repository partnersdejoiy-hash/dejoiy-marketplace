(function () {
	'use strict';

	var cols = document.querySelectorAll('nav.dgf__col');
	if (!cols.length) {
		return;
	}

	var first = true;
	cols.forEach(function (col) {
		var title = col.querySelector('.dgf__title');
		if (!title) {
			return;
		}
		if (first) {
			col.classList.add('is-open');
			title.setAttribute('aria-expanded', 'true');
			first = false;
		} else {
			title.setAttribute('aria-expanded', 'false');
		}
		title.addEventListener('click', function () {
			var open = col.classList.toggle('is-open');
			title.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	});
})();