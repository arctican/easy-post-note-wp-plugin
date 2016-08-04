<?php
    /*
    Plugin Name: Easy Post Note
    Plugin URI: https://wordpress.org/plugins/easy-post-note/
    Description: Lets you add a note to a post.
    Author: arcticanaudio
    Version: 1.4.1
    Author URI: http://arcticanaudio.com
    */


	include_once('plugin-options.php');


	/**
	 * Enqueue the CSS files for the plugin
	 *
	 */
	function EPN_enqueue_scripts() {
		// Font Awesome
		wp_enqueue_style( 'EPN-font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css', array(), '4.0.3' );

		// The plugin's own stylesheet
		wp_register_style( 'EPN-style', plugins_url('style.css', __FILE__) );
		wp_enqueue_style( 'EPN-style' );
	}
	add_action( 'admin_enqueue_scripts', 'EPN_enqueue_scripts' );








	/**
	*	The function that is called to add the meta box to the admin page.
	*
	*	Simply calls add_meta_box() with priority to display under post content area.
	*	Is called via a action hook (add_meta_boxes)
	*/
	function epn_add_meta_box()
	{


		$postTypes = get_post_types( ['public' => true], 'objects' );

		foreach ( $postTypes as $postType )
		{
			$postTypeInternalName = $postType->name;

			// Add meta box to the admin panel
			if (get_option("epn-show-$postTypeInternalName"))
				add_meta_box( 	'epn-meta-box-id', 		// CSS ID of meta box
								'Note', 				// Title displayed in meta box
								'epn_render_meta_box', 	// Callback function for displaying (rendering) meta box
								$postTypeInternalName, 	// Post or page type to display on
								'normal', 				// Where to place the meta box
								'high' );				// Priority (for order)
		}


	}
	add_action( 'add_meta_boxes', 'epn_add_meta_box' );





	/**
	*	The function that renders the meta box.
	*
	*	Retrieves the data from the post meta field and outputs the HTML form and data to the meta box.
	*	Is called from the function epn_add_meta_box()
	*
	*	@param	$post	The post that is being edited
	*	@see epn_add_meta_box()
	*/
	function epn_render_meta_box( $post )
	{
		// Get values for this post
		$metavalues = get_post_custom( $post->ID );
		$notedata = isset( $metavalues['epn_note_text_box'] ) ? esc_attr( $metavalues['epn_note_text_box'][0] ) : '';
		$urgentchecked = isset( $metavalues['epn_urgent_checkbox'] ) ? esc_attr( $metavalues['epn_urgent_checkbox'][0] ) : '';

		// The nonce is for checking that save code is actually intended
		wp_nonce_field( 	'epn_save_note_nonce', 	// Action name
							'epn_note_nonce' );		// Nonce name

		// Display the HTML objects in the meta box
		?>

		<p>
			<input type="checkbox" name="epn_urgent_checkbox" id="epn_urgent_checkbox" <?php checked( $urgentchecked, 'on' ); ?> />
			<label for="epn_urgent_checkbox">Urgent</label>
		</p>


		<p>
			<textarea 	name="epn_note_text_box" id="epn_note_text_box"
						style="width: 100%; min-height: 150px;"><?php echo $notedata; ?></textarea>
		</p>


		<?php
	}







	/**
	*	Function that saves the note to the database
	*
	*	Performs some checks, and strips some HTML before saving.
	*
	*/
	function epn_save_post_note( $post_id )
	{
		// Don't save if it's an autosave
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

		// Don't save if our nonce isn't there, or isn't verified
		if( !isset( $_POST['epn_note_nonce'] ) || !wp_verify_nonce( $_POST['epn_note_nonce'], 'epn_save_note_nonce' ) ) return;

		// Don't save if the user doesn't have editing privileges
		if( !current_user_can( 'edit_post' ) ) return;

		// Create an array of html tags we want to save
		// To avoid malicious code in the database
		$allowed = array(
			'a' => array(
				'href' => array(),
				'title' => array()
			),
			'br' => array(),
			'em' => array(),
			'strong' => array(),
			'b' => array(),
			'i' => array(),
			'u' => array(),
			'hr'  => array()
		);


		// Check if data is set, and update text box meta
		if( isset( $_POST['epn_note_text_box'] ) )
			update_post_meta( 	$post_id, 								// Post ID
								'epn_note_text_box', 					// The meta data key
								wp_kses( 	$_POST['epn_note_text_box'], // The new value to set after using
											$allowed ) );				// wp_kses to strip unwanted tags


		// Check if data is set and get 'urgent' checkbox value
		$chk = ( isset( $_POST['epn_urgent_checkbox'] ) && $_POST['epn_urgent_checkbox'] ) ? 'on' : 'off';

		// Update 'urgent' checkbox meta
		update_post_meta( $post_id, 'epn_urgent_checkbox', $chk );
	}
	add_action( 'save_post', 'epn_save_post_note' );






	/**
	 * Function that adds a column for the note display
	 *
	 */
	function add_note_column($columns) {
		$columns['note'] = 'Note';
		return $columns;
	}
	add_filter('manage_posts_columns', 'add_note_column');








	/**
	*	Function that shows the note status in the post list
	*
	*/
	function show_note_column($name) {
		// Get the post for the row
		global $post;

		// Select the note column
		switch ($name) {
			case 'note':

				// Get the note data
				$note = get_post_meta($post->ID, 'epn_note_text_box', true);
				$urgent = get_post_meta($post->ID, 'epn_urgent_checkbox', true);

				// If there is a note
				if ($note != "")
				{
					// Show Icons with the note when hovered over
					if ($urgent == "off")	// Non-Urgent
						echo '<a title="'.$note.'"><i style="color:grey;" class="fa fa-edit fa-2x"></i></a>';
					else 					// Urgent
						echo '<a title="'.$note.'"><i style="color:red;" class="fa fa-edit fa-2x"></i></a>';
				}
		}
	}
	add_action('manage_posts_custom_column',  'show_note_column');










?>
