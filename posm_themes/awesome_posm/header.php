<!DOCTYPE html>
<html>
<head lang="en">
	<meta charset="UTF-8">
	<meta name="robots" content="noindex, nofollow">
	<meta name="viewport" content="initial-scale=1.0, width=device-width">
	<?php
	$title = "";
	$pageTitle = get_page_title();
	if ($pageTitle != null) {
		$title = $pageTitle . " - " . get_posm_title();
	} else {
		$title = get_posm_title();
	}
	?>
	<title><?php echo $title ?></title>
	<?php posm_style_include() ?>
	<?php posm_head() ?>
</head>
<body class="<?php posm_body_classes() ?>">

<?php posm_admin_bar() ?>

<header>
	<h1><a href="<?php echo get_posm_url() ?>"><?php posm_title() ?></a></h1>
	<h3><?php posm_subtitle() ?></h3>

	<?php

	$options = array(
		'html5_nav' => false,
//		'include_home' => true,
	);

	posm_nav($options);

	?>
</header>