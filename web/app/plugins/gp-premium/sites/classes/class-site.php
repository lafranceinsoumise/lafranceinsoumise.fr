<?php
defined( 'WPINC' ) or die;

class GeneratePress_Site {

	/**
	 * Directory to our site.
	 *
	 * @var string
	 */
	protected $directory;

	/**
	 * Name of our site.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * URL to our preview.
	 *
	 * @var string
	 */
	protected $preview_url;

	/**
	 * Name of site author.
	 *
	 * @var string
	 */
	protected $author_name;

	/**
	 * URL of site author.
	 *
	 * @var string
	 */
	protected $author_url;

	/**
	 * Description of the site.
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Icon filename.
	 *
	 * @var string
	 */
	protected $icon;

	/**
	 * Screenshot filename.
	 *
	 * @var string
	 */
	protected $screenshot;

	/**
	 * Page Builder.
	 *
	 * @var string
	 */
	protected $page_builder;

	/**
	 * Minimum version.
	 *
	 * @var int|string
	 */
	protected $minimum_version;

	/**
	 * Check if site is installable.
	 *
	 * @var bool
	 */
	protected $installable;

	/**
	 * Get it rockin'
	 *
	 * @param array $config
	 */
	public function __construct( $config = array() ) {

		$config = wp_parse_args( $config, array(
			'directory'		=> '',
			'name' 			=> '',
			'preview_url' 	=> '',
			'author_name'	=> '',
			'author_url'	=> '',
			'icon'			=> 'icon.png',
			'screenshot'	=> 'screenshot.png',
			'page_builder'	=> array(),
			'min_version'	=> GP_PREMIUM_VERSION,
		) );

		$this->helpers = new GeneratePress_Sites_Helper();

		$this->directory	= trailingslashit( $config['directory'] );

		$provider = parse_url( $this->directory );

		if ( ! isset( $provider['host'] ) ) {
			return;
		}

		if ( ! in_array( $provider['host'], ( array ) get_transient( 'generatepress_sites_trusted_providers' ) ) ) {
			return;
		}

		$this->name 		= $config['name'];
		$this->slug			= str_replace( ' ', '_', strtolower( $this->name ) );
		$this->preview_url 	= $config['preview_url'];
		$this->author_name	= $config['author_name'];
		$this->author_url	= $config['author_url'];
		$this->description	= $config['description'];
		$this->icon			= $config['icon'];
		$this->screenshot	= $config['screenshot'];
		$this->page_builder = $config['page_builder'];
		$this->min_version	= $config['min_version'];
		$this->installable	= true;

		if ( empty( $this->min_version ) ) {
			$this->min_version = GP_PREMIUM_VERSION;
		}

		if ( version_compare( GP_PREMIUM_VERSION, $config['min_version'], '<' ) ) {
			$this->installable = false;
		}

		add_action( 'generate_inside_sites_container',						array( $this, 'build_box' ) );
		add_action( "wp_ajax_generate_setup_site_{$this->slug}",			array( $this, 'setup_site' ), 10, 0 );
		add_action( "wp_ajax_generate_check_plugins_{$this->slug}",			array( $this, 'check_plugins' ), 10, 0 );
		add_action( "wp_ajax_generate_backup_options_{$this->slug}",		array( $this, 'backup_options' ), 10, 0 );
		add_action( "wp_ajax_generate_import_options_{$this->slug}",		array( $this, 'import_options' ), 10, 0 );
		add_action( "wp_ajax_generate_activate_plugins_{$this->slug}",		array( $this, 'activate_plugins' ), 10, 0 );
		add_action( "wp_ajax_generate_import_site_options_{$this->slug}",	array( $this, 'import_site_options' ), 10, 0 );
		add_action( "wp_ajax_generate_download_content_{$this->slug}",		array( $this, 'download_content' ), 10, 0 );
		add_action( "wp_ajax_generate_import_content_{$this->slug}",		array( $this, 'import_content' ), 10, 0 );
		add_action( "wp_ajax_generate_import_widgets_{$this->slug}",		array( $this, 'import_widgets' ), 10, 0 );

	}

