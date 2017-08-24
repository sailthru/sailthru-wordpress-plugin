<?php

require_once SAILTHRU_PLUGIN_PATH . 'views/admin.functions.setup.options.php';
require_once SAILTHRU_PLUGIN_PATH . 'views/admin.functions.concierge.options.php';
require_once SAILTHRU_PLUGIN_PATH . 'views/admin.functions.scout.options.php';
require_once SAILTHRU_PLUGIN_PATH . 'views/admin.functions.subscribe.options.php';



/* ------------------------------------------------------------------------ *
 * Field Callbacks - Helpers to render form elements
 * ------------------------------------------------------------------------ */
/*
 * The calling function is expected to pass us an array of this format:
 * $args = array(
 * 		0 => 	collection
 * 		1 =>	option_name
 * 		2 =>	default
 *		3 =>	html_id
 * )
 * Echos a properly formatted <input type="text" /> with a value
 */
function sailthru_html_text_input_callback( $args ) {

	$collection    = $args[0];
	$option_name   = $args[1];
	$default_value = $args[2];
	$html_id       = $args[3];
	if ( isset( $args[4] ) ) {
		$hint          = $args[4];
	} else {
		$hint = '';
	}
	$options       = get_option( $collection );

	// Make sure the element is defined in the options. If not, we'll use the preferred default.
	$value = '';
	if ( isset( $options[ $option_name ] ) ) {
		$value = $options[ $option_name ];
	} else {
		$value = $default_value;
	}

	// Render the output
	echo '<input type="text" id="' . esc_attr( $html_id ) . '" name="' . esc_attr( $collection ) . '[' . esc_attr( $option_name ) . ']" value="' . esc_attr( $value ) . '" class="regular-text" />';
	if ( isset( $hint ) ) {
		echo '<div class="instructions">'.esc_html( $hint ).'</div>';
	}

} // end sailthru_html_text_input_callback.


/**
 * This function renders the interface elements for toggling a feature on or off.
 *
 * It accepts an array of arguments in the following format:
 * $args = array(
 *   0 =>  collection
 *   1 => option_name
 *   2 => default
 *  3 => html_id
 *  4 => label
 * )
 * Echos a properly formatted <input type="checkbox" /> with a value
 */
function sailthru_toggle_feature_callback( $args ) {

	$collection    = $args[0];
	$option_name   = $args[1];
	$default_value = $args[2];
	$html_id       = $args[3];
	$label         = $args[4];

	// Read the options collection
	$options = get_option( $collection );

	if (empty( $options ) ) {
		$options = array();
	}

	// We don't want errors on first run, and since this is
	// only a toggle, we can create this option_name if it
	// doesn't exist.
	if ( ! isset( $options[ $option_name ] ) ) {
		$options[ $option_name ] = 0; // evaluates to not checked
	}


	// Fully formed checkbox
	$html = '<input type="checkbox" id="' . esc_attr( $html_id ) . '" name="' . esc_attr( $collection ) . '[' . esc_attr( $option_name ) . ']" value="' . esc_attr( $default_value ) . '" ' . checked( 1,  $options[ $option_name ], false ) . '/>';

	// Add alabel next to the checkbox
	$html .= '<label for="' . esc_attr( $html_id ) . '">&nbsp;'  . esc_html( $label ) . '</label>';

	echo $html;

} // end sailthru_toggle_feature_callback





/* ------------------------------------------------------------------------ *
 * Setting Callbacks
 * ------------------------------------------------------------------------ */

/**
 * Sanitization callback for the text inputs.
 * Loops through the incoming option and strips all tags and slashes from the value
 * before serializing it.
 *
 * @param unknown s $input The unsanitized collection of options.
 *
 * @returns   The collection of sanitized values.
 */
