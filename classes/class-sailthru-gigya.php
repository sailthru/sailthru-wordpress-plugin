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
	 * Add gigya. But only if gigya is turned on.
	 */
	public function register_gigya_scripts() {

		// Is Gigya turned on?
		if( isset($params['sailthru_twitter_enabled']) &&  $params['sailthru_twitter_enabled']) {

			// Check first, otherwise js could throw errors
			if( get_option('sailthru_setup_complete') ) {


				add_action('wp_footer', array( $this, 'gigya_js' ), 10);


			} // end if sailthru setup is done

		} // end if gigya is on


	} // register_gigya_scripts

	/*-------------------------------------------
	 * Utility Functions
	 *------------------------------------------*/

	/**
	 * A function used to render the GIGYA JS
	 *
	 * @returns  string
	 */
	 function gigya_js() {

	 	$options = get_option('sailthru_setup_options');
		$horizon_domain = $options['sailthru_horizon_domain'];
		$gigya = get_option('sailthru_gigya_options');
		$gigya_params = array();

		// inlcudeConsumed?
		if( isset($gigya['sailthru_gigya_includeConsumed']) ) {
			$gigya_params[] = strlen( $gigya['sailthru_gigya_includeConsumed'] ) > 0 ?  'includeConsumed: '. (bool) $gigya['sailthru_gigya_includeConsumed'].'' : '';
		} else {
			$gigya['sailthru_gigya_includeConsumed'] = '';
		}

		// renderItem?
		if( isset( $gigya['sailthru_gigya_renderItem']) ) {
			$gigya_params[] = strlen($gigya['sailthru_gigya_renderItem']) > 0 ?  "renderItem: ". (bool) $gigya['sailthru_gigya_renderItem']."": '';
		} else {
			$gigya['sailthru_gigya_renderItem'] = '';
		}

		if( isset( $gigya['gigya_num_visible']) ) {
			$gigya_params[] = strlen($gigya['gigya_num_visible']) > 0 ?  "numVisible:'". esc_js( $gigya['sailthru_gigya_number'] )."' ": '';
		} else {
			$gigya['gigya_num_visible'] = '';
		}


		if ($gigya['sailthru_gigya_is_on'] == 1) {
			echo "<script type=\"text/javascript\" src=\"//ak.sail-horizon.com/gigya/v1.js\"></script>";
		 	echo "<script type=\"text/javascript\">\n";
	           echo "SailthruGigya.setup({\n";
	           echo "domain: '". esc_js($options['sailthru_horizon_domain'])."',\n";
				if( is_array($gigya_params) ) {
					foreach ($gigya_params as $key => $val) {
						if (strlen($val) >0)  {
							echo esc_js($val).",\n";
						}
					}
				}
	           echo "});\n";

		     echo " if(SailthruGigya.allContent.length == 0) { jQuery('#sailthru-gigya').hide() }";
		     echo "</script>\n";
		}

	 }

} // end class

