<?php
/**
 * Lightweight, dependency-free release checks.
 *
 * @package WP_Start_Menu
 */

$root     = dirname( __DIR__ );
$expected = isset( $argv[1] ) ? ltrim( (string) $argv[1], 'v' ) : '';
$errors   = array();

$required = array(
	'wp-start-menu.php',
	'includes/class-wp-start-menu.php',
	'assets/admin.js',
	'assets/admin.css',
	'readme.txt',
	'README.md',
	'LICENSE',
	'NOTICE',
	'SECURITY.md',
	'uninstall.php',
);

foreach ( $required as $relative ) {
	if ( ! is_file( $root . DIRECTORY_SEPARATOR . $relative ) ) {
		$errors[] = 'Fichier requis absent : ' . $relative;
	}
}

$main   = file_get_contents( $root . '/wp-start-menu.php' );
$readme = file_get_contents( $root . '/readme.txt' );

preg_match( '/^\s*\*\s+Version:\s+([0-9]+\.[0-9]+\.[0-9]+)/mi', $main, $header_match );
preg_match( "/define\\(\\s*'WPSM_VERSION',\\s*'([^']+)'\\s*\\)/", $main, $constant_match );
preg_match( '/^Stable tag:\s+([0-9]+\.[0-9]+\.[0-9]+)/mi', $readme, $stable_match );

$versions = array_filter(
	array(
		isset( $header_match[1] ) ? $header_match[1] : '',
		isset( $constant_match[1] ) ? $constant_match[1] : '',
		isset( $stable_match[1] ) ? $stable_match[1] : '',
	)
);

if ( 3 !== count( $versions ) || 1 !== count( array_unique( $versions ) ) ) {
	$errors[] = 'Les versions du fichier principal et du readme ne correspondent pas.';
}

$version = $versions ? reset( $versions ) : '';
if ( $expected && $version !== $expected ) {
	$errors[] = sprintf( 'La version %s ne correspond pas au tag attendu %s.', $version, $expected );
}

$iterator = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS )
);

$secret_patterns = array(
	'/-----BEGIN ' . '(?:RSA |EC |OPENSSH )?PRIVATE KEY-----/',
	'/sk-' . '[A-Za-z0-9_-]{20,}/',
	'/gh' . '[opsu]_[A-Za-z0-9]{30,}/',
	'/AKIA' . '[A-Z0-9]{16}/',
);
$dangerous_patterns = array(
	'/\b(?:eval|exec|shell_exec|passthru|proc_open|popen)\s*\(/i',
	'/\bbase64_decode\s*\(/i',
);
$required_security_controls = array(
	"check_ajax_referer( self::NONCE_ACTION, 'nonce' )",
	"current_user_can( 'read' )",
	'current_user_can( $capability )',
	'current_user_can( $child_capability )',
	'sanitize_text_field',
	'esc_url_raw',
	'wp_json_encode',
);

$main_class = file_get_contents( $root . '/includes/class-wp-start-menu.php' );
foreach ( $required_security_controls as $control ) {
	if ( false === strpos( $main_class, $control ) ) {
		$errors[] = 'Contrôle de sécurité absent : ' . $control;
	}
}

foreach ( $iterator as $file ) {
	if ( ! $file->isFile() ) {
		continue;
	}

	$relative = str_replace( '\\', '/', substr( $file->getPathname(), strlen( $root ) + 1 ) );
	if ( preg_match( '#^(?:\.git|vendor|node_modules|dist)/#', $relative ) ) {
		continue;
	}

	if ( $file->getSize() > 250000 && 'LICENSE' !== $relative ) {
		$errors[] = 'Fichier anormalement lourd : ' . $relative;
	}

	$extension = strtolower( pathinfo( $relative, PATHINFO_EXTENSION ) );
	if ( in_array( $extension, array( 'php', 'js', 'css' ), true ) ) {
		$line_count = count( file( $file->getPathname() ) );
		if ( $line_count > 1500 ) {
			$errors[] = sprintf( 'Fichier trop long (%d lignes) : %s', $line_count, $relative );
		}
	}

	if ( preg_match( '/(?:^|\/)(?:\.env(?:\.|$)|wp-config\.php$)|\.(?:pem|key|p12|pfx|sql|sqlite|bak|backup|zip)$/i', $relative ) ) {
		$errors[] = 'Fichier sensible ou archive suivi par Git : ' . $relative;
		continue;
	}

	$content = file_get_contents( $file->getPathname() );
	foreach ( $secret_patterns as $pattern ) {
		if ( preg_match( $pattern, $content ) ) {
			$errors[] = 'Secret potentiel détecté dans : ' . $relative;
			break;
		}
	}

	if ( 'php' === $extension && 0 !== strpos( $relative, 'tools/' ) ) {
		foreach ( $dangerous_patterns as $pattern ) {
			if ( preg_match( $pattern, $content ) ) {
				$errors[] = 'Primitive PHP dangereuse détectée dans : ' . $relative;
				break;
			}
		}
	}
}

if ( $errors ) {
	fwrite( STDERR, implode( PHP_EOL, array_unique( $errors ) ) . PHP_EOL );
	exit( 1 );
}

printf( "Contrôles de publication réussis pour WP Start Menu %s.%s", $version, PHP_EOL );
