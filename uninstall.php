<?php
/**
 * Al borrar el plugin: se eliminan sus ajustes y los datos guardados en los productos.
 *
 * @package SW_Image_Layer
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

foreach ( array( 'addon_id', 'search', 'placeholder', 'empty_text', 'hint', 'rows', 'accent' ) as $swil_key ) {
	delete_option( 'swil_' . $swil_key );
}

delete_site_transient( 'swil_github_release' );
delete_post_meta_by_key( '_swil_enabled' );
delete_post_meta_by_key( '_swil_zones' );
