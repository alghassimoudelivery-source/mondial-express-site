#!/bin/sh
# Installation / mise à jour de Mondial Express (lancé par le service « setup »).
# - Copie le thème du dépôt dans WordPress (à chaque lancement : c'est la mise à jour).
# - Installe WordPress et le contenu de démonstration UNE SEULE FOIS (option me_seeded).
set -e
cd /var/www/html
echo "Attente des fichiers WordPress…"
i=0; while [ ! -f wp-config.php ] && [ $i -lt 90 ]; do sleep 2; i=$((i+1)); done
echo "Attente de la base de données…"
i=0; until wp db check --quiet >/dev/null 2>&1 || [ $i -ge 60 ]; do sleep 3; i=$((i+1)); done

mkdir -p wp-content/themes
rm -rf wp-content/themes/mondial-express.new
cp -r /src/repo/theme/mondial-express wp-content/themes/mondial-express.new
rm -rf wp-content/themes/mondial-express
mv wp-content/themes/mondial-express.new wp-content/themes/mondial-express
echo "Thème copié."

if ! wp core is-installed >/dev/null 2>&1; then
  wp core install --url="$ME_URL" --title="Mondial Express Guinée" --admin_user="$WP_ADMIN_USER" --admin_password="$WP_ADMIN_PASSWORD" --admin_email="$WP_ADMIN_EMAIL" --skip-email
  wp language core install fr_FR --activate || echo "Langue fr_FR non installée (réseau) : WordPress reste en anglais dans l'administration."
  echo "WordPress installé."
fi

# Changement d'adresse du site (ex. aperçu IP:8085 -> domaine) : remplace l'ancienne URL partout.
OLD_URL=$(wp eval 'global $wpdb; echo $wpdb->get_var( "SELECT option_value FROM {$wpdb->options} WHERE option_name = \"home\"" );' 2>/dev/null | tr -d '\r')
if [ -n "$OLD_URL" ] && [ "$OLD_URL" != "$ME_URL" ]; then
  wp search-replace "$OLD_URL" "$ME_URL" --all-tables --skip-columns=guid --precise
  wp option update home "$ME_URL" >/dev/null
  wp option update siteurl "$ME_URL" >/dev/null
  echo "Adresse changée : $OLD_URL -> $ME_URL"
fi

if [ "$(wp option get me_seeded 2>/dev/null)" != "1" ]; then
  wp eval-file /src/repo/seed/seed.php
  wp option update me_seeded 1
  echo "Contenu de démonstration créé."
else
  wp theme activate mondial-express >/dev/null
fi
# Présentation de l'agence : photos de l'équipe et entrée « L’agence » du menu (idempotent).
wp eval-file /src/repo/seed/presentation.php || echo "Présentation : à reprendre au prochain déploiement."
wp eval-file /src/repo/seed/confidentialite.php || echo "Confidentialité : à reprendre."
wp rewrite structure '/%postname%/' >/dev/null
wp rewrite flush --hard >/dev/null 2>&1 || wp rewrite flush
echo SETUP_OK
