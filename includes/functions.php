<?php
	// Begin Functions
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWD, DB_NAME);
	function checkDatabaseConn() {
		global $conn;
		if($conn->connect_error) {
			echo "<h3 class='error'>Error connecting to the database, unable to fetch data!</h3>";
			echo "<p class='error'>Please try loading the page in a few minutes.</p>";
			die($conn->connect_error);
		}
	}

	function ssl() {
		if (SSL) {
			if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off") {
				header("HTTP/1.1 301 Moved Permanently");
				header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
			}
		}
	}

	function redirectLogin() {
		header("Location: " . PATH . "/login.php");
	}

	function redirectHome() {
		header("Location: " . PATH . "/");
	}

	function userLoggedIn() {
		if(isset($_SESSION['isloggedin'])) {
			return true;
		} else {
			return false;
		}
	}

	function sanitizeData($badData) {
		$goodData = trim($badData);
		$goodData = stripslashes($badData);
		$goodData = htmlspecialchars($badData);
		return $goodData;
	}

	function round_up($number, $precision) {
    	$fig = pow(10, $precision);
    	return (ceil($number * $fig) / $fig);
	}

	function changePassword() {
		if (isset($_SESSION['resetRequired'])) {
			if ($_SERVER['SCRIPT_NAME'] != PATH . 'changePassword.php')
				return true;
		} else
			return false;
	}

	function getUserPassword() {
		// Get the salt from the users password
		global $conn;
		if (isset($_SESSION['isloggedin']))
			$userSalt = "SELECT password FROM users WHERE id='$_SESSION[uid]'";  // User logged in, use userid
		else
			$userSalt = "SELECT password FROM users WHERE username='$_POST[username]'";  // User not logged in, use username (probably at login page)
		$getSalt = $conn->query($userSalt);
		if ($getSalt->num_rows > 0) {
			$pass[0] = $getSalt->fetch_object()->password;  // The entire hash
			$pass[1] = substr($pass[0], 3, 10);  // The salt
			return $pass;  // Return array containing the entire hash (pos 0) and the salt (pos 1)
		} else {
			return false;
		}
	}
?>