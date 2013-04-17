<?php
/*
Plugin Name: Sailthru Subscribe Widget for Wordpress
Plugin URI: http://sailthru.com
Description: Sailthru's plugin for Wordpress makes it easy to get up and running quickly on the Sailthru platform.
Version: 3.0
Author: Sailthru
Author URI: http://sailthru.com
Author Email: nick@sailthru.com
Text Domain: sailthru-subscribe-us
Domain Path: /lang/
Network: false
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Copyright 2012 Sailthru

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

-------------------------------------------------------------
The Sailthru Client Libraries in the lib folder are released under the
MIT License. Full details of the license for the Sailthru PHP5 Client library
is availlable in the document lib/MIT-LICENSE.txt
*/

if( ! defined('SAILTHRU_PLUGIN_PATH') )
	define( 'SAILTHRU_PLUGIN_PATH', plugin_dir_path(__FILE__) );


class Sailthru_Subscribe extends WP_Widget {

	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/

	/**
	 * Instantiates the widget,
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	public function __construct() {

		// load plugin text domain
		add_action( 'init', array( $this, 'load_widget_text_domain' ) );


		parent::__construct(
			'sailthru-subscribe-id',
			__( 'Sailthru Subscribe Widget', 'sailthru-for-wordpress' ),
			array(
				'classname'		=>	'Sailthru_Subscribe',
				'description'	=>	__( 'A widget to allow your visitors to subscirbe to your Sailthru lists.', 'sailthru-for-wordpress' )
			)
		);


		// Register admin styles and scripts
		add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );

		// Register site styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_scripts' ) );

	} // end constructor

	/*--------------------------------------------------*/
	/* Widget API Functions
	/*--------------------------------------------------*/

	/**
	 * Outputs the content of the widget.
	 *
	 * @param	array	args		The array of form elements
	 * @param	array	instance	The current instance of the widget
	 */
	public function widget( $args, $instance ) {

		extract( $args, EXTR_SKIP );

		echo $before_widget;

		include( plugin_dir_path( __FILE__ ) . '/views/widget.display.php' );

		echo $after_widget;

	} // end widget

	/**
	 * Processes the widget's options to be saved.
	 *
	 * @param	array	new_instance	The previous instance of values before the update.
	 * @param	array	old_instance	The new instance of values to be generated via the update.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		// We're handling everything via Ajax, so there's nothing to see here.
		return $new_instance;

	} // end widget

	/**
	 * Generates the administration form for the widget.
	 *
	 * @param	array	instance	The array of keys and values for the widget.
	 */
	public function form( $instance ) {

		// Default values for a widget instance
        $instance = wp_parse_args(
        	(array) $instance, array(
                'title' => '',
                'show_first_name' => '',
                'show_last_name' => '',
                'sailthru_list' => array('')
            )
        );

        $title = esc_attr($instance['title']);
        $show_first_name = esc_attr($instance['show_first_name']);
        $show_last_name = esc_attr($instance['show_last_name']);
        $sailthru_list = esc_attr($instance['sailthru_list']);



		// Display the admin form
		include( plugin_dir_path(__FILE__) . '/views/widget.admin.php' );

	} // end form

	/*--------------------------------------------------*/
	/* Public Functions
	/*--------------------------------------------------*/

	/**
	 * Loads the Widget's text domain for localization and translation.
	 */
	public function load_widget_text_domain() {

		load_plugin_textdomain( 'sailthru-for-wordpress', false, plugin_dir_path( __FILE__ ) . '/lang/' );

	} // end load_widget_text_domain


	public function activate( $network_wide ) {
		// we depend on horizon to get the proper API calls.
		// Try to activate it.
		$dependent = 'sailthru-for-wordpress/plugin.php';
		if( !is_plugin_active($dependent) ){
		 	add_action('update_option_active_plugins', array('Sailthru_Subscribe', 'sailthru_activate_dependent_plugin') );
		}
	} // end activate

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin uses
	 * 			"Network Activate" action, false if WPMU is disabled
	 * 			or plugin is activated on an individual blog
	 */
	public function deactivate( $network_wide ) {
		// nothing to see there
	} // end deactivate


	/**
	 * Activate the Sailthru Horizon Plugin when the Sailthru
	 * Subscribe is Activated.
	 */
	public function sailthru_activate_dependent_plugin() {
		//echo '<h1>HI I"M OVER HERE AGAIN.</h1>';
		//die();

		$dependent = 'sailthru-for-wordpress/plugin.php';
		activate_plugins($dependent);

	}


	/**
	 * Registers and enqueues admin-specific styles.
	 */
	public function register_admin_styles() {

		wp_enqueue_style( 'sailthru-subscribe-admin-styles', plugins_url( 'sailthru-for-wordpress/css/admin.widget.css' ) );

	} // end register_admin_styles

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 */
	public function register_admin_scripts() {

		wp_enqueue_script( 'sailthru-subscribe-admin-script', plugins_url( 'sailthru-for-wordpress/js/admin.widget.js' ), array('jquery') );

	} // end register_admin_scripts

	/**
	 * Registers and enqueues widget-specific styles.
	 */
	public function register_widget_styles() {

		wp_enqueue_style( 'sailthru-subscribe-widget-styles', plugins_url( 'sailthru-for-wordpress/css/widget.css' ) );

	} // end register_widget_styles

	/**
	 * Registers and enqueues widget-specific scripts.
	 */
	public function register_widget_scripts() {

		wp_enqueue_script( 'sailthru-subscribe-script', plugins_url( 'sailthru-for-wordpress/js/widget.js' ), array('jquery') );

	} // end register_widget_scripts

} // end class

// Register activation hook
register_activation_hook( __FILE__, array( 'Sailthru_Subscribe', 'activate' ) );

// Register a new widget with Wordpress
add_action( 'widgets_init', create_function( '', 'register_widget("Sailthru_Subscribe");' ) );
