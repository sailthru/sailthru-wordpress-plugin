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
		add_action( 'init', array( $this, 'plugin_textdomain' ) );

		// Register admin styles and scripts
		// Documentation says: admin_print_styles should not be used to enqueue styles or scripts on the admin pages. Use admin_enqueue_scripts instead.
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );

		// Register Horizon Javascripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_scripts' ) );

		// Register the menu
		add_action( 'admin_menu', array( $this, 'sailthru_menu') );

		// Register the Horizon meta tags
	    add_action( 'wp_head', array( $this, 'sailthru_horizon_meta_tags' ) );


	 	// Setup the meta box hooks
	 	add_action( 'add_meta_boxes', array( $this, 'sailthru_post_metabox' ) );
	 	add_action( 'save_post', array( $this, 'save_custom_meta_data' ) );


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
        load_textdomain( $domain, SAILTHRU_PLUGIN_PATH . $domain . '-' . $locale . '.mo' );
        load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

	} // end plugin_textdomain



	/**
	 * Registers and enqueues admin-specific JavaScript.
	 */
	public function register_admin_scripts( $hook ) {


		// datepicker for the meta box on post pages
		wp_enqueue_script('jquery-ui-datepicker', array('jquery'));
		wp_enqueue_style( 'jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');

		// our own magic
		wp_enqueue_script( 'sailthru-for-wordpress-admin-script', SAILTHRU_PLUGIN_URL . 'js/admin.js' , array('jquery') );


		// abandon if we're not on our own pages.
		if( !stristr( $hook, 'sailthru' ) &&
				!stristr( $hook, 'horizon' ) &&
					!stristr( $hook, 'scout' ) &&
						!stristr( $hook, 'concierge' ) &&
							!stristr( $hook, 'settings_configuration_page' ) ) {
			return;
		}

		// our own magic
		wp_enqueue_style( 'sailthru-for-wordpress-admin-styles', SAILTHRU_PLUGIN_URL . 'css/admin.css'  );

	} // end register_admin_scripts



	/**
	 * Registers and enqueues Horizon for every page, but only if setup has been completed.
	 */
	public function register_plugin_scripts() {

		// Check first, otherwise js could throw errors
		if( get_option('sailthru_setup_complete') ) {


			// we're not going to pass the enitre set of options
			// through to be seen in the html source so just stick it in this var
			$options = get_option('sailthru_setup_options');
				$horizon_domain = $options['sailthru_horizon_domain'];

			// and then grab only what we need and put it in this var
			$params = array();
			$params['sailthru_horizon_domain'] = $horizon_domain;

			add_action('wp_footer', array( $this, 'sailthru_client_horizon' ), 10);

			// A handy trick to update the parameters in js files
			wp_localize_script( 'sailthru-horizon-params', 'Horizon', $params );

			// Horizon paramters.
			wp_enqueue_script( 'sailthru-horizon-params' );


		} // end if sailthru setup is complete

	} // end register_plugin_scripts


	/*-------------------------------------------
	 * Create the Horizon Script for the <strike>page footer</strike>  page body.
	 *------------------------------------------*/
	 function sailthru_client_horizon() {

	 	// get the client's horizon domain
	 	$options = get_option('sailthru_setup_options');

	 	$concierge = get_option('sailthru_concierge_options');
	 		$concierge_from = isset($concierge['sailthru_concierge_from']) ? $concierge['sailthru_concierge_from'] : 'bottom';

	 		// threshold
	 		if( !isset($concierge['sailthru_concierge_threshold']) ) {
	 			$concierge_threshold = 'threshold: 500,';
	 		} else {
	 			$concierge_threshold =  strlen($concierge['sailthru_concierge_threshold']) ? "threshhold: ".intval( $concierge['sailthru_concierge_threshold'] ) .",": 'threshold: 500,';
	 		}

	 		// delay
	 		$concierge_delay = isset($concierge['sailthru_concierge_delay']) ? $concierge['sailthru_concierge_delay'] : '500';

	 		// offset
	 		$concierge_offset = isset($concierge['sailthru_concierge_offsetBottom']) ? $concierge['sailthru_concierge_offsetBottom'] : '20';

	 		// cssPath
	 		if( !isset($concierge['sailthru_concierge_cssPath']) ) {
	 			$concierge_css = 'https://ak.sail-horizon.com/horizon/recommendation.css';
	 		} else {
				$concierge_css = strlen($concierge['sailthru_concierge_cssPath']) > 0 ? $concierge['sailthru_concierge_cssPath']  :  'https://ak.sail-horizon.com/horizon/recommendation.css';
	 		}

	 		// filter
	 		if( !isset($concierge['sailthru_concierge_filter']) ) {
	 			$concierge_filter = '';
	 		} else {
	 			$concierge_filter = strlen($concierge['sailthru_concierge_filter']) >  0 ? "filter: '". esc_js( $concierge['sailthru_concierge_filter'] ) ."'" : '';
	 		}


	 	// check if concierge is on
	 	if ( isset($concierge['sailthru_concierge_is_on']) && $concierge['sailthru_concierge_is_on'] == 1 ) {
	 		$horizon_params = "domain: '".$options['sailthru_horizon_domain']."',concierge: {
	 			from: '". esc_js( $concierge_from ) ."',
	 			". esc_js( $concierge_threshold ) ."
	 			delay: ". esc_js( $concierge_delay ) .",
	 			offsetBottom: ". esc_js( $concierge_offset ) .",
	 			cssPath: '". esc_js( $concierge_css ) ."',
	 			$concierge_filter
	 		}";

		} else {
			$horizon_params =   "domain: '" . esc_js( $options['sailthru_horizon_domain'] ) . "'";
		}

	 	$horizon_js  = "<!-- Sailthru Horizon -->\n";
		$horizon_js .= "<script type=\"text/javascript\">\n";
		$horizon_js .= "(function() {\n";
		$horizon_js .= "     function loadHorizon() {\n";
		$horizon_js .= "           var s = document.createElement('script');\n";
		$horizon_js .= "           s.type = 'text/javascript';\n";
		$horizon_js .= "          s.async = true;\n";
		$horizon_js .= "          s.src = location.protocol + '//ak.sail-horizon.com/horizon/v1.js';\n";
		$horizon_js .= "         var x = document.getElementsByTagName('script')[0];\n";
		$horizon_js .= "         x.parentNode.insertBefore(s, x);\n";
		$horizon_js .= "      }\n";
		$horizon_js .= "     loadHorizon();\n";
		$horizon_js .= "      var oldOnLoad = window.onload;\n";
		$horizon_js .= "      window.onload = function() {\n";
		$horizon_js .= "          if (typeof oldOnLoad === 'function') {\n";
		$horizon_js .= "            oldOnLoad();\n";
		$horizon_js .= "         }\n";
		$horizon_js .= "           Sailthru.setup({\n";
		$horizon_js .= "              ". $horizon_params ."\n";
		$horizon_js .= "         });\n";
		$horizon_js .= "     };\n";
		$horizon_js .= "  })();\n";
		$horizon_js .= " </script>\n";

		echo $horizon_js;



	 } // end sailthru_client_horizon



	/*--------------------------------------------*
	 * Core Functions
	 *---------------------------------------------*/
	/**
	 * Add a top-level Sailthru menu and its submenus.
	 */
	function sailthru_menu() {

		$sailthru_menu = add_menu_page(
			'Sailthru',											// The value used to populate the browser's title bar when the menu page is active
			__( 'Sailthru', 'sailthru-for-wordpress' ),			// The text of the menu in the administrator's sidebar
			'manage_options',									// What roles are able to access the menu
			'sailthru_configuration_page',						// The ID used to bind submenu items to this menu
			array( &$this, 'load_sailthru_admin_display'),		// The callback function used to render this menu
			SAILTHRU_PLUGIN_URL . 'img/sailthru-menu-icon.png' 	// The icon to represent the menu item
		);
		$this->admin_views[$sailthru_menu] = 'sailthru_configuration_page';

			$redundant_menu = add_submenu_page(
				'sailthru_configuration_page',
				__( 'Welcome', 'sailthru-for-wordpress' ),
				__( 'Welcome', 'sailthru-for-wordpress' ),
				'manage_options',
				'sailthru_configuration_page',
				array( &$this, 'load_sailthru_admin_display')
			);
			$this->admin_views[$redundant_menu] = 'sailthru_configuration_page';


			$settings_menu = add_submenu_page(
				'sailthru_configuration_page',
				__( 'Settings', 'sailthru-for-wordpress' ),
				__( 'Settings', 'sailthru-for-wordpress' ),
				'manage_options',
				'settings_configuration_page',
				array( &$this, 'load_sailthru_admin_display')
			);
			$this->admin_views[$settings_menu] = 'settings_configuration_page';




			$concierge_menu = add_submenu_page(
				'sailthru_configuration_page',							// The ID of the top-level menu page to which this submenu item belongs
				__( 'Concierge Options', 'sailthru-for-wordpress' ),	// The value used to populate the browser's title bar when the menu page is active
				__( 'Concierge Options', 'sailthru-for-wordpress' ),	// The label of this submenu item displayed in the menu
				'manage_options',										// What roles are able to access this submenu item
				'concierge_configuration_page',							// The ID used to represent this submenu item
				array( &$this, 'load_sailthru_admin_display')			// The callback function used to render the options for this submenu item
			);
			$this->admin_views[$concierge_menu] = 'concierge_configuration_page';



			$scout_menu = add_submenu_page(
				'sailthru_configuration_page',
				__( 'Scout Options', 'sailthru-for-wordpress' ),
				__( 'Scout Options', 'sailthru-for-wordpress' ),
				'manage_options',
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

		// only do this on pages and posts
		if( ! is_single() ) {
			return;
		}



		global $post;

    	$post_object = get_post();

    	$horizon_tags = array();
    		$horizon_tags[] = "<!-- BEGIN Sailthru Horizon Meta Information -->";
    		$horizon_tags[] = "";

    		// date
    		$post_date = get_the_date( 'Y-m-d H:i:s' );
    			$horizon_tags[] = "<meta name='sailthru.date' content='" . esc_attr( $post_date ) . "' />";

    		// title
    		$post_title = get_the_title();
    			$horizon_tags[] = "<meta name='sailthru.title' content='" . esc_attr( $post_title ) . "' />";

    		// tags
    		$tags = get_the_tags();
			if ($tags) {
				$post_tags = '';

				foreach($tags as $tag) {
					$post_tags .= $tag->name . ', ';
				}
				$post_tags = rtrim( $post_tags, ", " );

				$horizon_tags[] = "<meta name='sailthru.tags' content='" . esc_attr( $post_tags ) . "' />";
			}

    		// author << works on display name. best option?
    		$post_author = get_the_author();
    			if( !empty($post_author) ) {
    				$horizon_tags[] = "<meta name='sailthru.author' content='" . esc_attr( $post_author ) . "' />";
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
    			$horizon_tags[] = "<meta name='sailthru.description' content='" . esc_attr( $post_description ) . "' />";

    		// image & thumbnail
			if(has_post_thumbnail( $post_object->ID ) ) {
				$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
				$thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'thumbnail' );

	    		$post_image = $image[0];
	    			$horizon_tags[] = "<meta name='sailthru.image.full' content='" . esc_attr( $post_image ) . "' /> ";
	    		$post_thumbnail = $thumb[0];
	    			$horizon_tags[] = "<meta name='sailthru.image.thumb' content='" . esc_attr( $post_thumbnail ) . "' />";
			}

			// expiration date
			$post_expiration = get_post_meta($post_object->ID, 'sailthru_post_expiration', true);

			if( !empty( $post_expiration ) ) {

				$horizon_tags[] = "<meta name='sailthru.expire_date' content='" . esc_attr( $post_expiration ) . "' />";
			}


    		$horizon_tags[] = "";
    		$horizon_tags[] = "";
    		$horizon_tags[] = "<!-- END Sailthru Horizon Meta Information -->";

    	echo implode("\n", $horizon_tags);

	} // sailthru_horizon_meta_tags


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


	/*--------------------------------------------*
	 * Hooks
	 *--------------------------------------------*/

	/**
	 * Introduces the meta box for expiring content.
	 */
	public function sailthru_post_metabox() {

		add_meta_box(
			'sailthru-expiration-date',
			__( 'Sailthru Expiration Date', 'sailthru' ),
			array( $this, 'post_metabox_display' ),
			'post',
			'side',
			'high'
		);

	} // sailthru_post_metabox

	/**
	 * Adds the input box for the post meta data.
	 *
	 * @param		object	$post	The post to which this information is going to be saved.
	 */
	public function post_metabox_display( $post ) {


		$sailthru_post_expiration = get_post_meta( $post->ID, 'sailthru_post_expiration', true);

		wp_nonce_field( plugin_basename( __FILE__ ), $this->nonce );
		$html  = '<input id="sailthru_post_expiration" type="text" name="sailthru_post_expiration" value="' . esc_attr($sailthru_post_expiration) . '" size="25" class="datepicker" />';

		$html .= '<p class="description">';
		$html .= '<br>Flash sales, events and some news stories should not be recommended after a certain date and time. Use this Sailthru-specific meta tag to prevent Horizon from suggesting the content at the given point in time. <a href="http://docs.sailthru.com/documentation/products/horizon-data-collection/horizon-meta-tags" target="_blank">More information can be found here</a>.';
		$html .= '</p><!-- /.description -->';

		echo $html;

	} // end post_media

	/**
	 * Determines whether or not the current user has the ability to save meta data associated with this post.
	 *
	 * @param		int		$post_id	The ID of the post being save
	 * @param		bool				Whether or not the user has the ability to save this post.
	 */
	public function save_custom_meta_data( $post_id ) {

		// First, make sure the user can save the post
		if( $this->user_can_save( $post_id, $this->nonce ) ) {

			// Did the user set an expiry date?
			if( ! empty( $_POST['sailthru_post_expiration'] ) && isset( $_POST['sailthru_post_expiration'] ) ) {
				$expiry_time = strotime( $_POST['sailthru_post_expiration'] );
				if ( $expiry_time ) {
					$expiry_date = date( 'Y-m-d', $expiry_time );
				
					// Save the date. hehe.
					update_post_meta( $post_id, 'sailthru_post_expiration', $expiry_date );
				}

			} // end if

		} // end if

	} // end save_custom_meta_data

	/*--------------------------------------------*
	 * Helper Functions
	 *--------------------------------------------*/

	/**
	 * FROM: https://github.com/tommcfarlin/WordPress-Upload-Meta-Box
	 * Determines whether or not the current user has the ability to save meta data associated with this post.
	 *
	 * @param		int		$post_id	The ID of the post being save
	 * @param		bool				Whether or not the user has the ability to save this post.
	 */
	function user_can_save( $post_id, $nonce ) {

	    $is_autosave = wp_is_post_autosave( $post_id );
	    $is_revision = wp_is_post_revision( $post_id );
	    $is_valid_nonce = ( isset( $_POST[ $nonce ] ) && wp_verify_nonce( $_POST[ $nonce ], plugin_basename( __FILE__ ) ) );

	    // Return true if the user is able to save; otherwise, false.
	    return ! ( $is_autosave || $is_revision ) && $is_valid_nonce;

	} // end user_can_save


} // end class
