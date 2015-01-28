<?php global $posm_settings;

if (isset($_POST['settingSubmit'])) {
	if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
		$replace      = array();
		$trimTitle    = trim( $_POST['posm_title'] );
		$trimSubtitle = trim( $_POST['posm_subtitle'] );
		$trimAuthor   = trim( $_POST['posm_author'] );
		$trimEmail    = trim( $_POST['posm_email'] );
		if ( isset( $_POST['posm_title'] ) && ! empty( $trimTitle ) ) {
			if ( strip_tags( $_POST['posm_title'] ) == $_POST['posm_title'] ) {
				$replace['title'] = $trimTitle;
			}
		}
		if ( strip_tags( $_POST['posm_subtitle'] ) == $_POST['posm_subtitle'] ) {
			$replace['subtitle'] = $trimSubtitle;
		}
		if ( isset( $_POST['posm_theme'] ) && ! empty( $_POST['posm_theme'] ) ) {
			$replace['theme'] = $_POST['posm_theme'];
		}
		if ( isset( $_POST['posm_author'] ) && ! empty( $trimAuthor ) ) {
			if ( strip_tags( $_POST['posm_author'] ) == $_POST['posm_author'] ) {
				$replace['author'] = $trimAuthor;
			}
		}
		if ( isset( $_POST['posm_email'] ) && ! empty( $trimEmail ) ) {
			if ( filter_var( $_POST['setupEmail'], FILTER_VALIDATE_EMAIL ) ) {
				$replace['email'] = $trimEmail;
			}
		}
		$replace = array_merge( $posm_settings, $replace );
		$success = write_ini_file( $replace, "posm_admin/settings.txt" );
		if ( $success ) {
			$posm_settings   = parse_ini_file( "posm_admin/settings.txt" );
			$success_message = 'Settings saved. <a href="' . get_posm_url() . '"><span>View changes</span></a>';
		}
	} else {
		$success = false;
	}
} else {
	$success = false;
}

?>
<!DOCTYPE html>
<html>
<head lang="en">
	<meta charset="UTF-8">
	<meta name="viewport" content="initial-scale=1.0, width=device-width">
	<title>Settings - <?php posm_title() ?></title>
	<?php posm_head() ?>
</head>
<body class="posm-settings <?php posm_body_classes() ?>">
<?php posm_admin_bar() ?>

	<form id="posm-settings" action="" method="post">

		<h1>Settings</h1>

		<?php if ($success) {
			echo '<div id="posm-setting-success">' . $success_message . '</div>';
		} ?>

		<br>

		<h2>Site Settings</h2>

		<label for="posm_title">Title</label>
		<input name="posm_title" id="posm_title" placeholder="<?php posm_title() ?>" type="text" value="<?php posm_title() ?>">
		<br>
		<label for="posm_subtitle">Description</label>
		<input name="posm_subtitle" id="posm_subtitle" type="text" value="<?php posm_subtitle() ?>">

			<?php

			$files = preg_grep('/^([^.])/', scandir("./posm_themes"));

			if (count($files) > 1) {

				?>
				<br>
				<label for="posm_theme">Theme</label>
				<select name="posm_theme" id="posm_theme" required="required" form="posm-settings">
				<?php

				foreach ( $files as $file ) {
					if ( file_exists( "./posm_themes/" . $file . '/index.php' ) ) {
						if ( $file == get_posm_theme() ) {
							$selected = ' selected="selected"';
						} else {
							$selected = '';
						}
						echo '<option value="' . $file . '"' . $selected . '>' . $file . '</option>';
					}
				}

				?>
				</select>
				<?php

			}

			?>

		<h2>Administrator Settings</h2>

		<label for="posm_author">Name</label>
		<input name="posm_author" id="posm_author" placeholder="<?php posm_author() ?>" type="text" value="<?php posm_author() ?>">
		<br>
		<label for="posm_email">Email Address</label>
		<input name="posm_email" id="posm_email" placeholder="<?php posm_email() ?>" type="email" value="<?php posm_email() ?>">
		<br>
		<input name="settingSubmit" type="submit" value="Save Changes">
		<input type="reset" value="Revert">
	</form>

</body>
</html>