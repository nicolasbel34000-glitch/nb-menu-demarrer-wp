<?php
/**
 * Main plugin class.
 *
 * Copyright (C) 2026 NBForm. Original author: NBForm.
 *
 * @package WP_Start_Menu
 * @author  NBForm
 * @license GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build and persist the current user's administration launcher.
 */
final class WP_Start_Menu {
	const USER_META_KEY = '_wpsm_layout';
	const NONCE_ACTION  = 'wpsm_save_layout';

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Applications visible to the current user.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	private $apps = array();

	/**
	 * Return the singleton instance.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register WordPress hooks.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_footer', array( $this, 'render_launcher' ) );
		add_action( 'wp_ajax_wpsm_save_layout', array( $this, 'save_layout' ) );
	}

	/**
	 * Load translations bundled with the plugin.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'wp-start-menu',
			false,
			dirname( plugin_basename( WPSM_FILE ) ) . '/languages'
		);
	}

	/**
	 * Load assets on authenticated admin screens only.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( ! is_admin() || ! is_user_logged_in() ) {
			return;
		}

		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style(
			'wp-start-menu',
			WPSM_URL . 'assets/admin.css',
			array( 'dashicons' ),
			WPSM_VERSION
		);
		wp_enqueue_script(
			'wp-start-menu',
			WPSM_URL . 'assets/admin.js',
			array(),
			WPSM_VERSION,
			true
		);
	}

	/**
	 * Render the launcher shell and expose the current user's visible admin menus.
	 *
	 * @return void
	 */
	public function render_launcher() {
		if ( ! is_admin() || ! is_user_logged_in() ) {
			return;
		}

		$this->apps = $this->get_visible_apps();
		$layout     = $this->get_layout( $this->apps );
		$config     = array(
			'apps'   => $this->apps,
			'layout' => $layout,
			'ajax'   => array(
				'url'    => admin_url( 'admin-ajax.php' ),
				'action' => 'wpsm_save_layout',
				'nonce'  => wp_create_nonce( self::NONCE_ACTION ),
			),
			'i18n'   => array(
				'organize'             => __( 'Organize', 'wp-start-menu' ),
				'done'                 => __( 'Done', 'wp-start-menu' ),
				'favorites'            => __( 'Favorites', 'wp-start-menu' ),
				'favoritesDescription' => __( 'Drag icons to set their priority order.', 'wp-start-menu' ),
				'addUrl'               => __( 'Add a URL', 'wp-start-menu' ),
				'emptyFavorites'       => __( 'Add an application from A–Z or create a URL shortcut.', 'wp-start-menu' ),
				'customShortcuts'      => __( 'Custom shortcuts', 'wp-start-menu' ),
				'customShortcutsHelp'  => __( 'Add them to favorites or drag them into a folder.', 'wp-start-menu' ),
				'folders'              => __( 'Folders', 'wp-start-menu' ),
				'folderDropHelp'       => __( 'Drop an application onto a folder.', 'wp-start-menu' ),
				'foldersDescription'   => __( 'Group applications and their shortcuts freely.', 'wp-start-menu' ),
				'newFolderButton'      => __( 'New folder', 'wp-start-menu' ),
				'emptyFolders'         => __( 'No folders yet.', 'wp-start-menu' ),
				'availableApps'        => __( 'Available applications', 'wp-start-menu' ),
				'availableAppsHelp'    => __( 'Drag an application to the desired folder.', 'wp-start-menu' ),
				'alphaNote'            => __( 'Top-level entries only — submenus excluded', 'wp-start-menu' ),
				'results'              => __( 'Results', 'wp-start-menu' ),
				'result'               => __( 'result', 'wp-start-menu' ),
				'resultsCount'         => __( 'results', 'wp-start-menu' ),
				'application'          => __( 'application', 'wp-start-menu' ),
				'applications'         => __( 'applications', 'wp-start-menu' ),
				'emptySearch'          => __( 'No results.', 'wp-start-menu' ),
				'emptyFolder'          => __( 'This folder is empty.', 'wp-start-menu' ),
				'saving'               => __( 'Saving…', 'wp-start-menu' ),
				'saved'                => __( 'Layout saved.', 'wp-start-menu' ),
				'saveError'            => __( 'Unable to save.', 'wp-start-menu' ),
				'removeFavorite'       => __( 'Remove from favorites', 'wp-start-menu' ),
				'addFavorite'          => __( 'Add to favorites', 'wp-start-menu' ),
				'openSubmenu'          => __( 'Show submenus', 'wp-start-menu' ),
				'removeFromFolder'     => __( 'Remove from folder', 'wp-start-menu' ),
				'newFolder'            => __( 'New folder name', 'wp-start-menu' ),
				'renameFolder'         => __( 'Rename folder', 'wp-start-menu' ),
				'deleteFolder'         => __( 'Delete this folder?', 'wp-start-menu' ),
				'shortcutName'         => __( 'Shortcut name', 'wp-start-menu' ),
				'shortcutUrl'          => __( 'Shortcut address', 'wp-start-menu' ),
				'invalidUrl'           => __( 'This address is not valid.', 'wp-start-menu' ),
				'deleteShortcut'       => __( 'Delete this shortcut?', 'wp-start-menu' ),
				'editShortcut'         => __( 'Edit shortcut', 'wp-start-menu' ),
			),
		);
		?>
		<div id="wpsm-root" class="wpsm-root" data-version="<?php echo esc_attr( WPSM_VERSION ); ?>">
			<button type="button" id="wpsm-trigger" class="wpsm-trigger" aria-haspopup="dialog" aria-expanded="false" aria-controls="wpsm-panel">
				<span class="wpsm-trigger-grid" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
				<span class="wpsm-trigger-label"><?php esc_html_e( 'Start', 'wp-start-menu' ); ?></span>
			</button>

			<section id="wpsm-panel" class="wpsm-panel" role="dialog" aria-modal="false" aria-labelledby="wpsm-title" hidden>
				<header class="wpsm-header">
					<div>
						<h2 id="wpsm-title"><?php esc_html_e( 'My applications', 'wp-start-menu' ); ?></h2>
						<p><?php esc_html_e( 'Your WordPress workspace', 'wp-start-menu' ); ?></p>
					</div>
					<div class="wpsm-header-actions">
						<button type="button" class="wpsm-button wpsm-organize" aria-pressed="false">
							<span class="dashicons dashicons-edit" aria-hidden="true"></span>
							<span><?php esc_html_e( 'Organize', 'wp-start-menu' ); ?></span>
						</button>
						<button type="button" class="wpsm-icon-button wpsm-close" aria-label="<?php esc_attr_e( 'Close', 'wp-start-menu' ); ?>">
							<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
						</button>
					</div>
				</header>

				<label class="wpsm-search-wrap">
					<span class="dashicons dashicons-search" aria-hidden="true"></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Search', 'wp-start-menu' ); ?></span>
					<input id="wpsm-search" type="search" autocomplete="off" placeholder="<?php esc_attr_e( 'Search for an action, page, or plugin…', 'wp-start-menu' ); ?>">
					<kbd>Ctrl K</kbd>
				</label>

				<nav class="wpsm-tabs" aria-label="<?php esc_attr_e( 'Start menu views', 'wp-start-menu' ); ?>">
					<button type="button" role="tab" id="wpsm-tab-favorites" aria-selected="true" aria-controls="wpsm-view-favorites" data-view="favorites"><?php esc_html_e( 'Favorites', 'wp-start-menu' ); ?></button>
					<button type="button" role="tab" id="wpsm-tab-folders" aria-selected="false" aria-controls="wpsm-view-folders" data-view="folders"><?php esc_html_e( 'Folders', 'wp-start-menu' ); ?></button>
					<button type="button" role="tab" id="wpsm-tab-alpha" aria-selected="false" aria-controls="wpsm-view-alpha" data-view="alpha"><?php esc_html_e( 'A–Z', 'wp-start-menu' ); ?></button>
				</nav>

				<div class="wpsm-content" aria-live="polite">
					<div id="wpsm-view-favorites" class="wpsm-view" role="tabpanel" aria-labelledby="wpsm-tab-favorites"></div>
					<div id="wpsm-view-folders" class="wpsm-view" role="tabpanel" aria-labelledby="wpsm-tab-folders" hidden></div>
					<div id="wpsm-view-alpha" class="wpsm-view" role="tabpanel" aria-labelledby="wpsm-tab-alpha" hidden></div>
					<div id="wpsm-view-search" class="wpsm-view" hidden></div>
				</div>

				<footer class="wpsm-footer">
					<span class="wpsm-status" role="status"></span>
					<button type="button" class="wpsm-all-apps"><?php esc_html_e( 'All applications', 'wp-start-menu' ); ?><span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span></button>
				</footer>
			</section>
		</div>
		<script type="application/json" id="wpsm-config"><?php echo wp_json_encode( $config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></script>
		<?php
	}

	/**
	 * Return top-level admin applications and their accessible submenus.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function get_visible_apps() {
		global $menu, $submenu;

		$apps = array();
		if ( ! is_array( $menu ) ) {
			return $apps;
		}

		foreach ( $menu as $entry ) {
			if ( empty( $entry[0] ) || empty( $entry[2] ) || ( ! empty( $entry[4] ) && false !== strpos( (string) $entry[4], 'wp-menu-separator' ) ) ) {
				continue;
			}

			$capability = isset( $entry[1] ) ? (string) $entry[1] : '';
			if ( '' !== $capability && ! current_user_can( $capability ) ) {
				continue;
			}

			$slug  = (string) $entry[2];
			$title = $this->clean_menu_title( $entry[0] );
			if ( '' === $title ) {
				continue;
			}

			$children = array();
			if ( isset( $submenu[ $slug ] ) && is_array( $submenu[ $slug ] ) ) {
				foreach ( $submenu[ $slug ] as $child ) {
					if ( empty( $child[0] ) || empty( $child[2] ) ) {
						continue;
					}

					$child_capability = isset( $child[1] ) ? (string) $child[1] : '';
					if ( '' !== $child_capability && ! current_user_can( $child_capability ) ) {
						continue;
					}

					$child_title = $this->clean_menu_title( $child[0] );
					if ( '' === $child_title ) {
						continue;
					}

					$children[] = array(
						'id'    => 'sub_' . substr( md5( $slug . '|' . $child[2] ), 0, 12 ),
						'title' => $child_title,
						'url'   => $this->menu_url( (string) $child[2], $slug ),
					);
				}
			}

			$apps[] = array(
				'id'       => 'app_' . substr( md5( $slug ), 0, 12 ),
				'slug'     => $slug,
				'title'    => $title,
				'url'      => $this->menu_url( $slug ),
				'icon'     => $this->normalize_icon( isset( $entry[6] ) ? (string) $entry[6] : '' ),
				'children' => $children,
			);
		}

		usort(
			$apps,
			static function ( $a, $b ) {
				return strnatcasecmp( remove_accents( $a['title'] ), remove_accents( $b['title'] ) );
			}
		);

		return $apps;
	}

	/**
	 * Build a safe admin URL from a menu slug.
	 *
	 * @param string $target Menu target.
	 * @param string $parent_slug Parent slug for plugin submenus.
	 * @return string
	 */
	private function menu_url( $target, $parent_slug = '' ) {
		if ( preg_match( '#^https?://#i', $target ) ) {
			return esc_url_raw( $target );
		}

		if ( false !== strpos( $target, '.php' ) ) {
			return self_admin_url( ltrim( $target, '/' ) );
		}

		if ( $parent_slug && false !== strpos( $parent_slug, '.php' ) ) {
			$parent_file = (string) wp_parse_url( $parent_slug, PHP_URL_PATH );
			if ( 'admin.php' === basename( $parent_file ) ) {
				return self_admin_url( 'admin.php?page=' . rawurlencode( $target ) );
			}

			return self_admin_url( add_query_arg( 'page', $target, $parent_slug ) );
		}

		return self_admin_url( 'admin.php?page=' . rawurlencode( $target ) );
	}

	/**
	 * Strip counters and markup injected into WordPress menu labels.
	 *
	 * @param mixed $title Raw menu title.
	 * @return string
	 */
	private function clean_menu_title( $title ) {
		$title = wp_strip_all_tags( (string) $title, true );
		$title = preg_replace( '/\s+[0-9]+(?:\s+[^\s]+)?$/u', '', $title );
		return trim( html_entity_decode( $title, ENT_QUOTES, get_bloginfo( 'charset' ) ) );
	}

	/**
	 * Only expose a Dashicons class. Other icon formats get a safe fallback.
	 *
	 * @param string $icon Menu icon.
	 * @return string
	 */
	private function normalize_icon( $icon ) {
		if ( preg_match( '/(?:^|\s)(dashicons-[a-z0-9-]+)/', $icon, $matches ) ) {
			return $matches[1];
		}

		return 'dashicons-admin-generic';
	}

	/**
	 * Read and normalize the current user's layout.
	 *
	 * @param array<int,array<string,mixed>> $apps Visible apps.
	 * @return array<string,mixed>
	 */
	private function get_layout( $apps ) {
		$stored    = get_user_meta( get_current_user_id(), self::USER_META_KEY, true );
		$valid_ids = wp_list_pluck( $apps, 'id' );

		if ( ! is_array( $stored ) ) {
			return $this->default_layout( $apps );
		}

		$shortcuts = $this->sanitize_shortcuts( isset( $stored['shortcuts'] ) ? $stored['shortcuts'] : array() );
		$valid_ids = array_merge( $valid_ids, wp_list_pluck( $shortcuts, 'id' ) );
		$favorites = isset( $stored['favorites'] ) && is_array( $stored['favorites'] ) ? array_slice( $stored['favorites'], 0, 200 ) : array();
		$favorites = array_values( array_intersect( array_map( 'sanitize_key', $favorites ), $valid_ids ) );
		$folders   = $this->sanitize_folders( isset( $stored['folders'] ) ? $stored['folders'] : array(), $valid_ids );

		return array(
			'favorites' => array_values( array_unique( $favorites ) ),
			'folders'   => $folders,
			'shortcuts' => $shortcuts,
		);
	}

	/**
	 * Create useful folders and favorites on first use.
	 *
	 * @param array<int,array<string,mixed>> $apps Visible apps.
	 * @return array<string,mixed>
	 */
	private function default_layout( $apps ) {
		$content = array();
		$admin   = array();
		$seo     = array();
		$shop    = array();

		foreach ( $apps as $app ) {
			$haystack = strtolower( remove_accents( $app['slug'] . ' ' . $app['title'] ) );
			if ( preg_match( '/edit\.php|upload\.php|media|post|page|comment/', $haystack ) ) {
				$content[] = $app['id'];
			}
			if ( preg_match( '/plugin|theme|user|option|setting|tool/', $haystack ) ) {
				$admin[] = $app['id'];
			}
			if ( preg_match( '/seo|yoast/', $haystack ) ) {
				$seo[] = $app['id'];
			}
			if ( preg_match( '/woocommerce|commerce|shop|boutique|product/', $haystack ) ) {
				$shop[] = $app['id'];
			}
		}

		$folders = array(
			array(
				'id'    => 'content',
				'name'  => __( 'Content', 'wp-start-menu' ),
				'items' => array_values( array_unique( $content ) ),
			),
			array(
				'id'    => 'administration',
				'name'  => __( 'Administration', 'wp-start-menu' ),
				'items' => array_values( array_unique( $admin ) ),
			),
		);
		if ( $seo ) {
			$folders[] = array(
				'id'    => 'seo',
				'name'  => 'SEO',
				'items' => array_values( array_unique( $seo ) ),
			);
		}
		if ( $shop ) {
			$folders[] = array(
				'id'    => 'shop',
				'name'  => __( 'Store', 'wp-start-menu' ),
				'items' => array_values( array_unique( $shop ) ),
			);
		}

		return array(
			'favorites' => array_slice( wp_list_pluck( $apps, 'id' ), 0, 6 ),
			'folders'   => $folders,
			'shortcuts' => array(),
		);
	}

	/**
	 * Sanitize user-defined shortcuts.
	 *
	 * @param mixed $shortcuts Raw shortcuts.
	 * @return array<int,array<string,string>>
	 */
	private function sanitize_shortcuts( $shortcuts ) {
		$clean = array();
		if ( ! is_array( $shortcuts ) ) {
			return $clean;
		}

		$seen_ids = array();
		foreach ( array_slice( $shortcuts, 0, 50 ) as $index => $shortcut ) {
			if ( ! is_array( $shortcut ) || empty( $shortcut['title'] ) || empty( $shortcut['url'] ) ) {
				continue;
			}

			$title = sanitize_text_field( $shortcut['title'] );
			$url   = esc_url_raw( $shortcut['url'], array( 'http', 'https', 'mailto', 'tel' ) );
			if ( '' === $title || '' === $url ) {
				continue;
			}

			$id = isset( $shortcut['id'] ) ? sanitize_key( $shortcut['id'] ) : '';
			if ( ! preg_match( '/^shortcut_[a-z0-9_]{6,64}$/', $id ) ) {
				$id = 'shortcut_' . substr( md5( $title . '|' . $url . '|' . $index ), 0, 12 );
			}
			if ( isset( $seen_ids[ $id ] ) ) {
				continue;
			}
			$seen_ids[ $id ] = true;

			$clean[] = array(
				'id'    => $id,
				'title' => function_exists( 'mb_substr' ) ? mb_substr( $title, 0, 60 ) : substr( $title, 0, 60 ),
				'url'   => $url,
			);
		}

		return $clean;
	}

	/**
	 * Sanitize folders against apps the current user can actually access.
	 *
	 * @param mixed         $folders Raw folders.
	 * @param array<string> $valid_ids Allowed app IDs.
	 * @param bool          $allow_app_ids Whether deterministic app IDs may be retained during AJAX.
	 * @return array<int,array<string,mixed>>
	 */
	private function sanitize_folders( $folders, $valid_ids, $allow_app_ids = false ) {
		$clean = array();
		if ( ! is_array( $folders ) ) {
			return $clean;
		}

		foreach ( array_slice( $folders, 0, 30 ) as $folder ) {
			if ( ! is_array( $folder ) || empty( $folder['name'] ) ) {
				continue;
			}

			$name      = sanitize_text_field( $folder['name'] );
			$folder_id = ! empty( $folder['id'] ) ? substr( sanitize_key( $folder['id'] ), 0, 64 ) : '';
			if ( '' === $folder_id ) {
				$folder_id = 'folder_' . wp_generate_password( 8, false, false );
			}
			$items   = isset( $folder['items'] ) && is_array( $folder['items'] ) ? array_slice( $folder['items'], 0, 200 ) : array();
			$items   = array_values(
				array_unique(
					array_filter(
						array_map( 'sanitize_key', $items ),
						static function ( $id ) use ( $valid_ids, $allow_app_ids ) {
							return in_array( $id, $valid_ids, true ) || ( $allow_app_ids && preg_match( '/^app_[a-f0-9]{12}$/', $id ) );
						}
					)
				)
			);
			$clean[] = array(
				'id'    => $folder_id,
				'name'  => function_exists( 'mb_substr' ) ? mb_substr( $name, 0, 40 ) : substr( $name, 0, 40 ),
				'items' => $items,
			);
		}

		return $clean;
	}

	/**
	 * AJAX callback: save the current user's layout only.
	 *
	 * @return void
	 */
	public function save_layout() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		if ( ! is_user_logged_in() || ! current_user_can( 'read' ) ) {
			wp_send_json_error( array( 'message' => __( 'Session expired.', 'wp-start-menu' ) ), 403 );
		}

		$raw = isset( $_POST['layout'] ) ? wp_unslash( $_POST['layout'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! is_string( $raw ) || strlen( $raw ) > 50000 ) {
			wp_send_json_error( array( 'message' => __( 'Invalid data.', 'wp-start-menu' ) ), 400 );
		}

		$layout = json_decode( $raw, true );
		if ( ! is_array( $layout ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid data.', 'wp-start-menu' ) ), 400 );
		}

		// admin-ajax.php does not build the global WordPress admin menu. App IDs
		// are therefore validated by their deterministic format during saving,
		// then intersected with the user's actual visible menu on the next render.
		$shortcuts    = $this->sanitize_shortcuts( isset( $layout['shortcuts'] ) ? $layout['shortcuts'] : array() );
		$shortcut_ids = wp_list_pluck( $shortcuts, 'id' );
		$favorites    = isset( $layout['favorites'] ) && is_array( $layout['favorites'] ) ? array_slice( $layout['favorites'], 0, 200 ) : array();
		$favorites    = array_values(
			array_unique(
				array_filter(
					array_map( 'sanitize_key', $favorites ),
					static function ( $id ) use ( $shortcut_ids ) {
						return in_array( $id, $shortcut_ids, true ) || preg_match( '/^app_[a-f0-9]{12}$/', $id );
					}
				)
			)
		);
		$folders      = $this->sanitize_folders( isset( $layout['folders'] ) ? $layout['folders'] : array(), $shortcut_ids, true );

		update_user_meta(
			get_current_user_id(),
			self::USER_META_KEY,
			array(
				'favorites' => $favorites,
				'folders'   => $folders,
				'shortcuts' => $shortcuts,
			)
		);

		wp_send_json_success( array( 'message' => __( 'Layout saved.', 'wp-start-menu' ) ) );
	}
}
