<?php
/*
 *
 *	Gigya depends entirely on Horizon
 *
 */

class Sailthru_Gigya {

	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/
	function __construct() {

		$option = get_option( 'sailthru_integrations_options' );	
			if(! $option['sailthru_gigya_enabled'] )	
				return;

		// hook up the endpoint
		add_action('init', array($this, 'add_endpoint'), 0);

		// do something if our endpoint matches
		add_action('parse_request', array($this, 'sniff_requests'), 0);


		// Register Gigya Javascripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_gigya_scripts' ) );

		
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
		if ( ! empty( $option['sailthru_gigya_url'] ) ) {

			// create the endpoint using the user-specified url
			add_rewrite_endpoint( $option['sailthru_gigya_url'], EP_ROOT);
		
		}
	
	}	

	/**	
	* If an endpoint is set by the user, we kill WP and capture Gigya Data
	* @return die if API request
	*/
	public function sniff_requests() {

		global $wp;

		$option = get_option( 'sailthru_integrations_options' );
			$endpoint = $option['sailthru_gigya_url'];
		$request = $wp->request;
		
		if( !empty( $wp->request ) ) {

			if( stristr( $endpoint, $request ) ){
				$this->handle_request();
				exit;
			}

		}
	
	}	


	/**
	 * Add gigya. But only if gigya is turned on.
	 */
	public function register_gigya_scripts() {

		// Is Gigya turned on?
		if( isset($params['sailthru_gigya_enabled']) &&  $params['sailthru_gigya_enabled']) {

			// Check first, otherwise js could throw errors
			if( get_option('sailthru_setup_complete') ) {


				add_action('wp_footer', array( $this, 'gigya_js' ), 10);


			} // end if sailthru setup is done

		} // end if gigya is on

	} // register_gigya_scripts



	/*--------------------------------------------------*/
	/* Protected Functions
	/*--------------------------------------------------*/
	/** 
	*	This is where we send off the lead card data to Sailthru.
	*	@return void 
	*/
	protected function handle_request(){

		$option = get_option( 'sailthru_integrations_options' );
		//$salt = $option['sailthru_twitter_salt'];		

		$social_data = json_decode($_POST['json']);	

		if (isset($_POST['token'])) {

		   if ($_POST['token'] == $token) {


				$use_email_as_key = false;
				$vars = array();

				// find out what provider we're dealing with;
				$provider = $social_data->provider;

				// by default use an email address as the key
				if (isset($social_data->user->email)) {

					$data['key']  = 'email';
					$data['id'] = $social_data->user->email;
				
				} else {

					// need to do check to see if sharing is enabled on the account at setup
					switch ($provider) {
						case 'twitter':
							$data['key'] = 'twitter';
						break;

						case 'facebook':
							$data['key'] = 'facebook';
						break;

						default;
							// post details on another provider
							$data['key'] = 'extid';
						break;
					}
				
					$data['id'] =  $social_data->loginProviderUID;
				}

				$data['options'] = array(
					'login' => array(
					'user_agent' => $_SERVER['HTTP_USER_AGENT'],
					'key' => $data['key'],
					'ip' => $_SERVER['REMOTE_ADDR'],
					'site' => $_SERVER['HTTP_HOST'],
				),
					'fields' => array('activity' => 1),
				);

				$data['vars'] = array();
				$not_wanted = array('UID', 'UIDSig', 'UIDSignature');

				foreach ($social_data->user as $key => $val) {

					// remove Gigya identifiers and put them in a different var
					if (!in_array($key, $not_wanted)) {
						$data['vars'][$provider.'_'.$key] = $val;
					}

				}		   	

			    try {

					$sailthru = get_option('sailthru_setup_options');
						$api_key = $sailthru['sailthru_api_key'];
						$api_secret = $sailthru['sailthru_api_secret'];
					$client = new WP_Sailthru_Client( $api_key, $api_secret);

					$post = $this->apiPost('user', $data);

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

	} // end handle_request()



	/*-------------------------------------------
	 * Utility Functions
	 *------------------------------------------*/

	/**
	 * A function used to render the GIGYA JS
	 *
	 * @returns  string
	 */
	 function gigya_js() {

		$option = get_option( 'sailthru_integrations_options' );
			$endpoint = $option['sailthru_gigya_url'];	 	

		echo '<script src="//ak.sailthru.com/gigya/sync.js"></script>';

		echo '<script type="text/javascript">';
			echo 'SailthruGigya.callback_url = "' + get_bloginfo('url')  + '/' + $endpoint + '"';
			echo 'gigya.socialize.addEventHandlers({';
	    		echo 'onLogin:SailthruSync';
			echo '});';
		echo '</script>';

	 }

} // end class

