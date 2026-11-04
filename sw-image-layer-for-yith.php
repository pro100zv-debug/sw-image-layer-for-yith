<?php
/**
 * Plugin Name:       SW Image Layer for YITH Product
 * Plugin URI:        https://github.com/pro100zv-debug/sw-image-layer-for-yith
 * Description:       Superposición de la imagen del complemento de YITH sobre la foto principal del producto. Añade buscador a la lista de opciones con imagen y coloca la imagen elegida (por ejemplo, un escudo bordado) sobre la primera foto de la galería, con posición y tamaño propios en cada producto.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * Author:            Sergio
 * Author URI:        https://github.com/pro100zv-debug
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sw-image-layer-for-yith
 * WC requires at least: 8.0
 * WC tested up to:   11.2
 */

defined( 'ABSPATH' ) || exit;

define( 'SWIL_VERSION', '1.0.0' );
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
						esc_html__( 'necesita el plugin YITH WooCommerce Product Add-ons & Extra Options activo.', 'sw-image-layer-for-yith' ) .
						'</p></div>';
				}
			);
		}
	},
	20
);
