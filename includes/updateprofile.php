<?php
	require_once(dirname( __FILE__ , 2) . "/config/config.php");
	session_start();
	checkDatabaseConn();
	if (!userLoggedIn()) {
		redirectLogin();
	}

	// Check the input of the user
	if (!empty($_POST['name'])) {
		$name = sanitizeData($_POST['name']);
		if (!preg_match("/[a-zA-Z ]+/", $name))
			$_SESSION['nameErrtxt'] = "Invalid input for Name. Only a-z, A-Z, and spaces are allowed.";
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

	// If errors are found, direct user back to the previous page
	if (isset($_SESSION['nameErrtxt']) || isset($_SESSION['emailErrtxt']) || isset($_SESSION['paypalErrtxt'])) {
		header("Location: " . PATH . "/profile.php");
	}

	$updateProfile = "UPDATE users SET email='$email', paypal='$paypal' WHERE id='$_SESSION[uid]'";
	if ($conn->query($updateProfile) === TRUE) {
		$_SESSION['updateStatus'] =  "<p>Profile updated successfully.</p>";
	} else {
		$_SESSION['updateStatus'] = "<p class=\"err\">Unable to update profile.</p>";
	}

?>