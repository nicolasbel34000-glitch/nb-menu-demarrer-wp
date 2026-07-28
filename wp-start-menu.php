<?php
/**
 * Plugin Name:       NB Menu démarrer WP
 * Plugin URI:        https://github.com/nicolasbel34000-glitch/nb-menu-demarrer-wp
 * Description:       Un menu Démarrer organisable pour l'administration WordPress, avec favoris, dossiers, recherche et index alphabétique.
 * Version:           1.0.1
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            NBForm
 * Copyright:         Copyright (C) 2026 NBForm
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-start-menu
 * Domain Path:       /languages
 *
 * @package WP_Start_Menu
 */

defined( 'ABSPATH' ) || exit;

define( 'WPSM_VERSION', '1.0.1' );
define( 'WPSM_FILE', __FILE__ );
define( 'WPSM_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPSM_URL', plugin_dir_url( __FILE__ ) );

require_once WPSM_DIR . 'includes/class-wp-start-menu.php';

WP_Start_Menu::instance();
