<?php

class GeneratePress_Site_Layout {
	/**
	 * Set our location variable.
	 *
	 * @since 1.7
	 */
	protected $conditional = array();

	/**
	 * Set our exclusion variable.
	 *
	 * @since 1.7
	 */
	protected $exclude = array();

	/**
	 * Set our user condition variable.
	 *
	 * @since 1.7
	 */
	protected $users = array();

	/**
	 * Set up our other options.
	 *
	 * @since 1.7
	 */
	protected static $options = array();

	/**
	 * Set our post ID.
	 *
	 * @since 1.7
	 */
	protected static $post_id = '';

	/**
	 * Count how many instances are set.
	 *
	 * @since 1.7
	 */
	public static $instances = 0;

	/**
	 * Set our class and give our variables values.
	 *
	 * @since 1.7
	 *
	 * @param int $post_ID The post ID of our element.
	 */
	function __construct( $post_id ) {

		self::$post_id = $post_id;

		if ( get_post_meta( $post_id, '_generate_element_display_conditions', true ) ) {
			$this->conditional = get_post_meta( $post_id, '_generate_element_display_conditions', true );
		}

		if ( get_post_meta( $post_id, '_generate_element_exclude_conditions', true ) ) {
			$this->exclude = get_post_meta( $post_id, '_generate_element_exclude_conditions', true );
		}

		if ( get_post_meta( $post_id, '_generate_element_user_conditions', true ) ) {
			$this->users = get_post_meta( $post_id, '_generate_element_user_conditions', true );
		}

		$display = apply_filters( 'generate_layout_element_display', GeneratePress_Conditions::show_data( $this->conditional, $this->exclude, $this->users ), $post_id );

		if ( $display ) {
			add_action( 'wp',	array( $this, 'after_setup' ), 100 );

			self::$instances++;
		}

	}

	/**
	 * Return our available options.
	 *
	 * @since 1.7
	 *
	 * @return array
	 */
	public static function get_options() {
		$post_id = self::$post_id;

		return apply_filters( 'generate_layout_options', array(
			'sidebar_layout'				=> get_post_meta( $post_id, '_generate_sidebar_layout', true ),
			'footer_widgets'				=> get_post_meta( $post_id, '_generate_footer_widgets', true ),
			'disable_site_header'			=> get_post_meta( $post_id, '_generate_disable_site_header', true ),
			'disable_top_bar'				=> get_post_meta( $post_id, '_generate_disable_top_bar', true ),
			'disable_primary_navigation'	=> get_post_meta( $post_id, '_generate_disable_primary_navigation', true ),
			'disable_secondary_navigation'	=> get_post_meta( $post_id, '_generate_disable_secondary_navigation', true ),
			'disable_featured_image'		=> get_post_meta( $post_id, '_generate_disable_featured_image', true ),
			'disable_content_title'			=> get_post_meta( $post_id, '_generate_disable_content_title', true ),
			'disable_footer'				=> get_post_meta( $post_id, '_generate_disable_footer', true ),
			'content_area'					=> get_post_meta( $post_id, '_generate_content_area', true ),
		) );
	}

	/**
	 * Initiate our set layout changes.
	 *
	 * @since 1.7
	 */
	public function after_setup() {
		$options = self::get_options();

		if ( $options['sidebar_layout'] && ! self::post_meta_exists( '_generate-sidebar-layout-meta' ) ) {
			add_filter( 'generate_sidebar_layout', array( $this, 'filter_options' ) );
		}

		if ( $options['footer_widgets'] && ! self::post_meta_exists( '_generate-footer-widget-meta' ) ) {
			add_filter( 'generate_footer_widgets', array( $this, 'filter_options' ) );
		}

		if ( $options['disable_site_header'] ) {
			remove_action( 'generate_header', 'generate_construct_header' );
		}

		if ( $options['disable_top_bar'] ) {
			remove_action( 'generate_before_header', 'generate_top_bar', 5 );
		}

		if ( $options['disable_primary_navigation'] ) {
			add_filter( 'generate_navigation_location', '__return_false' );
		}

		if ( $options['disable_secondary_navigation'] ) {
			add_filter( 'has_nav_menu', array( $this, 'disable_secondary_navigation' ), 10, 2 );
		}

		if ( $options['disable_featured_image'] ) {
			remove_action( 'generate_after_entry_header', 'generate_blog_single_featured_image' );
			remove_action( 'generate_before_content', 'generate_blog_single_featured_image' );
			remove_action( 'generate_after_header', 'generate_blog_single_featured_image' );
			remove_action( 'generate_before_content', 'generate_featured_page_header_inside_single' );
			remove_action( 'generate_after_header', 'generate_featured_page_header' );
		}

		if ( $options['disable_content_title'] ) {
			add_filter( 'generate_show_title', '__return_false' );
		}

		if ( $options['disable_footer'] ) {
			remove_action( 'generate_footer', 'generate_construct_footer' );
			add_filter( 'generate_footer_widgets', '__return_null' );
		}

		if ( $options['content_area'] ) {
			add_filter( 'body_class', array( $this, 'body_classes' ) );
		}
	}

	/**
	 * Check to see if our individual post metabox has a value.
	 *
	 * @since 1.7
	 *
	 * @param string $meta The meta key we're checking for.
	 * @return bool
	 */
	public static function post_meta_exists( $meta ) {
		if ( ! is_singular() ) {
			return false;
		}

		$value = get_post_meta( get_the_ID(), $meta, true );

		if ( '_generate-footer-widget-meta' === $meta && '0' === $value ) {
			$value = true;
		}

		if ( $value ) {
			return true;
		}

		return false;
	}

	/**
	 * Filter our filterable options.
	 *
	 * @since 1.7
	 */
	public function filter_options() {
		$options = self::get_options();
		$filter = current_filter();

		if ( 'generate_sidebar_layout' === $filter ) {
			return $options['sidebar_layout'];
		}

		if ( 'generate_footer_widgets' === $filter ) {
			if ( 'no-widgets' === $options['footer_widgets'] ) {
				return 0;
			} else {
				return $options['footer_widgets'];
			}
		}
	}

	/**
	 * Disable the Secondary Navigation if set.
	 *
	 * @since 1.7
	 *
	 * @param bool $has_nav_menu
	 * @param string $location
	 * @return bool
	 */
	public static function disable_secondary_navigation( $has_nav_menu, $location ) {
		if ( 'secondary' === $location ) {
			return false;
		}

		return $has_nav_menu;
	}

	/**
	 * Sets any necessary body classes.
	 *
	 * @since 1.7
	 *
	 * @param array $classes Our existing body classes.
	 * @return array Our new set of classes.
	 */
	public static function body_classes( $classes ) {
		$options = self::get_options();

		if ( 'full-width' === $options['content_area'] ) {
			$classes[] = 'full-width-content';
		}

		if ( 'contained' === $options['content_area'] ) {
			$classes[] = 'contained-content';
		}

		return $classes;
	}

}
