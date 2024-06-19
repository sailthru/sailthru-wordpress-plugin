<?php

function validate_api_setup() {

	$validate = true;

	if ( isset( $_GET['verify'] ) || isset ( $_POST['sailthru_skip_validation']) ) {

		if ( isset ( $_GET['verify'] ) ) {
			$v = sanitize_text_field( $_GET['verify'] ) === 'false' ? false : true;
		}

		if ( isset ( $_POST['sailthru_skip_validation']) ) {
			$v = sanitize_text_field( $_POST['sailthru_skip_validation'] ) === '1' ? false : true;
		}

		if (false === $v)  {
			add_filter('sailthru_api_verification', '__return_true');
			$validate = false === apply_filters( 'sailthru_api_verification', true );
		}
	}

	if ( has_filter( 'sailthru_api_verification' ) && ! isset( $_GET['verify'] ) ) {
		$validate = false === apply_filters( 'sailthru_api_verification', true );
	}

	return $validate;
}

/**
 * Setup options
 *
 * @return void
 */
function sailthru_initialize_setup_options() {

	$options = get_option( 'sailthru_setup_options' );

	add_settings_section(
		'sailthru_setup_section',   // ID used to identify this section and with which to register options
		__( '', 'sailthru-for-wordpress' ),    // Title to be displayed on the administration page
		'sailthru_setup_callback',   // Callback used to render the description of the section
		'sailthru_setup_options'   // Page on which to add this section of options
	);

	add_settings_field(
		'sailthru_form_name',     // ID used to identify the field throughout the theme
		__( 'Sailthru field name', 'sailthru-for-wordpress' ),     // The label to the left of the option interface element
		'sailthru_html_text_input_callback', // The name of the function responsible for rendering the option interface
		'sailthru_forms_options',   // The page on which this option will be displayed
		'sailthru_setup_section',   // The name of the section to which this field belongs
		array(        // The array of arguments to pass to the callback. In this case, just a description.
			'sailthru_setup_options',
			'sailthru_form_name',
			'',
			'sailthru_form_name',
		)
	);

	add_settings_field(
		'sailthru_api_key',     // ID used to identify the field throughout the theme
		__( 'Sailthru API Key', 'sailthru-for-wordpress' ),     // The label to the left of the option interface element
		'sailthru_html_text_input_callback', // The name of the function responsible for rendering the option interface
		'sailthru_setup_options',   // The page on which this option will be displayed
		'sailthru_setup_section',   // The name of the section to which this field belongs
		array(        // The array of arguments to pass to the callback. In this case, just a description.
			'sailthru_setup_options',
			'sailthru_api_key',
			'',
			'sailthru_api_key',
		)
	);

	add_settings_field(
		'sailthru_api_secret',
		__( 'Sailthru API Secret', 'sailthru-for-wordpress' ),
		'sailthru_html_text_input_callback',
		'sailthru_setup_options',
		'sailthru_setup_section',
		array(
			'sailthru_setup_options',
			'sailthru_api_secret',
			'',
			'sailthru_api_secret',
		)
	);

	$api_validated = get_option( 'sailthru_api_validated' );
	$validate_api = validate_api_setup();


	if ( $api_validated || ! $validate_api )  {

		add_settings_field(
			'sailthru_customer_id',
			'Customer Id',
			'sailthru_html_text_input_callback',
			'sailthru_setup_options',
			'sailthru_setup_section',
			array(
				'sailthru_setup_options',
				'sailthru_customer_id',
				'',
				'sailthru_customer_id',
			)
		);

		if ( ! $validate_api ) {

			add_settings_section(
				'sailthru_support_section',   // ID used to identify this section and with which to register options
				__( '', 'sailthru-for-wordpress' ),    // Title to be displayed on the administration page
				'sailthru_support_section_callback',   // Callback used to render the description of the section
				'sailthru_setup_options'   // Page on which to add this section of options
			);

			add_settings_field(
				'sailthru_skip_validation',
				'Skip API Validation',
				'sailthru_skip_validation_callback',
				'sailthru_setup_options',
				'sailthru_support_section',
				array(
					'sailthru_setup_options',
					'sailthru_skip_validation',
					'',
					'sailthru_skip_validation',
				)
			);


			add_settings_field(
				'sailthru_spm_enabled',
				'Site Personalization Manager',
				'sailthru_features_spm_callback',
				'sailthru_setup_options',
				'sailthru_support_section',
				array(
					'sailthru_setup_options',
					'features[spm_enabled]',
					'',
					'features[spm_enabled]',
				)
			);
		}

		add_settings_section(
			'sailthru_js_setup_section',   // ID used to identify this section and with which to register options
			__( '', 'sailthru-for-wordpress' ),    // Title to be displayed on the administration page
			'sailthru_js_setup_section_callback',   // Callback used to render the description of the section
			'sailthru_setup_options'   // Page on which to add this section of options
		);

		add_settings_field(
			'sailthru_js_type',
			'Sailthru JavaScript Version',
			'sailthru_js_type_callback',
			'sailthru_setup_options',
			'sailthru_js_setup_section',
			array(
				'sailthru_setup_options',
				'sailthru_js_type',
				'',
				'sailthru_js_type',
			)
		);

		if ( isset( $options['sailthru_js_type'] ) && $options['sailthru_js_type'] === 'personalize_js_custom' ) {

			add_settings_field(
				'sailthru_js_custom_mode',
				'Auto Track Pageviews',
				'sailthru_js_auto_track_pageview_callback',
				'sailthru_setup_options',
				'sailthru_js_setup_section',
				array(
					'sailthru_setup_options',
					'sailthru_js_auto_track_pageview',
					'',
					'sailthru_js_auto_track_pageview',
				)
			);

			add_settings_field(
				'sailthru_personalize_stored_tags',
				__( 'Use Stored Tags', 'sailthru-for-wordpress' ),
				'sailthru_personalize_stored_tags_callback',
				'sailthru_setup_options',
				'sailthru_js_setup_section',
				array(
					'sailthru_setup_options',
					'sailthru_personalize_stored_tags',
					'',
					'sailthru_personalize_stored_tags',
				)
			);

			add_settings_field(
				'sailthru_js_exclude_content',
				'Exclude Content',
				'sailthru_js_exclude_content_callback',
				'sailthru_setup_options',
				'sailthru_js_setup_section',
				array(
					'sailthru_setup_options',
					'sailthru_js_exclude_content',
					'',
					'sailthru_js_exclude_content',
				)
			);

		}

		if ( isset( $options['sailthru_js_type'] ) && $options['sailthru_js_type'] === 'horizon_js' ) {

			add_settings_field(
				'sailthru_horizon_domain',
				'Sailthru Horizon Domain',
				'sailthru_html_text_input_callback',
				'sailthru_setup_options',
				'sailthru_js_setup_section',
				array(
					'sailthru_setup_options',
					'sailthru_horizon_domain',
					'',
					'sailthru_horizon_domain',
				)
			);

			add_settings_field(
				'sailthru_horizon_load_type',
				'Horizon Loading',
				'sailthru_horizon_loadtype_callback',
				'sailthru_setup_options',
				'sailthru_js_setup_section',
				array(
					'sailthru_setup_options',
					'sailthru_horizon_load_type',
					'',
					'sailthru_horizon_load_type',
				)
			);
		}

		add_settings_section(
			'recaptcha_setup_section',   // ID used to identify this section and with which to register options
			__( '', 'sailthru-for-wordpress' ),    // Title to be displayed on the administration page
			'recaptcha_setup_callback',   // Callback used to render the description of the section
			'sailthru_setup_options'   // Page on which to add this section of options
		);

		add_settings_field(
			'google_recaptcha_site_key',
			__( 'reCaptcha Site Key', 'sailthru-for-wordpress' ),
			'sailthru_html_text_input_callback',
			'sailthru_setup_options',
			'recaptcha_setup_section',
			array(
				'sailthru_setup_options',
				'google_recaptcha_site_key',
				'',
				'google_recaptcha_site_key',
			)
		);

		add_settings_field(
			'google_recaptcha_secret',
			__( 'reCaptcha Secret Key', 'sailthru-for-wordpress' ),
			'sailthru_html_hidden_text_input_callback',
			'sailthru_setup_options',
			'recaptcha_setup_section',
			array(
				'sailthru_setup_options',
				'google_recaptcha_secret',
				'',
				'google_recaptcha_secret',
			)
		);

		add_settings_section(
			'sailthru_email_setup_section',   // ID used to identify this section and with which to register options
			__( '', 'sailthru-for-wordpress' ),    // Title to be displayed on the administration page
			'sailthru_email_section_callback',   // Callback used to render the description of the section
			'sailthru_setup_options'   // Page on which to add this section of options
		);

		add_settings_field(
			'sailthru_setup_new_user_override_template', // ID used to identify the field throughout the theme
			__( 'New User Registration', 'sailthru-for-wordpress' ),  // The label to the left of the option interface element
			'sailthru_setup_email_template_callback',
			'sailthru_setup_options',
			'sailthru_email_setup_section',
			array(
				'sailthru_setup_options',
				'sailthru_setup_new_user_override_template',
				'',
				'sailthru_setup_new_user_override_template',
				'Select a template to send new user registration emails via Sailthru. The template must have a subject line of subject and contain a variable {body} in the HTML of the email. ',
			)
		);

		// Only allow an override of all mail when not on VIP
		if ( ! defined( 'WPCOM_IS_VIP_ENV' ) && false === 'WPCOM_IS_VIP_ENV' ) {

			add_settings_field(
				'sailthru_setup_email_template', // ID used to identify the field throughout the theme
				__( 'Default Template', 'sailthru-for-wordpress' ),  // The label to the left of the option interface element
				'sailthru_setup_email_template_callback',
				'sailthru_setup_options',
				'sailthru_email_setup_section',
				array(
					'sailthru_setup_options',
					'sailthru_setup_email_template',
					'',
					'sailthru_setup_email_template',
					'Select a template to send all WordPress transactionals via Sailthru. The template must have a subject line of subject and contain a variable {body} in the HTML of the email. ',
				)
			);
		}
	}

	// Finally, we register the fields with WordPress
	register_setting(
		'sailthru_setup_options',
		'sailthru_setup_options',
		'sailthru_setup_handler'
	);

} // end sailthru_initialize_setup_options
add_action( 'admin_init', 'sailthru_initialize_setup_options' );



