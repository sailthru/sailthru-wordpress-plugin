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
            if (!empty($title)) {
                if(!isset($before_title)) {
                    $before_title = '';
                }
                if(!isset($after_title)) {
                    $after_title = '';
                }
                echo $before_title . esc_html(trim($title)) . $after_title;
            }
        ?>
         <form method="post" action="#" id="sailthru-add-subscriber-form">
            <div id="sailthru-add-subscriber-errors"></div>
            <?php
            $key = get_option('sailthru_forms_key');
			for($i = 0;$i < $key;$i++){
			$field_key = $i + 1;
			$name_stripped = preg_replace("/[^\da-z]/i", '_', $customfields[$field_key]['sailthru_customfield_name']);
					if( !empty($instance['show_'.$name_stripped.'_name']) ) {
					
						if($customfields[$field_key]['sailthru_customfield_type'] == 'select'){
						

				                ?>
				                <label for="custom_<?php echo $name_stripped;?>"><?php echo $customfields[$field_key]['sailthru_customfield_name'];?>:</label>
				                <select name="custom_<?php echo $name_stripped;?>" id="sailthru_<?php echo $name_stripped;?>_name">
				                <?php
				                $items = explode(',', $customfields[$field_key]['sailthru_customfield_value']);
				                foreach($items as $item){
					                echo '<option value="'.$item.'">'.$item.'</option>';
				                }
				                ?>
				                </select><br />
				                
				                <?php
							
						}
						elseif($customfields[$field_key]['sailthru_customfield_type'] == 'radio'){
						

				                $items = explode(',', $customfields[$field_key]['sailthru_customfield_value']);
				                ?>
				                <label ><?php echo $customfields[$field_key]['sailthru_customfield_name'];?>:</label><br /><br />
				                <?php
				                foreach($items as $item){
					                ?>
					                <input <?php if($instance['show_'.$name_stripped.'_required'] == 'checked'){ echo 'required=required';}?> type="radio" name="custom_<?php echo $name_stripped;?>" value="<?php echo $item;?>"><?php echo $item;?><br />
					                <?php
				                }
						}
						else{
						?>
						
		            <div class="sailthru_form_input">
		                <?php
		                $text_key = explode(':', $customfields[$field_key]['sailthru_customfield_value']);
		                $value = $text_key[1];
		                //check if the field is required
		                if($instance['show_'.$name_stripped.'_required'] == 'checked'){
			                ?>
			                <label for="custom_<?php echo $name_stripped;?>"><?php echo $customfields[$field_key]['sailthru_customfield_name'];?>*:</label>
			                <input type="<?php echo $customfields[$field_key]['sailthru_customfield_type'];?>" required="required" name="custom_<?php echo $name_stripped;?>" id="sailthru_<?php echo $name_stripped;?>_name" placeholder="<?php echo $value;?>" /><br />
							<?php
						}
						else{
						?>
							<label for="custom_<?php echo $name_stripped;?>"><?php echo $customfields[$field_key]['sailthru_customfield_name'];?>:</label>
							<input type="<?php echo $customfields[$field_key]['sailthru_customfield_type'];?>" name="custom_<?php echo $name_stripped;?>" id="sailthru_<?php echo $name_stripped;?>_name" placeholder="<?php echo $value;?>" /><br />
						
							<?php
						}
						?>
						</div>
		            	<?php 
		            
		            	} ?>
					
					
					<?php
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
