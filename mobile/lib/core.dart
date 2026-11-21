// Mondial Express Guinée — application native (Flutter). Réalisée par ConforDev (https://confordev.com).
// Cœur : langues, stockage, API du back-office WordPress (mondial/v1) et état global.
import 'dart:convert';
import 'dart:io';

import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

const String kSite = String.fromEnvironment('ME_SITE', defaultValue: 'https://mondialexpressguinee.com');
const String kApi = '$kSite/wp-json/mondial/v1/';

/// Langues proposées : français (par défaut), anglais, espagnol, arabe.
const Map<String, String> kLangs = {'fr': 'Français', 'en': 'English', 'es': 'Español', 'ar': 'العربية'};

class I18n {
  static Map<String, List<String>> _d = {};
  static String lang = 'fr';

  static Future<void> load() async {
    final raw = jsonDecode(await rootBundle.loadString('assets/i18n.json')) as Map<String, dynamic>;
    _d = raw.map((k, v) => MapEntry(k, List<String>.from(v as List)));
  }

  static String t(String fr) {
    final i = const {'en': 0, 'es': 1, 'ar': 2}[lang];
    if (i == null) return fr;
    final v = _d[fr];
    return v == null ? fr : v[i];
  }

  static bool get rtl => lang == 'ar';
}

String t(String fr) => I18n.t(fr);

class ApiError implements Exception {
  ApiError(this.message, [this.status = 0]);
  final String message;
  final int status;
  @override
  String toString() => message;
}

/// Fichier joint à une demande ou à un dossier.
class Piece {
  Piece(this.path, this.name);
  final String path;
  final String name;
}

class AppState extends ChangeNotifier {
  late SharedPreferences prefs;
  Map<String, dynamic> config = {};
  Map<String, dynamic>? user;
  String? _token;
  final Map<String, dynamic> _cache = {};

  Future<void> init() async {
    prefs = await SharedPreferences.getInstance();
    await I18n.load();
    var l = prefs.getString('lang');
    if (l == null || !kLangs.containsKey(l)) {
      final sys = PlatformDispatcher.instance.locale.languageCode;
      l = kLangs.containsKey(sys) ? sys : 'fr';
    }
    I18n.lang = l;
    _token = prefs.getString('token');
    final saved = prefs.getString('c_${I18n.lang}_config') ?? '';
    if (saved.isNotEmpty) config = jsonDecode(saved) as Map<String, dynamic>;
    final u = prefs.getString('user');
    if (u != null && _token != null) user = jsonDecode(u) as Map<String, dynamic>;
  }

  /// Charge la configuration (coordonnées, services, formulaires). Utilise la copie locale hors connexion.
  Future<void> refreshConfig() async {
    try {
      final c = await call('config') as Map<String, dynamic>;
      config = c;
      prefs.setString('c_${I18n.lang}_config', jsonEncode(c));
      notifyListeners();
    } catch (_) {
      if (config.isEmpty) rethrow;
    }
  }

  Future<void> setLang(String l) async {
    I18n.lang = l;
    await prefs.setString('lang', l);
    _cache.clear();
    final saved = prefs.getString('c_${l}_config');
    if (saved != null) config = jsonDecode(saved) as Map<String, dynamic>;
    notifyListeners();
    await refreshConfig();
    if (user != null) await refreshUser();
  }

  Map<String, String> get _headers => {
        'Accept': 'application/json',
        if (_token != null) 'Authorization': 'Bearer $_token',
      };

  Uri _uri(String path) => Uri.parse('$kApi$path${path.contains('?') ? '&' : '?'}lang=${I18n.lang}');

  Future<dynamic> call(String path, {Map<String, dynamic>? body, Map<String, String>? fields, Map<String, Piece>? files}) async {
    http.Response res;
    try {
      if (files != null || fields != null) {
        final req = http.MultipartRequest('POST', _uri(path))..headers.addAll(_headers);
        req.fields.addAll(fields ?? {});
        for (final e in (files ?? {}).entries) {
          req.files.add(await http.MultipartFile.fromPath(e.key, e.value.path, filename: e.value.name));
        }
        res = await http.Response.fromStream(await req.send().timeout(const Duration(seconds: 60)));
      } else if (body != null) {
        res = await http
            .post(_uri(path), headers: {..._headers, 'Content-Type': 'application/json'}, body: jsonEncode(body))
            .timeout(const Duration(seconds: 30));
      } else {
        res = await http.get(_uri(path), headers: _headers).timeout(const Duration(seconds: 30));
      }
    } on SocketException {
      throw ApiError(t('Connexion impossible. Vérifiez votre réseau.'));
    } catch (e) {
      throw ApiError(t('Connexion impossible. Vérifiez votre réseau.'));
    }
    dynamic data;
    try {
      data = jsonDecode(utf8.decode(res.bodyBytes));
    } catch (_) {
      data = {};
    }
    if (res.statusCode == 401 && _token != null && path != 'connexion') {
      await _forget();
    }
    if (res.statusCode >= 400) {
      final msg = data is Map && data['message'] is String ? data['message'] as String : t('Connexion impossible. Vérifiez votre réseau.');
      throw ApiError(msg, res.statusCode);
    }
    return data;
  }

