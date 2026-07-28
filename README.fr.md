# NB Menu démarrer WP

[English](README.md) | **Français**

NB Menu démarrer WP ajoute un lanceur d’applications organisable à l’administration WordPress sans remplacer le menu natif.

**Version stable : 1.1.0 — GPL-2.0-or-later**

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

## Prérequis

- WordPress 6.5 ou plus récent ;
- PHP 7.4 ou plus récent ;
- JavaScript activé dans le navigateur ;
- un compte autorisé à accéder à l’administration WordPress.

## Auteur original et forks

**NB Menu démarrer WP a été créé initialement par NBForm.**

Copyright © 2026 NBForm. Les forks, modifications et redistributions sont autorisés selon les conditions de la licence GPL-2.0-or-later. Les versions modifiées peuvent créditer leurs contributeurs, tout en conservant les mentions de copyright et de licence de la version originale et en signalant leurs propres modifications.

## Installation

1. Copier le dossier `wp-start-menu` dans `wp-content/plugins/`.
2. Activer **NB Menu démarrer WP** depuis la page Extensions.
3. Ouvrir une page de l’administration et cliquer sur **Démarrer** en bas à gauche.

La dernière archive prête à installer est publiée dans les [releases GitHub](https://github.com/nicolasbel34000-glitch/nb-menu-demarrer-wp/releases).

## Utilisation

Le bouton **Organiser** active les actions de personnalisation :

- faire glisser les favoris pour les classer ;
- cliquer sur l’étoile d’une application dans A–Z pour l’ajouter ou la retirer des favoris ;
- utiliser « Ajouter une URL » pour créer, modifier ou supprimer un raccourci personnel ;
- créer un dossier, puis y déposer des applications ;
- ouvrir un dossier pour renommer, supprimer ou retirer des éléments.

Les changements sont sauvegardés automatiquement dans les métadonnées du compte WordPress connecté.

## Langues

L’interface utilise automatiquement la langue du site WordPress :

- anglais : langue source ;
- français : traduction fournie dans l’extension.

## Droits et confidentialité

- chaque utilisateur ne voit que les menus déjà autorisés par ses capacités WordPress ;
- le plugin n’accorde aucun nouveau droit et ne modifie aucun rôle ;
- l’organisation est enregistrée séparément dans les métadonnées du compte connecté ;
- aucune donnée n’est transmise à NBForm ou à un service tiers ;
- aucun cookie, aucune télémétrie et aucun système de suivi ne sont ajoutés ;
- la désinstallation supprime les préférences enregistrées par le plugin.

## Architecture

Le projet reste volontairement compact et sans dépendance d’exécution :

```text
wp-start-menu.php                 charge l’extension
includes/class-wp-start-menu.php logique WordPress, droits et sauvegarde
assets/admin.js                  interactions du lanceur
assets/admin.css                 présentation responsive
languages/                       catalogue et traduction française
uninstall.php                    nettoyage à la désinstallation
```

## Développement

Le plugin n’a aucune dépendance de compilation : PHP, CSS et JavaScript natif uniquement.

```bash
php -l wp-start-menu.php
php -l includes/class-wp-start-menu.php
node --check assets/admin.js
php tools/check-release.php
composer phpcs
```

La CI teste PHP 7.4, 8.3 et 8.4. Un tag `vX.Y.Z` produit automatiquement une archive installable et sa somme SHA-256.

Consultez également [CONTRIBUTING.md](CONTRIBUTING.md), [SECURITY.md](SECURITY.md) et [CHANGELOG.md](CHANGELOG.md).

## Licence

GPL-2.0-or-later. Consultez `LICENSE` pour le texte intégral et `NOTICE` pour l’attribution d’origine.
