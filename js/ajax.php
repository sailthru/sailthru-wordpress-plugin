<?php

/**
 * Anything we want to post through the API can flow
 * this way. We'll have both WP functions availble to
 * us and the Sailthru Client library
 */
$wp_load = realpath("../../../../wp-load.php");

if(!file_exists($wp_load)) {

	$wp_config = realpath("../../../../wp-config.php");

	if (!file_exists($wp_config)) {
  		exit("Can't find wp-config.php or wp-load.php");
	} else {
  		require_once($wp_config);
	}

} else {

	require_once($wp_load);

}

require_once( '../lib/Sailthru_Util.php' );
require_once( '../lib/Sailthru_Client.php' );

$return = array();
$return['error']   = false;
$return['message'] = '';

if( isset( $_POST['sailthru_action'] ) ) {

	switch( $_POST['sailthru_action'] ) {

		case "add_subscriber":
			$email = isset( $_POST['email'] ) ? trim( sanitize_email( $_POST['email'] ) ) : '';
			if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) || empty( $email ) ) {
				$return['error']   = true;
				$return['message'] = 'Please enter a valid email address.';
			} else {
				$email = filter_var( $email, FILTER_VALIDATE_EMAIL );
			}

			if ( ! empty( $_POST['first_name'] ) ) {
				$first_name = filter_var( $_POST['first_name'], FILTER_CALLBACK, array( 'options' => 'sanitize_text_field' ) );
			} else {
				$first_name = '';
			}

			if ( ! empty( $_POST['last_name'] ) ) {
				$last_name = filter_var( $_POST['last_name'], FILTER_CALLBACK, array( 'options' => 'sanitize_text_field' ) );
			} else {
				$last_name = '';
			}

			$first_name = trim( $first_name );
			$last_name  = trim( $last_name );

			if ( $first_name || $last_name ) {
				$options = [
					'vars' => [
						'first_name'	=> $first_name,
						'last_name'		=> $last_name
					]
				];
			}

			$subscribe_to_lists = [];
				if ( !empty($_POST['sailthru_email_list'] ) ) {

					$lists = explode(',', sanitize_text_field( $_POST['sailthru_email_list'] ) );

					foreach( $lists as $key => $list ) {
						$subscribe_to_lists[ $list ] = 1;
					}

					$options['lists'] = $subscribe_to_lists;

				} else {

					$options['lists'] = array('Sailthru Subscribe Widget' => 1);	// subscriber is an orphan

				}

			$options['vars']['source'] = get_bloginfo('url');

			$return['data'] = [
				'email'	=> $email,
				'options' => $options
			];

			if ( false === $return['error'] ) {
				$sailthru = get_option('sailthru_setup_options');
				$api_key = $sailthru['sailthru_api_key'];
				$api_secret = $sailthru['sailthru_api_secret'];

				$client = new WP_Sailthru_Client( $api_key, $api_secret );
				$res = $client->saveUser($email, $options);

				if( $res['ok'] !== 'true' ) {
					$result['error'] = true;
					$result['message'] = "There was an error subscribing you. Please try again later.";
				}

				$return['result'] = $res;
			}

			break;

		default:

			$return['error'] = true;
			$return['message'] = 'No action defined. None taken.';

	}

}

echo wp_json_encode( $return );
die();
