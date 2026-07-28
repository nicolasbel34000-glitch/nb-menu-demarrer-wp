# Contribuer à NB Menu démarrer WP

Merci de proposer les corrections dans une branche dédiée et une pull request ciblée.

## Avant de proposer une modification

1. Ne jamais ajouter de mot de passe, jeton, clé privée, sauvegarde ou configuration de production.
2. Conserver la compatibilité PHP 7.4 et WordPress 6.5 au minimum.
3. Utiliser les API WordPress pour l’autorisation, l’échappement, les nonces et la traduction.
4. Ne pas introduire de dépendance d’exécution sans justification.
5. Préserver la séparation actuelle : logique PHP dans `includes/`, comportement dans `assets/admin.js` et présentation dans `assets/admin.css`.

## Vérifications locales

```bash
php -l wp-start-menu.php
php -l includes/class-wp-start-menu.php
php -l uninstall.php
node --check assets/admin.js
php tools/check-release.php
composer phpcs
```

La CI GitHub répète ces contrôles sur plusieurs versions de PHP.

## Traductions

Les textes affichés doivent utiliser le domaine `wp-start-menu`. Le catalogue se régénère avec WP-CLI :

```bash
wp i18n make-pot . languages/wp-start-menu.pot --domain=wp-start-menu
```

## Licence

Toute contribution est publiée sous GPL-2.0-or-later, comme le reste du projet.