	/**
	 * Build the site details, including the screenshot and description.
	 *
	 * @since 1.6
	 */
	public function site_details() {

		printf( '<div class="site-screenshot">
					%1$s
						<img src="%2$s" alt="%3$s" />
					%4$s
				</div>',
				$this->preview_url ? '<a class="preview-site" href="' . esc_url( $this->preview_url ) . '" target="_blank">' : '',
				esc_url( $this->directory . $this->screenshot ),
				esc_attr( $this->name ),
				$this->preview_url ? '</a>' : ''
		);

		printf( '<div class="site-description">
					<h3>
						%1$s
					</h3>
					<span class="author-name">
						<a href="%2$s" target="_blank" rel="noopener">
							%3$s
						</a>
					</span>
					<p>
						%4$s
					</p>
				</div>',
				$this->name,
				esc_url( $this->author_url ),
				$this->author_name,
				$this->description
		);

	}

	/**
	 * Build the site controls.
	 *
	 * @since 1.6
	 */
	public function site_controls() {
		?>
		<div class="controls">
			<button title="<?php esc_attr_e( 'Previous Site', 'gp-premium' ); ?>" class="prev"><span class="screen-reader-text"><?php esc_html_e( 'Previous', 'gp-premium' ); ?></span></button>
			<button title="<?php esc_attr_e( 'Next Site', 'gp-premium' ); ?>" class="next"><span class="screen-reader-text"><?php esc_html_e( 'Next', 'gp-premium' ); ?></span></button>
			<button title="<?php esc_attr_e( 'Close', 'gp-premium' ); ?>" class="close"><span class="screen-reader-text"><?php esc_html_e( 'Close', 'gp-premium' ); ?></span></button>
		</div>
		<?php
	}

	/**
	 * Build the loading icon.
	 *
	 * @since 1.6
	 */
	public function loading_icon() {
		?>
		<svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="40px" height="40px" viewBox="0 0 40 40" enable-background="new 0 0 40 40" xml:space="preserve">
			<path opacity="0.2" fill="#000" d="M20.201,5.169c-8.254,0-14.946,6.692-14.946,14.946c0,8.255,6.692,14.946,14.946,14.946s14.946-6.691,14.946-14.946C35.146,11.861,28.455,5.169,20.201,5.169z M20.201,31.749c-6.425,0-11.634-5.208-11.634-11.634c0-6.425,5.209-11.634,11.634-11.634c6.425,0,11.633,5.209,11.633,11.634C31.834,26.541,26.626,31.749,20.201,31.749z"/>
			<path fill="#000" d="M26.013,10.047l1.654-2.866c-2.198-1.272-4.743-2.012-7.466-2.012h0v3.312h0C22.32,8.481,24.301,9.057,26.013,10.047z">
				<animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 20 20" to="360 20 20" dur="0.5s" repeatCount="indefinite"/>
			</path>
		</svg>
		<?php
	}

