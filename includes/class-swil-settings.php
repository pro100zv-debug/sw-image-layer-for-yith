<?php
/**
 * Ajustes generales: WooCommerce → Ajustes → SW Image Layer.
 *
 * @package SW_Image_Layer
 */

defined( 'ABSPATH' ) || exit;

class SWIL_Settings {

	const TAB = 'swil';

	public static function init() {
		add_filter( 'woocommerce_settings_tabs_array', array( __CLASS__, 'add_tab' ), 60 );
		add_action( 'woocommerce_settings_tabs_' . self::TAB, array( __CLASS__, 'output' ) );
		add_action( 'woocommerce_update_options_' . self::TAB, array( __CLASS__, 'save' ) );
		add_filter( 'plugin_action_links_' . SWIL_BASENAME, array( __CLASS__, 'action_link' ) );
	}

	public static function add_tab( $tabs ) {
		$tabs[ self::TAB ] = 'SW Image Layer';
		return $tabs;
	}

	public static function action_link( $links ) {
		$url = admin_url( 'admin.php?page=wc-settings&tab=' . self::TAB );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'sw-image-layer-for-yith' ) . '</a>' );
		return $links;
	}

	public static function output() {
		woocommerce_admin_fields( self::fields() );
	}

	public static function save() {
		woocommerce_update_options( self::fields() );
	}

	/**
	 * Valor de un ajuste con su valor por defecto.
	 */
	public static function get( $key ) {
		$defaults = self::defaults();
		$value    = get_option( 'swil_' . $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
		return ( '' === $value && isset( $defaults[ $key ] ) && 'addon_id' !== $key ) ? $defaults[ $key ] : $value;
	}

	public static function defaults() {
		return array(
			'addon_id'    => '',
			'search'      => 'yes',
			'placeholder' => __( 'Search…', 'sw-image-layer-for-yith' ),
			'empty_text'  => __( 'No results found.', 'sw-image-layer-for-yith' ),
			'hint'        => __( 'Select an option to continue.', 'sw-image-layer-for-yith' ),
			'rows'        => 5,
			'accent'      => '#811609',
			'default_x'   => 50,
			'default_y'   => 35,
			'default_w'   => 18,
		);
	}

	/**
	 * Posición por defecto: [ 'x' => , 'y' => , 'w' => ].
	 */
	public static function default_zone() {
		$zone = array();
		foreach ( array( 'x', 'y', 'w' ) as $key ) {
			$value        = (float) str_replace( ',', '.', (string) self::get( 'default_' . $key ) );
			$min          = 'w' === $key ? 1 : 0;
			$zone[ $key ] = round( min( 100, max( $min, $value ) ), 2 );
		}
		return $zone;
	}

	private static function fields() {
		$d      = self::defaults();
		$addons = SWIL_YITH::get_addons();

		if ( $addons ) {
			$addon_field = array(
				'title'    => __( 'Image add-on', 'sw-image-layer-for-yith' ),
				'desc'     => __( 'YITH add-on («Label or image» type) whose images are placed on the product photo. Its list gets the search box.', 'sw-image-layer-for-yith' ),
				'id'       => 'swil_addon_id',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'css'      => 'min-width:420px;',
				'default'  => '',
				'options'  => array( '' => __( '— Select an add-on —', 'sw-image-layer-for-yith' ) ) + $addons,
				'desc_tip' => false,
			);
		} else {
			$addon_field = array(
				'title'             => __( 'Add-on ID', 'sw-image-layer-for-yith' ),
				'desc'              => __( 'The YITH list could not be read. Enter the add-on number: it appears on the page as id="yith-wapo-addon-NUMBER".', 'sw-image-layer-for-yith' ),
				'id'                => 'swil_addon_id',
				'type'              => 'number',
				'default'           => '',
				'custom_attributes' => array( 'min' => 1, 'step' => 1 ),
			);
		}

		return array(
			array(
				'title' => __( 'SW Image Layer for YITH Product', 'sw-image-layer-for-yith' ),
				'type'  => 'title',
				'desc'  => __( 'The image is shown on every product where YITH shows this add-on. To hide it or change its position on a product: Product → Product data → «SW Image Layer» tab.', 'sw-image-layer-for-yith' ),
				'id'    => 'swil_general',
			),
			$addon_field,
			array(
				'title'   => __( 'Search box', 'sw-image-layer-for-yith' ),
				'desc'    => __( 'Show a search box above the list', 'sw-image-layer-for-yith' ),
				'id'      => 'swil_search',
				'type'    => 'checkbox',
				'default' => $d['search'],
			),
			array(
				'title'   => __( 'Search placeholder', 'sw-image-layer-for-yith' ),
				'id'      => 'swil_placeholder',
				'type'    => 'text',
				'default' => $d['placeholder'],
				'css'     => 'min-width:420px;',
			),
			array(
				'title'   => __( 'No results text', 'sw-image-layer-for-yith' ),
				'id'      => 'swil_empty_text',
				'type'    => 'text',
				'default' => $d['empty_text'],
				'css'     => 'min-width:420px;',
			),
			array(
				'title'   => __( 'Help text', 'sw-image-layer-for-yith' ),
				'desc'    => __( 'Shown above the list while no option is selected; replaces the YITH error notice. Leave empty to hide it.', 'sw-image-layer-for-yith' ),
				'id'      => 'swil_hint',
				'type'    => 'text',
				'default' => $d['hint'],
				'css'     => 'min-width:420px;',
			),
			array(
				'title'             => __( 'Visible rows', 'sw-image-layer-for-yith' ),
				'desc'              => __( 'How many options are visible without scrolling; the rest scroll inside the list.', 'sw-image-layer-for-yith' ),
				'id'                => 'swil_rows',
				'type'              => 'number',
				'default'           => $d['rows'],
				'css'               => 'width:80px;',
				'custom_attributes' => array( 'min' => 2, 'max' => 20, 'step' => 1 ),
			),
			array(
				'title'   => __( 'Accent color', 'sw-image-layer-for-yith' ),
				'desc'    => __( 'Selected option, active search box border and help text.', 'sw-image-layer-for-yith' ),
				'id'      => 'swil_accent',
				'type'    => 'color',
				'default' => $d['accent'],
				'css'     => 'width:120px;',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'swil_general',
			),
			array(
				'title' => __( 'Default position', 'sw-image-layer-for-yith' ),
				'type'  => 'title',
				'desc'  => __( 'Used on every product that shows the add-on. On each product you can enter its own position or leave the fields empty to use this one.', 'sw-image-layer-for-yith' ),
				'id'    => 'swil_position',
			),
			array(
				'title'             => __( 'Horizontal position (X, %)', 'sw-image-layer-for-yith' ),
				'desc'              => __( 'Center of the image from the left edge of the photo. 50 = center.', 'sw-image-layer-for-yith' ),
				'id'                => 'swil_default_x',
				'type'              => 'number',
				'default'           => $d['default_x'],
				'css'               => 'width:90px;',
				'custom_attributes' => array( 'min' => 0, 'max' => 100, 'step' => '0.1' ),
			),
			array(
				'title'             => __( 'Vertical position (Y, %)', 'sw-image-layer-for-yith' ),
				'desc'              => __( 'Center of the image from the top edge of the photo.', 'sw-image-layer-for-yith' ),
				'id'                => 'swil_default_y',
				'type'              => 'number',
				'default'           => $d['default_y'],
				'css'               => 'width:90px;',
				'custom_attributes' => array( 'min' => 0, 'max' => 100, 'step' => '0.1' ),
			),
			array(
				'title'             => __( 'Width (%)', 'sw-image-layer-for-yith' ),
				'desc'              => __( 'Image width relative to the photo width.', 'sw-image-layer-for-yith' ),
				'id'                => 'swil_default_w',
				'type'              => 'number',
				'default'           => $d['default_w'],
				'css'               => 'width:90px;',
				'custom_attributes' => array( 'min' => 1, 'max' => 100, 'step' => '0.1' ),
			),
			array(
				'type' => 'sectionend',
				'id'   => 'swil_position',
			),
		);
	}
}
