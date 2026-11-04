<?php
/**
 * Pestaña «SW Image Layer» en Datos del producto: posición y tamaño de la imagen.
 *
 * Las zonas se guardan como lista para poder añadir más adelante otras
 * (espalda, manga…). En la versión 1.0 solo se usa la zona «frente».
 *
 * @package SW_Image_Layer
 */

defined( 'ABSPATH' ) || exit;

class SWIL_Product {

	const META_ENABLED = '_swil_enabled';
	const META_ZONES   = '_swil_zones';

	public static function init() {
		add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'add_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'panel' ) );
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	/**
	 * Zona por defecto.
	 */
	public static function default_zone() {
		return array(
			'id' => 'frente',
			'x'  => 50,
			'y'  => 35,
			'w'  => 18,
		);
	}

	/**
	 * Ajustes del producto: [ 'enabled' => bool, 'zones' => [ ... ] ].
	 *
	 * @param int $product_id ID del producto.
	 */
	public static function get_config( $product_id ) {
		$enabled = 'yes' === get_post_meta( $product_id, self::META_ENABLED, true );
		$zones   = get_post_meta( $product_id, self::META_ZONES, true );

		if ( ! is_array( $zones ) || empty( $zones ) ) {
			$zones = array( self::default_zone() );
		}

		return array(
			'enabled' => $enabled,
			'zones'   => array_values( $zones ),
		);
	}

	public static function add_tab( $tabs ) {
		$tabs['swil'] = array(
			'label'    => 'SW Image Layer',
			'target'   => 'swil_product_data',
			'class'    => array(),
			'priority' => 85,
		);
		return $tabs;
	}

	public static function panel() {
		global $post;

		$config = self::get_config( $post->ID );
		$zone   = $config['zones'][0];
		$photo  = get_the_post_thumbnail_url( $post->ID, 'large' );
		$sample = SWIL_YITH::get_sample_image( SWIL_Settings::get( 'addon_id' ) );
		?>
		<div id="swil_product_data" class="panel woocommerce_options_panel hidden">
			<div class="options_group">
				<?php
				woocommerce_wp_checkbox(
					array(
						'id'          => 'swil_enabled',
						'label'       => __( 'Mostrar imagen sobre la foto', 'sw-image-layer-for-yith' ),
						'description' => __( 'Coloca la imagen elegida en YITH sobre la primera foto de la galería.', 'sw-image-layer-for-yith' ),
						'value'       => $config['enabled'] ? 'yes' : 'no',
						'cbvalue'     => 'yes',
					)
				);

				$fields = array(
					'x' => array( __( 'Posición horizontal (X, %)', 'sw-image-layer-for-yith' ), __( 'Centro de la imagen desde el borde izquierdo de la foto. 50 = centro.', 'sw-image-layer-for-yith' ), 0, 100 ),
					'y' => array( __( 'Posición vertical (Y, %)', 'sw-image-layer-for-yith' ), __( 'Centro de la imagen desde el borde superior de la foto.', 'sw-image-layer-for-yith' ), 0, 100 ),
					'w' => array( __( 'Ancho (%)', 'sw-image-layer-for-yith' ), __( 'Ancho de la imagen respecto al ancho de la foto.', 'sw-image-layer-for-yith' ), 1, 100 ),
				);

				foreach ( $fields as $key => $f ) {
					woocommerce_wp_text_input(
						array(
							'id'                => 'swil_' . $key,
							'label'             => $f[0],
							'description'       => $f[1],
							'desc_tip'          => true,
							'type'              => 'number',
							'value'             => $zone[ $key ],
							'custom_attributes' => array(
								'min'  => $f[2],
								'max'  => $f[3],
								'step' => '0.1',
							),
						)
					);
				}
				?>
			</div>

			<div class="options_group swil-preview-group">
				<p class="form-field">
					<label><?php esc_html_e( 'Vista previa', 'sw-image-layer-for-yith' ); ?></label>
					<?php if ( $photo ) : ?>
						<span class="swil-preview" id="swil-preview">
							<img src="<?php echo esc_url( $photo ); ?>" alt="" class="swil-preview-photo">
							<span class="swil-preview-layer<?php echo $sample ? '' : ' is-empty'; ?>">
								<?php if ( $sample ) : ?>
									<img src="<?php echo esc_url( $sample ); ?>" alt="">
								<?php endif; ?>
							</span>
						</span>
						<span class="description swil-preview-note"><?php esc_html_e( 'Imagen principal del producto. La vista previa cambia al escribir los valores; guarda el producto para aplicarlos.', 'sw-image-layer-for-yith' ); ?></span>
					<?php else : ?>
						<span class="description"><?php esc_html_e( 'Asigna una imagen principal al producto para ver la vista previa.', 'sw-image-layer-for-yith' ); ?></span>
					<?php endif; ?>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Guardado.
	 *
	 * @param WC_Product $product Producto.
	 */
	public static function save( $product ) {
		// WooCommerce ya ha comprobado el nonce del formulario del producto.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$enabled = isset( $_POST['swil_enabled'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['swil_enabled'] ) );

		$zone = self::default_zone();
		foreach ( array( 'x', 'y', 'w' ) as $key ) {
			if ( isset( $_POST[ 'swil_' . $key ] ) && '' !== $_POST[ 'swil_' . $key ] ) {
				$value        = (float) str_replace( ',', '.', sanitize_text_field( wp_unslash( $_POST[ 'swil_' . $key ] ) ) );
				$min          = 'w' === $key ? 1 : 0;
				$zone[ $key ] = round( min( 100, max( $min, $value ) ), 2 );
			}
		}
		// phpcs:enable

		$product->update_meta_data( self::META_ENABLED, $enabled ? 'yes' : 'no' );
		$product->update_meta_data( self::META_ZONES, array( $zone ) );
	}

	public static function assets( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || 'product' !== get_post_type() ) {
			return;
		}
		wp_enqueue_style( 'swil-admin', SWIL_URL . 'assets/css/swil-admin.css', array(), SWIL_VERSION );
		wp_enqueue_script( 'swil-admin', SWIL_URL . 'assets/js/swil-admin.js', array( 'jquery' ), SWIL_VERSION, true );
	}
}
