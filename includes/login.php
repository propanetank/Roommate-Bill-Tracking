<?php
	// if the user checked the 'remember me' box, have the session last for 30 days instead of the default php value
	if (isset($_POST['remember'])) {
		session_set_cookie_params(2592000);
		$_SESSION['remember'] = 'yes';
	}
	require_once(dirname( __FILE__ , 2) . "/config/config.php");
	session_start();
	checkDatabaseConn();
	if (userLoggedIn()) {
		redirectHome();
	}

	// Make sure they actually sent a username or password
	if(empty($_POST['username']) || empty($_POST['password'])) {
		$_SESSION['errtxt'] = "You must enter a username and password.";
		header("Location: " . PATH . "/login.php?error=true&username=" . $username);
	}

	// Get the salt from the users password
	$pass = getUserPassword();
	if ($pass === FALSE) {
		goto end;
	}
	$salt = $pass[1];

	$username = $_POST['username'];
	$password = crypt($_POST['password'], '$6$' . $salt);


	$loginSQL = "SELECT id, name, role, resetRequired FROM users WHERE username='$username' AND password='$password'";
	$checkLogin = $conn->query($loginSQL);
	if ($checkLogin->num_rows > 0) {
		// Sucessful login, redirect to homepage.
		$_SESSION['isloggedin'] = true;
		$_SESSION['username'] = $username;
		while ($row = $checkLogin->fetch_assoc()) {
			$_SESSION['uid'] = $row['id']; 
			$_SESSION['name'] = $row['name'];
			$_SESSION['role'] = $row['role'];
			// Check if the user is required to change their password, if so, make them change it
			if ($row['resetRequired'] === 'Y') {
				$_SESSION['password'] = $_POST['password'];
				$_SESSION['resetRequired'] = 'Y';
				header("Location: " . PATH . "/includes/changePassword.php");
			}
		}
		header("Location: " . PATH . "/");
	} else {
		end:
		// Incorrect login, redirect back to login page and give them an error
		$_SESSION['errtxt'] = "Username and/or password incorrect, please try again.";
		header("Location: " . PATH . "/login.php?error=true&username=" . $username);
	}
?>