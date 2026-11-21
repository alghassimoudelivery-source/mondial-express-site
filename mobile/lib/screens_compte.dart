// Compte client (connexion, inscription, dossiers, envoi de documents), contact, notifications, langue.
import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';

import 'core.dart';
import 'screens_agence.dart';
import 'screens_demande.dart';
import 'screens_suivi.dart';
import 'ui.dart';

class CompteScreen extends StatelessWidget {
  const CompteScreen({super.key});
  @override
  Widget build(BuildContext context) => ListenableBuilder(
        listenable: app,
        builder: (context, _) => Scaffold(
          appBar: AppBar(title: Text(t('Mon compte'))),
          body: app.user == null ? const _AuthForm() : const _Profile(),
        ),
      );
}

class _AuthForm extends StatefulWidget {
  const _AuthForm();
  @override
  State<_AuthForm> createState() => _AuthFormState();
}

class _AuthFormState extends State<_AuthForm> {
  bool signup = false;
  bool busy = false;
  bool hide = true;
  String? error;
  final nom = TextEditingController(), tel = TextEditingController(), id = TextEditingController(), pass = TextEditingController();

  Future<void> _submit() async {
    if (id.text.trim().isEmpty || pass.text.isEmpty) return;
    setState(() {
      busy = true;
      error = null;
    });
    try {
      if (signup) {
        await app.signup(nom.text.trim(), tel.text.trim(), id.text.trim(), pass.text);
      } else {
        await app.login(id.text.trim(), pass.text);
      }
      if (mounted) toast(context, signup ? t('Bienvenue ! Votre compte est créé.') : t('Vous êtes connecté.'));
    } on ApiError catch (e) {
      if (mounted) setState(() => error = e.message);
    } finally {
      if (mounted) setState(() => busy = false);
    }
  }

  @override
  Widget build(BuildContext context) => ListView(padding: const EdgeInsets.all(16), children: [
        SegmentedButton<bool>(
          segments: [ButtonSegment(value: false, label: Text(t('Se connecter'))), ButtonSegment(value: true, label: Text(t('Créer un compte')))],
          selected: {signup},
          showSelectedIcon: false,
          style: SegmentedButton.styleFrom(selectedBackgroundColor: const Color(0xFFFFE9D6), selectedForegroundColor: MeColors.orangeInk),
          onSelectionChanged: (s) => setState(() {
            signup = s.first;
            error = null;
          }),
        ),
        const Gap(14),
        MeCard(
          child: AutofillGroup(
            child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
              if (error != null) ErrorBox(error!),
              if (signup) ...[
                TextField(controller: nom, textCapitalization: TextCapitalization.words, autofillHints: const [AutofillHints.name], decoration: InputDecoration(labelText: t('Nom complet'))),
                const Gap(12),
                TextField(controller: tel, keyboardType: TextInputType.phone, autofillHints: const [AutofillHints.telephoneNumber], decoration: InputDecoration(labelText: t('Téléphone / WhatsApp'))),
                const Gap(12),
              ],
              TextField(
                controller: id,
                keyboardType: TextInputType.emailAddress,
                autofillHints: const [AutofillHints.username, AutofillHints.email],
                decoration: InputDecoration(labelText: signup ? t('E-mail') : t('E-mail ou identifiant')),
              ),
              const Gap(12),
              TextField(
                controller: pass,
                obscureText: hide,
                autofillHints: [signup ? AutofillHints.newPassword : AutofillHints.password],
                onSubmitted: (_) => _submit(),
                decoration: InputDecoration(
                  labelText: t('Mot de passe'),
                  suffixIcon: IconButton(icon: Icon(hide ? Icons.visibility_outlined : Icons.visibility_off_outlined), onPressed: () => setState(() => hide = !hide)),
                ),
              ),
              const Gap(16),
              FilledButton(
                onPressed: busy ? null : _submit,
                child: busy ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)) : Text(signup ? t('Créer mon compte') : t('Se connecter')),
              ),
              if (!signup)
                TextButton(onPressed: () => openUrl('$kSite/wp-login.php?action=lostpassword'), child: Text(t('Mot de passe oublié ?'))),
            ]),
          ),
        ),
        const Gap(18),
        ListRow(icon: Icons.groups_outlined, color: MeColors.orange, title: t('L’agence'), subtitle: t('Notre histoire, nos valeurs et notre équipe'), onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const AgenceScreen()))),
        const Gap(10),
        const ContactList(),
        const LangBlock(),
        const ConfordevCredit(),
      ]);
}

