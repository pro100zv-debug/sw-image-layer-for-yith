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
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Ajustes', 'sw-image-layer-for-yith' ) . '</a>' );
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
			'placeholder' => 'Buscar…',
			'empty_text'  => 'No se ha encontrado ningún resultado.',
			'hint'        => 'Selecciona una opción para continuar.',
			'rows'        => 5,
			'accent'      => '#811609',
		);
	}

	private static function fields() {
		$d      = self::defaults();
		$addons = SWIL_YITH::get_addons();

		if ( $addons ) {
			$addon_field = array(
				'title'    => __( 'Complemento con imágenes', 'sw-image-layer-for-yith' ),
				'desc'     => __( 'Complemento de YITH (tipo «Label or image») cuyas imágenes se colocan sobre la foto del producto. Su lista recibe el buscador.', 'sw-image-layer-for-yith' ),
				'id'       => 'swil_addon_id',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'css'      => 'min-width:420px;',
				'default'  => '',
				'options'  => array( '' => __( '— Selecciona un complemento —', 'sw-image-layer-for-yith' ) ) + $addons,
				'desc_tip' => false,
			);
		} else {
			$addon_field = array(
				'title'             => __( 'ID del complemento', 'sw-image-layer-for-yith' ),
				'desc'              => __( 'No se ha podido leer la lista de YITH. Escribe el número del complemento: aparece en la página como id="yith-wapo-addon-NÚMERO".', 'sw-image-layer-for-yith' ),
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
				'desc'  => __( 'La posición y el tamaño de la imagen se ajustan en cada producto: Producto → Datos del producto → pestaña «SW Image Layer».', 'sw-image-layer-for-yith' ),
				'id'    => 'swil_general',
			),
			$addon_field,
			array(
				'title'   => __( 'Buscador', 'sw-image-layer-for-yith' ),
				'desc'    => __( 'Mostrar un buscador encima de la lista', 'sw-image-layer-for-yith' ),
				'id'      => 'swil_search',
				'type'    => 'checkbox',
				'default' => $d['search'],
			),
			array(
				'title'   => __( 'Texto del buscador', 'sw-image-layer-for-yith' ),
				'id'      => 'swil_placeholder',
				'type'    => 'text',
				'default' => $d['placeholder'],
				'css'     => 'min-width:420px;',
			),
			array(
				'title'   => __( 'Texto sin resultados', 'sw-image-layer-for-yith' ),
				'id'      => 'swil_empty_text',
				'type'    => 'text',
				'default' => $d['empty_text'],
				'css'     => 'min-width:420px;',
			),
			array(
				'title'   => __( 'Texto de ayuda', 'sw-image-layer-for-yith' ),
				'desc'    => __( 'Se muestra encima de la lista mientras no hay ninguna opción elegida y sustituye al aviso de error de YITH. Déjalo vacío para no mostrarlo.', 'sw-image-layer-for-yith' ),
				'id'      => 'swil_hint',
				'type'    => 'text',
				'default' => $d['hint'],
				'css'     => 'min-width:420px;',
			),
			array(
				'title'             => __( 'Filas visibles', 'sw-image-layer-for-yith' ),
				'desc'              => __( 'Cuántas opciones se ven sin desplazarse; el resto se desplaza dentro de la lista.', 'sw-image-layer-for-yith' ),
				'id'                => 'swil_rows',
				'type'              => 'number',
				'default'           => $d['rows'],
				'css'               => 'width:80px;',
				'custom_attributes' => array( 'min' => 2, 'max' => 20, 'step' => 1 ),
			),
			array(
				'title'   => __( 'Color de acento', 'sw-image-layer-for-yith' ),
				'desc'    => __( 'Opción elegida, borde del buscador activo y texto de ayuda.', 'sw-image-layer-for-yith' ),
				'id'      => 'swil_accent',
				'type'    => 'color',
				'default' => $d['accent'],
				'css'     => 'width:120px;',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'swil_general',
			),
		);
	}
}
