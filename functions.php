<?php

ini_set("auto_detect_line_endings", true);

$posm_settings = parse_ini_file("posm_admin/settings.txt");

// Moves an element of an array at a given position to another position
// (used in add_page.php, manage_pages.php)
function moveValueByIndex( array $array, $from = null, $to = null ) {
	if ( null === $from ) {
		$from = count( $array ) - 1;
	}
	if ( ! isset( $array[ $from ] ) ) {
		throw new Exception( "Offset $from does not exist" );
	}
	if ( array_keys( $array ) != range( 0, count( $array ) - 1 ) ) {
		throw new Exception( "Invalid array keys" );
	}
	$value = $array[ $from ];
	unset( $array[ $from ] );
	if ( null === $to ) {
		array_push( $array, $value );
	} else {
		$tail = array_splice( $array, $to );
		array_push( $array, $value );
		$array = array_merge( $array, $tail );
	}
	return $array;
}

// Appends a class to a list of HTML classes (for use in the HTML class="" attribute)
function class_append($classes, $class) {
	if ($classes == "") {
		$classes = $class;
	} else {
		$classes = $classes . " " . $class;
	}
	return $classes;
}

/**
 * Returns the path of the active theme's directory
 */
function get_posm_theme_path() {
	return "posm_themes/" . get_posm_theme();
}

/**
 * Outputs various tags, scripts, and includes used by the POSM core in theme files
 */
function posm_head() {
	echo '<!-- POSM Header Includes -->' . PHP_EOL .
	     '<link rel="stylesheet" type="text/css" href="css/defaults.css">' . PHP_EOL;
	if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
		echo '<link rel="stylesheet" type="text/css" href="css/font-awesome/css/font-awesome.min.css">' . PHP_EOL .
			 '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>' . PHP_EOL .
			 '<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>' . PHP_EOL .
			 '<script src="http://rangy.googlecode.com/svn/trunk/currentrelease/rangy-core.js"></script>' . PHP_EOL .
			 '<script src="scripts/hallo.js"></script>' . PHP_EOL;
	}
}

/**
 * Echoes a path to style.css in the active theme's directory
 */
function posm_style_include() {
	echo '<link rel="stylesheet" type="text/css" href="' . get_posm_theme_path() . '/style.css">';
}

/**
 * Echoes the current POSM page classes, required for properly styling the backend
 */
function posm_body_classes() {
	$classes = "";
	// add a class for posm-home (if homepage) or posm-page (if not)
	if ( isset( $_GET['page'] ) ) {
		$permalink = urldecode($_GET['page']);
		if (substr($permalink, -1) == "/") {
			$permalink = substr($permalink, 0, -1);
		}
		$permalink = str_replace("/", "-", $permalink);
		$permalink = str_replace(" ", "-", $permalink);
		$classes = class_append($classes, "posm-page");
		$classes = class_append($classes, "posm-page-$permalink");
	} elseif ( isset( $_GET['edit'] ) ) {
		$classes = class_append($classes, "posm-edit");
	} else {
		$classes = class_append($classes, "posm-home");
	}
	// add a class if the user is logged in
	if ($_SESSION['logged_in']) {
		$classes = class_append($classes, "logged-in");
	}
	echo $classes;
}

/**
 * Returns the site title
 */
function get_posm_title() {
	global $posm_settings;
	return $posm_settings['title'];
}

/**
 * Echoes the site title
 */
function posm_title() {
	global $posm_settings;
	echo $posm_settings['title'];
}

/**
 * Returns the site subtitle
 */
function get_posm_subtitle() {
	global $posm_settings;
	return $posm_settings['subtitle'];
}

/**
 * Echoes the site subtitle
 */
function posm_subtitle() {
	global $posm_settings;
	echo $posm_settings['subtitle'];
}

/**
 * Returns the site author
 */
function get_posm_author() {
	global $posm_settings;
	return $posm_settings['author'];
}

/**
 * Echoes the site author
 */
function posm_author() {
	global $posm_settings;
	echo $posm_settings['author'];
}

/**
 * Returns the admin email address
 */
function get_posm_email() {
	global $posm_settings;
	return $posm_settings['email'];
}

/**
 * Echoes the admin email address
 */
function posm_email() {
	global $posm_settings;
	echo $posm_settings['email'];
}

