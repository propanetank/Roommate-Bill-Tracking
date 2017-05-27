<?php
	require_once("config/config.php");
	session_start();
	checkDatabaseConn();
	if (!userLoggedIn()) {
		redirectLogin();
	}
?>

<!DOCTYPE HTML>
<html>
	<head>
		<link rel="stylesheet" href="includes/default.css" type="text/css" />
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script type="text/javascript">
			function redirctPaypal() {
				var paypal = "<?php echo $result['paypal']; ?>";
				var amount = "<?php echo $result['amount']; ?>";
				window.open("https://paypal.me/" + paypal + "/" + amount);
			}
		</script>
		<script src="includes/navbar.js"></script>
		<title><?php echo SITE_TITLE; ?> | Pay Bill</title>
	</head>
	<body>
		<section id="nav">
			<?php require("includes/nav.php"); ?>
		</section>
		<div id="container">
			<section id="primary">
			<?php
				if ($_SERVER['METHOD']) === "POST") {
					$i = 0;
					foreach ($_POST['bill'] as $value) {				
						$result = $conn->query("SELECT name, paypal, amount FROM users, bills WHERE bfrom=users.id AND bills.id='$value");
						if ($result->num_rows < 1) {
							echo "<p class=\"err\">Error, unable to find the bill specified.</p>";
							exit();
						}
						$_SESSION['billid'][$i] = $value;
						$_SESSION['bill'][$i] = $result->fetch_assoc();
						$totalAmount += $_SESSION['bill'][$i]['amount'];
						$i++;
					}
					$_SESSION['total'];
					?>
					<?php
					echo "Paypal should have opened to " . $result['name'] . "'s PayPal with an amount of $" . $totalAmount . " already filled in. If not, check your popup blocker, or go to <a href=\"https://paypal.me/" . $result['paypal'] . "/" . $totalAmount . "\">https://paypal.me/" . $result['paypal'] . "</a> and enter the amount shown prior, or click the link. Once paid, <a href=\"" . $_SERVER['PHP_SELF'] . "?paid=y\">click here</a> to finalize the payment process and return to the dashboard.";
				} elseif (isset($_GET['paid']) && $_GET['paid'] === 'y') {
					$i = 0;
					$currDate = date('m/d/y');
					foreach ($_SESSION['billid'] as $value) {
						// Error handling is done, time to add the data to the database
						$updateBill = "UPDATE bills SET paid='Y', paidDate='$currDate' WHERE id='$value'";
						if ($conn->query($updateBill) === FALSE) {
							echo "<p class=\"err\">Unable to update the bill to " . $_SESSION['bill'][$i]['name'] . " in the amount of $" . $_SESSION['bill'][$i]['amount'] . " in the database. Unknown error.</p>";
						} else {
							echo "<p>Successfully marked bill to " . $_SESSION['bill'][$i]['name'] . " in the amount of $" . $_SESSION['bill'][$i]['amount'] . " as paid in the database.</p>";
						}
						$i++;
					}
					echo "<p>Successfully paid " . $_SESSION['bill'][$i]['name'] . " $" . $_SESSION['total'] . ". Return to your <a href=\"" . PATH . "/dashboard.php\">dashboard</a>.</p>";
					unset($_SESSION['bill']);
					unset($_SESSION['billid']);
					unset($_SESSION['total']);
				} else {
					header("Location: " . PATH . "/dashboard.php");
				}
			?>