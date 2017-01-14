<?php

/**
 * Override the WordPress mailing system to use Sailthru
 *
 * Using the Sailthru plugin the customer can select specific templates to Send via Sailthru
 * if any of the options are selected we override the mail functionality but give the option
 * to only use Sailthru in explicit cases using the phpmailer_init action. When sailthru
 * is not declared as the sender the mailing will still continue to use the default options
 */


if ( get_option( 'sailthru_setup_complete' ) && !function_exists( 'wp_mail' ) ) {

	
	// configure the mail override function. 
	function sailthru_configure_mailer($phpmailer, $template = '') {
		$phpmailer->Mailer = 'sailthru';
		$phpmailer->template = $template;
		
	}


	// check each of the options for transactionals

	$sailthru = get_option( 'sailthru_setup_options' );
	$phpmailer = new SailthruMailer();

	$sailthru_template_fields = array( 'sailthru_setup_new_user_override_template',
		'sailthru_setup_password_reset_override_template',
		'sailthru_setup_email_template' );

	// look to see if any of the fields have a template selected and override the mailer.
	foreach ($sailthru_template_fields as $field) {

		if ( isset ( $sailthru[$field] ) && !empty( $sailthru[$field] ) ) {
			// We know a template is being used so include our wp_mail to override. 
			require_once SAILTHRU_PLUGIN_PATH . 'sailthru-wpmail.php';
			add_action( 'phpmailer_init', 'sailthru_configure_mailer', 1, 3 );
			break; 
		}

	}


		if ( isset ( $sailthru['sailthru_setup_new_user_override_template'] ) &&
					!empty( $sailthru['sailthru_setup_new_user_override_template'] ) ) {

				if ( !function_exists('wp_new_user_notification') ) {
				/**
				 * Pluggable - Email login credentials to a newly-registered user
				 *
				 * A new user registration notification is also sent to admin email.
				 *
				 * @since 2.0.0
				 *
				 * @param int    $user_id        User ID.
				 * @param string $plaintext_pass Optional. The user's plaintext password. Default empty.
				 */
					function wp_new_user_notification($user_id, $plaintext_pass = ''){

					    $user = get_userdata($user_id);
					    $sailthru = get_option( 'sailthru_setup_options' );
					    $template = $sailthru['sailthru_setup_new_user_override_template'];


					    // The blogname option is escaped with esc_html on the way into the database in sanitize_option
					    // we want to reverse this for the plain text arena of emails.
					    $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

					    $message  = sprintf(__('New user registration on your site %s:'), $blogname) . "\r\n\r\n";
					    $message .= sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n";
					    $message .= sprintf(__('E-mail: %s'), $user->user_email) . "\r\n";

					    @wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), $blogname), $message, '', '', $template);

					    if ( empty($plaintext_pass) )
					        return;

					    $message  = sprintf(__('Username: %s'), $user->user_login) . "\r\n";
					    $message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n";
					    $message .= wp_login_url() . "\r\n";
			    		
					    wp_mail($user->user_email, sprintf(__('[%s] Your username and password'), $blogname), $message, '', '', $template);

					}
				}
		}



	// // Template for default emails to be sent via Sailthru
	// if ( isset ( $sailthru['sailthru_setup_email_template'] ) &&
	// 	!empty( $sailthru['sailthru_setup_email_template'] ) ) {

	// 		//do_action( 'phpmailer_init', $phpmailer, 'default', $sailthru['sailthru_setup_email_template'] );

	// }


}
