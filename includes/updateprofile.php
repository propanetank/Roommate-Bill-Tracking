<?php
	require_once(dirname( __FILE__ , 2) . "/config/config.php");
	session_start();
	checkDatabaseConn();
	if (!userLoggedIn()) {
		redirectLogin();
	}

	// Check the input of the user
	if (!empty($_POST['name'])) {   // This should never be empty as the user can't modify this normally, but we check just in case.
		$name = sanitizeData($_POST['name']);
		if (!preg_match("/[a-zA-Z ]*/", $name)) {
			$_SESSION['errtxt'] = "Invalid input for Name. Only a-z, A-Z, and spaces are allowed.</br>";
		}
	} else {
		$_SESSION['errtxt'] = "Name cannot be empty.</br>";
	}
	if (!empty($_POST['email'])) {
		$email = $_POST['email'];
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$_SESSION['errtxt'] .= "Invalid input for Email.</br>";
		}
	} else {
		$_SESSION['errtxt'] .= "Email cannot be empty.</br>";
	}
	if (!empty($_POST['paypal'])) {
		$paypal = $_POST['paypal'];
		if (!preg_match("/[a-zA-Z0-9]*/", $paypal)) {
			$_SESSION['errtxt'] .= "Invalid input for PayPal. Only a-z, A-Z, and 0-9 are allowed.</br>";
		}
	} else {
		$_SESSION['errtxt'] .= "Paypal cannot be empty.</br>";
	}
	if(!empty($_POST['currPass'])) {
		$pass = getUserPassword();
		$password = crypt($_POST['currPass'], '$6$' . $pass['1']);
		if (!hash_equals($pass['0'], $password)) {
			$_SESSION['errtxt'] .= "Invalid password";
		}
	} else {
		$_SESSION['errtxt'] .= "Password cannot be empty.";
	}

	// If errors are found, direct user back to the previous page
	if (isset($_SESSION['errtxt'])) {
		$_SESSION['updateStatus'] = $_SESSION['errtxt'];
		unset($_SESSION['errtxt']);
		header("Location: " . PATH . "/profile.php");
		exit();
	}

	$updateProfile = "UPDATE users SET email='$email', paypal='$paypal' WHERE id='$_SESSION[uid]'";
	if ($conn->query($updateProfile) === TRUE) {
		$_SESSION['updateStatus'] =  "<p>Profile updated successfully.</p>";
	} else {
		$_SESSION['updateStatus'] = "<p class=\"err\">Unable to update profile.</p>";
	}
	header("Location: " . PATH . "/profile.php");
?>