	<div id="icon-sailthru" class="icon32"></div>
	<h2><?php _e( 'Sailthru Subscribe', 'sailthru-for-wordpress' ); ?></h2>

	<?php

		$sailthru     = get_option( 'sailthru_setup_options' );
		$customfields = get_option( 'sailthru_forms_options' );
		$order 		  = get_option( 'sailthru_customfield_order' );
		$key 		  = get_option( 'sailthru_forms_key' );

		if ( ! is_array( $sailthru ) )
		{

			echo '<p>Please return to the <a href="' . esc_url( menu_page_url( 'sailthru_configuration_menu', false ) ) . '">Sailthru Settings screen</a> and set up your API key and secret before setting up this widget.</p>';
			return;

		}
		$api_key    = $sailthru['sailthru_api_key'];
		$api_secret = $sailthru['sailthru_api_secret'];

		$client = new WP_Sailthru_Client( $api_key, $api_secret );
			try {
				if ( $client ) {
					$res = $client->getLists();
				}
			}
			catch ( Sailthru_Client_Exception $e ) {
				//silently fail
				return;
			}


			$lists = $res['lists'];

	?>
                <div id="<?php echo $this->get_field_id( 'title' ); ?>_div" style="display: block;">
            <p>
            	<label for="<?php echo $this->get_field_id( 'title' ); ?>">
            		<?php _e( 'Widget Title:' ); ?>
            		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
            	</label>
            </p>
            <p>
			<?php
			echo '<div class="sortable_widget">';
			echo '<table class="wp-list-table widefat">';
			echo '<thead>';
			echo '<tr><th style="width:20px"></th><th align="left">Field</th><th align="left">Active</th><th>Required</th></tr>';
			echo '</thead>';
			echo '<tbody>';
			echo '<tr id="not_sortable"><td>&nbsp;</td><td>Email</td><td colspan="2">Always displayed</td></tr>';
			if ( isset($customfields) && !empty($customfields)){
			//If these were sorted display in proper order
			if( isset($order ) && $order != '' ){
				$order_list = explode(',', $order);
			}
			if( isset($order_list) ){
			for ( $j = 0; $j < count($order_list); $j++ ) {
						$field_key = (int)$order_list[$j];
						for ( $i = 0; $i <= $key; $i++ ) {
							if ( $i == $field_key ) {
								echo '<tr id="pos_' . $field_key . '">';
								if( isset( $customfields[ $i ]['sailthru_customfield_name'] )){
								$name_stripped = preg_replace("/[^\da-z]/i", '_', $customfields[ $field_key ]['sailthru_customfield_name']);

								if( ! empty( $instance['show_'.$name_stripped.'_name'] ) ) {
									echo '<td><span class="dashicons icon-sort"></span></td>';
									echo '<td>'. esc_html($customfields[ $field_key ]['sailthru_customfield_label']) . '</td>';
									echo '<td><input id="' . $this->get_field_id( 'show_'.$name_stripped.'_name' ) . '" name="' . $this->get_field_name( 'show_'.$name_stripped.'_name' ) . '" type="checkbox"' .(( $instance['show_'.$name_stripped.'_name']) ? ' checked' : '') . '/></td>';
									echo '<td><input id="' . $this->get_field_id( 'show_'.$name_stripped.'_required' ) . '" name="' . $this->get_field_name( 'show_'.$name_stripped.'_required' ) . '" type="checkbox"' . (( $instance['show_'.$name_stripped.'_required'] ) ? ' checked' : '') . ' /> </td>';
								}
								else{
									echo '<td><span class="dashicons icon-sort"></span></td>';
									echo '<td>'. esc_html($customfields[ $field_key ]['sailthru_customfield_label'] ). '</td>';
									echo '<td><input id="' . $this->get_field_id( 'show_'.$name_stripped.'_name' ) . '" name="' . $this->get_field_name( 'show_'.$name_stripped.'_name' ) . '" type="checkbox" /></td>';
									echo '<td><input id="' . $this->get_field_id( 'show_'.$name_stripped.'_required' ) . '" name="' . $this->get_field_name( 'show_'.$name_stripped.'_required' ) . '" type="checkbox" /></td>';
								}
								echo '</tr>';
							}
						} //if field name exists
					} //for loop
				} //for loop
			} else {
				 for ( $i = 0; $i <= $key; $i++ ) {
				// 		//if ( $i == $field_key ) {
				 			echo '<tr id="pos_' . $i . '">';
				 			if( isset( $customfields[ $i ]['sailthru_customfield_name'] )){
							$name_stripped = preg_replace("/[^\da-z]/i", '_', $customfields[ $i ]['sailthru_customfield_name']);

							if( ! empty( $instance['show_'.$name_stripped.'_name'] ) ) {
								echo '<td><span class="dashicons icon-sort"></span></td>';
								echo '<td>'. esc_html($customfields[ $i ]['sailthru_customfield_label']) . '</td>';
								echo '<td><input id="' . $this->get_field_id( 'show_'.$name_stripped.'_name' ) . '" name="' . $this->get_field_name( 'show_'.$name_stripped.'_name' ) . '" type="checkbox"' .(( $instance['show_'.$name_stripped.'_name']) ? ' checked' : '') . '/></td>';
								echo '<td><input id="' . $this->get_field_id( 'show_'.$name_stripped.'_required' ) . '" name="' . $this->get_field_name( 'show_'.$name_stripped.'_required' ) . '" type="checkbox"' . (( $instance['show_'.$name_stripped.'_required'] ) ? ' checked' : '') . ' /> </td>';
							}
							else{
								echo '<td><span class="dashicons icon-sort"></span></td>';
								echo '<td>'. esc_html($customfields[ $i ]['sailthru_customfield_label'] ). '</td>';
								echo '<td><input id="' . $this->get_field_id( 'show_'.$name_stripped.'_name' ) . '" name="' . $this->get_field_name( 'show_'.$name_stripped.'_name' ) . '" type="checkbox" /></td>';
								echo '<td><input id="' . $this->get_field_id( 'show_'.$name_stripped.'_required' ) . '" name="' . $this->get_field_name( 'show_'.$name_stripped.'_required' ) . '" type="checkbox" /></td>';
							}
						}
	
				 			echo '</tr>';

				// 		//} //if field name exists
				 	} //for loop
				} //for loop
			}
		

			echo '</tbody>';
			echo '</table>';
			echo '<div>';
			echo '<p id="field_order" value=""></p>';
			echo '<input type="hidden" value="" name="'. $this->get_field_name( 'field_order' ) .'" id="' . $this->get_field_id( 'field_order' ) . '"></input>';
			echo '</div>';
			echo '</div>';
			

            		?>

					</p>
			<p>
				<?php _e( 'Subscribe to list(s): ' ); ?>
				<?php
					foreach ( $lists as $key => $list ) {
					if( ! empty( $instance['sailthru_list'][ $key ] ) ) {
						$list_key = $instance['sailthru_list'][ $key ];
					}
					else{
						$list_key = '';
					}
						?>
						<br />
						<input type="checkbox" value="<?php echo esc_attr( $list['name'] ); ?>" name="<?php echo $this->get_field_name( 'sailthru_list' ); ?>[<?php echo $key; ?>]" id="<?php echo esc_attr( $this->get_field_id( 'sailthru_list' ) . '-' . $key ); ?>" <?php checked( $list_key, $list['name'] ); ?>  />
						<label for=""><?php echo esc_html( $list['name'] ); ?></label>
						<?php
					}
				?>
			</p>
        </div>
