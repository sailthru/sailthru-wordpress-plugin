<?php

	require_once( SAILTHRU_PLUGIN_PATH . 'lib/Sailthru_Util.php' );
	require_once( SAILTHRU_PLUGIN_PATH . 'lib/Sailthru_Client.php' );

	function sailthru_add_subscriber() {

		die('add_subscriber');

		if ( !wp_verify_nonce( $_POST['sailthru_nonce'], "add_subscriber_nonce")) {
			$result['error'] = true;
			$result['message'] = "No naughty business please";
		}

		
		$email = trim( $_POST['email'] );
		if( ! filter_var($email , FILTER_VALIDATE_EMAIL) || empty($email) ) {
			$result['error'] = true;
			$result['message'] = "Please enter a valid email address.";
		} else {
			$email = filter_var($email, FILTER_VALIDATE_EMAIL);
		}

		if( isset($_POST['first_name'] ) && !empty($_POST['first_name'] ) ){
			$first_name = filter_var(trim($_POST['first_name']), FILTER_SANITIZE_STRING);
		} else {
			$first_name = '';
		}

		if( isset($_POST['last_name']) && !empty($_POST['last_name'] ) ){
			$last_name = filter_var(trim($_POST['last_name']), FILTER_SANITIZE_STRING);
		} else {
			$last_name = '';
		}

		if( $first_name || $last_name ) {

			$options = array(
				'vars' => array(
					'first_name'	=> $first_name,
					'last_name'		=> $last_name,
				)
			);

		}

		$subscribe_to_lists = array();
			if( !empty($_POST['sailthru_email_list'] ) ) {

				$lists = explode(',', $_POST['sailthru_email_list']);

				foreach( $lists as $key => $list ) {

					$subscribe_to_lists[ $list ] = 1;

				}

				$options['lists'] = $subscribe_to_lists;

			} else {

				$options['lists'] = array('Sailthru Subscribe Widget' => 1);	// subscriber is an orphan

			}


		$options['vars']['source'] = get_bloginfo('url');


		$result['data'] = array(
			'email'	=> $email,
			'options' => $options
		);

		if( $result['error'] == false ) {

			$sailthru = get_option('sailthru_setup_options');
			$api_key = $sailthru['sailthru_api_key'];
			$api_secret = $sailthru['sailthru_api_secret'];

			//$client = new Sailthru_Client( $api_key, $api_secret );
			$client = new WP_Sailthru_Client( $api_key, $api_secret);
				$res = $client->saveUser($email, $options);

			if( $res['ok'] != true ) {
				$result['error'] = true;
				$result['message'] = "There was an error subscribing you. Please try again later.";
			}

			$result['result'] = $res;

		}

		// did this request come from an ajax call?
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			$result = json_encode($result);
			echo $result;
		} else {
			echo $result['message'];
		}

		die();

	}	// end add_subscriber()