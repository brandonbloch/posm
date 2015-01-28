<?php

function posm_manage_pages($directory = "/", $level = 1) {
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
				$listing = false;
				posm_manage_pages($directory . $files[$i] . "/", $level + 1);
			} elseif (file_exists($path)) {
				$relativePath = substr($directory, 1) . $files[$i];
				$listing = true;
				if ($relativePath == "index.txt") {
					$permalink = "index";
					$indent = 0;
				} elseif (strpos($relativePath, "index.txt") !== false) {
					$permalink = substr($relativePath, 0, strpos($relativePath, "index.txt"));
					$indent = $level - 1;
				} elseif (strpos($relativePath, ".txt") !== false) {
					$permalink = substr($relativePath, 0, strpos($relativePath, ".txt"));
					$indent = $level;
				} else {
					$listing = false;
				}
			} else {
				$listing = false;
			}
			if ($listing) {
				$metadata = get_posm_metadata($relativePath);
				if ($permalink == "index") {
					$aHref = get_posm_url();
				} else {
					$aHref = get_posm_url() . "/?page=" . urlencode( $permalink );
				}
				echo '<tr>' .
				     '<td>' . str_repeat( '––', $indent ) . ' ' .
				     '<a href="' . $aHref . '" title="View &quot;' . $metadata["title"] . '&quot;">' .
				     $metadata["title"] . '</a>' .
				     '</td>';
				echo '<td class="posm-manage-icons">' .
				     '<a class="posm-manage-icon" href="' . get_posm_url() . "/?edit=" . urlencode( $permalink ) .
				     '&ref=manage" title="Edit &quot;' . $metadata["title"] . '&quot;">' .
				     '<i class="fa fa-edit"></i><span class="fa-text-hide">Edit Page</span></a>' .
				     '<a style="opacity: 0.2" class="posm-manage-icon" title="Move Page (coming soon)">' .
				     '<i class="fa fa-reorder"></i><span class="fa-text-hide">Move Page</span></a>';
				if (has_children($permalink)) {
					if ($permalink == "index") {
						$aTitle = "Cannot delete the homepage";
					} else {
						$aTitle = "Cannot delete page with children";
					}
					echo '<a style="opacity: 0.2; cursor: default" class="posm-manage-icon" title="' . $aTitle . '">' .
					     '<i class="fa fa-trash-o"></i><span class="fa-text-hide">Delete Page</span></a>';
				} else {
					echo '<a class="posm-manage-icon" href="' . get_posm_url() . "/?delete=" . urlencode($permalink) .
					     '&ref=manage" title="Delete &quot;' . $metadata["title"]. '&quot;">' .
					     '<i class="fa fa-trash-o"></i><span class="fa-text-hide">Delete Page</span></a>';
				}
				echo '</td>' .
				     '</tr>';
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
	<title>Manage Pages - <?php posm_title() ?></title>
	<?php posm_head() ?>
</head>
<body class="posm-manage <?php posm_body_classes() ?>">
<?php posm_admin_bar() ?>

<div id="posm-manage">
<!--	<div id="posm-manage-add">
			<a href="<?php echo get_posm_url() . '/?add' ?>" title="Create Page"><i class="fa fa-plus"></i><span class="fa-text-hide">Create Page</span></a>
	</div>-->
	<h1>Manage Pages</h1>
	<table cellspacing="0">
		<tr>
<!--			<th>Sort Order</th>-->
			<th>Page</th>
<!--			<th>Path</th>-->
<!--			<th>Parent</th>-->
			<th class="posm-manage-icons"></th>
		</tr>
		<?php posm_manage_pages() ?>
		<tr id="posm-manage-add-row">
			<td><a href="<?php echo get_posm_url() . "/?add" ?>"><i class="fa fa-plus" style="padding-right: 5px"></i>Add a Page</a></td>
			<td></td>
		</tr>
	</table>
</div>

</body>
</html>