<?php

	/* ------------------------------------------------------------------------ *
	 * Subscribe Widget Fields
	 * ------------------------------------------------------------------------ */

function sailthru_initialize_forms_options() {

	function sailthru_forms_callback( $args ) {

		/*
		** Custom and Extra Sections should be in a first column.
		** Begin the column here. It ends in delete_field()
		*/
		echo '<div class="wrap">';
		echo '<h2>Custom fields</h2>';
		echo '<p>Custom fields allow you to collect additional information from the user that can be stored in their Sailthru User Profile. </p>';

		$customfields  = get_option( 'sailthru_forms_options' );
		$key           = get_option( 'sailthru_forms_key' );
		$order		   = get_option( 'sailthru_customfields_order' );
		echo '<table class="wp-list-table widefat">';
		echo '<thead>';
		echo '<th scope="col" class=manage-column">&nbsp;</th>';
		echo '<th scope="col" class="manage-column">Display Label</th>';
		echo '<th scope="col" class="manage-column">Field Name</th>';
		echo '<th scope="col" class="manage-column">Field Type</th>';

		echo '<th scope="col" class="manage-column"> </th>';
		echo '</thead>';
		echo '<tbody id="sortable">';
		if ( isset($customfields) && !empty($customfields)){
			//If these were sorted display in proper order
			//echo $key;
			//print_r($customfields);
			//echo $order;
			if( isset($order) && !empty($order) ){
				$order_list = explode(',', $order);
				foreach ($order_list as $pos) {
					for ($i=1; $i <= (int)$key; $i++) {
						if($i == (int)$pos){
							if( isset($customfields[$i]['sailthru_customfield_label']) and !empty($customfields[$i]['sailthru_customfield_label'])
								&& isset($customfields[$i]['sailthru_customfield_name']) and !empty($customfields[$i]['sailthru_customfield_name']) ){
								echo '<tr id="pos_'. $i.'">';
								echo '<td><span class="icon-sort">&nbsp;</span></td>';
								echo '<td>'. esc_html($customfields[$i]['sailthru_customfield_label']).' </td>';
								echo '<td>'. esc_html($customfields[$i]['sailthru_customfield_name']).' </td>';
								echo '<td>'. esc_html($customfields[$i]['sailthru_customfield_type']).' </td>';
								echo '<td><button name="delete" class="button button-primary delete"  type="submit" id="delete" value="'. esc_attr( $i ). '">Delete</button></td>';
							 	echo '</tr>';
							}
						}
					}
				}
			} else {
				for ( $i = 1; $i <= $key; $i++ ) {
					if( isset($customfields[$i]['sailthru_customfield_label']) and !empty($customfields[$i]['sailthru_customfield_label'])
								&& isset($customfields[$i]['sailthru_customfield_name']) and !empty($customfields[$i]['sailthru_customfield_name']) ){
						echo '<tr id="pos_'. $i.'">';
						echo '<td><span class="icon-sort">&nbsp;</span></td>';
						echo '<td>'. esc_html($customfields[$i]['sailthru_customfield_label']).' </td>';
						echo '<td>'. esc_html($customfields[$i]['sailthru_customfield_name']).' </td>';
						echo '<td>'. esc_html($customfields[$i]['sailthru_customfield_type']).' </td>';
						echo '<td><button name="delete" class="button button-primary delete"  type="submit" id="delete" value="'. esc_attr( $i ). '">Delete</button></td>';
						echo '</tr>';
					}

				}
			}
		}
		echo '</tbody>';
		echo '</table>';
		echo '<input type="hidden" value="" name="sailthru_forms_options[sailthru_customfield_delete]" id="delete_value"></input>';


		echo '<h2>Add new custom fields to your Subscribe Widget';
		echo '<p>Use the form below to create a custom field library. Each created field will be available in our Sailthru Subscribe widget.</p>';


	}

	function field_type ( $args ) {

		$collection    = $args[0];
		$option_name   = $args[1];
		$default_value = $args[2];
		$html_id       = $args[3];
		$options       = get_option( $collection );
		$value         = '';
		if ( isset( $options[ $option_name ] ) ) {
			$value = $options[ $option_name ];
		} else {
			$value = $default_value;
		}

	// Render the output of the field type selector

		echo '<select id="type" name="' . esc_attr( $collection ) . '[' . esc_attr( $option_name ) . ']">
				  <option value="text"' . selected( esc_attr( $value ), 'text' ) . '>Text Field</option>
				  <option value="password"'. selected( esc_attr( $value ), 'password' ) . '>Password</option>
				  <option value="tel"' . selected( esc_attr( $value ), 'tel' ) . '>Telephone</option>
				  <option value="date"' . selected( esc_attr( $value ), 'date' ) . '>Date</option>
				  <option value="hidden"' . selected( esc_attr( $value ), 'hidden' ) . '>Hidden</option>
				  <option value="select"' . selected( esc_attr( $value ), 'select' ) . '>Select</option>
				  <option value="radio"' . selected( esc_attr( $value ), 'radio' ) . '>Radio</option>
				  <option value="checkbox"' . selected( esc_attr( $value ), 'radio' ) . '>Checkbox</option>
			  </select>';
		echo  '<div class="instructions">The type of html form field displayed.</div>';

	}

	function sailthru_create_second_column() {
		/*
		** Delete and Existing Sections should be in a second column.
		** Begin the column here. It ends in views/admin.php (unfortunately)
		*/
		echo '';
	}

	function delete_field ( $args ) {
		$customfields  = get_option( 'sailthru_forms_options' );
		$collection    = $args[0];
		$option_name   = $args[1];
		$default_value = $args[2];
		$html_id       = $args[3];
		$options       = get_option( $collection );
		$key           = get_option( 'sailthru_forms_key' );


		echo '<select name="' . esc_attr( $collection ) . '[' . esc_attr( $option_name ) . ']">
				<option value="select">Select...</option>';
				for ( $i = 0; $i < $key; $i++ ) {
					 $field_key = $i + 1;
					 if ( ! empty ( $customfields[ $field_key ][ 'sailthru_customfield_name' ] ) ) {
					 echo '<option value="'.esc_attr($field_key).'" >'.esc_html($customfields[ $field_key ][ 'sailthru_customfield_label' ]).'</option>';
					 }
				} //end for loop
		echo '</select>';
		echo '<div>'.submit_button('Delete Field') .'</div>';

	}


	function field_order( $args ){

		echo '<input type="hidden" value="" name="sailthru_forms_options[sailthru_customfield_field_order]" id="field_order"></input>';
	}

	function sailthru_success_field ( $args ) {
		$customfields  = get_option( 'sailthru_forms_options' );
		$collection    = $args[0];
		$option_name   = $args[1];
		$default_value = $args[2];
		$html_id       = $args[3];
		$options       = get_option( $collection );

		if ( empty ( $customfields['sailthru_customfield_success'] ) ) {
			$message = '';
		}
		else{
			$message = $customfields['sailthru_customfield_success'];
		}

		echo '<div  id="postbox-container-1" class="postbox-container">';
			echo '<div id="normal-sortables" class="meta-box-sortables ui-sortable">';
				echo '<div id="dashboard_right_now" class="postbox ">';
					echo '<h3 class="hndle"><span>Custom Thank You Message</span></h3>';
					echo '<div class="inside">';
						echo '<div class="main">';
						echo '<p>Use the field below to update the message that the user sees after subscribing</p>';
						echo '<p><textarea name="' . esc_attr( $collection ) . '[sailthru_customfield_success]" placeholder="" rows="5" cols="80">'.esc_textarea($message).'</textarea></p>';
						echo '<div style="text-align:left;"><input type="submit" name="submit" id="submit" class="button button-primary" value="Update Thank You Message"></div>';
					echo '</div>';
				echo '</div>';
			echo '</div>';
		echo '</div>';

	}



	function sailthru_fields() {

		    $customfields = get_option( 'sailthru_forms_options' );
		    $key          = get_option( 'sailthru_forms_key' );

			for ( $i = 0; $i < $key; $i++ ) {
			$field_key = $i + 1;
				if ( ! empty ( $customfields[ $field_key ] ) ) {
					if ( $customfields[ $field_key ]['sailthru_customfield_name'] != '' ) {

						$name_stripped = preg_replace( "/[^\da-z]/i", '_', $customfields[ $field_key ]['sailthru_customfield_name'] );
						//select field
						if ( $customfields[ $field_key ]['sailthru_customfield_type'] == 'select' ) {
					        echo '
					        <label for="custom_' . $name_stripped . '">' . $customfields[ $field_key ]['sailthru_customfield_name'] . ':</label>
							<select name="custom_' . $name_stripped .'" id="sailthru_' . $name_stripped . '_name">';

					        $items = explode( ',', $customfields[ $field_key ]['sailthru_customfield_value'] );
					        foreach( $items as $item ) {
						        $vals = explode( ':', $item );
							    echo '<option value="' . esc_attr($vals[0]) . '">' . esc_html($vals[1]) . '</option>';
						    }
					        echo '</select>';
													}
						//radio field
						elseif ( $customfields[ $field_key ]['sailthru_customfield_type'] == 'radio' ) {

				                $items = explode( ',', $customfields[ $field_key ]['sailthru_customfield_value'] );
				                echo '<label >' . esc_html($customfields[ $field_key ]['sailthru_customfield_name']) . ':</label>';

				                foreach ( $items as $item ) {
				                	$vals = explode( ':', $item );
					                echo '<input type="radio" name="custom_' . esc_attr($name_stripped) . '" value="' . esc_attr($vals[0]) . '"> ' . esc_html($vals[1]);
				                }
						}
						//hidden field
						elseif ( $customfields[ $field_key ]['sailthru_customfield_type'] == 'hidden' ) {
							echo 'hidden field: ' . esc_html($customfields[ $field_key ]['sailthru_customfield_name']).'';
						}
						//field is a text input
						else{

							echo '<div class="sailthru_form_input">';
			                //check if the field is required
							if ( $customfields[ $field_key ]['sailthru_customfield_type'] != 'hidden' ) {
								echo '<br /><label for="custom_' . esc_attr($name_stripped) . '">' . esc_html($customfields[ $field_key ]['sailthru_customfield_name']) . ':</label>';
							}
							echo '<input type="' . esc_attr($customfields[ $field_key ]['sailthru_customfield_type']) . '" name="custom_' . esc_attr($name_stripped) . '" id="sailthru_' . esc_attr($name_stripped) . '_name" />';

		            	} //end text input
					} // end if name ! empty
				} // end if
			}
	}

	function sailthru_value_field ( $args ) {
		$collection    = $args[0];
		$option_name   = $args[1];
		$default_value = $args[2];
		$html_id       = $args[3];
		$options       = get_option( $collection );

		echo '<div class="sailthru_keypair_fields"  id="sailthru_value_fields_block">';
		echo '<input class="selection" name="' . esc_attr( $collection ) . '[' . esc_attr( $option_name .'2' ) . ']" type="text" placeholder="display " />';
		echo '<input class="selection" name="' . esc_attr( $collection ) . '[' . esc_attr( $option_name.'1' ) . ']" type="text"  placeholder="value"/>';
		echo '<input id="value_amount" type="hidden" name="' . esc_attr( $collection ) . '[' . esc_attr( $option_name .'_val' ) . ']" value="1" />';
		echo '</div>';
		echo '</div>';
		echo '<div class="instructions">';
		echo '<a id="add_value" href ="">Add Another</a>';
		echo '<div>';

	}
	function sailthru_attr_field ( $args ) {
		$collection    = $args[0];
		$option_name   = $args[1];
		$default_value = $args[2];
		$html_id       = $args[3];
		$options       = get_option( $collection );

		echo '<div class="sailthru_keypair_fields" id="sailthru_attr_fields_block">';
		echo '<input class="attribute" name="' . esc_attr( $collection ) . '[' . esc_attr( $option_name .'1' ) . ']" type="text" placeholder="attribute" />';
		echo '<input class="attribute" name="' . esc_attr( $collection ) . '[' . esc_attr( $option_name.'2' ) . ']" type="text"  placeholder="value"/>';
		echo '<input id="attr_amount" type="hidden" name="' . esc_attr( $collection ) . '[' . esc_attr( $option_name .'_val' ) . ']" value="2" />';
		echo '</div>';
		echo '</div>';
		echo '<div class="instructions">';
		echo '<a id="add_attr" href ="">Add Another</a>';
		echo '<div>';

	}

	// If the section options don't exist, create them.
	if ( false == get_option( 'sailthru_forms_options' ) ) {
		add_option( 'sailthru_forms_options' );
	} // end if

	$forms = get_option( 'sailthru_forms_options' );



	/* ------------------------------------------------------------------------ *
	 * Show Options for Adding Custom Fields
	 * ------------------------------------------------------------------------ */
	add_settings_section(
		'sailthru_forms_section',							// ID used to identify this section and with which to register options
		__( '', 'sailthru-for-wordpress' ),	// Title to be displayed on the administration page
		'sailthru_forms_callback',							// Callback used to render the description of the section
		'sailthru_forms_options'							// Page on which to add this section of options
	);

		add_settings_field(
			'sailthru_customfield_type',					// ID used to identify the field throughout the theme
			__( 'Field Type', 'sailthru-for-wordpress' ),	// The label to the left of the option interface element
			'field_type',									// The name of the function responsible for rendering the option interface
			'sailthru_forms_options',						// The page on which this option will be displayed
			'sailthru_forms_section',						// The name of the section to which this field belongs
			array(											// The array of arguments to pass to the callback. In this case, just a description.
				'sailthru_forms_options',
				'sailthru_customfield_type',
				'',
				'sailthru_customfield_type'
			)
		);

		add_settings_field(
			'sailthru_customfield_label',					// ID used to identify the field throughout the theme
			__( 'Display label', 'sailthru-for-wordpress' ),	// The label to the left of the option interface element
			'sailthru_html_text_input_callback',			// The name of the function responsible for rendering the option interface
			'sailthru_forms_options',						// The page on which this option will be displayed
			'sailthru_forms_section',						// The name of the section to which this field belongs
			array(											// The array of arguments to pass to the callback. In this case, just a description.
				'sailthru_forms_options',
				'sailthru_customfield_label',
				'',
				'sailthru_customfield_label',
				'The text in this field is used for the field label.',
			)
		);

		add_settings_field(
			'sailthru_customfield_name',					// ID used to identify the field throughout the theme
			__( 'Field name', 'sailthru-for-wordpress' ),	// The label to the left of the option interface element
			'sailthru_html_text_input_callback',			// The name of the function responsible for rendering the option interface
			'sailthru_forms_options',						// The page on which this option will be displayed
			'sailthru_forms_section',						// The name of the section to which this field belongs
			array(											// The array of arguments to pass to the callback. In this case, just a description.
				'sailthru_forms_options',
				'sailthru_customfield_name',
				'',
				'sailthru_customfield_name',
				'The name used as a var in the Sailthru user profile.',
			)
		);

		add_settings_field(
			'sailthru_customfield_field_order',
			__( '', 'sailthru-for-wordpress' ),
			'field_order',
			'sailthru_forms_options',
			'sailthru_forms_section',
			array(
					'sailthru_forms_options',
					'sailthru_customfield_field_order',
					'',
					'sailthru_customfield_field_order',
				)
			);


		add_settings_field(
				'sailthru_customfield_value',				// ID used to identify the field throughout the theme
				__( 'Field values', 'sailthru-for-wordpress' ),					// The label to the left of the option interface element
				'sailthru_value_field',						// The name of the function responsible for rendering the option interface
				'sailthru_forms_options',					// The page on which this option will be displayed
				'sailthru_forms_section',					// The name of the section to which this field belongs
				array(										// The array of arguments to pass to the callback. In this case, just a description.
					'sailthru_forms_options',
					'sailthru_customfield_value',
					'',
					'sailthru_customfield_value',
				)
		);



	/* ------------------------------------------------------------------------ *
	 * Show Existing Advanced Fields
	 * ------------------------------------------------------------------------ */
	add_settings_section(
		'sailthru_adv_section',								// ID used to identify this section and with which to register options
		__( '', 'sailthru-for-wordpress' ),	     // Title to be displayed on the administration page
		'sailthru_html_fields_options_callback',							// Callback used to render the description of the section
		'sailthru_forms_options'							// Page on which to add this section of options
	);
		add_settings_field(
				'sailthru_customfield_class',				// ID used to identify the field throughout the theme
				__( 'CSS Class(es)', 'sailthru-for-wordpress' ),	// The label to the left of the option interface element
				'sailthru_html_text_input_callback',		// The name of the function responsible for rendering the option interface
				'sailthru_forms_options',					// The page on which this option will be displayed
				'sailthru_adv_section',						// The name of the section to which this field belongs
				array(										// The array of arguments to pass to the callback. In this case, just a description.
					'sailthru_forms_options',
					'sailthru_customfield_class',
					'',
					'sailthru_customfield_class',
					'Separate multiple css classes using a space'
				)
		);

		add_settings_field(
				'sailthru_customfield_attr',				// ID used to identify the field throughout the theme
				__( 'Data Attributes', 'sailthru-for-wordpress' ),	// The label to the left of the option interface element
				'sailthru_attr_field',						// The name of the function responsible for rendering the option interface
				'sailthru_forms_options',					// The page on which this option will be displayed
				'sailthru_adv_section',						// The name of the section to which this field belongs
				array(										// The array of arguments to pass to the callback. In this case, just a description.
					'sailthru_forms_options',
					'sailthru_customfield_attr',
					'',
					'sailthru_customfield_attr'
				)
		);





	/* ------------------------------------------------------------------------ *
	 * Custom Success Message
	 * ------------------------------------------------------------------------ */
	add_settings_section(
		'sailthru_delete_section',							// ID used to identify this section and with which to register options
		__( '', 'sailthru-for-wordpress' ),					// Title to be displayed on the administration page
		'sailthru_create_second_column',					// Callback used to render the description of the section
		'sailthru_forms_options'							// Page on which to add this section of options
	);


		add_settings_field(
				'sailthru_customfield_success',				// ID used to identify the field throughout the theme
				__( 'Subscribe Message', 'sailthru-for-wordpress' ),					// The label to the left of the option interface element
				'sailthru_success_field',					// The name of the function responsible for rendering the option interface
				'sailthru_forms_options',					// The page on which this option will be displayed
				'sailthru_delete_section',						// The name of the section to which this field belongs
				array(										// The array of arguments to pass to the callback. In this case, just a description.
					'sailthru_forms_options',
					'sailthru_customfield_success',
					'',
					'sailthru_customfield_success'
				)
		);


	// Finally, we register the fields with WordPress
	register_setting(
		'sailthru_forms_options',
		'sailthru_forms_options',
		'sailthru_forms_handler'
	);

} // end sailthru_initialize_setup_options
add_action( 'admin_init', 'sailthru_initialize_forms_options' );


