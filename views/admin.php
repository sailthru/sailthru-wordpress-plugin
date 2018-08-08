<!-- Create a header in the default WordPress 'wrap' container -->
<div class="wrap" id="sailthru-admin">
	<div id="icon-sailthru" class="icon32"></div>
	<h2><?php esc_html_e( 'Sailthru for WordPress', 'sailthru-for-wordpress' ); ?></h2>
	<?php
	if ( isset( $_GET['page'] ) ) {
		$active_tab = sanitize_text_field( $_GET['page'] );
	} elseif ( 'concierge_configuration_page' === $active_tab ) {
		$active_tab = 'concierge_configuration_page';
	} elseif ( 'scout_configuration_page' === $active_tab ) {
		$active_tab = 'scout_configuration_page';
	} elseif ( 'settings_configuration_page' === $active_tab  ) {
		$active_tab = 'settings_configuration_page';
	} elseif ( 'customforms_configuration_page' === $active_tab ) {
		$active_tab = 'customforms_configuration_page';
	} elseif ( 'sailthru_content_settings' === $active_tab ) {
		$active_tab = 'sailthru_content_settings';
	} else {
		$active_tab = 'customforms_configuration_page';
	} // End if/else.

	// Display errors from form submissions at the top.
	settings_errors();

	// Sailthru setup options.
	//$sailthru = get_option( 'sailthru_setup_options' );

	// Setup.
	$setup = get_option( 'sailthru_setup_options' );


	//Set defaults for setup to be false
	$show_concierge = false;
	$show_scout     = false;
	$list_signup    = false;


	if ( sailthru_verify_setup() ) {
		$list_signup = true;

		if ( isset( $setup['sailthru_js_type'] ) && 'horizon_js' === $setup['sailthru_js_type'] ) {
			$show_concierge = true;
			$show_scout     = true;
		}
	} else {
		$list_signup = false;
	}

?>

		<form method="post" action="options.php">

		<?php
		if ( 'sailthru_configuration_page' === $active_tab ) {
			require SAILTHRU_PLUGIN_PATH . 'views/settings.html.php';
		} elseif ( 'concierge_configuration_page' === $active_tab  ) {
			settings_fields( 'sailthru_concierge_options' );
			do_settings_sections( 'sailthru_concierge_options' );
		} elseif ( 'scout_configuration_page' === $active_tab ) {
			settings_fields( 'sailthru_scout_options' );
			do_settings_sections( 'sailthru_scout_options' );
		} elseif ( 'custom_fields_configuration_page' === $active_tab ) {
			settings_fields( 'sailthru_forms_options' );
			do_settings_sections( 'sailthru_forms_options' );
			echo '</div>'; // Ends the half column begun in delete_field().
		} elseif ( 'sailthru_content_settings' === $active_tab ) {
			settings_fields( 'sailthru_forms_options' );
			do_settings_sections( 'sailthru_content_options' );
		// Show welcome page.
		} else {
			require SAILTHRU_PLUGIN_PATH . 'views/settings.html.php';
		} // End if/else.

		submit_button();
		echo '</form>';


		?>


	</div>