class _Profile extends StatelessWidget {
  const _Profile();
  @override
  Widget build(BuildContext context) {
    final u = app.user!;
    final nom = u['nom'] as String? ?? '';
    final initials = nom.split(' ').where((p) => p.isNotEmpty).map((p) => p[0]).take(2).join().toUpperCase();
    final dossiers = ((u['dossiers'] as List?) ?? []).cast<Map<String, dynamic>>();
    return RefreshIndicator(
      color: MeColors.orange,
      onRefresh: app.refreshUser,
      child: ListView(padding: const EdgeInsets.all(16), children: [
        Container(
          padding: const EdgeInsets.all(18),
          decoration: BoxDecoration(borderRadius: BorderRadius.circular(24), gradient: const LinearGradient(colors: [MeColors.orange, MeColors.violet])),
          child: Row(children: [
            CircleAvatar(radius: 28, backgroundColor: Colors.white, child: Text(initials, style: const TextStyle(color: MeColors.violet, fontWeight: FontWeight.w800, fontSize: 18))),
            const SizedBox(width: 14),
            Expanded(
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(nom, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 18)),
                Text(u['email'] as String? ?? '', style: TextStyle(color: Colors.white.withValues(alpha: .9), fontSize: 13)),
              ]),
            ),
          ]),
        ),
        SectionHead(t('Mes dossiers')),
        if (dossiers.isEmpty) MeCard(child: Text(t('Aucun dossier pour le moment. Envoyez une demande : votre dossier apparaîtra ici.'), style: const TextStyle(color: MeColors.muted))),
        ...dossiers.map((d) => DossierCard(d, footer: _DocUpload(d))),
        const Gap(8),
        ListRow(icon: Icons.notifications_none_rounded, title: t('Notifications'), subtitle: t('Être prévenu à chaque étape'), onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const NotificationsScreen()))),
        const Gap(10),
        ListRow(icon: Icons.groups_outlined, title: t('L’agence'), subtitle: t('Notre histoire, nos valeurs et notre équipe'), onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const AgenceScreen()))),
        const Gap(10),
        ListRow(icon: Icons.phone_in_talk_outlined, color: MeColors.vert, title: t('Contacter l’agence'), onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const ContactScreen()))),
        const Gap(10),
        ListRow(
          icon: Icons.logout_rounded,
          color: MeColors.violet,
          title: t('Se déconnecter'),
          trailing: false,
          onTap: () async {
            await app.logout();
            if (context.mounted) toast(context, t('Vous êtes déconnecté.'));
          },
        ),
        const LangBlock(),
        const ConfordevCredit(),
      ]),
    );
  }
}

class _DocUpload extends StatefulWidget {
  const _DocUpload(this.d);
  final Map<String, dynamic> d;
  @override
  State<_DocUpload> createState() => _DocUploadState();
}

class _DocUploadState extends State<_DocUpload> {
  final label = TextEditingController();
  Piece? piece;
  bool busy = false;