/* ------------------------------------------------------------------------ *
 * Section Callbacks
 * ------------------------------------------------------------------------ */

/**
 * Provides a simple description for each setup page respectively.
 */

function sailthru_setup_callback() {

	// render the admin tabs
	// sailthru_admin_tabs('sailthru_configuration_page');
	echo '<div id="icon-options-general"><h3>API Keys</h3></div>';
	echo '<p>Add your Sailthru API key & Secret, you can find this on the <a href="https://my.sailthru.com/settings_api">settings page</a> of the Sailthru dashboard.</p><p>Not sure what these are? Contact <a href="mailto:support@sailthru.com">support@sailthru.com</a> ';
} // end sailthru_setup_callback

function recaptcha_setup_callback() {
	echo '<h3 class="sailthru-sub-section">reCaptcha Site Key and Secret</h3>';
	echo '<p>(Optional) Add reCaptcha to your signup forms to detect potential spamming and bots. <a href="https://www.google.com/recaptcha/intro/v3.html" target="_blank">Visit the reCaptcha homepage</a> to set up a site key and secret.</p>';
}


/* ------------------------------------------------------------------------ *
 * Field Callbacks - Helpers to render form elements specific to this section
 * ------------------------------------------------------------------------ */
/**
 * Creates a checkbox for the Horizon JS output type
 *
 */
