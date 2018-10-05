<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access, please.
}

require plugin_dir_path( __FILE__ ) . 'class-elements-helper.php';
require plugin_dir_path( __FILE__ ) . 'class-hooks.php';
require plugin_dir_path( __FILE__ ) . 'class-hero.php';
require plugin_dir_path( __FILE__ ) . 'class-layout.php';
require plugin_dir_path( __FILE__ ) . 'class-conditions.php';

if ( is_admin() ) {
	require plugin_dir_path( __FILE__ ) . 'class-post-type.php';
}

add_action( 'wp', 'generate_premium_do_elements' );
/**
 * Execute our Elements.
 *
 * @since 1.7
 */
function generate_premium_do_elements() {
	$args = array(
		'post_type'     => 'gp_elements',
		'no_found_rows' => true,
		'post_status'   => 'publish',
		'numberposts'	=> 500,
		'fields'		=> 'ids',
		'order'			=> 'ASC',
	);

	$posts = get_posts( $args );

	foreach ( $posts as $post_id ) {
		$type = get_post_meta( $post_id, '_generate_element_type', true );

		if ( 'hook' === $type ) {
			new GeneratePress_Hook( $post_id );
		}

		if ( 'header' === $type && ! GeneratePress_Hero::$instances ) {
			new GeneratePress_Hero( $post_id );
		}

		if ( 'layout' === $type && ! GeneratePress_Site_Layout::$instances ) {
			new GeneratePress_Site_Layout( $post_id );
		}
	}
}

add_filter( 'generate_dashboard_tabs', 'generate_elements_dashboard_tab' );
/**
 * Add the Sites tab to our Dashboard tabs.
 *
 * @since 1.6
 *
 * @param array $tabs Existing tabs.
 * @return array New tabs.
 */
function generate_elements_dashboard_tab( $tabs ) {
	$tabs['Elements'] = array(
		'name' => __( 'Elements', 'gp-premium' ),
		'url' => admin_url( 'edit.php?post_type=gp_elements' ),
		'class' => '',
	);

	return $tabs;
}