  Future<void> _choose() async {
    final c = await showModalBottomSheet<String>(
      context: context,
      showDragHandle: true,
      builder: (b) => SafeArea(
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          ListTile(leading: const Icon(Icons.photo_camera_outlined), title: Text(t('Prendre une photo')), onTap: () => Navigator.pop(b, 'camera')),
          ListTile(leading: const Icon(Icons.photo_library_outlined), title: Text(t('Choisir dans la galerie')), onTap: () => Navigator.pop(b, 'gallery')),
          ListTile(leading: const Icon(Icons.picture_as_pdf_outlined), title: Text(t('Choisir un PDF')), onTap: () => Navigator.pop(b, 'pdf')),
        ]),
      ),
    );
    if (c == null) return;
    final p = await pickPiece(c);
    if (p != null) setState(() => piece = p);
  }

  Future<void> _send() async {
    if (piece == null) {
      toast(context, t('Choisissez un fichier.'));
      return;
    }
    setState(() => busy = true);
    try {
      await app.call('document', fields: {'dossier': '${widget.d['id']}', 'libelle': label.text.trim()}, files: {'fichier': piece!});
      await app.refreshUser();
      if (mounted) toast(context, t('Document envoyé à l’agence.'));
    } on ApiError catch (e) {
      if (mounted) toast(context, e.message);
    } finally {
      if (mounted) setState(() => busy = false);
    }
  }

  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(color: MeColors.bg, borderRadius: BorderRadius.circular(16)),
        child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
          Text(t('Envoyer un document'), style: const TextStyle(fontWeight: FontWeight.w800)),
          Text(t('Passeport, photo, relevé… (PDF, JPG, PNG — 8 Mo)'), style: const TextStyle(color: MeColors.muted, fontSize: 12.5)),
          const Gap(10),
          TextField(controller: label, decoration: InputDecoration(labelText: t('Nom du document'), hintText: t('Passeport'))),
          const Gap(10),
          OutlinedButton.icon(
            onPressed: _choose,
            icon: Icon(piece == null ? Icons.upload_file_outlined : Icons.check_circle, color: piece == null ? MeColors.violet : MeColors.vert),
            label: Text(piece?.name ?? t('Choisir ou prendre une photo'), overflow: TextOverflow.ellipsis),
          ),
          const Gap(8),
          FilledButton(onPressed: busy ? null : _send, child: Text(busy ? t('Envoi…') : t('Envoyer'))),
        ]),
      );
}

class ContactList extends StatelessWidget {
  const ContactList({super.key});
  @override
  Widget build(BuildContext context) {
    final c = app.config;
    final tel = c['telephone'] as String? ?? '+224 613 00 39 70';
    return Column(children: [
      ListRow(icon: FontAwesomeIcons.whatsapp.data, color: MeColors.vert, title: 'WhatsApp', subtitle: tel, onTap: () => openWhatsApp()),
      const Gap(10),
      ListRow(icon: Icons.phone_outlined, title: t('Appeler l’agence'), subtitle: c['horaires'] as String?, onTap: () => openUrl('tel:${tel.replaceAll(' ', '')}')),
      if ((c['email'] ?? '') != '') ...[
        const Gap(10),
        ListRow(icon: Icons.mail_outline, color: MeColors.violet, title: t('E-mail'), subtitle: c['email'] as String, onTap: () => openUrl('mailto:${c['email']}')),
      ],
      const Gap(10),
      ListRow(icon: Icons.place_outlined, color: MeColors.violet, title: t('Nous trouver'), subtitle: c['adresse'] as String?, onTap: () => openUrl('$kSite${I18n.lang == 'fr' ? '' : '/${I18n.lang}'}/contact/')),
    ]);
  }
}

class LangBlock extends StatelessWidget {
  const LangBlock({super.key});
  @override
  Widget build(BuildContext context) => Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
        SectionHead(t('Langue')),
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: kLangs.entries
              .map((e) => ChoiceChip(
                    label: Text(e.value),
                    selected: I18n.lang == e.key,
                    showCheckmark: false,
                    selectedColor: const Color(0xFFFFE9D6),
                    labelStyle: TextStyle(fontWeight: FontWeight.w700, color: I18n.lang == e.key ? MeColors.orangeInk : MeColors.ink),
                    side: BorderSide(color: I18n.lang == e.key ? MeColors.orange : MeColors.line),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                    onSelected: (_) => app.setLang(e.key),
                  ))
              .toList(),
        ),
      ]);
}

