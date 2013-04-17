<?php

	$sailthru = get_option('sailthru_setup_options');
	$api_key = $sailthru['sailthru_api_key'];
	$api_secret = $sailthru['sailthru_api_secret'];

	$client = new Sailthru_Client( $api_key, $api_secret );

		$lists = $client->getLists();	
		$templates = $client->getTemplates();
		
?>