	/**
	 * Build our site boxes in our Dashboard.
	 *
	 * @since 1.6
	 */
	public function build_box() {

		$site_data = array(
			'slug'			=> $this->slug,
			'preview_url' 	=> $this->preview_url,
		);

		$page_builders = array();
		foreach ( ( array ) $this->page_builder as $builder ) {
			$page_builders = str_replace( ' ', '-', strtolower( $builder ) );
		}

		$site_classes = array(
			'site-box',
			$page_builders,
			! $this->installable ? 'disabled-site' : ''
		);

		?>
		<div class="<?php echo implode( ' ', $site_classes ); ?>" data-site-data="<?php echo htmlspecialchars( json_encode( $site_data ), ENT_QUOTES, 'UTF-8' ); ?>">
			<div class="steps step-one">
				<div class="site-info">
					<div class="site-description">
						<a href="<?php echo esc_url( $this->author_url ); ?>" target="_blank" rel="noopener"><span class="author-name"><?php echo $this->author_name; ?></span></a>
						<h3><a class="site-details" href="#"><?php echo $this->name; ?></a></h3>
						<?php
						if ( $this->description ) {
							echo '<a class="site-details" href="#"> ' . wpautop( $this->description ) . '</a>';
						}
						?>

						<?php if ( $this->installable ) : ?>
							<button class="button preview-site"><?php _e( 'Preview', 'gp-premium' ); ?></button>
							<button class="button-primary site-details"><?php _e( 'Details', 'gp-premium' ); ?></button>
						<?php else : ?>
							<span class="version-required-message">
								<?php printf( _x( 'Requires GP Premium %s', 'required version number', 'gp-premium' ), $this->min_version ); ?>
							</span>
						<?php endif; ?>
					</div>
				</div>

				<div class="site-screenshot">
					<img class="lazyload" src="<?php echo GENERATE_SITES_URL; ?>/assets/images/screenshot.png" data-src="<?php echo esc_url( $this->directory . $this->screenshot ); ?>" alt="" />
				</div>

				<div class="site-title">
					<span class="author-name"><?php echo $this->author_name; ?></span>
					<h3><?php echo $this->name; ?></h3>
				</div>
			</div>

			<div class="steps step-overview" style="display: none;">
				<div class="step-information">
					<?php $this->site_controls(); ?>
					<h3 style="margin-bottom: 0;"><?php _e( 'Overview', 'gp-premium' ); ?></h3>

					<div class="separator"></div>

					<h3><?php _e( 'Theme Options', 'gp-premium' ); ?></h3>
					<div class="theme-options loading">
						<?php $this->loading_icon(); ?>
					</div>
					<p style="display:none;"></p>

					<div class="site-content-description">
						<div class="separator"></div>
						<h3><?php _e( 'Site Content', 'gp-premium' ); ?><span class="step-note"><?php _e( 'Optional', 'gp-premium' ); ?></span></h3>
						<div class="site-content loading">
							<?php $this->loading_icon(); ?>
						</div>
						<p style="display:none;"></p>

						<div class="plugin-area" style="display:none;">
							<h4><?php _e( 'Plugins', 'gp-premium' ); ?></h4>
							<div class="checking-for-plugins loading">
								<?php $this->loading_icon(); ?>
							</div>

							<div class="automatic-plugins" style="display:none">
								<p><?php _e( 'The following plugins can be installed and activated automatically.', 'gp-premium' ); ?></p>
								<ul></ul>
							</div>

							<div class="installed-plugins" style="display:none">
								<p><?php _e( 'The following plugins are already installed.', 'gp-premium' ); ?></p>
								<ul></ul>
							</div>

							<div class="manual-plugins" style="display:none;">
								<p><?php _e( 'The following plugins need to be installed and activated manually.', 'gp-premium' ); ?></p>
								<ul></ul>
							</div>
						</div>
					</div>
				</div>

				<div class="site-overview-details">
					<div class="actions">
						<div class="left">
							<?php if ( $this->preview_url ) { ?>
								<a class="button preview-site" href="<?php echo esc_url( $this->preview_url ); ?>" target="_blank"><?php _e( 'Preview', 'gp-premium' ); ?></a>
							<?php } ?>
						</div>
						<div class="right">
							<span class="error-message" style="display: none;"></span>

							<div class="loading">
								<span class="site-message"><?php _e( 'Gathering data', 'gp-premium' ); ?></span>
								<?php $this->loading_icon(); ?>
							</div>

							<button style="display: none;" class="button-primary next-step"><?php _e( 'Get Started', 'gp-premium' ); ?></button>
						</div>
					</div>

					<?php $this->site_details(); ?>

				</div>
			</div>

			<div class="steps" style="display: none;">
				<div class="step-information">
					<?php $this->site_controls(); ?>
					<h3><?php _e( 'GeneratePress Options', 'gp-premium' ); ?></h3>

					<div class="separator"></div>

					<p><?php _e( 'This step will backup your current theme options, then import the new ones.', 'gp-premium' ); ?></p>

					<div class="required-modules">
						<p><?php _e( 'The following GP Premium modules will be activated:', 'gp-premium' ); ?></p>
						<ul></ul>
					</div>
				</div>

				<div class="site-overview-details">
					<div class="actions">
						<div class="left">
							<button class="button start-over"><?php _e( 'Go Back', 'gp-premium' ); ?></button>
						</div>
						<div class="right">
							<form method="post">
								<span class="error-message" style="display: none;"></span>

								<?php submit_button(
									__( 'Backup Options', 'gp-premium' ),
									'button-primary backup-options site-action',
									'submit',
									false,
									array(
										'id' => ''
									)
								); ?>

								<?php submit_button(
									__( 'Import Options', 'gp-premium' ),
									'button-primary import-options site-action',
									'submit',
									false,
									array(
										'id' => '',
										'style' => 'display:none'
									)
								); ?>

								<div class="loading" style="display: none;">
									<span class="site-message" style="display: none;"></span>
									<?php $this->loading_icon(); ?>
								</div>

								<span class="complete" style="display: none;"></span>
							</form>
						</div>
					</div>
					<?php $this->site_details(); ?>
				</div>
			</div>

			<div class="steps site-content-description" style="display: none;">
				<div class="step-information">
					<?php $this->site_controls(); ?>
					<h3><?php _e( 'Content', 'gp-premium' ); ?><span class="step-note"><?php _e( 'Optional', 'gp-premium' ); ?></span></h3>

					<div class="separator"></div>

					<div class="important-note">
						<label>
							<input id="confirm-content-import" name="confirm-content-import" class="confirm-content-import" type="checkbox" />
							<?php _e( 'I understand that this step will add content, site options, menus, widgets and plugins to my site.', 'gp-premium' ); ?>
						</label>
					</div>

					<p>
						<?php
						printf(
							__( 'For best results, only install this demo content on fresh sites with no content. If you have already installed another GeneratePress Site, be sure to %s by deleting the content, plugins and menus that it added.', 'gp-premium' ),
							sprintf(
								'<a href="https://docs.generatepress.com/article/removing-imported-site/" target="_blank" rel="noopener">%s</a>',
								__( 'clean it up', 'gp-premium' )
							)
						);
						?>
					</p>
					<p><?php _e( 'You can <a href="#" class="next-step">skip</a> this step if you already have content and do not want the demo content imported.', 'gp-premium' ); ?></p>

					<div class="plugin-area" style="display:none;">
						<div class="checking-for-plugins loading">
							<?php $this->loading_icon(); ?>
						</div>

						<div class="automatic-plugins" style="display:none">
							<p><?php _e( 'The following plugins can be installed and activated automatically.', 'gp-premium' ); ?></p>
							<ul></ul>
						</div>

						<div class="installed-plugins" style="display:none">
							<p><?php _e( 'The following plugins are already installed.', 'gp-premium' ); ?></p>
							<ul></ul>
						</div>

						<div class="manual-plugins" style="display:none;">
							<p><?php _e( 'The following plugins need to be installed and activated manually.', 'gp-premium' ); ?></p>
							<ul></ul>
						</div>
					</div>
				</div>

				<div class="site-overview-details">
					<div class="actions">
						<div class="left">
							<?php submit_button( __( 'Skip', 'gp-premium' ), 'button next-step', 'submit', false, array( 'id' => '' ) ); ?>
						</div>
						<div class="right">
							<form method="post">
								<span class="error-message" style="display: none;"></span>

								<?php
								submit_button(
									__( 'Import Content', 'gp-premium' ),
									'button-primary import-content site-action',
									'submit',
									false,
									array(
										'id' => '',
										'disabled' => 'disabled'
									)
								);
								?>

								<div class="loading" style="display: none;">
									<span class="site-message" style="display: none;"></span>
									<?php $this->loading_icon(); ?>
								</div>

								<span class="complete" style="display: none;"></span>
							</form>
						</div>
					</div>
					<?php $this->site_details(); ?>
				</div>
			</div>

			<div class="steps last-step" style="display: none;">
				<div class="step-information">
					<?php $this->site_controls(); ?>
					<h3><?php _e( 'All done!', 'gp-premium' ); ?></h3>
					<p><?php _e( 'Your site is ready to go!', 'gp-premium' ); ?></p>

					<?php if ( $this->author_name ) : ?>
						<p class="author-credit-byline">
							<?php printf( __( 'Crafted with %s by', 'gp-premium' ), '<span class="dashicons dashicons-heart"></span>' ); ?>
						</p>

						<span class="author-credit">
							<?php
							printf( '%1$s%2$s%3$s',
								$this->author_url ? '<a href="' . $this->author_url . '" target="_blank">' : '',
								$this->author_name,
								$this->author_url ? '</a>' : ''
							);
							?>
						</span>
					<?php endif; ?>
				</div>

				<div class="site-overview-details">
					<div class="actions">
						<div class="left">
							<button class="button start-over"><?php _e( 'Start Over', 'gp-premium' ); ?></button>
						</div>
						<div class="right">
							<a href="<?php echo esc_url( site_url() ); ?>" class="button-primary"><?php _e( 'View Site', 'gp-premium' ); ?></a>
						</div>
					</div>
					<?php $this->site_details(); ?>
				</div>

			</div>

			<div class="site-demo" style="display: none;">
				<div class="demo-loading loading">
					<?php $this->loading_icon(); ?>
				</div>

				<iframe></iframe>
				<div class="demo-panel">
					<button title="<?php esc_attr_e( 'Close', 'gp-premium' ); ?>" class="close-demo"><span class="screen-reader-text"><?php _e( 'Close', 'gp-premium' ); ?></span></button>
					<button title="<?php esc_attr_e( 'Previous', 'gp-premium' ); ?>" class="prev"><span class="screen-reader-text"><?php _e( 'Previous', 'gp-premium' ); ?></span></button>
					<button title="<?php esc_attr_e( 'Next', 'gp-premium' ); ?>" class="next"><span class="screen-reader-text"><?php _e( 'Next', 'gp-premium' ); ?></span></button>
					<button title="<?php esc_attr_e( 'Desktop', 'gp-premium' ); ?>" class="show-desktop"><span class="screen-reader-text"><?php _e( 'Desktop', 'gp-premium' ); ?></span></button>
					<button title="<?php esc_attr_e( 'Tablet', 'gp-premium' ); ?>" class="show-tablet"><span class="screen-reader-text"><?php _e( 'Tablet', 'gp-premium' ); ?></span></button>
					<button title="<?php esc_attr_e( 'Mobile', 'gp-premium' ); ?>" class="show-mobile"><span class="screen-reader-text"><?php _e( 'Mobile', 'gp-premium' ); ?></span></button>
					<button class="button button-primary get-started"><?php _e( 'Details', 'gp-premium' ); ?></button>
				</div>
			</div>
		</div>
	<?php

	}

