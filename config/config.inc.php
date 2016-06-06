<?php
//localhost and phpdev.nl: DEV
if ($_SERVER['SERVER_ADDR'] == '127.0.0.1' ||
	strpos($_SERVER['HTTP_HOST'], 'localhost') !== FALSE ||
	strpos($_SERVER['HTTP_HOST'], 'phpdev.nl') !== FALSE) {

	define('ENV', 'dev');
	define('DB_HOST', '');

//phptest.nl: TEST
} elseif (strpos($_SERVER['HTTP_HOST'], 'phptest.nl') !== FALSE) {

	define('ENV', 'test');
	define('DB_HOST', '127.0.0.1');

//everything else: PROD
} else {
	define('ENV', 'prod');
	define('DB_HOST', 'db');
}

define('DB_DATABASE', '');
define('DB_USERNAME', '');
define('DB_PASSWORD', '');

define('SALT', 'MBSalty');
define('WATTS', 'AllMyWatts');
