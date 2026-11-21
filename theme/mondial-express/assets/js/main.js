/* Mondial Express – interactions (menu mobile, onglets de recherche, partage). */
(function () {
	'use strict';
	var L = {
		fr: { open: 'Ouvrir le menu', close: 'Fermer le menu', sub: 'Afficher le sous-menu', copied: 'Lien copié' },
		en: { open: 'Open the menu', close: 'Close the menu', sub: 'Show submenu', copied: 'Link copied' },
		es: { open: 'Abrir el menú', close: 'Cerrar el menú', sub: 'Mostrar el submenú', copied: 'Enlace copiado' },
		ar: { open: 'فتح القائمة', close: 'إغلاق القائمة', sub: 'عرض القائمة الفرعية', copied: 'تم نسخ الرابط' }
	}[(document.documentElement.lang || 'fr').slice(0, 2)] || null;
	if (!L) { L = { open: 'Ouvrir le menu', close: 'Fermer le menu', sub: 'Afficher le sous-menu', copied: 'Lien copié' }; }
	// Sélecteur de langue.
	document.querySelectorAll('.lang').forEach(function (w) {
		var btn = w.querySelector('.lang__btn');
		btn.addEventListener('click', function (e) {
			e.stopPropagation();
			var o = w.classList.toggle('is-open');
			btn.setAttribute('aria-expanded', String(o));
		});
		document.addEventListener('click', function (e) { if (!w.contains(e.target)) { w.classList.remove('is-open'); btn.setAttribute('aria-expanded', 'false'); } });
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && w.classList.contains('is-open')) { w.classList.remove('is-open'); btn.setAttribute('aria-expanded', 'false'); btn.focus(); } });
	});
	var burger = document.querySelector('.burger');
	var nav = document.getElementById('menu-principal');
	if (burger && nav) {
		burger.addEventListener('click', function () {
			var open = burger.getAttribute('aria-expanded') === 'true';
			var hd = document.querySelector('.site-header'); if (hd) { document.documentElement.style.setProperty('--nav-top', hd.getBoundingClientRect().bottom + 'px'); }
			burger.setAttribute('aria-expanded', String(!open));
			burger.setAttribute('aria-label', open ? L.open : L.close);
			document.body.classList.toggle('menu-open', !open);
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && document.body.classList.contains('menu-open')) { burger.click(); burger.focus(); }
		});
	}
	// Sous-menus sur mobile.
	document.querySelectorAll('.nav .menu-item-has-children > a').forEach(function (a) {
		var b = document.createElement('button');
		b.type = 'button'; b.className = 'submenu-toggle'; b.setAttribute('aria-expanded', 'false'); b.setAttribute('aria-label', L.sub);
		a.after(b);
		b.addEventListener('click', function () {
			var o = b.getAttribute('aria-expanded') === 'true';
			b.setAttribute('aria-expanded', String(!o)); b.parentElement.classList.toggle('is-open', !o);
		});
	});
	// Onglets de la recherche.
	var tabs = document.querySelectorAll('.search__tabs [role="tab"]');
	function select(t) {
		tabs.forEach(function (x) {
			var on = x === t;
			x.setAttribute('aria-selected', String(on)); x.tabIndex = on ? 0 : -1;
			document.getElementById(x.getAttribute('aria-controls')).hidden = !on;
		});
	}
	tabs.forEach(function (t, i) {
		t.addEventListener('click', function () { select(t); });
		t.addEventListener('keydown', function (e) {
			var n = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0;
			if (n) { var nt = tabs[(i + n + tabs.length) % tabs.length]; select(nt); nt.focus(); }
		});
	});
	// Copier le lien.
	document.querySelectorAll('.share__copy').forEach(function (b) {
		b.addEventListener('click', function () {
			var url = b.getAttribute('data-url');
			var done = function () { b.classList.add('is-copied'); b.setAttribute('aria-label', L.copied); setTimeout(function () { b.classList.remove('is-copied'); }, 2000); };
			if (navigator.clipboard) { navigator.clipboard.writeText(url).then(done, function () { window.prompt('Copiez le lien :', url); }); }
			else { window.prompt('Copiez le lien :', url); }
		});
	});
	// Boutons réseaux sociaux pas encore renseignés.
	document.querySelectorAll('[data-a-completer]').forEach(function (a) {
		a.addEventListener('click', function (e) { e.preventDefault(); });
	});
	// Nom du fichier choisi.
	document.querySelectorAll('.upload__drop input[type=file]').forEach(function (i) {
		i.addEventListener('change', function () {
			var s = i.parentElement.querySelector('span');
			if (i.files[0]) { s.textContent = i.files[0].name; }
		});
	});
	// En-tête compact au défilement.
	var h = document.querySelector('.site-header');
	if (h) {
		var onScroll = function () { h.classList.toggle('is-scrolled', window.scrollY > 40); };
		window.addEventListener('scroll', onScroll, { passive: true }); onScroll();
	}
})();
