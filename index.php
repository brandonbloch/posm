<?php

// Write an array to a given path as an INI file
// - parse_ini_file is already a built-in PHP function, but this isn't
// - this is used in setup.php, before even auth.php is loaded, so it has to go here
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

// check for the user/password file
// - if it doesn't exist, take us to the New User screen
if (!file_exists("posm_admin/.pasm")) {
	include "posm_admin/setup.php";
}

// if we make it this far, include functions and check authorization/authentication
require_once "functions.php";
require_once "posm_admin/auth.php";

// include the current theme file
require_once "posm_themes/" . get_posm_theme() . "/index.php";

// if the Edit Page screen is requested, include the editor scripts and page elements
if (isset($_GET['edit'])) {
	if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
		require_once "posm_admin/edit_page.php";
	}
}