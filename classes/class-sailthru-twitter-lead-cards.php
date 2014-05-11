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

		$option = get_option( 'sailthru_integrations_options' );	
			if(! $option['sailthru_twitter_enabled'] )	
				return;

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

		
		}
	
	}	

	/**	
	* If an endpoint is set by the user, we kill WP and capture Twitter Lead Cards
	* @return die if API request
	*/
	public function sniff_requests() {

		global $wp;

		$option = get_option( 'sailthru_integrations_options' );
			$endpoint = $option['sailthru_twitter_url'];
		$request = $wp->request;
		
		if( !empty( $wp->request ) ) {

			if( stristr( $endpoint, $request ) ){
				$this->handle_request();
				exit;
			}

		}
	
	}

	/*--------------------------------------------------*/
	/* Protected Functions
	/*--------------------------------------------------*/
	/** 
	*	This is where we send off the lead card data to Sailthru.
	*	@return void 
	*/
	protected function handle_request(){

		$option = get_option( 'sailthru_integrations_options' );
		$salt = $option['sailthru_twitter_salt'];

		if (isset($_POST['token'])) {

		   $email = isset($_POST['email']) ? $_POST['email'] : '';
		   $name = isset($_POST['name']) ? $_POST['name'] : '';
		   $screen_name = isset($_POST['Screen_name']) ? $_POST['Screen_name'] : '';
		   $token = MD5(''.$email.','.$name.','.$screen_name.','.$salt.'');


		   if ($_POST['token'] == $token) {

		    try {
				$sailthru = get_option('sailthru_setup_options');
					$api_key = $sailthru['sailthru_api_key'];
					$api_secret = $sailthru['sailthru_api_secret'];
				$client = new WP_Sailthru_Client( $api_key, $api_secret);
		        $vars = array();
		        foreach ($_POST as $key => $val) {
		          // we don't need to store the token, throw it away
		          if ($key != 'token') {
		            $vars ['Twitter_'.$key] = $val;
		          }
		        }

		        // The Client Account MUST have Twitter keys enabled
		        if ($enabled) {
		            $result = $client->apiPost('user', array(
		                           'id' => $_POST['email'],
		                           'keys' => array('twitter' => $_POST['Screen_name']),
		                           'vars' => $vars,
		                           'lists'  => array('Twitter Lead Cards' => 1),
		                            ));
		        } else {
		          $result = $client->apiPost('user', array(
		                         'id' => $_POST['email'],
		                         'vars' => $vars,
		                         'lists'  => array('Twitter Lead Cards' => 1),
		                          ));
		        }

		      } catch (Sailthru_Client_Exception $e) {
		        // log this error for debugging
		        header('HTTP/1.1 500 Server Error');
		      }

		   } else {
		    // SEND A 403 Forbidden HTTP code, because the token didn't match
		    header('HTTP/1.1 403 Forbidden');
		   }

		} else {
		  // SEND A 403 Forbidden HTTP code, because there was no token
		  header('HTTP/1.1 403 Forbidden');
		}

	}

} // end class

