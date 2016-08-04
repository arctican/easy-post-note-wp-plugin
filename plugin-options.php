<?php




function epn_add_settings_menu()
{
	add_submenu_page( 'options-general.php', 'Easy Post Note Settings', 'Easy Post Note','manage_options', 'easy-post-note-settings', 'epn_show_settings_window' );
}
add_action('admin_menu', 'epn_add_settings_menu');

function epn_show_settings_window()
{
	?>
	<div class='wrap'>
		<h1>Easy Post Note Settings</h1>
		<p>Here you can edit the settings for the East Post Note plugin.</p>


		<h2 class="title">Show on post types</h2>
		<?php
			$postTypes = get_post_types( ['public' => true], 'objects' );

			foreach ( $postTypes as $postType )
			{
				$postTypeName = $postType->labels->name;
				$postTypeInternalName = $postType->name;

				echo "<label for='epn-show-$postTypeInternalName'>";
				echo "<input name='epn-show-$postTypeInternalName' type='checkbox' id='default_pingback_flag' value='1' checked='checked'>$postTypeName</label><br>";
			}

		?>

	</div>

	<?php
}
































	?>
