# Traductions

Le fichier `wp-start-menu.pot` contient toutes les chaînes traduisibles.

Pour ajouter une langue, créez les fichiers `wp-start-menu-LOCALE.po` et `wp-start-menu-LOCALE.mo`, par exemple `wp-start-menu-en_US.po` et `wp-start-menu-en_US.mo`.

Le catalogue source se régénère avec :

```bash
wp i18n make-pot . languages/wp-start-menu.pot --domain=wp-start-menu
```
