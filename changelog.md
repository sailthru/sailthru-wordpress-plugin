# Changelog


## v3.0.6-dev (2016-07-15)

Version 3.0.6 of the plugin adds support for Sailthru's content API and additional support for our personalization engine JavaScript as well as bug fixes and improvements. 

#### Added Content API support

Each time a post is added or saved it is now pushed to Sailthru's Content API. Any additional custom fields produced by a WordPress plugin are passed as vars. The post type is also passed a a var so that you can filter data feeds based on post type as well as tags. 

Content API calls can be disabled and the Spidering process used by applying the filter ```sailthru_content_api_enable``` in your functions.php file with a return value of ```false```

#### Added option to choose Javascript Library

Customers can choose between Personalize JS and Horizon JS versions. During the sunsetting of Horizon JS in 2016 we will provide options for which version of our JavaScript library that will be available. 

#### Sailthru Subscription Widget
Fixed a bug whereby smart lists were available in the subscription widget. Changed Sailthru subscription widget to only use natural lists as subscription option as Smart Lists cannot be posted to.

#### Added setHorizonCookie to newsletter singup widget

Added a call to setHorizonCookie that will be called upon form submission of the Sailthru subscribe widget. This should drop the sailthru_hid cookie.


#### Updated Sailthru Client Library 
Added an integration parameter to all API calls to help Salthru support identify the WordPress plugin version to help provide faster responses and initial investigations.
