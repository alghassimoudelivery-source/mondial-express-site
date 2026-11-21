// Demandes : liste des types et formulaire dynamique (champs définis dans le back-office).
import 'dart:convert';

import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import 'core.dart';
import 'main.dart';
import 'ui.dart';

class DemandeListScreen extends StatelessWidget {
  const DemandeListScreen({super.key});
  @override
  Widget build(BuildContext context) {
    final forms = (app.config['demandes'] as List? ?? []).cast<Map<String, dynamic>>().where((f) => f['type'] != 'contact').toList();
    return Scaffold(
      appBar: AppBar(title: Text(t('Nouvelle demande'))),
      body: ListView(padding: const EdgeInsets.all(16), children: [
        Text(t('Que souhaitez-vous ?'), style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800)),
        const Gap(4),
        Text(t('Un conseiller vous répond rapidement, par téléphone ou WhatsApp.'), style: const TextStyle(color: MeColors.muted)),
        const Gap(16),
        ...forms.map((f) => Padding(
              padding: const EdgeInsets.only(bottom: 10),
              child: ListRow(
                icon: demandeIcon(f['type'] as String),
                color: demandeColor(f['type'] as String),
                title: f['libelle'] as String? ?? '',
                onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => DemandeFormScreen(type: f['type'] as String))),
              ),
            )),
      ]),
    );
  }
}

class DemandeFormScreen extends StatefulWidget {
  const DemandeFormScreen({super.key, required this.type, this.prefill = const {}});
  final String type;
  final Map<String, dynamic> prefill;
  @override
  State<DemandeFormScreen> createState() => _DemandeFormScreenState();
}

class _DemandeFormScreenState extends State<DemandeFormScreen> {
  late final Map<String, dynamic> form;
  final Map<String, TextEditingController> ctrl = {};
  final Map<String, String> selects = {};
  late final TextEditingController nom, tel, email, message;
  Piece? piece;
  String? error;
  bool sending = false;
  Map<String, dynamic>? done;

  bool get withPiece => const ['visa', 'hajj', 'evacuation'].contains(widget.type);

  @override
  void initState() {
    super.initState();
    final forms = (app.config['demandes'] as List? ?? []).cast<Map<String, dynamic>>();
    form = forms.firstWhere((f) => f['type'] == widget.type, orElse: () => forms.isNotEmpty ? forms.first : {'type': widget.type, 'libelle': '', 'champs': []});
    for (final c in (form['champs'] as List).cast<Map<String, dynamic>>()) {
      final k = c['cle'] as String;
      final v = '${widget.prefill[k] ?? c['valeur'] ?? ''}';
      if (c['type'] == 'select') {
        final opts = (c['options'] as List).cast<String>();
        selects[k] = opts.contains(v) ? v : (opts.isNotEmpty ? opts.first : '');
      } else {
        ctrl[k] = TextEditingController(text: v);
      }
    }
    final u = app.user ?? {};
    nom = TextEditingController(text: u['nom'] as String? ?? app.get<String>('nom', ''));
    tel = TextEditingController(text: (u['telephone'] as String?)?.isNotEmpty == true ? u['telephone'] as String : app.get<String>('tel', ''));
    email = TextEditingController(text: u['email'] as String? ?? '');
    message = TextEditingController();
  }