/* ------------------------------------------------------------------------ *
 * Section Callbacks
 * ------------------------------------------------------------------------ */

/**
 * Provides a simple description for each setup page respectively.
 *
 * It's called from the 'sailthru_initialize_setup_options' function by being passed as a parameter
 * in the add_settings_section function.
 */


function sailthru_html_fields_options_callback() {
	echo '<h2>Advanced HTML options</h2>';
	//Add additional HTML attributes such as CSS classes and data attributes to the form field. These are optional fields to allow theme developers to integrate with their own themes.
}


/* ------------------------------------------------------------------------ *
 * Field Callbacks
 * ------------------------------------------------------------------------ */

 function sailthru_forms_handler( $input ) {

	$fields = get_option( 'sailthru_forms_options' );
	$output = $fields;
	$key    = get_option( 'sailthru_forms_key' );
	if( isset( $input['sailthru_customfield_field_order']) ) {
		$order = sanitize_text_field($input['sailthru_customfield_field_order']);
	} else {
		$order = false;
	}
		if ( isset( $order ) and $order != ''){
			update_option( 'sailthru_customfields_order', $order);
		}
		if ( isset( $key ) ) {
			$new_key = $key + 1;
			update_option( 'sailthru_forms_key',$new_key );
		}
		else{
			$new_key = 0;
			add_option( 'sailthru_forms_key',$new_key );
		}
		if ( ! empty( $input['sailthru_customfield_name'] ) ) {
			//remove custom order
			delete_option('sailthru_customfields_order');
			$output[ $new_key ]['sailthru_customfield_label']    = sanitize_text_field($input['sailthru_customfield_label']);
			$output[ $new_key ]['sailthru_customfield_name']    = sanitize_text_field($input['sailthru_customfield_name']);
			$output[ $new_key ]['sailthru_customfield_type']      = sanitize_text_field($input['sailthru_customfield_type']);
			$output[ $new_key ]['sailthru_customfield_class']     = sanitize_html_class($input['sailthru_customfield_class']);
			$output[ $new_key ]['sailthru_customfield_field_order']		= sanitize_text_field($input['sailthru_customfield_field_order']);

			if ( ! empty( $input['sailthru_customfield_attr'] ) ) {
			$output[ $new_key ]['sailthru_customfield_attr']      = sanitize_text_field($input['sailthru_customfield_attr']);
			}
			if ( $input['sailthru_customfield_type'] == 'select' || $input['sailthru_customfield_type'] == 'radio' || $input['sailthru_customfield_type'] == 'checkbox' ) {
				$amount = sanitize_text_field($input['sailthru_customfield_value_val']);
					$values = '';
					for( $i = 1; $i <= $amount; $i++ ) {
						if ( $i != $amount ) {
							if ( $i % 2 == 0 ) {
								$values .= sanitize_text_field($input['sailthru_customfield_value'.$i]) .',';
							}
							else{
								$values .= sanitize_text_field($input['sailthru_customfield_value'.$i]) .':';
							}
						}
						else{
							$values .= sanitize_text_field($input['sailthru_customfield_value'.$i]);
						}
					} //end for
					$output[ $new_key ]['sailthru_customfield_value']      = $values;
			}
			if ( $input['sailthru_customfield_type'] == 'hidden' ) {
				$output[ $new_key ]['sailthru_customfield_value'] = sanitize_text_field($input['sailthru_customfield_value2']);
			}
				if ( ! empty( $input['sailthru_customfield_attr1'] ) && ! empty( $input['sailthru_customfield_attr2'] ) ) {
					$amount = $input['sailthru_customfield_attr_val'];
					$values = '';
					for( $i = 1; $i <= $amount; $i++ ) {
						if ( $i != $amount ) {
							if ( $i % 2 == 0 ) {
								$values .= sanitize_text_field($input['sailthru_customfield_attr'.$i]) .',';
							}
							else{
								$values .= sanitize_text_field($input['sailthru_customfield_attr'.$i]) .':';
							}
						}
						else{
							$values .= sanitize_text_field($input['sailthru_customfield_attr'.$i]);
						}
					}
					$output[ $new_key ]['sailthru_customfield_attr']      = $values;
			}
		}// end if empty field name

		if ( !empty ( $input['sailthru_customfield_delete'] ) ) {
			unset($output[$input['sailthru_customfield_delete']]);
			update_option( 'sailthru_forms_options', $output);

			// $order = str_replace( '"'.$input['sailthru_customfield_delete'].'"', '', $order);
			delete_option('sailthru_customfields_order');
		}
		$output['sailthru_customfield_success'] = sanitize_text_field($input['sailthru_customfield_success']);

	return $output;

}



 /* ------------------------------------------------------------------------ *
 * Setting Callbacks
 * ------------------------------------------------------------------------ */



/* ------------------------------------------------------------------------ *
 * Utility Functions
 * ------------------------------------------------------------------------ */



