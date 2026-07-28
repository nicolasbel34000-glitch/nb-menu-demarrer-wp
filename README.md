# NB Menu démarrer WP

**English** | [Français](README.fr.md)

NB Menu démarrer WP adds an organized application launcher to the WordPress administration area without replacing the native menu.

**Stable version: 1.1.0 — GPL-2.0-or-later**

## Features

- persistent **Start** button at the bottom of the administration menu;
- drag-and-drop favorites;
- personal folders that can be added, renamed, and deleted;
- custom shortcuts to internal or external URLs;
- search across the menus and submenus available to the current user;
- A–Z index limited to top-level entries;
- submenu access directly from application tiles;
- a separate layout for every WordPress user;
- responsive interface and keyboard navigation (`Ctrl/Cmd + K`, `Escape`).

The plugin does not change the order or visibility of the native WordPress menu. It builds the launcher exclusively from pages the current user is already allowed to access.

## Requirements

- WordPress 6.5 or later;
- PHP 7.4 or later;
- JavaScript enabled in the browser;
- an account allowed to access WordPress administration.

## Original author and forks

**NB Menu démarrer WP was originally created by NBForm.**

Copyright © 2026 NBForm. Forks, modifications, and redistribution are allowed under GPL-2.0-or-later. Modified versions may credit their contributors while retaining the copyright and license notices that apply to the original version and documenting their own changes.

## Installation

1. Copy the `wp-start-menu` directory to `wp-content/plugins/`.
2. Activate **NB Menu démarrer WP** from the Plugins screen.
3. Open any administration screen and select **Start** at the bottom left.

The latest installable archive is available from [GitHub Releases](https://github.com/nicolasbel34000-glitch/nb-menu-demarrer-wp/releases).

## Usage

Select **Organize** to customize the launcher:

- drag favorites to reorder them;
- select an application’s star in A–Z to add it to or remove it from favorites;
- use “Add a URL” to create, edit, or delete a personal shortcut;
- create a folder, then drag applications into it;
- open a folder to rename or delete it, or remove its items.

Changes are saved automatically in the metadata of the connected WordPress account.

## Languages

The interface automatically follows the WordPress site language:

- English: source language;
- French: bundled translation.

## Permissions and privacy

- users only see menus allowed by their existing WordPress capabilities;
- the plugin grants no new permission and changes no role;
- each layout is stored separately in the connected account’s metadata;
- no data is sent to NBForm or any third-party service;
- no cookie, telemetry, or tracking system is added;
- uninstalling the plugin removes the preferences it stored.

## Architecture

The project intentionally remains compact and has no runtime dependency:

```text
wp-start-menu.php                 plugin bootstrap
includes/class-wp-start-menu.php WordPress logic, permissions, and persistence
assets/admin.js                  launcher interactions
assets/admin.css                 responsive presentation
languages/                       translation template and French translation
uninstall.php                    uninstall cleanup
```

## Development

The plugin has no build dependency: it uses native PHP, CSS, and JavaScript only.

```bash
php -l wp-start-menu.php
php -l includes/class-wp-start-menu.php
node --check assets/admin.js
php tools/check-release.php
composer phpcs
```

CI checks PHP 7.4, 8.3, and 8.4. A `vX.Y.Z` tag automatically produces an installable archive and its SHA-256 checksum.

See [CONTRIBUTING.md](CONTRIBUTING.md), [SECURITY.md](SECURITY.md), and [CHANGELOG.md](CHANGELOG.md).

## License

GPL-2.0-or-later. See `LICENSE` for the full license and `NOTICE` for original attribution.
