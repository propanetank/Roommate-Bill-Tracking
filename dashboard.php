<?php
	require_once("config/config.php");
	session_start();
	checkDatabaseConn();
	if (!userLoggedIn()) {
		redirectLogin();
	}
	if (changePassword())
		header("Location: " . PATH . "/includes/changePassword.php");
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
				<p>WIP</p>
				<?php
				/*
				$getGroceries = "SELECT item,next FROM groceries ORDER BY item DESC";
				$groceryList = $conn->query($getGroceries);
				if($groceryList->num_rows != 0) {
					echo "<table><tr><th>Item</th><th>Next Person</th></tr>";
					while($row = $groceryList->fetch_assoc()) {
						echo "<tr>";
						echo "<td>" . $row["item"] . "</td><td>" . $row["next"] . "</td>";
						echo "</tr>";
					}
					echo "</table>";
					$conn->close();
				} else { ?>
					<h3 class="error">Error getting data from the database!</h3><?php
				} */
				?>
				<h2>Bills</h2>
					<form name="bills" action="paybill.php" method="post">
						<?php
						$getBills = "SELECT bills.id, name, bdate, amount, description FROM users, bills WHERE bto='$_SESSION[uid]' AND bfrom=users.id AND paid='N' ORDER BY bdate, btime DESC";
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
									  <td>" . $row["description"] . "</td>
									  <td><input type=\"checkbox\" name=\"bill\" value=" . $row["id"] . " /></td>
									  </tr>";
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