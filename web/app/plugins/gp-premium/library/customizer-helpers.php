<?php
defined( 'WPINC' ) or die;

// Controls
require plugin_dir_path( __FILE__ ) . 'customizer/controls/class-information-control.php';
require plugin_dir_path( __FILE__ ) . 'customizer/controls/class-backgrounds-control.php';
require plugin_dir_path( __FILE__ ) . 'customizer/controls/class-refresh-button-control.php';
require plugin_dir_path( __FILE__ ) . 'customizer/controls/class-alpha-color-control.php';
require plugin_dir_path( __FILE__ ) . 'customizer/controls/class-copyright-control.php';
require plugin_dir_path( __FILE__ ) . 'customizer/controls/class-spacing-control.php';
require plugin_dir_path( __FILE__ ) . 'customizer/controls/class-range-slider-control.php';
require plugin_dir_path( __FILE__ ) . 'customizer/controls/class-title-control.php';
require plugin_dir_path( __FILE__ ) . 'customizer/controls/class-typography-control.php';
require plugin_dir_path( __FILE__ ) . 'customizer/controls/class-control-toggle.php';
require plugin_dir_path( __FILE__ ) . 'customizer/controls/class-deprecated.php';

// Other
require plugin_dir_path( __FILE__ ) . 'customizer/sanitize.php';
require plugin_dir_path( __FILE__ ) . 'customizer/active-callbacks.php';
require plugin_dir_path( __FILE__ ) . 'customizer/deprecated.php';

add_action( 'customize_controls_enqueue_scripts', 'generate_premium_control_inline_scripts', 100 );
/**
 * Add misc inline scripts to our controls.
 *
 * We don't want to add these to the controls themselves, as they will be repeated
 * each time the control is initialized.
 *
 * @since 1.4
 */
function generate_premium_control_inline_scripts() {
	if ( ! function_exists( 'generate_typography_default_fonts' ) ) {
		return;
	}

	wp_localize_script( 'generatepress-pro-typography-customizer', 'gp_customize', array( 'nonce' => wp_create_nonce( 'gp_customize_nonce' ) ) );
	wp_localize_script( 'generatepress-pro-typography-customizer', 'typography_defaults', generate_typography_default_fonts() );
}