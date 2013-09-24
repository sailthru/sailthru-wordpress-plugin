<?php

    /*
     * Grab the settings from $instance and fill out default
     * values as needed.
     */
    $title      = empty( $instance['title'] ) ? ' ' : apply_filters( 'widget_title', $instance['title'] );
    if ( ! empty( $instance['sailthru_list'] ) ) {
	    if ( is_array( $instance['sailthru_list'] ) )
	    {
	        $sailthru_list = implode( ',', $instance['sailthru_list'] );
	    } else {
	        $sailthru_list = $instance['sailthru_list'];
	    }
	}
	else{
		$sailthru_list = 'Sailthru Wordpress Shortcode';
	}

    // display options
    $customfields = get_option( 'sailthru_forms_options' );
 
    // nonce
    $nonce = wp_create_nonce( 'add_subscriber_nonce' ); 

 ?>
 <div class="sailthru-signup-widget">
     <div class="sailthru_form">

        <?php
            // title
            if ( ! empty( $title ) ) {
                if ( ! isset( $before_title ) ) {
                    $before_title = '';
                }
                if ( !isset( $after_title ) ) {
                    $after_title = '';
                }
                echo $before_title . esc_html( trim( $title ) ) . $after_title;
            }
            if ( empty( $customfields['sailthru_customfield_success'] ) ) {
	            $success = 'Thank you for subscribing!';
            }
            else{
	            $success = $customfields['sailthru_customfield_success'];
            }
        ?>
        <div id="success" hidden="hidden"><?php echo $success;?></div>
         <form method="post" action="#" id="sailthru-add-subscriber-form">
            <div id="sailthru-add-subscriber-errors"></div>
            <?php
            $key = get_option( 'sailthru_forms_key' );
            if ( ! empty( $instance['fields'] ) ) {
            	$fields = explode( ',', $instance['fields'] );
	            foreach ( $fields as $field ) {
	            	$name_stripped = preg_replace( "/[^\da-z]/i", '_', $field );
	            	$instance['show_'.$name_stripped.'_name']     = true;
	            	$instance['show_'.$name_stripped.'_required'] = false;
	            }
            }
				for ( $i = 0; $i < $key; $i++ ) {
				$field_key = $i + 1;
				if ( ! empty( $customfields[ $field_key ] ) ) {
				$name_stripped = preg_replace( "/[^\da-z]/i", '_', $customfields[ $field_key ]['sailthru_customfield_name'] );
					if ( ! empty( $instance['show_'.$name_stripped.'_name'] ) ) {
						if( ! empty ( $customfields[ $field_key ]['sailthru_customfield_attr'] ) ) {
				                $attributes = $customfields[ $field_key ]['sailthru_customfield_attr'];
				        } else {
					                $attributes = '';
				        }
				                
						if ( $customfields[ $field_key ]['sailthru_customfield_type'] == 'select' ) {
						
	
				                echo '<br /><label for="custom_' . $name_stripped . '">' . $customfields[ $field_key ]['sailthru_customfield_name'] . ':</label>
				                <select ' . field_class( $customfields[ $field_key ]['sailthru_customfield_class'] ) .' '. attributes( $attributes ) . 'name="custom_' . $name_stripped . '" id="sailthru_' . $name_stripped . '_name">';
				                
				                $items = explode( ',', $customfields[ $field_key ]['sailthru_customfield_value'] );
				                foreach( $items as $item ) {
				                	$vals = explode( ':', $item );
					                echo '<option value="' . $vals[0] . '">' . $vals[1] . '</option>';
				                }
				                echo '</select>';
							
						}
						elseif ( $customfields[ $field_key ]['sailthru_customfield_type'] == 'radio' ) {
						
	
				                $items = explode( ',', $customfields[ $field_key ]['sailthru_customfield_value'] );
				                echo '<br /><label for="custom_' . $name_stripped . '">' . $customfields[ $field_key ]['sailthru_customfield_name'] . ':</label><br />';
				                foreach ( $items as $item ) {
				                	$vals = explode( ':', $item );
					                echo '<input ';
					                if ( $instance['show_'.$name_stripped.'_required'] == 'checked' ) {
					                	echo 'required=required ';
					                }
					                echo 'type="radio" name="custom_'. $name_stripped . '" value="' . $vals[0] . '"> ' . $vals[1] . '<br />';
				                }
						}
						else{
							echo '<div class="sailthru_form_input">';
			                //check if the field is required
			                if ( $instance['show_'.$name_stripped.'_required'] == 'checked' ) {
								if ( $customfields[ $field_key ]['sailthru_customfield_type'] != 'hidden' ) {
									echo '<br /><label for="custom_' . $name_stripped . '">' . $customfields[ $field_key ]['sailthru_customfield_name'] . '*:</label>';
					            }
					            echo '<input ' . field_class( $customfields[ $field_key ]['sailthru_customfield_class'] ) . ' type="' . $customfields[ $field_key ]['sailthru_customfield_type'] . '" '; 
					            if ( $customfields[ $field_key ]['sailthru_customfield_type'] == 'hidden' ) {
					                echo 'value="'.$customfields[ $field_key ]['sailthru_customfield_value'].'" ';
				                }
				                echo attributes( $attributes ) . 'required="required" name="custom_' . $name_stripped . '" id="sailthru_' . $name_stripped . '_name" />';
							}
							else{
							if ( $customfields[ $field_key ]['sailthru_customfield_type'] != 'hidden' ) {
								echo '<br /><label for="custom_' .$name_stripped . '">' . $customfields[ $field_key ]['sailthru_customfield_name'] . ':</label>';
							}
								echo '<input ';
								if ( $customfields[ $field_key ]['sailthru_customfield_type'] == 'hidden' ) {
				                echo 'value="'.$customfields[ $field_key ]['sailthru_customfield_value'].'" ';
								} 
								echo field_class( $customfields[ $field_key ]['sailthru_customfield_class'] ) .' type="' .$customfields[ $field_key ]['sailthru_customfield_type'] . '" ' . attributes( $attributes ) . 'name="custom_' . $name_stripped. '" id="sailthru_' .$name_stripped. '_name" />';
							
							}
							echo '</div>';
		            
		            	} 
					} //end if !empty name
				} // end if !empty field key
			}// end for loop
            		?> 

            <div class="sailthru_form_input">
                <label for="sailthru_email">Email:</label>
                <input type="email" name="email" id="sailthru_email" value="" />
            </div>

            <div class="sailthru_form_input">
                <input type="hidden" name="sailthru_nonce" value="<?php echo $nonce; ?>" />
                <input type="hidden" name="sailthru_email_list" value="<?php echo esc_attr( $sailthru_list ); ?>" />
                <input type="hidden" name="action" value="add_subscriber" />
                <input type="hidden" name="vars[source]" value="<?php bloginfo( 'url' ); ?>" />
                <input type="submit" value="Subscribe" />
            </div>
        </form>
    </div>
</div>