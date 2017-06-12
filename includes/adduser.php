<?php
	require_once(dirname( __FILE__ , 2) . "/config/config.php");
	session_start();
	checkDatabaseConn();
	if (!userLoggedIn()) {
		redirectLogin();
	}
	if ($_SESSION['role'] != 'ADMIN') {
		header("Location: " . SITE_URL . PATH . "dashboard.php");
	}

	if ($_SERVER['REQUEST_METHOD'] != 'POST')
		header("Location: " . SITE_URL . PATH . "dashboard.php");

	// Check the input of the user
	if (!empty($_POST['username'])) {
		$username = $_POST['username'];
		if (!preg_match("/[a-zA-Z]+/", $username))
			$_SESSION['usernameErrtxt'] = "Invalid input for Username, can only contain a-z, A-Z, 0-9";
		else {
			$usernameUniq = $conn->query("SELECT username FROM users WHERE username='$username'");
			if ($usernameUniq->num_rows == 1)
				$_SESSION['usernameErrtxt'] = "Username already in use, please enter a different one.";
		}
	} else
		$_SESSION['usernameErrtxt'] = "Username cannot be left blank";

	if (!empty($_POST['name'])) {
		$name = sanitizeData($_POST['name']);
		if (!preg_match("/[a-zA-Z ]+/", $name))
			$_SESSION['nameErrtxt'] = "Invalid input for Name. Only a-z, A-Z, and spaces are allowed.";
	} else
		$_SESSION['nameErrtxt'] = "Name cannot be left blank";

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
		header("Location: " . SITE_URL . PATH . "profile.php?error=true");
		exit();
	}

	// All checks have passed, generate a password for the user and add them to the database.
	// Generate a salt and a password for the new user
	$salt = bin2hex(random_bytes(5));
	$plainPassword = bin2hex(random_bytes(5));
	$password = crypt($plainPassword, '$6$' . $salt);

	$createUser = "INSERT INTO users (username, name, email, password, paypal, role) VALUES ('$username', '$name', '$email', '$password', '$paypal', '$_POST[role]')";
	$addUser = $conn->query($createUser);
	if ($addUser === TRUE) {
		$_SESSION['userStatus'] = "<p>Added <b>" . $name . "</b> with username <b>" . $username . "</b> and password <b>" . $plainPassword . "</b> with role <b>" . $_POST['role'] . "</b>. The new user will be required to change their password on login. If an email was given, the user has been emailed their login information.</p>";
		if (isset($email)) {
			// Email the user their login details
			$mailTo =  $name . " <" . $email . ">";
			$mailHeaders = "FROM: " . ADMIN_EMAIL . "\r\n";
			if (ADMIN_REPLY != '')
				$mailHeaders .= "Reply-To: " . ADMIN_REPLY . "\r\n";
			$mailHeaders .= "MIME-Version: 1.0\r\n";
			$mailHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";
			$mailSubject = "New account created at " . SITE_URL;
			$mailMessage = "<h3>Hello " . $name . "!</h3>
			<p>A new account has been created for you at " . SITE_URL  . PATH . ". Please login with the following credentials and change your password when prompted.</p>
			<p>Username: <b>" . $username . "</b></p>
			<p>Password: <b>" . $plainPassword . "</b></p>
			<p>After changing your password, please add your PayPal.me username at <a href=\"" . SITE_URL . PATH . "profile.php\">" . SITE_URL . PATH . "profile.php</a> if one hasn't already been entered (or update it if it's wrong) so that you can get paid when you request money from a household member. Without it, you can't get reimbursed for purchases.</p>
			<p>To add a bill for one or more members of the household, simply login and visit <a href=\"" . SITE_URL . PATH . "new.php?type=bill\">" . SITE_URL . PATH . "new.php?type=bill</a> and select one or more members, the total bill amount, and an optional description (such as Internet). If more than one person is selected to receive the bill, the application will automatically split the amount, always rounding up to the nearest whole cent. If the person(s) receiving the bill have an email address on file, they will be emailed a notification of the newly requested bill.</p>
			<p>--<br />
			The admins at " . SITE_TITLE . "<br />
			<i>Please note that this email box might not be monitored and may be used solely for sending email.<br />
			You are receiving this email because you are were registered for an account at <a href=\"" . SITE_URL . PATH . "\">" . SITE_URL . PATH . "</a></i></p>";
			if (USE_SMTP === FALSE) {
				// Send email via built-in mail function
				mail($mailTo, $mailSubject, $mailMessage, $mailHeaders);
			} else {
				// Send email via SMTP
			}
		}
	} else
		$_SESSION['userStatus'] =  "<p class=\"err\">Unable to add the user to the database.<br /></p>";
	header("Location: " . SITE_URL . PATH . "profile.php");
?>