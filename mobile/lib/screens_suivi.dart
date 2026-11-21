// Suivi de dossier : recherche par numéro + 4 derniers chiffres, dossiers suivis, dossiers du compte.
import 'package:flutter/material.dart';

import 'core.dart';
import 'ui.dart';

class DossierCard extends StatelessWidget {
  const DossierCard(this.d, {super.key, this.footer});
  final Map<String, dynamic> d;
  final Widget? footer;
  @override
  Widget build(BuildContext context) {
    final steps = (d['etapes'] as List? ?? []).cast<Map<String, dynamic>>();
    final pos = (d['position'] as num? ?? 0).toInt();
    final pct = steps.isEmpty ? 0.0 : (pos + 1) / steps.length;
    final finished = d['etape'] == 'termine';
    final pays = (app.config['paiements'] as List? ?? []).cast<Map<String, dynamic>>();
    return MeCard(
      margin: const EdgeInsets.only(bottom: 14),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          Expanded(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(t('Dossier').toUpperCase(), style: const TextStyle(color: MeColors.muted, fontSize: 11, fontWeight: FontWeight.w700)),
              Text(d['numero'] as String? ?? '', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, letterSpacing: .5)),
            ]),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(color: (finished ? MeColors.vert : MeColors.orange).withValues(alpha: .12), borderRadius: BorderRadius.circular(30)),
            child: Text(d['type'] as String? ?? '', style: TextStyle(color: finished ? MeColors.vertInk : MeColors.orangeInk, fontWeight: FontWeight.w700, fontSize: 12)),
          ),
        ]),
        const Gap(8),
        Text(d['titre'] as String? ?? '', style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
        const Gap(12),
        ClipRRect(
          borderRadius: BorderRadius.circular(10),
          child: LinearProgressIndicator(value: pct, minHeight: 8, color: MeColors.vert, backgroundColor: MeColors.line),
        ),
        const Gap(14),
        for (var i = 0; i < steps.length; i++) _step(steps[i], i, pos, i == steps.length - 1),
        if ((d['rdv'] ?? '') != '') _info(Icons.schedule, d['rdv'] as String, MeColors.orange),
        if ((d['message'] ?? '') != '') _info(Icons.info_outline, d['message'] as String, MeColors.vert),
        if (d['paiement'] == 'solde')
          Container(
            margin: const EdgeInsets.only(top: 10),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(color: const Color(0xFFFFF4EA), borderRadius: BorderRadius.circular(16)),
            child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
              Row(children: [const Icon(Icons.credit_card, color: MeColors.orangeInk), const SizedBox(width: 8), Text(t('Solde à régler'), style: const TextStyle(fontWeight: FontWeight.w800))]),
              const Gap(8),
              Wrap(spacing: 6, runSpacing: 6, children: pays.map((p) => PayChip(p)).toList()),
              const Gap(10),
              WaButton(t('Payer mon solde'), text: '${t('Bonjour, je souhaite régler le solde du dossier')} ${d['numero']}'),
            ]),
          ),
        if (d['paiement'] == 'paye') _info(Icons.verified_outlined, t('Paiement reçu. Merci !'), MeColors.vert),
        if ((d['documents'] as List? ?? []).isNotEmpty) ...[
          const Gap(10),
          Text(t('Documents'), style: const TextStyle(fontWeight: FontWeight.w800)),
          ...(d['documents'] as List).cast<Map<String, dynamic>>().map((x) => Padding(
                padding: const EdgeInsets.only(top: 6),
                child: Row(children: [
                  const Icon(Icons.description_outlined, size: 18, color: MeColors.violet),
                  const SizedBox(width: 8),
                  Expanded(child: Text(x['titre'] as String? ?? '')),
                  Text(x['date'] as String? ?? '', style: const TextStyle(color: MeColors.muted, fontSize: 12)),
                ]),
              )),
        ],
        if (footer != null) ...[const Gap(12), footer!],
      ]),
    );
  }

  Widget _step(Map<String, dynamic> s, int i, int pos, bool last) {
    final done = i < pos || (i == pos && s['cle'] == 'termine');
    final now = i == pos && !done;
    final c = done ? MeColors.vert : (now ? MeColors.orange : MeColors.line);
    return IntrinsicHeight(
      child: Row(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
        Column(children: [
          Container(
            width: 22,
            height: 22,
            decoration: BoxDecoration(color: done ? c : Colors.white, shape: BoxShape.circle, border: Border.all(color: c, width: 2.5)),
            child: done ? const Icon(Icons.check, size: 14, color: Colors.white) : null,
          ),
          if (!last) Expanded(child: Container(width: 2.5, color: done ? MeColors.vert : MeColors.line)),
        ]),
        const SizedBox(width: 12),
        Expanded(
          child: Padding(
            padding: const EdgeInsets.only(bottom: 14, top: 1),
            child: Text(s['libelle'] as String? ?? '', style: TextStyle(fontWeight: now ? FontWeight.w800 : FontWeight.w500, color: now ? MeColors.orangeInk : (done ? MeColors.ink : MeColors.muted))),
          ),
        ),
      ]),
    );
  }

  Widget _info(IconData i, String text, Color c) => Container(
        width: double.infinity,
        margin: const EdgeInsets.only(top: 8),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(color: c.withValues(alpha: .1), borderRadius: BorderRadius.circular(14)),
        child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [Icon(i, color: c, size: 20), const SizedBox(width: 8), Expanded(child: Text(text, style: const TextStyle(height: 1.4)))]),
      );
}

