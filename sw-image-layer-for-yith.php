<?php
/**
 * Plugin Name:       SW Image Layer for YITH Product
 * Description:       Overlays the image chosen in a YITH Product Add-ons option on the main product photo. Adds a search box to image option lists and places the chosen image (for example, an embroidered crest) on the first gallery photo, with its own position and size per product.
 * Version:           1.2.3
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce, yith-woocommerce-advanced-product-options-premium
 * Author:            Sergio
 * Author URI:        https://sergioweb.es/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sw-image-layer-for-yith
 * Domain Path:       /languages
 * WC requires at least: 8.0
 * WC tested up to:   11.2
 */

defined( 'ABSPATH' ) || exit;

define( 'SWIL_VERSION', '1.2.3' );
define( 'SWIL_FILE', __FILE__ );
define( 'SWIL_DIR', plugin_dir_path( __FILE__ ) );
define( 'SWIL_URL', plugin_dir_url( __FILE__ ) );
define( 'SWIL_BASENAME', plugin_basename( __FILE__ ) );
define( 'SWIL_SLUG', 'sw-image-layer-for-yith' );
define( 'SWIL_REPO', 'pro100zv-debug/sw-image-layer-for-yith' );

require_once SWIL_DIR . 'includes/class-swil-yith.php';
require_once SWIL_DIR . 'includes/class-swil-settings.php';
require_once SWIL_DIR . 'includes/class-swil-product.php';
require_once SWIL_DIR . 'includes/class-swil-frontend.php';
require_once SWIL_DIR . 'includes/class-swil-updater.php';

/* Compatibilidad con los pedidos HPOS de WooCommerce. */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', SWIL_FILE, true );
		}
	}
);

/*
 * Traducciones: inglés de base; español y ruso en /languages.
 * Otras variantes de esos idiomas (es_MX, es_AR…) usan es_ES.
 */
add_action(
	'init',
	function () {
		$domain = 'sw-image-layer-for-yith';
		$locale = determine_locale();
		$dir    = SWIL_DIR . 'languages/';
		$file   = $dir . $domain . '-' . $locale . '.mo';

		if ( ! is_readable( $file ) ) {
			$fallback = array(
				'es' => 'es_ES',
				'ru' => 'ru_RU',
			);
			$lang = substr( $locale, 0, 2 );
			if ( isset( $fallback[ $lang ] ) ) {
				$file = $dir . $domain . '-' . $fallback[ $lang ] . '.mo';
			}
		}
		if ( is_readable( $file ) ) {
			load_textdomain( $domain, $file, $locale );
		}
	}
);

/* El actualizador funciona aunque falte WooCommerce o YITH. */
SWIL_Updater::init();

add_action(
	'plugins_loaded',
	function () {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		SWIL_Settings::init();
		SWIL_Product::init();
		SWIL_Frontend::init();

		if ( is_admin() && ! SWIL_YITH::is_active() ) {
			add_action(
				'admin_notices',
				function () {
					if ( ! current_user_can( 'activate_plugins' ) ) {
						return;
					}
					echo '<div class="notice notice-warning"><p><strong>SW Image Layer for YITH Product:</strong> ' .
						esc_html__( 'requires the YITH WooCommerce Product Add-ons & Extra Options Premium plugin to be active.', 'sw-image-layer-for-yith' ) .
						'</p></div>';
				}
			);
		}
	},
	20
);
