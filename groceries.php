<?php
	require_once("config/config.php");
	session_start();
	checkDatabaseConn();
	if (!userLoggedIn()) {
		redirectLogin();
	}
	if (changePassword())
		header("Location: " . SITE_URL . PATH . "includes/changePassword.php");
?>

<!DOCTYPE HTML>
<html>
	<head>
		<link rel="stylesheet" href="includes/default.css" type="text/css" />
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script src="includes/navbar.js"></script>
		<title><?php echo SITE_TITLE; ?> | Update Groceries</title>
	</head>
	<body>
		<section id="nav">
			<?php require("includes/nav.php"); ?>
		</section>
		<div id="container">
			<section id="primary">
				<?php
				// Run a check to see if user is next for the specified grocery.
				$groceryInfo = "SELECT * FROM groceries WHERE id='$_GET[grocery]'";
				$getGrocery = $conn->query($groceryInfo);
				if ($getGrocery->num_rows > 0) {
					$existing = $getGrocery->fetch_assoc();
					$users = explode(',', $existing['users']);
					if ($users['0'] != $_SESSION['uid']) {
						$_SESSION['errtxt'] = "It doesn't appear you were next for " . $existing['item'] . ".";
						header("Location: " . SITE_URL . PATH . "dashboard.php?err=true");
					}
				} else {
					$_SESSION['errtxt'] = "Unable to find specified grocery item.";
					header("Location: " . SITE_URL . PATH . "dashboard.php?err=true");
				}

				if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_GET['grocery'] = $existing['id']) {
					// Check if amount was given and is in the correct format
					if (!empty($_POST['amount'])) {
						if (!preg_match("/^\d{1,3}\.\d{2}$/", $_POST['amount']))
							$_SESSION['amtErrtxt'] = "Invalid dollar amount, must be more than $0 and less than $1000 with the cents (including 00 cents)";
						else
							$amount = $_POST['amount'];
					}
					// Check if location was given and is using invalid characters
					if (!empty($_POST['location'])) {
						$location = sanitizeData($_POST['location']);
						if (!preg_match("/^[a-zA-Z0-9 ]*$/", $location)) {
							$_SESSION['locErrtxt'] = "Invalid input for Location. Only a-z, A-Z, 0-9, and spaces are allowed.";
						}
					}
					// If errors were found, redirect back and display the error
					if (isset($_SESSION['amtErrtxt']) || isset($_SESSION['locErrtxt'])) {
						$_SESSION['amt'] = $_POST['amount'];
						$_SESSION['location'] = $_POST['location'];
						header("Location: " . SITE_URL . PATH . "groceries.php?grocery=" . $existing['id'] . "&err=true");
					}

					$currDate = date('m/d/y');
					// Add the data to the database
					$analyticalData = "INSERT INTO groceryHistory (name, item, amount, date, location) VALUES ('$_SESSION[uid]', '$existing[id]', '$amount', '$currDate', '$location')";
					$updateTable = $conn->query($analyticalData);
					if ($updateTable === TRUE) {
						if (isset($amount) || isset($location))
							echo "<p>Thank you for the information.</p>";
						$newUsers[count($users) - 1] = $users[0];
						for ($i = 0; $i < (count($users) - 1); $i++) {
							$newUsers[$i] = $users[$i + 1];
						}
						ksort($newUsers);  // Apparently arrays can get out of order in php(?) So we are putting it back in order. I could not for the life of me figure out why implode was not working as expected until I dumped the $newUsers array and saw it was out of order... Go figure.
						$users = implode(',', $newUsers);

						// Time to save the new order back to the database
						$updateItem = "UPDATE groceries SET users='$users' WHERE id='$existing[id]'";
						if ($conn->query($updateItem) === TRUE)
							echo "<p>You have been moved to the back of the list. <a href=\"" . PATH . "dashboard.php\">Return to dashboard.</a></p>";
						else
							echo "<p class=\"err\">Error updating database.</p>";
					} else
						echo "<p class=\"err\">Error updating database.</p>";

				} else if (isset($_GET['grocery'])) { ?>
				<p>To mark that you purchased <b><?php echo $existing['item']; ?></b> off the grocery list, either fill out the optional form below or leave it blank an click save.</p>
				<form action="groceries.php?grocery=<?php echo $existing['id']; ?>" method="post">
				<p>The following is an optional survey for analytical purposes and you may click save without submitting anything.</p>
					<label for="amount">Amount: </label><input type="number" name="amount" placeholder="0.00" maxlength="6" size="6" step="0.01"
															value="<?php if (isset($_SESSION['amt'])) {
																			echo $_SESSION['amt'];
																			unset($_SESSION['amt']);
																		} ?>" /><br />
					<label for="location">Purchase Location: </label><input list="locations" name="location" placeholder="Sunshine"
																		value="<?php if (isset($_SESSION['location'])) {
																						echo $_SESSION['location'];
																						unset($_SESSION['location']);
																					 } ?>" />
						<datalist id="locations">
						<?php
						$locations = "SELECT location FROM groceryHistory";
						$getLocations = $conn->query($locations);
						if ($getLocations->num_rows > 0) {
							while ($row = $getLocations->fetch_object()->location)
								echo "<option value=\"" . $row . "\">";
						}
						?>
						</datalist><br />
					<input type="submit" name="submitGrocery" value="Save" /><br />
					<?php
					if (isset($_GET['err'])) {
						if (isset($_SESSION['amtErrtxt'])) {
							echo "<p class=\"err\">" . $_SESSION['amtErrtxt'] . "</p>";
							unset($_SESSION['amtErrtxt']);
						}
						if (isset($_SESSION['locErrtxt'])) {
							echo "<p class=\"err\">" . $_SESSION['locErrtxt'] . "</p>";
							unset($_SESSION['locErrtxt']);
						}
					}
					?>
				</form>
				<?php } ?>
			</section>
		</div>
	</body>
</html>