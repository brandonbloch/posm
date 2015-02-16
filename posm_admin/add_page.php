<?php

$trimTitle = "";
$trimShortname = "";
$error = "";
$pageOrder = 0;

if (isset($_POST['posm_edit_title'])) {
	$trimTitle = trim($_POST['posm_edit_title']);
}
if (isset($_POST['posm_edit_shortname'])) {
	$trimShortname = trim($_POST['posm_edit_shortname']);
}
if (isset($_POST['posm_edit_order'])) {
	if (is_numeric($_POST['posm_edit_order'])) {
		$pageOrder = intval($_POST['posm_edit_order']);
	}
}

if (isset($_POST['posm_addpage_submit'])) {

	if (empty($trimTitle)) {
		$error = "You must give the page a title.";
	} elseif(strip_tags($_POST['posm_edit_title']) != $_POST['posm_edit_title']) {
		$error = "The title you entered contains invalid characters.";
	} else { // title verification successful

		if (empty($trimShortname)) {
			$trimShortname = trim(substr($trimTitle, 0, 31));
			$proceed = true;
		} elseif (strip_tags($_POST['posm_edit_shortname']) != $_POST['posm_edit_shortname']) {
			$error = "The shortname you entered contains invalid characters.";
			$proceed = false;
		} else {
			$proceed = true;
		} // shortname verification successful

		if ($proceed) {

			if ( isset( $_POST['posm_edit_order'] ) && $_POST['posm_edit_order'] != "" ) {
				$trimOrder = intval( $_POST['posm_edit_order'] );
			} else {
				$trimOrder = 0;
			} // order verification successful

			$parent = $_POST['posm_edit_parent'];

			$newMetadata = array(
				'title' => $trimTitle,
				'shortname' => $trimShortname,
				'order' => $trimOrder,
			);

			if ( strpos( $parent, "/index.txt" ) !== false ) {

				$dirname = dirname( $parent );
				$newDir = $dirname . '/' . $trimShortname;

				if ( file_exists( $newDir ) ) {
					$error = "A conflicting page already exists";
				} else {
					mkdir($newDir, 0777, true);
					$success = posm_write_file($newDir . "/index.txt", "", $newMetadata, true);
					if ($success == false) {
						$error = "The file could not be created.";
					} else {
						$pLink = str_replace("posm_content/pages/", "", $parent);
						$pLink = str_replace(".txt", "", $pLink);
						if ($pLink == "index") {
							$pLink = $trimShortname . '/';
						} else {
							$pLink = $pLink . '/' . $trimShortname . '/';
						}
						header('Location: ' . get_posm_url() . '/?edit=' . urlencode($pLink));
					}
				}

			} elseif ( strpos( $parent, ".txt" ) !== false ) {

				$dirname = dirname($parent);
				$newDir = $dirname . '/' . basename($parent, ".txt");

				if (file_exists($newDir)) {
					$error = "A conflict exists within your existing site structure. Please manually clear conflicts.";
				} else {
					mkdir($newDir, 0777, true);
					rename($parent, $newDir . "/index.txt");
					mkdir ($newDir . '/' . $trimShortname, 0777, true);
					$success = posm_write_file($newDir . '/' . $trimShortname . "/index.txt", "", $newMetadata, true);
					if ($success == false) {
						$error = "The file could not be created.";
					} else {
						$pLink = str_replace("posm_content/pages/", "", $parent);
						$pLink = str_replace(".txt", "", $pLink);
						$pLink = $pLink . '/' . $trimShortname . '/';
						header('Location: ' . get_posm_url() . '/?edit=' . urlencode($pLink));
					}
				}

			}

			// 1. check if $parent ends in "index.txt"
			//    - if it does:
			//      2. get the dirname of $parent (double-check!!)
			//      3. create a new folder in $parent for our new page
			//      4. write the page metadata to "index.txt" in the new folder
			//    - if it does not, move $parent to a folder
			//      2. create a folder in the same directory as $parent, with name [[parent - ".txt"]]
			//      3. move $parent to this folder and rename "index.txt" (note its new path)
			//      4. create a new folder in this folder for our new page
			//      5. write the page metadata to "index.txt" in this new folder

		}

	}
}

