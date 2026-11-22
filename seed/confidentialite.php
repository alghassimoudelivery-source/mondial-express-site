<?php
/**
 * Politique de confidentialité complète (site + application), exigée par Google Play et l'App Store.
 * Écrite une fois par version (option me_privacy_v). Usage : wp eval-file seed/confidentialite.php
 */
if ( (int) get_option( 'me_privacy_v' ) >= 2 ) {
	return;
}
$page = get_page_by_path( 'confidentialite' );
if ( ! $page ) {
	WP_CLI::warning( 'Page confidentialité absente.' );
	return;
}

$fr = '<p><em>Dernière mise à jour : 22 novembre 2026.</em></p>
<p>Cette politique s’applique au site mondialexpressguinee.com et à l’application mobile Mondial Express (Android et iPhone), édités par Mondial Express Guinée, agence de voyages à Conakry (République de Guinée). Contact : +224 613 00 39 70 (téléphone et WhatsApp).</p>
<h2>Données que nous collectons</h2>
<ul><li><strong>Demandes</strong> : nom, téléphone, e-mail (facultatif), détails du voyage (destination, dates, nombre de voyageurs, message).</li>
<li><strong>Compte client</strong> : nom, téléphone, e-mail, mot de passe (chiffré).</li>
<li><strong>Documents</strong> : pièces que vous envoyez pour votre dossier (passeport, photos, justificatifs), depuis l’appareil photo, la galerie ou un fichier PDF.</li>
<li><strong>Suivi de dossier</strong> : numéro de dossier et 4 derniers chiffres du téléphone, pour afficher l’avancement.</li></ul>
<p>L’application n’utilise ni la localisation, ni les contacts, ni de publicité, ni de traceur publicitaire. L’appareil photo et la galerie ne sont utilisés que lorsque vous choisissez d’envoyer un document.</p>
<h2>Utilisation</h2>
<p>Ces données servent uniquement à traiter vos demandes (billets, visas, hôtels, Hajj et Oumra, transport), à suivre votre dossier et à vous contacter. Elles ne sont ni vendues ni louées. Elles peuvent être transmises, seulement si votre dossier l’exige, aux compagnies aériennes, hôtels ou services consulaires concernés.</p>
<h2>Stockage et sécurité</h2>
<p>Les données sont hébergées sur notre serveur sécurisé (connexion chiffrée HTTPS). Les documents sont rangés dans un dossier protégé, accessible seulement à nos conseillers. Elles sont conservées le temps nécessaire au traitement du dossier et aux obligations légales de l’agence.</p>
<h2>Mesure d’audience</h2>
<p>Le site peut utiliser un outil de mesure d’audience (Google Analytics) une fois activé par l’agence. L’application n’en utilise pas.</p>
<h2>Vos droits et suppression du compte</h2>
<p>Vous pouvez consulter, corriger ou supprimer vos données à tout moment :</p>
<ul><li><strong>Dans l’application</strong> : onglet Compte, puis « Supprimer mon compte ». Le compte et les informations personnelles associées sont effacés immédiatement.</li>
<li><strong>Sur demande</strong> : par WhatsApp ou téléphone au +224 613 00 39 70, en indiquant votre nom et votre numéro. Nous traitons la demande sous 30 jours.</li></ul>
<p>Les dossiers de voyage déjà engagés (billets émis, rendez-vous) peuvent être conservés par l’agence pour ses obligations comptables et légales.</p>
<h2>Enfants</h2>
<p>Le compte est réservé aux personnes majeures. Les demandes concernant des mineurs sont faites par un parent ou tuteur.</p>
<h2>Modifications</h2>
<p>Cette politique peut évoluer. La date de mise à jour figure en haut de cette page.</p>';

