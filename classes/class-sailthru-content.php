<?php

class Sailthru_Content_Settings {

	public function __construct() {

		// make sure the priority is above the default of 10, the meta boxes are saved first. 
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 11 );
		add_action( 'admin_init', array( $this, 'init_settings'  ), 11 );
		add_action( 'save_post', array( $this, 'sailthru_save_post' ), 11, 3 );

	}

	public function add_admin_menu() {

		add_submenu_page(
			'sailthru_configuration_page',
			esc_html__( 'Content Settings', 'text_domain' ),
			esc_html__( 'Content Settings', 'text_domain' ),
			'manage_options',
			'sailthru_content_settings',
			array( $this, 'page_layout' )
		);

	}

	public function init_settings() {

		$options = get_option( 'sailthru_content_settings' );

		register_setting(
			'sailthru_content_settings',
			'sailthru_content_settings'
		);

		add_settings_section(
			'sailthru_content_settings_section',
			'',
			false,
			'sailthru_content_settings'
		);

		add_settings_field(
			'sailthru_content_api_status',
			__( 'Content API Syncing', 'text_domain' ),
			array( $this, 'render_sailthru_content_api_status_field' ),
			'sailthru_content_settings',
			'sailthru_content_settings_section'
		);

		// Only show these fields if the status has been enabled
		if ( isset ( $options['sailthru_content_api_status'] ) && 'true' === $options['sailthru_content_api_status'] ) {
			
			add_settings_field(
				'sailthru_content_post_types',
				__( 'Included Post Types', 'text_domain' ),
				array( $this, 'render_sailthru_content_post_types_field' ),
				'sailthru_content_settings',
				'sailthru_content_settings_section'
			);
			add_settings_field(
				'sailthru_content_vars',
				__( 'Global Content Vars', 'text_domain' ),
				array( $this, 'render_sailthru_content_vars_field' ),
				'sailthru_content_settings',
				'sailthru_content_settings_section'
			);

			add_settings_field(
				'sailthru_interest_tag_options',
				__( 'Additional Interest Tags', 'text_domain' ),
				array( $this, 'render_sailthru_interest_tag_options_field' ),
				'sailthru_content_settings',
				'sailthru_content_settings_section'
			);

			add_settings_field(
				'sailthru_content_interest_tags',
				__( 'Global Interest Tag', 'text_domain' ),
				array( $this, 'render_sailthru_content_interest_tags_field' ),
				'sailthru_content_settings',
				'sailthru_content_settings_section'
			);

		}


	}

	/**
	 * Renders the page layout.
	 */
	public function page_layout() {

		// Check required user capability
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'text_domain' ) );
		}

		// Admin Page Layout
		echo '<div class="wrap">' . "\n";
		echo '	<h1>Sailthru for WordPress</h1>' . "\n";
		echo '	<form action="options.php" method="post">' . "\n";

		// render the admin tabs
		sailthru_admin_tabs('sailthru_content_settings');
		echo '<div class="postbox">';
		echo '<div class="inside">';
		settings_fields( 'sailthru_content_settings' );
		do_settings_sections( 'sailthru_content_settings' );
		echo '</div>';
		echo '</div>';
		submit_button();
		echo '</div>' . "\n";
		echo '</form>' . "\n";


	}

	/**
	 * Renders the content api status field.
	 */
	function render_sailthru_content_api_status_field() {

		// Retrieve data from the database.
		$options = get_option( 'sailthru_content_settings' );

		// Set default value.
		$value = isset( $options['sailthru_content_api_status'] ) ? $options['sailthru_content_api_status'] : '';

		// Field output.
		echo '<select name="sailthru_content_settings[sailthru_content_api_status]" class="sailthru_content_api_status_field">';
		echo '	<option value="true" ' . selected( $value, 'true', false ) . '> ' . __( 'Enabled', 'text_domain' ) . '</option>';
		echo '	<option value="false" ' . selected( $value, 'false', false ) . '> ' . __( 'Disabled', 'text_domain' ) . '</option>';
		echo '</select>';
		echo '<p class="description">' . __( 'When Content API syncing is disabled Sailthru will crawl your web site to retrieve content for the Content Library.', 'text_domain' ) . '</p>';

	}

	/**
	 * Renders the post types field.
	 */
	function render_sailthru_content_post_types_field() {

		$options = get_option( 'sailthru_content_settings' );
		// Set default value.
		$value = isset( $options['sailthru_content_post_types'] ) ? $options['sailthru_content_post_types'] : '';

		if ( false != $options ) {
			
			$post_type_args = ['public' => true];
        	$post_types = get_post_types( $post_type_args, 'names');
        	
        	// Always remove the attachment post type, we never need this. 
        	unset ($post_types['attachment']);

        	echo '<p class="description">' . __( 'Choose which type of post types should be synced to Sailthru', 'text_domain' ) . '</p>';

        	foreach ( $post_types as $type ) {
        		echo '<input type="checkbox" name="sailthru_content_settings[sailthru_content_post_types][]" class="sailthru_content_post_types_field" value="' . esc_attr( $type ) . '" ' . ( in_array( $type , isset( $options['sailthru_content_post_types'] ) ? $options['sailthru_content_post_types'] : [] )? 'checked="checked"' : '' ) . '> ' . __( ucwords ( $type ) , 'text_domain' ) . '<br>';
        	}

		}        

	}

	/**
	 * Renders the content vars field.
	 */
	function render_sailthru_content_vars_field() {

		// Retrieve data from the database.
		$options = get_option( 'sailthru_content_settings' );

		// Set default value.
		$value = isset( $options['sailthru_content_vars'] ) ? $options['sailthru_content_vars'] : '';

		// Field output.
		echo '<input type="text" name="sailthru_content_settings[sailthru_content_vars]" class="regular-text sailthru_content_vars_field" placeholder="' . esc_attr__( '', 'text_domain' ) . '" value="' . esc_attr( $value ) . '">';
		echo '<p class="description">' . __( '<p>Provide a comma separated list of vars to include.</p> <p class="small">When left blank all WordPress content type attributes will be synced with Sailthru.</p>', 'text_domain' ) . '</p>';

	}

	/**
	 * Renders the interest tag options field.
	 */
	function render_sailthru_interest_tag_options_field() {

		// Retrieve data from the database.
		$options = get_option( 'sailthru_content_settings' );

		// Set default value.
                $value = isset( $options['sailthru_interest_tag_options'] ) ? $options['sailthru_interest_tag_options'] : array();

		// Field output.
		echo '<input type="checkbox" name="sailthru_content_settings[sailthru_interest_tag_options][]" class="sailthru_interest_tag_options_field" value="' . esc_attr( 'wordpress_tags' ) . '" ' . ( in_array( 'wordpress_tags', $value )? 'checked="checked"' : '' ) . '> ' . __( 'WordPress Tags', 'text_domain' ) . '<br>';
		echo '<input type="checkbox" name="sailthru_content_settings[sailthru_interest_tag_options][]" class="sailthru_interest_tag_options_field" value="' . esc_attr( 'wordpress_categories' ) . '" ' . ( in_array( 'wordpress_categories', $value )? 'checked="checked"' : '' ) . '> ' . __( 'WordPress Categories', 'text_domain' ) . '<br>';

	}


	/**
	 * Renders the interest tags field.
	 */
	function render_sailthru_content_interest_tags_field() {

		// Retrieve data from the database.
		$options = get_option( 'sailthru_content_settings' );

		// Set default value.
		$value = isset( $options['sailthru_content_interest_tags'] ) ? $options['sailthru_content_interest_tags'] : '';

		// Field output.
		echo '<input type="text" name="sailthru_content_settings[sailthru_content_interest_tags]" class="regular-text sailthru_content_interest_tags_field" placeholder="' . esc_attr__( '', 'text_domain' ) . '" value="' . esc_attr( $value ) . '">';

	}

	 /**
	 * Generates the output for the Content API call. 
	 *
	 * @param integer $post_id
	 * @param object $post
	 */

	function generate_payload( $post, $post_id ) {
		
		$data = array();
		$data['url'] = get_permalink( $post->ID );
		$data['title']             = $post->post_title;
		$data['author']            = get_the_author_meta( 'display_name', $post->post_author );
		$data['date']              = $post->post_date;
		$data['vars']['post_type'] = $post->post_type;
		$data['spider']            = 1;

		if ( ! empty( $post->post_excerpt ) ) {
			$data['description'] = $post->post_excerpt;
		} else {
			$data['description'] = wp_trim_words( $post->post_content, 250, '' );
		}
		// image & thumbnail
		if ( has_post_thumbnail( $post->ID ) ) {
			$image                          = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
			$thumb                          = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'concierge-thumb' );
			$post_image                     = $image[0];
			$data['images']['full']['url']  = esc_attr( $post_image );
			$post_thumbnail                 = $thumb[0];
			$data['images']['thumb']['url'] = $post_thumbnail;
		}

		$data['tags'] = $this->generate_tags( $post->ID);

		// Apply any filters to the tags. 
		$data['tags'] = apply_filters( 'sailthru_horizon_meta_tags', ['sailthru.tags' => $data['tags'] ] ) ; 

		// Check if the filter has returned sailthru.tags and convert to string. 
		if ( is_array( $data['tags'] ) && isset ( $data['tags']['sailthru.tags'] ) ) {
			$data['tags'] =  $data['tags']['sailthru.tags']; 
		}

		$post_expiration = get_post_meta( $post->ID, 'sailthru_post_expiration', true );
		if ( ! empty( $post_expiration ) ) {
			$data['expire_date'] = esc_attr( $post_expiration );
		} else {
			// set the expiry date in the future as you can't unset the value via the API
			$data['expire_date'] = date( 'Y-m-d', strtotime( '+5 years' ) );
		}

		// get all the custom fields and add them to the vars
		$custom_fields = get_post_custom( $post_id );
		// exclude  tags
		$exclude_fields = array( '_edit_lock', '_edit_last', '_encloseme', ' sailthru_meta_tags', 'sailthru_post_expiration' );

		foreach ( $custom_fields as $key => $val ) {

			if ( ! in_array( $key, $exclude_fields, true ) ) {

				if ( is_array( $val ) ) {
					$data['vars'][ $key ] = implode( ',', $val );
				} else {
					$data['vars'][ $key ] = $val;
				}
			}
		}

		// Get the Whitelisted vars from the settings screen. 
		$whitelist = explode(', ', $sailthru['content_vars']);

		// Apply vars whitelist filtering to only include vars that are white listed. 
		$whitelist = apply_filters( 'sailthru_content_whitelist_vars', $whitelist);
		
		// Remove everything except the whitelisted vars.
		foreach ( $whitelist as $key ) {

			if ( isset ( $data['vars'][$key]) )  {
				unset( $data['vars'][$key] );
			}
		}

		// If there are no vars left, remove it as a parameter. 
		if ( empty ($data['vars'] ) ) {
			unset( $data['vars'] );
		}

		return $data;
	}


	 /**
	 * Generates the output of the interest tags for both the Content API and the meta tags. 
	 *
	 * @param integer $post_id
	 */

	function generate_tags( $post_id ) {
		
		$options = get_option( 'sailthru_content_settings' );
		$post_tags = get_post_meta( $post->ID, 'sailthru_meta_tags', true );
		$post_tag_options = get_post_meta( $post->ID, 'sailthru_sailthru_tags_extra_data', true );

		// Get the Sailthru Tags and configuration options. 
		$post_tags = get_post_meta( $post_id, 'sailthru_meta_tags', true );
		$post_tag_options = get_post_meta( $post_id, 'sailthru_sailthru_tags_extra_data', true );
		
		// Add WordPress tags if option set. 
		if ( isset( $options['sailthru_interest_tag_options'] ) && in_array( 'wordpress_tags',$options['sailthru_interest_tag_options'] ) ) {
			
			$wp_tags = get_the_tags();
			if ( $wp_tags ) {
				$post_tags .= ',' .esc_attr( implode( ',', wp_list_pluck( $wp_tags, 'name' ) ) );
			}
		}

		// Add WordPress categories if option set. 
		if ( in_array( 'wordpress_category', $post_tag_options ) ) {
			$post_categories = get_the_category( $post->ID );
			foreach ( $post_categories as $post_category ) {
				$post_tags .= ','. $post_category->name;
			}
		}

		return $post_tags;
	}

	/* TODO - remember to set the defaults on plugin activation */

	/**
	 * Capture the saving of a post and make a Content API call to add
	 * the page details and tags to Sailthru's Horizon API for recommendations
	 *
	 * @param integer $post_id
	 */

	function sailthru_save_post( $post_id, $post, $update ) {

		// Get the content options to see if we want to fire the API. 
		$options = get_option( 'sailthru_content_settings' );

		// Check to see if Content API is disabled in the UI
		if( !isset ($options['sailthru_content_api_status'] ) || "false" === $options['sailthru_content_api_status'] ) {
			return;
		}

		// See if a filter has disabled the content API, this may be done to override a specific use case. 
		if ( false === apply_filters( 'sailthru_content_api_enable', true ) ) {
			return;
		}


		if (in_array ( $post->post_type, $options['sailthru_content_post_types'] ) ) {


			if ( 'publish' === $post->post_status ) {
				// Make sure Sailthru is setup
				if ( get_option( 'sailthru_setup_complete' ) ) {
					$sailthru   = get_option( 'sailthru_setup_options' );
					$api_key    = $sailthru['sailthru_api_key'];
					$api_secret = $sailthru['sailthru_api_secret'];
					$client     = new WP_Sailthru_Client( $api_key, $api_secret );
					try {
						
						if ( $client ) {
							$data = $this->generate_payload($post, $post_id);
							// Make the API call to Sailthru
							$api = $client->apiPost( 'content', $data );
						}

					} catch ( Sailthru_Client_Exception $e ) {
						write_log($e);
						return;
					}
				}
			}
		}
	}

}

new Sailthru_Content_Settings;
