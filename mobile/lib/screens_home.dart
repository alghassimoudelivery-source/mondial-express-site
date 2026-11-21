// Accueil : recherche rapide, services, offres du moment, Hajj & Oumra, suivi, blog.
import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';

import 'core.dart';
import 'main.dart';
import 'screens_agence.dart';
import 'screens_compte.dart';
import 'screens_demande.dart';
import 'screens_offres.dart';
import 'ui.dart';

/// Écran ouvert par chaque service du site.
Widget? serviceTarget(String slug) => switch (slug) {
      'billetterie' => const DemandeFormScreen(type: 'billet'),
      'assistance-visa' => const DemandeFormScreen(type: 'visa'),
      'hotels-tourisme' => const DemandeFormScreen(type: 'hotel'),
      'hajj-oumra' => const HajjScreen(),
      'evacuation-sanitaire' => const DemandeFormScreen(type: 'evacuation'),
      'voyages-affaires' => const DemandeFormScreen(type: 'affaires'),
      _ => null,
    };

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});
  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  late Future<List<dynamic>> offres = app.list('offres');
  late Future<List<dynamic>> actus = app.list('actus');
  late Future<Map<String, dynamic>> pres = loadPresentation();
  String type = 'billet';
  final depart = TextEditingController(text: 'Conakry');
  final destination = TextEditingController();
  DateTime? date;
  String passagers = '1 adulte';

  Future<void> _refresh() async {
    setState(() {
      offres = app.list('offres', fresh: true);
      actus = app.list('actus', fresh: true);
    });
    await Future.wait([offres, actus, app.refreshConfig()]).catchError((_) => <Object>[]);
  }

  void _search() {
    if (type == 'hajj') {
      Navigator.of(context).push(MaterialPageRoute(builder: (_) => const HajjScreen()));
      return;
    }
    final d = date == null ? '' : '${date!.year}-${date!.month.toString().padLeft(2, '0')}-${date!.day.toString().padLeft(2, '0')}';
    final pre = {'depart': depart.text, 'destination': destination.text, 'aller': d, 'passagers': passagers, 'pays': destination.text, 'date': d, 'arrivee': d};
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => DemandeFormScreen(type: type, prefill: pre)));
  }

  @override
  Widget build(BuildContext context) {
    final c = app.config;
    final hour = DateTime.now().hour;
    final first = (app.user?['nom'] as String? ?? '').split(' ').first;
    final hello = '${t(hour < 18 ? 'Bonjour' : 'Bonsoir')}${first.isNotEmpty ? ' $first' : ''} 👋';
    final services = (c['services'] as List? ?? []).cast<Map<String, dynamic>>();
    final stats = (c['stats'] as List? ?? []).map((e) => '${e ?? ''}').toList();
    return Scaffold(
      appBar: AppBar(
        toolbarHeight: 64,
        title: Image.asset('assets/logo.png', height: 38),
        actions: [
          ListenableBuilder(
            listenable: app,
            builder: (_, _) => IconButton(
              tooltip: t('Notifications'),
              onPressed: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const NotificationsScreen())),
              icon: Badge(isLabelVisible: app.unread, smallSize: 9, backgroundColor: MeColors.orange, child: const Icon(Icons.notifications_none_rounded)),
            ),
          ),
          Padding(
            padding: const EdgeInsetsDirectional.only(end: 10),
            child: IconButton.filled(
              style: IconButton.styleFrom(backgroundColor: MeColors.wa),
              tooltip: 'WhatsApp',
              onPressed: () => openWhatsApp(),
              icon: const FaIcon(FontAwesomeIcons.whatsapp, color: Colors.white, size: 20),
            ),
          ),
        ],
      ),
      body: RefreshIndicator(
        color: MeColors.orange,
        onRefresh: _refresh,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 4, 16, 24),
          children: [
            // ---------- En-tête ----------
            Container(
              padding: const EdgeInsets.fromLTRB(20, 20, 20, 24),
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(26),
                gradient: const LinearGradient(colors: [Color(0xFFFFF1E4), Color(0xFFF3ECFA)], begin: Alignment.topLeft, end: Alignment.bottomRight),
              ),
              child: Row(children: [
                Expanded(
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Text(hello, style: const TextStyle(color: MeColors.muted, fontWeight: FontWeight.w600)),
                    const Gap(6),
                    Text.rich(
                      TextSpan(style: const TextStyle(fontSize: 26, fontWeight: FontWeight.w800, height: 1.15), children: [
                        TextSpan(text: '${t('Où partez-vous')} '),
                        TextSpan(text: t('aujourd’hui'), style: const TextStyle(color: MeColors.orange)),
                        const TextSpan(text: ' ?'),
                      ]),
                    ),
                  ]),
                ),
                Transform.rotate(angle: I18n.rtl ? -0.5 : 0.5, child: const Icon(Icons.flight_rounded, size: 52, color: MeColors.orange)),
              ]),
            ),
            const Gap(14),
            // ---------- Recherche rapide ----------
            MeCard(
              child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      ('billet', Icons.flight_takeoff_rounded, 'Billet'),
                      ('visa', Icons.badge_outlined, 'Visa'),
                      ('hotel', Icons.hotel_outlined, 'Hôtel'),
                      ('hajj', Icons.mosque_outlined, 'Hajj & Oumra'),
                    ]
                        .map((x) => Padding(
                              padding: const EdgeInsetsDirectional.only(end: 8),
                              child: ChoiceChip(
                                selected: type == x.$1,
                                onSelected: (_) => setState(() => type = x.$1),
                                avatar: Icon(x.$2, size: 18, color: type == x.$1 ? MeColors.orangeInk : MeColors.muted),
                                label: Text(t(x.$3)),
                                showCheckmark: false,
                                selectedColor: const Color(0xFFFFE9D6),
                                side: BorderSide(color: type == x.$1 ? MeColors.orange : MeColors.line),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                                labelStyle: TextStyle(fontWeight: FontWeight.w700, color: type == x.$1 ? MeColors.orangeInk : MeColors.ink),
                              ),
                            ))
                        .toList(),
                  ),
                ),
                const Gap(14),
                Row(children: [
                  Expanded(child: TextField(controller: depart, decoration: InputDecoration(labelText: t('De')))),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 8),
                    child: CircleAvatar(radius: 17, backgroundColor: MeColors.orange, child: Icon(I18n.rtl ? Icons.arrow_back : Icons.arrow_forward, color: Colors.white, size: 18)),
                  ),
                  Expanded(child: TextField(controller: destination, decoration: InputDecoration(labelText: t('Vers'), hintText: 'Dubaï'))),
                ]),
                const Gap(12),
                Row(children: [
                  Expanded(
                    child: InkWell(
                      borderRadius: BorderRadius.circular(14),
                      onTap: () async {
                        final now = DateTime.now();
                        final d = await showDatePicker(context: context, firstDate: now, lastDate: now.add(const Duration(days: 730)), initialDate: date ?? now);
                        if (d != null) setState(() => date = d);
                      },
                      child: InputDecorator(
                        decoration: InputDecoration(labelText: t('Date de départ'), suffixIcon: const Icon(Icons.calendar_month_outlined)),
                        child: Text(date == null ? '—' : MaterialLocalizations.of(context).formatShortDate(date!)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: DropdownButtonFormField<String>(
                      initialValue: passagers,
                      isExpanded: true,
                      decoration: InputDecoration(labelText: t('Passagers')),
                      items: ['1 adulte', '2 adultes', '3 adultes', 'Famille avec enfants', 'Groupe'].map((p) => DropdownMenuItem(value: p, child: Text(t(p), overflow: TextOverflow.ellipsis))).toList(),
                      onChanged: (v) => setState(() => passagers = v ?? passagers),
                    ),
                  ),
                ]),
                const Gap(14),
                FilledButton.icon(onPressed: _search, icon: const Icon(Icons.search), label: Text(t('Demander les meilleurs prix'))),
              ]),
            ),
            // ---------- Services ----------
            SectionHead(t('Nos services'), action: t('Tout voir'), onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const ServicesScreen()))),
            GridView.count(
              crossAxisCount: 3,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              mainAxisSpacing: 10,
              crossAxisSpacing: 10,
              childAspectRatio: .8,
              children: services
                  .map((s) => Material(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(20),
                        child: InkWell(
                          borderRadius: BorderRadius.circular(20),
                          onTap: () {
                            final w = serviceTarget(s['slug'] as String? ?? '');
                            if (w != null) Navigator.of(context).push(MaterialPageRoute(builder: (_) => w));
                          },
                          child: Padding(
                            padding: const EdgeInsets.all(8),
                            child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                              IconBadge(serviceIcon(s['icone'] as String?), color: MeColors.named(s['couleur'] as String?), size: 46),
                              const Gap(8),
                              Text(s['titre'] as String? ?? '', textAlign: TextAlign.center, maxLines: 2, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, height: 1.2)),
                            ]),
                          ),
                        ),
                      ))
                  .toList(),
            ),
            // ---------- Offres ----------
            SectionHead(t('Offres du moment'), action: t('Toutes'), onTap: () => goTab(context, 1)),
            SizedBox(
              height: 232,
              child: FutureBuilder<List<dynamic>>(
                future: offres,
                builder: (context, snap) {
                  if (snap.hasError) return EmptyBox(t('Offres indisponibles hors connexion.'));
                  if (!snap.hasData) return const Skeleton(height: 220, count: 1);
                  final all = snap.data!.cast<Map<String, dynamic>>();
                  final v = all.where((o) => o['vedette'] == true).toList();
                  final list = (v.isEmpty ? all : v).take(6).toList();
                  return ListView.separated(
                    scrollDirection: Axis.horizontal,
                    itemCount: list.length,
                    separatorBuilder: (_, _) => const SizedBox(width: 12),
                    itemBuilder: (_, i) => SizedBox(width: 220, child: OfferCard(list[i])),
                  );
                },
              ),
            ),
            const Gap(18),
            _Banner(
              icon: Icons.mosque_outlined,
              title: t('Hajj & Oumra 2027'),
              text: t('Forfaits accompagnés au départ de Conakry'),
              color: MeColors.violet,
              onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const HajjScreen())),
            ),
            const Gap(12),
            _Banner(icon: Icons.folder_open_rounded, title: t('Suivez votre dossier'), text: t('Visa, billet : chaque étape en temps réel'), color: MeColors.vert, onTap: () => goTab(context, 3)),
            if (stats.where((s) => s.isNotEmpty).isNotEmpty) ...[
              const Gap(18),
              MeCard(
                padding: const EdgeInsets.symmetric(vertical: 18, horizontal: 8),
                child: Row(
                  children: [
                    for (var i = 0; i < 3 && i < stats.length; i++)
                      Expanded(
                        child: Column(children: [
                          Text(stats[i], style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: MeColors.orangeInk)),
                          Text(t(['voyageurs', 'destinations', 'visas obtenus'][i]), style: const TextStyle(color: MeColors.muted, fontSize: 12.5)),
                        ]),
                      ),
                  ],
                ),
              ),
            ],
            // ---------- Compagnies partenaires ----------
            SectionHead(t('Nos compagnies partenaires'), action: t('L’agence'), onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const AgenceScreen()))),
            FutureBuilder<Map<String, dynamic>>(
              future: pres,
              builder: (context, snap) => snap.hasData ? PartnersStrip((snap.data!['partenaires'] as List? ?? []).cast<Map<String, dynamic>>()) : const SizedBox(height: 62),
            ),
            const Gap(12),
            _Banner(icon: Icons.groups_outlined, title: t('Découvrir l’agence'), text: t('Notre histoire, nos valeurs et notre équipe'), color: MeColors.orange, onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const AgenceScreen()))),
            // ---------- Blog ----------
            SectionHead('#MondialTrip', action: t('Le blog'), onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const ActusScreen()))),
            FutureBuilder<List<dynamic>>(
              future: actus,
              builder: (context, snap) {
                if (snap.hasError) return const SizedBox.shrink();
                if (!snap.hasData) return const Skeleton(height: 88, count: 2);
                return Column(children: snap.data!.take(3).map((a) => NewsTile(a as Map<String, dynamic>)).toList());
              },
            ),
            const Gap(10),
            Center(child: Text(c['slogan'] as String? ?? '', style: const TextStyle(color: MeColors.muted, fontStyle: FontStyle.italic))),
          ],
        ),
      ),
    );
  }
}

class _Banner extends StatelessWidget {
  const _Banner({required this.icon, required this.title, required this.text, required this.color, required this.onTap});
  final IconData icon;
  final String title, text;
  final Color color;
  final VoidCallback onTap;
  @override
  Widget build(BuildContext context) => Material(
        borderRadius: BorderRadius.circular(22),
        color: color,
        child: InkWell(
          borderRadius: BorderRadius.circular(22),
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsets.all(18),
            child: Row(children: [
              Container(
                width: 50,
                height: 50,
                decoration: BoxDecoration(color: Colors.white.withValues(alpha: .18), borderRadius: BorderRadius.circular(16)),
                child: Icon(icon, color: Colors.white, size: 28),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text(title, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 17)),
                  const Gap(2),
                  Text(text, style: TextStyle(color: Colors.white.withValues(alpha: .9), fontSize: 13)),
                ]),
              ),
              Icon(I18n.rtl ? Icons.arrow_back : Icons.arrow_forward, color: Colors.white),
            ]),
          ),
        ),
      );
}
