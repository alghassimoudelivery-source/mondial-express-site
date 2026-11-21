// Identité visuelle et composants partagés de l'application.
import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:share_plus/share_plus.dart';
import 'package:url_launcher/url_launcher.dart';

import 'core.dart';

late AppState app;

class MeColors {
  static const orange = Color(0xFFE8730C);
  static const orangeInk = Color(0xFFB4560A);
  static const vert = Color(0xFF64A70B);
  static const vertInk = Color(0xFF4B7F08);
  static const violet = Color(0xFF5B2D90);
  static const ink = Color(0xFF1C1530);
  static const muted = Color(0xFF6B6478);
  static const bg = Color(0xFFF7F4FA);
  static const line = Color(0xFFE9E3F0);
  static const wa = Color(0xFF25D366);

  static Color named(String? c) => switch (c) { 'vert' => vert, 'violet' => violet, _ => orange };
}

ThemeData meTheme() {
  final base = ThemeData(
    useMaterial3: true,
    colorScheme: ColorScheme.fromSeed(seedColor: MeColors.orange, primary: MeColors.orange, secondary: MeColors.vert, tertiary: MeColors.violet, surface: Colors.white),
    scaffoldBackgroundColor: MeColors.bg,
  );
  final text = I18n.rtl ? GoogleFonts.cairoTextTheme(base.textTheme) : GoogleFonts.montserratTextTheme(base.textTheme);
  return base.copyWith(
    textTheme: text.apply(bodyColor: MeColors.ink, displayColor: MeColors.ink),
    appBarTheme: AppBarTheme(
      backgroundColor: Colors.white,
      foregroundColor: MeColors.ink,
      elevation: 0,
      scrolledUnderElevation: 1,
      centerTitle: false,
      titleTextStyle: (I18n.rtl ? GoogleFonts.cairo : GoogleFonts.montserrat)(fontSize: 18, fontWeight: FontWeight.w800, color: MeColors.ink),
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: Colors.white,
      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: MeColors.line)),
      enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: MeColors.line)),
      focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: MeColors.orange, width: 2)),
    ),
    filledButtonTheme: FilledButtonThemeData(
      style: FilledButton.styleFrom(
        backgroundColor: MeColors.orange,
        foregroundColor: Colors.white,
        minimumSize: const Size.fromHeight(52),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        textStyle: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15),
      ),
    ),
    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        foregroundColor: MeColors.violet,
        minimumSize: const Size.fromHeight(52),
        side: const BorderSide(color: MeColors.line, width: 1.5),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        textStyle: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15),
      ),
    ),
  );
}

// ---------- Actions externes ----------
String waNumber() => (app.config['whatsapp'] as String? ?? '224613003970').replaceAll(RegExp(r'\D'), '');

Future<void> openUrl(String url) async {
  final u = Uri.tryParse(url);
  if (u == null) return;
  if (!await launchUrl(u, mode: LaunchMode.externalApplication)) {
    await launchUrl(u);
  }
}

Future<void> openWhatsApp([String? text]) =>
    openUrl('https://wa.me/${waNumber()}${text != null ? '?text=${Uri.encodeComponent(text)}' : ''}');

Future<void> shareLink(String title, String url) => SharePlus.instance.share(ShareParams(text: '$title — Mondial Express\n$url', subject: title));

void toast(BuildContext context, String msg) {
  ScaffoldMessenger.of(context)
    ..hideCurrentSnackBar()
    ..showSnackBar(SnackBar(content: Text(msg), behavior: SnackBarBehavior.floating, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14))));
}

// ---------- Icônes ----------
IconData serviceIcon(String? name) => switch (name) {
      'plane' => Icons.flight_takeoff_rounded,
      'pass' || 'passport' => Icons.badge_outlined,
      'bed' || 'hotel' => Icons.hotel_outlined,
      'kaaba' => Icons.mosque_outlined,
      'med' || 'medical' => Icons.medical_services_outlined,
      'case' || 'briefcase' => Icons.business_center_outlined,
      _ => Icons.travel_explore,
    };

IconData demandeIcon(String type) => switch (type) {
      'billet' => Icons.flight_takeoff_rounded,
      'visa' => Icons.badge_outlined,
      'hotel' => Icons.hotel_outlined,
      'hajj' => Icons.mosque_outlined,
      'evacuation' => Icons.medical_services_outlined,
      'affaires' => Icons.business_center_outlined,
      _ => Icons.mail_outline,
    };

Color demandeColor(String type) => switch (type) { 'visa' || 'evacuation' => MeColors.vert, 'hotel' || 'affaires' => MeColors.violet, _ => MeColors.orange };

