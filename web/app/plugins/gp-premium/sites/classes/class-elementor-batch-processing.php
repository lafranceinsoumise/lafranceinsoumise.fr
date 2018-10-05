<?php
/**
 * Searches Elementor for images to download and update.
 *
 * @since 1.6
 */

namespace Elementor;

// If plugin - 'Elementor' not exist then return.
if ( ! class_exists( '\Elementor\Plugin' ) ) {
	return;
}

namespace Elementor\TemplateLibrary;

use Elementor\Core\Settings\Manager as SettingsManager;
use Elementor\TemplateLibrary\Classes\Import_Images;
use Elementor\TemplateLibrary;
use Elementor\TemplateLibrary\Classes;
use Elementor\Api;
use Elementor\PageSettings\Page;

// For working protected methods defined in.
// file '/elementor/includes/template-library/sources/base.php'.
use Elementor\Plugin;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( '\Elementor\TemplateLibrary\Source_Base' ) ) {
	return;
}

/**
 * Elementor Source Remote.
 *
 * @see https://github.com/pojome/elementor/blob/v1.9.2/includes/template-library/sources/remote.php
 */
class GeneratePress_Sites_Process_Elementor extends Source_Base {

	/**
	 * Get ID
	 *
	 * @since 1.6
	 *
	 * @return string
	 */
	public function get_id() {
		return 'remote';
	}

	/**
	 * Get Title.
	 *
	 * @since 1.0.4
	 *
	 * @return string
	 */
	public function get_title() {}

	/**
	 * Get Data
	 *
	 * @since 1.6
	 *
	 * @return void
	 */
	public function register_data() {}

	/**
	 * @since 1.0.0
	 * @access public
	*/
	public function get_items( $args = [] ) {
		$templates_data = Api::get_templates_data();

		$templates = [];

		if ( ! empty( $templates_data ) ) {
			foreach ( $templates_data as $template_data ) {
				$templates[] = $this->get_item( $template_data );
			}
		}

		if ( ! empty( $args ) ) {
			$templates = wp_list_filter( $templates, $args );
		}

		return $templates;
	}

	/**
	 * @since 1.0.0
	 * @access public
	 * @param array $template_data
	 *
	 * @return array
	 */
	public function get_item( $template_data ) {
		$favorite_templates = $this->get_user_meta( 'favorites' );

		return [
			'template_id' => $template_data['id'],
			'source' => $this->get_id(),
			'title' => $template_data['title'],
			'thumbnail' => $template_data['thumbnail'],
			'date' => $template_data['tmpl_created'],
			'author' => $template_data['author'],
			'tags' => json_decode( $template_data['tags'] ),
			'isPro' => ( '1' === $template_data['is_pro'] ),
			'popularityIndex' => (int) $template_data['popularity_index'],
			'trendIndex' => (int) $template_data['trend_index'],
			'hasPageSettings' => ( '1' === $template_data['has_page_settings'] ),
			'url' => $template_data['url'],
			'favorite' => ! empty( $favorite_templates[ $template_data['id'] ] ),
		];
	}

	/**
	 * @since 1.0.0
	 * @access public
	*/
	public function save_item( $template_data ) {
		return false;
	}

	/**
	 * @since 1.0.0
	 * @access public
	*/
	public function update_item( $new_data ) {
		return false;
	}

	/**
	 * @since 1.0.0
	 * @access public
	*/
	public function delete_template( $template_id ) {
		return false;
	}

	/**
	 * @since 1.0.0
	 * @access public
	*/
	public function export_template( $template_id ) {
		return false;
	}