// Return the name of the currently selected theme
// - not sure if this should be a public function or not
function get_posm_theme() {
	global $posm_settings;
	$default_theme = "bootstrap";
	if (isset($posm_settings['theme'])) {
		if (file_exists("posm_themes/" . $posm_settings['theme'] . "/index.php")) {
			return $posm_settings['theme'];
		} else {
			$posm_settings['theme'] = $default_theme;
			return $posm_settings['theme'];
		}
	} else {
		$posm_settings['theme'] = $default_theme;
		return $posm_settings['theme'];
	}
}

/**
 * Return the title of the current page
 */
function get_page_title() {
	if (isset($_GET['page'])) {
		$permalink = urldecode($_GET['page']);
		if (substr($permalink, -1) == "/") {
			$permalink = $permalink . "index.txt";
		} else {
			$permalink = $permalink . ".txt";
		}
		return get_posm_metadata($permalink, "title");
	} elseif (isset($_GET['edit'])) {
		$permalink = urldecode($_GET['edit']);
		if (substr($permalink, -1) == "/") {
			$permalink = $permalink . "index.txt";
		} else {
			$permalink = $permalink . ".txt";
		}
		return get_posm_metadata($permalink, "title");
	} else {
		$permalink = "index.txt";
		return get_posm_metadata($permalink, "title");
	}
}

/**
 * Echo the title of the current page
 */
function page_title() {
	echo get_page_title();
}

// Get the root URL of the current POSM installation
function get_posm_url() {
	$path = str_replace('\\', '/', __DIR__);
	$path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $path);
	return $path;
}

// Turn a file path into a POSM link (for use in the ?page= URL parameter)
function generate_posm_link($path) {
	$path = str_replace(get_posm_url() . "/", '', $path);
	$path = str_replace("posm_content/pages/", "", $path);
	$path = str_replace(".txt", "", $path);
	$path = urlencode($path);
	$link = get_posm_url() . "?page=$path";
	return $link;
}

// Return a page's metadata as an array, or a specific metadata value if a field is specified
function get_posm_metadata($file, $field = false) {
	$metadata = array();
	if ($file[0]== '/') {
		$path = str_replace(get_posm_url() . "/", '', $file);
	} elseif (strpos($file, "posm_content/pages/") === 0) {
		$path = $file;
	} else {
		$path = "posm_content/pages/$file";
	}
	$handle = fopen($path, "r");
	if ($handle) {
		while (($line = fgets($handle)) !== false && !stristr($line, "POSM-->")) {
			if (!stristr($line, "<!--")) {
				$lineParts = explode("---", $line, 2);
				$lineParts[0] = strtolower(trim($lineParts[0]));
				if (count($lineParts) > 1) {
					$lineParts[1] = trim($lineParts[1]);
					if (ctype_digit($lineParts[1])) {
						$lineParts[1] = intval($lineParts[1]);
					} elseif (is_numeric($lineParts[1])) {
						$lineParts[1] = floatval($lineParts[1]);
					}
					$metadata[$lineParts[0]] = $lineParts[1];
				} else {
					$metadata[$lineParts[0]] = false;
				}
				if ($field && $lineParts[0] == $field) {
					return $lineParts[1];
				}
			}
		}
		fclose($handle);
	} else {
		return null;
	}
	// merge default settings
	$defaults = array(
		'title' => str_replace(".txt", "", basename($file)),
		'order' => 0,
	);
	$metadata = array_merge($defaults, $metadata);
	$defaults = array(
		'shortname' => $metadata['title'],
	);
	$metadata = array_merge($defaults, $metadata);
	// return the entire array or the single requested value
	if ($field) {
		return $metadata[$field];
	} else {
		return $metadata;
	}
}

// Sort an array of files based on their order setting (metadata value)
function order_sort($files, $dir) {
	$dir = preg_replace("#posm_content/pages#", "", $dir);
	$sorted = [];
	$orders = [];
	$maxPriority = 1;
	for ($i = 0; $i < count($files); $i++) {
		$orders[$i] = 0;
		if (strstr($files[$i], ".txt")) {
			if ($dir == "") {
				$thisFile = $files[$i];
			} else {
				if ($dir[0] == '/') {
					$dir = substr( $dir, 1 );
				}
				$thisFile = "$dir/$files[$i]";
			}
			$orders[$i] = get_posm_metadata($thisFile, "order");
		} else if (file_exists("posm_content/pages/$files[$i]/index.txt")) {
			$orders[$i] = get_posm_metadata("$files[$i]/index.txt", "order");
		}
	}
	foreach ($orders as $i) {
		if ($i > $maxPriority) {
			$maxPriority = $i;
		}
	}
	for ($priority = 1; $priority <= $maxPriority; $priority++) {
		for ($i = 0; $i < count($files); $i++) {
			if ($orders[$i] == $priority) {
				$sorted[] = $files[$i];
			}
		}
	}
	for ($i = 0; $i < count($files); $i++) {
		if ($orders[$i] == 0) {
			$sorted[] = $files[$i];
		}
	}
	return $sorted;
}