IconData socialIcon(String key) => switch (key) {
      'facebook' => FontAwesomeIcons.facebookF.data,
      'twitter' || 'x' => FontAwesomeIcons.xTwitter.data,
      'tiktok' => FontAwesomeIcons.tiktok.data,
      'instagram' => FontAwesomeIcons.instagram.data,
      'youtube' => FontAwesomeIcons.youtube.data,
      'linkedin' => FontAwesomeIcons.linkedinIn.data,
      'snapchat' => FontAwesomeIcons.snapchat.data,
      _ => Icons.public,
    };

// ---------- Composants ----------
class IconBadge extends StatelessWidget {
  const IconBadge(this.icon, {super.key, this.color = MeColors.orange, this.size = 44});
  final IconData icon;
  final Color color;
  final double size;
  @override
  Widget build(BuildContext context) => Container(
        width: size,
        height: size,
        decoration: BoxDecoration(color: color.withValues(alpha: .12), borderRadius: BorderRadius.circular(size * .32)),
        child: Icon(icon, color: color, size: size * .5),
      );
}

class MeCard extends StatelessWidget {
  const MeCard({super.key, required this.child, this.padding = const EdgeInsets.all(18), this.margin = EdgeInsets.zero});
  final Widget child;
  final EdgeInsets padding;
  final EdgeInsets margin;
  @override
  Widget build(BuildContext context) => Container(
        margin: margin,
        padding: padding,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(22),
          boxShadow: [BoxShadow(color: MeColors.violet.withValues(alpha: .06), blurRadius: 18, offset: const Offset(0, 6))],
        ),
        child: child,
      );
}

class SectionHead extends StatelessWidget {
  const SectionHead(this.title, {super.key, this.action, this.onTap});
  final String title;
  final String? action;
  final VoidCallback? onTap;
  @override
  Widget build(BuildContext context) => Padding(
        padding: const EdgeInsets.fromLTRB(4, 22, 4, 12),
        child: Row(children: [
          Expanded(child: Text(title, style: const TextStyle(fontSize: 19, fontWeight: FontWeight.w800))),
          if (action != null)
            GestureDetector(onTap: onTap, child: Text(action!, style: const TextStyle(color: MeColors.orangeInk, fontWeight: FontWeight.w700))),
        ]),
      );
}

class ListRow extends StatelessWidget {
  const ListRow({super.key, required this.icon, required this.title, this.subtitle, this.color = MeColors.orange, this.onTap, this.trailing = true});
  final IconData icon;
  final String title;
  final String? subtitle;
  final Color color;
  final VoidCallback? onTap;
  final bool trailing;
  @override
  Widget build(BuildContext context) => Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        child: InkWell(
          borderRadius: BorderRadius.circular(18),
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Row(children: [
              IconBadge(icon, color: color),
              const SizedBox(width: 14),
              Expanded(
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text(title, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
                  if (subtitle != null && subtitle!.isNotEmpty)
                    Padding(padding: const EdgeInsets.only(top: 2), child: Text(subtitle!, style: const TextStyle(color: MeColors.muted, fontSize: 13))),
                ]),
              ),
              if (trailing) Icon(I18n.rtl ? Icons.chevron_left : Icons.chevron_right, color: MeColors.muted),
            ]),
          ),
        ),
      );
}

class Gap extends StatelessWidget {
  const Gap([this.h = 12, Key? key]) : super(key: key);
  final double h;
  @override
  Widget build(BuildContext context) => SizedBox(height: h);
}

class WaButton extends StatelessWidget {
  const WaButton(this.label, {super.key, this.text});
  final String label;
  final String? text;
  @override
  Widget build(BuildContext context) => FilledButton.icon(
        style: FilledButton.styleFrom(backgroundColor: MeColors.wa),
        onPressed: () => openWhatsApp(text),
        icon: const FaIcon(FontAwesomeIcons.whatsapp, size: 20),
        label: Text(label),
      );
}

class ErrorBox extends StatelessWidget {
  const ErrorBox(this.message, {super.key});
  final String message;
  @override
  Widget build(BuildContext context) => Container(
        width: double.infinity,
        margin: const EdgeInsets.only(bottom: 12),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(color: const Color(0xFFFDECEC), borderRadius: BorderRadius.circular(12)),
        child: Text(message, style: const TextStyle(color: Color(0xFFB3261E), fontWeight: FontWeight.w600)),
      );
}

