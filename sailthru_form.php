<?php

class sailthru_form {
	
	var $id;
	var $name;
	var $mailing_lists = array();
	var $content;
	
	function sailthru_form($id = false) {
		
		$sailthru_forms = self::get_forms();
		
		if($id && isset($sailthru_forms[$id])) {
			$this->id				= 	$sailthru_forms[$id]->id;
			$this->name 			= 	$sailthru_forms[$id]->name;
			$this->mailing_lists 	=	$sailthru_forms[$id]->mailing_lists;
			$this->content			=	$sailthru_forms[$id]->content;
		}
		elseif($id) {
			throw new Exception('sailthru_form constructor passed invalid form id');
		}
	}
	
	function required_fields() {
		$fields = array('email' => 'You must enter an email address.');
		
		if(preg_match('/\[fname\*/i', $this->content)) {
			$fields['fname'] = 'You must enter a first name.';
		}
		
		if(preg_match('/\[lname\*/i', $this->content)) {
			$fields['lname'] = 'You must enter a last name.';
		}
		
		return $fields;
	}
	
	function has_lists() {
		return preg_match('/\[optin-[\d]+/', $this->content);
	}
	
	function get_form() {
		$form = $this->content;

		//email fields
		$form = preg_replace('/\[email( "([^"]+)")?\]/i', '<input id="sailthru-email-' . $this->id . '" type="text" class="required $2" />', $form);
		
		//first name
		$form = preg_replace('/\[fname(\*?)( "([^"]+)")?\]/i', '<input id="sailthru-fname-' . $this->id . '" type="text" class="$1$3" />', $form);
		
		//last name
		$form = preg_replace('/\[lname(\*?)( "([^"]+)")?\]/i', '<input id="sailthru-lname-' . $this->id . '" type="text" class="$1$3" />', $form);
		
		//submit
		$form = preg_replace('/\[submit "([^"]+)"( "([^"]+)")?\]/i', '<input id="sailthru-submit-' . $this->id . '" onclick="return false;" type="submit" class="$3" value="$1" />&nbsp;&nbsp;&nbsp;<img id="sailthru_loader-' . $this->id . '" src="' . Sailthru::get_plugin_url() . '/loading.gif" style="display: none;"/>', $form);
		
		//each list
		$lists = sailthru_form::get_all_lists();
		foreach ($lists as $id => $list) {
	        $form = preg_replace("/\[optin-{$id}( \"([^\"]+)\")?\]/i", "<label class=\"$2\"><input checked=\"checked\" type=\"checkbox\" value=\"true\" id=\"sailthru-optin-{$this->id}-{$id}\" /> {$list}</label>", $form);
	    }
		
		//replace the asterisks with "required"
		//remove empty class=""
		$form = str_replace(
		    array(
		        'class="*', 
		        ' class=""'
		    ),
		    array(
		        'class="required ', 
		        ''
		    ),
		    $form
		);
		
		$form .= '<input type="hidden" id="sailthru_plugin_url" value="' . Sailthru::get_plugin_path() . '" />';
		
		$form = "<form id=\"sailthru-{$this->id}\" class=\"sailthru-form\">{$form}</form>";
	
		return $form;
	}
	
	function get_form_code() {
		if($this->id) {
			return "[sailthru {$this->id}]";
		}
		return 'You must save this form before a code can be generated';
	}
	
	function get_all_lists() {
		require_once('client/requires.php');
		$client = new Sailthru_Client(get_option('sailthru_api_key'), get_option('sailthru_secret'));
		
		//@TODO:  need to do an API call here
	        //return array('426' => 'test');
		//return array('pheonix', 'seed');

		$lists = $client->apiGet('list',false);
		$list_names = array();
		
		foreach ($lists as $list) {
			if (is_array($list)) {
				foreach ($list as $list_name) {
					$list_names[] = $list_name['name'];
				}
			}
		}
		return $list_names;
		
	}
	
	function save() {
		$sailthru_forms = self::get_forms();
		
		if(!$this->id) {
			$this->id = self::get_new_id();
		}
		
		$sailthru_forms[$this->id] = $this;
		update_option('sailthru_forms', $sailthru_forms);
		return $sailthru_forms;
		
	}
	
	function delete_form($form_id) {
		$sailthru_forms = self::get_forms();
		unset($sailthru_forms[$form_id]);
		update_option('sailthru_forms', $sailthru_forms);
		return $sailthru_forms;
	}
	
	function get_new_id() {
		$id = get_option('sailthru_id');
		$id++;
		update_option('sailthru_id', $id);
		return $id;
	}
	
	function get_forms() {
		
		$sailthru_forms = get_option('sailthru_forms');

		if(!isset($sailthru_forms) || !is_array($sailthru_forms)) {
			$sailthru_forms = array();
		}
		
		update_option('sailthru_forms', $sailthru_forms);
		return $sailthru_forms;
	}	
}