// Recursively list the files in a directory (used by posm_nav and posm_children)
// - there are various options available, and these can be passed in from posm_nav and posm_children as well
function posm_tree($directory, $options = array(), $is_root = true) {

	// Merge options
	$defaults = array(
		'top_level' => false,
		'include_home' => true,
		'html5_nav' => false,
		'active_class' => "current-page",
		'wrapper_element' => true,
		'custom_class' => "",
	);
	$options = array_merge($defaults, $options);

	if (is_dir(__DIR__ . "/" . $directory)) {
		// Ignore hidden files
		$files = preg_grep('/^([^.])/', scandir(__DIR__ . "/" . $directory));

		// Get the current permalink
		if ( isset( $_GET['page'] ) ) { // if the "page" parameter was set, load the page
			$permalink = urldecode( $_GET['page'] );
			$permalink = "posm_content/pages/$permalink";
		} else {
			$permalink = "";
		}

		if (count($files) > 0) {
			natcasesort( $files );
			$files = array_values( $files );
			if (!(count($files) == 1 && $files[0] == "index.txt")) {
				$files = order_sort($files, $directory);

				if ( $options['html5_nav'] ) {
					$navElement = "nav";
				} else {
					$navElement = "ul";
				}

				$navClasses = "";
				if ( $is_root ) {
					$navClasses = $navClasses . "posm-nav";
					if ( $options['custom_class'] != '' ) {
						$navClasses = $navClasses . " " . $options['custom_class'];
					}
				}
				$navClasses = trim( $navClasses );

				if ($options['wrapper_element']) {
					if ( $navClasses == "" ) {
						echo "<$navElement>";
					} else {
						echo "<$navElement class=\"" . $navClasses . "\">";
					}
				}

				// Deal with the root index first, as it behaves differently than directory-level indexes
				if ( $is_root ) {

					if ( in_array( "index.txt", $files ) ) {
						if ( $options['include_home'] ) {
							if ( ! $options['html5_nav'] ) {
								echo "<li>";
							}
							$fileData = get_posm_metadata( "index.txt", "shortname");
							echo "<a href=\"" . get_posm_url() . "/\">" . $fileData . "</a>";
							if ( ! $options['html5_nav'] ) {
								echo "</li>";
							}
							if ( ( $key = array_search( "index.txt", $files ) ) !== false ) {
								unset( $files[ $key ] );
							}
						}
					} else {
						// No index.txt file exists, somehow.
						// This shouldn't happen unless the user manually deletes the file.
					}
				}
				$aClass = "";
				foreach ( $files as $file ) {
					$filePath    = "$directory/$file";
					$itemClasses = "";
					if ( is_dir( $filePath ) ) { // if the path is a directory, recurse
						if ( "$filePath/" == $permalink ) {
							$itemClasses .= $options['active_class'];
						} elseif ( preg_match("#^$filePath/#", $permalink) ) {
							$itemClasses .= "current-ancestor";
						}
						if ( ! $options['html5_nav'] ) {
							$liClass = "";
							if ( $itemClasses != "" ) {
								$liClass = " class=\"$itemClasses\"";
							}
							echo "<li$liClass>";
						} else {
							if ( $itemClasses != "" ) {
								$aClass = " class=\"$itemClasses\"";
							}
						}
						if ( file_exists( "$filePath/index.txt" ) ) { // check for an index file
							$fileData = get_posm_metadata( get_posm_url() . "/$filePath/index.txt", "shortname" );
							echo "<a href=\"" . generate_posm_link( "$filePath/" ) . "\"$aClass>" . $fileData . "</a>";
						} else {
							echo "<a>" . basename( get_posm_url() . "/$filePath" ) . "</a>";
						}
						if ( ! $options['top_level'] ) { // display sub-pages, if desired
							posm_tree( $filePath, $options, $is_root = false );
						}
						if ( ! $options['html5_nav'] ) {
							echo "</li>";
						}

					} else { // if the path is a file
						if ( $file != "index.txt" ) { // we are only interested in non-index files
							if ( str_replace( ".txt", "", $filePath ) == $permalink ) {
								$itemClasses .= $options['active_class'];
							} elseif ( preg_match("#^$filePath#", $permalink) ) {
								$itemClasses .= "current-ancestor";
							}
							if ( ! $options['html5_nav'] ) {
								$liClass = "";
								if ( $itemClasses != "" ) {
									$liClass = " class=\"$itemClasses\"";
								}
								echo "<li$liClass>";
							} else {
								if ( $itemClasses != "" ) {
									$aClass = " class=\"$itemClasses\"";
								}
							}
							$fileData = get_posm_metadata( get_posm_url() . "/$filePath", "shortname" );
							echo "<a href=\"" . generate_posm_link( get_posm_url() . "/$filePath" ) . "\">" . $fileData . "</a>";
							if ( ! $options['html5_nav'] ) {
								echo "</li>";
							}
						}
					}
				}
				if ($options['wrapper_element']) {
					echo "</$navElement>";
				}
			}
		}
	}

}