class EmptyBox extends StatelessWidget {
  const EmptyBox(this.message, {super.key, this.icon = Icons.info_outline});
  final String message;
  final IconData icon;
  @override
  Widget build(BuildContext context) => Padding(
        padding: const EdgeInsets.symmetric(vertical: 48, horizontal: 24),
        child: Column(children: [
          Icon(icon, size: 40, color: MeColors.muted),
          const Gap(10),
          Text(message, textAlign: TextAlign.center, style: const TextStyle(color: MeColors.muted)),
        ]),
      );
}

class Skeleton extends StatelessWidget {
  const Skeleton({super.key, this.height = 120, this.count = 3});
  final double height;
  final int count;
  @override
  Widget build(BuildContext context) => Column(
        children: List.generate(
          count,
          (_) => Container(
            height: height,
            margin: const EdgeInsets.only(bottom: 12),
            decoration: BoxDecoration(color: MeColors.line.withValues(alpha: .6), borderRadius: BorderRadius.circular(20)),
          ),
        ),
      );
}

/// Visuel d'une offre : photo si disponible, sinon dégradé aux couleurs de la marque.
class OfferVisual extends StatelessWidget {
  const OfferVisual(this.o, {super.key, this.height = 130, this.big = false});
  final Map<String, dynamic> o;
  final double height;
  final bool big;
  @override
  Widget build(BuildContext context) {
    final c = MeColors.named(o['visuel'] as String?);
    final img = o['image'];
    return Container(
      height: height,
      decoration: BoxDecoration(
        gradient: LinearGradient(colors: [c, Color.lerp(c, MeColors.violet, .45)!], begin: Alignment.topLeft, end: Alignment.bottomRight),
        image: img is String && img.isNotEmpty
            ? DecorationImage(image: NetworkImage(img), fit: BoxFit.cover, colorFilter: ColorFilter.mode(Colors.black.withValues(alpha: .25), BlendMode.darken))
            : null,
      ),
      padding: EdgeInsets.all(big ? 20 : 14),
      alignment: AlignmentDirectional.bottomStart,
      child: Stack(children: [
        if (img is! String || img.isEmpty)
          PositionedDirectional(end: 0, top: 0, child: Icon(Icons.flight_rounded, color: Colors.white.withValues(alpha: .35), size: big ? 70 : 44)),
        Align(
          alignment: AlignmentDirectional.bottomStart,
          child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.start, children: [
            if ((o['code'] ?? '') != '')
              Text(o['code'] as String, style: TextStyle(color: Colors.white.withValues(alpha: .9), fontWeight: FontWeight.w700, letterSpacing: 1.2, fontSize: big ? 14 : 11)),
            Text(o['titre'] as String? ?? '', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: big ? 30 : 19, height: 1.1)),
          ]),
        ),
      ]),
    );
  }
}

class PriceText extends StatelessWidget {
  const PriceText(this.o, {super.key, this.size = 20});
  final Map<String, dynamic> o;
  final double size;
  @override
  Widget build(BuildContext context) {
    if ((o['prix'] ?? '') == '') return const SizedBox.shrink();
    return Wrap(crossAxisAlignment: WrapCrossAlignment.end, spacing: 6, children: [
      Text(o['prix'] as String, style: TextStyle(fontSize: size, fontWeight: FontWeight.w800, color: MeColors.orangeInk)),
      if (o['indicatif'] == true) Padding(padding: const EdgeInsets.only(bottom: 3), child: Text(t('prix indicatif'), style: const TextStyle(fontSize: 12, color: MeColors.muted))),
    ]);
  }
}

class ShareButton extends StatelessWidget {
  const ShareButton({super.key, required this.label, required this.title, required this.url});
  final String label, title, url;
  @override
  Widget build(BuildContext context) => OutlinedButton.icon(onPressed: () => shareLink(title, url), icon: const Icon(Icons.share_outlined), label: Text(label));
}

class ConfordevCredit extends StatelessWidget {
  const ConfordevCredit({super.key});
  @override
  Widget build(BuildContext context) => Padding(
        padding: const EdgeInsets.symmetric(vertical: 22),
        child: Center(
          child: GestureDetector(
            onTap: () => openUrl('https://confordev.com'),
            child: Text.rich(
              TextSpan(style: const TextStyle(color: MeColors.muted, fontSize: 12.5), children: [
                TextSpan(text: '${t('Application réalisée par')} '),
                const TextSpan(text: 'ConforDev', style: TextStyle(color: MeColors.violet, fontWeight: FontWeight.w700, decoration: TextDecoration.underline)),
              ]),
            ),
          ),
        ),
      );
}
