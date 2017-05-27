<?php

	// Keeps session active for 30 days on every page load when the user logged in with the 'remember me' check box selected
	if (isset($_SESSION['remember'])) {
		session_set_cookie_params(2592000);
	}
?>