  Future<void> _pick() async {
    final choice = await showModalBottomSheet<String>(
      context: context,
      showDragHandle: true,
      builder: (c) => SafeArea(
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          ListTile(leading: const Icon(Icons.photo_camera_outlined), title: Text(t('Prendre une photo')), onTap: () => Navigator.pop(c, 'camera')),
          ListTile(leading: const Icon(Icons.photo_library_outlined), title: Text(t('Choisir dans la galerie')), onTap: () => Navigator.pop(c, 'gallery')),
          ListTile(leading: const Icon(Icons.picture_as_pdf_outlined), title: Text(t('Choisir un PDF')), onTap: () => Navigator.pop(c, 'pdf')),
        ]),
      ),
    );
    if (choice == null) return;
    final p = await pickPiece(choice);
    if (p != null) setState(() => piece = p);
  }

  Future<void> _send() async {
    if (nom.text.trim().isEmpty || tel.text.replaceAll(RegExp(r'\D'), '').length < 6) {
      setState(() => error = t('Indiquez votre nom et un numéro de téléphone valide.'));
      return;
    }
    final champs = <String, String>{};
    ctrl.forEach((k, c) {
      if (c.text.trim().isNotEmpty) champs[k] = c.text.trim();
    });
    selects.forEach((k, v) {
      if (v.isNotEmpty) champs[k] = v;
    });
    setState(() {
      sending = true;
      error = null;
    });
    try {
      final r = await app.call('demande',
          fields: {
            'type': widget.type,
            'nom': nom.text.trim(),
            'telephone': tel.text.trim(),
            'email': email.text.trim(),
            'message': message.text.trim(),
            'champs': jsonEncode(champs),
          },
          files: piece == null ? null : {'piece': piece!});
      app.set('nom', nom.text.trim());
      app.set('tel', tel.text.trim());
      app.addNotif(t('Demande envoyée'), '${form['libelle']} — ${t('référence')} ${r['ref']}. ${t('Un conseiller vous contacte rapidement.')}');
      setState(() => done = {'ref': r['ref'], 'champs': champs});
    } on ApiError catch (e) {
      setState(() => error = e.message);
    } finally {
      if (mounted) setState(() => sending = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final title = form['libelle'] as String? ?? '';
    if (done != null) return _done(title);
    final champs = (form['champs'] as List).cast<Map<String, dynamic>>();
    return Scaffold(
      appBar: AppBar(title: Text(title)),
      body: ListView(padding: const EdgeInsets.all(16), children: [
        MeCard(
          child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
            Text(title, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800)),
            const Gap(4),
            Text(t('Remplissez l’essentiel, nous complétons avec vous.'), style: const TextStyle(color: MeColors.muted)),
            const Gap(16),
            if (error != null) ErrorBox(error!),
            ...champs.map((c) => Padding(padding: const EdgeInsets.only(bottom: 12), child: _field(c))),
            TextField(controller: nom, textCapitalization: TextCapitalization.words, autofillHints: const [AutofillHints.name], decoration: InputDecoration(labelText: '${t('Nom complet')} *')),
            const Gap(12),
            TextField(controller: tel, keyboardType: TextInputType.phone, autofillHints: const [AutofillHints.telephoneNumber], decoration: InputDecoration(labelText: '${t('Téléphone / WhatsApp')} *', hintText: '+224 6xx xx xx xx')),
            const Gap(12),
            TextField(controller: email, keyboardType: TextInputType.emailAddress, autofillHints: const [AutofillHints.email], decoration: InputDecoration(labelText: t('E-mail (facultatif)'))),
            const Gap(12),
            if (withPiece) ...[
              OutlinedButton.icon(
                onPressed: _pick,
                icon: Icon(piece == null ? Icons.upload_file_outlined : Icons.check_circle, color: piece == null ? MeColors.violet : MeColors.vert),
                label: Text(piece?.name ?? t('Ajouter une photo du passeport'), overflow: TextOverflow.ellipsis),
              ),
              Padding(padding: const EdgeInsets.only(top: 4, bottom: 12), child: Text(t('Photo ou PDF, 8 Mo maximum (facultatif)'), style: const TextStyle(color: MeColors.muted, fontSize: 12))),
            ],
            TextField(controller: message, maxLines: 4, minLines: 3, decoration: InputDecoration(labelText: t('Précisions (facultatif)'), alignLabelWithHint: true)),
            const Gap(16),
            FilledButton.icon(
              onPressed: sending ? null : _send,
              icon: sending ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)) : const Icon(Icons.send_rounded),
              label: Text(sending ? t('Envoi…') : t('Envoyer ma demande')),
            ),
            const Gap(10),
            Text(t('Vos informations servent uniquement à traiter votre demande.'), textAlign: TextAlign.center, style: const TextStyle(color: MeColors.muted, fontSize: 12)),
          ]),
        ),
        const Gap(20),
      ]),
    );
  }

  Widget _field(Map<String, dynamic> c) {
    final k = c['cle'] as String;
    final label = c['libelle'] as String? ?? k;
    if (c['type'] == 'select') {
      final opts = (c['options'] as List).cast<String>();
      return DropdownButtonFormField<String>(
        initialValue: selects[k]!.isEmpty ? null : selects[k],
        isExpanded: true,
        decoration: InputDecoration(labelText: label),
        items: opts.map((o) => DropdownMenuItem(value: o, child: Text(o, overflow: TextOverflow.ellipsis))).toList(),
        onChanged: (v) => selects[k] = v ?? '',
      );
    }
    if (c['type'] == 'date') {
      return TextField(
        controller: ctrl[k],
        readOnly: true,
        decoration: InputDecoration(labelText: label, suffixIcon: const Icon(Icons.calendar_month_outlined)),
        onTap: () async {
          final now = DateTime.now();
          final d = await showDatePicker(context: context, firstDate: now.subtract(const Duration(days: 1)), lastDate: now.add(const Duration(days: 730)), initialDate: DateTime.tryParse(ctrl[k]!.text) ?? now);
          if (d != null) ctrl[k]!.text = '${d.year}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}';
        },
      );
    }
    return TextField(controller: ctrl[k], keyboardType: c['type'] == 'number' ? TextInputType.number : TextInputType.text, decoration: InputDecoration(labelText: label));
  }

  Widget _done(String title) {
    final champs = done!['champs'] as Map<String, String>;
    final labels = {for (final c in (form['champs'] as List).cast<Map<String, dynamic>>()) c['cle']: c['libelle']};
    final summary = '$title\n${champs.entries.map((e) => '${labels[e.key] ?? e.key} : ${e.value}').join('\n')}';
    return Scaffold(
      appBar: AppBar(title: Text(title)),
      body: ListView(padding: const EdgeInsets.all(16), children: [
        MeCard(
          padding: const EdgeInsets.all(24),
          child: Column(children: [
            Container(width: 76, height: 76, decoration: const BoxDecoration(color: MeColors.vert, shape: BoxShape.circle), child: const Icon(Icons.check_rounded, color: Colors.white, size: 46)),
            const Gap(16),
            Text(t('Demande envoyée !'), style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800)),
            const Gap(8),
            Text('${t('Merci')} ${nom.text.split(' ').first}. ${t('Un conseiller Mondial Express vous contacte très vite au')} ${tel.text}.', textAlign: TextAlign.center, style: const TextStyle(color: MeColors.muted, height: 1.5)),
            const Gap(6),
            Text('${t('référence')} ${done!['ref']}', style: const TextStyle(fontWeight: FontWeight.w700)),
            const Gap(20),
            WaButton(t('Envoyer aussi sur WhatsApp'), text: '${t('Bonjour Mondial Express, je viens d’envoyer une demande depuis l’application (réf.')} ${done!['ref']}).\n$summary'),
            const Gap(10),
            OutlinedButton(onPressed: () => goTab(context, 0), child: Text(t('Retour à l’accueil'))),
          ]),
        ),
      ]),
    );
  }
}

/// Choix d'une pièce jointe : appareil photo, galerie ou PDF.
Future<Piece?> pickPiece(String source) async {
  if (source == 'pdf') {
    final r = await FilePicker.pickFiles(type: FileType.custom, allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png']);
    if (r.isEmpty || r.first.path == null) return null;
    return Piece(r.first.path!, r.first.name);
  }
  final x = await ImagePicker().pickImage(source: source == 'camera' ? ImageSource.camera : ImageSource.gallery, imageQuality: 80, maxWidth: 2200);
  return x == null ? null : Piece(x.path, x.name);
}