	/**
	 * @since 1.5.0
	 * @access public
	*/
	public function get_data( array $args, $context = 'display' ) {
		$data = Api::get_template_content( $args['template_id'] );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		// TODO: since 1.5.0 to content container named `content` instead of `data`.
		if ( ! empty( $data['data'] ) ) {
			$data['content'] = $data['data'];
			unset( $data['data'] );
		}

		$data['content'] = $this->replace_elements_ids( $data['content'] );
		$data['content'] = $this->process_export_import_content( $data['content'], 'on_import' );

		if ( ! empty( $args['page_settings'] ) && ! empty( $data['page_settings'] ) ) {
			$page = new Page( [
				'settings' => $data['page_settings'],
			] );

			$page_settings_data = $this->process_element_export_import_content( $page, 'on_import' );
			$data['page_settings'] = $page_settings_data['settings'];
		}

		return $data;
	}

	/*
	 * Begin base.php.
	 *
	 * @see https://github.com/pojome/elementor/blob/v1.9.2/includes/template-library/sources/base.php
	 */

	 /**
 	 * @since 1.0.0
 	 * @access protected
 	*/
 	protected function replace_elements_ids( $content ) {
 		return Plugin::$instance->db->iterate_data( $content, function( $element ) {
 			$element['id'] = Utils::generate_random_string();
 			return $element;
 		} );
 	}

	/**
	 * @since 1.5.0
	 * @access protected
	 * @param array  $content a set of elements.
	 * @param string $method  (on_export|on_import).
	 *
	 * @return mixed
	 */
	protected function process_export_import_content( $content, $method ) {
		return Plugin::$instance->db->iterate_data(
			$content, function( $element_data ) use ( $method ) {
				$element = Plugin::$instance->elements_manager->create_element_instance( $element_data );

				// If the widget/element isn't exist, like a plugin that creates a widget but deactivated
				if ( ! $element ) {
					return null;
				}

				return $this->process_element_export_import_content( $element, $method );
			}
		);
	}

	/**
	 * @since 1.5.0
	 * @access protected
	 * @param \Elementor\Controls_Stack $element
	 * @param string                    $method
	 *
	 * @return array
	 */
	protected function process_element_export_import_content( $element, $method ) {
		$element_data = $element->get_data();

		if ( method_exists( $element, $method ) ) {
			// TODO: Use the internal element data without parameters.
			$element_data = $element->{$method}( $element_data );
		}

		foreach ( $element->get_controls() as $control ) {
			$control_class = Plugin::$instance->controls_manager->get_control( $control['type'] );

			// If the control isn't exist, like a plugin that creates the control but deactivated.
			if ( ! $control_class ) {
				return $element_data;
			}

			if ( method_exists( $control_class, $method ) ) {
				$element_data['settings'][ $control['name'] ] = $control_class->{$method}( $element->get_settings( $control['name'] ), $control );
			}
		}

		return $element_data;
	}

	/**
	 * Import
	 *
	 * @since 1.0.14
	 * @return void
	 */
	public function import() {
		\GeneratePress_Sites_Helper::log( '== Start Processing Elementor Images ==' );
		$post_ids = \GeneratePress_Sites_Helper::get_all_posts();
		if ( is_array( $post_ids ) ) {
			foreach ( $post_ids as $post_id ) {
				$this->import_single_post( $post_id );
			}
		}

	}

	/**
	 * Update post meta.
	 *
	 * @since 1.6
	 * @param  integer $post_id Post ID.
	 * @return void
	 */
	public function import_single_post( $post_id = 0 ) {

		if ( ! empty( $post_id ) ) {
			$already_imported = get_post_meta( $post_id, '_generate_sites_elementor_images_imported', true );

			if ( empty( $already_imported ) ) {

				$data = get_post_meta( $post_id, '_elementor_data', true );

				if ( ! empty( $data ) ) {

					$data = json_decode( $data, true );

					$data = $this->replace_elements_ids( $data );
					$data = $this->process_export_import_content( $data, 'on_import' );

					// Update processed meta.
					update_metadata( 'post', $post_id, '_elementor_data', $data );
					update_metadata( 'post', $post_id, '_generate_sites_elementor_images_imported', true );

					// !important, Clear the cache after images import.
					Plugin::$instance->posts_css_manager->clear_cache();

				}
			}
		}

	}
}