function sailthru_horizon_loadtype_callback() {

	$options   = get_option( 'sailthru_setup_options' );
	$load_type = isset( $options['sailthru_horizon_load_type'] ) ? $options['sailthru_horizon_load_type'] : '';
	echo '<input type="checkbox" id="checkbox_example" name="sailthru_setup_options[sailthru_horizon_load_type]" value="1"' . checked( 1, esc_attr( $load_type ), false ) . '/>';
	echo '<small>Use synchronous loading for Horizon</small>';

}

/**
 * Creates an option to manually enable SPM
 *
 */

function sailthru_features_spm_callback() {

		$options = get_option( 'sailthru_settings' );
		$value   = isset( $options['features']['spm_enabled'] ) ? $options['features']['spm_enabled'] : '';

		echo '<input type="checkbox" id="checkbox_example" name="sailthru_setup_options[features][spm_enabled]" value="1"' . checked( 1,esc_attr( $value ), false ) . '/>';
		echo '<small>Check if SPM is enabled on your Sailthru account</small>';
}

function sailthru_skip_validation_callback() {

		echo '<input type="checkbox" id="checkbox_example" name="sailthru_skip_validation" value="1" checked/>';
		echo '<small>Enables setup without API validation</small>';
}


/**
 * Creates a section for support tools if API validation is problematic.
 *
 */