	/**
	 * Backup our existing GeneratePress options.
	 *
	 * @since 1.6
	 */
	public function backup_options() {

		check_ajax_referer( 'generate_sites_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$theme_mods = GeneratePress_Sites_Helper::get_theme_mods();
		$settings = GeneratePress_Sites_Helper::get_theme_settings();

		$data = array(
			'mods' => array(),
			'options' => array()
		);

		foreach ( $theme_mods as $theme_mod ) {
			$data['mods'][$theme_mod] = get_theme_mod( $theme_mod );
		}

		foreach ( $settings as $setting ) {
			$data['options'][$setting] = get_option( $setting );
		}

		echo json_encode( $data );

		die();

	}

	/**
	 * Tells our JS which files exist.
	 *
	 * @since 1.6
	 */
	public function setup_site() {

		check_ajax_referer( 'generate_sites_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = GeneratePress_Sites_Helper::get_options( $this->directory . 'options.json' );

		$data['modules'] = $settings['modules'];
		$data['plugins'] = $settings['plugins'];

		if ( GeneratePress_Sites_Helper::file_exists( $this->directory . 'options.json' ) ) {
			$data['options'] = true;
		} else {
			$data['options'] = false;
		}

		if ( GeneratePress_Sites_Helper::file_exists( $this->directory . 'content.xml' ) ) {
			$data['content'] = true;
		} else {
			$data['content'] = false;
		}

		if ( GeneratePress_Sites_Helper::file_exists( $this->directory . 'widgets.wie' ) ) {
			$data['widgets'] = true;
		} else {
			$data['widgets'] = false;
		}

		wp_send_json( $data );

		die();

	}

	/**
	 * Import our demo GeneratePress options.
	 *
	 * @since 1.6
	 */
	public function import_options() {

		check_ajax_referer( 'generate_sites_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = GeneratePress_Sites_Helper::get_options( $this->directory . 'options.json' );

		// Remove all existing theme options.
		$option_keys = array(
			'generate_settings',
			'generate_background_settings',
			'generate_blog_settings',
			'generate_hooks',
			'generate_page_header_settings',
			'generate_secondary_nav_settings',
			'generate_spacing_settings',
			'generate_menu_plus_settings',
			'generate_woocommerce_settings',
		);

		foreach ( $option_keys as $key ) {
			delete_option( $key );
		}

		// Remove existing theme mods.
		remove_theme_mods();

		// Remove existing activated premium modules.
		$premium_modules = generatepress_get_site_premium_modules();

		foreach ( $premium_modules as $name => $key ) {
			delete_option( $key );
		}

		// Activate necessary modules.
		foreach ( $settings['modules'] as $name => $key ) {
			// Only allow valid premium modules.
			if ( ! in_array( $key, $premium_modules ) ) {
				GeneratePress_Sites_Helper::log( 'Bad premium module key: ' . $key );
				continue;
			}

			update_option( $key, 'activated' );
		}

		// Set theme mods.
		foreach ( $settings['mods'] as $key => $val ) {
			// Only allow valid theme mods.
			if ( ! in_array( $key, GeneratePress_Sites_Helper::get_theme_mods() ) ) {
				GeneratePress_Sites_Helper::log( 'Bad theme mod key: ' . $key );
				continue;
			}

			set_theme_mod( $key, $val );
		}

		// Set theme options.
		foreach ( $settings['options'] as $key => $val ) {
			// Only allow valid options.
			if ( ! in_array( $key, GeneratePress_Sites_Helper::get_theme_settings() ) ) {
				GeneratePress_Sites_Helper::log( 'Bad theme setting key: ' . $key );
				continue;
			}

			// Import any images
			if ( is_array( $val ) || is_object( $val ) ) {
				foreach ( $val as $option_name => $option_value ) {
					if ( is_string( $option_value ) && preg_match( '/\.(jpg|jpeg|png|gif)/i', $option_value ) ) {

						$data = GeneratePress_Sites_Helper::sideload_image( $option_value );

						if ( ! is_wp_error( $data ) ) {
							$val[$option_name] = $data->url;
						}

					}
				}
			}

			update_option( $key, $val );
		}

		// Remove dynamic CSS cache.
		delete_option( 'generate_dynamic_css_output' );
		delete_option( 'generate_dynamic_css_cached_version' );

		// Custom CSS.
		$css = $settings['custom_css'];
		$css = '/* GeneratePress Site CSS */ ' . $css . ' /* End GeneratePress Site CSS */';

		$current_css = wp_get_custom_css_post();
		$current_css->post_content = preg_replace( '#(/\\* GeneratePress Site CSS \\*/).*?(/\\* End GeneratePress Site CSS \\*/)#s', '', $current_css->post_content );
		$css = $current_css->post_content . $css;

		wp_update_custom_css_post( $css );

		die();

	}

	public function download_content() {

		check_ajax_referer( 'generate_sites_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Increase PHP max execution time.
		set_time_limit( apply_filters( 'generate_sites_content_import_time_limit', 300 ) );

		$xml_path = $this->directory . 'content.xml';
		$xml_file = GeneratePress_Sites_Helper::download_file( $xml_path );
		$xml_path = $xml_file['data']['file'];

		if ( file_exists( $xml_path ) ) {
			set_transient( 'generatepress_sites_content_file', $xml_path, HOUR_IN_SECONDS );
		}

		die();
	}

	/**
	 * Import our demo content.
	 *
	 * @since 1.6
	 */
	public function import_content() {

		check_ajax_referer( 'generate_sites_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Increase PHP max execution time.
		set_time_limit( apply_filters( 'generate_sites_content_import_time_limit', 300 ) );

		// Disable import of authors.
		add_filter( 'wxr_importer.pre_process.user', '__return_false' );

		// Disables generation of multiple image sizes (thumbnails) in the content import step.
		if ( ! apply_filters( 'generate_sites_regen_thumbnails', true ) ) {
			add_filter( 'intermediate_image_sizes_advanced', '__return_null' );
		}

		// Import content
		$content = get_transient( 'generatepress_sites_content_file' );

		if ( $content ) {
			GeneratePress_Sites_Helper::import_xml( $content, $this->slug );
			delete_transient( 'generatepress_sites_content_file' );
		}

		die();

	}

	/**
	 * Import our widgets.
	 *
	 * @since 1.6
	 */
	public function import_widgets() {

		check_ajax_referer( 'generate_sites_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$widgets_path = $this->directory . 'widgets.wie';

		$wie_file = GeneratePress_Sites_Helper::download_file( $widgets_path );
		$wie_path = $wie_file['data']['file'];

		$data = implode( '', file( $wie_path ) );
		$data = json_decode( $data );

		GeneratePress_Sites_Helper::clear_widgets();

		$widgets_importer = GeneratePress_Sites_Widget_Importer::instance();
		$widgets_importer->wie_import_data( $data );

		die();

	}

	/**
	 * Import any necessary site options.
	 *
	 * @since 1.6
	 */
	public function import_site_options() {

		check_ajax_referer( 'generate_sites_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = GeneratePress_Sites_Helper::get_options( $this->directory . 'options.json' );

		delete_option( 'generate_page_header_global_locations' );

		foreach( $settings['site_options'] as $key => $val ) {

			switch( $key ) {

				case 'page_for_posts':
				case 'page_on_front':
					GeneratePress_Sites_Helper::set_reading_pages( $key, $val, $this->slug );
				break;

				case 'woocommerce_shop_page_id':
				case 'woocommerce_cart_page_id':
				case 'woocommerce_checkout_page_id':
				case 'woocommerce_myaccount_page_id':
					GeneratePress_Sites_Helper::set_woocommerce_pages( $key, $val, $this->slug );
				break;

				case 'nav_menu_locations':
					GeneratePress_Sites_Helper::set_nav_menu_locations( $val );
				break;

				case 'page_header_global_locations':
					GeneratePress_Sites_Helper::set_global_page_header_locations( $val, $this->slug );
				break;

				case 'page_headers':
					GeneratePress_Sites_Helper::set_page_headers( $val, $this->slug );
				break;

				case 'element_locations':
					GeneratePress_Sites_Helper::set_element_locations( $val, $this->slug );
				break;

				case 'element_exclusions':
					GeneratePress_Sites_Helper::set_element_exclusions( $val, $this->slug );
				break;

				case 'custom_logo':

					$data = GeneratePress_Sites_Helper::sideload_image( $val );

					if ( ! is_wp_error( $data ) && isset( $data->attachment_id ) ) {
						set_theme_mod( 'custom_logo', $data->attachment_id );
						update_post_meta( $data->attachment_id, '_wp_attachment_is_custom_header', get_option( 'stylesheet' ) );
					} else {
						remove_theme_mod( 'custom_logo' );
					}

				break;

				default:
					if ( in_array( $key, ( array ) generatepress_sites_disallowed_options() ) ) {
						GeneratePress_Sites_Helper::log( 'Disallowed option: ' . $key );
						continue;
					}

					delete_option( $key );
					update_option( $key, $val );
				break;

			}

		}

		// Clear page builder cache.
		GeneratePress_Sites_Helper::clear_page_builder_cache();

		// if ( class_exists( 'Code_Snippets' ) && function_exists( '_code_snippets_save_imported_snippets' ) ) {
		// 	if ( ! GeneratePress_Sites_Helper::file_exists( $this->directory . 'code-snippets.json' ) ) {
		// 		return;
		// 	}
		//
		// 	$data = GeneratePress_Sites_Helper::get_options( $this->directory . 'code-snippets.json' );
		//
		// 	$snippets = array();
		//
		// 	/* Reformat the data into snippet objects */
		// 	foreach ( $data['snippets'] as $snippet ) {
		// 		$snippet = new Code_Snippet( $snippet );
		// 		$snippet->active = true;
		// 		$snippets[] = $snippet;
		// 	}
		//
		// 	_code_snippets_save_imported_snippets( $snippets, null, 'replace' );
		// }

		wp_send_json( __( 'Site options imported', 'gp-premium' ) );

		die();

	}

	/**
	 * Activates our freshly installed plugins.
	 *
	 * @since 1.6
	 */
	public function activate_plugins() {

		check_ajax_referer( 'generate_sites_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = GeneratePress_Sites_Helper::get_options( $this->directory . 'options.json' );
		$plugins = $settings['plugins'];

		if ( ! empty( $plugins ) ) {

			$pro_plugins = GeneratePress_Sites_Helper::check_for_pro_plugins();

			foreach( $plugins as $plugin ) {
				// If the plugin has a pro version and it exists, activate it instead.
				if ( array_key_exists( $plugin, $pro_plugins ) ) {
					if ( file_exists( WP_PLUGIN_DIR . '/' . $pro_plugins[$plugin] ) ) {
						$plugin = $pro_plugins[$plugin];
					}
				}

				// Install BB lite if pro doesn't exist.
				if ( 'bb-plugin/fl-builder.php' === $plugin && ! file_exists( WP_PLUGIN_DIR . '/bb-plugin/fl-builder.php' ) ) {
					$plugin = 'beaver-builder-lite-version/fl-builder.php';
				}

				if ( ! is_plugin_active( $plugin ) ) {
					activate_plugin( $plugin, '', false, true );
				}
			}

			wp_send_json( __( 'Plugins activated', 'gp-premium' ) );

		}

		die();

	}

	/**
	 * Checks a few things:
	 * 1. Is the plugin installed already?
	 * 2. Is the plugin active already?
	 * 3. Can the plugin be downloaded from WordPress.org?
	 *
	 * @since 1.6
	 */
	public function check_plugins() {

		check_ajax_referer( 'generate_sites_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( GeneratePress_Sites_Helper::file_exists( $this->directory . 'options.json' ) ) {
			$data['options'] = true;

			$settings = GeneratePress_Sites_Helper::get_options( $this->directory . 'options.json' );
			$data['modules'] = $settings['modules'];
			$data['plugins'] = $settings['plugins'];

			if ( ! is_array( $data['plugins'] ) ) {
				return;
			}

			$plugin_data = array();
			foreach( $data['plugins'] as $name => $slug ) {
				$basename = strtok( $slug, '/' );
				$plugin_data[$name] = array(
					'name' => $name,
					'slug' => $slug,
					'installed' => GeneratePress_Sites_Helper::is_plugin_installed( $slug ) ? true : false,
					'active' => is_plugin_active( $slug ) ? true : false,
					'repo' => GeneratePress_Sites_Helper::file_exists( 'https://api.wordpress.org/plugins/info/1.0/' . $basename ) ? true : false,
				);
			}

			$data['plugin_data'] = $plugin_data;
		}

		wp_send_json( array(
			'plugins'		=> $data['plugins'],
			'plugin_data'	=> $data['plugin_data'],
		) );

		die();

	}

}
