<?php

    /*
     * Grab the settings from $instance and fill out default
     * values as needed.
     */
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
    $first_name = empty($instance['first_name']) ? '' : $instance['first_name'];
    $last_name = empty($instance['last_name']) ? '' : $instance['last_name'];
    if( is_array( $instance['sailthru_list'] ) )
    {
        $sailthru_list = implode(',', $instance['sailthru_list'] );
    } else {
        $sailthru_list = $instance['sailthru_list'];
    }


    // display options
    $customfields = get_option('sailthru_forms_options');
 
    // nonce
    $nonce = wp_create_nonce("add_subscriber_nonce"); 

 ?>
 <div class="sailthru-signup-widget">
     <div class="sailthru_form">

        <?php
            // title
            if (!empty( $title ) ) {
                if ( !isset( $before_title ) ) {
                    $before_title = '';
                }
                if ( !isset( $after_title ) ) {
                    $after_title = '';
                }
                echo $before_title . esc_html(trim($title)) . $after_title;
            }
            if(empty($customfields['sailthru_customfield_success'])){
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
            function attributes ( $attribute_list ) {
				if ( !empty( $attribute_list ) ) {
					$attributes = explode(',', $attribute_list);
					$list = '';
						foreach( $attributes as $attribute ) {
							$split = explode(':', $attribute);
							$list .= $split[0]. '="'.$split[1].'" ';
						
						}
					return $list;
				}
				return ''; 
			}
			function field_class ( $class ) {
				if ( !empty($class ) ) {
					return 'class="' . $class.'"';
				}
				return ''; 
			}
            $key = get_option('sailthru_forms_key');
			for ( $i = 0;$i < $key;$i++ ) {
			$field_key = $i + 1;
			if(!empty($customfields[$field_key])){
			$name_stripped = preg_replace("/[^\da-z]/i", '_', $customfields[$field_key]['sailthru_customfield_name']);
					if ( !empty( $instance['show_'.$name_stripped.'_name'] ) ) {
						if( !empty ( $customfields[$field_key]['sailthru_customfield_attr'] ) ) {
				                $attributes = $customfields[$field_key]['sailthru_customfield_attr'];
				                } else {
					                $attributes = '';
				        }
				                
						if ( $customfields[$field_key]['sailthru_customfield_type'] == 'select' ) {
						

				                ?>
				                <br />
				                <label for="custom_<?php echo $name_stripped;?>"><?php echo $customfields[$field_key]['sailthru_customfield_name'];?>:</label>
				                <select <?php echo field_class($customfields[$field_key]['sailthru_customfield_class']);?> <?php echo attributes($attributes);?> name="custom_<?php echo $name_stripped;?>" id="sailthru_<?php echo $name_stripped;?>_name">
				                <?php
				                $items = explode(',', $customfields[$field_key]['sailthru_customfield_value']);
				                foreach( $items as $item ) {
				                	$vals = explode(':', $item);
					                echo '<option value="'.$vals[0].'">'.$vals[1].'</option>';
				                }
				                ?>
				                </select>
				                
				                <?php
							
						}
						elseif ( $customfields[$field_key]['sailthru_customfield_type'] == 'radio' ) {
						

				                $items = explode(',', $customfields[$field_key]['sailthru_customfield_value']);
				                ?>
				                <br />
				                <label ><?php echo $customfields[$field_key]['sailthru_customfield_name'];?>:</label><br />
				                <?php
				                foreach ( $items as $item ) {
				                	$vals = explode(':', $item);
					                ?>
					                <input <?php echo field_class($customfields[$field_key]['sailthru_customfield_class']);?>  <?php echo attributes($attributes);?> <?php if ( $instance['show_'.$name_stripped.'_required'] == 'checked' ) { echo 'required=required';}?> type="radio" name="custom_<?php echo $name_stripped;?>" value="<?php echo $vals[0];?>"><?php echo $vals[1];?><br />
					                <?php
				                }
						}
						else{
						?>
						
		            <div class="sailthru_form_input">
		                <?php
		                //check if the field is required
		                if ( $instance['show_'.$name_stripped.'_required'] == 'checked' ) {
						if($customfields[$field_key]['sailthru_customfield_type'] != 'hidden'){
						?>
							<br />
			                <label for="custom_<?php echo $name_stripped;?>"><?php echo $customfields[$field_key]['sailthru_customfield_name'];?>*:</label>
			                <?php
			                }
			                ?>
			                <input <?php echo field_class($customfields[$field_key]['sailthru_customfield_class']);?> type="<?php echo $customfields[$field_key]['sailthru_customfield_type'];?>" <?php if($customfields[$field_key]['sailthru_customfield_type'] == 'hidden'){
			                echo 'value="'.$customfields[$field_key]['sailthru_customfield_value'].'" ';
		                }echo attributes($attributes);?>required="required" name="custom_<?php echo $name_stripped;?>" id="sailthru_<?php echo $name_stripped;?>_name" />
							<?php
						}
						else{
						if($customfields[$field_key]['sailthru_customfield_type'] != 'hidden'){
						?>
							<br />
							<label for="custom_<?php echo $name_stripped;?>"><?php echo $customfields[$field_key]['sailthru_customfield_name'];?>:</label>
							<?php
							}
							?>
							<input <?php if($customfields[$field_key]['sailthru_customfield_type'] == 'hidden'){
			                echo 'value="'.$customfields[$field_key]['sailthru_customfield_value'].'"';
		                } echo field_class($customfields[$field_key]['sailthru_customfield_class']);?> type="<?php echo $customfields[$field_key]['sailthru_customfield_type'];?>"  <?php echo attributes($attributes);?> name="custom_<?php echo $name_stripped;?>" id="sailthru_<?php echo $name_stripped;?>_name" />
						
							<?php
						}
						?>
						</div>
		            	<?php 
		            
		            	} ?>
					
					
					<?php
					}
					}
					}
            		?> 

            <div class="sailthru_form_input">
                <label for="sailthru_email">Email:</label>
                <input type="email" name="email" id="sailthru_email" value="" />
            </div>

            <div class="sailthru_form_input">
                <input type="hidden" name="sailthru_nonce" value="<?php echo $nonce; ?>" />
                <input type="hidden" name="sailthru_email_list" value="<?php echo esc_attr($sailthru_list); ?>" />
                <input type="hidden" name="action" value="add_subscriber" />
                <input type="hidden" name="vars[source]" value="<?php bloginfo('url'); ?>" />
                <input type="submit" value="Subscribe" />
            </div>
        </form>
    </div>
</div>
