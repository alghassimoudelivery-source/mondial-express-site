// L'agence : présentation, valeurs, mission, équipe, compagnies partenaires, histoire, témoignages.
import 'package:flutter/material.dart';

import 'core.dart';
import 'ui.dart';

IconData valueIcon(String? n) => switch (n) {
      'star' => Icons.star_outline_rounded,
      'shield' => Icons.verified_user_outlined,
      'heart' => Icons.favorite_border_rounded,
      'users' => Icons.groups_outlined,
      _ => Icons.circle_outlined,
    };

/// Bandeau horizontal des compagnies partenaires (accueil).
class PartnersStrip extends StatelessWidget {
  const PartnersStrip(this.list, {super.key});
  final List<Map<String, dynamic>> list;
  @override
  Widget build(BuildContext context) => SizedBox(
        height: 62,
        child: ListView.separated(
          scrollDirection: Axis.horizontal,
          itemCount: list.length,
          separatorBuilder: (_, _) => const SizedBox(width: 10),
          itemBuilder: (_, i) => PartnerChip(list[i], i),
        ),
      );
}

class PartnerChip extends StatelessWidget {
  const PartnerChip(this.a, this.i, {super.key});
  final Map<String, dynamic> a;
  final int i;
  @override
  Widget build(BuildContext context) {
    final c = [MeColors.violet, MeColors.orangeInk, MeColors.vertInk][i % 3];
    return Container(
      padding: const EdgeInsets.fromLTRB(8, 8, 14, 8),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), border: Border.all(color: MeColors.line)),
      child: Row(mainAxisSize: MainAxisSize.min, children: [
        Container(
          width: 40,
          height: 40,
          alignment: Alignment.center,
          decoration: BoxDecoration(color: c.withValues(alpha: .1), borderRadius: BorderRadius.circular(12)),
          child: Text(a['code'] as String? ?? '', style: TextStyle(color: c, fontWeight: FontWeight.w800, fontSize: 13)),
        ),
        const SizedBox(width: 10),
        Text(a['nom'] as String? ?? '', style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13.5)),
      ]),
    );
  }
}

Future<Map<String, dynamic>> loadPresentation() async {
  final key = 'c_${I18n.lang}_presentation';
  try {
    final d = await app.call('presentation') as Map<String, dynamic>;
    app.set(key, d);
    return d;
  } catch (e) {
    final saved = app.get<Map<String, dynamic>?>(key, null);
    if (saved != null) return saved;
    rethrow;
  }
}

class AgenceScreen extends StatefulWidget {
  const AgenceScreen({super.key});
  @override
  State<AgenceScreen> createState() => _AgenceScreenState();
}