/**
 * Echoes the site navigation structure
 * @param array $options    The options array
 */
function posm_nav($options = array()) {
	posm_tree("posm_content/pages", $options);
}

/**
 * Echoes the children of a sub-directory as a navigation structure
 * @param $root             The permalink path for which to display children
 * @param array $options    The options array
 */
function posm_children($root, $options = array()) {
	if ($root[0] == "/") {
		$root = substr($root, 1);
	}
	$overrides = array(
		'include_home' => false,
	);
	$options = array_merge($options, $overrides);
	posm_tree("posm_content/pages/$root", $options);
}

// Check if a page has children or not
// - used by manage_page to decide if a page can be deleted or not (pages with children cannot be deleted)
function has_children($permalink) {
	if ($permalink == "index") {
		return true;
	}
	if (is_dir("posm_content/pages/" . $permalink)) {
		$files = preg_grep('/^([^.])/', scandir("posm_content/pages/" . $permalink));
		if (count($files) == 0) {
			return false;
		} elseif (count($files) == 1) {
			$files = array_values($files);
			if (basename($files[0]) == "index.txt") {
				return false;
			}
		}
	} elseif (file_exists("posm_content/pages/" . $permalink . ".txt")) {
		return false;
	}
	return true;
}

/**
 * Echoes the main page content
 * - this function must be called somewhere in a site theme, as it contains all page-specific content!
 */
function posm_content() {
	if ( isset( $_GET['login'] ) ) {            // if the login form page is requested
		posm_login_form();
	} elseif ( isset( $_GET['edit'] ) ) {       // if the page is to be edited
		if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == false) {
			posm_login_form();
		} else {
			$file = urldecode($_GET['edit']);
			if (substr($file, -1) == "/") {
				$file = $file . "index.txt";
			} else {
				$file = $file . ".txt";
			}
			echo '<div id="posm_body_visual" class="editable">' . posm_read_file($file) . '</div>';
		}
	} else {                                    // otherwise, assume we are viewing a page

		if ( isset( $_GET['page'] ) ) { // if the "page" parameter was set, load that as the page
			$permalink = urldecode($_GET['page']);
		} else { // otherwise, assume the homepage is being accessed
			$permalink = 'index';
		}
		if (substr($permalink, -1) == "/") { // link is a directory
			if (file_exists("posm_content/pages/" . $permalink)) {
				if (is_dir("posm_content/pages/" . $permalink)) { // make sure the directory exists
					if (file_exists("posm_content/pages/" . $permalink . "index.txt")) { // make sure the index file exists
						echo posm_read_file($permalink . "index.txt");
					} else {
						echo "<i>The requested directory does not have an index.</i>";
					}
				} else {
					echo "<i>The requested directory does not exist.</i>";
				}
			} else {
				echo "<i>The requested directory does not exist.</i>";
			}
		} else { // link is a file
			if (file_exists("posm_content/pages/" . $permalink . ".txt")) { // make sure the file exists
				require "posm_content/pages/" . $permalink . ".txt";
			} elseif (file_exists("posm_content/pages/" . $permalink . "/index.txt")) {
				require "posm_content/pages/" . $permalink . "/index.txt";
			} else {
				echo "<i>The requested file does not exist.</i>";
			}
		}

	}
}


