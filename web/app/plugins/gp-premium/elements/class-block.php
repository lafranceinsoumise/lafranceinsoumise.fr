<?php
/**
 * This file displays our block elements on the site.
 *
 * @package GP Premium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access, please.
}

/**
 * Build our Block Elements.
 */
class GeneratePress_Block_Element {

	/**
	 * The element ID.
	 *
	 * @since 1.11.0
	 * @var int ID of the element.
	 */
	protected $post_id = '';

	/**
	 * The element type.
	 *
	 * @since 1.11.0
	 * @var string Type of element.
	 */
	protected $type = '';

	/**
	 * Kicks it all off.
	 *
	 * @since 1.11.0
	 *
	 * @param int $post_id The element post ID.
	 */
	public function __construct( $post_id ) {
		$this->post_id = $post_id;
		$this->type = get_post_meta( $post_id, '_generate_block_type', true );

		$display_conditions = get_post_meta( $post_id, '_generate_element_display_conditions', true ) ? get_post_meta( $post_id, '_generate_element_display_conditions', true ) : array();
		$exclude_conditions = get_post_meta( $post_id, '_generate_element_exclude_conditions', true ) ? get_post_meta( $post_id, '_generate_element_exclude_conditions', true ) : array();
		$user_conditions = get_post_meta( $post_id, '_generate_element_user_conditions', true ) ? get_post_meta( $post_id, '_generate_element_user_conditions', true ) : array();

		$display = apply_filters(
			'generate_block_element_display',
			GeneratePress_Conditions::show_data(
				$display_conditions,
				$exclude_conditions,
				$user_conditions
			),
			$post_id
		);

		if ( $display ) {
			$hook = get_post_meta( $post_id, '_generate_hook', true );
			$custom_hook = get_post_meta( $post_id, '_generate_custom_hook', true );
			$priority = get_post_meta( $post_id, '_generate_hook_priority', true );

			if ( '' === $priority ) {
				$priority = 10;
			}

			switch ( $this->type ) {
				case 'site-header':
					$hook = 'generate_header';
					break;

				case 'site-footer':
					$hook = 'generate_footer';
					break;

				case 'right-sidebar':
					$hook = 'generate_before_right_sidebar_content';
					break;

				case 'left-sidebar':
					$hook = 'generate_before_left_sidebar_content';
					break;

				case 'custom':
					$hook = $custom_hook;
					break;
			}

			if ( ! $hook ) {
				return;
			}

			if ( 'generate_header' === $hook ) {
				remove_action( 'generate_header', 'generate_construct_header' );
			}

			if ( 'generate_footer' === $hook ) {
				remove_action( 'generate_footer', 'generate_construct_footer' );
			}

			add_action( esc_attr( $hook ), array( $this, 'build_hook' ), absint( $priority ) );

			if ( 'right-sidebar' === $this->type || 'left-sidebar' === $this->type ) {
				add_filter( 'sidebars_widgets', array( $this, 'remove_sidebar_widgets' ) );
				add_filter( 'generate_show_default_sidebar_widgets', '__return_false' );
			}

			add_filter( 'generateblocks_do_content', array( $this, 'do_block_content' ) );
		}

	}

	/**
	 * Tell GenerateBlocks about our block element content so it can build CSS.
	 *
	 * @since 1.11.0
	 * @param string $content The existing content.
	 */
	public function do_block_content( $content ) {
		if ( has_blocks( $this->post_id ) ) {
			$block_element = get_post( $this->post_id );

			if ( ! $block_element || 'gp_elements' !== $block_element->post_type ) {
				return $content;
			}

			if ( 'publish' !== $block_element->post_status || ! empty( $block_element->post_password ) ) {
				return $content;
			}

			$content .= $block_element->post_content;
		}

		return $content;
	}

	/**
	 * Remove existing sidebar widgets.
	 *
	 * @since 1.11.0
	 * @param array $widgets The existing widgets.
	 */
	public function remove_sidebar_widgets( $widgets ) {
		if ( 'right-sidebar' === $this->type ) {
			unset( $widgets['sidebar-1'] );
		}

		if ( 'left-sidebar' === $this->type ) {
			unset( $widgets['sidebar-2'] );
		}

		return $widgets;
	}

	/**
	 * Builds the HTML structure for Page Headers.
	 *
	 * @since 1.11.0
	 */
	public function build_hook() {
		echo GeneratePress_Elements_Helper::build_content( $this->post_id); // phpcs:ignore -- No escaping needed.
	}
}