function sailthru_support_section_callback() {

	echo '<h3 class="sailthru-sub-section">Support Tools</h3>';
	echo "<p>Enabled for support tools to bypass some settings</p>";

}


/**
 * Creates a checkbox for the Sailthru JS output type
 *
 */
function sailthru_js_type_callback() {

	$options = get_option( 'sailthru_setup_options' );
	$js_type = isset( $options['sailthru_js_type'] ) ? $options['sailthru_js_type'] : '';

	$html_options = array(
		'none'                  => 'Select',
		'personalize_js'        => 'Sailthru Script Tag',
		'personalize_js_custom' => 'Sailthru Script Tag (custom mode)',
	);

	if ( $js_type == 'horizon_js' ) {
		$html_options['horizon_js'] = 'Horizon (deprecated)';
	}

	echo '<select id="sailthru_js_type" name="sailthru_setup_options[sailthru_js_type]">';
	foreach ( $html_options as $key => $val ) {

		if ( $key === $js_type ) {
			$selected = ' selected';
		} else {
			$selected = '';
		}
		echo '<option value="' . esc_attr( $key ) . '"' . esc_attr( $selected ) . '>' . esc_attr( $val ) . '</option>';
	}
	echo '</select>';

}

/**
 * Creates field for the customer Id
 *
 */
function sailthru_customer_id_callback() {

	$options     = get_option( 'sailthru_setup_options' );
	$customer_id = isset( $options['sailthru_customer_id'] ) ? $options['sailthru_customer_id'] : '';
	echo esc_html( $customer_id );
}


/**
 * Creates a checkbox to use custom mode
 *
 */
function sailthru_js_auto_track_pageview_callback() {

		$options = get_option( 'sailthru_setup_options' );
		$value   = isset( $options['sailthru_js_auto_track_pageview'] ) ? $options['sailthru_js_auto_track_pageview'] : 'true';

		echo '<select name="sailthru_setup_options[sailthru_js_auto_track_pageview]" id="sailthru_js_auto_track_pageview">';
		echo '<option value="true" ' . selected( 'true', esc_attr( $value ), false ) . '>Yes</option>';
		echo '<option value="false" ' . selected( 'false', esc_attr( $value ), false ) . '>No</option>';
		echo '</select>';
}

/**
 * Creates a checkbox to use stored tags
 *
 */
function sailthru_personalize_stored_tags_callback() {

	$options = get_option( 'sailthru_setup_options' );
	$value   = isset( $options['sailthru_ignore_personalize_stored_tags'] ) ? $options['sailthru_ignore_personalize_stored_tags'] : 'true';

	echo '<select name="sailthru_setup_options[sailthru_ignore_personalize_stored_tags]" id="sailthru_ignore_personalize_stored_tags">';
	echo '<option value="true" ' . selected( 'true', esc_attr( $value ), false ) . '>Yes</option>';
	echo '<option value="false" ' . selected( 'false', esc_attr( $value ), false ) . '>No</option>';
	echo '</select>';

}


