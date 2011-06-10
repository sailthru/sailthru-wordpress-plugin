<?php
/**
 * @package Sailthru
 * @author Jon Tascher
 * @version 1.1
 */


$wp_load = realpath("../../../wp-load.php");
if(!file_exists($wp_load)) {
  $wp_config = realpath("../../../wp-config.php");
  if (!file_exists($wp_config)) {
      exit("Can't find wp-config.php or wp-load.php");
  } else {
      require_once($wp_config);
  }
} else {
  require_once($wp_load);
}

switch(@$_GET['action']) {
	case 'subscribe':
		
		try {
			$form = new sailthru_form($_POST['form_id']);
		}
		catch(Exception $e) {
			echo "{error: {$e->getMessage()}";
		}
		
		if(is_object($form)) {
			
			//validate again b/c this has only passed client side (tainted) validation
			$required_fields = $form->required_fields();
			
			$errors = array();
			foreach($required_fields as $field => $error) {
				if(!isset($_POST[$field]) || !$_POST[$field]) {
					$errors[] = $error;
				}
			}
			
			if(!preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $_POST['email'])) {
				$errors[] = 'You must enter a valid email address.';
			}
			
			$has_lists = $form->has_lists();
			$_POST['lists'] = explode(',', $_POST['lists']);  //annoying hack..
			if($has_lists && (!is_array($_POST['lists']) || !count($_POST['lists']))) {
				$errors[] = 'You must subscribe to at least one list.';
			}
			
			if(count($errors)) {
				echo json_encode($errors);
				die();
			}
			else {
				require_once('client/requires.php');
				$client = new Sailthru_Client(get_option('sailthru_api_key'), get_option('sailthru_secret'));
				
				$replacement_fields = array();
				if(isset($_POST['fname'])) {
					$replacement_fields['first_name'] = $_POST['fname'];
				}
				if(isset($_POST['lname'])) {
					$replacement_fields['last_name'] = $_POST['lname'];
				}

				$all_lists = $form->get_all_lists();
				$lists = array();
				if($has_lists) {
					foreach($all_lists as $id => $list_name) {
						$lists[$list_name] = in_array($id, $_POST['lists']) ? '1' : '0';
					}
				}
				
				$client->setEmail($_POST['email'], $replacement_fields, $lists);
				
				if ((bool)get_option('sailthru_welcome') && $template = get_option('sailthru_welcome_template')) {

					require_once('client/requires.php');
					$client = new Sailthru_Client(get_option('sailthru_api_key'), get_option('sailthru_secret'));
					$r = $client->send($template, $_POST['email'], $replacement_fields, array());
				}
				
				echo '{}';
			}
			
		}
		
		break;
}
die();