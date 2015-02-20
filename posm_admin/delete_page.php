<?php

if (isset($_GET['delete'])) {
	$result = "";
	$permalink = urldecode($_GET['delete']);
	if (substr($permalink, -1) == "/") {
		$file = $permalink . "index.txt";
		$dir = true;
	} else {
		$file = $permalink . ".txt";
		$dir = false;
	}
	if (!file_exists("posm_content/pages/" . $file)) {
		header('Location: ' . get_posm_url());
	}
	if (isset($_POST['posm_delete_submit'])) {
		if ($dir) {
			$success = unlink("posm_content/pages/" . $file);
			if ($success) {
				$success = rmdir("posm_content/pages/" . $permalink);
				if ($success) {
					if(isset($_GET['ref']) && $_GET['ref'] == "manage") {
						header('Location: ' . get_posm_url() . "/?manage");
					} else {
						header('Location: ' . get_posm_url());
					}
				} else {
					$result = "<p>POSM deleted the file, but can't properly delete the page's folder.</p><p>It's likely that there are other files in this folder, possibly hidden files.</p><p>Please delete the folder manually at $permalink.</p>";
				}
			} else {
				$result = "<p>POSM does not have the proper permission to delete the page file. Please do this manually at $file.</p>";
			}
		} else {
			$success = unlink("posm_content/pages/" . $file);
			if ($success) {
				if(isset($_GET['ref']) && $_GET['ref'] == "manage") {
					header('Location: ' . get_posm_url() . "/?manage");
				} else {
					header('Location: ' . get_posm_url());
				}
			} else {
				$result = "<p>POSM does not have the proper permission to delete the page file. Please do this manually at $file.</p>";
			}
		}
	} else {
		$metadata = get_posm_metadata($file);
	}
} else {
	die();
}

?>

<!DOCTYPE html>
<html>
<head lang="en">
	<meta charset="UTF-8">
	<meta name="viewport" content="initial-scale=1.0, width=device-width">
	<title>Delete Page - <?php posm_title() ?></title>
	<?php posm_head() ?>
</head>
<body class="posm-delete-page <?php posm_body_classes() ?>">
<?php posm_admin_bar() ?>

<div id="posm-delete-page">

	<h1>Delete Page</h1>

	<form id="posm-delete" action="" method="post">

		<?php if ($result == "") { ?>
			<p>Are you sure you wish to delete the page &quot;<strong><?php echo $metadata["title"] ?></strong>&quot;?</p>
			<p>This cannot be undone.</p>
			<input name="posm_delete_submit" type="submit" value="Delete Page">
			<a href="<?php echo get_posm_url() . "/?manage" ?>" id="posm_cancel">Cancel</a>
		<?php } else { ?>
			<div id="posm-delete-error"><?php echo $result ?></div>
			<a href="<?php echo get_posm_url() . "/?manage" ?>" id="posm_cancel">Back to Manage</a>
		<?php } ?>

	</form>

</div>

</body>
</html>