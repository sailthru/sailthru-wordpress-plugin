<?php
/**
Plugin Name: Sailthru for WordPress
Plugin URI: http://sailthru.com/
Description: Add the power of Sailthru to your WordPress set up.
Version: 3.2.2
Author: Sailthru
Author URI: http://sailthru.com
Author Email: integrations@sailthru.com
License:

Copyright 2013 (Sailthru)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

**/



/**
 * The current version of the plugin.
 *
 * @since 3.0.6
 * @var   const    $version    The current version of the plugin.
 */
if ( ! defined( 'SAILTHRU_PLUGIN_VERSION' ) ) {
	define( 'SAILTHRU_PLUGIN_VERSION', '3.2.1' );
}

if ( ! defined( 'SAILTHRU_PLUGIN_PATH' ) ) {
	define( 'SAILTHRU_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'SAILTHRU_PLUGIN_URL' ) ) {
	define( 'SAILTHRU_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}


/*
 * Sailthru PHP5 Developer Library.
 * Source: http://getstarted.sailthru.com/developers/client-libraries/set-config-file/php5.
 */
require_once SAILTHRU_PLUGIN_PATH . 'lib/Sailthru_Util.php';
require_once SAILTHRU_PLUGIN_PATH . 'lib/Sailthru_Client.php';
require_once SAILTHRU_PLUGIN_PATH . 'lib/Sailthru_Client_Exception.php';
require_once SAILTHRU_PLUGIN_PATH . 'classes/class-wp-sailthru-client.php';

/*
 * Get Sailthru for WordPress plugin classes
 */
require_once SAILTHRU_PLUGIN_PATH . 'classes/class-sailthru-horizon.php';
require_once SAILTHRU_PLUGIN_PATH . 'classes/class-sailthru-concierge.php';
require_once SAILTHRU_PLUGIN_PATH . 'classes/class-sailthru-content.php';
require_once SAILTHRU_PLUGIN_PATH . 'classes/class-sailthru-meta-box.php';
require_once SAILTHRU_PLUGIN_PATH . 'classes/class-sailthru-scout.php';
require_once SAILTHRU_PLUGIN_PATH . 'classes/class-sailthru-mailer.php';

/*
 * Get Sailthru Custom Subscribe Fields classes
 */
require_once SAILTHRU_PLUGIN_PATH . 'classes/class-sailthru-subscribe-fields.php';

/*
 * Sailthru for WordPress admin view settings and registrations.
 */
require_once SAILTHRU_PLUGIN_PATH . 'views/admin.functions.php';

/*
 * Grab and activate the Sailthru Subscribe widget.
 */
require_once SAILTHRU_PLUGIN_PATH . 'widget.subscribe.php';

/*
 * Add the email handler
 */
require_once SAILTHRU_PLUGIN_PATH . 'sailthru_mail.php';


/*
 * Horizon handles the foundational actions like adding menus, meta tags,
 * and javascript files.
 */
if ( class_exists( 'Sailthru_Horizon' ) ) {

	$sailthru_horizon = new Sailthru_Horizon();

	// add a record in the db to keep track of the version of this plugin
	if ( false === get_option( 'sailthru_plugin_version' ) ) {
		add_option( 'sailthru_plugin_version', SAILTHRU_PLUGIN_VERSION );
	} else {
		update_option( 'sailthru_plugin_version', SAILTHRU_PLUGIN_VERSION );
	} // end if

	if ( class_exists( 'Sailthru_Scout' ) ) {
		$sailthru_scout = new Sailthru_Scout();
	}
}



/**
 * Register hooks that are fired when the plugin is activated,
 * deactivated, and uninstalled, respectively.
 */
register_activation_hook( __FILE__, array( 'Sailthru_Horizon', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Sailthru_Horizon', 'deactivate' ) );
register_uninstall_hook( __FILE__, array( 'Sailthru_Horizon', 'uninstall' ) );



// This is called from sailthru_setup_handler()
function sailthru_create_wordpress_template() {

	$wordpress_template = 'WordPress Template';

	if ( sailthru_verify_setup() ) {

		$sailthru   = get_option( 'sailthru_setup_options' );
		$api_key    = $sailthru['sailthru_api_key'];
		$api_secret = $sailthru['sailthru_api_secret'];

		$client = new WP_Sailthru_Client( $api_key, $api_secret );

		// Find out if 'WordPress Template' already exists
		$template_exists = false;

		try {
			if ( $client ) {
				// if we try to grab a template by name that doesn't exist
				// the world blows up. Grab them all and loop through
				$response  = $client->getTemplates();
				$templates = $response['templates'];
				foreach ( $templates as $template ) {
					foreach ( $template as $key => $value ) {
						if ( 'name' === $key ) {
							if ( $value === $wordpress_template ) {
								$template_exists = true;
							}
						}
					}
				}
			}
		} catch ( Sailthru_Client_Exception $e ) {
			//silently fail
			return;
		}

		// the Template doesn't exist, so we need to create it.
		if ( false === $template_exists ) {

			try {
				if ( $client ) {
					$client->saveTemplate(
						'wordpress-template',
						array(
							'name'         => $wordpress_template,
							'subject'      => '{subject}',
							'content_html' => "<html>\n<head>\n<body>\n{body}\n</body>\n</html>",
						)
					);
				}
			} catch ( Sailthru_Client_Exception $e ) {
				//silently fail
				return;
			}
		}
	}

}


// Add and action to handle when a user logs in.
add_action( 'wp_login', 'sailthru_user_login', 10, 2 );


function sailthru_user_login( $user_login, $user ) {
	if ( get_option( 'sailthru_setup_complete' ) ) {
		$sailthru   = get_option( 'sailthru_setup_options' );
		$api_key    = $sailthru['sailthru_api_key'];
		$api_secret = $sailthru['sailthru_api_secret'];

		$client = new WP_Sailthru_Client( $api_key, $api_secret );

		$id      = $user->user_email;
		$options = array(
			'login'  => array(
				'user_agent' => sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ),
				'key'        => 'email',
				'ip'         => sanitize_text_field( $_SERVER['SERVER_ADDR'] ),
				'site'       => sanitize_text_field ($_SERVER['HTTP_HOST'] ) ,
			),
			'fields' => array( 'keys' => 1 ),
		);

		try {
			if ( $client ) {
				$st = $client->saveUser( $id, $options );
			}
		} catch ( Sailthru_Client_Exception $e ) {
			 //silently fail.
			 return;
		}
	}
}


if ( ! function_exists( 'write_log' ) ) {
	function write_log( $log ) {
		if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {

			if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
			} else {
				error_log( $log );
			}
		}
	}
}


