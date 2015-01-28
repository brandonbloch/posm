<!DOCTYPE html>
<html>
<head>

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


	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">

	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>

	<?php posm_head() ?>
</head>

<body class="<?php posm_body_classes() ?>">

<?php posm_admin_bar() ?>

<nav class="navbar navbar-inverse">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php echo get_posm_url() ?>"><?php posm_title() ?></a>
		</div>
		<div id="navbar" class="collapse navbar-collapse">
			<ul class="nav navbar-nav">
				<?php posm_nav(array('wrapper_element' => false, 'top_level' => true, 'include_home' => false, 'active_class' => 'active')) ?>
			</ul>
		</div>
	</div>
</nav>

<div class="container">
	<div class="jumbotron">
		<h1><?php posm_title() ?></h1>
		<p class="lead"><?php posm_subtitle() ?></p>
	</div>

	<?php posm_content() ?>
</div>

</body>
</html>