class _AgenceScreenState extends State<AgenceScreen> {
  late Future<Map<String, dynamic>> f = loadPresentation();

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(title: Text(t('L’agence'))),
        body: FutureBuilder<Map<String, dynamic>>(
          future: f,
          builder: (context, snap) {
            if (snap.hasError) return EmptyBox(snap.error.toString(), icon: Icons.wifi_off_rounded);
            if (!snap.hasData) return const Padding(padding: EdgeInsets.all(16), child: Skeleton(height: 160, count: 4));
            final p = snap.data!;
            List<Map<String, dynamic>> l(String k) => (p[k] as List? ?? []).cast<Map<String, dynamic>>();
            final intro = (p['intro'] as List? ?? []).cast<String>();
            return ListView(padding: const EdgeInsets.all(16), children: [
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(borderRadius: BorderRadius.circular(26), gradient: const LinearGradient(colors: [MeColors.orange, MeColors.violet], begin: Alignment.topLeft, end: Alignment.bottomRight)),
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text(t('Qui nous sommes').toUpperCase(), style: TextStyle(color: Colors.white.withValues(alpha: .85), fontWeight: FontWeight.w800, fontSize: 12, letterSpacing: 1)),
                  const Gap(8),
                  if (intro.isNotEmpty) Text(intro.first, style: const TextStyle(color: Colors.white, fontSize: 15.5, height: 1.55, fontWeight: FontWeight.w600)),
                ]),
              ),
              if (intro.length > 1) Padding(padding: const EdgeInsets.fromLTRB(4, 14, 4, 0), child: Text(intro[1], style: const TextStyle(color: MeColors.muted, height: 1.55))),
              const Gap(14),
              GridView.count(
                crossAxisCount: 2,
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                mainAxisSpacing: 10,
                crossAxisSpacing: 10,
                childAspectRatio: 1.9,
                children: [
                  for (var i = 0; i < l('chiffres').length; i++)
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(color: [const Color(0xFFFDF0E3), const Color(0xFFEEF6E2), const Color(0xFFF1EBF8), const Color(0xFFFDF0E3)][i % 4], borderRadius: BorderRadius.circular(18)),
                      child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisAlignment: MainAxisAlignment.center, children: [
                        Text('${l('chiffres')[i]['valeur']}', style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: [MeColors.orangeInk, MeColors.vertInk, MeColors.violet, MeColors.orangeInk][i % 4])),
                        Text('${l('chiffres')[i]['libelle']}', maxLines: 2, style: const TextStyle(color: MeColors.muted, fontSize: 12.5)),
                      ]),
                    ),
                ],
              ),
              SectionHead(t('Nos valeurs')),
              ...l('valeurs').map((v) => Padding(
                    padding: const EdgeInsets.only(bottom: 10),
                    child: ListRow(icon: valueIcon(v['icone'] as String?), color: MeColors.named(v['couleur'] as String?), title: '${v['titre']}', subtitle: '${v['texte']}', trailing: false),
                  )),
              const Gap(12),
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(color: const Color(0xFF2A1645), borderRadius: BorderRadius.circular(24)),
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text(t('Notre mission').toUpperCase(), style: const TextStyle(color: Color(0xFFF6B77E), fontWeight: FontWeight.w800, fontSize: 12, letterSpacing: 1)),
                  const Gap(8),
                  Text('${p['mission']}', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 16, height: 1.5)),
                  const Gap(12),
                  for (var i = 0; i < l('etapes').length; i++)
                    Container(
                      width: double.infinity,
                      margin: const EdgeInsets.only(bottom: 8),
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(color: Colors.white.withValues(alpha: .08), borderRadius: BorderRadius.circular(14)),
                      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                        Text('${i + 1}. ${l('etapes')[i]['titre']}', style: const TextStyle(color: Color(0xFFF6B77E), fontWeight: FontWeight.w800)),
                        Text('${l('etapes')[i]['texte']}', style: TextStyle(color: Colors.white.withValues(alpha: .9), fontSize: 13.5)),
                      ]),
                    ),
                ]),
              ),
              const Gap(12),
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(color: const Color(0xFFFDF0E3), borderRadius: BorderRadius.circular(24)),
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text(t('Notre vision').toUpperCase(), style: const TextStyle(color: MeColors.orangeInk, fontWeight: FontWeight.w800, fontSize: 12, letterSpacing: 1)),
                  const Gap(8),
                  Text('${p['vision']}', style: const TextStyle(height: 1.55)),
                  const Gap(10),
                  Text(app.config['slogan'] as String? ?? '', style: const TextStyle(color: MeColors.orangeInk, fontWeight: FontWeight.w800, fontSize: 17)),
                ]),
              ),
              SectionHead(t('Notre équipe')),
              GridView.count(
                crossAxisCount: 2,
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                mainAxisSpacing: 10,
                crossAxisSpacing: 10,
                childAspectRatio: .66,
                children: l('equipe').map((m) => _Member(m)).toList(),
              ),
              SectionHead(t('Nos compagnies partenaires')),
              Text(t('Nous émettons vos billets sur les principales compagnies qui desservent Conakry et le monde.'), style: const TextStyle(color: MeColors.muted)),
              const Gap(12),
              Wrap(spacing: 8, runSpacing: 8, children: [for (var i = 0; i < l('partenaires').length; i++) PartnerChip(l('partenaires')[i], i)]),
              SectionHead(t('Notre histoire')),
              for (var i = 0; i < l('histoire').length; i++) _HistoryItem(l('histoire')[i], last: i == l('histoire').length - 1),
              SectionHead(t('Nos clients témoignent')),
              ...l('temoignages').map((q) => MeCard(
                    margin: const EdgeInsets.only(bottom: 12),
                    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      const Icon(Icons.format_quote_rounded, color: MeColors.orange, size: 34),
                      Text('${q['texte']}', style: const TextStyle(fontSize: 15, height: 1.55)),
                      const Gap(10),
                      Text('${q['nom']}', style: const TextStyle(fontWeight: FontWeight.w800)),
                      Text('${q['role']}', style: const TextStyle(color: MeColors.muted, fontSize: 13)),
                    ]),
                  )),
              const ConfordevCredit(),
            ]);
          },
        ),
      );
}

class _Member extends StatelessWidget {
  const _Member(this.m);
  final Map<String, dynamic> m;
  @override
  Widget build(BuildContext context) {
    final name = '${m['nom']}';
    final photo = m['photo'] as String? ?? '';
    final initials = name.split(' ').where((p) => p.isNotEmpty).take(2).map((p) => p[0]).join().toUpperCase();
    final ph = Container(
      alignment: Alignment.center,
      decoration: const BoxDecoration(gradient: LinearGradient(colors: [MeColors.orange, MeColors.violet], begin: Alignment.topLeft, end: Alignment.bottomRight)),
      child: Text(initials, style: const TextStyle(color: Colors.white, fontSize: 30, fontWeight: FontWeight.w800)),
    );
    return MeCard(
      padding: const EdgeInsets.all(10),
      child: Column(children: [
        AspectRatio(
          aspectRatio: 1,
          child: ClipRRect(
            borderRadius: BorderRadius.circular(16),
            child: photo.isEmpty ? ph : Image.network(photo, fit: BoxFit.cover, alignment: Alignment.topCenter, errorBuilder: (_, _, _) => ph),
          ),
        ),
        const Gap(8),
        Text(name, textAlign: TextAlign.center, maxLines: 2, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5, height: 1.25)),
        const SizedBox(height: 2),
        Expanded(child: Text('${m['role']}', textAlign: TextAlign.center, maxLines: 3, overflow: TextOverflow.ellipsis, style: const TextStyle(color: MeColors.muted, fontSize: 11.5, height: 1.3))),
      ]),
    );
  }
}

class _HistoryItem extends StatelessWidget {
  const _HistoryItem(this.h, {required this.last});
  final Map<String, dynamic> h;
  final bool last;
  @override
  Widget build(BuildContext context) => IntrinsicHeight(
        child: Row(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
          Column(children: [
            Container(width: 16, height: 16, margin: const EdgeInsets.only(top: 4), decoration: const BoxDecoration(color: MeColors.orange, shape: BoxShape.circle)),
            if (!last) Expanded(child: Container(width: 3, color: MeColors.line)),
          ]),
          const SizedBox(width: 14),
          Expanded(
            child: Padding(
              padding: const EdgeInsets.only(bottom: 18),
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text('${h['annee']}', style: const TextStyle(color: MeColors.orangeInk, fontWeight: FontWeight.w800, fontSize: 18)),
                const SizedBox(height: 2),
                Text('${h['texte']}', style: const TextStyle(color: MeColors.muted, height: 1.45)),
              ]),
            ),
          ),
        ]),
      );
}
