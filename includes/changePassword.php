<?php
	require_once(dirname( __FILE__ , 2) . "/config/config.php");
	session_start();
	checkDatabaseConn();
	if (!userLoggedIn()) {
		redirectLogin();
	}
?>

<!DOCTYPE html>
	<head>
		<link rel="stylesheet" href="<?php echo PATH; ?>/includes/default.css" type="text/css" />
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script type="text/javascript">
			function checkSame() {
				var newPass = $("#newPass").val();
				var confirmPass = $("#confirmPass").val();

				if (newPass != confirmPass) {
					$(".err").first().html("Passwords don't match");
				} else {
					$(".err").first().html(" ");
				}

				$(document).ready(function () {
					$("#confirmPass").keyup(checkSame);
				});
			}
		</script>
		<title><?php echo SITE_TITLE; ?> | Change Password</title>
	</head>
	<body>
		<div id="container">
			<section id="primary">
				<h1>Password Change</h1>
				<form name="changePass" action="changePassword.php" method="post">
					<label for="current">Current Password: </label><input type="password" class="login" name="current" value="<?php echo $_SESSION['password']; ?>" /><br />
					<label for="newPass">New Password: </label><input type="password" class="login" name="new" onBlur="checkSame()" /><br />
					<label for="confirmPass">Confirm Password: </label><input type="password" class="login" name="confirm" onBlur="checkSame()" /><br />
					<?php
						echo "<p class=\"err\">" . $_SESSION['errtxt'] . "</p>";
						if (isset($_SESSION['errtxt']))
							unset($_SESSION['errtxt']);
					?>
					<label for="submit"></label><input type="submit" class="loginSub" name="submitted" value="Change Password" />
				</form>
				<?php if ($_SERVER['REQUEST_METHOD'] === 'POST') {
						// Verify the passwords still match
						if (!($_POST['new'] === $_POST['confirm'])) {
							$_SESSION['errtxt'] = "Passwords do not match, please try again.";
							header("Location: changePassword.php");
						} else {
							$pass = getUserPassword();
							if ($pass === FALSE) {
								goto end;
							}
							$salt = $pass[1];

							$oldPassword = crypt($_POST['current'], '$6$' . $salt);

							// Make a new salt everytime the user changes their password
							$salt = bin2hex(random_bytes(5));
							$password = crypt($_POST['new'], '$6$' . $salt);

							if ($pass[0] != $oldPassword) {
								goto end;
							}

							$updatePasswd = "UPDATE users SET password='$password', resetRequired='N' WHERE id='$_SESSION[uid]'";
							if ($conn->query($updatePasswd) === TRUE) {
								unset($_SESSION['password']);
								unset($_SESSION['resetRequired']);
								echo "<p>Password updated sucessfully! <a href=\"" . PATH . "/dashboard.php\">Go Home</a>.</p>";
							} else {
								end:
								echo "<p class=\"err\">Unable to update password. Maybe the current password you submitted doesn't match the one saved in your account.</p>";
							}
						}
					} ?>
			</section>
		</div>
	</body>
</html>