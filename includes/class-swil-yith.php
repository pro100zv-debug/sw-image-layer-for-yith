<?php
/**
 * Lectura de los bloques y complementos de YITH Product Add-ons.
 *
 * @package SW_Image_Layer
 */

defined( 'ABSPATH' ) || exit;

class SWIL_YITH {

	/**
	 * ¿Está activo YITH Product Add-ons (gratis o premium)?
	 */
	public static function is_active() {
		return defined( 'YITH_WAPO' ) || defined( 'YITH_WAPO_VERSION' ) || defined( 'YITH_WAPO_PREMIUM' ) || class_exists( 'YITH_WAPO' );
	}

	/**
	 * Lista de complementos: [ id => 'Título (#id) · Bloque' ].
	 *
	 * @return array
	 */
	public static function get_addons() {
		global $wpdb;

		$table = $wpdb->prefix . 'yith_wapo_addons';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return array();
		}

		$blocks = self::get_block_names();
		$rows   = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id ASC" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$list   = array();

		foreach ( (array) $rows as $row ) {
			if ( isset( $row->deleted ) && (int) $row->deleted ) {
				continue;
			}
			$settings = isset( $row->settings ) ? maybe_unserialize( $row->settings ) : array();
			$settings = is_array( $settings ) ? $settings : array();
			$type     = isset( $settings['type'] ) ? $settings['type'] : '';

			if ( in_array( $type, array( 'html_heading', 'html_text', 'html_separator' ), true ) ) {
				continue;
			}

			$title = ! empty( $settings['title'] ) ? wp_strip_all_tags( $settings['title'] ) : __( 'Untitled', 'sw-image-layer-for-yith' );
			$label = sprintf( '%s (#%d)', $title, (int) $row->id );

			if ( isset( $row->block_id ) && isset( $blocks[ (int) $row->block_id ] ) ) {
				$label .= ' · ' . $blocks[ (int) $row->block_id ];
			}
			if ( $type ) {
				$label .= ' · ' . $type;
			}
			$list[ (int) $row->id ] = $label;
		}

		return $list;
	}

	/**
	 * Nombres de los bloques: [ id => nombre ].
	 *
	 * @return array
	 */
	private static function get_block_names() {
		global $wpdb;

		$table = $wpdb->prefix . 'yith_wapo_blocks';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return array();
		}

		$rows  = $wpdb->get_results( "SELECT * FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$names = array();

		foreach ( (array) $rows as $row ) {
			$name = '';
			if ( ! empty( $row->name ) ) {
				$name = $row->name;
			} elseif ( isset( $row->settings ) ) {
				$settings = maybe_unserialize( $row->settings );
				if ( is_array( $settings ) && ! empty( $settings['name'] ) ) {
					$name = $settings['name'];
				}
			}
			if ( $name ) {
				$names[ (int) $row->id ] = wp_strip_all_tags( $name );
			}
		}

		return $names;
	}

	/**
	 * Primera imagen de las opciones de un complemento (para la vista previa del admin).
	 *
	 * @param int $addon_id ID del complemento.
	 * @return string URL o cadena vacía.
	 */
	public static function get_sample_image( $addon_id ) {
		global $wpdb;

		$addon_id = absint( $addon_id );
		$table    = $wpdb->prefix . 'yith_wapo_addons';
		if ( ! $addon_id || $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return '';
		}

		$options = $wpdb->get_var( $wpdb->prepare( "SELECT options FROM {$table} WHERE id = %d", $addon_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$options = maybe_unserialize( $options );

		if ( ! is_array( $options ) ) {
			return '';
		}

		// YITH puede guardar la imagen como URL o como ID de adjunto, según la versión.
		foreach ( array( 'image', 'images', 'image_url', 'image_id' ) as $key ) {
			if ( empty( $options[ $key ] ) || ! is_array( $options[ $key ] ) ) {
				continue;
			}
			foreach ( $options[ $key ] as $image ) {
				$url = self::image_to_url( $image );
				if ( $url ) {
					return $url;
				}
			}
		}

		return '';
	}

	/**
	 * URL a partir de lo que YITH haya guardado: URL, ID o array con url/id.
	 *
	 * @param mixed $image Valor guardado.
	 * @return string
	 */
	private static function image_to_url( $image ) {
		if ( is_array( $image ) ) {
			$image = isset( $image['url'] ) ? $image['url'] : ( isset( $image['id'] ) ? $image['id'] : '' );
		}
		if ( is_numeric( $image ) && (int) $image > 0 ) {
			$url = wp_get_attachment_image_url( (int) $image, 'medium' );
			return $url ? $url : '';
		}
		if ( is_string( $image ) ) {
			$image = trim( $image );
			if ( preg_match( '#^(https?:)?//#i', $image ) || 0 === strpos( $image, '/' ) ) {
				return esc_url_raw( $image );
			}
		}
		return '';
	}
}
