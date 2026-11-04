<?php
/**
 * Ficha de producto: buscador, texto de ayuda y capa sobre la primera foto.
 *
 * @package SW_Image_Layer
 */

defined( 'ABSPATH' ) || exit;

class SWIL_Frontend {

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ), 20 );
	}

	public static function assets() {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}

		$addon_id = absint( SWIL_Settings::get( 'addon_id' ) );
		if ( ! $addon_id ) {
			return;
		}

		$product_id = get_queried_object_id();
		$config     = SWIL_Product::get_config( $product_id );
		$zone       = $config['zones'][0];
		$accent     = sanitize_hex_color( SWIL_Settings::get( 'accent' ) );

		wp_enqueue_style( 'swil-front', SWIL_URL . 'assets/css/swil-front.css', array(), SWIL_VERSION );
		if ( $accent ) {
			wp_add_inline_style( 'swil-front', ':root{--swil-accent:' . $accent . ';}' );
		}

		wp_enqueue_script( 'swil-front', SWIL_URL . 'assets/js/swil-front.js', array( 'jquery' ), SWIL_VERSION, true );
		wp_localize_script(
			'swil-front',
			'swilData',
			array(
				'addonId'     => $addon_id,
				'enabled'     => $config['enabled'] ? 1 : 0,
				'zone'        => array(
					'x' => (float) $zone['x'],
					'y' => (float) $zone['y'],
					'w' => (float) $zone['w'],
				),
				'search'      => 'yes' === SWIL_Settings::get( 'search' ) ? 1 : 0,
				'rows'        => max( 2, min( 20, absint( SWIL_Settings::get( 'rows' ) ) ) ),
				'placeholder' => SWIL_Settings::get( 'placeholder' ),
				'emptyText'   => SWIL_Settings::get( 'empty_text' ),
				'hint'        => get_option( 'swil_hint', SWIL_Settings::defaults()['hint'] ),
			)
		);
	}
}
