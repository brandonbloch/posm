<?php

////
// Authorization and authentication functions
////

// Generates a secure, pseudo-random password with a safe fallback.
function pseudo_rand($length) {
	if (function_exists('openssl_random_pseudo_bytes')) {
		$is_strong = false;
		$rand = openssl_random_pseudo_bytes($length, $is_strong);
		if ($is_strong === true) return $rand;
	}
	$rand = '';
	$sha = '';
	for ($i = 0; $i < $length; $i++) {
		$sha = hash('sha256', $sha . mt_rand());
		$chr = mt_rand(0, 62);
		$rand .= chr(hexdec($sha[$chr] . $sha[$chr + 1]));
	}
	return $rand;
}

// Creates a very secure hash. Uses blowfish by default with a fallback on SHA512.
function create_hash($string, &$salt = '', $stretch_cost = 10) {
	$salt = pseudo_rand(128);
	$salt = substr(str_replace('+', '.', base64_encode($salt)), 0, 22);
	if (function_exists('hash')) {
		return crypt($string, '$2a$' . $stretch_cost . '$' . $salt);
	}
	return _create_hash($string, $salt);
}

// Fallback SHA512 hashing algorithm with stretching.
function _create_hash($password, $salt) {
	$hash = '';
	for ($i = 0; $i < 20000; $i++) {
		$hash = hash('sha512', $hash . $salt . $password);
	}
	return $hash;
}

function get_ip_address() {
	$ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
	foreach ($ip_keys as $key) {
		if (array_key_exists($key, $_SERVER) === true) {
			foreach (explode(',', $_SERVER[$key]) as $ip) {
				// trim for safety measures
				$ip = trim($ip);
				// attempt to validate IP
				if (validate_ip($ip)) {
					return $ip;
				}
			}
		}
	}

	return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
}

// Ensures an ip address is both a valid IP and does not fall within a private network range.
function validate_ip($ip)
{
	if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
		return false;
	}
	return true;
}

// Trims the IP address and returns it in the format XXX.XXX.XXX.0
function trimIP($ip) {
	$pos = strrpos($ip, '.');
	if ($pos !== false) {
		$ip = substr($ip, 0, $pos+1);
	}
	return $ip . '0';
}

// if print_r($array, true) returns $string, print_r_reverse($string) returns $array
function print_r_reverse($in) {
	$lines = explode("\n", trim($in));
	if (trim($lines[0]) != 'Array') {
		// bottomed out to something that isn't an array
		return $in;
	} else {
		// this is an array, lets parse it
		if (preg_match("/(\\s{5,})\\(/", $lines[1], $match)) {
			// this is a tested array/recursive call to this function
			// take a set of spaces off the beginning
			$spaces = $match[1];
			$spaces_length = strlen($spaces);
			$lines_total = count($lines);
			for ($i = 0; $i < $lines_total; $i++) {
				if (substr($lines[$i], 0, $spaces_length) == $spaces) {
					$lines[$i] = substr($lines[$i], $spaces_length);
				}
			}
		}
		array_shift($lines); // Array
		array_shift($lines); // (
		array_pop($lines); // )
		$in = implode("\n", $lines);
		// make sure we only match stuff with 4 preceding spaces (stuff for this array and not a nested one)
		preg_match_all("/^\\s{4}\\[(.+?)\\] \\=\\> /m", $in, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
		$pos = array();
		$previous_key = '';
		$in_length = strlen($in);
		// store the following in $pos:
		// array with key = key of the parsed array's item
		// value = array(start position in $in, $end position in $in)
		foreach ($matches as $match) {
			$key = $match[1][0];
			$start = $match[0][1] + strlen($match[0][0]);
			$pos[$key] = array($start, $in_length);
			if ($previous_key != '') $pos[$previous_key][1] = $match[0][1] - 1;
			$previous_key = $key;
		}
		$ret = array();
		foreach ($pos as $key => $where) {
			// recursively see if the parsed out value is an array too
			$ret[$key] = print_r_reverse(substr($in, $where[0], $where[1] - $where[0]));
		}
		return $ret;
	}
}

// Echoes a login form
function posm_login_form() {
	global $errorMSG;
	echo '<form id="posm-login" action="" method="post">' .
	     '<div>' . $errorMSG . '</div>' .
	     '<input name="posm_login_username" placeholder="username" type="text" value="' . htmlentities($_POST['username']) . '">' .
	     '<input name="posm_login_password" placeholder="password" type="password">' .
	     '<input name="posm_login_submit" type="submit" value="Sign In">' .
	     '</form>';
}

// Page Edit - read function
function posm_read_file($file) {
	if ($file[0]== '/') {
		$path = str_replace(get_posm_url() . "/", '', $file);
	} elseif(strpos($file, "posm_content/pages/") === 0) {
		$path = $file;
	} else {
		$path = "posm_content/pages/$file";
	}
	$handle = fopen($path, "r");
	if ($handle) {
		$contents = "";
		do {
			$line = fgets($handle);
		} while ($line !== false && !stristr($line, "POSM-->"));
		while ($line !== false) {
			$line = fgets($handle);
			$contents .= $line;
		}
		fclose($handle);
		return $contents;
	} else {
		return null;
	}
}

// Page Edit - write function
function posm_write_file($file, $data, $newMetadata, $newFile = false) {
	if ($file[0]== '/') {
		$path = str_replace(get_posm_url() . "/", '', $file);
	} elseif(strpos($file, "posm_content/pages/") === 0) {
		$path = $file;
	} else {
		$path = "posm_content/pages/$file";
	}
	$contents = "<!--POSM" . PHP_EOL;
	if ($newFile) {
		$metadata = $newMetadata;
	} else {
		$metadata = get_posm_metadata( $file );
		$metadata = array_merge( $metadata, $newMetadata );
	}
	foreach ($metadata as $key => $value) {
		$contents .= "$key --- $value" . PHP_EOL;
	}
	$contents .= "POSM-->" . PHP_EOL . PHP_EOL;
	$contents .= $data;
	$result = file_put_contents($path, $contents, LOCK_EX);
	return $result;
}




// before anything, check whether the user is currently logged in to the site

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == false) { // if not logged in, set session variables

	$_SESSION['ip_address'] = trimIP(get_ip_address()) ;
	$_SESSION['user_agent'] = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '';
	$_SESSION['last_activity'] = time();

} else { // if logged in, check session validity

	// if there has been no activity for 30 minutes, destroy the session
	// otherwise, update the last activity time
	if (time() > $_SESSION['last_activity'] + 30 * 60) {
		session_unset();
		session_destroy();
	} else {
		$_SESSION['last_activity'] = time();

		// if the IP address has changed, destroy the session
		if ($_SESSION['ip_address'] !== trimIP(get_ip_address()) ) {
			session_unset();
			session_destroy();
		} else {

			// if the user agent does not validate, destroy the session
			if (!isset($_SERVER['HTTP_USER_AGENT']) || $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
				session_unset();
				session_destroy();
			} else {

				// if we make it this far, the session is still valid

			}

		}

	}
}

