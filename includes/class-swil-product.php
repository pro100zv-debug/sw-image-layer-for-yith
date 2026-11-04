<?php
/**
 * Pestaña «SW Image Layer» en Datos del producto.
 *
 * Por defecto la imagen se muestra en todos los productos donde YITH muestra el
 * complemento, con la posición por defecto de los ajustes. En cada producto se
 * puede ocultar o escribir una posición propia (los campos vacíos usan la de por defecto).
 *
 * Las zonas se guardan como lista para poder añadir más adelante otras
 * (espalda, manga…). En esta versión solo se usa la zona «frente».
 *
 * @package SW_Image_Layer
 */

defined( 'ABSPATH' ) || exit;

class SWIL_Product {

	const META_HIDDEN = '_swil_hidden';
	const META_ZONES  = '_swil_zones';

	public static function init() {
		add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'add_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'panel' ) );
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	/**
	 * Valores propios del producto (solo los que se han escrito).
	 *
	 * @param int $product_id ID del producto.
	 * @return array [ 'x' => , 'y' => , 'w' => ] con las claves que existan.
	 */
	public static function get_own_zone( $product_id ) {
		$zones = get_post_meta( $product_id, self::META_ZONES, true );
		$own   = array();

		if ( is_array( $zones ) && ! empty( $zones[0] ) && is_array( $zones[0] ) ) {
			foreach ( array( 'x', 'y', 'w' ) as $key ) {
				if ( isset( $zones[0][ $key ] ) && '' !== $zones[0][ $key ] && null !== $zones[0][ $key ] ) {
					$own[ $key ] = (float) $zones[0][ $key ];
				}
			}
		}

		return $own;
	}

	/**
	 * Ajustes finales del producto: [ 'hidden' => bool, 'zone' => [ x, y, w ] ].
	 *
	 * @param int $product_id ID del producto.
	 */
	public static function get_config( $product_id ) {
		return array(
			'hidden' => 'yes' === get_post_meta( $product_id, self::META_HIDDEN, true ),
			'zone'   => array_merge( SWIL_Settings::default_zone(), self::get_own_zone( $product_id ) ),
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

		$hidden   = 'yes' === get_post_meta( $post->ID, self::META_HIDDEN, true );
		$own      = self::get_own_zone( $post->ID );
		$defaults = SWIL_Settings::default_zone();
		$photo    = get_the_post_thumbnail_url( $post->ID, 'large' );
		$sample   = SWIL_YITH::get_sample_image( SWIL_Settings::get( 'addon_id' ) );
		$settings = admin_url( 'admin.php?page=wc-settings&tab=' . SWIL_Settings::TAB );
		?>
		<div id="swil_product_data" class="panel woocommerce_options_panel hidden">
			<div class="options_group">
				<?php
				woocommerce_wp_checkbox(
					array(
						'id'          => 'swil_hidden',
						'label'       => __( 'Do not show the image', 'sw-image-layer-for-yith' ),
						'description' => __( 'On this product the chosen image is not placed on the photo. The YITH option and its price stay.', 'sw-image-layer-for-yith' ),
						'value'       => $hidden ? 'yes' : 'no',
						'cbvalue'     => 'yes',
					)
				);
				?>
			</div>

			<div class="options_group">
				<p class="swil-intro">
					<?php
					printf(
						/* translators: %s: enlace a los ajustes */
						esc_html__( 'Leave a field empty to use the default position (%s).', 'sw-image-layer-for-yith' ),
						'<a href="' . esc_url( $settings ) . '" target="_blank">' . esc_html__( 'Settings', 'sw-image-layer-for-yith' ) . '</a>'
					);
					?>
				</p>
				<?php
				$fields = array(
					'x' => array( __( 'Horizontal position (X, %)', 'sw-image-layer-for-yith' ), __( 'Center of the image from the left edge of the photo. 50 = center.', 'sw-image-layer-for-yith' ), 0, 100 ),
					'y' => array( __( 'Vertical position (Y, %)', 'sw-image-layer-for-yith' ), __( 'Center of the image from the top edge of the photo.', 'sw-image-layer-for-yith' ), 0, 100 ),
					'w' => array( __( 'Width (%)', 'sw-image-layer-for-yith' ), __( 'Image width relative to the photo width.', 'sw-image-layer-for-yith' ), 1, 100 ),
				);

				foreach ( $fields as $key => $f ) {
					woocommerce_wp_text_input(
						array(
							'id'                => 'swil_' . $key,
							'label'             => $f[0],
							'description'       => $f[1],
							'desc_tip'          => true,
							'type'              => 'number',
							'value'             => isset( $own[ $key ] ) ? $own[ $key ] : '',
							'placeholder'       => $defaults[ $key ],
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
					<label><?php esc_html_e( 'Preview', 'sw-image-layer-for-yith' ); ?></label>
					<?php if ( $photo ) : ?>
						<span class="swil-preview" id="swil-preview">
							<img src="<?php echo esc_url( $photo ); ?>" alt="" class="swil-preview-photo">
							<span class="swil-preview-layer<?php echo $sample ? '' : ' is-empty'; ?>">
								<?php if ( $sample ) : ?>
									<img src="<?php echo esc_url( $sample ); ?>" alt="">
								<?php endif; ?>
							</span>
						</span>
						<span class="description swil-preview-note"><?php esc_html_e( 'Main product image. The preview changes as you type; update the product to apply the values.', 'sw-image-layer-for-yith' ); ?></span>
					<?php else : ?>
						<span class="description"><?php esc_html_e( 'Set a product image to see the preview.', 'sw-image-layer-for-yith' ); ?></span>
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
		$hidden = isset( $_POST['swil_hidden'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['swil_hidden'] ) );

		$zone = array();
		foreach ( array( 'x', 'y', 'w' ) as $key ) {
			$raw = isset( $_POST[ 'swil_' . $key ] ) ? trim( sanitize_text_field( wp_unslash( $_POST[ 'swil_' . $key ] ) ) ) : '';
			if ( '' === $raw ) {
				continue;
			}
			$value        = (float) str_replace( ',', '.', $raw );
			$min          = 'w' === $key ? 1 : 0;
			$zone[ $key ] = round( min( 100, max( $min, $value ) ), 2 );
		}
		// phpcs:enable

		if ( $hidden ) {
			$product->update_meta_data( self::META_HIDDEN, 'yes' );
		} else {
			$product->delete_meta_data( self::META_HIDDEN );
		}

		if ( $zone ) {
			$zone['id'] = 'frente';
			$product->update_meta_data( self::META_ZONES, array( $zone ) );
		} else {
			$product->delete_meta_data( self::META_ZONES );
		}
	}

	public static function assets( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || 'product' !== get_post_type() ) {
			return;
		}
		wp_enqueue_style( 'swil-admin', SWIL_URL . 'assets/css/swil-admin.css', array(), SWIL_VERSION );
		wp_enqueue_script( 'swil-admin', SWIL_URL . 'assets/js/swil-admin.js', array( 'jquery' ), SWIL_VERSION, true );
	}
}
