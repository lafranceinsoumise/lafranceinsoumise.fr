<?php
// No direct access, please
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'generate_enqueue_premium_icons' );
/**
 * Register our GP Premium icons.
 *
 * @since 1.6
 */
function generate_enqueue_premium_icons() {
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	wp_register_style( 'gp-premium-icons', plugin_dir_url( __FILE__ ) . "icons/icons{$suffix}.css", array(), GP_PREMIUM_VERSION );
}
