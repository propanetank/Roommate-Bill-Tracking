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
		<title><?php echo SITE_TITLE; ?> | Dashboard</title>
	</head>
	<body>
		<section id="nav">
			<?php require("includes/nav.php"); ?>
		</section>
		<div id="container">
			<section id="primary">
				<h1>Dashboard</h1>
				<h2>Groceries</h2>
				<?php
					$getGroceries = "SELECT * FROM groceries";
					$selectGroceries = $conn->query($getGroceries);
					if($selectGroceries->num_rows > 0) {
						echo "<div style=\"overflow-x: auto;\">";
						echo "<table class=\"dashboard\">
								<tr>
									<th>Item</th>
									<th>Users</th>
									<th></th>
								</tr>";
						while ($row = $selectGroceries->fetch_assoc()) {
							echo "<tr>";
								echo "<td>" . $row["item"] . "</td>";
								if ($row["users"] == NULL) {
									echo "<td>No users</td>";
									goto end;
								}
								$users = explode(',', $row['users']);
								echo "<td>";
								$nameMatch = false;
								$i = 0;
								foreach ($users as $value) {
									$getName = "SELECT name FROM users WHERE id='$value'";
									$selectNames = $conn->query($getName);
									if ($selectNames->num_rows > 0) {
										$name = $selectNames->fetch_object()->name;
										if ($i != (count($users) - 1))
											echo $name . ", ";
										else
											echo $name;
									$i++;
									if ($users[0] === $_SESSION['uid'])
											$nameMatch = true;
									} else {
										echo "<p class=\"err\">Error getting names from database.</p>";
										goto end;
									}
								}
								end:
								echo "</td>";
								if ($nameMatch)
									echo "<td><a href=\"groceries.php?grocery=" . $row['id'] . "\">Purchased</a></td>";
								else
									echo "<td></td>";
							echo "</tr>";
						}
						echo "</table>";
						echo "</div>"; 
					} else {
						echo "<p>Nothing found for shared groceries!</p>";
					}
					if (isset($_GET['err'])) {
						if (isset($_SESSION['errtxt'])) {
							echo "<p class=\"err\">" . $_SESSION['errtxt'] . "</p>";
							unset($_SESSION['errtxt']);
						} else {
							echo "<p class=\"err\">Unknown error.</p>";
						}
					}
				?>
				<h2>Bills</h2>
					<form name="bills" action="paybill.php" method="post">
						<?php
						$getBills = "SELECT bills.id, bfrom, name, bdate, amount, description, paypal FROM users, bills WHERE bto='$_SESSION[uid]' AND bfrom=users.id AND paid='N' ORDER BY bdate, btime DESC";
						$billList = $conn->query($getBills);
						if ($billList->num_rows > 0) {
							echo "<div style=\"overflow-x: auto;\">";
							echo "<table class=\"dashboard\">
									<tr>
										<th>Date</th>
										<th>From</th>
										<th>Amount</th>
										<th>Description</th>
										<th></th>
									</tr>";
							while ($row = $billList->fetch_assoc()) {
								echo "<tr>";
								echo "<td>" . $row["bdate"] . "</td>
									  <td>" . $row["name"] . "</td>
									  <td>$" . $row["amount"] . "</td>
									  <td>" . $row["description"] . "</td>";
									  if ($row['paypal'] != '')
									  	echo "<td><input type=\"checkbox\" name=\"bill[]\" value=\"" . $row['id'] . ":" . $row['bfrom'] . "\" /></td>";
									  else
									  	echo "<td>No Paypal Found</td>";
									  echo "</tr>";
							}
							echo "</table>";
							echo "</div>";
							?>
							<input type="submit" name="paySelected" value="Pay Selected" />
						</form>
						<?php 
						$conn->close();
					} else {
						echo "<p>No unpaid bills found for " . $_SESSION['name'] . "!</p>";
					}
				?>
			</section>
		</div>
	</body>
</html>