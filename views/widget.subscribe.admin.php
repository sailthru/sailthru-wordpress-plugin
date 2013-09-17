	<div id="icon-sailthru" class="icon32"></div>
	<h2><?php _e( 'Sailthru Subscribe', 'sailthru-for-wordpress' ); ?></h2>

	<?php

		$sailthru = get_option('sailthru_setup_options');
		$customfields = get_option('sailthru_forms_options');

		if( !is_array($sailthru) )
		{

			echo '<p>Please return to the <a href="' . esc_url( menu_page_url( 'sailthru_configuration_menu', false ) ) . '">Sailthru Settings screen</a> and set up your API key and secret before setting up this widget.</p>';
			return;

		}
		$api_key = $sailthru['sailthru_api_key'];
		$api_secret = $sailthru['sailthru_api_secret'];

		//$client = new Sailthru_Client( $api_key, $api_secret );
		$client = new WP_Sailthru_Client( $api_key, $api_secret);
			try {
				if ($client) {
					$res = $client->getLists();
				}
			}
			catch (Sailthru_Client_Exception $e) {
				//silently fail
				return;
			}


			$lists = $res['lists'];

	?>


        <div id="<?php echo $this->get_field_id('title'); ?>_div" style="display: block;">
            <p>
            	<label for="<?php echo $this->get_field_id('title'); ?>">

            		<?php _e('Widget Title:'); ?>
            		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
            	</label>
            </p>
            <p>
			<?php
			if(get_option('sailthru_forms_key')){
				$key = get_option('sailthru_forms_key');
					for($i = 0;$i < $key;$i++){
					 	$field_key = $i + 1;
					 	if(!empty($customfields[$field_key]['sailthru_customfield_name'])){
						if(!empty($customfields[$field_key])){
						$name_stripped = preg_replace("/[^\da-z]/i", '_', $customfields[$field_key]['sailthru_customfield_name']);
						if(!empty($instance['show_'.$name_stripped.'_name'])){
							?>

						            <label for="<?php echo $this->get_field_id('show_'.$name_stripped.'_name'); ?>">
						            	<?php
						            	//takes out the rest of the name if it's a select / radio
										_e("Show '".$customfields[$field_key]['sailthru_customfield_name']."' field:");
										?>
										<input id="<?php echo $this->get_field_id('show_'.$name_stripped.'_name'); ?>" name="<?php echo $this->get_field_name('show_'.$name_stripped.'_name'); ?>" type="checkbox" <?php echo (($instance['show_'.$name_stripped.'_name']) ? ' checked' : ''); ?> />
										<br>
										Required: <input id="<?php echo $this->get_field_id('show_'.$name_stripped.'_required'); ?>" name="<?php echo $this->get_field_name('show_'.$name_stripped.'_required'); ?>" type="checkbox" <?php echo (($instance['show_'.$name_stripped.'_required']) ? ' checked' : ''); ?> /> <br>
										<?php
						}
						else{
						?>
							 <label for="<?php echo $this->get_field_id('show_'.$name_stripped.'_name'); ?>">
						            	<?php
						            	//takes out the rest of the name if it's a select / radio
										_e("Show '".$customfields[$field_key]['sailthru_customfield_name']."' field:");
										?>
										<input id="<?php echo $this->get_field_id('show_'.$name_stripped.'_name'); ?>" name="<?php echo $this->get_field_name('show_'.$name_stripped.'_name'); ?>" type="checkbox" />
										<br>
										Required: <input id="<?php echo $this->get_field_id('show_'.$name_stripped.'_required'); ?>" name="<?php echo $this->get_field_name('show_'.$name_stripped.'_required'); ?>" type="checkbox" /> <br>
						<?php
						}

						}
					}
				}
			}

            		?>

					</p>
			<p>
				<?php _e('Subscribe to list(s): '); ?>
				<?php
					foreach( $lists as $key => $list )
					{
					if(!empty($instance['sailthru_list'][$key])){
						$list_key = $instance['sailthru_list'][$key];
					}
					else{
						$list_key = '';
					}
						?>
						<br />
						<input type="checkbox" value="<?php echo esc_attr( $list['name'] ); ?>" name="<?php echo $this->get_field_name('sailthru_list'); ?>[<?php echo $key; ?>]" id="<?php echo esc_attr( $this->get_field_id('sailthru_list') . '-' . $key ); ?>" <?php checked($list_key, $list['name']); ?>  />
						<label for="<?php // echo esc_attr( $this->get_field_id('sailthru_list') . '-' . $key ); ?>"><?php echo esc_html( $list['name'] ); ?></label>
						<?php
					}
				?>
			</p>
        </div>


