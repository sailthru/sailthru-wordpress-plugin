<?php

class Sailthru_Meta_Box {

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

		// Retrieves the global content options. 
		$options = get_option( 'sailthru_content_settings' );

		if ( isset( $options['sailthru_interest_tag_options'] )  && !empty( $options['sailthru_interest_tag_options'] ) ) {
			$tags_checked = in_array( 'wordpress_tags', $options['sailthru_interest_tag_options'] ) ? true : false;
			$cat_checked = in_array( 'wordpress_categories', $options['sailthru_interest_tag_options'] ) ? true : false;
		}	

		// Do we send this content type? Warn the user if not. 
		if ( isset( $options['sailthru_content_post_types'] )  && !empty( $options['sailthru_content_post_types'] ) ) {
			
			if ( ! in_array($post->post_type, $options['sailthru_content_post_types'] ) ) {
				echo '<p>This content type is not enabled for Sailthru recommendations.</p><p><a href=" ' . admin_url('admin.php?page=sailthru_content_settings') .' ">Enable this content type</a>.</p>';
				return;
			}
		}



		// Retrieve an existing value from the database.
		$sailthru_meta_tags = get_post_meta( $post->ID, 'sailthru_meta_tags', true );
		$sailthru_post_expiration = get_post_meta( $post->ID, 'sailthru_post_expiration', true );
		$sailthru_sailthru_tags_extra_data = get_post_meta( $post->ID, 'sailthru_sailthru_tags_extra_data', true );

		// Set default values.
		if( empty( $sailthru_meta_tags ) ) $sailthru_meta_tags = '';
		if( empty( $sailthru_post_expiration ) ) $sailthru_post_expiration = '';
		if( empty( $sailthru_sailthru_tags_extra_data ) ) $sailthru_sailthru_tags_extra_data = array();

		/* tags */ 
		echo '<table class="form-table">';

		echo '<tr>';
		echo '<td>';
		echo '<label for="sailthru_meta_tags" class="sailthru_meta_tags_label" style="padding-bottom:8px; display:block; font-weight:bold">' . __( 'Interest Tags', 'text_domain' ) . '</label>';
		echo '<input type="text" id="sailthru_meta_tags" name="sailthru_meta_tags" class="sailthru_meta_tags_field widefat" placeholder="' . esc_attr__( '', 'text_domain' ) . '" value="' . esc_attr( $sailthru_meta_tags ) . '">';
		echo '<p class="description">' . __( 'Comma separated list of tags', 'text_domain' ) . '</p>';
		echo '</td>';
		echo '</tr>';		

		echo '<tr>';
		echo '<td>';
		echo '<label><input type="checkbox" name="sailthru_sailthru_tags_extra_data[]" class="sailthru_sailthru_tags_extra_data_field" value="' . esc_attr( 'wordpress_category' ) . '" ' . ( in_array( 'wordpress_category', $sailthru_sailthru_tags_extra_data )? 'checked="checked"' : checked( 1, esc_attr( $tags_checked ), false ) ) . '> ' . __( 'WordPress Tags', 'text_domain' ) . '</label><br>';
		echo '<label><input type="checkbox" name="sailthru_sailthru_tags_extra_data[]" class="sailthru_sailthru_tags_extra_data_field" value="' . esc_attr( 'wp_category' ) . '" ' . ( in_array( 'wp_category', $sailthru_sailthru_tags_extra_data )? 'checked="checked"' : checked( 1, esc_attr( $cat_checked ), false ) ) . '> ' . __( ' WordPress Categories', 'text_domain' ) . '</label><br>';
		echo '			<p class="description">' . __( 'Include checked items in Sailthru interest tags.', 'text_domain' ) . '</p>';
		echo '</td>';
		echo '</tr>';

		/* post expiration */ 
		echo '<tr class="line">';
		echo '<td>';
		echo '<label for="sailthru_post_expiration" class="sailthru_post_expiration_label" style="padding-bottom:8px; display:block; font-weight:bold">' . __( 'Content Expires', 'text_domain' ) . '</label>';
		echo '<input type="date" id="sailthru_post_expiration" name="sailthru_post_expiration" class="sailthru_post_expiration_field" placeholder="' . esc_attr__( '', 'text_domain' ) . '" value="' . esc_attr( $sailthru_post_expiration ) . '">';
		echo '<p class="description">' . __( 'Do not recommend this content after above date', 'text_domain' ) . '</p>';
		echo '</td>';
		echo '</tr>';

		echo '</table>';

	}

	public function save_metabox( $post_id, $post ) {

		// Sanitize user input.
		$sailthru_new_meta_tags = isset( $_POST[ 'sailthru_meta_tags' ] ) ? sanitize_text_field( $_POST[ 'sailthru_meta_tags' ] ) : '';
		$sailthru_new_post_expiration = isset( $_POST[ 'sailthru_post_expiration' ] ) ? sanitize_text_field( $_POST[ 'sailthru_post_expiration' ] ) : '';
		$sailthru_new_sailthru_tags_extra_data = isset( $_POST[ 'sailthru_sailthru_tags_extra_data' ] ) ? array_intersect( (array) $_POST[ 'sailthru_sailthru_tags_extra_data' ], array( 'wordpress_category','wp_category' ) )  : array();

		// Update the meta field in the database.
		update_post_meta( $post_id, 'sailthru_meta_tags', $sailthru_new_meta_tags );
		update_post_meta( $post_id, 'sailthru_post_expiration', $sailthru_new_post_expiration );
		update_post_meta( $post_id, 'sailthru_sailthru_tags_extra_data', $sailthru_new_sailthru_tags_extra_data );

	}

}

