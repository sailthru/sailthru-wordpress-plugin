<?php

class Sailthru_Social Extends Sailthru_Client{

  var $debug = true;
  var $fields = array('keys' => 1);

  function __construct($api_key, $api_secret) {
    parent::__construct($api_key, $api_secret);
  }

  /*
  * Passes login data from social login services to the user API
  *
  */
  function social_login($social_data) {


    if ($this->debug) {
      // print '<pre>';
      var_dump($social_data);
      // print '</pre>';
    }

    $use_email_as_key = false;
    $vars = array();
    // find out what provider we're dealing with;
    $provider = $social_data->provider;

    // by default use an email address as the key
    if (isset($social_data->user->email)) {
      $data['key']  = 'email';
      $data['id'] = $social_data->user->email;
    } else {
      // need to do check to see if sharding is enabled on the account at setup
      switch ($provider) {
          case 'twitter':
            $data['key'] = 'twitter';
          break;

          case 'facebook':
            $data['key'] = 'facebook';
          break;

          default;
          // post details on another provider
          $data['key'] = 'extid';
      }
      $data['id'] =  $social_data->loginProviderUID;
    }

    $data['options'] = array(
                  'login' => array(
                  'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                  'key' => $data['key'],
                  'ip' => $_SERVER['REMOTE_ADDR'],
                  'site' => $_SERVER['HTTP_HOST'],
                  ),
                  'fields' => array('activity' => 1),
                  );

    $data['vars'] = array();
    $not_wanted = array('UID', 'UIDSig', 'UIDSignature');

    foreach ($social_data->user as $key => $val) {
        // remove Gigya identifiers and put them in a different var
        if (!in_array($key, $not_wanted)) {
          $data['vars'][$provider.'_'.$key] = $val;
        }

    }
    $sync = $this->sync_data($data);

    //var_dump($sync);

  }

  /*
  * POST the data to the Sailthru API using the user call
  */
  private function sync_data($data) {

    //var_dump($data);
    try {
      return $post = $this->apiPost('user', $data);
    } catch (Sailthru_Client_Exception $e) {
      // log the error somewhere
      return false;
    }

  }


}