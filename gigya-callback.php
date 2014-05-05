<?php
//Client will need to setup if different
  if( ! defined('SAILTHRU_PLUGIN_PATH') )
	define( 'SAILTHRU_PLUGIN_PATH', "wp-content/themes/vip/plugins/sailthru/" );
  require_once( 'wp-blog-header.php' );	
  require_once( SAILTHRU_PLUGIN_PATH . 'lib/Sailthru_Util.php' );
  require_once( SAILTHRU_PLUGIN_PATH . 'lib/Sailthru_Client.php' );
  require_once( SAILTHRU_PLUGIN_PATH . 'lib/Sailthru_Client_Exception.php' );

$sailthru = get_option( 'sailthru_setup_options' );
$api_key = $sailthru[ 'sailthru_api_key' ];
$api_secret = $sailthru[ 'sailthru_api_secret' ];

$client = new Sailthru_Social($api_key, $api_secret);
$profile = json_decode($_POST['json']);
$result = $client->social_login($profile);

?>