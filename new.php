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
		<title><?php echo SITE_TITLE; ?> | New <?php echo ucfirst($_GET['type']); ?></title>
	</head>
	<body>
		<section id="nav">
			<?php require("includes/nav.php"); ?>
		</section>
		<div id="container">
			<section id="primary">
				<h1>New Bill</h1>
				<form action="<?php echo PATH; ?>/includes/submitnew.php?type=bill" method="post">
					<p>Fields labeled with <span class="error">*</span> are required.</p>
					<p>To select more than one recipient, hold <i>control</i>. When selecting more that one recipient, enter the full bill amount.</p>
					<table>
						<tr>
							<th>From</th>
							<th>To<span class="error">*</span></th>
							<th>Amount<span class="error">*</span></th>
							<th>Description</th>
							<th></th>
						</tr>
						<tr>
							<td>
								<input type="text" name="from" value="<?php echo $_SESSION['name'] ?>" readonly required style="cursor: not-allowed;" /></td>
							<td>
								<select name="to[]" multiple required>
									<?php
									$getUsers = "SELECT id, name FROM users WHERE id!='$_SESSION[uid]'";
									$listUsers = $conn->query($getUsers);
									if ($listUsers->num_rows > 0) {
										while ($row = $listUsers->fetch_assoc()) {
											echo "<option value=\"" . $row['id'] . "\">" . $row['name'] . "</option>";
										}
									} else {
										echo "<option disabled>No Data</option>";
									}
									?>
								</select>
							</td>
							<td>
								$ <input type="number" name="amount" placeholder="0.00" maxlength="6" size="6" value="<?php
																														if (isset($_SESSION['amt'])) {
																															echo $_SESSION['amt'];
																															unset ($_SESSION['amt']);
																														}
																													?>" required />
							</td>
							<td>
								<input type="text" name="description" placeholder="Internet" value="<?php
																										if (isset($_SESSION['desc'])) {
																											echo $_SESSION['desc'];
																											unset($_SESSION['desc']);
																										}
																									?>" maxlength="50" />
							</td>
							<td>
								<input type="submit" value="Send" name="submit" />
							</td>
						</tr>
					</table>
					<?php
						if (isset($_GET['error'])) {
							if (isset($_SESSION['toErrtxt'])) {
								echo "<p class='err'>" . $_SESSION['toErrtxt'] . "</p>";
								unset($_SESSION['toErrtxt']);
							}
							if (isset($_SESSION['amtErrtxt'])) {
								echo "<p class='err'>" . $_SESSION['amtErrtxt'] . "</p>";
								unset($_SESSION['amtErrtxt']);
							}
							if (isset($_SESSION['descErrtxt'])) {
								echo "<p class='err'>" . $_SESSION['descErrtxt'] . "</p>";
								unset($_SESSION['descErrtxt']);
							}
							if (isset($_SESSION['errtxt'])) {
								echo "<p class='err'>" . $_SESSION['errtxt'] . "</p>";
								unset($_SESSION['errtxt']);
							}
						}
					?>
				</form>
				<?php if (isset($_GET['success'])) {
					echo "<p>Bill sent successfully</p>";
					echo "<p>To: ";
					foreach ($_SESSION['to'] as $value) {
						if (count($_SESSION['to']) > 1)
							echo $value . ", ";
						else
							echo $value;
					}
					echo "</p>";
					echo "<p>Amount: $" . $_SESSION['amount'] . "</p>";
					echo "<p>Description: " . $_SESSION['description'] . "</p>";
					echo "<p>To add another, fill out the form again.</p>";
					unset($_SESSION['to']);
					unset($_SESSION['amount']);
					unset($_SESSION['description']);
				} ?>
			</section>
		</div>
	</body>
</html>
