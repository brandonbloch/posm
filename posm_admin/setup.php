<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Setup wizard for POSM

if (file_exists("posm_admin/.pasm")) {
	header('Location: ./');
}

$setupError = "";
if (isset($_POST['setupSubmit'])) {

	if (empty($_POST['siteTitle'])) {
		$setupError = "You must name your website with a title.";
	} elseif (strip_tags($_POST['siteTitle']) != $_POST['siteTitle']) {
		$setupError = "Your website title contains invalid characters.";
	} elseif(strip_tags($_POST['siteSubtitle']) != $_POST['siteSubtitle']) {
		$setupError = "Your website subtitle contains invalid characters.";
	} else { // title/subtitle verification successful

		if (empty($_POST['setupUser'])) {
			$setupError = "You must enter a username.";
		} elseif(strip_tags($_POST['setupUser']) != $_POST['setupUser']) {
			$setupError = "Your username contains invalid characters.";
		} elseif (strlen($_POST['setupUser']) < 6) {
			$setupError = "Your username must be at least 6 characters long.";
		} elseif (strlen($_POST['setupUser']) > 20) {
			$setupError = "Your username cannot be more than 20 characters long.";
		} else { // username verification successful

			if (empty($_POST['setupEmail'])) {
				$setupError = "You must sign up with an email address.";
			} elseif (strip_tags($_POST['setupEmail']) != $_POST['setupEmail']) {
				$setupError = "Please enter a valid email address.";
			} elseif (!filter_var($_POST['setupEmail'], FILTER_VALIDATE_EMAIL)) {
				$setupError = "Please enter a valid email address.";
			} elseif ($_POST['setupEmail'] != $_POST['setupEmail2']) {
				$setupError = "The email addresses below do not match.";
			} else { // email address verification successful

				if (empty($_POST['setupPass'])) {
					$setupError = "You must enter a password.";
				} elseif (strip_tags($_POST['setupPass']) != $_POST['setupPass']) {
					$setupError = "Your password contains invalid characters.";
				} elseif (strlen($_POST['setupPass']) < 8) {
					$setupError = "Your password must be at least 8 characters long.";
				} elseif (strlen($_POST['setupPass2']) > 30) {
					$setupError = "Your password cannot be more than 30 characters long.";
				} elseif ($_POST['setupPass'] != $_POST['setupPass2']) {
					$setupError = "The passwords below do not match.";
				} else { // password verification successful

					$salt = mcrypt_create_iv(40);
					$pass = hash("sha256", $salt . $_POST['setupPass']);
					$creds = array(
						'u' => $_POST['setupUser'],
						's' => $salt,
						'p' => $pass
					);
					$result = file_put_contents("posm_admin/.pasm", print_r($creds, true), LOCK_EX);
					if ($result === false) {
						$setupError = "An error occurred creating the account file.";
					} else { // if the login file was written successfully
						$settings = array(
							'title' => $_POST['siteTitle'],
							'theme' => 'bootstrap',
							'email' => $_POST['setupEmail'],
						);
						if (isset($_POST['siteSubtitle']) && !empty($_POST['siteSubtitle'])) {
							$settings['subtitle'] = $_POST['siteSubtitle'];
						}
						write_ini_file($settings, "posm_admin/settings.txt");
						header('Location: ./?login');
					}

				}

			}

		}

	}

}

$siteTitle = "";
$siteSubtitle = "";
$setupUser = "";
$setupEmail = "";
$setupEmail2 = "";

if (isset($_POST['siteTitle']) && !empty($_POST['siteTitle'])) {
	$siteTitle = htmlentities($_POST['siteTitle']);
}
if (isset($_POST['siteSubtitle']) && !empty($_POST['siteSubtitle'])) {
	$siteSubtitle = htmlentities($_POST['siteSubtitle']);
}
if (isset($_POST['setupUser']) && !empty($_POST['setupUser'])) {
	$setupUser = htmlentities($_POST['setupUser']);
}
if (isset($_POST['setupEmail']) && !empty($_POST['setupEmail'])) {
	$setupEmail = htmlentities($_POST['setupEmail']);
}
if (isset($_POST['setupEmail2']) && !empty($_POST['setupEmail2'])) {
	$setupEmail2 = htmlentities($_POST['setupEmail2']);
}


?>

<!DOCTYPE html>
<html>
	<head lang="en">
		<meta charset="UTF-8">
		<meta name="viewport" content="initial-scale=1.0, width=device-width">
		<title>Get Started with POSM</title>
		<link rel="stylesheet" type="text/css" href="css/defaults.css">
	</head>

<body class="posm-initialize">

	<form id="posm-setup" action="" method="post">

		<h1>Welcome to POSM</h1>
		<p>We need to set up a few things before you can get started.</p>
		<br>

		<div id="setupError"><?php echo $setupError ?></div>

		<h2>Site Settings</h2>

		<label for="siteTitle">Title</label>
		<input name="siteTitle" id="siteTitle" placeholder="My Website" type="text" value="<?php echo $siteTitle ?>">
		<br>
		<label for="siteSubtitle">Description</label>
		<input name="siteSubtitle" id="siteSubtitle" placeholder="Optional" type="text" value="<?php echo $siteSubtitle ?>">

		<h2>Create Account</h2>

		<label for="setupUser">Username</label>
		<input name="setupUser" id="setupUser" placeholder="username (6-20 letters/numbers)" type="text" value="<?php echo $setupUser ?>">
		<br>
		<label for="setupEmail">Admin Email</label>
		<input name="setupEmail" id="setupEmail" placeholder="me@example.com" type="email" value="<?php echo $setupEmail ?>">
		<br>
		<label for="setupEmail2">Confirm Email</label>
		<input name="setupEmail2" id="setupEmail2" placeholder="me@example.com" type="email" value="<?php echo $setupEmail2 ?>">
		<br>
		<label for="setupPass">Password</label>
		<input name="setupPass" id="setupPass" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull; (8-30 letters/numbers)" type="password">
		<br>
		<label for="setupPass2">Confirm Password</label>
		<input name="setupPass2" id="setupPass2" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;" type="password">

		<br>
		<input name="setupSubmit" id="setupSubmit" type="submit" value="Create Account">
	</form>

</body>
</html>

<?php die();