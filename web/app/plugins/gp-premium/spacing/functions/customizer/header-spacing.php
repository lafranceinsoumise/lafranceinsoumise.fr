<?php
// No direct access, please
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Add our old header section
$wp_customize->add_section(
	'generate_spacing_header',
	array(
		'title' => __( 'Header', 'gp-premium' ),
		'capability' => 'edit_theme_options',
		'priority' => 5,
		'panel' => 'generate_spacing_panel'
	)
);

// If we don't have a layout panel, use our old spacing section
$header_section = ( $wp_customize->get_panel( 'generate_layout_panel' ) ) ? 'generate_layout_header' : 'generate_spacing_header';

// Header top
$wp_customize->add_setting( 'generate_spacing_settings[header_top]',
	array(
		'default' 			=> $defaults['header_top'],
		'type' 				=> 'option',
		'sanitize_callback' => 'absint',
		'transport' 		=> 'postMessage'
	)
);

// Header right
$wp_customize->add_setting( 'generate_spacing_settings[header_right]',
	array(
		'default' 			=> $defaults['header_right'],
		'type' 				=> 'option',
		'sanitize_callback' => 'absint',
		'transport' 		=> 'postMessage'
	)
);

// Header bottom
$wp_customize->add_setting( 'generate_spacing_settings[header_bottom]',
	array(
		'default' 			=> $defaults['header_bottom'],
		'type' 				=> 'option',
		'sanitize_callback' => 'absint',
		'transport' 		=> 'postMessage'
	)
);

// Header left
$wp_customize->add_setting( 'generate_spacing_settings[header_left]',
	array(
		'default' 			=> $defaults['header_left'],
		'type' 				=> 'option',
		'sanitize_callback' => 'absint',
		'transport' 		=> 'postMessage'
	)
);

// Do something with our header controls
$wp_customize->add_control(
	new GeneratePress_Spacing_Control(
		$wp_customize,
		'header_spacing',
		array(
			'type' => 'generatepress-spacing',
			'label'       => esc_html__( 'Header Padding', 'gp-premium' ),
			'section'     => $header_section,
			'settings'    => array(
				'top'     => 'generate_spacing_settings[header_top]',
				'right'   => 'generate_spacing_settings[header_right]',
				'bottom'  => 'generate_spacing_settings[header_bottom]',
				'left'    => 'generate_spacing_settings[header_left]'
			),
			'element'	  => 'header',
		)
	)
);
