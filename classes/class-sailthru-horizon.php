<?php

class Sailthru_Horizon {

	protected $admin_views = array();

	// Represents the nonce value used to save the post media
	private $nonce = 'wp_sailthru_nonce';


	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/

	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'sailthru_init' ) );

		// Register admin styles and scripts
		// Documentation says: admin_print_styles should not be used to enqueue styles or scripts on the admin pages. Use admin_enqueue_scripts instead.
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );

		// Register Horizon Javascripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_scripts' ) );

		// Register the menu
		add_action( 'admin_menu', array( $this, 'sailthru_menu' ) );

		// Check for updates and make changes as needed
		add_action( 'plugins_loaded', array( $this, 'sailthru_update_check' ) );

	} // end constructor

	/**
	 * Fired after plugins are loaded.
	 * Checks versions to see if changes
	 * need to be made to the database
	 */
	public static function sailthru_update_check() {

		$sailthru_plugin_version = get_option( 'sailthru_plugin_version' );

		// changes to <3.0.5
		// delete custom subscribe widget fields from the database,
		// don't just hide them.
		if ( $sailthru_plugin_version <= '3.0.5' || $sailthru_plugin_version === false ) {

			$customfields = get_option( 'sailthru_forms_options' );
			$key          = get_option( 'sailthru_forms_key' );

			$updatedcustomfields = array();

			if ( isset( $customfields ) && ! empty( $customfields ) ) {

				for ( $i = 0; $i <= $key; $i++ ) {
					if ( isset( $customfields[ $i ]['sailthru_customfield_name'] )
						and ! empty( $customfields[ $i ]['sailthru_customfield_name'] ) ) {

						// save non-empty custom fields to the database
						$updatedcustomfields[ $i ] = $customfields[ $i ];

					} else {

						// don't save empty custom fields to the database
						// we're pruning them.
					}
				}

				update_option( 'sailthru_forms_options', $updatedcustomfields );

			} // end if $customfields isset
		}

	}




	/**
	 * Loads the plugin text domain for translation
	 */
	public function sailthru_init() {

		$domain = 'sailthru-for-wordpress-locale';
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
		load_textdomain( $domain, SAILTHRU_PLUGIN_PATH . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

		// Add a thumbnail size for concierge
		add_theme_support( 'post-thumbnails' );
		add_image_size( 'concierge-thumb', 50, 50 );

	} // end plugin_textdomain



	/**
	 * Registers and enqueues admin-specific JavaScript.
	 */
	public function register_admin_scripts( $hook ) {

		/* loads the admin js and css for editing/creating posts to make sure this
			is only loaded in context rather than everywhere in the admin screens
		*/

		$screens = array( 'post-new.php', 'post.php', 'edit.php', 'admin.php' );

		if ( in_array( $hook, $screens, true ) ) {

			if ( isset( $_GET['action'] ) ) {
				if ( $_GET['action'] === 'edit' ) {
					// datepicker for the meta box on post pages
					wp_enqueue_style( 'jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
					// our own magic
					wp_enqueue_script( 'sailthru-for-wordpress-admin-script', SAILTHRU_PLUGIN_URL . 'js/admin.js', array( 'jquery' ) );
				}
			}
		}
		// wp_enqueue_script( 'sailthru-subscribe-widget-admin-jquery-script', SAILTHRU_PLUGIN_URL . 'js/widget.subscribe.admin.js', array( 'jquery' ) );
		wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery' ) );

		// abandon if we're not on our own pages.
		if ( ! stristr( $hook, 'sailthru' ) &&
			! stristr( $hook, 'horizon' ) &&
			! stristr( $hook, 'scout' ) &&
			! stristr( $hook, 'concierge' ) &&
			! stristr( $hook, 'custom_fields_configuration_page' ) &&
			! stristr( $hook, 'settings_configuration_page' ) ) {
			return;
		}

		// loads the admin js and css for the configuration pages
		wp_enqueue_script( 'sailthru-for-wordpress-admin-script', SAILTHRU_PLUGIN_URL . 'js/admin.js', array( 'jquery' ) );
		wp_enqueue_style( 'sailthru-for-wordpress-admin-styles', SAILTHRU_PLUGIN_URL . 'css/admin.css' );

	} // end register_admin_scripts



	/**
	 * Registers and enqueues Horizon for every page, but only if setup has been completed.
	 */
	public function register_plugin_scripts() {

		// Check first, otherwise js could throw errors.
		if ( get_option( 'sailthru_setup_complete' ) ) {

			// we're not going to pass the enitre set of options
			// through to be seen in the html source so just stick it in this var
			$options = get_option( 'sailthru_setup_options' );

			if ( isset( $options['sailthru_js_type'] ) && 'none' === $options['sailthru_js_type'] || empty( $options['sailthru_js_type'] ) ) {
				return;
			}

			// check what JS version is being used.

			if ( isset( $options['sailthru_js_type'] ) && 'personalize_js' === $options['sailthru_js_type'] || 'personalize_js_custom' === $options['sailthru_js_type'] ) {


				$customer_id = $options['sailthru_customer_id'];

				if ( $customer_id ) {

					// have to wrap the options in an inner array to preserve booleans.
					$params = array(
						'options' => array(
							'customerId' => $options['sailthru_customer_id'],
						),
					);


					if ( 'personalize_js_custom' === $options['sailthru_js_type'] ) {

						$params['isCustom'] = true;

						// override defaults
						if ( 'false' === $options['sailthru_js_auto_track_pageview'] ) {
							$params['options']['autoTrackPageview'] = (bool) false;
						}

						if ( 'false' === $options['sailthru_ignore_personalize_stored_tags'] ) {
							$params['options']['useStoredTags'] = (bool) false;
						}

						if ( 'true' === $options['sailthru_js_exclude_content'] ) {
							$params['options']['excludeContent'] = (bool) true;
						}
					}

					if ( ! is_404() && ! is_preview() && apply_filters( 'sailthru_add_spm_js', true ) ) {
						wp_enqueue_script( 'personalize_js', '//ak.sail-horizon.com/spm/spm.v1.min.js' );
						wp_register_script( 'tag', plugin_dir_url( __DIR__ ) . 'js/tag.js', array('jquery') );
						wp_localize_script( 'tag', 'tag', $params );
						wp_enqueue_script( 'tag' , '', [], false, $this->js_in_footer());
					}
				}
			} else {
				$params = array(
					'options' => array(
						'horizon_domain' => $options['sailthru_horizon_domain'],
					),
				);

				$concierge = get_option( 'sailthru_concierge_options' );

				if ( $concierge['sailthru_concierge_is_on'] ) {
					$params['concierge']['enabled']      = (bool) true;
					$params['concierge']['from']         = isset( $concierge['sailthru_concierge_from'] ) ? $concierge['sailthru_concierge_from'] : 'bottom';
					$params['concierge']['delay']        = isset( $concierge['sailthru_concierge_delay'] ) ? $concierge['sailthru_concierge_delay'] : '500';
					$params['concierge']['offsetBottom'] = isset( $concierge['sailthru_concierge_offsetBottom'] ) ? $concierge['sailthru_concierge_offsetBottom'] : '20';

					// threshold.
					if ( ! isset( $concierge['sailthru_concierge_threshold'] ) ) {
						$params['concierge']['threshold'] = 'threshold: 500,';
					} else {
						$params['concierge']['threshold'] = strlen( $concierge['sailthru_concierge_threshold'] ) ? 'threshhold: ' . intval( $concierge['sailthru_concierge_threshold'] ) . ',' : 'threshold: 500,';
					}

					if ( isset( $concierge['sailthru_concierge_filter'] ) ) {
						//remove whitespace around the commas
						$tags_filtered                 = preg_replace( '/\s*([\,])\s*/', '$1', $concierge['sailthru_concierge_filter'] );
						$params['concierge']['filter'] = strlen( $concierge['sailthru_concierge_filter'] ) > 0 ? "{tags: '" . esc_js( $tags_filtered ) . "'}" : '';
					}

					// cssPath.
					if ( ! isset( $concierge['sailthru_concierge_cssPath'] ) ) {
						$params['concierge']['cssPath'] = 'https://ak.sail-horizon.com/horizon/recommendation.css';
					} else {
						$params['concierge']['cssPath'] = strlen( $concierge['sailthru_concierge_cssPath'] ) > 0 ? $concierge['sailthru_concierge_cssPath'] : 'https://ak.sail-horizon.com/horizon/recommendation.css';
					}
				}

				if ( ! is_404() && ! is_preview() && apply_filters( 'sailthru_add_horizon_js', true ) ) {
					wp_enqueue_script( 'horizon_js', '//ak.sail-horizon.com/horizon/v1.js' );
					wp_register_script( 'tag', plugin_dir_url( __DIR__ ) . 'js/horizon.js' );
					wp_localize_script( 'tag', 'tag', $params );
					wp_enqueue_script( 'tag' , '', [], false, $this->js_in_footer());
				}
				// Horizon parameters.
			}
		} // end if sailthru setup is complete

	} // end register_plugin_scripts


	function js_in_footer() {
		return true === apply_filters( 'sailthru_scripts_in_footer', false );
	}


	/*--------------------------------------------*
	 * Core Functions
	 *---------------------------------------------*/
	/**
	 * Add a top-level Sailthru menu and its submenus.
	 */
	function sailthru_menu() {

		$options = get_option( 'sailthru_setup_options' );

		$sailthru_menu                       = add_menu_page(
			'Sailthru',           // The value used to populate the browser's title bar when the menu page is active
			__( 'Sailthru', 'sailthru-for-wordpress' ),   // The text of the menu in the administrator's sidebar
			'manage_options',         // What roles are able to access the menu
			'sailthru_configuration_page',      // The ID used to bind submenu items to this menu
			array( $this, 'load_sailthru_admin_display' ),  // The callback function used to render this menu
			SAILTHRU_PLUGIN_URL . 'img/sailthru-menu-icon.png'  // The icon to represent the menu item
		);
		$this->admin_views[ $sailthru_menu ] = 'sailthru_configuration_page';

		$redundant_menu                       = add_submenu_page(
			'sailthru_configuration_page',
			__( 'Settings', 'sailthru-for-wordpress' ),
			__( 'Settings', 'sailthru-for-wordpress' ),
			'manage_options',
			'sailthru_configuration_page',
			array( $this, 'load_sailthru_admin_display' )
		);
		$this->admin_views[ $redundant_menu ] = 'sailthru_configuration_page';

		if ( sailthru_verify_setup() ) {

			if ( isset( $options['sailthru_js_type'] ) && 'horizon_js' === $options['sailthru_js_type'] ) {

				$concierge_menu                       = add_submenu_page(
					'sailthru_configuration_page',       // The ID of the top-level menu page to which this submenu item belongs
					__( 'Concierge Options', 'sailthru-for-wordpress' ), // The value used to populate the browser's title bar when the menu page is active
					__( 'Concierge Options', 'sailthru-for-wordpress' ), // The label of this submenu item displayed in the menu
					'manage_options',          // What roles are able to access this submenu item
					'concierge_configuration_page',       // The ID used to represent this submenu item
					array( $this, 'load_sailthru_admin_display' )   // The callback function used to render the options for this submenu item
				);
				$this->admin_views[ $concierge_menu ] = 'concierge_configuration_page';

				$scout_menu                       = add_submenu_page(
					'sailthru_configuration_page',
					__( 'Scout Options', 'sailthru-for-wordpress' ),
					__( 'Scout Options', 'sailthru-for-wordpress' ),
					'manage_options',
					'scout_configuration_page',
					array( $this, 'load_sailthru_admin_display' )
				);
				$this->admin_views[ $scout_menu ] = 'scout_configuration_page';

			}

			$scout_menu                       = add_submenu_page(
				'sailthru_configuration_page',
				__( 'List Signup Options', 'sailthru-for-wordpress' ),
				__( 'List Signup Options', 'sailthru-for-wordpress' ),
				'manage_options',
				'custom_fields_configuration_page',
				array( $this, 'load_sailthru_admin_display' )
			);
			$this->admin_views[ $scout_menu ] = 'customforms_configuration_page';

			$forms_menu                       = add_submenu_page(
				'customforms_configuration_page',
				__( 'Custom Forms', 'sailthru-for-wordpress' ),
				__( 'Custom Forms', 'sailthru-for-wordpress' ),
				'manage_options',
				'customforms_configuration_page',
				array( $this, 'load_sailthru_admin_display' )
			);
			$this->admin_views[ $forms_menu ] = 'customforms_configuration_page';
		}
	} // end sailthru_menu

	/**
	 * Renders a simple page to display for the theme menu defined above.
	 */
	function load_sailthru_admin_display() {

		$active_tab = empty( $this->views[ current_filter() ] ) ? '' : $this->views[ current_filter() ];
		// display html
		include SAILTHRU_PLUGIN_PATH . 'views/admin.php';

	} // end sailthru_admin_display


} // end class