function sailthru_sanitize_text_input( $input ) {

	// Define the array for the updated options
	$output = array();

	if ( is_array( $input ) ) {

		// Loop through each of the options sanitizing the data
		foreach ( $input as $key => $val ) {
			if ( $key === 'sailthru_scout_renderItem' ) {
				$output[ $key ] = esc_js( $input[ $key ] );
			} else if ( isset ( $input[ $key ] ) ) {
					$output[ $key ] = sanitize_text_field( stripslashes( $input[ $key ] ) );
				} // end if

		} // end foreach

	} // end if

	// Return the new collection
	return apply_filters( 'sailthru_sanitize_text_input', $output, $input );

} // end sailthru_sanitize_text_input



/* ------------------------------------------------------------------------ *
 * Utility Functions
 * ------------------------------------------------------------------------ */

/**
 * Create a fully formed <select></select> dropdown
 * out of the arguments provided.
 *
 * @param unknown $args
 * It accepts an array of arguments in the following format:
 * $args = array(
 *   0 =>  collection
 *   1 => option_name
 *   2 => default
 *  3 => html_id
 * )
 *
 * @param unknown $values
 * An array of an array of values.
 * It should take on this format:
 * array(
 * 0  => array('thing' => 'value')
 * )
 */
function sailthru_create_dropdown( $args, $values ) {

	$collection  = $args[0];
	$option_name = $args[1];
	$default     = $args[2]; // we're not using this yet
	$html_id     = $args[3];
	if ( isset( $args[4] ) ) {
		$instructions = $args[4];
	} else {
		$instructions = false;
	}
	$current     = get_option( $collection );

	if ( isset( $current[ $option_name ] ) ) {
		$saved_value = $current[ $option_name ];
	} else {
		$saved_value = '';
	}



	$html = '<select name="' . esc_attr( $collection ) . '[' . esc_attr( $option_name ) . ']" id="' . esc_attr( $html_id ) . '">';

	$html .= '<option value=""> - Select - </option>';

	if ( is_array( $values ) ) {
		foreach ( $values as $key => $value ) {

			$html .= '<option value="' . esc_attr( $value['name'] ) . '" ' . selected( $saved_value, $value['name'], false ) . '>' . esc_attr( $value['name'] ) . '</option>';

		}
	}

	$html .= '</select>';

	if ( !empty( $instructions ) ) {
		$html .= '<p class="description">' . $instructions . '</p>';
	}

	return $html;

}

/**
 * Gets the Sailthru account settings
 *
 * @return stdclass
 */
function sailthru_account_settings() {

	$settings   = get_option( 'sailthru_setup_options' );

	if ( ! empty( $settings['sailthru_api_key'] ) && ! empty( $settings['sailthru_api_secret'] ) ) {

		$client = new WP_Sailthru_Client( $settings['sailthru_api_key'], $settings['sailthru_api_secret'] );

		try {
			return $client->apiGet( 'settings' );
		} catch ( Exception $e ) {
			write_log( $e );
			return false;
		}
	}

}

/**
 * Function to check if Sailthru has been configured
 *
 * @return void
 */
function sailthru_status() {

	// default to false
	$status = array (
		'setup' => false,
		'api' => false,
	);

	$api =  get_option( 'sailthru_api_validated' );

	if ($api != '0' || $api != false) {
		$status['api'] = true;
	} else {
		// invalidate the setup if the API is invalid
		update_option( 'sailthru_setup_complete', false );
	}

	$setup =  get_option( 'sailthru_setup_complete' );
	if ($setup != '0' || $setup != false) {
		$status['setup'] = true;
	}

	return $status;
}

 function sailthru_invalidate($api, $setup) {
	update_option( 'sailthru_setup_complete', $setup );
	update_option( 'sailthru_api_validated', $api );
 }

/**
 * This function verifies Sailthru is working by making an API Call to Sailthru
 *
 */
function sailthru_verify_setup() {
	
	return get_option( 'sailthru_api_validated' );
}
// end sailthru_verify_setup.

/**
 * This function verifies that the template is coded correctly
 *
 */
function sailthru_verify_template( $tpl ) {

	$errors = array();

	if ( $tpl['subject'] != '{subject}' ) {
		$errors = 'Your template needs to have {subject} as the subject line.';
	}

	if ( ! strstr( $tpl['content_html'], '{body}' ) ) {
		$errors = 'Your template needs to have {body} variable.';
	}

	return $errors;
}
