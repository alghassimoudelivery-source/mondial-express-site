#!/bin/bash
# Rattache mondialexpressguinee.com au site Docker (port 8085) via le serveur web déjà installé sur l'hôte.
# N'ajoute qu'un nouveau fichier de configuration ; ne modifie aucun autre site. Teste avant de recharger.
set -e
D=mondialexpressguinee.com
if systemctl is-active --quiet nginx; then
  F=/etc/nginx/sites-available/$D
  [ -e "$F" ] && cp "$F" "$F.bak.$(date +%s)"
  cat > "$F" <<NGX
server {
    listen 80;
    listen [::]:80;
    server_name $D www.$D;
    client_max_body_size 64m;
    location / {
        proxy_pass http://127.0.0.1:8085;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}
NGX
  ln -sf "$F" /etc/nginx/sites-enabled/$D
  nginx -t && systemctl reload nginx
  command -v certbot >/dev/null || (apt-get update -qq && apt-get install -y -qq certbot python3-certbot-nginx)
  certbot --nginx -d $D -d www.$D --non-interactive --agree-tos --register-unsafely-without-email --redirect
elif systemctl is-active --quiet apache2; then
  a2enmod -q proxy proxy_http headers ssl >/dev/null
  F=/etc/apache2/sites-available/$D.conf
  [ -e "$F" ] && cp "$F" "$F.bak.$(date +%s)"
  cat > "$F" <<APA
<VirtualHost *:80>
    ServerName $D
    ServerAlias www.$D
    ProxyPreserveHost On
    RequestHeader set X-Forwarded-Proto expr=%{REQUEST_SCHEME}
    ProxyPass / http://127.0.0.1:8085/
    ProxyPassReverse / http://127.0.0.1:8085/
</VirtualHost>
APA
  a2ensite -q $D.conf
  apachectl configtest && systemctl reload apache2
  command -v certbot >/dev/null || (apt-get update -qq && apt-get install -y -qq certbot python3-certbot-apache)
  certbot --apache -d $D -d www.$D --non-interactive --agree-tos --register-unsafely-without-email --redirect
else
  echo "Serveur web de l'hôte non reconnu. Voici ce qui écoute sur 80/443 :"; ss -ltnp | grep -E ':(80|443) '
  exit 1
fi
echo DOMAINE_OK
