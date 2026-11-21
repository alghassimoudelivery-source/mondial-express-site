# Mondial Express Guinée — site WordPress et application mobile

Thème WordPress sur mesure (site + application installable sur `/app/` + API `mondial/v1`), contenu de démonstration en 4 langues (FR, EN, ES, AR) et installation Docker.

- `theme/mondial-express/` : le thème.
- `seed/` : contenu de démonstration (à lancer une seule fois, automatique avec Docker).
- `docker-compose.yml` + `setup.sh` : WordPress + MariaDB. Les mots de passe sont fournis par l'environnement du projet Docker, jamais dans ce dépôt.

Mise à jour du site : pousser sur `main`, puis relancer le projet Docker (le service `fetch` récupère le dépôt et `setup` recopie le thème ; le contenu n'est jamais recréé).

Réalisé par ConforDev — https://confordev.com