class PayChip extends StatelessWidget {
  const PayChip(this.p, {super.key});
  final Map<String, dynamic> p;
  @override
  Widget build(BuildContext context) {
    final c = switch (p['cle']) { 'om' => MeColors.orange, 'mtn' => const Color(0xFFB98A00), 'card' => MeColors.violet, 'cash' => MeColors.vert, _ => MeColors.ink };
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(30), border: Border.all(color: c.withValues(alpha: .5))),
      child: Text(p['nom'] as String? ?? '', style: TextStyle(color: c, fontWeight: FontWeight.w700, fontSize: 12.5)),
    );
  }
}

class SuiviScreen extends StatefulWidget {
  const SuiviScreen({super.key});
  @override
  State<SuiviScreen> createState() => _SuiviScreenState();
}

class _SuiviScreenState extends State<SuiviScreen> {
  final numero = TextEditingController();
  final tel = TextEditingController();
  Map<String, dynamic>? result;
  String? error;
  bool loading = false;
  List<Map<String, dynamic>> followed = [];

  @override
  void initState() {
    super.initState();
    _loadFollowed();
  }

  Future<void> _loadFollowed() async {
    final out = <Map<String, dynamic>>[];
    for (final s in app.get<List<dynamic>>('suivis', [])) {
      try {
        out.add(await app.call('suivi', body: Map<String, dynamic>.from(s as Map)) as Map<String, dynamic>);
      } catch (_) {/* hors ligne */}
    }
    if (mounted) setState(() => followed = out);
  }

  Future<void> _search() async {
    final q = {'numero': numero.text.trim().toUpperCase(), 'tel': tel.text.trim()};
    if (q['numero']!.isEmpty || q['tel']!.length < 4) {
      setState(() => error = t('Aucun dossier ne correspond. Vérifiez le numéro et les 4 derniers chiffres du téléphone.'));
      return;
    }
    FocusScope.of(context).unfocus();
    setState(() {
      loading = true;
      error = null;
      result = null;
    });
    try {
      final d = await app.call('suivi', body: q) as Map<String, dynamic>;
      setState(() => result = {...d, '_q': q});
    } on ApiError catch (e) {
      setState(() => error = e.message);
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  void _follow() {
    final q = result!['_q'] as Map<String, String>;
    final l = app.get<List<dynamic>>('suivis', []).where((x) => (x as Map)['numero'] != q['numero']).toList()..add(q);
    app.set('suivis', l);
    app.remember(result!);
    toast(context, t('Dossier ajouté : vous serez prévenu à chaque étape.'));
    _loadFollowed();
  }

  @override
  Widget build(BuildContext context) => ListenableBuilder(
        listenable: app,
        builder: (context, _) {
          final mine = ((app.user?['dossiers'] as List?) ?? []).cast<Map<String, dynamic>>();
          return Scaffold(
            appBar: AppBar(title: Text(t('Suivi de dossier'))),
            body: RefreshIndicator(
              color: MeColors.orange,
              onRefresh: () async {
                await app.refreshUser();
                await _loadFollowed();
              },
              child: ListView(padding: const EdgeInsets.all(16), children: [
                MeCard(
                  child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
                    Text(t('Où en est mon dossier ?'), style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800)),
                    const Gap(4),
                    Text(t('Numéro inscrit sur votre reçu et les 4 derniers chiffres de votre téléphone.'), style: const TextStyle(color: MeColors.muted)),
                    const Gap(14),
                    if (error != null) ErrorBox(error!),
                    TextField(controller: numero, textCapitalization: TextCapitalization.characters, decoration: InputDecoration(labelText: t('Numéro de dossier'), hintText: 'ME-2611-2418')),
                    const Gap(12),
                    TextField(controller: tel, keyboardType: TextInputType.number, maxLength: 4, decoration: InputDecoration(labelText: t('4 derniers chiffres du téléphone'), hintText: '3970', counterText: '')),
                    const Gap(14),
                    FilledButton.icon(
                      onPressed: loading ? null : _search,
                      icon: loading ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)) : const Icon(Icons.search),
                      label: Text(t('Voir mon dossier')),
                    ),
                  ]),
                ),
                const Gap(14),
                if (result != null)
                  DossierCard(result!, footer: OutlinedButton.icon(onPressed: _follow, icon: const Icon(Icons.notifications_active_outlined), label: Text(t('Suivre ce dossier dans l’application')))),
                if (mine.isNotEmpty) ...[SectionHead(t('Mes dossiers')), ...mine.map((d) => DossierCard(d))],
                if (followed.isNotEmpty) ...[SectionHead(t('Dossiers suivis')), ...followed.map((d) => DossierCard(d))],
              ]),
            ),
          );
        },
      );
}
