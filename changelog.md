# Changelog
## v4.3.8 (2024-07-26)
Custom fields: Radio buttons, checkboxes, hidden fields, and select options not working so removed from dropdown.

## v4.3.7 (2024-06-06)
Resolved issue with quotes not encoding properly

## v4.3.6 (2024-06-06)
Quotes encoding issue fixed

## v4.3.5 (2024-04-08)
Removed commented code inside the if condition

## v4.3.4 (2024-04-08)
Image URL encoding issue fixed 

## v4.3.3 (2024-03-08)
Fixed em dash character in image file name

## v4.3.2 (2024-02-23)
Added for in label and id in input field

## v4.3.1 (2023-07-07)
Added fix for ad-blocker

## v4.3.0 (2021-09-01)
Added filter for content vars

## v4.2.0 (2021-07-06)
Added post object to sailthru_horizon_meta_tags filter

## v4.1.1 (2021-05-25)
Fixed Bulk Edit not propagating category and Wordpress tags properly

## v4.1.0 (2021-03-23)
Fixed JQUery queries without scope hidding non-Sailthru elements
Added delete from Sailthru when item is sent to trash in Wordpress

## v4.0.3 (2021-01-20)
Fixed bug where an unset Welcome Template breaks the signup widget

## v4.0.2 (2020-12-17)
Updated Wordpress version tested through value

## v4.0.1 (2020-12-03)
Fixed warning where post_type not set properly

## v4.0.0 (2020-10-14)
Supports Wordpress version 5.5.
This version isn't backwards compatible with older Wordpress versions.

## v3.6.3 (2021-01-20)
Fixed bug where an unset Welcome Template breaks the signup widget

## v3.6.2 (2020-12-03)
Fixed warning where post_type not set properly

## v3.6.1 (2020-10-14)
Fixed bug where double opt-in option adds users to list immediately

## v3.6.0 (2020-08-04)
Added option for users to have Sailthru Subscription widget title to disappear after user sign-up
Changed code which produced some errors and warnings in Wordpress PHP Codesniffer

## v3.5.0 (2020-07-15)
Added option for users to change Spider value in Content API calls (defaults to enabled)
Renamed Global Vars to Custom Fields and updated helper text on Content Settings page

## v3.4.4 (2020-07-01)
Fixed bug where “Scout from Sailthru” page was created for all sites, rather than just Scout-enabled sites.

## v3.4.3 (2020-04-07)
Fixed bug where Sailthru onsite JS would sometimes fail to initialize due to asynchronous loading of scripts

## v3.4.2 (2020-03-26)
Fixed bug where onsite JS taking too long to load would prevent some pages from loading

## v.3.4.1 (2020-01-18)
Fixed bug where HTTPS urls were sent to the Sailthru Content API as HTTP

## v.3.4.0 (2020-01-09)
Added the option to reset user optout status on newsletter subscription. Appears as a checkbox in the footer widget and as the following option in the shortcode:
```
[sailthru_widget ... reset_optout_status="true"]
```

Enabling this option will change the user's optout status to "valid" in Sailthru by passing the `optout_email=none` option in the API.

## v3.3.1 (2019-11-18)
VIP: Clear cache because of 5.3 changes handling for user_activation_key

## v3.3.0 (2018-11-12)
Address codestyle issues for VIP

## v3.3-beta (2018-06-18)
Added content settings section to the setup and migrated some horizon code to a common content class. 

* Add abilility to combine WordPress tags with Sailthru interest tags via a UI setting
* Added abilility to combine categories with Sailthru interest tags via a UI setting
* Added ability to add any available taxonomy to Sailthru tags via the UI
* Added a global whitelist of vars to be included in content api posts
* Added a global tag option so a tag can be added to every post. 
* Added ability to turn on/off Content API syncing via the UI. 

## v3.2.2 (2018-06-18)
Updated sailthru_horizon_meta_tags filter to apply to Content API calls. 

## v3.2.1 (2018-04-11)
Added a filter to allow customers to override API verification in the setup process. The goal of this feature is to mitigate some edge cases where the setup process returns a payload to WordPress VIP creates an error on their platform. Most customers using this plugin are not affected. 
Changed ajaxurl used in widget to be namespaced to prevent collision.
Fixed issue whereby any error on a signup widget would render on all signup widgets on the page.
Added a filter to allow the localized js to be loaded in wp_footer()
Removed php 7.2 unsupported code. (Thanks srtfisher)
Added support for Page in Sailthru meta box. 
Extended timeout for VIP API calls to 3 seconds. 
Squished a few more bugs. 


## v3.2.0 (2018-02-18)
Fixed bug with rendering of checkboxes on widget subscription.
Fixed issue with validation of email addresses on subscription widget. 
Fixed PHP warnings on newly created instance on subscription widget if debugging is turned on. 


## v3.1 (2017-12-10)
Added ability to select JS versions in the setup
Added new flag to check for API readiness. Must re-save keys ato add flag
Added a check to verify if SPM is enabled on Sailthru customer account
Subscribe widget now supports instance level source var
Subscribe widget now can add an Event API call when converting
Non VIP customers can create a WordPress user when new users subscribe via the subscribe widget
Fixed a number of bugs, and updated coding standards to WordPress VIP
Concierge and Scout disabled when Sailthru Script Tag is enabled
Added support for latest SPM and Sailthru Script Tag. 

## v3.0.8 (2017-10--24)
Fixed a bug with deployment of Sailthru Script Tag where Sailthru functions are not available due to incorrect Setup of Sailthru.init
Added filters to allow customers to customize rendering of Script Tag 

## v3.0.7 (2017-10--5)
Added support for latest version of Sailthru Script Tag and some big fixes. 

## v3.0.6 (2016-07-15)

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