$en = '<p><em>Last updated: 22 November 2026.</em></p>
<p>This policy covers the mondialexpressguinee.com website and the Mondial Express mobile app (Android and iPhone), published by Mondial Express Guinée, travel agency in Conakry (Republic of Guinea). Contact: +224 613 00 39 70 (phone and WhatsApp).</p>
<h2>Data we collect</h2>
<ul><li><strong>Requests</strong>: name, phone, email (optional), trip details (destination, dates, travellers, message).</li>
<li><strong>Customer account</strong>: name, phone, email, password (encrypted).</li>
<li><strong>Documents</strong>: files you send for your application (passport, photos, supporting documents), from the camera, gallery or a PDF file.</li>
<li><strong>File tracking</strong>: file number and last 4 digits of your phone, to show progress.</li></ul>
<p>The app does not use location, contacts, advertising or ad tracking. The camera and gallery are only used when you choose to send a document.</p>
<h2>Use</h2>
<p>This data is only used to handle your requests (tickets, visas, hotels, Hajj and Umrah, transport), track your file and contact you. It is never sold or rented. It may be shared, only when your file requires it, with the airlines, hotels or consular services concerned.</p>
<h2>Storage and security</h2>
<p>Data is hosted on our secure server (encrypted HTTPS connection). Documents are kept in a protected folder only our advisers can access, for as long as needed to handle the file and meet the agency’s legal obligations.</p>
<h2>Your rights and account deletion</h2>
<ul><li><strong>In the app</strong>: Account tab, then “Delete my account”. The account and its personal information are erased immediately.</li>
<li><strong>On request</strong>: by WhatsApp or phone at +224 613 00 39 70 with your name and number. We handle requests within 30 days.</li></ul>
<p>Travel files already committed (issued tickets, appointments) may be kept by the agency for accounting and legal obligations.</p>
<h2>Children</h2><p>Accounts are for adults. Requests for minors are made by a parent or guardian.</p>';

$es = '<p><em>Última actualización: 22 de noviembre de 2026.</em></p>
<p>Esta política se aplica al sitio mondialexpressguinee.com y a la aplicación Mondial Express (Android y iPhone), de Mondial Express Guinée, agencia de viajes en Conakry. Contacto: +224 613 00 39 70.</p>
<h2>Datos recogidos</h2><p>Solicitudes (nombre, teléfono, correo, datos del viaje), cuenta de cliente (nombre, teléfono, correo, contraseña cifrada), documentos que usted envía y número de expediente. La aplicación no usa localización, contactos ni publicidad.</p>
<h2>Uso</h2><p>Solo para tramitar sus solicitudes, seguir su expediente y contactarle. Nunca se venden. Se comparten únicamente con aerolíneas, hoteles o consulados cuando su expediente lo requiere.</p>
<h2>Seguridad</h2><p>Servidor seguro (HTTPS); documentos en una carpeta protegida accesible solo a nuestros asesores.</p>
<h2>Sus derechos y eliminación de la cuenta</h2><p>En la aplicación: pestaña Cuenta, «Eliminar mi cuenta» (borrado inmediato). O por WhatsApp al +224 613 00 39 70 (respuesta en 30 días). Los expedientes ya en curso pueden conservarse por obligaciones legales.</p>';

$ar = '<p><em>آخر تحديث: 22 نوفمبر 2026.</em></p>
<p>تنطبق هذه السياسة على موقع mondialexpressguinee.com وتطبيق Mondial Express (أندرويد وآيفون) التابعين لوكالة السفر Mondial Express Guinée في كوناكري. للتواصل: <bdi dir="ltr">+224 613 00 39 70</bdi>.</p>
<h2>البيانات التي نجمعها</h2><p>الطلبات (الاسم، الهاتف، البريد، تفاصيل الرحلة)، حساب العميل (الاسم، الهاتف، البريد، كلمة مرور مشفرة)، الوثائق التي ترسلها ورقم الملف. لا يستخدم التطبيق الموقع الجغرافي ولا جهات الاتصال ولا الإعلانات.</p>
<h2>الاستخدام</h2><p>فقط لمعالجة طلباتك ومتابعة ملفك والتواصل معك. لا تُباع أبدًا، وتُشارك فقط مع شركات الطيران أو الفنادق أو القنصليات عند الحاجة.</p>
<h2>الأمان</h2><p>خادم آمن (HTTPS) والوثائق في مجلد محمي لا يطّلع عليه إلا مستشارونا.</p>
<h2>حقوقك وحذف الحساب</h2><p>من التطبيق: تبويب الحساب ثم «حذف حسابي» (حذف فوري). أو عبر واتساب على <bdi dir="ltr">+224 613 00 39 70</bdi> (خلال 30 يومًا). قد تحتفظ الوكالة بملفات السفر الجارية لالتزاماتها القانونية.</p>';

wp_update_post( wp_slash( array( 'ID' => $page->ID, 'post_content' => $fr ) ) );
foreach ( array( 'en' => $en, 'es' => $es, 'ar' => $ar ) as $l => $html ) {
	update_post_meta( $page->ID, '_me_' . $l . '_contenu', wp_slash( $html ) );
}
update_option( 'me_privacy_v', 2, false );
WP_CLI::log( 'Politique de confidentialité mise à jour (v2).' );
