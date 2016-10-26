<?php

/***
This file is called by vip-init and can be used to run code that is specific to the VIP platform,
for more information about developing for the VIP platform check out

http://vip.wordpress.com/documentation/development-environment/#vip-init-php
*/

// Don't allow Sailthru to override wp_mail on the VIP platform
add_filter( 'pre_option_sailthru_override_wp_mail', '__return_false', 99999 ); // This should never happen

// Don't track logins on the VIP platform. Change made at the request of Automattic
remove_action( 'wp_login', 'sailthru_user_login', 10 );