/**
 * Creates a checkbox to use exclude content flag.
 *
 */
function sailthru_js_exclude_content_callback() {

		$options = get_option( 'sailthru_setup_options' );
		$value   = isset( $options['sailthru_js_exclude_content'] ) ? $options['sailthru_js_exclude_content'] : 'true';

		echo '<select name="sailthru_setup_options[sailthru_js_exclude_content]" id="sailthru_js_exclude_content">';
		echo '<option value="false" ' . selected( 'false', esc_attr( $value ), false ) . '>No</option>';
		echo '<option value="true" ' . selected( 'true', esc_attr( $value ), false ) . '>Yes</option>';
		echo '</select>';

}


/**
 * Creates a section header for the emails
 *
 */
function sailthru_email_section_callback() {

	echo '<h3 class="sailthru-sub-section">Email Settings</h3>';
	echo '<p>You can use Sailthru to deliver your WordPress emails.</p>';

}

/**
 * Creates a section header for the JavaScript Settings.
 *
 */
function sailthru_js_setup_section_callback() {

	echo '<h3 class="sailthru-sub-section">JavaScript Tag Settings</h3>';
	echo "<p>Deploy Sailthru's JavaScript Tags automatically. Horizon JS is officially deprecated and will be retired in a future release.</p>";

}


/**
 * Creates a default template if there are none yet.
 *
 */
function sailthru_setup_email_template_callback( $args ) {

	$sailthru = get_option( 'sailthru_setup_options' );
	if ( isset( $sailthru['sailthru_api_key'] ) && isset( $sailthru['sailthru_api_secret'] ) ) {
		$api_key    = $sailthru['sailthru_api_key'];
		$api_secret = $sailthru['sailthru_api_secret'];

		$client = new WP_Sailthru_Client( $api_key, $api_secret );

		try {
			if ( $client ) {
				$res = $client->getTemplates();
			}
		} catch ( Sailthru_Client_Exception $e ) {
				$api_error = true;
		}

		if ( isset( $res['error'] ) ) {

			$tpl = array();

		} else {

			if ( isset( $res['templates'] ) ) {
				$tpl = $res['templates'];
			} else {
				$tpl = '';
			}
		}

		// if there are no templates available create a basic one
		// since multiple settings use this callback, we do this
		// only if we're in setup mode:
		if ( isset( $arg[1] ) ) {
			$has_default_template = 'sailthru_setup_email_template';
		} else {
			$has_default_template = false;
		}
		if ( $has_default_template ) {

			if ( isset( $tpl ) || ! empty( $tpl ) ) {

				$name  = get_bloginfo( 'name' );
				$email = get_bloginfo( 'admin_email' );
				try {

					if ( $sailthru_client ) {

						$template = 'default-template';
						$options  = array(
							'from_name'    => $name,
							'from_email'   => $email,
							'content_html' => '{body}',
							'subject'      => '{subject}',
						);
						$response = $client->saveTemplate( $template, $options );

					}
				} catch ( Sailthru_Client_Exception $e ) {
					//silently fail
					return;
				}
			}
		}
	}

	// Render the Drop Downs
	if ( isset( $tpl ) ) {
		// escaped in function.
		echo sailthru_create_dropdown( $args, $tpl );
	} else {
		// escaped in function.
		echo sailthru_create_dropdown( $args, array() );

		if ( ! empty( $api_error ) ) {
			echo '<p>We could not connect to the Sailthru API</p>';
		} else {
			echo  '<p>Sailthru Api Key and Secret must be saved first</p>';
		}
	}

}


/**
 * Sanitize the text inputs, and don't let the horizon
 * domain get saved with either http:// https:// or www
 */