function posm_choose_parent($directory = "/", $level = 0) {
	$files = preg_grep('/^([^.])/', scandir("posm_content/pages" . $directory));
	if (count($files) > 0) {
		$files = array_values($files);
		$files = order_sort($files, substr($directory, 1));
		$index = array_search("index.txt", $files);
		if ($index !== false) {
			$files = moveValueByIndex($files, $index, 0);
		}
		for ($i = 0; $i < count($files); $i++) {
			$path = "posm_content/pages" . $directory . $files[$i];
			if (is_dir($path)) {
				posm_choose_parent($directory . $files[$i] . "/", $level + 1);
			} elseif (file_exists($path)) {
				$relativePath = substr($directory, 1) . $files[$i];

				$listing = true;
				if ($relativePath == "index.txt") {
					$indent = 0;
				} elseif (strpos($relativePath, "index.txt") !== false) {
					$indent = $level;
				} elseif (strpos($relativePath, ".txt") !== false) {
					$indent = $level + 1;
				} else {
					$listing = false;
				}

				if (isset($_POST['posm_edit_parent'])) {
					if ($_POST['posm_edit_parent'] == $path) {
						$selected = ' selected="selected"';
					} else {
						$selected = '';
					}
				} else {
					$selected = '';
				}

				if ($listing) {
					echo '<option value="' . $path . '"' . $selected . '>' . str_repeat( "––", $indent ) . ' ' . get_posm_metadata( $path, "shortname" ) . '</option>';
				}
			}
		}
	}
}

?>

<!DOCTYPE html>
<html>
<head lang="en">
	<meta charset="UTF-8">
	<meta name="viewport" content="initial-scale=1.0, width=device-width">
	<title>Create Page - <?php posm_title() ?></title>
	<?php posm_head() ?>
</head>
<body class="posm-add-page <?php posm_body_classes() ?>">
<?php posm_admin_bar() ?>

<div id="posm-add-page">

	<h1>Create Page</h1>

	<form id="posm-add" action="" method="post">

		<?php if ($error) {
			echo '<div class="posm-add-error">' . $error . '</div>';
		} ?>

		<label for="posm_edit_title" style="display: none;">Page Title</label>
		<input name="posm_edit_title" id="posm_edit_title" placeholder="Page Title" value="<?php echo $trimTitle ?>">

		<div class="posm-edit-field">
			<label for="posm_edit_parent"><span>Parent Page</span></label>
			<select name="posm_edit_parent" id="posm_edit_parent">
				<?php posm_choose_parent() ?>
			</select>
		</div>

		<div>
			<h2>Navigation Settings</h2>

			<div class="posm-edit-field">
				<label for="posm_edit_shortname"><span>Page Shortname</span> <span>(displayed in menus)</span></label>
				<input name="posm_edit_shortname" id="posm_edit_shortname" placeholder="Shortname" value="<?php echo $trimShortname ?>">
			</div>

			<div class="posm-edit-field">
				<label for="posm_edit_order"><span>Sort Order</span> <span>(1, 2, 3... then 0)</span></label>
				<input name="posm_edit_order" id="posm_edit_order" placeholder="0" type="number" min="0" value="<?php echo $pageOrder ?>">
			</div>
		</div>

		<input name="posm_addpage_submit" type="submit" value="Create">
		<a href="<?php if(isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) echo $_SERVER['HTTP_REFERER'] ?>" id="posm_cancel">Cancel</a>

	</form>

</div>

<script>
	var titleField = document.getElementById("posm_edit_title");
	var shortField = document.getElementById("posm_edit_shortname");
	var synchronize = true;
	shortField.addEventListener('input', function(){
		if (shortField.value == null || shortField.value == "" || shortField.value == titleField.value) {
			synchronize = true;
		} else {
			synchronize = false;
		}
	});
	titleField.addEventListener('input', function(){
		if (shortField.value == titleField.value) {
			synchronize = true;
		}
		if (synchronize) {
			shortField.value = titleField.value.trim().substring(0, 31).trim(); // max 30 characters for the auto-shortname
		}
	});
</script>

</body>
</html>