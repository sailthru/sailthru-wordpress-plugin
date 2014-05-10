<?php
/*
 *
 *	Integrations depends entirely on Horizon
 *
 */

class Sailthru_Integrations {

	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/
	function __construct() {

		// Register Integrations Javascripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_integrations_scripts' ) );

		
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
	 * Add integrations. But only if integrations is turned on.
	 */
	public function register_integrations_scripts() {

		// Is Gigya turned on?
		if( isset($params['sailthru_twitter_enabled']) &&  $params['sailthru_twitter_enabled']) {

			// Check first, otherwise js could throw errors
			if( get_option('sailthru_setup_complete') ) {


				add_action('wp_footer', array( $this, 'gigya_js' ), 10);


			} // end if sailthru setup is done

		} // end if integrations is on


	} // register_integrations_scripts

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
		$integrations = get_option('sailthru_integrations_options');
		$integrations_params = array();

		// inlcudeConsumed?
		if( isset($integrations['sailthru_integrations_includeConsumed']) ) {
			$integrations_params[] = strlen( $integrations['sailthru_integrations_includeConsumed'] ) > 0 ?  'includeConsumed: '. (bool) $integrations['sailthru_integrations_includeConsumed'].'' : '';
		} else {
			$integrations['sailthru_integrations_includeConsumed'] = '';
		}

		// renderItem?
		if( isset( $integrations['sailthru_integrations_renderItem']) ) {
			$integrations_params[] = strlen($integrations['sailthru_integrations_renderItem']) > 0 ?  "renderItem: ". (bool) $integrations['sailthru_integrations_renderItem']."": '';
		} else {
			$integrations['sailthru_integrations_renderItem'] = '';
		}

		if( isset( $integrations['integrations_num_visible']) ) {
			$integrations_params[] = strlen($integrations['integrations_num_visible']) > 0 ?  "numVisible:'". esc_js( $integrations['sailthru_integrations_number'] )."' ": '';
		} else {
			$integrations['integrations_num_visible'] = '';
		}


		if ($integrations['sailthru_integrations_is_on'] == 1) {
			echo "<script type=\"text/javascript\" src=\"//ak.sail-horizon.com/integrations/v1.js\"></script>";
		 	echo "<script type=\"text/javascript\">\n";
	           echo "SailthruIntegrations.setup({\n";
	           echo "domain: '". esc_js($options['sailthru_horizon_domain'])."',\n";
				if( is_array($integrations_params) ) {
					foreach ($integrations_params as $key => $val) {
						if (strlen($val) >0)  {
							echo esc_js($val).",\n";
						}
					}
				}
	           echo "});\n";

		     echo " if(SailthruIntegrations.allContent.length == 0) { jQuery('#sailthru-integrations').hide() }";
		     echo "</script>\n";
		}

	 }

} // end class

