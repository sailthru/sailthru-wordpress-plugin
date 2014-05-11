<?php
/*
 *
 *	Gigya depends entirely on Horizon
 *
 */

class Sailthru_Twitter_Lead_Cards {

	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/
	function __construct() {

		// hook up the endpoint
		add_action('init', array($this, 'add_endpoint'), 0);

		// do something if our endpoint matches
		add_action('parse_request', array($this, 'sniff_requests'), 0);
		
		// load plugin text domain
		add_action( 'init', array( $this, 'load_widget_text_domain' ) );

	} // end constructor


	/*--------------------------------------------------*/
	/* Public Functions
	/*--------------------------------------------------*/

	/**
	 * Loads the Widget's text domain for localization and translation.
	 */
	public function load_widget_text_domain() {

		load_plugin_textdomain( 'sailthru-for-wordpress', false, plugin_dir_path( SAILTHRU_PLUGIN_PATH ) . '/lang/' );

	} // end load_widget_text_domain

	/**
	 * Set up the end point according to user preferences (settings page)
	 * This only matters if Twitter Lead Cards are turned on.
	 */
	public function add_endpoint(){


		$option = get_option( 'sailthru_integrations_options' );
		// twitter leads cards are enabled. check for endpoint
		if ( ! empty( $option['sailthru_twitter_url'] ) ) {

			// create the endpoint using the user-specified url
			add_rewrite_endpoint( $option['sailthru_twitter_url'], EP_ROOT);
			//add_rewrite_rule( $option['sailthru_twitter_url'],'index.php?__api=1&twitter=1','top');
		
		}
	
	}	

	/**	
	* If $_GET['__api'] is set, we kill WP and capture Twitter Lead Cards
	* @return die if API request
	*/
	public function sniff_requests(){

		global $wp;

		$option = get_option( 'sailthru_integrations_options' );
		$endpoint = $option['sailthru_twitter_url'];


		if( stristr( $endpoint, $wp->request ) ){
			$this->handle_request();
			exit;
		}
	
	}

	/*--------------------------------------------------*/
	/* Protected Functions
	/*--------------------------------------------------*/
	/** 
	*	This is where we send off for an intense pug bomb package
	*	@return void 
	*/
	protected function handle_request(){

		echo 'End point found';

	}


	/*-------------------------------------------
	 * Utility Functions
	 *------------------------------------------*/



} // end class

