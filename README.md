# WP Start Menu

WP Start Menu ajoute un lanceur d’applications organisable à l’administration WordPress sans remplacer le menu natif.

## Fonctionnalités

- bouton **Démarrer** persistant en bas de l’administration ;
- favoris ordonnables par glisser-déposer ;
- dossiers personnels avec ajout, renommage et suppression ;
- raccourcis personnalisés vers des URL internes ou externes ;
- recherche dans les menus et sous-menus accessibles à l’utilisateur ;
- index A–Z limité aux entrées principales — les sous-menus en sont volontairement exclus ;
- accès aux sous-menus depuis les tuiles des applications ;
- organisation enregistrée séparément pour chaque utilisateur ;
- interface responsive et navigation au clavier (`Ctrl/Cmd + K`, `Échap`).

Le plugin ne modifie ni l’ordre ni la visibilité du menu WordPress d’origine. Il construit son lanceur à partir des pages auxquelles l’utilisateur courant a déjà accès.

## Auteur original et forks

**WP Start Menu a été créé initialement par NBForm.**

Copyright © 2026 NBForm. Les forks, modifications et redistributions sont autorisés selon les conditions de la licence GPL-2.0-or-later. Les versions modifiées peuvent créditer leurs contributeurs, tout en conservant les mentions de copyright et de licence de la version originale et en signalant leurs propres modifications.

## Installation

1. Copier le dossier `wp-start-menu` dans `wp-content/plugins/`.
2. Activer **WP Start Menu** depuis la page Extensions.
3. Ouvrir une page de l’administration et cliquer sur **Démarrer** en bas à gauche.

## Utilisation

Le bouton **Organiser** active les actions de personnalisation :

- faire glisser les favoris pour les classer ;
- cliquer sur l’étoile d’une application dans A–Z pour l’ajouter ou la retirer des favoris ;
- utiliser « Ajouter une URL » pour créer, modifier ou supprimer un raccourci personnel ;
- créer un dossier, puis y déposer des applications ;
- ouvrir un dossier pour renommer, supprimer ou retirer des éléments.

Les changements sont sauvegardés automatiquement dans les métadonnées du compte WordPress connecté.

## Développement

Le plugin n’a aucune dépendance de compilation : PHP, CSS et JavaScript natif uniquement.

```bash
php -l wp-start-menu.php
php -l includes/class-wp-start-menu.php
node --check assets/admin.js
```

## Licence

GPL-2.0-or-later. Consultez `LICENSE` pour le texte intégral et `NOTICE` pour l’attribution d’origine.
