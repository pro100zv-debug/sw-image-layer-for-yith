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

		$config = SWIL_Product::get_config( get_queried_object_id() );
		$accent = sanitize_hex_color( SWIL_Settings::get( 'accent' ) );

		wp_enqueue_style( 'swil-front', SWIL_URL . 'assets/css/swil-front.css', array(), SWIL_VERSION );
		if ( $accent ) {
			wp_add_inline_style( 'swil-front', ':root{--swil-accent:' . $accent . ';}' );
		}

		wp_enqueue_script( 'swil-front', SWIL_URL . 'assets/js/swil-front.js', array( 'jquery' ), SWIL_VERSION, true );

		// JSON (no wp_localize_script): así los números y los sí/no llegan como tales y no como texto.
		$data = array(
			'addonId'     => $addon_id,
			'enabled'     => ! $config['hidden'],
			'zone'        => array(
				'x' => (float) $config['zone']['x'],
				'y' => (float) $config['zone']['y'],
				'w' => (float) $config['zone']['w'],
			),
			'search'      => 'yes' === SWIL_Settings::get( 'search' ),
			'rows'        => max( 2, min( 20, absint( SWIL_Settings::get( 'rows' ) ) ) ),
			'placeholder' => (string) SWIL_Settings::get( 'placeholder' ),
			'emptyText'   => (string) SWIL_Settings::get( 'empty_text' ),
			'hint'        => (string) get_option( 'swil_hint', SWIL_Settings::defaults()['hint'] ),
		);

		wp_add_inline_script( 'swil-front', 'window.swilData = ' . wp_json_encode( $data ) . ';', 'before' );
	}
}
