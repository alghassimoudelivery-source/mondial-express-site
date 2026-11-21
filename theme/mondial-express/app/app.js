/* Application Mobile Mondial Express Guinée — ConforDev (https://confordev.com) */
(() => {
	'use strict';
	const ME = window.ME;
	const C = ME.config;
	const I = window.MEI18N || { lang: 'fr', t: (s) => s, locale: 'fr-FR', langs: { fr: 'Français' }, setLang() {} };
	const t = I.t;
	const $ = (s, r = document) => r.querySelector(s);
	const $$ = (s, r = document) => [...r.querySelectorAll(s)];
	const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
	const extra = {
		home: '<path fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" d="M3 11l9-7 9 7v9a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1z"/>',
		tag: '<g fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M3 12V4a1 1 0 0 1 1-1h8l9 9-9 9z"/><circle cx="8" cy="8" r="1.6"/></g>',
		plus: '<path fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" d="M12 5v14M5 12h14"/>',
		back: '<path fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" d="M15 5l-7 7 7 7"/>',
		logout: '<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M15 4h4a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1h-4M10 8l-4 4 4 4M6 12h10"/>',
		news: '<g fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8h10M7 12h10M7 16h6"/></g>',
		info: '<g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7.5v.5"/></g>',
		download: '<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 4v12M7 11l5 5 5-5M4 20h16"/>',
	};
	const ico = (n, cls = '') => {
		if (ME.icones[n]) return cls ? ME.icones[n].replace('class="ico"', `class="ico ${cls}"`) : ME.icones[n];
		return extra[n] ? `<svg class="ico ${cls}" viewBox="0 0 24 24" aria-hidden="true">${extra[n]}</svg>` : '';
	};
	const sky = {
		orange: 'M0 70V50h20V38h14v12h12V30h10v20h16V44h10V20l4-18 4 18v24h12V34h18v16h14V26h16v24h20V40h12v10h18V30l8-6 8 6v20h14V44h20v26z',
		bleu: 'M0 70V48h30V40h26v8h34V44h22v-6l12-30 2-8 2 8 12 30v6h4v-6h24v10h30V36h26v12h30V42h46v28z',
		violet: 'M0 70V50h24V12h4v38h18V38a26 26 0 0 1 52 0v12h18V12h4v38h40V42h22v8h22V18h4v32h20V40h30v10h42v20z',
		vert: 'M0 70V52h40V24h6v28h30V40h22v-8h30v8h40V8l3-8 3 8v44h26V30h6v22h50V44h44v26z',
	};
	const skySvg = (v) => `<svg class="sky" viewBox="0 0 300 70" preserveAspectRatio="none" aria-hidden="true"><path fill="currentColor" d="${sky[v] || sky.orange}"/></svg>`;
	const wa = (msg = t('Bonjour Mondial Express, ')) => `https://wa.me/${C.whatsapp}?text=${encodeURIComponent(msg)}`;
	const store = {
		get(k, d) { try { const v = localStorage.getItem('me_' + k); return v ? JSON.parse(v) : d; } catch (e) { return d; } },
		set(k, v) { try { localStorage.setItem('me_' + k, JSON.stringify(v)); } catch (e) { /* stockage indisponible */ } },
	};
	let nonce = ME.nonce;
	let user = ME.utilisateur;

	/* ---------- API ---------- */
	async function api(path, opts = {}) {
		const headers = opts.body instanceof FormData ? {} : { 'Content-Type': 'application/json' };
		if (nonce) headers['X-WP-Nonce'] = nonce;
		const res = await fetch(ME.api + path + (path.includes('?') ? '&' : '?') + 'lang=' + I.lang, {
			method: opts.method || 'GET', credentials: 'same-origin', headers,
			body: opts.body ? (opts.body instanceof FormData ? opts.body : JSON.stringify(opts.body)) : undefined,
		});
		const data = await res.json().catch(() => ({}));
		if (res.status === 403 && data.code === 'rest_cookie_invalid_nonce') { nonce = ''; return api(path, opts); }
		if (!res.ok) throw new Error(data.message || t('Connexion impossible. Vérifiez votre réseau.'));
		return data;
	}
	const cache = {};
	async function load(name) {
		if (cache[name]) return cache[name];
		const key = 'c_' + I.lang + '_' + name;
		const saved = store.get(key);
		const fresh = api(name).then((d) => { cache[name] = d; store.set(key, d); return d; });
		if (saved) { fresh.catch(() => {}); return saved; }
		return fresh;
	}

	/* ---------- Interface ---------- */
	const view = $('#view');
	const top = $('#topbar');
	function toast(msg) { const t = $('#toast'); t.textContent = msg; t.classList.add('is-on'); clearTimeout(toast.t); toast.t = setTimeout(() => t.classList.remove('is-on'), 2800); }
	function header(title, opts = {}) {
		top.className = 'topbar' + (opts.home ? ' is-home' : '');
		const bell = `<a class="iconbtn" href="#/notifications" aria-label="Notifications">${ico('bell')}${unread() ? '<span class="dot"></span>' : ''}</a>`;
		if (opts.home) {
			top.innerHTML = `<img class="topbar__logo" src="${esc(ME.logo)}" alt="Mondial Express"><span class="topbar__sp"></span>${bell}<a class="iconbtn iconbtn--wa" href="${wa()}" target="_blank" rel="noopener" aria-label="WhatsApp">${ico('whatsapp')}</a>`;
		} else {
			top.innerHTML = `${opts.back !== false ? `<button class="iconbtn back" type="button" aria-label="Retour" data-back>${ico('back')}</button>` : ''}<span class="topbar__title">${esc(title)}</span>${bell}`;
		}
	}
	function tabs(active) {
		const t = [['', 'home', 'Accueil'], ['offres', 'tag', 'Offres'], ['demande', 'plus', 'Demande', true], ['suivi', 'folder', 'Suivi'], ['compte', 'user', 'Compte']];
		$('#tabbar').innerHTML = t.map(([r, i, l, main]) => main
			? `<a class="tab tab--main${active === r ? ' is-on' : ''}" href="#/${r}" aria-label="${l}"><span class="fab">${ico(i)}</span>${l}</a>`
			: `<a class="tab${active === r ? ' is-on' : ''}" href="#/${r}"${active === r ? ' aria-current="page"' : ''}>${ico(i)}${l}</a>`).join('');
	}
	const price = (o) => o.prix ? `<span class="price">${esc(o.prix)}</span>${o.indicatif ? ' <span class="note">prix indicatif</span>' : ''}` : '';
	const vis = (o) => `<div class="vis vis--${esc(o.visuel)}">${o.image ? `<img src="${esc(o.image)}" alt="" loading="lazy">` : skySvg(o.visuel)}<b>${esc(o.titre)}${o.code ? `<small>${esc(o.code)}</small>` : ''}</b></div>`;
	const offerCard = (o, full) => `<a class="offer${full ? ' offer--full' : ''}" href="#/offre/${o.id}">${vis(o)}<div class="offer__body"><p>${esc(o.libelle)} ${t('dès')}</p>${price(o)}${full && o.pays ? `<p>${esc(o.pays)}</p>` : ''}</div></a>`;
	const skeleton = (h = 120, n = 3) => Array.from({ length: n }, () => `<div class="skel" style="height:${h}px;margin-bottom:12px"></div>`).join('');
	const svcRoute = { billetterie: 'demande/billet', 'assistance-visa': 'demande/visa', 'hotels-tourisme': 'demande/hotel', 'hajj-oumra': 'hajj', 'evacuation-sanitaire': 'demande/evacuation', 'voyages-affaires': 'demande/affaires' };

	/* ---------- Écrans ---------- */
	const screens = {};

	screens[''] = async () => {
		header('', { home: true }); tabs('');
		const hour = new Date().getHours();
		const hello = t(hour < 18 ? 'Bonjour' : 'Bonsoir') + (user ? ' ' + user.nom.split(' ')[0] : '');
		view.innerHTML = `
		<section class="hero"><p class="hero__hello">${esc(hello)} 👋</p><h1>Où partez-vous <em>aujourd’hui</em> ?</h1>${ico('plane', 'hero__plane')}</section>
		<form class="search" id="qs" autocomplete="off">
			<div class="chips" role="tablist">${[['billet', 'plane', 'Billet'], ['visa', 'pass', 'Visa'], ['hotel', 'bed', 'Hôtel'], ['hajj', 'kaaba', 'Hajj & Oumra']].map(([k, i, l], n) => `<button type="button" role="tab" class="chip${n ? '' : ' is-on'}" data-k="${k}" aria-selected="${!n}">${ico(i)}${l}</button>`).join('')}</div>
			<div class="route"><label class="route__end"><small>De</small><input name="depart" value="Conakry" aria-label="Départ"></label><span class="route__sw">${ico('arrow')}</span><label class="route__end"><small>Vers</small><input name="destination" placeholder="Dubaï" aria-label="Destination"></label></div>
			<div class="row2"><label class="field"><span>Date de départ</span><input class="input" type="date" name="aller"></label><label class="field"><span>Passagers</span><select class="input" name="passagers"><option>1 adulte</option><option>2 adultes</option><option>3 adultes</option><option>Famille avec enfants</option><option>Groupe</option></select></label></div>
			<button class="btn" type="submit">${ico('search')} Demander les meilleurs prix</button>
		</form>
		${installBanner()}
		<section class="section"><div class="section__head"><h2>Nos services</h2><a href="#/services">Tout voir</a></div>
			<div class="services">${C.services.map((s) => `<a class="svc" href="#/${svcRoute[s.slug] || 'demande'}"><span class="svc__ic c-${s.couleur}">${ico(s.icone)}</span>${esc(s.titre)}</a>`).join('')}</div></section>
		<section class="section"><div class="section__head"><h2>Offres du moment</h2><a href="#/offres">Toutes</a></div><div class="hscroll" id="h-offres">${skeleton(180, 1)}</div></section>
		<section class="section"><a class="banner" href="#/hajj"><span class="banner__ic">${ico('kaaba')}</span><span><h3>Hajj & Oumra 2027</h3><p>Forfaits accompagnés au départ de Conakry</p></span>${ico('arrow', 'ico-arrow')}</a></section>
		<section class="section"><a class="banner banner--green" href="#/suivi"><span class="banner__ic">${ico('folder')}</span><span><h3>Suivez votre dossier</h3><p>Visa, billet : chaque étape en temps réel</p></span>${ico('arrow', 'ico-arrow')}</a></section>
		<section class="section"><div class="stats"><div><b>${esc(C.stats[0])}</b><span>voyageurs</span></div><div><b>${esc(C.stats[1])}</b><span>destinations</span></div><div><b>${esc(C.stats[2])}</b><span>visas obtenus</span></div></div></section>
		<section class="section"><div class="section__head"><h2>#MondialTrip</h2><a href="#/actus">Le blog</a></div><div id="h-actus">${skeleton(90, 2)}</div></section>
		<p class="credit">${esc(C.slogan)}</p>`;
		let type = 'billet';
		$$('.chip', view).forEach((b) => b.addEventListener('click', () => { $$('.chip', view).forEach((x) => { x.classList.toggle('is-on', x === b); x.setAttribute('aria-selected', x === b); }); type = b.dataset.k; }));
		$('#qs').addEventListener('submit', (e) => {
			e.preventDefault();
			const f = new FormData(e.target);
			if (type === 'hajj') { location.hash = '#/hajj'; return; }
			const pre = { depart: f.get('depart'), destination: f.get('destination'), aller: f.get('aller'), passagers: f.get('passagers'), pays: f.get('destination'), date: f.get('aller'), arrivee: f.get('aller') };
			store.set('prefill', pre); location.hash = '#/demande/' + type;
		});
		bindInstall();
		load('offres').then((o) => { const el = $('#h-offres'); if (el) el.innerHTML = (o.filter((x) => x.vedette).length ? o.filter((x) => x.vedette) : o).slice(0, 6).map((x) => offerCard(x)).join(''); }).catch(() => { const el = $('#h-offres'); if (el) el.innerHTML = '<p class="empty">Offres indisponibles hors connexion.</p>'; });
		load('actus').then((a) => { const el = $('#h-actus'); if (el) el.innerHTML = a.slice(0, 3).map(newsItem).join(''); }).catch(() => {});
	};

	const newsItem = (a) => `<a class="news" href="#/actu/${a.id}">${a.image ? `<img src="${esc(a.image)}" alt="" loading="lazy">` : '<span class="ph skel"></span>'}<span><small>${esc(a.categorie)}</small><h3>${esc(a.titre)}</h3></span></a>`;

	screens.offres = async () => {
		header('Offres & destinations', { back: false }); tabs('offres');
		view.innerHTML = `<div class="pad"><p style="margin:0 0 14px;color:var(--muted)">Billets au départ de Conakry. Prix donnés à titre indicatif : demandez votre devis.</p><div id="l">${skeleton(112, 4)}</div></div>`;
		try { const o = await load('offres'); $('#l').innerHTML = o.map((x) => offerCard(x, true)).join('<div style="height:12px"></div>'); } catch (e) { $('#l').innerHTML = `<p class="empty">${esc(e.message)}</p>`; }
	};

	screens.offre = async (id) => {
		header('Destination'); tabs('offres');
		const o = (await load('offres')).find((x) => String(x.id) === id);
		if (!o) { view.innerHTML = '<p class="empty">Offre introuvable.</p>'; return; }
		view.innerHTML = `<div class="vis vis--${esc(o.visuel)}" style="height:210px;padding:18px">${o.image ? `<img src="${esc(o.image)}" alt="">` : skySvg(o.visuel)}<b style="font-size:30px">${esc(o.titre)}<small>${esc(o.code)}</small></b></div>
		<div class="pad stack"><div class="card"><p style="margin:0">${esc(o.libelle)} ${t('depuis Conakry dès')}</p>${price(o)}<div class="article"><div class="content">${o.contenu}</div></div></div>
		<a class="btn" href="#/demande/billet" data-dest="${esc(o.titre)}">${ico('plane')} Demander ce billet</a>
		<a class="btn btn--wa" href="${wa(t('Bonjour Mondial Express, je suis intéressé(e) par l’offre') + ' ' + o.titre + ' (' + (o.prix || '') + ').')}" target="_blank" rel="noopener">${ico('whatsapp')} Réserver sur WhatsApp</a>
		<button class="btn btn--ghost" type="button" data-share="${esc(o.lien)}" data-title="${esc(o.titre)}">${ico('share')} Partager l’offre</button></div>`;
		$('[data-dest]', view).addEventListener('click', (e) => store.set('prefill', { destination: e.currentTarget.dataset.dest, depart: 'Conakry (CKY)' }));
	};

	screens.demande = async (type) => {
		const forms = C.demandes.filter((f) => f.type !== 'contact');
		if (!type) {
			header('Nouvelle demande', { back: false }); tabs('demande');
			const ic = { billet: 'plane', visa: 'pass', hotel: 'bed', hajj: 'kaaba', evacuation: 'med', affaires: 'case' };
			const col = { billet: 'orange', visa: 'vert', hotel: 'violet', hajj: 'orange', evacuation: 'vert', affaires: 'violet' };
			view.innerHTML = `<div class="pad"><h2 style="font-size:22px;margin:4px 0 6px">Que souhaitez-vous ?</h2><p style="color:var(--muted);margin:0 0 16px">Un conseiller vous répond rapidement, par téléphone ou WhatsApp.</p>
			<div class="list">${forms.map((f) => `<a href="#/demande/${f.type}"><span class="svc__ic c-${col[f.type] || 'orange'}">${ico(ic[f.type] || 'plane')}</span><span>${esc(f.libelle)}</span>${ico('arrow', 'ico-arrow')}</a>`).join('')}</div></div>`;
			return;
		}
		const f = C.demandes.find((x) => x.type === type) || C.demandes[0];
		header(f.libelle); tabs('demande');
		const pre = store.get('prefill', {}); store.set('prefill', {});
		const fieldHtml = (c) => {
			const v = pre[c.cle] || c.valeur || '';
			const input = c.type === 'select'
				? `<select class="input" name="c_${c.cle}">${c.options.map((o) => `<option${o === v ? ' selected' : ''}>${esc(o)}</option>`).join('')}</select>`
				: `<input class="input" type="${c.type === 'number' ? 'number' : c.type === 'date' ? 'date' : 'text'}" name="c_${c.cle}" value="${esc(v)}"${c.type === 'number' ? ' min="1" inputmode="numeric"' : ''}>`;
			return `<label class="field"><span>${esc(c.libelle)}</span>${input}</label>`;
		};
		const u = user || {};
		view.innerHTML = `<div class="pad"><form class="card" id="df" novalidate>
			<h2>${esc(f.libelle)}</h2><p>Remplissez l’essentiel, nous complétons avec vous.</p>
			<div id="derr"></div>
			<div class="row2">${f.champs.map(fieldHtml).join('').replace(/<label class="field">/g, '<label class="field">')}</div>
			<label class="field"><span>Nom complet *</span><input class="input" name="nom" autocomplete="name" required value="${esc(u.nom || store.get('nom', ''))}"></label>
			<label class="field"><span>Téléphone / WhatsApp *</span><input class="input" name="telephone" type="tel" inputmode="tel" autocomplete="tel" placeholder="+224 6xx xx xx xx" required value="${esc(u.telephone || store.get('tel', ''))}"></label>
			<label class="field"><span>E-mail (facultatif)</span><input class="input" name="email" type="email" autocomplete="email" value="${esc(u.email || '')}"></label>
			${type === 'visa' || type === 'hajj' || type === 'evacuation' ? `<label class="file">${ico('upload')}<span><span data-fn>Ajouter une photo du passeport</span><small>Photo ou PDF, 8 Mo maximum (facultatif)</small></span><input type="file" name="piece" accept="image/*,application/pdf" capture="environment"></label><div style="height:12px"></div>` : ''}
			<label class="field"><span>Précisions (facultatif)</span><textarea class="input" name="message"></textarea></label>
			<input class="hp" name="site_web" tabindex="-1" autocomplete="off" aria-hidden="true">
			<button class="btn" type="submit">${ico('arrow')} Envoyer ma demande</button>
			<p class="legal">Vos informations servent uniquement à traiter votre demande.</p>
		</form></div>`;
		// Les champs longs prennent toute la largeur.
		$$('#df .row2 .field', view).forEach((l) => { const sel = $('select', l); const txt = $('input[type=text]', l); if ((sel && [...sel.options].some((o) => o.text.length > 12)) || (txt && l.textContent.length > 18)) l.style.gridColumn = '1 / -1'; });
		const rest = $$('#df .row2 .field', view).filter((l) => !l.style.gridColumn);
		if (rest.length % 2) rest[rest.length - 1].style.gridColumn = '1 / -1';
		const fi = $('input[type=file]', view);
		if (fi) fi.addEventListener('change', () => { $('[data-fn]', view).textContent = fi.files[0] ? fi.files[0].name : 'Ajouter une photo du passeport'; });
		$('#df').addEventListener('submit', async (e) => {
			e.preventDefault();
			const fd = new FormData(e.target);
			const btn = $('button[type=submit]', e.target);
			if (!fd.get('nom').trim() || fd.get('telephone').replace(/\D/g, '').length < 6) { $('#derr').innerHTML = '<p class="err">Indiquez votre nom et un numéro de téléphone valide.</p>'; return; }
			const champs = {};
			for (const [k, v] of fd.entries()) if (k.startsWith('c_') && v) champs[k.slice(2)] = v;
			const body = new FormData();
			['nom', 'telephone', 'email', 'message', 'site_web'].forEach((k) => body.append(k, fd.get(k) || ''));
			body.append('type', f.type); body.append('champs', JSON.stringify(champs));
			if (fi && fi.files[0]) body.append('piece', fi.files[0]);
			btn.disabled = true; btn.textContent = 'Envoi…';
			try {
				const r = await api('demande', { method: 'POST', body });
				store.set('nom', fd.get('nom')); store.set('tel', fd.get('telephone'));
				const summary = f.libelle + '\n' + Object.entries(champs).map(([k, v]) => `${(f.champs.find((c) => c.cle === k) || {}).libelle || k} : ${v}`).join('\n');
				addNotif('Demande envoyée', `${f.libelle} — référence ${r.ref}. Un conseiller vous contacte rapidement.`);
				view.innerHTML = `<div class="pad"><div class="card done"><div class="done__ic">${ico('check')}</div><h2>Demande envoyée !</h2><p>${t('Merci')} ${esc(fd.get('nom').split(' ')[0])}. ${t('Un conseiller Mondial Express vous contacte très vite au')} ${esc(fd.get('telephone'))}.</p>
				<div class="stack"><a class="btn btn--wa" href="${wa(t('Bonjour Mondial Express, je viens d’envoyer une demande depuis l’application (réf.') + ' ' + r.ref + ').\n' + summary)}" target="_blank" rel="noopener">${ico('whatsapp')} Envoyer aussi sur WhatsApp</a><a class="btn btn--ghost" href="#/">Retour à l’accueil</a></div></div></div>`;
			} catch (err) {
				btn.disabled = false; btn.innerHTML = `${ico('arrow')} Envoyer ma demande`;
				$('#derr').innerHTML = `<p class="err">${esc(err.message)}</p>`;
			}
		});
	};

	const payChips = () => (C.paiements || []).map((p) => `<span class="${esc(p.cle)}">${esc(p.nom)}</span>`).join('');
	const payBlock = () => (C.paiements || []).length ? `<div class="section__head"><h2>Paiements acceptés</h2></div><div class="card"><div class="pay__m" style="margin:0">${payChips()}</div>${C.paiement_note ? `<p style="margin:10px 0 0;font-size:13px;color:var(--muted)">${esc(C.paiement_note)}</p>` : ''}</div>` : '';
	function dossierHtml(d) {
		const pct = Math.round(((d.position + 1) / d.etapes.length) * 100);
		const pay = d.paiement === 'solde' ? `<div class="pay"><b>${ico('card')} Solde à régler</b><div class="pay__m">${payChips()}</div><a class="btn btn--sm" href="${wa(t('Bonjour, je souhaite régler le solde du dossier') + ' ' + d.numero)}" target="_blank" rel="noopener">Payer mon solde</a></div>` : d.paiement === 'paye' ? '<p class="info info--v">Paiement reçu. Merci !</p>' : '';
		return `<article class="dossier"><div class="dossier__top"><div><small>Dossier</small><strong>${esc(d.numero)}</strong></div><span class="pill${d.etape === 'termine' ? '' : ' pill--o'}">${esc(d.type)}</span></div>
		<h3>${esc(d.titre)}</h3><div class="progress" role="progressbar" aria-valuenow="${pct}" aria-valuemin="0" aria-valuemax="100"><span style="width:${pct}%"></span></div>
		<ol class="tl">${d.etapes.map((s, i) => `<li class="${i < d.position || (i === d.position && s.cle === 'termine') ? 'is-done' : i === d.position ? 'is-now' : ''}"><i>${i < d.position || (i === d.position && s.cle === 'termine') ? ico('check') : ''}</i>${esc(s.libelle)}</li>`).join('')}</ol>
		${d.rdv ? `<p class="info">${ico('clock')} ${esc(d.rdv)}</p>` : ''}${d.message ? `<p class="info info--v">${ico('info')} ${esc(d.message)}</p>` : ''}${pay}
		${d.documents && d.documents.length ? `<ul class="docs">${d.documents.map((x) => `<li>${ico('folder')} ${esc(x.titre)} <span style="margin-left:auto;color:var(--muted)">${esc(x.date)}</span></li>`).join('')}</ul>` : ''}</article>`;
	}

	screens.suivi = async () => {
		header('Suivi de dossier', { back: false }); tabs('suivi');
		const saved = store.get('suivis', []);
		view.innerHTML = `<div class="pad"><form class="card" id="sf"><h2>Où en est mon dossier ?</h2><p>Numéro inscrit sur votre reçu et les 4 derniers chiffres de votre téléphone.</p><div id="serr"></div>
			<label class="field"><span>Numéro de dossier</span><input class="input" name="numero" placeholder="ME-2611-2418" autocapitalize="characters" required></label>
			<label class="field"><span>4 derniers chiffres du téléphone</span><input class="input" name="tel" inputmode="numeric" maxlength="4" placeholder="3970" required></label>
			<button class="btn" type="submit">${ico('search')} Voir mon dossier</button></form>
			<div id="sres" style="margin-top:14px"></div>
			${user && user.dossiers.length ? `<div class="section__head"><h2>Mes dossiers</h2></div>${user.dossiers.map(dossierHtml).join('')}` : ''}
			<div id="ssaved"></div></div>`;
		const renderSaved = async () => {
			const list = store.get('suivis', []);
			if (!list.length) return;
			const box = $('#ssaved'); if (!box) return;
			box.innerHTML = '<div class="section__head"><h2>Dossiers suivis</h2></div>' + skeleton(200, 1);
			const out = [];
			for (const s of list) { try { out.push(dossierHtml(await api('suivi', { method: 'POST', body: s }))); } catch (e) { /* dossier retiré */ } }
			if ($('#ssaved')) box.innerHTML = '<div class="section__head"><h2>Dossiers suivis</h2></div>' + out.join('');
		};
		$('#sf').addEventListener('submit', async (e) => {
			e.preventDefault();
			const q = { numero: e.target.numero.value.trim().toUpperCase(), tel: e.target.tel.value.trim() };
			$('#serr').innerHTML = ''; $('#sres').innerHTML = skeleton(240, 1);
			try {
				const d = await api('suivi', { method: 'POST', body: q });
				$('#sres').innerHTML = dossierHtml(d) + `<button class="btn btn--ghost" type="button" id="save">${ico('bell')} Suivre ce dossier dans l’application</button>`;
				$('#save').addEventListener('click', () => { const l = store.get('suivis', []).filter((x) => x.numero !== q.numero); l.push(q); store.set('suivis', l); remember(d); toast('Dossier ajouté : vous serez prévenu à chaque étape.'); askNotif(); });
			} catch (err) { $('#sres').innerHTML = ''; $('#serr').innerHTML = `<p class="err">${esc(err.message)}</p>`; }
		});
		if (saved.length) renderSaved();
	};

	screens.hajj = async () => {
		header('Hajj & Oumra'); tabs('');
		view.innerHTML = `<div class="pad"><a class="banner" style="margin-bottom:14px" href="#/demande/hajj"><span class="banner__ic">${ico('kaaba')}</span><span><h3>Pré-inscription 2027</h3><p>Places limitées, inscrivez-vous tôt</p></span>${ico('arrow', 'ico-arrow')}</a><div id="fl">${skeleton(200, 2)}</div></div>`;
		try {
			const f = await load('forfaits');
			$('#fl').innerHTML = f.map((x) => `<article class="forfait"><small>${esc(x.type === 'hajj' ? 'Hajj' : 'Oumra')} · ${esc(x.dates)}</small><h3>${esc(x.titre)}</h3>${price(x)}<ul>${x.inclus.map((i) => `<li>${ico('check')}${esc(i)}</li>`).join('')}</ul><a class="btn btn--sm" href="#/demande/hajj" data-forfait="${esc(x.titre)}">Je m’inscris</a></article>`).join('');
			$$('[data-forfait]', view).forEach((a) => a.addEventListener('click', () => store.set('prefill', { forfait: a.dataset.forfait })));
		} catch (e) { $('#fl').innerHTML = `<p class="empty">${esc(e.message)}</p>`; }
	};

	screens.services = async () => {
		header('Nos services'); tabs('');
		view.innerHTML = `<div class="pad"><div class="list">${C.services.map((s) => `<a href="#/${svcRoute[s.slug] || 'demande'}"><span class="svc__ic c-${s.couleur}">${ico(s.icone)}</span><span>${esc(s.titre)}<small>${esc(s.texte)}</small></span>${ico('arrow', 'ico-arrow')}</a>`).join('')}</div></div>`;
	};

	screens.actus = async () => {
		header('Blog #MondialTrip'); tabs('');
		view.innerHTML = `<div class="pad" id="al">${skeleton(90, 4)}</div>`;
		try { $('#al').innerHTML = (await load('actus')).map(newsItem).join(''); } catch (e) { $('#al').innerHTML = `<p class="empty">${esc(e.message)}</p>`; }
	};

	screens.actu = async (id) => {
		header('Article'); tabs('');
		const a = (await load('actus')).find((x) => String(x.id) === id);
		if (!a) { view.innerHTML = '<p class="empty">Article introuvable.</p>'; return; }
		view.innerHTML = `<article class="pad article">${a.image ? `<img class="cover" src="${esc(a.image)}" alt="">` : ''}<small style="color:var(--orange-ink);font-weight:700;text-transform:uppercase;font-size:12px">${esc(a.categorie)} · ${esc(a.date)}</small><h1>${esc(a.titre)}</h1><div class="content">${a.contenu}</div>
		<div class="stack" style="margin-top:18px"><button class="btn btn--ghost" type="button" data-share="${esc(a.lien)}" data-title="${esc(a.titre)}">${ico('share')} Partager l’article</button></div></article>`;
		// Les liens internes s'ouvrent dans le navigateur.
		$$('.content a', view).forEach((l) => { l.target = '_blank'; l.rel = 'noopener'; });
	};

	screens.compte = async () => {
		header('Mon compte', { back: false }); tabs('compte');
		if (!user) {
			view.innerHTML = `<div class="pad"><div class="seg" role="tablist"><button type="button" class="is-on" data-m="in">Se connecter</button><button type="button" data-m="up">Créer un compte</button></div>
			<form class="card" id="af"><div id="aerr"></div><div data-up hidden><label class="field"><span>Nom complet</span><input class="input" name="nom" autocomplete="name"></label><label class="field"><span>Téléphone / WhatsApp</span><input class="input" name="telephone" type="tel" autocomplete="tel"></label></div>
			<label class="field"><span data-l>E-mail ou identifiant</span><input class="input" name="identifiant" autocomplete="username" required></label>
			<label class="field"><span>Mot de passe</span><input class="input" name="mot_de_passe" type="password" autocomplete="current-password" required></label>
			<button class="btn" type="submit">Se connecter</button><p class="legal" data-in><a href="${esc(C.site)}wp-login.php?action=lostpassword" target="_blank" rel="noopener">Mot de passe oublié ?</a></p></form>
			<div style="height:14px"></div>${contactBlock()}${langBlock()}</div>`;
			let mode = 'in';
			$$('.seg button', view).forEach((b) => b.addEventListener('click', () => {
				mode = b.dataset.m; $$('.seg button', view).forEach((x) => x.classList.toggle('is-on', x === b));
				$('[data-up]', view).hidden = mode !== 'up'; $('[data-in]', view).hidden = mode === 'up';
				$('[data-l]', view).textContent = mode === 'up' ? 'E-mail' : 'E-mail ou identifiant';
				$('#af button[type=submit]').textContent = mode === 'up' ? 'Créer mon compte' : 'Se connecter';
				$('#af [name=mot_de_passe]').autocomplete = mode === 'up' ? 'new-password' : 'current-password';
			}));
			$('#af').addEventListener('submit', async (e) => {
				e.preventDefault(); const f = e.target; const btn = $('button[type=submit]', f); btn.disabled = true;
				try {
					const r = mode === 'up'
						? await api('inscription', { method: 'POST', body: { nom: f.nom.value, telephone: f.telephone.value, email: f.identifiant.value, mot_de_passe: f.mot_de_passe.value } })
						: await api('connexion', { method: 'POST', body: { identifiant: f.identifiant.value, mot_de_passe: f.mot_de_passe.value } });
					nonce = r.nonce; user = r.utilisateur; (user.dossiers || []).forEach(remember);
					toast(mode === 'up' ? 'Bienvenue ! Votre compte est créé.' : 'Vous êtes connecté.'); screens.compte();
				} catch (err) { btn.disabled = false; $('#aerr').innerHTML = `<p class="err">${esc(err.message)}</p>`; }
			});
			return;
		}
		const initials = user.nom.split(' ').map((p) => p[0]).slice(0, 2).join('').toUpperCase();
		view.innerHTML = `<div class="pad stack"><div class="profile"><span class="avatar">${esc(initials)}</span><span><b style="font:800 18px var(--title)">${esc(user.nom)}</b><br><span style="opacity:.9;font-size:13px">${esc(user.email)}</span></span></div>
			<div class="section__head"><h2>Mes dossiers</h2></div>
			${user.dossiers.length ? user.dossiers.map((d) => dossierHtml(d) + `<form class="card" data-doc="${d.id}"><h2 style="font-size:16px">Envoyer un document</h2><p>Passeport, photo, relevé… (PDF, JPG, PNG — 8 Mo)</p><label class="field"><span>Nom du document</span><input class="input" name="libelle" placeholder="Passeport"></label><label class="file">${ico('upload')}<span><span data-fn>Choisir ou prendre une photo</span><small>Depuis votre téléphone</small></span><input type="file" name="fichier" accept="image/*,application/pdf" required></label><div style="height:12px"></div><button class="btn btn--sm" type="submit">${ico('upload')} Envoyer</button></form>`).join('') : `<div class="card empty">${ico('folder')}Aucun dossier pour le moment.<br>Vos dossiers apparaissent ici dès que l’agence les ouvre.<div style="height:12px"></div><a class="btn btn--sm" href="#/demande">Faire une demande</a></div>`}
			<div class="list">
				<button type="button" data-notif><span class="svc__ic c-orange">${ico('bell')}</span><span>Notifications<small>${'Notification' in window && Notification.permission === 'granted' ? 'Activées' : 'Être prévenu à chaque étape'}</small></span>${ico('arrow', 'ico-arrow')}</button>
				<a href="#/contact"><span class="svc__ic c-vert">${ico('phone')}</span><span>Contacter l’agence</span>${ico('arrow', 'ico-arrow')}</a>
				<button type="button" data-out><span class="svc__ic c-violet">${ico('logout')}</span><span>Se déconnecter</span></button></div>
			${langBlock()}<p class="credit">Application réalisée par <a href="https://confordev.com" target="_blank" rel="noopener">ConforDev</a></p></div>`;
		$$('[data-doc]', view).forEach((form) => {
			const fi = $('input[type=file]', form);
			fi.addEventListener('change', () => { $('[data-fn]', form).textContent = fi.files[0] ? fi.files[0].name : 'Choisir ou prendre une photo'; });
			form.addEventListener('submit', async (e) => {
				e.preventDefault(); if (!fi.files[0]) { toast('Choisissez un fichier.'); return; }
				const b = new FormData(form); b.append('dossier', form.dataset.doc);
				const btn = $('button', form); btn.disabled = true; btn.textContent = 'Envoi…';
				try { await api('document', { method: 'POST', body: b }); user = await api('moi'); toast('Document envoyé à l’agence.'); screens.compte(); } catch (err) { btn.disabled = false; btn.textContent = 'Envoyer'; toast(err.message); }
			});
		});
		$('[data-notif]', view).addEventListener('click', askNotif);
		$('[data-out]', view).addEventListener('click', async () => { try { await api('deconnexion', { method: 'POST' }); } catch (e) { /* déjà déconnecté */ } user = null; nonce = ''; toast('Vous êtes déconnecté.'); screens.compte(); });
	};

	const langBlock = () => `<div class="section__head"><h2>${t('Langue')}</h2></div><div class="seg seg--lang">${Object.entries(I.langs).map(([k, v]) => `<button type="button" lang="${k}" data-lang="${k}"${k === I.lang ? ' class="is-on" aria-pressed="true"' : ''}>${esc(v)}</button>`).join('')}</div>`;
	function contactBlock() {
		return `<div class="list"><a href="${wa()}" target="_blank" rel="noopener"><span class="svc__ic c-vert">${ico('whatsapp')}</span><span>WhatsApp<small>${esc(C.telephone)}</small></span>${ico('arrow', 'ico-arrow')}</a>
		<a href="tel:${esc(C.telephone.replace(/\s/g, ''))}"><span class="svc__ic c-orange">${ico('phone')}</span><span>Appeler l’agence<small>${esc(C.horaires)}</small></span>${ico('arrow', 'ico-arrow')}</a>
		${C.email ? `<a href="mailto:${esc(C.email)}"><span class="svc__ic c-violet">${ico('mail')}</span><span>E-mail<small>${esc(C.email)}</small></span>${ico('arrow', 'ico-arrow')}</a>` : ''}
		<a href="${esc(C.site)}contact/" target="_blank" rel="noopener"><span class="svc__ic c-violet">${ico('pin')}</span><span>Nous trouver<small>${esc(C.adresse)}</small></span>${ico('arrow', 'ico-arrow')}</a></div>`;
	}

	screens.contact = async () => {
		header('Contact'); tabs('compte');
		view.innerHTML = `<div class="pad">${contactBlock()}${payBlock()}${langBlock()}<div class="section__head"><h2>Suivez-nous</h2></div>
		<div class="socials">${C.reseaux.map((r) => `<a ${r.url ? `href="${esc(r.url)}" target="_blank" rel="noopener"` : 'aria-disabled="true"'} aria-label="${esc(r.nom)}">${ico(r.cle)}</a>`).join('')}<a href="${wa()}" target="_blank" rel="noopener" aria-label="WhatsApp">${ico('whatsapp')}</a></div>
		<p class="credit">${esc(C.slogan)}<br>Application réalisée par <a href="https://confordev.com" target="_blank" rel="noopener">ConforDev</a></p></div>`;
	};

	/* ---------- Notifications d'étape ---------- */
	function remember(d) { const s = store.get('etapes', {}); s[d.numero] = d.etape; store.set('etapes', s); }
	function unread() { return store.get('notifs', []).some((n) => !n.lu); }
	function addNotif(titre, texte) {
		const l = store.get('notifs', []); l.unshift({ titre, texte, date: new Date().toLocaleString(I.locale, { dateStyle: 'short', timeStyle: 'short' }), lu: false }); store.set('notifs', l.slice(0, 30));
		if ('Notification' in window && Notification.permission === 'granted' && navigator.serviceWorker) navigator.serviceWorker.ready.then((r) => r.showNotification(titre, { body: texte, icon: ME.base.replace('/app/', '/assets/img/android-chrome-192.png'), badge: ME.base.replace('/app/', '/assets/img/android-chrome-192.png') })).catch(() => {});
	}
	async function askNotif() {
		if (!('Notification' in window)) { toast('Notifications non disponibles sur cet appareil.'); return; }
		const p = await Notification.requestPermission();
		toast(p === 'granted' ? 'Notifications activées.' : 'Notifications refusées dans les réglages.');
	}
	async function checkUpdates() {
		const known = store.get('etapes', {});
		if (user) { try { user = await api('moi'); } catch (e) { /* hors ligne ou session expirée */ } }
		const list = user ? [...user.dossiers] : [];
		for (const s of store.get('suivis', [])) { try { list.push(await api('suivi', { method: 'POST', body: s })); } catch (e) { /* hors ligne */ } }
		list.forEach((d) => {
			if (known[d.numero] && known[d.numero] !== d.etape) {
				const lib = (d.etapes.find((x) => x.cle === d.etape) || {}).libelle || d.etape;
				addNotif(t('Dossier') + ' ' + d.numero, t('Nouvelle étape :') + ' ' + lib + (d.rdv ? ' — ' + d.rdv : ''));
			}
			remember(d);
		});
	}
	screens.notifications = async () => {
		header('Notifications'); tabs('');
		const l = store.get('notifs', []);
		view.innerHTML = `<div class="pad">${l.length ? l.map((n) => `<div class="notif"><span class="svc__ic c-orange">${ico('bell')}</span><span><b>${esc(n.titre)}</b><span>${esc(n.texte)}</span><br><span style="font-size:11px">${esc(n.date)}</span></span></div>`).join('') : `<div class="empty">${ico('bell')}Aucune notification.<br>Suivez un dossier pour être prévenu à chaque étape.</div>`}</div>`;
		store.set('notifs', l.map((n) => ({ ...n, lu: true })));
	};

	/* ---------- Installation ---------- */
	let deferred = null;
	window.addEventListener('beforeinstallprompt', (e) => { e.preventDefault(); deferred = e; const b = $('#install'); if (b) b.hidden = false; });
	const standalone = () => matchMedia('(display-mode: standalone)').matches || navigator.standalone;
	const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
	function installBanner() {
		if (standalone()) return '';
		return `<div class="install" id="install"${isIos || deferred ? '' : ' hidden'}><img src="${esc(ME.base.replace('/app/', '/assets/img/android-chrome-192.png'))}" alt=""><span style="flex:1"><b>Installer l’application</b><span>${isIos ? 'Touchez Partager puis « Sur l’écran d’accueil »' : 'Accès direct depuis votre écran d’accueil'}</span></span>${isIos ? '' : `<button class="btn btn--sm" type="button" data-install>${ico('download')} Installer</button>`}</div>`;
	}
	function bindInstall() {
		const b = $('[data-install]'); if (!b) return;
		b.addEventListener('click', async () => { if (!deferred) return; deferred.prompt(); await deferred.userChoice; deferred = null; $('#install').hidden = true; });
	}

	/* ---------- Routeur ---------- */
	async function route() {
		const [name = '', arg] = location.hash.replace(/^#\/?/, '').split('/');
		const fn = screens[name] || screens[''];
		try { await fn(arg); } catch (e) { view.innerHTML = `<p class="empty">${esc(e.message)}</p>`; }
		window.scrollTo(0, 0); view.focus({ preventScroll: true });
	}
	document.addEventListener('click', async (e) => {
		const lb = e.target.closest('[data-lang]');
		if (lb && lb.dataset.lang !== I.lang) { I.setLang(lb.dataset.lang); return; }
		if (e.target.closest('[data-back]')) { if (history.length > 1) history.back(); else location.hash = '#/'; }
		const s = e.target.closest('[data-share]');
		if (s) {
			const data = { title: s.dataset.title, text: s.dataset.title + ' — Mondial Express', url: s.dataset.share };
			if (navigator.share) navigator.share(data).catch(() => {});
			else { try { await navigator.clipboard.writeText(data.url); toast('Lien copié.'); } catch (err) { window.open(wa(data.title + ' ' + data.url), '_blank'); } }
		}
	});
	window.addEventListener('hashchange', route);
	(async () => {
		if (I.lang !== 'fr') {
			const k = 'c_' + I.lang + '_config';
			const saved = store.get(k);
			const fresh = api('config').then((d) => { store.set(k, d); return d; }).catch(() => null);
			const cfg = saved || await fresh;
			if (cfg) Object.assign(C, cfg);
		}
		route();
	})();
	setTimeout(() => $('#splash').classList.add('is-off'), 700);
	if ('serviceWorker' in navigator) navigator.serviceWorker.register(ME.home + 'sw.js', { scope: ME.home }).catch(() => {});
	checkUpdates().then(() => { if (!location.hash.includes('notifications')) { const b = $('.topbar .iconbtn[href="#/notifications"]'); if (b && unread() && !$('.dot', b)) b.insertAdjacentHTML('beforeend', '<span class="dot"></span>'); } });
	document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'visible') checkUpdates(); });
})();
