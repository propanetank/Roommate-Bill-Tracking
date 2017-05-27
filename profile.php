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
		<style type="text/css">
		div#container {
			overflow: auto;
		}
		section#primary {
			float: left;
		}
		section#right {
			float: right;
		}
		</style>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script src="includes/navbar.js"></script>
		<title><?php echo SITE_TITLE; ?> | Profile</title>
	</head>
	<body>
		<section id="nav">
			<?php require("includes/nav.php"); ?>
		</section>
		<div id="container">
			<section id="primary">
				<h1><?php echo $_SESSION['name']; ?>'s Profile</h1>
				<h2>Sent Bills</h2>
				<?php 
					$getBills = "SELECT bills.id, name, bdate, amount, description, paid, paidDate FROM users, bills WHERE bfrom='$_SESSION[uid]' AND bto=users.id ORDER BY bdate, btime DESC";
					$billList = $conn->query($getBills);
					if ($billList->num_rows > 0) {
						echo "<div style=\"overflow-x: auto;\">";
						echo "<table class=\"dashboard\">
								<tr>
									<th>Date</th>
									<th>To</th>
									<th>Amount</th>
									<th>Description</th>
									<th>Paid</th>
									<th>Date Paid</th>
									<th></th>
									<th></th>
								</tr>";
						while ($row = $billList->fetch_assoc()) {
							echo "<tr>";
							echo "<td>" . $row["bdate"] . "</td>
								  <td>" . $row["name"] . "</td>
								  <td>$" . $row["amount"] . "</td>
								  <td>" . $row["description"] . "</td>
								  <td>" . $row["paid"] . "</td>
								  <td>" . $row["paidDate"] . "</td>
								  <td><a href=\"" . PATH . "/editbill.php?bill=" . $row['id'] . "\">Edit</a></td>
								  <td><a href=\"" . PATH . "/editbill.php?bill=" . $row['id'] . "&delete=true\">Delete</a></td>
								  </tr>";
						}
						echo "</table>";
						echo "</div>";
					} else {
						echo "<p>No bills found for " . $_SESSION['name'] . "!</p>";
					}

					if (isset($_GET['editerror'])) {
						echo "<p class=\"err\">" . $_SESSION['errtxt'] . "</p>";
						unset($_SESSION['errtxt']);
					}
				?>
				<h2>Received Bills</h2>
				<form name="bills" action="paybill.php" method="post">
					<?php 
						$getBills = "SELECT bills.id, name, bdate, amount, description, paid, paidDate FROM users, bills WHERE bto='$_SESSION[uid]' AND bfrom=users.id  AND deleted='N' ORDER BY bdate, btime DESC";
						$billList = $conn->query($getBills);
						if ($billList->num_rows > 0) {
							echo "<div style=\"overflow-x: auto;\">";
							echo "<table class=\"dashboard\">
									<tr>
										<th>Date</th>
										<th>From</th>
										<th>Amount</th>
										<th>Description</th>
										<th>Paid</th>
										<th>Date Paid</th>
										<th></th>
									</tr>";
							while ($row = $billList->fetch_assoc()) {
								echo "<tr>";
								echo "<td>" . $row["bdate"] . "</td>
									  <td>" . $row["name"] . "</td>
									  <td>$" . $row["amount"] . "</td>
									  <td>" . $row["description"] . "</td>
									  <td>" . $row["paid"] . "</td>
									  <td>" . $row["paidDate"] . "</td>";
									  if ($row['paid'] === 'N')
									  	echo "<td><input type=\"checkbox\" name=\"bill\" value=" . $row["id"] . " /></td>";
									  else
									  	echo "<td></td>";
								echo  "</tr>";
							}
							echo "</table>";
							echo "</div>";
							?>
							<input type="submit" name="paySelected" value="Pay Selected" />
						</form>
						<?php 
					} else {
						echo "<p>No bills found for " . $_SESSION['name'] . "!</p>";
					}
				?>
			</section>
			<section id="right">
				<h2>Update profile</h2>
				<?php
					$profileInfo = "SELECT name, email, paypal, apiKey FROM users WHERE id='$_SESSION[uid]'";
					$getProfileInfo = $conn->query($profileInfo);
					$row = $getProfileInfo->fetch_assoc();
					$conn->close();
				?>
				<form action="includes/updateprofile.php" method="post">
					<label for="name">Name: </label><input type="text" name="name" value="<?php echo $row['name']; ?>" readonly style="cursor: not-allowed;" /><br />
					<label for="email">Email: </label><input type="text" name="email" value="<?php echo $row['email']; ?>" required /><br />
					<label for="paypal">Paypal.me username: </label><input type="text" name="paypal" value="<?php echo $row['paypal']; ?>" required /><br />
					<label for="api">API Key: </label><input type="text" name="api" id="api" value="<?php echo $row['apiKey']; ?>" readonly />
					<button type="button" name="generateAPI">Generate New API Key</button><br />
					<label for="currPass">Current Password*: </label><input type="password" name="currPass" required /><br />
					<input type="submit" name="update" value="Update" />
				</form>
				<?php if(isset($_SESSION['updateStatus'])) {
					echo $_SESSION['updateStatus'];
					unset($_SESSION['updateStatus']);
				}
				?>
				<p><a href="<?php echo PATH; ?>/includes/changePassword.php">Update Password</a></p>
				<?php
					if ($_SESSION['role'] === 'ADMIN') {
				?>
				<h2>Add User</h2>
				<form action="includes/adduser.php" method="post">
					<label for="username">Username*: </label><input type="text" name="username" required /><br />
					<label for="name">Name*: </label><input type="text" name="name" required /><br />
					<label for="email">Email: </label><input type="text" name="email" /><br />
					<label for="paypal">PayPal.me username: </label><input type="text" name="paypal" /><br />
					<label for="submit"></label><input type="submit" id="submit" name="create" value="Create User" />
				</form>
				<?php 
					if (isset($_SESSION['userStatus'])) {
						echo $_SESSION['userStatus'];
						unset($_SESSION['userStatus']);
					}
				} ?>
			</section>
		</div>
	</body>
</html>