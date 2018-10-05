<?php
// No direct access, please
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'GeneratePress_Spacing_Control' ) ) :
class GeneratePress_Spacing_Control extends WP_Customize_Control {

	public $type = 'generatepress-spacing';

	public $l10n = array();

	public $element = '';

	public function __construct( $manager, $id, $args = array() ) {
		// Let the parent class do its thing.
		parent::__construct( $manager, $id, $args );
		// Make sure we have labels.
		$this->l10n = wp_parse_args(
			$this->l10n,
			array(
				'top'            => esc_html__( 'Top', 'gp-premium' ),
				'right'          => esc_html__( 'Right', 'gp-premium' ),
				'bottom'         => esc_html__( 'Bottom', 'gp-premium' ),
				'left'           => esc_html__( 'Left', 'gp-premium' )
			)
		);
	}

	public function enqueue() {
		wp_enqueue_script( 'gp-spacing-customizer', trailingslashit( plugin_dir_url( __FILE__ ) )  . 'js/spacing-customizer.js', array( 'customize-controls' ), GP_PREMIUM_VERSION, true );
		wp_enqueue_style( 'gp-spacing-customizer-controls-css', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'css/spacing-customizer.css', array(), GP_PREMIUM_VERSION );
	}

	public function to_json() {
		parent::to_json();
		// Loop through each of the settings and set up the data for it.
		foreach ( $this->settings as $setting_key => $setting_id ) {
			$this->json[ $setting_key ] = array(
				'link'  => $this->get_link( $setting_key ),
				'value' => $this->value( $setting_key ),
				'label' => isset( $this->l10n[ $setting_key ] ) ? $this->l10n[ $setting_key ] : ''
			);
		}

		$this->json[ 'element' ] = $this->element;
		$this->json[ 'title' ] = __( 'Link values','generate-spacing' );
		$this->json[ 'unlink_title' ] = __( 'Un-link values','generate-spacing' );
	}

	public function content_template() {
		?>
		<# if ( data.label ) { #>
			<label for="{{{ data.element }}}-{{{ data.top.label }}}">
				<span class="customize-control-title">{{ data.label }}</span>
			</label>
		<# } #>

		<# if ( data.description ) { #>
			<span class="description customize-control-description">{{{ data.description }}}</span>
		<# } #>

		<div class="gp-spacing-section">
			<input id="{{{ data.element }}}-{{{ data.top.label }}}" min="0" class="generate-number-control spacing-top" type="number" style="text-align: center;" {{{ data.top.link }}} value="{{{ data.top.value }}}" />
			<# if ( data.top.label ) { #>
				<span class="description" style="font-style:normal;">{{ data.top.label }}</span>
			<# } #>
		</div>

		<div class="gp-spacing-section">
			<input min="0" class="generate-number-control spacing-right" type="number" style="text-align: center;" {{{ data.right.link }}} value="{{{ data.right.value }}}" />
			<# if ( data.right.label ) { #>
				<span class="description" style="font-style:normal;">{{ data.right.label }}</span>
			<# } #>
		</div>

		<div class="gp-spacing-section">
			<input min="0" class="generate-number-control spacing-bottom" type="number" style="text-align: center;" {{{ data.bottom.link }}} value="{{{ data.bottom.value }}}" />
			<# if ( data.bottom.label ) { #>
				<span class="description" style="font-style:normal;">{{ data.bottom.label }}</span>
			<# } #>
		</div>

		<div class="gp-spacing-section">
			<input min="0" class="generate-number-control spacing-left" type="number" style="text-align: center;" {{{ data.left.link }}} value="{{{ data.left.value }}}" />
			<# if ( data.left.label ) { #>
				<span class="description" style="font-style:normal;">{{ data.left.label }}</span>
			<# } #>
		</div>

		<# if ( data.element ) { #>
			<div class="gp-spacing-section gp-link-spacing-section">
				<span class="dashicons dashicons-editor-unlink gp-link-spacing" data-element="{{ data.element }}" title="{{ data.title }}"></span>
				<span class="dashicons dashicons-admin-links gp-unlink-spacing" style="display:none" data-element="{{ data.element }}" title="{{ data.unlink_title }}"></span>
			</div>
		<# } #>
		<?php
	}
}
endif;
