// Offres, fiche offre, Hajj & Oumra, services, blog.
import 'package:flutter/material.dart';
import 'package:flutter_widget_from_html_core/flutter_widget_from_html_core.dart';

import 'core.dart';
import 'screens_demande.dart';
import 'screens_home.dart';
import 'ui.dart';

class OfferCard extends StatelessWidget {
  const OfferCard(this.o, {super.key, this.full = false});
  final Map<String, dynamic> o;
  final bool full;
  @override
  Widget build(BuildContext context) => Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        clipBehavior: Clip.antiAlias,
        child: InkWell(
          onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => OffreScreen(o))),
          child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
            OfferVisual(o, height: full ? 150 : 130),
            Padding(
              padding: const EdgeInsets.fromLTRB(14, 12, 14, 14),
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text('${o['libelle'] ?? ''} ${t('dès')}', style: const TextStyle(color: MeColors.muted, fontSize: 12.5)),
                const SizedBox(height: 2),
                PriceText(o),
                if (full && (o['pays'] ?? '') != '') Padding(padding: const EdgeInsets.only(top: 4), child: Text(o['pays'] as String, style: const TextStyle(color: MeColors.muted))),
              ]),
            ),
          ]),
        ),
      );
}

class OffresScreen extends StatefulWidget {
  const OffresScreen({super.key});
  @override
  State<OffresScreen> createState() => _OffresScreenState();
}

class _OffresScreenState extends State<OffresScreen> {
  late Future<List<dynamic>> f = app.list('offres');
  String q = '';
  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(title: Text(t('Offres & destinations'))),
        body: RefreshIndicator(
          color: MeColors.orange,
          onRefresh: () async {
            setState(() => f = app.list('offres', fresh: true));
            await f.catchError((_) => <dynamic>[]);
          },
          child: FutureBuilder<List<dynamic>>(
            future: f,
            builder: (context, snap) {
              return ListView(padding: const EdgeInsets.all(16), children: [
                Text(t('Billets au départ de Conakry. Prix donnés à titre indicatif : demandez votre devis.'), style: const TextStyle(color: MeColors.muted)),
                const Gap(12),
                TextField(
                  decoration: InputDecoration(prefixIcon: const Icon(Icons.search), hintText: t('Rechercher une destination')),
                  onChanged: (v) => setState(() => q = v.toLowerCase().trim()),
                ),
                const Gap(14),
                if (snap.hasError) EmptyBox(snap.error.toString(), icon: Icons.wifi_off_rounded),
                if (!snap.hasData && !snap.hasError) const Skeleton(height: 220, count: 3),
                if (snap.hasData)
                  ...snap.data!
                      .cast<Map<String, dynamic>>()
                      .where((o) => q.isEmpty || '${o['titre']} ${o['pays']} ${o['code']}'.toLowerCase().contains(q))
                      .map((o) => Padding(padding: const EdgeInsets.only(bottom: 14), child: OfferCard(o, full: true))),
              ]);
            },
          ),
        ),
      );
}

class OffreScreen extends StatelessWidget {
  const OffreScreen(this.o, {super.key});
  final Map<String, dynamic> o;
  @override
  Widget build(BuildContext context) => Scaffold(
        body: CustomScrollView(slivers: [
          SliverAppBar(
            pinned: true,
            expandedHeight: 230,
            foregroundColor: Colors.white,
            backgroundColor: MeColors.named(o['visuel'] as String?),
            flexibleSpace: FlexibleSpaceBar(background: OfferVisual(o, height: 290, big: true)),
          ),
          SliverPadding(
            padding: const EdgeInsets.all(16),
            sliver: SliverList.list(children: [
              MeCard(
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text('${o['libelle'] ?? ''} ${t('depuis Conakry dès')}', style: const TextStyle(color: MeColors.muted)),
                  const Gap(4),
                  PriceText(o, size: 26),
                  if ((o['pays'] ?? '') != '') ...[const Gap(4), Text(o['pays'] as String, style: const TextStyle(fontWeight: FontWeight.w600))],
                  const Gap(10),
                  HtmlWidget(o['contenu'] as String? ?? '', textStyle: const TextStyle(fontSize: 15, height: 1.55), onTapUrl: (u) async {
                    await openUrl(u.startsWith('/') ? '$kSite$u' : u);
                    return true;
                  }),
                ]),
              ),
              const Gap(16),
              FilledButton.icon(
                onPressed: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => DemandeFormScreen(type: 'billet', prefill: {'destination': o['titre'], 'depart': 'Conakry (CKY)'}))),
                icon: const Icon(Icons.flight_takeoff_rounded),
                label: Text(t('Demander ce billet')),
              ),
              const Gap(10),
              WaButton(t('Réserver sur WhatsApp'), text: '${t('Bonjour Mondial Express, je suis intéressé(e) par l’offre')} ${o['titre']} (${o['prix'] ?? ''}).'),
              const Gap(10),
              ShareButton(label: t('Partager l’offre'), title: o['titre'] as String? ?? '', url: o['lien'] as String? ?? kSite),
              const Gap(24),
            ]),
          ),
        ]),
      );
}

