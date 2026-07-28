# Translations

English is the plugin source language. The bundled `wp-start-menu-fr_FR.po` and `wp-start-menu-fr_FR.mo` files provide the complete French interface.

`wp-start-menu.pot` contains every translatable source string.

To add another language, create `wp-start-menu-LOCALE.po` and `wp-start-menu-LOCALE.mo`.

Regenerate the source catalog with:

```bash
wp i18n make-pot . languages/wp-start-menu.pot --domain=wp-start-menu
```

Compile translation files with:

```bash
wp i18n make-mo languages
```
