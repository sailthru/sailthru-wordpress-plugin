# Changelog


## v3.0.6-dev (2016-07-15)

Version 3.0.6 of the plugin adds support for Sailthru's content API and additional support for our personalization engine JavaScript as well as bug fixes and improvements. 

#### Added Content API support

Each time a post is added or saved it is now pushed toSailthru"s using the Content API. Any additional custom fields produced by a WordPress plugin are passed as vars. The post type is also passed a a var so that you can filter data feeds based on post type as well as tags. 

#### Added option to choose Javascript Library

Customers can choose between Personalize JS and Horizon JS versions. During the sunsetting of Horizon JS in 2016 we will provide options for which version of our JavaScript library that will be available. 

#### Sailthru Subscription Widget
Fixed a bug whereby smart lists were available in the subscription widget. Changed Sailthru subscription widget to only use natural lists as subscription option as Smart Lists cannot be posted to.

#### Updated Sailthru Client Library 
Added an integration parameter to all API calls to help Salthru support identify the WordPress plugin version to help provide faster responses and initial investigations.
