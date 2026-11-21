// Mondial Express Guinée — application mobile native (Android / iOS).
// Réalisée par ConforDev (https://confordev.com). Données : back-office WordPress du site.
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:google_fonts/google_fonts.dart';

import 'core.dart';
import 'screens_compte.dart';
import 'screens_demande.dart';
import 'screens_home.dart';
import 'screens_offres.dart';
import 'screens_suivi.dart';
import 'ui.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  SystemChrome.setSystemUIOverlayStyle(const SystemUiOverlayStyle(statusBarColor: Colors.transparent, statusBarIconBrightness: Brightness.dark));
  app = AppState();
  await app.init();
  runApp(const MondialApp());
}

class MondialApp extends StatelessWidget {
  const MondialApp({super.key});
  @override
  Widget build(BuildContext context) => ListenableBuilder(
        listenable: app,
        builder: (context, _) => MaterialApp(
          key: ValueKey(I18n.lang),
          title: 'Mondial Express',
          debugShowCheckedModeBanner: false,
          theme: meTheme(),
          locale: Locale(I18n.lang),
          supportedLocales: kLangs.keys.map(Locale.new).toList(),
          localizationsDelegates: const [GlobalMaterialLocalizations.delegate, GlobalWidgetsLocalizations.delegate, GlobalCupertinoLocalizations.delegate],
          home: const Splash(),
        ),
      );
}

/// Écran de démarrage : charge la configuration, puis ouvre l'application.
class Splash extends StatefulWidget {
  const Splash({super.key});
  @override
  State<Splash> createState() => _SplashState();
}

class _SplashState extends State<Splash> {
  String? error;

  @override
  void initState() {
    super.initState();
    _start();
  }

  Future<void> _start() async {
    setState(() => error = null);
    final wait = Future.wait([
      Future<void>.delayed(const Duration(milliseconds: 900)),
      // Polices de la marque prêtes avant le premier affichage (sinon les textes seraient mesurés avec une police de secours).
      GoogleFonts.pendingFonts().timeout(const Duration(seconds: 4), onTimeout: () => []).catchError((_) => []),
    ]);
    try {
      if (app.config.isEmpty) {
        await app.refreshConfig();
      } else {
        app.refreshConfig().catchError((_) {});
      }
      await wait;
      if (!mounted) return;
      Navigator.of(context).pushReplacement(PageRouteBuilder(pageBuilder: (_, _, _) => const Shell(), transitionsBuilder: (_, a, _, c) => FadeTransition(opacity: a, child: c)));
      app.checkUpdates();
    } catch (e) {
      setState(() => error = e.toString());
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        body: Container(
          decoration: const BoxDecoration(gradient: LinearGradient(colors: [MeColors.orange, Color(0xFFF29A3B), MeColors.violet], begin: Alignment.topLeft, end: Alignment.bottomRight, stops: [0, .55, 1])),
          child: SafeArea(
            child: Center(
              child: Padding(
                padding: const EdgeInsets.all(32),
                child: Column(mainAxisSize: MainAxisSize.min, children: [
                  Image.asset('assets/logo-blanc.png', width: 240),
                  const Gap(28),
                  if (error == null)
                    const SizedBox(width: 28, height: 28, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 3))
                  else ...[
                    Text(error!, textAlign: TextAlign.center, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600)),
                    const Gap(16),
                    FilledButton(style: FilledButton.styleFrom(backgroundColor: Colors.white, foregroundColor: MeColors.orangeInk), onPressed: _start, child: Text(t('Réessayer'))),
                  ],
                ]),
              ),
            ),
          ),
        ),
      );
}

/// Navigation principale : 5 onglets, avec le bouton central « Demande ».
class Shell extends StatefulWidget {
  const Shell({super.key});
  static ShellState? of(BuildContext c) => c.findAncestorStateOfType<ShellState>();
  @override
  State<Shell> createState() => ShellState();
}

class ShellState extends State<Shell> with WidgetsBindingObserver {
  int index = 0;
  final _keys = List.generate(5, (_) => GlobalKey<NavigatorState>());

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) app.checkUpdates();
  }

  /// Ouvre un onglet, éventuellement avec un écran par-dessus.
  void go(int i, [Widget? push]) {
    setState(() => index = i);
    final nav = _keys[i].currentState;
    if (nav == null) return;
    nav.popUntil((r) => r.isFirst);
    if (push != null) nav.push(MaterialPageRoute(builder: (_) => push));
  }

  Widget _tab(int i, Widget root) => Offstage(
        offstage: index != i,
        child: Navigator(key: _keys[i], onGenerateRoute: (_) => MaterialPageRoute(builder: (_) => root)),
      );

  @override
  Widget build(BuildContext context) {
    final items = [
      (Icons.home_outlined, Icons.home_rounded, t('Accueil')),
      (Icons.local_offer_outlined, Icons.local_offer, t('Offres')),
      (Icons.add, Icons.add, t('Demande')),
      (Icons.folder_open_outlined, Icons.folder_rounded, t('Suivi')),
      (Icons.person_outline, Icons.person_rounded, t('Compte')),
    ];
    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, _) async {
        if (didPop) return;
        final nav = _keys[index].currentState!;
        if (nav.canPop()) {
          nav.pop();
        } else if (index != 0) {
          setState(() => index = 0);
        } else {
          SystemNavigator.pop();
        }
      },
      child: Scaffold(
        body: Stack(children: [
          _tab(0, const HomeScreen()),
          _tab(1, const OffresScreen()),
          _tab(2, const DemandeListScreen()),
          _tab(3, const SuiviScreen()),
          _tab(4, const CompteScreen()),
        ]),
        bottomNavigationBar: Container(
          decoration: BoxDecoration(color: Colors.white, boxShadow: [BoxShadow(color: MeColors.violet.withValues(alpha: .08), blurRadius: 20, offset: const Offset(0, -4))]),
          child: SafeArea(
            top: false,
            child: SizedBox(
              height: 68,
              child: Row(
                children: List.generate(5, (i) {
                  final on = index == i;
                  final it = items[i];
                  if (i == 2) {
                    return Expanded(
                      child: InkWell(
                        onTap: () => go(2),
                        child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                          Container(
                            width: 46,
                            height: 46,
                            decoration: BoxDecoration(
                              color: MeColors.orange,
                              shape: BoxShape.circle,
                              boxShadow: [BoxShadow(color: MeColors.orange.withValues(alpha: .4), blurRadius: 12, offset: const Offset(0, 4))],
                            ),
                            child: const Icon(Icons.add, color: Colors.white, size: 28),
                          ),
                          const SizedBox(height: 2),
                          Text(it.$3, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: on ? MeColors.orangeInk : MeColors.muted)),
                        ]),
                      ),
                    );
                  }
                  return Expanded(
                    child: InkWell(
                      onTap: () => on ? go(i) : setState(() => index = i),
                      child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                        Icon(on ? it.$2 : it.$1, color: on ? MeColors.orange : MeColors.muted, size: 25),
                        const SizedBox(height: 3),
                        Text(it.$3, style: TextStyle(fontSize: 11, fontWeight: on ? FontWeight.w800 : FontWeight.w600, color: on ? MeColors.orangeInk : MeColors.muted)),
                      ]),
                    ),
                  );
                }),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

/// Raccourcis de navigation utilisables depuis n'importe quel écran.
void goTab(BuildContext context, int i, [Widget? push]) => Shell.of(context)?.go(i, push);