class HajjScreen extends StatefulWidget {
  const HajjScreen({super.key});
  @override
  State<HajjScreen> createState() => _HajjScreenState();
}

class _HajjScreenState extends State<HajjScreen> {
  late Future<List<dynamic>> f = app.list('forfaits');
  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(title: Text(t('Hajj & Oumra'))),
        body: FutureBuilder<List<dynamic>>(
          future: f,
          builder: (context, snap) => ListView(padding: const EdgeInsets.all(16), children: [
            Material(
              color: MeColors.violet,
              borderRadius: BorderRadius.circular(22),
              child: ListTile(
                contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 10),
                leading: const Icon(Icons.mosque_outlined, color: Colors.white, size: 34),
                title: Text(t('Pré-inscription 2027'), style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 17)),
                subtitle: Text(t('Places limitées, inscrivez-vous tôt'), style: TextStyle(color: Colors.white.withValues(alpha: .9))),
                trailing: Icon(I18n.rtl ? Icons.arrow_back : Icons.arrow_forward, color: Colors.white),
                onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const DemandeFormScreen(type: 'hajj'))),
              ),
            ),
            const Gap(16),
            if (snap.hasError) EmptyBox(snap.error.toString(), icon: Icons.wifi_off_rounded),
            if (!snap.hasData && !snap.hasError) const Skeleton(height: 220, count: 2),
            if (snap.hasData)
              ...snap.data!.cast<Map<String, dynamic>>().map((x) => MeCard(
                    margin: const EdgeInsets.only(bottom: 14),
                    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Text('${x['type'] == 'hajj' ? t('Hajj') : t('Oumra')} · ${x['dates'] ?? ''}'.toUpperCase(),
                          style: const TextStyle(color: MeColors.orangeInk, fontWeight: FontWeight.w800, fontSize: 12, letterSpacing: .6)),
                      const Gap(6),
                      Text(x['titre'] as String? ?? '', style: const TextStyle(fontSize: 19, fontWeight: FontWeight.w800)),
                      if ((x['duree'] ?? '') != '') Text(x['duree'] as String, style: const TextStyle(color: MeColors.muted)),
                      const Gap(6),
                      PriceText(x, size: 22),
                      const Gap(10),
                      ...(x['inclus'] as List? ?? []).map((i) => Padding(
                            padding: const EdgeInsets.only(bottom: 6),
                            child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
                              const Icon(Icons.check_circle, color: MeColors.vert, size: 20),
                              const SizedBox(width: 8),
                              Expanded(child: Text('$i')),
                            ]),
                          )),
                      const Gap(8),
                      FilledButton(
                        onPressed: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => DemandeFormScreen(type: 'hajj', prefill: {'forfait': x['titre']}))),
                        child: Text(t('Je m’inscris')),
                      ),
                    ]),
                  )),
          ]),
        ),
      );
}

