<?php
	require_once("config/config.php");
	session_start();
	checkDatabaseConn();
	if (userLoggedIn()) {
		redirectHome();
	}
	ssl();
	if(isset($_COOKIE['username']))
		$username = $_COOKIE['username'];
	if(isset($_GET['username']))
		$username = $_GET['username'];
		
?>

<!DOCTYPE HTML>
<html>
	<head>
		<link rel="stylesheet" href="includes/default.css" type="text/css" />
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script src="includes/navbar.js"></script>
		<title><?php echo SITE_TITLE; ?> | Login</title>
	</head>
	<body>
		<section id="nav">
			<?php require("includes/nav.php"); ?>
		</section>
		<div id="container">
			<section id="primary">
				<h1><?php echo $page; ?></h1>
				<h2>Login</h2>
				<form action="<?php echo PATH; ?>/includes/login.php" method="post">
					<label for="username">Username: </label><input type="text" name="username" id="username" value="<?php echo $username; ?>" maxlength="20" autofocus/><br />
					<label for="password">Password: </label><input type="password" name="password" id="password" maxlength="25" /><br />
					<label for="remember">Remember Me: </label><input type="checkbox" name="remember" id="remember" value="yes" checked /><br />
					<input type="submit" name="login" id="login" value="Login" />
					<span id="error">
						<?php if(isset($_GET['error'])) {
							echo "<p class=\"err\">" . $_SESSION['errtxt'] . "</p>";
							unset($_SESSION['errtxt']);
						} ?>
					</span>
				</form>
			</section>
		</div>
	</body>
</html>