/**
 * Returns a logout URL that does its best to preserve the user's location
 * - works in conjunction with appropriate parameter checking in auth.php
 * @return string   Logout URL
 */
function get_posm_logout_link() {
	if (isset($_GET['edit'])) {
		$permalink = get_posm_url() . '/?logout&edit=' . $_GET['edit'];
	} elseif (isset($_GET['page'])) {
		$permalink = get_posm_url() . '/?logout&page=' . $_GET['page'];
	} else {
		$permalink = get_posm_url() . '/?logout';
	}
	return $permalink;
}

/**
 * Generate an admin toolbar for displaying while logged in only
 */
function posm_admin_bar() {
	if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
		if ( isset( $_GET['page'] ) ) { // if the "page" parameter was set, load the page
			$permalink = urldecode($_GET['page']);
		} elseif (isset($_GET['edit'])) { // if the page is being edited
			$permalink = urldecode($_GET['edit']);
		} else { // otherwise, assume the homepage is being accessed
			$permalink = 'index';
		}
		if (isset($_GET['login']) || isset($_GET['settings']) || isset($_GET['manage']) || isset($_GET['add']) || isset($_GET['edit'])) {
			$showEditButton = false;
		} else {
			$showEditButton = true;
		}
		if (isset($_GET['edit'])) {
			$editing = true;
		} else {
			$editing = false;
		}
		echo '<div id="posm_admin_bar" class="posm-admin-bar">' .
		     '<div style="float: left">';
		if (!$editing) {
			echo '<a href="' . get_posm_url() . '" id="posm_admin_btn_home" title="Home">' .
			     '<i class="fa fa-home"></i><span class="fa-text-hide">' . get_posm_title() . '</span>' .
			     '</a>';
			echo '<a href="' . get_posm_url() . '/?manage" id="posm_admin_btn_manage_pages" title="Manage Pages">' .
			     '<i class="fa fa-bars"></i><span class="fa-text-hide">Manage Pages</span>' .
			     '</a>';
			echo '<a href="' . get_posm_url() . '/?add" id="posm_admin_btn_add_page" title="Create Page">' .
			     '<i class="fa fa-plus"></i><span class="fa-text-hide">Create Page</span>' .
			     '</a>';
		}
		if ($showEditButton) {
			echo '<a title="Edit This Page" href="' . get_posm_url() . '/?edit=' . urlencode($permalink) . '">' .
			     '<i class="fa fa-pencil"></i><span class="fa-text-hide">Edit This Page</span>' .
			     '</a>';
		}
		if ($editing) {
			if (isset($_GET['ref']) && $_GET['ref'] == 'manage') {
				$href = get_posm_url() . "/?manage";
			} elseif ($permalink == "index") {
				$href = get_posm_url() . "/";
			} else {
				$href = get_posm_url() . "/?page=" . urlencode($permalink);
			}
			echo '<a id="posm_adminbar_cancel_edit_link" title="Stop Editing" href="' . $href . '">' .
			     '<i class="fa fa-close"></i><span class="fa-text-hide">Stop Editing</span>' .
			     '</a>';
			echo '<a id="posm_adminbar_revert_link" style="display: none" title="Revert Changes" href="#">' .
			     '<i class="fa fa-undo"></i><span class="fa fa-text-hide">Revert Changes</span>' .
			     '</a>';

		}
		echo '</div>';

//		if ($editing) {
//			echo '<span id="posm_edit_bar_heading">Edit Mode</span>';
//		}

		echo '<div style="float: right; background">';

		if (!$editing) {
			echo '<a href="' . get_posm_url() . '/?settings" id="posm_admin_btn_settings" title="Settings">' .
			     '<i class="fa fa-wrench"></i><span class="fa-text-hide">Settings</span>' .
			     '</a>';
			echo '<a href="' . get_posm_logout_link() . '" id="posm_admin_btn_logout" title="Log Out">' .
			     '<i class="fa fa-sign-out"></i><span class="fa-text-hide">Log Out</span>' .
			     '</a>';
		}
		echo '</div></div>';
	}
}
