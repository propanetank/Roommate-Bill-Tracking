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
		<script type="text/javascript">
			function showhide(divID) {
				var x = document.getElementById(divID);
				if (x.style.display === 'none')
					x.style.display = 'block';
				else
					x.style.display = 'none';
			}
		</script>
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
				<h3><a style="cursor: pointer; text-decoration: none;" onclick="showhide('sunpaid')">Unpaid</a></h3>
				<div id="sunpaid">
					<?php 
						$getBills = "SELECT bills.id, name, bdate, amount, description FROM users, bills WHERE bfrom='$_SESSION[uid]' AND bto=users.id AND paid='N' AND deleted='N' ORDER BY bdate, btime DESC";
						$billList = $conn->query($getBills);
						if ($billList->num_rows > 0) {
							echo "<div style=\"overflow-x: auto;\">";
							echo "<table class=\"dashboard\">
									<tr>
										<th>Date</th>
										<th>To</th>
										<th>Amount</th>
										<th>Description</th>
										<th></th>
										<th></th>
									</tr>";
							while ($row = $billList->fetch_assoc()) {
							  	echo "<td>" . $row["bdate"] . "</td>
								  	  <td>" . $row["name"] . "<input type=\"hidden\" name=\"name\" value=\"" . $row["name"] . "\" /></td>
								 	  <td>$" . $row["amount"] . "</td>
								 	  <td>" . $row["description"] . "</td>
								  	  <td><a href=\"" . PATH . "/editbill.php?bill=" . $row['id'] . "\">Edit</a></td>
							  	 	  <td><a href=\"" . PATH . "/editbill.php?bill=" . $row['id'] . "&delete=true\">Delete</a></td>";
								echo "</tr>";
							}
							echo "</table>";
							echo "</div>";
						} else
							echo "<p>No unpaid bills found from you!</p>";
					?>
				</div>
				<h3><a style="cursor: pointer; text-decoration: none;" onclick="showhide('spaid')">Paid</a></h3>
				<div id="spaid">
					<?php 
						$getBills = "SELECT bills.id, name, bdate, amount, description, paidDate FROM users, bills WHERE bfrom='$_SESSION[uid]' AND bto=users.id AND paid='Y' AND deleted='N' ORDER BY bdate, btime DESC";
						$billList = $conn->query($getBills);
						$currDate = strtotime(date('m/d/y'));
						if ($billList->num_rows > 0) {
							echo "<div style=\"overflow-x: auto;\">";
							echo "<table class=\"dashboard\">
									<tr>
										<th>Date</th>
										<th>To</th>
										<th>Amount</th>
										<th>Description</th>
										<th>Paid</th>
										<th></th>
										<th></th>
									</tr>";
							while ($row = $billList->fetch_assoc()) {
							  	echo "<td>" . $row["bdate"] . "</td>
								  	  <td>" . $row["name"] . "<input type=\"hidden\" name=\"name\" value=\"" . $row["name"] . "\" /></td>
								 	  <td>$" . $row["amount"] . "</td>
								 	  <td>" . $row["description"] . "</td>
									  <td>" . $row["paidDate"] . "</td>
								  	  <td><a href=\"" . PATH . "/editbill.php?bill=" . $row['id'] . "\">Edit</a></td>
							  	 	  <td><a href=\"" . PATH . "/editbill.php?bill=" . $row['id'] . "&delete=true\">Delete</a></td>";
								echo "</tr>";
							}
							echo "</table>";
							echo "</div>";
						} else
							echo "<p>No paid bills found from you!</p>";
					?>
				</div>
				<h3><a style="cursor: pointer; text-decoration: none;" onclick="showhide('sdeleted')">Deleted</a></h3>
				<div id="sdeleted">
					<p>Deleted bills can only be recovered for 7 days before they are unrecoverable.</p>
					<?php 
						$getBills = "SELECT bills.id, name, bdate, amount, description, deletedDate FROM users, bills WHERE bfrom='$_SESSION[uid]' AND bto=users.id AND deleted='Y' ORDER BY bdate, btime DESC";
						$billList = $conn->query($getBills);
						$currDate = strtotime(date('m/d/y'));
						if ($billList->num_rows > 0) {
							echo "<div style=\"overflow-x: auto;\">";
							echo "<table class=\"dashboard\">
									<tr>
										<th>Date</th>
										<th>To</th>
										<th>Amount</th>
										<th>Description</th>
										<th>Deleted</th>
										<th></th>
									</tr>";
							while ($row = $billList->fetch_assoc()) {
								$ddate = strtotime($row['deletedDate']);
								$dateDiff = floor(($currDate - $ddate) / (60*60*24));
								if ($dateDiff <= 7 ) {
									echo "<tr>";
									echo "<td>" . $row["bdate"] . "</td>
										  <td>" . $row["name"] . "<input type=\"hidden\" name=\"name\" value=\"" . $row["name"] . "\" /></td>
										  <td>$" . $row["amount"] . "</td>
										  <td>" . $row["description"] . "</td>
										  <td>" . $row["deletedDate"] . "</td>
										  <td><a href=\"" . PATH . "/editbill.php?bill=" . $row['id'] . "&recover=true\">Recover</a></td>";
									echo "</tr>";
								}
							}
							echo "</table>";
							echo "</div>";
						} else
							echo "<p>No deleted bills found by you!</p>";
					?>
				</div>
				<h2>Received Bills</h2>
				<h3><a style="cursor: pointer; text-decoration: none;" onclick="showhide('runpaid')">Unpaid</a></h3>
				<div id="runpaid">
					<form name="bills" action="paybill.php" method="post">
						<?php 
							$getBills = "SELECT bills.id, name, bdate, amount, description, paid, paidDate, paypal FROM users, bills WHERE bto='$_SESSION[uid]' AND bfrom=users.id AND deleted='N' AND paid='N' ORDER BY paid, bdate, btime DESC";
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
											  if ($row['paid'] === 'N') {
											  	if ($row['paypal'] != '')
											  		echo "<td><input type=\"checkbox\" name=\"bill\" value=" . $row["id"] . " /></td>";
											  	else
											  		echo "<td>No Paypal Found</td>";
											  } else
											  	echo "<td></td>";
										echo "</tr>";
									}
								echo "</table>";
								echo "</div>";
								?>
								<input type="submit" name="paySelected" value="Pay Selected" />
							</form>
							<?php 
						} else {
							echo "<p>No unpaid bills found for " . $_SESSION['name'] . "!</p>";
						}
					?>
			</div>
			<h3><a style="cursor: pointer; text-decoration: none;" onclick="showhide('rpaid')">Paid</a></h3>
			<div id="rpaid">
				<?php 
					$getBills = "SELECT bills.id, name, bdate, amount, description, paid, paidDate, paypal FROM users, bills WHERE bto='$_SESSION[uid]' AND bfrom=users.id AND deleted='N' AND paid='Y' ORDER BY paid, bdate, btime DESC";
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
								</tr>";
							while ($row = $billList->fetch_assoc()) {
								echo "<tr>";
								echo "<td>" . $row["bdate"] . "</td>
									  <td>" . $row["name"] . "</td>
									  <td>$" . $row["amount"] . "</td>
									  <td>" . $row["description"] . "</td>
									  <td>" . $row["paidDate"] . "</td>";
								echo "</tr>";
							}
						echo "</table>";
						echo "</div>"; 
					} else {
					echo "<p>No paid bills found for " . $_SESSION['name'] . "!</p>";
				}
				?>
			</div>
			</section>
			<section id="right">
				<h2>Update profile</h2>
				<?php
					$profileInfo = "SELECT name, email, paypal, apiKey FROM users WHERE id='$_SESSION[uid]'";
					$getProfileInfo = $conn->query($profileInfo);
					$row = $getProfileInfo->fetch_assoc();
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
					print "<p class=\"err\">" . $_SESSION['updateStatus'] . "</p>";
					unset($_SESSION['updateStatus']);
				}
				?>
				<p><a href="<?php echo PATH; ?>/includes/changePassword.php">Update Password</a></p>
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
										if ($name === $_SESSION['name'])
											$nameMatch = true;
									$i++;
									} else {
										echo "<p class=\"err\">Error getting names from database.</p>";
										goto end;
									}
								}
								end:
								echo "</td>";
								if ($nameMatch)
									echo "<td><a href=\"includes/updateprofile.php?removeGrocery=" . $row['id'] . "\">Remove Me</a></td>";
								else
									echo "<td><a href=\"includes/updateprofile.php?addGrocery=" . $row['id'] . "\">Add Me</a></td>";
							echo "</tr>";
						}
						echo "</table>";
						echo "</div>"; 
					} else {
						echo "<p>Nothing found for shared groceries!</p>";
					}
				?>
				<?php
					if ($_SESSION['role'] === 'ADMIN') {
				?>
				<h2>Add User</h2>
				<form action="includes/adduser.php" method="post">
					<label for="username">Username*: </label><input type="text" name="username" required /><br />
					<label for="name">Name*: </label><input type="text" name="name" required /><br />
					<label for="email">Email: </label><input type="email" name="email" /><br />
					<label for="paypal">PayPal.me username: </label><input type="text" name="paypal" /><br />
					<label for="role">Role: </label><select name="role">
														<option value="USER">User</option>
														<option value="ADMIN">Admin</option>
													</select>
					<p>Admins can create, delete, change user roles, and reset user passwords.</p>
					<label for="submit"></label><input type="submit" id="submit" name="create" value="Create User" />
				</form>
				<?php 
					if (isset($_SESSION['userStatus'])) {
						echo $_SESSION['userStatus'];
						unset($_SESSION['userStatus']);
					}
				?>
				<h2>Add Groceries</h2>
				<form action="profile.php?addGrocery=y" method="post">
					<p>Required items are marked with *</p>
					<label for="item">Item*: </label><input type="text" name="item" placeholder="Milk" required /><br />
					<label for="users">Add Users: </label><select name="users[]" multiple>
						<?php
						$getUsers = "SELECT id, name FROM users";
						$listUsers = $conn->query($getUsers);
						if ($listUsers->num_rows > 0) {
							while ($row = $listUsers->fetch_assoc()) {
								echo "<option value=\"" . $row['id'] . "\">" . $row['name'] . "</option>";
							}
						}
						?>
					</select><br />
					<input type="submit" value="submit" name="addGrocery" />
				</form>
				<?php 
				if ($_POST['addGrocery'] === 'submit') {
					if (!empty($_POST['item'])) {
						$item = sanitizeData($_POST['item']);
						if (!preg_match("/^[a-zA-Z0-9 ]*$/", $item)) {
							echo "<p class=\"err\">Invalid input for item, only a-z, A-Z, and spaces are allowed.</p>";
							exit();
						}
					} else {
						echo  "<p class=\"err\">Item name must be given.</p>";
						exit();
					}
					if (empty($_POST['users']))
						$addSharedGrocery = "INSERT INTO groceries (item) VALUES ('$item')";
					else {
						$users = implode(',', $_POST['users']);
						$addSharedGrocery = "INSERT INTO groceries (item, users) VALUES ('$item', '$users')";
					}
					if ($conn->query($addSharedGrocery) === TRUE) {
						echo "<p>Added " . $item . " to shared grocery list with " . count($_POST['users']) . " people added.</p>";
					} else {
						echo "<p class=\"err\">Error added shared grocery item. Unknown error.</p>";
					}
				}
				}
				?>
			</section>
		</div>
	</body>
</html>