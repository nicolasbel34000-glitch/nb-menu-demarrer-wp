=== WP Start Menu ===
Contributors: nbform
Tags: admin, dashboard, menu, launcher, productivity
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 0.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Un menu Démarrer organisable pour l’administration WordPress : favoris, dossiers, recherche et liste A–Z.

== Description ==

WP Start Menu ajoute une surcouche d’organisation sans remplacer le menu WordPress d’origine.

WP Start Menu a été créé initialement par NBForm. Copyright 2026 NBForm. Les forks et modifications sont autorisés selon la GPLv2 ou toute version ultérieure ; les contributeurs peuvent être crédités pour leurs propres modifications sans effacer l’attribution de la version originale.

* Favoris ordonnables.
* Dossiers personnels.
* Raccourcis personnalisés vers des URL internes ou externes.
* Recherche dans les menus et sous-menus accessibles.
* Liste alphabétique des entrées principales uniquement.
* Préférences enregistrées par utilisateur.
* Interface accessible et responsive.

== Installation ==

1. Téléversez le dossier dans `/wp-content/plugins/`.
2. Activez le plugin depuis la page Extensions.
3. Utilisez le bouton Démarrer en bas à gauche de l’administration.

== Frequently Asked Questions ==

= Le plugin remplace-t-il le menu WordPress ? =

Non. Le menu natif reste intact et utilisable.

= Pourquoi certains éléments n’apparaissent-ils pas ? =

Le lanceur respecte les droits du compte connecté et ne montre que les menus auxquels ce compte a accès.

= La liste A–Z contient-elle les sous-menus ? =

Non. Elle contient uniquement les entrées principales. Les sous-menus restent accessibles depuis les applications et la recherche.

== Changelog ==

= 0.2.1 =

* Le lanceur reste ouvert lorsqu’un favori est ajouté ou retiré depuis une vue reconstruite dynamiquement.

= 0.2.0 =

* Correction de la persistance des favoris et dossiers pendant les requêtes AJAX.
* Ajout de raccourcis URL personnalisés, modifiables et utilisables dans les favoris et dossiers.
* Sauvegarde renforcée lors d’un changement de page.

= 0.1.2 =

* Le bouton Démarrer reste visible en permanence en bas de la colonne WordPress sans recouvrir les autres liens.

= 0.1.1 =

* Le lanceur devient une surcouche non modale et ne bloque plus les liens de l’administration.
* Le bouton Démarrer est intégré au menu WordPress au lieu de recouvrir son contenu.

= 0.1.0 =

* Première version fonctionnelle.