// if the user clicked a logout link, destroy their session and return home
if (isset($_GET['logout'])) {
	session_unset();
	session_destroy();
	if (isset($_GET['edit'])) {
		if ($_GET['edit'] == 'index') {
			$permalink = get_posm_url() . '/';
		} else {
			$permalink = get_posm_url() . '/?page=' . $_GET['edit'];
		}
	} elseif (isset($_GET['page'])) {
		$permalink = get_posm_url() . '/?page=' . $_GET['page'];
	} else {
		$permalink = get_posm_url() . '/';
	}
	header('Location: ' . $permalink);
}

$errorMSG = "";

// If the login form was submitted, authenticate the form data
if (isset($_POST['posm_login_submit'])) {
	if (empty($_POST['posm_login_username'])) {
		$errorMSG = "Enter a username.";
	} else {
		if (empty($_POST['posm_login_password'])) {
			$errorMSG = "Enter a password.";
		} else {
			$file = file_get_contents("posm_admin/.pasm");
			if ($file === false) {
				echo "<i>Could not log in: user authentication failed.</i>";
			} else {
				$array = print_r_reverse($file);
				if ($_POST['posm_login_username'] != $array['u']) {
					$errorMSG = "Incorrect username/password combination.";
				} else {
					if (hash("sha256", $array['s'] . $_POST['posm_login_password']) != $array['p']) {
						$errorMSG = "Incorrect username/password combination.";
					} else {
						$_SESSION['logged_in'] = true;

						// try diligently to redirect to the page the user was on before login
						if (isset($_GET['settings'])) {
							header('Location: ' . get_posm_url() . "/?settings");
						} elseif (isset($_GET['manage'])) {
							header('Location: ' . get_posm_url() . "/?manage");
						} elseif (isset($_GET['add'])) {
							header('Location: ' . get_posm_url() . "/?add");
						} elseif (isset($_GET['edit'])) {
							header('Location: ' . get_posm_url() . "/?edit=" . $_GET['edit']);
						} else {
							header( 'Location: ' . get_posm_url() );
						}
					}
				}
			}
		}
	}
}

if (isset($_GET['add'])) {
	if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == false) {
		if (!isset($_GET['login'])) {
			header( 'Location: ' . get_posm_url() . "/?login&add" );
		}
	} else {
		include_once "posm_admin/add_page.php";
		die();
	}
}

if (isset($_GET['delete'])) {
	if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == false) {
		header( 'Location: ' . get_posm_url() . "/?manage" );
	} else {
		include_once "posm_admin/delete_page.php";
		die();
	}
}

if (isset($_GET['manage'])) {
	if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == false) {
		if (!isset($_GET['login'])) {
			header( 'Location: ' . get_posm_url() . "/?login&manage" );
		}
	} else {
		include_once "posm_admin/manage_pages.php";
		die();
	}
}

// if the settings administration page is requested, check authentication and load the page
if (isset($_GET['settings'])) {
	if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == false) {
		if (!isset($_GET['login'])) {
			header('Location: ' . get_posm_url() . "/?login&settings");
		}
	} else {
		include_once "posm_admin/edit_settings.php";
		die();
	}
}