class ServicesScreen extends StatelessWidget {
  const ServicesScreen({super.key});
  @override
  Widget build(BuildContext context) {
    final s = (app.config['services'] as List? ?? []).cast<Map<String, dynamic>>();
    return Scaffold(
      appBar: AppBar(title: Text(t('Nos services'))),
      body: ListView.separated(
        padding: const EdgeInsets.all(16),
        itemCount: s.length,
        separatorBuilder: (_, _) => const Gap(10),
        itemBuilder: (_, i) => ListRow(
          icon: serviceIcon(s[i]['icone'] as String?),
          color: MeColors.named(s[i]['couleur'] as String?),
          title: s[i]['titre'] as String? ?? '',
          subtitle: s[i]['texte'] as String?,
          onTap: () {
            final w = serviceTarget(s[i]['slug'] as String? ?? '');
            if (w != null) Navigator.of(context).push(MaterialPageRoute(builder: (_) => w));
          },
        ),
      ),
    );
  }
}

class NewsTile extends StatelessWidget {
  const NewsTile(this.a, {super.key});
  final Map<String, dynamic> a;
  @override
  Widget build(BuildContext context) {
    final img = a['image'];
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        clipBehavior: Clip.antiAlias,
        child: InkWell(
          onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => ActuScreen(a))),
          child: Row(children: [
            SizedBox(
              width: 96,
              height: 88,
              child: img is String && img.isNotEmpty ? Image.network(img, fit: BoxFit.cover, errorBuilder: (_, _, _) => Container(color: MeColors.line)) : Container(color: MeColors.line),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text((a['categorie'] as String? ?? '').toUpperCase(), style: const TextStyle(color: MeColors.orangeInk, fontWeight: FontWeight.w800, fontSize: 11)),
                const SizedBox(height: 3),
                Text(a['titre'] as String? ?? '', maxLines: 2, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w700, height: 1.25)),
              ]),
            ),
            const SizedBox(width: 10),
          ]),
        ),
      ),
    );
  }
}

class ActusScreen extends StatefulWidget {
  const ActusScreen({super.key});
  @override
  State<ActusScreen> createState() => _ActusScreenState();
}

class _ActusScreenState extends State<ActusScreen> {
  late Future<List<dynamic>> f = app.list('actus');
  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(title: Text(t('Blog #MondialTrip'))),
        body: FutureBuilder<List<dynamic>>(
          future: f,
          builder: (context, snap) => ListView(padding: const EdgeInsets.all(16), children: [
            if (snap.hasError) EmptyBox(snap.error.toString(), icon: Icons.wifi_off_rounded),
            if (!snap.hasData && !snap.hasError) const Skeleton(height: 88, count: 5),
            if (snap.hasData) ...snap.data!.map((a) => NewsTile(a as Map<String, dynamic>)),
          ]),
        ),
      );
}

class ActuScreen extends StatelessWidget {
  const ActuScreen(this.a, {super.key});
  final Map<String, dynamic> a;
  @override
  Widget build(BuildContext context) {
    final img = a['image'];
    return Scaffold(
      appBar: AppBar(title: Text(t('Article'))),
      body: ListView(padding: const EdgeInsets.all(16), children: [
        if (img is String && img.isNotEmpty) ClipRRect(borderRadius: BorderRadius.circular(20), child: AspectRatio(aspectRatio: 16 / 10, child: Image.network(img, fit: BoxFit.cover))),
        const Gap(14),
        Text('${(a['categorie'] as String? ?? '').toUpperCase()} · ${a['date'] ?? ''}', style: const TextStyle(color: MeColors.orangeInk, fontWeight: FontWeight.w800, fontSize: 12)),
        const Gap(6),
        Text(a['titre'] as String? ?? '', style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w800, height: 1.2)),
        const Gap(12),
        HtmlWidget(a['contenu'] as String? ?? '', textStyle: const TextStyle(fontSize: 15.5, height: 1.6), onTapUrl: (u) async {
          await openUrl(u.startsWith('/') ? '$kSite$u' : u);
          return true;
        }),
        const Gap(18),
        ShareButton(label: t('Partager l’article'), title: a['titre'] as String? ?? '', url: a['lien'] as String? ?? kSite),
        const Gap(24),
      ]),
    );
  }
}
