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
		<title><?php echo SITE_TITLE; ?> | Edit Bill</title>
	</head>
	<body>
		<section id="nav">
			<?php require("includes/nav.php"); ?>
		</section>
		<div id="container">
			<section id="primary">
				<?php 
				// Make sure someone didn't modify the data between pages and if so, cancel editing the bill because it might not be their bill
				$billInfo = "SELECT bills.id, bto, name, amount, description, paid FROM users, bills WHERE bills.id='$_GET[bill]' AND users.id='$_SESSION[uid]'";
				$getBill = $conn->query($billInfo);
				if ($getBill->num_rows > 0) {
					$existing = $getBill->fetch_assoc();
				} else {
					$_SESSION['errtxt'] = "Cannot modify bill. Data appears to have been modified in transit.";
					header("Location: " . PATH . "/profile.php?editerror=true");
				}
				if(!isset($_GET['deleted'])) { ?>
				<h1>Edit Bill</h1>
				<form action="<?php echo PATH; ?>/includes/editbill.php?bill=<?php echo $exisiting['bill']; ?>" method="post">
					<table>
						<tr>
							<th>From</th>
							<th>To<span class="error">*</span></th>
							<th>Amount<span class="error">*</span></th>
							<th>Description</th>
							<th>Paid</th>
							<th></th>
						</tr>
						<tr>
							<td>
								<input type="text" name="from" value="<?php echo $existing['name']; ?>" readonly required /></td>
							<td>
								<select name='to' required>
									<?php
									$getUsers = "SELECT id, name FROM users WHERE id!='$_SESSION[uid]'";
									$listUsers = $conn->query($getUsers);
									if ($listUsers->num_rows > 0) {
										while ($row = $listUsers->fetch_assoc()) {
											if ($row['id'] == $existing['bto'])
												echo "<option value=\"" . $row['id'] . "\" selected >" . $row['name'] . "</option>";
											else
												echo "<option value=\"" . $row['id'] . "\">" . $row['name'] . "</option>";
										}
									} else {
										echo "<option value='null' disabled>No Data</option>";
									}
									?>
								</select>
							</td>
							<td>
								$ <input type="text" name="amount" value="<?php echo $existing['amount']; ?>" placeholder="0.00" maxlength="6" size="6" required />
							</td>
							<td>
								<input type="text" name="description" value="<?php echo $existing['description']; ?>" placeholder="Internet" maxlength="50" />
							</td>
							<td>
								<input type="radio" name="paid" value="yes" <?php if ($existing['paid'] === 'Y') echo "checked" ?> />Yes
								<input type="radio" name="paid" value="no" <?php if ($existing['paid'] === 'N') echo "checked" ?> />No
							</td>
							<td>
								<input type="submit" value="Update" name="submit" />
							</td>
						</tr>
					</table>
					<?php if (isset($_GET['error'])) {
						if (isset($_SESSION['toErrtxt'])) {
							echo "<p class='error'>" . $_SESSION['toErrtxt'] . "</p>";
							unset($_SESSION['toErrtxt']);
						}
						if (isset($_SESSION['amtErrtxt'])) {
							echo "<p class='error'>" . $_SESSION['amtErrtxt'] . "</p>";
							unset($_SESSION['amtErrtxt']);
						}
						if (isset($_SESSION['descErrtxt'])) {
							echo "<p class='error'>" . $_SESSION['descErrtxt'] . "</p>";
							unset($_SESSION['descErrtxt']);
						}
						if (isset($_SESSION['errtxt'])) {
							echo "<p class='error'>" . $_SESSION['errtxt'] . "</p>";
							unset($_SESSION['errtxt']);
						}
					} ?>
				</form>
				<?php } else { ?>
				<h2>Delete Bill</h2>

				<?php } ?>
			</section>
		</div>
	</body>
</html>
