<?php
	require_once(dirname( __FILE__ , 2) . "/config/config.php");
	session_start();
	checkDatabaseConn();
	if (!userLoggedIn()) {
		redirectLogin();
	}
	if ($_SERVER['REQUEST_METHOD'] != 'POST')
		header("Location: " . SITE_URL . PATH . "new.php");

	// Make sure the Bill To isn't null
	if (!isset($_POST['to']))
		$_SESSION['toErrtxt'] = "Invalid person to send a bill to.";

	// Validate the data in the amount field is what we intended
	if (!empty($_POST['amount'])) {
		if (!preg_match("/^\d{1,3}\.\d{2}$/", $_POST['amount']))
			$_SESSION['amtErrtxt'] = "Invalid dollar amount, must be more than $0 and less than $1000 with the cents (including 00 cents)";
		else
			$amount = $_POST['amount'];
	} else
		$_SESSION['amtErrtxt'] = "Must specify an amount";

	// Validate the data in the description field if entered
	if (!empty($_POST['description'])) {
		$description = sanitizeData($_POST['description']);
		if (!preg_match("/^[a-zA-Z0-9 ]*$/", $description)) {
			$_SESSION['descErrtxt'] = "Invalid input for Description. Only a-z, A-Z, 0-9, and spaces are allowed.";
		}
	}

	// If errors are found, direct user back to the previous page
	if (isset($_SESSION['toErrtxt']) || isset($_SESSION['amtErrtxt']) || isset($_SESSION['descErrtxt'])) {
		$_SESSION['amt'] = $_POST['amount'];
		$_SESION['desc'] = $_POST['description'];
		header("Location: " . SITE_URL . PATH . "new.php?type=bill&error=true");
		exit(1);
	}

	// Error handling is done, time to add the data to the database
	$currDate = date('m/d/y');
	$currTime = date('H:i:s');

	// If bill is sent to more than one person, divide it by the number of people and always round up (15.172 becomes 15.18)
	if (count($_POST['to']) > 1 ) {
		$amount = round_up($amount / count($_POST['to']), 2);
	}

	$i = 0;
	foreach ($_POST['to'] as $value) {
		$addBill = "INSERT INTO bills (bfrom, bto, bdate, btime, amount, description) VALUES ('" . $_SESSION['uid'] . "', '$value', '$currDate', '$currTime', '$amount', '$description')";
		if ($conn->query($addBill) === FALSE) {
			$_SESSION['errtxt'] = "Unable to add data to the database. Unknown error.";
			header("Location: " . SITE_URL . PATH . "new.php?type=bill&error=true");
		} else {
			$result = $conn->query("SELECT name, email FROM users WHERE id='$value'");
			$result = $result->fetch_assoc();
			$_SESSION['to'][$i] = $result['name'];
			
			// If the user has an email on-file, send them an email via the configured process to send email, either built-in mail function or via smtp
/*			if ($result['email'] != '') {
				$mailTo =  "$result['name'] <$result['email']>";
				$mailFrom = "FROM: ADMIN_EMAIL";
				$mailSubject = "New bill in your account";
				$mailMessage = "Hello $result['name']!\r\n\r\n
				A new bill has been posted to your account from $_SESSION['name'] in the amount of $amount for $description .\r\n
				You can view this bill by logging into your account at SITE_URL/dashboard.php or by copying and pasting the following into your web browsers URL bar:\r\n
				SITE_URL/dashboard.php\r\n\r\n
				--
				The admins at SITE_TITLE\r\n\r\n
				Please note that this email box might not be monitored and may be used solely for sending email.\r\n
				You are receiving these emails because you are a registered user of SITE_TITLE";
				if (USE_SMTP === FALSE) {
					// Send email via built-in mail function
					mail($mailTo, $mailSubject, $mailMessage, $mailFrom);
				} else {
					// Send email via SMTP
				}
			} else {
				echo "<p>" . $result['name'] . " doesn't have an email on file, unable to notify user.</p>";
			}
*/			$i++;
		}
	}
	$conn->close();
	$_SESSION['amount'] = $amount;
	$_SESSION['description'] = $description;
	header("Location: " . SITE_URL . PATH . "new.php?type=bill&success=true");

?>
