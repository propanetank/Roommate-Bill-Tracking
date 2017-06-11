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
				$billInfo = "SELECT bills.id, bto, name, amount, email, description, paid, paidDate FROM users, bills WHERE bills.id='$_GET[bill]' AND users.id='$_SESSION[uid]'";
				$getBill = $conn->query($billInfo);
				if ($getBill->num_rows > 0) {
					$existing = $getBill->fetch_assoc();
				} else {
					$_SESSION['errtxt'] = "Cannot modify bill. Data appears to have been modified in transit.";
					header("Location: " . SITE_URL . PATH . "profile.php?editerror=true");
				}

				$currDate = date('m/d/y');
				if (isset($_GET['delete'])) { ?>
				<h2>Delete Bill</h2>
				<?php 
					$deleteBill = $conn->query("UPDATE bills SET deleted='Y', deletedDate='$currDate' WHERE id='$existing[id]'");
					if ($deleteBill === TRUE) {
						echo "<p>Removed bill to <b>" . $existing['name'] . "</b> in the amount of <b>$" . $existing['amount'] . "</b>";
						if ($existing['description'] != '')
							echo  " for <b>" . $existing['description'] . "</b>. ";
						else
							echo ".</p>";
						if ($existing['email'] != '') {
							// Email the user that a bill has been deleted
							$mailTo =  $existing['name'] . " <" . $existing['email'] . ">";
							$mailHeaders = "FROM: " . ADMIN_EMAIL . "\r\n";
							if (ADMIN_REPLY != '')
								$mailHeaders .= "Reply-To: " . ADMIN_REPLY . "\r\n";
							$mailHeaders .= "MIME-Version: 1.0\r\n";
							$mailHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";
							$mailSubject = $_SESSION['name'] . " has deleted a bill";
							$mailMessage = "<h3>Hello " . $existing['name'] . "!</h3>
							<p>" . $_SESSION['name'] . " has removed the following bill from your account: ";
								if ($existing['description'] != '')
									$mailMessage .= $existing['description'];
								else
									$mailMessage .= "<i>No description</i>";
									$mailMessage .= " for " . $existing['amount'] . "</p>
							<p>--<br />
							The admins at " . SITE_TITLE . "<br />
							<i>Please note that this email box might not be monitored and may be used solely for sending email.</i></p>";
							if (USE_SMTP === FALSE) {
								// Send email via built-in mail function
								mail($mailTo, $mailSubject, $mailMessage, $mailHeaders);
							} else {
								// Send email via SMTP
							}
						}
						echo "<a href=\"" . PATH . "profile.php\">Return to profile</a>.</p>";
					} else
						echo "<p class=\"err\">Error deleting bill.</p>";
				} else if (isset($_GET['recover'])) { ?>
				<h2>Recover Bill</h2>
					<?php
						$recoverBill = $conn->query("UPDATE bills SET deleted='N', deletedDate=NULL WHERE id='$existing[id]'");
						if ($recoverBill === TRUE) {
							echo "<p>Recovered bill to <b>" . $existing['name'] . "</b> in the amount of <b>$" . $existing['amount'] . "</b>";
							if ($existing['description'] != '')
								echo  " for <b>" . $existing['description'] . "</b>. ";
							else
								echo ". ";
							if ($existing['email'] != '') {
								// Email the user that a bill has been restored
								$mailTo =  $existing['name'] . " <" . $existing['email'] . ">";
								$mailHeaders = "FROM: " . ADMIN_EMAIL . "\r\n";
								if (ADMIN_REPLY != '')
									$mailHeaders .= "Reply-To: " . ADMIN_REPLY . "\r\n";
								$mailHeaders .= "MIME-Version: 1.0\r\n";
								$mailHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";
								$mailSubject = $_SESSION['name'] . " has restored a bill";
								$mailMessage = "<h3>Hello " . $existing['name'] . "!</h3>
								<p>" . $_SESSION['name'] . " has restored the following bill to your account: ";
									if ($existing['description'] != '')
										$mailMessage .= $existing['description'];
									else
										$mailMessage .= "<i>No description</i>";
										$mailMessage .= " for " . $existing['amount'] . "</p>
								<p>--<br />
								The admins at " . SITE_TITLE . "<br />
								<i>Please note that this email box might not be monitored and may be used solely for sending email.</i></p>";
								if (USE_SMTP === FALSE) {
									// Send email via built-in mail function
									mail($mailTo, $mailSubject, $mailMessage, $mailHeaders);
								} else {
									// Send email via SMTP
								}
							}
							echo "<a href=\"" . PATH . "profile.php\">Return to profile</a>.</p>";
						} else
							echo "<p class=\"err\">Error recovering bill.</p>";
				} else if ($_GET['edit'] === 'y') {
					// Make sure the Bill To isn't null
					if (!isset($_POST['to']))
						$_SESSION['toErrtxt'] = "Invalid person to send a bill to.";

					// Validate the data in the amount field is what we intended
					if (!empty($_POST['amount'])) {
						if (!preg_match("/^\d{1,3}\.\d{2}$/", $_POST['amount'])) {
							$_SESSION['amtErrtxt'] = "Invalid dollar amount, must be more than $0 and less than $1000 with the cents (including 00 cents)";
						} else {
							// Convert the string to a number format so we can make sure they entered a value larger than 0
							$amount = $_POST['amount'];
						}
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
						$_SESION['description'] = $_POST['description'];
						header("Location: " . SITE_URL . PATH . "editbill.php?bill=" . $existing['id']);
						exit(1);
					}
					if ($existing['paid'] === 'Y' && $_POST['paid'] === 'N')
						$updateBill = "UPDATE bills SET bto='$_POST[to]', amount='$amount', description='$description', paid='$_POST[paid]', paidDate=NULL WHERE id='$existing[id]'";
					else if ($existing['paid'] === 'N' && $_POST['paid'] === 'Y')
						$updateBill = "UPDATE bills SET bto='$_POST[to]', amount='$amount', description='$description', paid='$_POST[paid]', paidDate='$currDate' WHERE id='$existing[id]'";
					else
						$updateBill = "UPDATE bills SET bto='$_POST[to]', amount='$amount', description='$description', paid='$_POST[paid]' WHERE id='$existing[id]'";
					if ($conn->query($updateBill) === TRUE) {
						if ($existing['email'] != '') {
							// Email the user that a bill has been modified
							$mailTo =  $existing['name'] . " <" . $existing['email'] . ">";
							$mailHeaders = "FROM: " . ADMIN_EMAIL . "\r\n";
							if (ADMIN_REPLY != '')
								$mailHeaders .= "Reply-To: " . ADMIN_REPLY . "\r\n";
							$mailHeaders .= "MIME-Version: 1.0\r\n";
							$mailHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";
							$mailSubject = $_SESSION['name'] . " has modified a bill";
							$mailMessage = "<h3>Hello " . $existing['name'] . "!</h3>
							<p>" . $_SESSION['name'] . " has modified the following bill: ";
								if ($existing['description'] != '')
									$mailMessage .= $existing['description'];
								else
									$mailMessage .= "<i>No description</i>";
							$mailMessage .= " for " . $existing['amount'] . "
							marked as ";
								if ($existing['paid'] === 'Y')
									$mailMessage .= "paid";
								else
									$mailMessage .= "unpaid";
							$mailMessage .= ".</p><p>The new bill is now: ";
							if (isset($description))
									$mailMessage .= $_POST['description'];
								else
									$mailMessage .= "<i>No description</i>";
							$mailMessage .= " for " . $amount . "
							marked as ";
								if ($_POST['paid'] === 'Y')
									$mailMessage .= "paid";
								else
									$mailMessage .= "unpaid";
							$mailMessage .= ".</p><p>--<br />
							The admins at " . SITE_TITLE . "<br />
							<i>Please note that this email box might not be monitored and may be used solely for sending email.</i></p>";
							if (USE_SMTP === FALSE) {
								// Send email via built-in mail function
								mail($mailTo, $mailSubject, $mailMessage, $mailHeaders);
							} else {
								// Send email via SMTP
							}
						}
						echo "<p>Bill has been successfully updated. <a href=\"" . PATH . "profile.php\">Return to profile.</a></p>";
					} else
						echo "<p class=\"err\">" . $conn->error . "<a href=\"" . PATH . "profile.php\">Return to profile.</a></p>";
				} else { ?>
				<h2>Edit Bill</h2>
				<form action="<?php echo PATH . "editbill.php?bill=" . $existing['id'] . "&edit=y"; ?>" method="post">
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
								$ <input type="text" name="amount" value="<?php
																			if (isset($_SESSION['amt'])) {
																				echo $_SESSION['amt'];
																				unset($_SESSION['amt']);
																			} else
																				echo $existing['amount'];
																			?>" placeholder="0.00" step="0.01" maxlength="6" size="6" required />
							</td>
							<td>
								<input type="text" name="description" value="<?php
																				if (isset($_SESSION['description'])) {
																				echo $_SESSION['description'];
																				unset($_SESSION['description']);
																			} else
																				echo $existing['description'];
																			?>" placeholder="Internet" maxlength="50" />
							</td>
							<td>
								<input type="radio" name="paid" value="Y" <?php if ($existing['paid'] === 'Y') echo "checked" ?> />Yes
								<input type="radio" name="paid" value="N" <?php if ($existing['paid'] === 'N') echo "checked" ?> />No
							</td>
							<td>
								<input type="submit" value="Update" name="submit" />
							</td>
						</tr>
					</table>
					<?php if (isset($_GET['error'])) {
						if (isset($_SESSION['toErrtxt'])) {
							echo "<p class=\"err\">" . $_SESSION['toErrtxt'] . "</p>";
							unset($_SESSION['toErrtxt']);
						}
						if (isset($_SESSION['amtErrtxt'])) {
							echo "<p class=\"err\">" . $_SESSION['amtErrtxt'] . "</p>";
							unset($_SESSION['amtErrtxt']);
						}
						if (isset($_SESSION['descErrtxt'])) {
							echo "<p class=\"err\">" . $_SESSION['descErrtxt'] . "</p>";
							unset($_SESSION['descErrtxt']);
						}
						if (isset($_SESSION['errtxt'])) {
							echo "<p class=\"err\">" . $_SESSION['errtxt'] . "</p>";
							unset($_SESSION['errtxt']);
						}
					} ?>
				</form>
				<?php } ?>
			</section>
		</div>
	</body>
</html>