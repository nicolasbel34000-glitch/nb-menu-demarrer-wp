<?php
/**
 * Remove NB Menu démarrer WP user preferences on uninstall.
 *
 * @package WP_Start_Menu
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_metadata( 'user', 0, '_wpsm_layout', '', true );
