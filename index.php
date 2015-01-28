<?php

function write_ini_file($assoc_arr, $path) {
	$content = "";

	foreach ($assoc_arr as $key => $elem) {
		if (is_array($elem)) {
			for($i = 0; $i < count($elem); $i++) {
				$content .= $key . "[] = \"" . $elem[$i] . "\"\n";
			}
		} elseif ($elem=="") {
			$content .= $key." = \n";
		} else {
			$content .= $key . " = \"" . $elem . "\"\n";
		}
	}

	if (!$handle = fopen($path, "w")) {
		return false;
	}

	$success = fwrite($handle, $content);
	fclose($handle);

	return $success;
}

if (!file_exists("posm_admin/.pasm")) {
	include "posm_admin/setup.php";
}

require_once "functions.php";
require_once "posm_admin/auth.php";

require_once "posm_themes/" . get_posm_theme() . "/index.php";

if (isset($_GET['edit'])) {
	if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
		require_once "posm_admin/edit_page.php";
	}
}