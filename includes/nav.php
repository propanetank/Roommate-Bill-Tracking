<nav>
	<ul>
		<?php
			if (!userLoggedIn()) {
				echo "<li><a href=\"" . PATH . "login.php\">Login</a></li>";
			} else {
				echo "<li><a href=\"" . PATH . "dashboard.php\">Dashboard</a></li>";
				echo "<li><a href=\"" . PATH . "new.php?type=bill\">New Bill</a></li>";
				echo "<li><a href=\"" . PATH . "profile.php\">Profile</a></li>";
				echo "<li><a href=\"" . PATH . "logout.php\">Logout</a></li>";
			} ?>
	</ul>
</nav>
<div>
	<h1><?php echo SITE_TITLE; ?></h1>
</div>