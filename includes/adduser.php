<?php
	require_once(dirname( __FILE__ , 2) . "/config/config.php");
	session_start();
	checkDatabaseConn();
	if (!userLoggedIn()) {
		redirectLogin();
	}
	if ($_SESSION['role'] != 'ADMIN') {
		header("Location: " . PATH . "/dashboard.php");
	}

	if ($_SERVER['REQUEST_METHOD'] != 'POST')
		header("Location: " . PATH . "/dashboard.php");

	// Check the input of the user
	if (!empty($_POST['username'])) {
		$username = $_POST['username'];
		if (!preg_match("/[a-zA-Z]+/", $username))
			$_SESSION['usernameErrtxt'] = "Invalid input for Username, can only contain a-z, A-Z, 0-9";
	} else {
		$_SESSION['usernameErrtxt'] = "Username cannot be left blank";
	}
	if (!empty($_POST['name'])) {
		$name = sanitizeData($_POST['name']);
		if (!preg_match("/[a-zA-Z ]+/", $name))
			$_SESSION['nameErrtxt'] = "Invalid input for Name. Only a-z, A-Z, and spaces are allowed.";
	} else {
		$_SESSION['nameErrtxt'] = "Name cannot be left blank";
	}
	if (!empty($_POST['email'])) {
		$email = $_POST['email'];
		if (!filter_var($email, FILTER_VALIDATE_EMAIL))
			$_SESSION['emailErrtxt'] = "Invalid input for Email.";
	}
	if (!empty($_POST['paypal'])) {
		$paypal = $_POST['paypal'];
		if (!preg_match("/[a-zA-Z0-9]+/", $paypal))
			$_SESSION['paypalErrtxt'] = "Invalid input for PayPal. Only a-z, A-Z, and 0-9 are allowed.";
	}

	if (isset($_SESSION['usernameErrtxt']) || isset($_SESSION['nameErrtxt']) || isset($_SESSION['emailErrtxt']) || isset($_SESSION['paypalErrtxt'])) {
		$_SESSION['userStatus'] = "<p class=\"err\">" . $_SESSION['usernameErrtxt'] . "<br />" . $_SESSION['nameErrtxt'] . "<br />" . $_SESSION['emailErrtxt'] . "<br />" . $_SESSION['paypalErrtxt'] . "</p>";
		unset($_SESSION['usernameErrtxt'], $_SESSION['nameErrtxt'], $_SESSION['emailErrtxt'], $_SESSION['paypalErrtxt']);
		header("Location: " . PATH . "/profile.php?error=true");
	}

	// All checks have passed, generate a password for the user and add them to the database.
	// Generate a salt and a password for the new user
	$salt = bin2hex(random_bytes(5));
	$plainPassword = bin2hex(random_bytes(5));
	$password = crypt($plainPassword, '$6$' . $salt);

	$createUser = "INSERT INTO users (username, name, email, password, paypal, resetRequired) VALUES ('$username', '$name', '$email', '$password', '$paypal', 'Y')";
	$addUser = $conn->query($updatePasswd);
	if ($addUser === TRUE) {
		$_SESSION['userStatus'] = "<p>Added <b>" . $name . "</b> with username <b>" . $username . "</b> and password <b>" . $plainPassword . "</b>. The new user will be requried to change their password on login. If an email was given, the user has been emailed their login information.</p>";
		if ($email != '') {
			// Email the user their login details
			$mailTo =  "$name <$email>";
			$mailFrom = "FROM: ADMIN_EMAIL";
			$mailSubject = "New account created at " . SITE_URL;
			$mailMessage = "Hello $name!\r\n\r\n
			A new account has been created for you at SITE_URL . Please login with the following credentials and change your password when prompted.\r\n\r\n
			Username: $username\r\n
			Password: $plainPassword\r\n\r\n

			After changing your passssword, please add your PayPal.me username at SITE_URL/profile.php if one hasn't already been entered (or update it if it's wrong) so that you can get paid when you request money from a household member. Without it, you can't get reimbursted for purchases.\r\n\r\n
			To add a bill for one or more members of the household, simply login and visit SITE_URL/new.php?type=bill and select one or more members, the total bill amount, and an optional description (such as Internet). If more than one person is selected to receive the bill, the application will automatically split the amount, always rounding up to the nearest whole cent. If the person(s) receiving the bill have an email address on file, they will be emailed a notification of the newly requested bill.\r\n\r\n
			If you have any questions, feel free to contact the site administrator at ADMIN_EMAIL .
			--
			The admins at SITE_TITLE\r\n\r\n
			Please note that this email box might not be monitored and may be used solely for sending email.\r\n
			You are receiving this email because you are were registered for an account at SITE_URL";
			if (USE_SMTP === FALSE) {
				// Send email via built-in mail function
				mail($mailTo, $mailSubject, $mailMessage, $mailFrom);
			} else {
				// Send email via SMTP
			}
		}
	} else 
		$_SESSION['userStatus'] =  "<p class=\"err\">Unable to add the user to the database. <br />" . $addUser->error() . "</p>";
	header("Location: " . PATH . "/profile.php");
?>