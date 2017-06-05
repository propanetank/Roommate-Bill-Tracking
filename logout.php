<?php
	session_start();
	require_once("config/config.php");
	if (!userLoggedIn()) {
		redirectLogin();
	}
	session_destroy();
?>
<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" href="includes/default.css" type="text/css" />
		<title><?php echo SITE_TITLE; ?> | Logout Success</title>
		<meta http-equiv="refresh" content="2; url=<?php echo PATH . "login.php"; ?>" />
	</head>
	<body>
		<div id="container">
			<section id="primary">
				<h2>Logged out!</h2>
				<p>You have been successfully logged out. Redirecting to login page...</p>
			</section>
		</div>
	</body>
</html>