class ContactScreen extends StatelessWidget {
  const ContactScreen({super.key});
  @override
  Widget build(BuildContext context) {
    final c = app.config;
    final pays = (c['paiements'] as List? ?? []).cast<Map<String, dynamic>>();
    final socials = (c['reseaux'] as List? ?? []).cast<Map<String, dynamic>>();
    return Scaffold(
      appBar: AppBar(title: Text(t('Contact'))),
      body: ListView(padding: const EdgeInsets.all(16), children: [
        const ContactList(),
        if (pays.isNotEmpty) ...[
          SectionHead(t('Paiements acceptés')),
          MeCard(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Wrap(spacing: 6, runSpacing: 6, children: pays.map((p) => PayChip(p)).toList()),
              if ((c['paiement_note'] ?? '') != '') ...[const Gap(10), Text(c['paiement_note'] as String, style: const TextStyle(color: MeColors.muted, fontSize: 13))],
            ]),
          ),
        ],
        const LangBlock(),
        SectionHead(t('Suivez-nous')),
        Wrap(spacing: 10, runSpacing: 10, children: [
          ...socials.map((r) {
            final url = r['url'] as String? ?? '';
            return Tooltip(
              message: r['nom'] as String? ?? '',
              child: Material(
                color: url.isEmpty ? MeColors.line : MeColors.violet,
                shape: const CircleBorder(),
                child: InkWell(
                  customBorder: const CircleBorder(),
                  onTap: url.isEmpty ? null : () => openUrl(url),
                  child: SizedBox(width: 48, height: 48, child: Center(child: Icon(socialIcon(r['cle'] as String? ?? ''), color: Colors.white, size: 20))),
                ),
              ),
            );
          }),
          Material(
            color: MeColors.wa,
            shape: const CircleBorder(),
            child: InkWell(customBorder: const CircleBorder(), onTap: () => openWhatsApp(), child: const SizedBox(width: 48, height: 48, child: Center(child: FaIcon(FontAwesomeIcons.whatsapp, color: Colors.white, size: 22)))),
          ),
        ]),
        const Gap(14),
        Center(child: Text(c['slogan'] as String? ?? '', style: const TextStyle(color: MeColors.muted, fontStyle: FontStyle.italic))),
        const ConfordevCredit(),
      ]),
    );
  }
}

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});
  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  late final List<dynamic> list = app.notifs;
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => app.markRead());
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(title: Text(t('Notifications'))),
        body: list.isEmpty
            ? EmptyBox(t('Aucune notification. Suivez un dossier pour être prévenu à chaque étape.'), icon: Icons.notifications_none_rounded)
            : ListView.separated(
                padding: const EdgeInsets.all(16),
                itemCount: list.length,
                separatorBuilder: (_, _) => const Gap(10),
                itemBuilder: (_, i) {
                  final n = list[i] as Map;
                  final d = DateTime.tryParse('${n['date']}');
                  return MeCard(
                    padding: const EdgeInsets.all(14),
                    child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      IconBadge(Icons.notifications_active_outlined, size: 40, color: n['lu'] == true ? MeColors.muted : MeColors.orange),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                          Text('${n['titre']}', style: const TextStyle(fontWeight: FontWeight.w800)),
                          const SizedBox(height: 2),
                          Text('${n['texte']}', style: const TextStyle(height: 1.4)),
                          if (d != null)
                            Padding(
                              padding: const EdgeInsets.only(top: 4),
                              child: Text('${MaterialLocalizations.of(context).formatShortDate(d)} · ${MaterialLocalizations.of(context).formatTimeOfDay(TimeOfDay.fromDateTime(d))}', style: const TextStyle(color: MeColors.muted, fontSize: 12)),
                            ),
                        ]),
                      ),
                    ]),
                  );
                },
              ),
      );
}
