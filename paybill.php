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
				if ($_SERVER['REQUEST_METHOD'] == "POST") {
					$i = 0;
					$totalAmount = 0;

					// For now I wrote the code to only pay bills of the same payee, bills are sent to this page in one variable using billID:userID format
					$origName = explode(":", $_POST['bill'][0]);

					// Loop through the bills making sure they all are from the same person, then total the amounts to create a paypal link the user can click (or auto open if the JS works) to the payee's paypal page.
					foreach ($_POST['bill'] as $value) {
						$bill = explode(":", $value);				
						$result = $conn->query("SELECT users.id, name, paypal, amount FROM users, bills WHERE bfrom=users.id AND bills.id='$bill[0]'");
						$row = $result->fetch_assoc();
						if ($result->num_rows < 1) {
							echo "<p class=\"err\">Error, unable to find the bill specified. <a href=\"" . PATH . "/dashboard.php\">Return to dashboard</a>.</p>";
							exit();
						}
						if ($origName[1] != $row['id']) {
							echo "<p class=\"err\">Whoops! As of right now, paying multiple bills is only supported if they are from the same person. <a href=\"" . PATH . "/dashboard.php\">Return to dashboard</a>.</p>";
							exit();
						}
						$_SESSION['billid'][$i] = $bill[0];
						$_SESSION['bill'][$i] = $row;
						$totalAmount += $_SESSION['bill'][$i]['amount'];
						$i++;
					}
					$totalAmount = number_format($totalAmount, 2);
					$_SESSION['total'] = $totalAmount;
					?>
					<script type="text/javascript">
						function redirectPaypal() {
							var paypal = "<?php echo $row['paypal']; ?>";
							var amount = "<?php echo $totalAmount; ?>";
							window.open("https://paypal.me/" + paypal + "/" + amount);
							return;
						}
					</script>
					<?php
					echo "<p onload=\"redirectPaypal\">Paypal should have opened to " . $row['name'] . "'s PayPal with an amount of $" . $totalAmount . " already filled in. If not, check your pop-up blocker, or go to <a href=\"https://paypal.me/" . $_SESSION['bill'][0]['paypal'] . "/" . $totalAmount . "\" target=\"_blank\">https://paypal.me/" . $_SESSION['bill'][0]['paypal'] . "/" . $totalAmount . "</a> and enter the amount shown prior, or click the link. Once paid, <a href=\"" . $_SERVER['PHP_SELF'] . "?paid=y\">click here</a> to finalize the payment process and return to the dashboard.</p>";

				// Once the user has paid the person and clicks the link in the above paragraph, they get taken to this section of the page that marks those bills as paid in the database
				} else if (isset($_GET['paid']) && $_GET['paid'] === 'y') {
					$i = 0;
					$currDate = date('m/d/y');
					foreach ($_SESSION['billid'] as $value) {
						$updateBill = "UPDATE bills SET paid='Y', paidDate='$currDate' WHERE id='$value'";
						if ($conn->query($updateBill) === FALSE) {
							echo "<p class=\"err\">Unable to update the bill to " . $_SESSION['bill'][$i]['name'] . " in the amount of $" . $_SESSION['bill'][$i]['amount'] . " in the database. Unknown error.</p>";
						} else {
							echo "<p>Successfully marked bill to " . $_SESSION['bill'][$i]['name'] . " in the amount of $" . $_SESSION['bill'][$i]['amount'] . " as paid in the database. </p>";
						}
						$i++;
					}
					echo "<p>Successfully paid " . $_SESSION['bill'][0]['name'] . " $" . $_SESSION['total'] . ". Return to your <a href=\"" . PATH . "dashboard.php\">dashboard</a>.</p>";
					unset($_SESSION['bill']);
					unset($_SESSION['billid']);
					unset($_SESSION['total']);
				} else {
					header("Location: " . SITE_URL . PATH . "dashboard.php");
				}
			?>
			</section>
		</div>
	</body>
</html>