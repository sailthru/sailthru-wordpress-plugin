<?php
/**
 * @package Sailthru
 * @author Jon Tascher
 * @version 1.1
 */
/*
Plugin Name: Sailthru
Plugin URI: http://www.sailthru.com
Description: Intergrate Sailthru API functionality into your WordPress blog.
Author: Jon Tascher
Version: 1.1
Author URI: http://www.jontascher.com
*/
ob_start();

if(!class_exists('Sailthru')) {
	
	require_once('sailthru_form.php');
	
	class Sailthru {
	
		function Sailthru() {
			
			$this->php4_json();
			
			register_activation_hook(__FILE__, array(&$this, 'activate'));
			
			//actions
			add_action('admin_menu', array(&$this, 'create_options_menu'));
			add_action('admin_print_scripts-settings_page_sailthru', array(&$this, 'admin_header'));
			add_action('wp_print_scripts', array(&$this, 'public_header'));
			add_action('wp_ajax_sailthru_save_datafeed', array(&$this, 'save_datafeed'));
			add_action('wp_ajax_sailthru_delete_datafeed', array(&$this, 'delete_datafeed'));
			add_action('wp_ajax_sailthru_get_datafeed_html', array(&$this, 'get_datafeed_html'));
			add_action('wp_ajax_sailthru_get_template_info', array(&$this, 'get_template_info'));
			
			//filters
			add_filter('the_content', array(&$this, 'content_filter'));
			add_filter('get_footer', array(&$this, 'footer_filter'));
			add_filter('widget_text', array(&$this, 'content_filter'));

			$sailthru_forms = sailthru_form::get_forms();
			
		}
		
		function public_header() {
			wp_enqueue_script('sailthru', $this->get_plugin_url() . '/js/sailthru_public.js', array('jquery'));
		}
		
		function footer_filter() {
			if ((is_single() /* || is_page() */) && $subdomain = get_option('horizon_subdomain')) {
				global $post;
				$tags = get_the_tags($post->ID);
				
				$t = array();
				if ($tags) {
					foreach ($tags as $tag) {
						$t[] = $tag->name;
					}
				}
				$tags = json_encode($t);
				
				echo <<<EOT
<!-- Sailthru Horizon --> 
<script type="text/javascript">sailthru_horizon = { domain: '{$subdomain}', tag: {$tags}}</script> 
<script type="text/javascript" src="http://cdn.sailthru.com/js/horizon.js"></script>
EOT;
			}
		}
		
		function content_filter($content) {
			//check to see if there are any sailthru tags in the post
			if(preg_match('/\[sailthru ([\d]+)\]/i', $content, $matches)) {
				
				$sailthru_forms = sailthru_form::get_forms();
				$form_id = $matches[1];
				
				if(isset($sailthru_forms[$form_id])) {
					try {
						$form = new sailthru_form($form_id);
					}
					catch(Exception $e) {
					}
				}
				
				if(is_object($form)) {
					return preg_replace('/\[sailthru ([\d]+)\]/i', $form->get_form(), $content);
				}
			}
			return $content;
		}
		
		function activate() {
			update_option('sailthru_id', 1);
			// update_option('sailthru_forms', array());
		}
		
		function admin_header() {
			wp_enqueue_script('sailthru', $this->get_plugin_url() . '/js/sailthru_admin.js', array('jquery'));
		}
		
		function get_plugin_url() {
			return plugins_url(plugin_basename(dirname(__FILE__)));
		}
		
		function get_plugin_path() {
			return preg_replace('|^http://[^/].*?/|i', '/', plugins_url(plugin_basename(dirname(__FILE__))));
		}
		
		function create_options_menu() {
			add_options_page('Sailthru Options', 'Sailthru', 'edit_pages', 'sailthru', array(&$this, 'options_page'));
		}
		
		function options_page() {
			include('sailthru_options.php');
		}
		
		function get_datafeed_html() {
			
			require_once('client/requires.php');
			$client = new Sailthru_Client(get_option('sailthru_api_key'), get_option('sailthru_secret'));
			echo json_encode($client->sailthru_eval($_POST['datafeed'], $_POST['template']));
			die();
		}
		
		function delete_datafeed() {
			$datafeeds = get_option('sailthru_datafeeds');
			
			if (isset($_POST['name']) && $_POST['name'] && isset($datafeeds[$_POST['name']])) {
				unset($datafeeds[$_POST['name']]);
			}
			update_option('sailthru_datafeeds', $datafeeds);
			echo json_encode($datafeeds);
			die();
		}
		
		function save_datafeed() {
			$datafeeds = get_option('sailthru_datafeeds');
			$datafeeds[$_POST['name']] = $_POST['url'];
			
			if (isset($_POST['del']) && $_POST['del'] && isset($datafeeds[$_POST['del']])) {
				unset($datafeeds[$_POST['del']]);
			}
			update_option('sailthru_datafeeds', $datafeeds);
			
			echo json_encode($datafeeds);
			die();
		}
		
        function get_template_info() {
            require_once('client/requires.php');
            $client = new Sailthru_Client(get_option('sailthru_api_key'), get_option('sailthru_secret'));
			$res = $client->getTemplate($_POST['template']);
            echo json_encode($res);
            die();
        }
		
		function get_template_list() {
			
			require_once('client/requires.php');
			$client = new Sailthru_Client(get_option('sailthru_api_key'), get_option('sailthru_secret'));
			$res = $client->getTemplate('');

			if (isset($res['templates']) && $res['templates']) {
			    $return = array();
                foreach ($res['templates'] as $template) {
                    $return[] = $template['name'];
                }
                return $return;
			} else {
			    return array();
		    }
		}
		
		function get_sailthru_form($form_id) {
			$sailthru_forms = sailthru_form::get_forms();
			if(isset($sailthru_forms[$form_id])) {
				$form = new sailthru_form($form_id);
				return $form->get_form();
			}
			return '';
		}
		
		function php4_json() {
			if(!function_exists('json_encode')) {
				require_once('json.php');
				function json_encode($data) {
					$json = new Services_JSON();
					return($json->encode($data));
				}
			}
			if(!function_exists('json_decode')) {
				require_once('json.php');
				function json_decode($data) {
					$json = new Services_JSON();
					return($json->decode($data));
				}
			}
		}
	}
}

if(class_exists('Sailthru')) {
	$sailthru = new Sailthru();
}

if(get_option('sailthru_all_email') && !function_exists('wp_mail')) {

	function wp_mail($to, $subject, $message, $headers = '', $attachments = array()) {

		extract(apply_filters('wp_mail', compact('to', 'subject', 'message', 'headers', 'attachments')));
		
		//can sailthru handle attachments?  I don't think it can at the moment..
		if (!is_array($attachments)) {
			$attachments = explode("\n", $attachments);
		}
		
		$replacements = array(
			'subject' => $subject,
			'body' => $message
		);
		
		require_once('client/requires.php');
		$client = new Sailthru_Client(get_option('sailthru_api_key'), get_option('sailthru_secret'));
		$r = $client->send('WordPress Template', $to, $replacements, array());
        return true;
	}
}