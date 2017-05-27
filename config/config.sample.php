<?php
	/* MySQL Connection Information */
	define('DB_HOST', 'localhost');
	define('DB_USER', 'dbuser');
	define('DB_PASSWD', 'dbpass');
	define('DB_NAME', 'house');

	/* Misc MySQL Information */
	define('DB_CHARSET', 'utf8');
	define('TABLE_PREFIX', '');

	/* General Site Information */
	define('SITE_TITLE', 'Website Name');
	define('SITE_URL', 'http://example.com');
	define('PATH', '/');  // Path relative to the web server, don't add the trailing slash
	define('OSPATH', dirname(dirname(__FILE__))); // Leave this as is
	define('LOGIN_TYPE', 'local'); // Options here are google or local
	define('SSL', false); // Enable if you can use SSL on your server and you are using local LOGIN_TYPE, otherwise it's not necessary
	define('ADMIN_EMAIL', 'admin@example.com'); // Set so your users get notified when someone posts a bill regarding them
	define('USE_SMTP', false); // Enable this if you can't send email from your server and require an external SMTP server, then uncomment and set the next 4 variables
//	define('SMTP_HOST', 'smtp.example.com');
//	define('SMTP_PORT', 25);
//	define('SMTP_USER', 'username');
//	define('SMTP_PASSWD', 'password');

	require_once(OSPATH . "/includes/functions.php");
	require_once(OSPATH . "/includes/session.php");  // Comment if not using local login
//	require_once(OSPATH . PATH . "/vendor/autoload.php");   // Comment if not using google authentication

?>