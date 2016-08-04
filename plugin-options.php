<?php


function epn_register_settings()
{
	$groupSettingName = "epn-settings-group";
	$postTypes = get_post_types( ['public' => true], 'objects' );
	foreach ( $postTypes as $postType )
	{
		$postTypeInternalName = $postType->name;
		register_setting($groupSettingName, "epn-show-$postTypeInternalName");
	}

}
add_action('admin_init', 'epn_register_settings');


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

		<form method="post" action="options.php">
			<?php
			settings_fields( 'epn-settings-group' );
			do_settings_sections( 'epn-settings-group' );
			$postTypes = get_post_types( ['public' => true], 'objects' );

			foreach ( $postTypes as $postType )
			{
				$postTypeName = $postType->labels->name;
				$postTypeInternalName = $postType->name;
				?>

				<label for='epn-show-<?=$postTypeInternalName?>'>
					<input 	name='epn-show-<?=$postTypeInternalName?>'
					type='checkbox'
					id='default_pingback_flag'
					value='1'
					<?=checked( 1, get_option( "epn-show-$postTypeInternalName", false ))?> >
					<?=$postTypeName?>
				</label>
				<br>

				<?php
			}

			submit_button(); ?>
		</form>




	</div>

	<?php
}






?>