function sailthru_setup_handler( $input ) {

	$output = array();
	// api key
	if ( isset( $input['sailthru_api_key'] ) ) {
		$output['sailthru_api_key'] = filter_var( $input['sailthru_api_key'], FILTER_CALLBACK, array( 'options' => 'sanitize_text_field' ) );
	} else {
		$output['sailthru_api_key'] = false;
	}

	// api secret
	if ( isset( $input['sailthru_api_secret'] ) ) {
		$output['sailthru_api_secret'] = filter_var( $input['sailthru_api_secret'], FILTER_CALLBACK, array( 'options' => 'sanitize_text_field' ) );
	} else {
		$output['sailthru_api_secret'] = false;
	}

	// customer Id
	if ( isset( $input['sailthru_customer_id'] ) ) {
		$output['sailthru_customer_id'] = filter_var( $input['sailthru_customer_id'], FILTER_CALLBACK, array( 'options' => 'sanitize_text_field' ) );
	} else {
		$output['sailthru_customer_id'] = '';
	}

	if ( ! $output['sailthru_api_key'] || ! $output['sailthru_api_secret'] ) {
		add_settings_error( 'sailthru-notices', 'sailthru-api-keys-fail', __( 'Add a valid API key and Secret' ), 'error' );
		return $output;
	}


	$sailthru = new WP_Sailthru_Client( $output['sailthru_api_key'], $output['sailthru_api_secret'] );
	$validate_api = validate_api_setup();

	if ( ! $validate_api ) {

		// If the customer is overriding verification store the SPM value in the settings.
		if ( isset( $input['features']['spm_enabled'] ) ) {

			$output['features']['spm_enabled'] = filter_var( $input['features']['spm_enabled'], FILTER_CALLBACK, array( 'options' => 'sanitize_text_field' ) );

			if ( '1' === $output['features']['spm_enabled'] ) {
				$st_settings['features']['spm_enabled'] = '1';
			}

			$st_settings['customer_id'] = $output['sailthru_customer_id'];

			update_option( 'sailthru_settings', $st_settings );
		}

		// if API verification has been overriden assume the API is good and allow error to surface in template call.
		update_option( 'sailthru_api_validated', true );

	} else {

		try {
			$settings = $sailthru->apiGet( 'settings' );

			if ( $settings ) {
				// Get the Customer ID from Sailthru.
				$output['sailthru_customer_id'] = filter_var( $settings['customer_id'], FILTER_CALLBACK, array( 'options' => 'sanitize_text_field' ) );

				$st_settings = array(
					'customer_id' => $settings['customer_id'],
					'features'    => $settings['features'],
					'domains'     => $settings['domains'],
				);

				$st_settings = apply_filters( 'sailthru_settings_api_filter', $st_settings );

				update_option( 'sailthru_settings', $st_settings );
				update_option( 'sailthru_api_validated', true );
			} else {
				sailthru_invalidate( false, false );
				return $output;
			}
		} catch ( Exception $e ) {
				sailthru_invalidate( false, false );
				add_settings_error( 'sailthru-notices', 'sailthru-api-secret-fail', __( $e->getMessage() ), 'error' );
				return $output;
		}
	}

	// recaptcha settings
    $output['google_recaptcha_site_key'] = '';
    if ( isset( $input['google_recaptcha_site_key'] ) ) {
	    $output['google_recaptcha_site_key'] = filter_var( $input['google_recaptcha_site_key'], FILTER_CALLBACK, array( 'options' => 'sanitize_text_field' ) );
    }

    $output['google_recaptcha_secret'] = '';
    if ( isset( $input['google_recaptcha_secret'] ) ) {
	    $output['google_recaptcha_secret'] = filter_var( $input['google_recaptcha_secret'], FILTER_CALLBACK, array( 'options' => 'sanitize_text_field' ) );
    }

	// javascript type
	if ( isset( $input['sailthru_js_type'] ) ) {
		$output['sailthru_js_type'] = filter_var( $input['sailthru_js_type'], FILTER_CALLBACK, array( 'options' => 'sanitize_text_field' ) );
	} else {
		$output['sailthru_js_type'] = '';
	}

	// auto pageviews
	if ( isset( $input['sailthru_js_auto_track_pageview'] ) ) {
		$output['sailthru_js_auto_track_pageview'] = filter_var( $input['sailthru_js_auto_track_pageview'], FILTER_CALLBACK, array( 'options' => 'sanitize_text_field' ) );
	} else {
		$output['sailthru_js_auto_track_pageview'] = false;
	}

	// ignore stored tags
	if ( isset( $input['sailthru_ignore_personalize_stored_tags'] ) ) {
		$output['sailthru_ignore_personalize_stored_tags'] = filter_var( $input['sailthru_ignore_personalize_stored_tags'], FILTER_CALLBACK, array( 'options' => 'sanitize_text_field' ) );
	} else {
		$output['sailthru_ignore_personalize_stored_tags'] = false;
	}

	// exclude content
	if ( isset( $input['sailthru_js_exclude_content'] ) ) {
		$output['sailthru_js_exclude_content'] = filter_var( $input['sailthru_js_exclude_content'], FILTER_CALLBACK, array( 'options' => 'sanitize_text_field' ) );
	} else {
		$output['sailthru_js_exclude_content'] = false;
	}

	// // horizon domain
	if ( isset( $input['sailthru_horizon_domain'] ) ) {
		$output['sailthru_horizon_domain'] = filter_var( $input['sailthru_horizon_domain'], FILTER_CALLBACK, array( 'options' => 'sanitize_text_field' ) );
	} else {
		$output['sailthru_horizon_domain'] = '';
	}

	// horizon load type
	if ( isset( $input['sailthru_horizon_load_type'] ) ) {
		$output['sailthru_horizon_load_type'] = 1;
	} else {
		$output['sailthru_horizon_load_type'] = false;
	}

	// set errors
	if ( empty( $output['sailthru_api_secret'] ) ) {
		add_settings_error( 'sailthru-notices', 'sailthru-api-secret-fail', __( 'Sailthru will not function without an API secret.' ), 'error' );
	}

	if ( empty( $output['sailthru_api_key'] ) ) {
		add_settings_error( 'sailthru-notices', 'sailthru-api-key-fail', __( 'Sailthru will not function without an API key.' ), 'error' );
	}

	if ( empty( $output['sailthru_horizon_domain'] ) && ( 'horizon_js' === $input['sailthru_js_type'] ) ) {
		add_settings_error( 'sailthru-notices', 'sailthru-horizon-domain-fail', __( 'Please enter your Horizon domain.' ), 'error' );
	} else {

		$output['sailthru_horizon_domain'] = str_ireplace( 'http://', '', $output['sailthru_horizon_domain'] );
		$output['sailthru_horizon_domain'] = str_ireplace( 'https://', '', $output['sailthru_horizon_domain'] );
		$output['sailthru_horizon_domain'] = str_ireplace( 'www.', '', $output['sailthru_horizon_domain'] );

		// remove trailing
		if ( substr( $output['sailthru_horizon_domain'], -1 ) === '/' ) {
			$output['sailthru_horizon_domain'] = substr( $output['sailthru_horizon_domain'], 0, -1 );
		}
	}

	// This will have run before this section has been displayed
	$api_validated = get_option( 'sailthru_api_validated' );

	if ( $api_validated ) {

		// creates an email template if one does not already exist
		// don't try and setup the template if validation is disabled.
		if ( $validate_api ) {
			sailthru_create_wordpress_template();
		}

		// sitewide email template
		if ( isset( $input['sailthru_setup_email_template'] ) ) {
			$output['sailthru_setup_email_template'] = trim( $input['sailthru_setup_email_template'] );
		} else {
			$output['sailthru_setup_email_template'] = false;
		}

		if ( isset( $input['sailthru_setup_new_user_override_template'] ) ) {
			$output['sailthru_setup_new_user_override_template'] = trim( $input['sailthru_setup_new_user_override_template'] );
		} else {
			$output['sailthru_setup_new_user_override_template'] = false;
		}
	}

	// Content Vars
	if ( isset( $input['content_vars'] ) ) {
		$output['content_vars'] = filter_var( $input['content_vars'], FILTER_CALLBACK, array( 'options' => 'sanitize_text_field' ) );
	} else {
		$output['content_vars'] = '';
	}

	update_option( 'sailthru_setup_complete', true );
	return $output;

}
// end sailthru_setup_handler
