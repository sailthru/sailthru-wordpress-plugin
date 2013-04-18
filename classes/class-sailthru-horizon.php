<?php


class Sailthru_Horizon {

	protected $admin_views = array();

	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/

	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'plugin_textdomain' ) );

		// Register admin styles and scripts
		add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );

		// Register Horizon Javascripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_scripts' ) );

		// Register the menu
		add_action( 'admin_menu', array( $this, 'sailthru_menu') );

		// Register the Horizon meta tags
	    add_action( 'wp_head', array( $this, 'sailthru_horizon_meta_tags' ) );



		// Initialize the metabox class.
		add_action( 'init', array( $this, 'cmb_initialize_cmb_meta_boxes' ), 9999 );

		// Register the Horizon metabox for posts
		add_filter( 'cmb_meta_boxes', array( $this, 'sailthru_post_metabox' )  );


	} // end constructor

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin
	 * 			uses "Network Activate" action, false if WPMU is
	 * 			disabled or plugin is activated on an individual blog
	 */
	public function activate( $network_wide ) {

		if ( ! current_user_can( 'activate_plugins' ) )
            return;

		// signal that it's ok to override Wordpress's built-in email functions
		if( false == get_option( 'sailthru_override_wp_mail' ) ) {
			add_option( 'sailthru_override_wp_mail', 1 );
		} // end if


	} // end activate

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin
	 * 			uses "Network Activate" action, false if WPMU is
	 * 			disabled or plugin is activated on an individual blog
	 */
	public function deactivate( $network_wide ) {

		if ( ! current_user_can( 'activate_plugins' ) )
            return;


		// stop overriding Wordpress's built in email functions
		if( false != get_option( 'sailthru_override_wp_mail' ) ) {
			delete_option( 'sailthru_override_wp_mail' );
		}

		// we don't know if the API keys, etc, will still be
		// good, so kill the flag that said we knew.
		if( false != get_option( 'sailthru_setup_complete') ) {
			delete_option( 'sailthru_setup_complete' );
		}

		// remove all setup information including API key info
		if( false != get_option( 'sailthru_setup_options') ) {
			delete_option( 'sailthru_setup_options' );
		}

		// remove concierge settings
		if( false != get_option( 'sailthru_concierge_options') ) {
			delete_option( 'sailthru_concierge_options' );
		}

		// remove scout options
		if( false != get_option( 'sailthru_scout_options') ) {
			delete_option( 'sailthru_scout_options' );
		}

		// remove data feeds
		if( false != get_option( 'sailthru_datafeeds') ) {
			delete_option( 'sailthru_datafeeds' );
		}

		// remove email blast settings
		if( false != get_option( 'sailthru_blast_options') ) {
			delete_option( 'sailthru_blast_options' );
		}


		// try to deactivate the Sailthru Subscribe Widget
		$dependent = 'sailthru-for-wordpress/widget.php';
	    if( is_plugin_active($dependent) ){
	         add_action('update_option_active_plugins', array( 'Sailthru_Horizon', 'sailthru_deactivate_dependent_widget') );
	    }

	} // end deactivate

	/**
	 * Fired when the plugin is uninstalled.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin
	 * 			uses "Network Activate" action, false if WPMU is
	 * 			disabled or plugin is activated on an individual blog
	 */
	public function uninstall( $network_wide ) {
		// nothing to see here.
	} // end uninstall

	/**
	 * Loads the plugin text domain for translation
	 */
	public function plugin_textdomain() {

		$domain = 'sailthru-for-wordpress-locale';
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
        load_textdomain( $domain, WP_LANG_DIR.'/'.$domain.'/'.$domain.'-'.$locale.'.mo' );
        load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

	} // end plugin_textdomain

	/**
	 * Registers and enqueues admin-specific styles.
	 */
	public function register_admin_styles() {

		// jquery ui
		wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');

		// our own magic
		wp_enqueue_style( 'sailthru-for-wordpress-admin-styles', SAILTHRU_PLUGIN_URL . '/css/admin.css'  );

	} // end register_admin_styles

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 */
	public function register_admin_scripts() {

		// jQuery UI
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-widget' );
		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-dialog' );


		// date/time picker add on for the email blast screen
		wp_enqueue_script( 'jquery-date-time-picker-addon', SAILTHRU_PLUGIN_URL . '/js/jquery-ui-timepicker-addon.js' , array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-slider') );
		wp_enqueue_script( 'jquery-date-time-picker-slider', SAILTHRU_PLUGIN_URL . '/js/jquery-ui-sliderAccess.js' , array('jquery-date-time-picker-addon') );

		// our own magic
		wp_enqueue_script( 'sailthru-for-wordpress-admin-script', SAILTHRU_PLUGIN_URL . '/js/admin.js' , array('jquery') );



		// get some user specific info to pass to the timepicker
		$sailthru = get_option('sailthru_setup_options');

		// set up is not complete yet
		if( is_array($sailthru) ) {

			$api_key = $sailthru['sailthru_api_key'];
			$api_secret = $sailthru['sailthru_api_secret'];

			$client = new Sailthru_Client( $api_key, $api_secret );
			$res = $client->apiPost('settings', array() );

			// if the Sailthru client hasn't been initialized return
			if (isset($res['error'] ) ) {
				return;
			}

			$timezone = new DateTime( $res['timezone'] );
			$timezoneOffset = ( $timezone->getOffset() ) / 60 / 60 * 100;


			$params = array('timezone' => $timezoneOffset);


			// A handy trick to update the parameters in js files
			wp_localize_script( 'sailthru-for-wordpress-admin-script', 'UserSettingsAPI', $params );

		}	// if $sailthru is not an array

	} // end register_admin_scripts



	/**
	 * Registers and enqueues Horizon for every page, but only if setup has been completed.
	 */
	public function register_plugin_scripts() {

		// Check first, otherwise js could throw errors
		if( get_option('sailthru_setup_complete') ) {


			wp_register_script( 'sailthru-horizon-params', SAILTHRU_PLUGIN_URL . '/js/horizon.params.js' , array('jquery', 'sailthru-horizon'), '', true );

			// Horizon itself
			wp_enqueue_script( 'sailthru-horizon', '//ak.sail-horizon.com/horizon/v1.js', array('jquery') );


			// we're not going to pass the enitre set of options
			// through to be seen in the html source so just stick it in this var
			$options = get_option('sailthru_setup_options');
				$horizon_domain = $options['sailthru_horizon_domain'];

			// and then grab only what we need and put it in this var
			$params = array();
			$params['sailthru_horizon_domain'] = $horizon_domain;

			// A handy trick to update the parameters in js files
			wp_localize_script( 'sailthru-horizon-params', 'Horizon', $params );


			// Horizon paramters.
			wp_enqueue_script( 'sailthru-horizon-params' );



		} // end if sailthru setup is complete

	} // end register_plugin_scripts


	/**
	 * Deactivate the Sailthru Subscribe Widget when the Horizon
	 * Plugin is deactivated.
	 *
	 * TODO: abstract $dependent into an argument
	 */
	function sailthru_deactivate_dependent_widget() {

		$dependent = 'sailthru-for-wordpress/widget.php';
		deactivate_plugins($dependent);

	}


	/*--------------------------------------------*
	 * Core Functions
	 *---------------------------------------------*/
	/**
	 * Add a top-level Sailthru menu and its submenus.
	 */
	function sailthru_menu() {

		$sailthru_menu = add_menu_page(
			'Sailthru',											// The value used to populate the browser's title bar when the menu page is active
			__( 'Sailthru', 'sailthru-for-wordpress' ),	// The text of the menu in the administrator's sidebar
			'administrator',									// What roles are able to access the menu
			'sailthru_configuration_page',						// The ID used to bind submenu items to this menu
			array( &$this, 'load_sailthru_admin_display'),		// The callback function used to render this menu
			SAILTHRU_PLUGIN_URL . '/img/sailthru-menu-icon.png' 	// The icon to represent the menu item
		);
		$this->admin_views[$sailthru_menu] = 'sailthru_configuration_page';

			$redundant_menu = add_submenu_page(
				'sailthru_configuration_page',
				__( 'Welcome', 'sailthru-for-wordpress' ),
				__( 'Welcome', 'sailthru-for-wordpress' ),
				'administrator',
				'sailthru_configuration_page',
				array( &$this, 'load_sailthru_admin_display')
			);
			$this->admin_views[$redundant_menu] = 'sailthru_configuration_page';


			$settings_menu = add_submenu_page(
				'sailthru_configuration_page',
				__( 'Settings', 'sailthru-for-wordpress' ),
				__( 'Settings', 'sailthru-for-wordpress' ),
				'administrator',
				'settings_configuration_page',
				array( &$this, 'load_sailthru_admin_display')
			);
			$this->admin_views[$settings_menu] = 'settings_configuration_page';




			$concierge_menu = add_submenu_page(
				'sailthru_configuration_page',							// The ID of the top-level menu page to which this submenu item belongs
				__( 'Concierge Options', 'sailthru-for-wordpress' ),	// The value used to populate the browser's title bar when the menu page is active
				__( 'Concierge Options', 'sailthru-for-wordpress' ),	// The label of this submenu item displayed in the menu
				'administrator',										// What roles are able to access this submenu item
				'concierge_configuration_page',							// The ID used to represent this submenu item
				array( &$this, 'load_sailthru_admin_display')			// The callback function used to render the options for this submenu item
			);
			$this->admin_views[$concierge_menu] = 'concierge_configuration_page';



			$scout_menu = add_submenu_page(
				'sailthru_configuration_page',
				__( 'Scout Options', 'sailthru-for-wordpress' ),
				__( 'Scout Options', 'sailthru-for-wordpress' ),
				'administrator',
				'scout_configuration_page',
				array( &$this, 'load_sailthru_admin_display')
			);
			$this->admin_views[$scout_menu] = 'scout_configuration_page';


	} // end sailthru_menu

	/**
	 * Renders a simple page to display for the theme menu defined above.
	 */
	function load_sailthru_admin_display( ) {

		$active_tab = empty($this->views[current_filter()]) ? '' : $this->views[current_filter()] ;
		// display html
		include( SAILTHRU_PLUGIN_PATH . 'views/admin.php' );

	} // end sailthru_admin_display

	/**
 	 * Renders Horizon specific meta tags in the <head></head>
	 */
	function sailthru_horizon_meta_tags() {

    	$post_object = get_post();

    	$horizon_tags = array();
    		$horizon_tags[] = "<!-- BEGIN Sailthru Horizon Meta Information -->";
    		$horizon_tags[] = "";

    		// date
    		$post_date = get_the_date( 'Y-m-d H:i:s' );
    			$horizon_tags[] = "<meta name='sailthru.date' content='" . $post_date . "' />";

    		// title
    		$post_title = get_the_title();
    			$horizon_tags[] = "<meta name='sailthru.title' content='" . $post_title . "' />";

    		// tags
    		$tags = get_the_tags();
			if ($tags) {
				$post_tags = '';

				foreach($tags as $tag) {
					$post_tags .= $tag->name . ', ';
				}
				$post_tags = rtrim( $post_tags, ", " );

				$horizon_tags[] = "<meta name='sailthru.tags' content='" . $post_tags . "' />";
			}

    		// author << works on display name. best option?
    		$post_author = get_the_author();
    			if( !empty($post_author) ) {
    				$horizon_tags[] = "<meta name='sailthru.author' content='" . $post_author . "' />";
    			}

    		// description
    		$post_description = get_the_excerpt();
    		if( empty($post_description) ) {
				$excerpt_length = 250;
				// get the entire post and then strip it down to just sentences.
				$text = $post_object->post_content;
				$text = apply_filters( 'the_content', $text );
				$text = str_replace( ']]>', ']]>', $text );
				$text = strip_shortcodes( $text );
				$text = strip_tags( $text );
				$text = substr( $text, 0, $excerpt_length );
				$post_description = $this->reverse_strrchr( $text, '.', 1 );
    		}
    			$horizon_tags[] = "<meta name='sailthru.description' content='" . $post_description . "' />";

    		// image & thumbnail
			if(has_post_thumbnail( $post_object->ID ) ) {
				$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
				$thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'thumbnail' );

	    		$post_image = $image[0];
	    			$horizon_tags[] = "<meta name='sailthru.image.full' content='" . $post_image . "' /> ";
	    		$post_thumbnail = $thumb[0];
	    			$horizon_tags[] = "<meta name='sailthru.image.thumb' content='" . $post_thumbnail . "' />";
			}

			// expiration date
			$post_expiration = get_post_meta($post_object->ID, '_wpb_sailthru_expiry_date_metabox', true);

			if( !empty( $post_expiration ) ) {

				$post_expiration = date( 'Y-m-d', strtotime( $post_expiration) );

				$horizon_tags[] = "<meta name='sailthru.expire_date' content='" . $post_expiration . "' />";
			}


    		$horizon_tags[] = "";
    		$horizon_tags[] = "";
    		$horizon_tags[] = "<!-- END Sailthru Horizon Meta Information -->";

    	echo implode("\n", $horizon_tags);

	} // sailthru_horizon_meta_tags


	/*-------------------------------------------
	 * Filter Methods
	 *------------------------------------------*/

	/**
	 * Add a meta box to posts.
	 */
	function sailthru_post_metabox() {

		$prefix = '_wpb_'; // Prefix for all fields

		$meta_boxes[] = array(
			'id' => 'sailthru_expiry_date_metabox',
			'title' => 'Sailthru Expiration Date',
			'pages' => array('post'), // post type
			'context' => 'side',
			'priority' => 'high',
			'show_names' => false, // Show field names on the left
			'fields' => array(
				array(
					'name' => 'Sailthru Expiration Date',
					'desc' => '<br />Flash sales, events and some news stories should not be recommended after a certain date and time. Use this Sailthru-specific meta tag to prevent Horizon from suggesting the content at the given point in time. <a href="http://docs.sailthru.com/documentation/products/horizon-data-collection/horizon-meta-tags" target="_blank">More information can be found here</a>.',
					'id' => $prefix . 'sailthru_expiry_date_metabox',
					'type' => 'text_date'
				),
			),
		);

		return $meta_boxes;

	}


	/*-------------------------------------------
	 * Utility Functions
	 *------------------------------------------*/

	/*
	 * Returns the portion of haystack which goes until the last occurrence of needle
	 * Credit: http://www.wprecipes.com/wordpress-improved-the_excerpt-function
	 */
	function reverse_strrchr($haystack, $needle, $trail) {
	    return strrpos($haystack, $needle) ? substr($haystack, 0, strrpos($haystack, $needle) + $trail) : false;
	}



	/*-------------------------------------------
	 * Custom Metaboxes and Fields for WordPress
	 *------------------------------------------*/
	function cmb_initialize_cmb_meta_boxes() {

	  if ( ! class_exists( 'cmb_Meta_Box' ) )
	    require_once ( SAILTHRU_PLUGIN_PATH . 'lib/metabox/init.php');

	}


} // end class