  /// Liste mise en cache (offres, forfaits, actus) : affiche la copie locale puis la met à jour.
  Future<List<dynamic>> list(String name, {bool fresh = false}) async {
    if (!fresh && _cache[name] != null) return _cache[name] as List<dynamic>;
    final key = 'c_${I18n.lang}_$name';
    try {
      final d = await call(name) as List<dynamic>;
      _cache[name] = d;
      prefs.setString(key, jsonEncode(d));
      return d;
    } catch (e) {
      final saved = prefs.getString(key);
      if (saved != null) return jsonDecode(saved) as List<dynamic>;
      rethrow;
    }
  }

  Future<void> _saveUser(Map<String, dynamic> r) async {
    _token = r['jeton'] as String?;
    user = r['utilisateur'] as Map<String, dynamic>?;
    if (_token != null) await prefs.setString('token', _token!);
    await prefs.setString('user', jsonEncode(user));
    for (final d in (user?['dossiers'] as List? ?? [])) {
      remember(d as Map<String, dynamic>);
    }
    notifyListeners();
  }

  Future<void> login(String id, String pass) async =>
      _saveUser(await call('connexion', body: {'identifiant': id, 'mot_de_passe': pass}) as Map<String, dynamic>);

  Future<void> signup(String nom, String tel, String email, String pass) async => _saveUser(
      await call('inscription', body: {'nom': nom, 'telephone': tel, 'email': email, 'mot_de_passe': pass}) as Map<String, dynamic>);

  Future<void> refreshUser() async {
    if (_token == null) return;
    try {
      user = await call('moi') as Map<String, dynamic>?;
      await prefs.setString('user', jsonEncode(user));
      notifyListeners();
    } catch (_) {/* hors ligne : on garde la copie locale */}
  }

  Future<void> logout() async {
    try {
      await call('deconnexion', body: {});
    } catch (_) {/* déjà déconnecté */}
    await _forget();
  }

  Future<void> _forget() async {
    _token = null;
    user = null;
    await prefs.remove('token');
    await prefs.remove('user');
    notifyListeners();
  }

  // ---------- Stockage local simple ----------
  T get<T>(String k, T def) {
    final v = prefs.getString('s_$k');
    if (v == null) return def;
    try {
      return jsonDecode(v) as T;
    } catch (_) {
      return def;
    }
  }

  void set(String k, Object? v) => prefs.setString('s_$k', jsonEncode(v));

  // ---------- Notifications d'étape (dans l'application) ----------
  List<dynamic> get notifs => get<List<dynamic>>('notifs', []);
  bool get unread => notifs.any((n) => n['lu'] != true);

  void addNotif(String titre, String texte) {
    final l = notifs..insert(0, {'titre': titre, 'texte': texte, 'date': DateTime.now().toIso8601String(), 'lu': false});
    set('notifs', l.take(30).toList());
    notifyListeners();
  }

  void markRead() {
    set('notifs', notifs.map((n) => {...(n as Map), 'lu': true}).toList());
    notifyListeners();
  }

  void remember(Map<String, dynamic> d) {
    final s = get<Map<String, dynamic>>('etapes', {});
    s[d['numero'] as String] = d['etape'];
    set('etapes', s);
  }

  /// Vérifie les dossiers suivis et le compte : une nouvelle étape crée une notification.
  Future<void> checkUpdates() async {
    final known = get<Map<String, dynamic>>('etapes', {});
    await refreshUser();
    final list = <Map<String, dynamic>>[...((user?['dossiers'] as List?) ?? []).cast<Map<String, dynamic>>()];
    for (final s in get<List<dynamic>>('suivis', [])) {
      try {
        list.add(await call('suivi', body: Map<String, dynamic>.from(s as Map)) as Map<String, dynamic>);
      } catch (_) {/* hors ligne ou dossier retiré */}
    }
    for (final d in list) {
      final num = d['numero'] as String;
      if (known[num] != null && known[num] != d['etape']) {
        final step = (d['etapes'] as List).cast<Map>().firstWhere((x) => x['cle'] == d['etape'], orElse: () => {'libelle': d['etape']});
        addNotif('${t('Dossier')} $num', '${t('Nouvelle étape :')} ${step['libelle']}${(d['rdv'] ?? '') != '' ? ' — ${d['rdv']}' : ''}');
      }
      remember(d);
    }
  }
}
