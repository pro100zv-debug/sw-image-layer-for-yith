<?php
/**
 * Actualizaciones desde los releases de GitHub.
 *
 * Cada release publicado en GitHub lleva adjunto el archivo
 * sw-image-layer-for-yith.zip (lo crea la acción .github/workflows/release.yml).
 * WordPress muestra «Actualizar» cuando la versión del release es mayor que la instalada.
 *
 * @package SW_Image_Layer
 */

defined( 'ABSPATH' ) || exit;

class SWIL_Updater {

	const ASSET = 'sw-image-layer-for-yith.zip';
	const CACHE = 'swil_github_release';

	public static function init() {
		add_filter( 'pre_set_site_transient_update_plugins', array( __CLASS__, 'check' ) );
		add_filter( 'plugins_api', array( __CLASS__, 'info' ), 20, 3 );
		add_action( 'upgrader_process_complete', array( __CLASS__, 'purge' ), 10, 2 );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'row_meta' ), 10, 2 );
		add_action( 'admin_init', array( __CLASS__, 'check_now' ) );
	}

	/**
	 * Último release de GitHub (con caché).
	 *
	 * @param bool $force Ignorar la caché.
	 * @return array|null
	 */
	private static function release( $force = false ) {
		$cached = get_site_transient( self::CACHE );
		if ( ! $force && false !== $cached ) {
			return is_array( $cached ) ? $cached : null;
		}

		$response = wp_remote_get(
			'https://api.github.com/repos/' . SWIL_REPO . '/releases/latest',
			array(
				'timeout' => 10,
				'headers' => array(
					'Accept'     => 'application/vnd.github+json',
					'User-Agent' => 'SW-Image-Layer-for-YITH',
				),
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			set_site_transient( self::CACHE, 'none', HOUR_IN_SECONDS );
			return null;
		}

		$data    = json_decode( wp_remote_retrieve_body( $response ), true );
		$package = '';

		if ( ! empty( $data['assets'] ) && is_array( $data['assets'] ) ) {
			foreach ( $data['assets'] as $asset ) {
				if ( isset( $asset['name'], $asset['browser_download_url'] ) && self::ASSET === $asset['name'] ) {
					$package = $asset['browser_download_url'];
					break;
				}
			}
		}

		if ( empty( $data['tag_name'] ) || ! $package ) {
			// Release sin ZIP todavía (la acción tarda unos segundos): volver a mirar en 15 minutos.
			set_site_transient( self::CACHE, 'none', 15 * MINUTE_IN_SECONDS );
			return null;
		}

		$release = array(
			'version'   => ltrim( $data['tag_name'], 'vV' ),
			'package'   => $package,
			'url'       => isset( $data['html_url'] ) ? $data['html_url'] : 'https://github.com/' . SWIL_REPO,
			'body'      => isset( $data['body'] ) ? (string) $data['body'] : '',
			'published' => isset( $data['published_at'] ) ? $data['published_at'] : '',
		);

		set_site_transient( self::CACHE, $release, 6 * HOUR_IN_SECONDS );
		return $release;
	}

	private static function assets_url( $file ) {
		return 'https://raw.githubusercontent.com/' . SWIL_REPO . '/main/.wp-assets/' . $file;
	}

	private static function item( $release ) {
		return (object) array(
			'id'            => 'github.com/' . SWIL_REPO,
			'slug'          => SWIL_SLUG,
			'plugin'        => SWIL_BASENAME,
			'new_version'   => $release['version'],
			'url'           => 'https://github.com/' . SWIL_REPO,
			'package'       => $release['package'],
			'icons'         => array(
				'1x' => self::assets_url( 'icon-128x128.png' ),
				'2x' => self::assets_url( 'icon-256x256.png' ),
			),
			'banners'       => array(
				'low'  => self::assets_url( 'banner-772x250.png' ),
				'high' => self::assets_url( 'banner-1544x500.png' ),
			),
			'requires_php'  => '7.4',
			'compatibility' => new stdClass(),
		);
	}

	public static function check( $transient ) {
		if ( ! is_object( $transient ) || empty( $transient->checked ) ) {
			return $transient;
		}

		$release = self::release();
		if ( ! $release ) {
			return $transient;
		}

		$item = self::item( $release );
		if ( version_compare( $release['version'], SWIL_VERSION, '>' ) ) {
			$transient->response[ SWIL_BASENAME ] = $item;
			unset( $transient->no_update[ SWIL_BASENAME ] );
		} else {
			$transient->no_update[ SWIL_BASENAME ] = $item;
			unset( $transient->response[ SWIL_BASENAME ] );
		}

		return $transient;
	}

	/**
	 * Ventana «Ver detalles».
	 */
	public static function info( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) || SWIL_SLUG !== $args->slug ) {
			return $result;
		}

		$release = self::release();
		$version = $release ? $release['version'] : SWIL_VERSION;

		$items = array(
			__( 'Places the image chosen in YITH Product Add-ons (for example, an embroidered crest) on the first photo of the product gallery.', 'sw-image-layer-for-yith' ),
			__( 'Shown on every product where YITH shows the add-on, with a default position.', 'sw-image-layer-for-yith' ),
			__( 'Per product: own position (X, Y) and width in percent, or a checkbox to hide the image.', 'sw-image-layer-for-yith' ),
			__( 'Search box that ignores case and accents, scrollable list and help text.', 'sw-image-layer-for-yith' ),
			__( 'Tested with WooCommerce and the WoodMart theme (grid and carousel gallery).', 'sw-image-layer-for-yith' ),
		);

		$description  = '<p>' . esc_html__( 'Overlays the image of the YITH add-on on the main product photo.', 'sw-image-layer-for-yith' ) . '</p><ul>';
		foreach ( $items as $item ) {
			$description .= '<li>' . esc_html( $item ) . '</li>';
		}
		$description .= '</ul><p><strong>' . esc_html__( 'Requires:', 'sw-image-layer-for-yith' ) . '</strong> WooCommerce, YITH WooCommerce Product Add-ons &amp; Extra Options Premium.</p>';

		return (object) array(
			'name'          => 'SW Image Layer for YITH Product',
			'slug'          => SWIL_SLUG,
			'version'       => $version,
			'author'        => '<a href="https://sergioweb.es/">Sergio</a>',
			'homepage'      => 'https://sergioweb.es/',
			'requires'      => '6.5',
			'requires_php'  => '7.4',
			'last_updated'  => $release ? $release['published'] : '',
			'download_link' => $release ? $release['package'] : '',
			'sections'      => array(
				'description' => $description,
				'changelog'   => $release && $release['body'] ? wpautop( esc_html( $release['body'] ) ) : '<p>—</p>',
			),
			'banners'       => array(
				'low'  => self::assets_url( 'banner-772x250.png' ),
				'high' => self::assets_url( 'banner-1544x500.png' ),
			),
			'icons'         => array(
				'1x' => self::assets_url( 'icon-128x128.png' ),
				'2x' => self::assets_url( 'icon-256x256.png' ),
			),
		);
	}

	public static function purge( $upgrader, $options ) {
		if ( isset( $options['type'] ) && 'plugin' === $options['type'] ) {
			delete_site_transient( self::CACHE );
		}
	}

	public static function row_meta( $links, $file ) {
		if ( SWIL_BASENAME !== $file || ! current_user_can( 'update_plugins' ) ) {
			return $links;
		}
		$url     = wp_nonce_url( admin_url( 'plugins.php?swil_check=1' ), 'swil_check' );
		$links[] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Check for updates', 'sw-image-layer-for-yith' ) . '</a>';
		$links[] = '<a href="https://github.com/' . esc_attr( SWIL_REPO ) . '" target="_blank" rel="noopener">GitHub</a>';
		return $links;
	}

	/**
	 * Enlace «Buscar actualizaciones»: vacía la caché y vuelve a la lista de plugins.
	 */
	public static function check_now() {
		if ( empty( $_GET['swil_check'] ) || ! current_user_can( 'update_plugins' ) ) {
			return;
		}
		check_admin_referer( 'swil_check' );

		delete_site_transient( self::CACHE );
		delete_site_transient( 'update_plugins' );
		wp_update_plugins();

		wp_safe_redirect( admin_url( 'plugins.php' ) );
		exit;
	}
}
