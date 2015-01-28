<?php

$permalink = urldecode($_GET['edit']);
if (substr($permalink, -1) == "/") {
	$permalink = $permalink . "index.txt";
} else {
	$permalink = $permalink . ".txt";
}

if (isset($_POST['posm_edit_submit'])) {
	if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
		$trimTitle = trim($_POST['posm_edit_title']);
		$trimShortname = trim($_POST['posm_edit_shortname']);
		if (!empty($trimTitle) && !empty($trimShortname)) {
			if (isset($_POST['posm_edit_order'])) {
				$order = intval($_POST['posm_edit_order']);
			} else {
				$order = 0;
			}
			$newMetadata = array(
				'title' => $trimTitle,
				'shortname' => $trimShortname,
				'order' => $order,
			);
			$success = posm_write_file($permalink, $_POST['posm_body'], $newMetadata);
			if ($success == false) {
				echo '<div id="edit-failure">Changes could not be saved.</div>';
			} else {
				if (isset($_GET['ref'])) {
					echo '<meta http-equiv="refresh" content="0; url=' . get_posm_url() . '/?edit=' . urlencode($_GET['edit']) . '&ref=' . $_GET['ref'] . '">';
				} else {
					echo '<meta http-equiv="refresh" content="0; url=' . get_posm_url() . '/?edit=' . urlencode($_GET['edit']) . '">';
				}
			}
		}
	}
}

$thisData = get_posm_metadata($permalink);

?>

<form id="posm-edit" action="" method="post">
<div id="posm_edit_container">

	<div id="posm_edit_mode_buttons"><input type="radio" form="" name="posm_edit_mode" id="posm_edit_mode_visual" class="posm-edit-mode-radio" value="visual"><label for="posm_edit_mode_visual" class="posm-edit-mode-label" style="display: none;" title="No-Code Mode"><i class="fa fa-eye"></i><span class="fa-text-hide">No-Code Mode</span></label><input type="radio" form="" name="posm_edit_mode" id="posm_edit_mode_html" class="posm-edit-mode-radio" value="html" checked><label for="posm_edit_mode_html" class="posm-edit-mode-label" style="display: none;" title="HTML Mode"><i class="fa fa-code"></i><span class="fa-text-hide">HTML Mode</span></label></div>

	<label for="posm_edit_title" style="display: none;">Page Title</label>
	<input name="posm_edit_title" id="posm_edit_title" placeholder="Page Title" value="<?php echo $thisData['title'] ?>">

	<label for="posm_body" style="display: none;">Page Content</label>
	<textarea name="posm_body" id="posm_body" cols="30" rows="10"><?php echo htmlspecialchars(posm_read_file($permalink)) ?></textarea>

	<div>
		<h2>Navigation Settings</h2>

		<div class="posm-edit-field">
		<label for="posm_edit_shortname"><span>Page Shortname</span> <span>(displayed in menus)</span></label>
		<input name="posm_edit_shortname" id="posm_edit_shortname" placeholder="<?php echo $thisData['shortname'] ?>" value="<?php echo $thisData['shortname'] ?>">
		</div>

		<div class="posm-edit-field">
		<label for="posm_edit_order"><span>Sort Order</span> <span>(1, 2, 3... then 0)</span></label>
		<input name="posm_edit_order" id="posm_edit_order" placeholder="0" type="number" min="0" value="<?php echo $thisData['order'] ?>">
		</div>
	</div>

	<input name="posm_edit_submit" type="submit" value="Save Changes">
	<input type="reset" value="Revert">

</div>
</form>


<script src="scripts/jquery.htmlClean.min.js"></script>
<script>

	$(document).ready(function(){

		var radios = $(".posm-edit-mode-radio");
		var radio_labels = $(".posm-edit-mode-label");
		var visual_button = $("#posm_edit_mode_visual");
		var textarea = $("#posm_body");
		var visualarea = $("#posm_body_visual");
		var resets = $("input[type='reset']");
		var revertLink = $("#posm_adminbar_revert_link");

		revertLink.css("display", "inline-block");
		radio_labels.css("display", "inline-block");
		visual_button.prop("checked", true);
		textarea.css("display", "none");

		visualarea.addClass("visual_mode_on");
//		visualarea.prop("contenteditable", true);
		visualarea.hallo({
			plugins: {
				'halloheadings': {
					// TODO figure out how to customize this properly
				},
				'halloformat': {
					'formattings': {
						'bold': true,
						'italic': true,
						'underline': false,
						'strikethrough': false
					}
				},
				'hallojustify': {},
				'hallolists': {
					'lists': {
						'ordered': true,
						'unordered': true
					}
				},
//				'hallolink': {}
			},
			toolbar: 'halloToolbarFixed'
		});

		radios.on('click', function(){
			if ($("#posm_edit_mode_visual").is(':checked')) {
				textarea.css("display", "none");
			} else {
				textarea.css("display", "block");
			}
		});

		// Custom keypress events in the visual editor
//		visualarea.on('keypress', function(event){
//			var code = event.keyCode || event.which;
//			if (code == 13) {
//				event.preventDefault();
//			}
//		});

		// Synchronize the code editor with the visual editor
		visualarea.on('input', function(){
//			var innards = visualarea.html();
			var innards = $.htmlClean(visualarea.html(), {
				format: true
			});
			textarea.val(innards);
		});

		// Synchronize the visual editor with the code editor
		textarea.on('input', function(){
			visualarea.html(textarea.val());
		});

		// Reset the visual editor's contents as well when Revert is clicked
		resets.on("click", function(){
			visualarea.hallo("restoreOriginalContent");
		});
		revertLink.on("click", function(event){
			visualarea.hallo("restoreOriginalContent");
			$("#posm-edit").trigger("reset");
			event.preventDefault();
		})

	});

</script>