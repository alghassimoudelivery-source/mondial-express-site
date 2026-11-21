/* Service worker de l'application Mondial Express : hors connexion et notifications. */
const CACHE = 'me-app-' + ME_V;
const SHELL = [ME_HOME, ME_BASE + 'app.css', ME_BASE + 'i18n.js', ME_BASE + 'app.js'];
self.addEventListener('install', (e) => {
	e.waitUntil(caches.open(CACHE).then((c) => c.addAll(SHELL)).then(() => self.skipWaiting()));
});
self.addEventListener('activate', (e) => {
	e.waitUntil(caches.keys().then((k) => Promise.all(k.filter((n) => n.startsWith('me-app-') && n !== CACHE).map((n) => caches.delete(n)))).then(() => self.clients.claim()));
});
self.addEventListener('fetch', (e) => {
	const req = e.request;
	const url = new URL(req.url);
	if (req.method !== 'GET' || url.origin !== location.origin) return;
	// API : toujours le réseau (les données en cache sont gérées par l'application).
	if (url.pathname.includes('/wp-json/')) return;
	// Écran de l'application : réseau d'abord, copie en cache si hors connexion.
	if (req.mode === 'navigate') {
		e.respondWith(fetch(req).then((r) => { const c = r.clone(); caches.open(CACHE).then((x) => x.put(ME_HOME, c)); return r; }).catch(() => caches.match(ME_HOME)));
		return;
	}
	// Fichiers du thème (styles, polices, images) : cache d'abord.
	if (url.pathname.includes('/wp-content/')) {
		e.respondWith(caches.match(req).then((hit) => hit || fetch(req).then((r) => { if (r.ok) { const c = r.clone(); caches.open(CACHE).then((x) => x.put(req, c)); } return r; })));
	}
});
self.addEventListener('notificationclick', (e) => {
	e.notification.close();
	e.waitUntil(self.clients.matchAll({ type: 'window' }).then((l) => (l[0] ? l[0].focus() : self.clients.openWindow(ME_HOME + '#/suivi'))));
});
