	<?php
	function register_mysettings() {
	//register our settings
	register_setting( 'sailthru_forms_options', 'sailthru_customfield_name' );
}
	
	?>
	
	<div id="icon-sailthru" class="icon32"></div>
	<h2><?php _e( 'Custom Forms', 'sailthru-for-wordpress' ); ?></h2>


        <div style="display: block;"><br>
        	<div class="description">Create and manage custom forms for your subscription lists here!</div><br>
            	<label for="title">
            		New Custom Field Name: 
            		<input id="<?php echo $this->get_field_id('sailthru_customfield_name'); ?>" name="<?php echo $this->get_field_name('sailthru_customfield_name'); ?>" value="<?php echo get_option('sailthru_customfield_name'); ?>" type="text" />
            	</label>
            </p>
            <div class="description">Delete Custom Fields</div>
            <select name="">
            <option>Select...</option>
            <option>Gamertag</option>
            <option>Phone Number</option>
            
            </select>
        </div>

