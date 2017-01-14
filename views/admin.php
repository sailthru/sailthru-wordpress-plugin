<!-- Create a header in the default WordPress 'wrap' container -->
<div class="wrap" id="sailthru-admin">
    <div id="icon-sailthru" class="icon32"></div>
    <h2><?php _e( 'Sailthru for WordPress', 'sailthru-for-wordpress' ); ?></h2>

    <?php
//Check to see if everything is set up correctly
$verify_setup = sailthru_verify_setup();

if ( isset( $_GET[ 'page' ] ) ) {
    $active_tab = $_GET[ 'page' ];
} else if ( $active_tab == 'concierge_configuration_page' ) {
        $active_tab = 'concierge_configuration_page';
    } else if ( $active_tab == 'scout_configuration_page' ) {
        $active_tab = 'scout_configuration_page';
    } else if ( $active_tab == 'settings_configuration_page' ) {
        $active_tab = 'settings_configuration_page';
    } else if ( $active_tab == 'customforms_configuration_page' ) {
        $active_tab = 'customforms_configuration_page';
    } else {
    $active_tab = 'customforms_configuration_page';
} // End if/else.

// Display errors from form submissions at the top.
settings_errors();

// Sailthru setup options.
$sailthru = get_option( 'sailthru_setup_options' );

// Setup.
$setup = get_option( 'sailthru_setup_options' );

/*
         * This is pretty important.
         * If we're done setting up the user. Set this flag so
         * we can start injecting our js to the public side.
         *
         * This also indicates that sitewide options have
         * been chosen. So that means it's ok to start
         * overriding WP email.
         */
if ( false == get_option( 'sailthru_setup_complete' ) ) {
    add_option( 'sailthru_setup_complete', 1 );
} // end if


// check to see what JS version is implemented.
if ( isset( $setup['sailthru_js_type'] ) && $setup['sailthru_js_type'] == 'personalize_js' ) {
    $show_concierge = false;
    $show_scout = false;
} else {
    $show_concierge = true;
    $show_scout = true;
}
?>

    <h2 class="nav-tab-wrapper">
            <a href="?page=sailthru_configuration_page" class="nav-tab <?php echo $active_tab == 'sailthru_configuration_page' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Configuration', 'sailthru-for-wordpress' ); ?></a>

            <?php if ( $show_concierge ): ?>
            <a href="?page=concierge_configuration_page" class="nav-tab <?php echo $active_tab == 'concierge_configuration_page' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Concierge', 'sailthru-for-wordpress' ); ?></a>
            <?php endif; ?>
            <?php if ( $show_scout ): ?>
            <a href="?page=scout_configuration_page" class="nav-tab <?php echo $active_tab == 'scout_configuration_page' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Scout', 'sailthru-for-wordpress' ); ?></a>
            <?php endif; ?>
            <a href="?page=custom_fields_configuration_page" class="nav-tab <?php echo $active_tab == 'custom_fields_configuration_page' ? 'nav-tab-active' : ''; ?>"><?php _e( 'List Signup Options', 'sailthru-for-wordpress' ); ?></a>
        </h2>

        <form method="post" action="options.php">

        <?php
        if ( $active_tab == 'sailthru_configuration_page' ) {
            
            require SAILTHRU_PLUGIN_PATH . 'views/settings.html.php';

        }  
        elseif ( $active_tab == 'concierge_configuration_page' ) {
            
            settings_fields( 'sailthru_concierge_options' );
            do_settings_sections( 'sailthru_concierge_options' );

        } elseif ( $active_tab == 'scout_configuration_page' ) {
            
            settings_fields( 'sailthru_scout_options' );
            do_settings_sections( 'sailthru_scout_options' );

        } elseif ( $active_tab == 'custom_fields_configuration_page' ) {
            
            settings_fields( 'sailthru_forms_options' );
            do_settings_sections( 'sailthru_forms_options' );
            echo '</div>'; // Ends the half column begun in delete_field().

            // Show welcome page.
        } else {
            require SAILTHRU_PLUGIN_PATH . 'views/settings.html.php';

        } // End if/else.

        echo '<div style="clear:both;">';
        submit_button();
        echo '</div>';
        echo '</form>'

        ?>


    </div>
