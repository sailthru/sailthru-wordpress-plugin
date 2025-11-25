<?php

class Sailthru_Meta_Box {

	// Represents the nonce value used to save the post meta
	private $nonce = 'sailthru_metabox_nonce';

	public function __construct() {

		if ( is_admin() ) {
			add_action( 'load-post.php',     array( $this, 'init_metabox' ) );
			add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
		}

	}

	public function init_metabox() {

		add_action( 'add_meta_boxes',        array( $this, 'add_metabox' )         );
		add_action( 'save_post',             array( $this, 'save_metabox' ), 10, 2 );

	}

	public function add_metabox() {

		add_meta_box(
			'sailthru',
			__( 'Sailthru', 'text_domain' ),
			array( $this, 'render_metabox' ),
			array( 'post', ' page' ),
			'side',
			'high'
		);

	}

	public function render_metabox( $post ) {

		// Add nonce for security
		wp_nonce_field( plugin_basename( __FILE__ ), $this->nonce );

		// Retrieves the global content options. 
		$options = get_option( 'sailthru_content_settings' );

		if ( isset( $options['sailthru_interest_tag_options'] )  && !empty( $options['sailthru_interest_tag_options'] ) ) {
			$tags_checked = in_array( 'wordpress_tags', $options['sailthru_interest_tag_options'], true ) ? true : false;
			$cat_checked = in_array( 'wordpress_categories', $options['sailthru_interest_tag_options'], true ) ? true : false;
		}	

		// Do we send this content type? Warn the user if not. 
		if ( isset( $options['sailthru_content_post_types'] )  && !empty( $options['sailthru_content_post_types'] ) ) {
			
			if ( ! in_array($post->post_type, $options['sailthru_content_post_types'] ) ) {
				echo '<p>This content type is not enabled for Sailthru recommendations.</p><p><a href=" ' . esc_url( admin_url( 'admin.php?page=sailthru_content_settings') ) .' ">Enable this content type</a>.</p>';
				return;
			}
		}

		// Retrieve an existing value from the database.
		$sailthru_meta_tags = get_post_meta( $post->ID, 'sailthru_meta_tags', true );
		$sailthru_post_expiration = get_post_meta( $post->ID, 'sailthru_post_expiration', true );

		// Set default values.
		if( empty( $sailthru_meta_tags ) ) $sailthru_meta_tags = '';
		if( empty( $sailthru_post_expiration ) ) $sailthru_post_expiration = '';

		/* tags */ 
		echo '<table class="form-table">';

		echo '<tr>';
		echo '<td>';
		echo '<label for="sailthru_meta_tags" class="sailthru_meta_tags_label" style="padding-bottom:8px; display:block; font-weight:bold">' . esc_attr__( 'Interest Tags', 'text_domain' ) . '</label>';
		echo '<input type="text" id="sailthru_meta_tags" name="sailthru_meta_tags" class="sailthru_meta_tags_field widefat" placeholder="' . esc_attr__( '', 'text_domain' ) . '" value="' . esc_attr( $sailthru_meta_tags ) . '">';
		echo '<p class="description">' . esc_attr__( 'Comma separated list of tags', 'text_domain' ) . '</p>';
		echo '</td>';
		echo '</tr>';		

		if ($tags_checked || $cat_checked) {
			echo '<tr>';
			echo '<td>';
			echo '<small>' . esc_attr__( 'Your settings include', 'text_domain' ) . '';

			if ($cat_checked) {
				
				if (true === $cat_checked) {
					echo  esc_attr__( 'WordPress categories', 'text_domain' ) ;
				}

				if ($cat_checked && $tags_checked) {
					echo ' and ';
				}

				if (true === $tags_checked) {
					echo  esc_attr__( 'WordPress tags', 'text_domain' ) ;
				}

				echo 'in Sailthru interest tags.</small>';
			}
			echo '</td>';
			echo '</tr>';
		}

		/* post expiration */ 
		echo '<tr class="line">';
		echo '<td>';
		echo '<label for="sailthru_post_expiration" class="sailthru_post_expiration_label" style="padding-bottom:8px; display:block; font-weight:bold">' . esc_attr__( 'Content Expires', 'text_domain' ) . '</label>';
		echo '<input type="date" id="sailthru_post_expiration" name="sailthru_post_expiration" class="sailthru_post_expiration_field" placeholder="' . esc_attr__( '', 'text_domain' ) . '" value="' . esc_attr( $sailthru_post_expiration ) . '">';
		echo '<p class="description">' . esc_attr__( 'Do not recommend this content after above date', 'text_domain' ) . '</p>';
		echo '</td>';
		echo '</tr>';

		echo '</table>';

	}

	public function save_metabox( $post_id, $post ) {

		$is_valid_nonce = ( isset( $_POST[ $this->nonce ] ) && wp_verify_nonce( $_POST[ $this->nonce ], plugin_basename( __FILE__ ) ) );

		// First, make sure the user can save the post
		if ( $is_valid_nonce && $this->user_can_save( $post_id ) && current_user_can( 'edit_post', $post_id ) ) {

			// Sanitize user input.
			$sailthru_new_meta_tags = isset( $_POST[ 'sailthru_meta_tags' ] ) ? sanitize_text_field( $_POST[ 'sailthru_meta_tags' ] ) : '';
			$sailthru_new_post_expiration = isset( $_POST[ 'sailthru_post_expiration' ] ) ? sanitize_text_field( $_POST[ 'sailthru_post_expiration' ] ) : '';

			// Update the meta field in the database.
			update_post_meta( $post_id, 'sailthru_meta_tags', $sailthru_new_meta_tags );
			update_post_meta( $post_id, 'sailthru_post_expiration', $sailthru_new_post_expiration );
		}

	}

	/**
	 * Determines whether or not the current user has the ability to save meta data associated with this post.
	 *
	 * @param int     $post_id The ID of the post being save
	 * @param bool    Whether or not the user has the ability to save this post.
	 */
	function user_can_save( $post_id ) {

		$is_autosave = wp_is_post_autosave( $post_id );
		$is_revision = wp_is_post_revision( $post_id );

		// Return true if the user is able to save; otherwise, false.
		return ! ( $is_autosave || $is_revision );

	}

}

new Sailthru_Meta_Box;