new Sailthru_Meta_Box;

// class Sailthru_Meta_Box {

// 	public function __construct() {

// 		if ( is_admin() ) {
// 			add_action( 'load-post.php',     array( $this, 'init_metabox' ) );
// 			add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
// 		}

// 	}

// 	public function init_metabox() {

// 		add_action( 'add_meta_boxes',        array( $this, 'add_metabox' )         );
// 		add_action( 'save_post',             array( $this, 'save_metabox' ), 10, 2 );

// 	}

// 	public function add_metabox() {

// 		add_meta_box(
// 			'sailthru',
// 			__( 'Sailthru', 'text_domain' ),
// 			array( $this, 'render_metabox' ),
// 			array( 'post', ' page' ),
// 			'side',
// 			'high'
// 		);

// 	}

// 	public function render_metabox( $post ) {

// 		// Retrieve an existing value from the database.
// 		$sailthru_meta_tags = get_post_meta( $post->ID, 'sailthru_meta_tags', true );
// 		$sailthru_post_expiration = get_post_meta( $post->ID, 'sailthru_post_expiration', true );

// 		// Set default values.
// 		if( empty( $sailthru_meta_tags ) ) $sailthru_meta_tags = '';
// 		if( empty( $sailthru_post_expiration ) ) $sailthru_post_expiration = '';

// 		echo '<div class="inside">';

// 		echo '<div>';
// 		echo '<p><label for="sailthru_meta_tags" class="sailthru_meta_tags_label">' . __( 'Interest Tags', 'text_domain' ) . '</label></p>';
// 		echo '</div>';
// 		echo '<div>';
// 		echo '<input type="text" id="sailthru_meta_tags" name="sailthru_meta_tags" class="sailthru_meta_tags_field widefat" placeholder="' . esc_attr__( '', 'text_domain' ) . '" value="' . esc_attr( $sailthru_meta_tags ) . '">';
// 		echo '</div>';

// 		echo '<div>';
// 		echo '<p><label for="sailthru_post_expiration" class="sailthru_post_expiration_label">' . __( 'Expire Content', 'text_domain' ) . '</label></p>';
// 		echo '</div>';
// 		echo '<div>';
// 		echo '<input type="date" id="sailthru_post_expiration" name="sailthru_post_expiration" class="sailthru_post_expiration_field widefat" placeholder="' . esc_attr__( '', 'text_domain' ) . '" value="' . esc_attr( $sailthru_post_expiration ) . '">';
// 		echo '</div>';

// 		echo '</div>';

// 		// // Form fields.
// 		// echo '<table class="form-table">';

// 		// echo '	<tr>';
// 		// echo '		<th><label for="sailthru_meta_tags" class="sailthru_meta_tags_label">' . __( 'Tags', 'text_domain' ) . '</label></th>';
// 		// echo '		<td>';
// 		// echo '			<input type="text" id="sailthru_meta_tags" name="sailthru_meta_tags" class="sailthru_meta_tags_field" placeholder="' . esc_attr__( '', 'text_domain' ) . '" value="' . esc_attr( $sailthru_meta_tags ) . '">';
// 		// echo '		</td>';
// 		// echo '	</tr>';

// 		// echo '	<tr>';
// 		// echo '		<th><label for="sailthru_post_expiration" class="sailthru_post_expiration_label">' . __( 'Recommendations Expire', 'text_domain' ) . '</label></th>';
// 		// echo '		<td>';
// 		// echo '			<input type="date" id="sailthru_post_expiration" name="sailthru_post_expiration" class="sailthru_post_expiration_field" placeholder="' . esc_attr__( '', 'text_domain' ) . '" value="' . esc_attr( $sailthru_post_expiration ) . '">';
// 		// echo '		</td>';
// 		// echo '	</tr>';

// 		// echo '</table>';

// 	}

// 	public function save_metabox( $post_id, $post ) {

// 		// Sanitize user input.
// 		$sailthru_new_meta_tags = isset( $_POST[ 'sailthru_meta_tags' ] ) ? sanitize_text_field( $_POST[ 'sailthru_meta_tags' ] ) : '';
// 		$sailthru_new_post_expiration = isset( $_POST[ 'sailthru_post_expiration' ] ) ? sanitize_text_field( $_POST[ 'sailthru_post_expiration' ] ) : '';

// 		// Update the meta field in the database.
// 		update_post_meta( $post_id, 'sailthru_meta_tags', $sailthru_new_meta_tags );
// 		update_post_meta( $post_id, 'sailthru_post_expiration', $sailthru_new_post_expiration );

// 	}

// }

// new Sailthru_Meta_Box;