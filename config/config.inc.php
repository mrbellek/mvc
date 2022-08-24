<?php
//localhost and phpdev.nl: DEV
if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
	strpos($_SERVER['HTTP_HOST'], 'phpdev.nl') !== false) {

	define('ENV', 'dev');
	define('DB_HOST', 'localhost');

//phptest.nl: TEST
} elseif (strpos($_SERVER['HTTP_HOST'], 'phptest.nl') !== false) {

	define('ENV', 'test');
	define('DB_HOST', '127.0.0.1');

//everything else: PROD
} else {
	define('ENV', 'prod');
	define('DB_HOST', 'db');
}

const DB_DATABASE = 'mvc';
const DB_USERNAME = 'mvc';
const DB_PASSWORD = 'mYmVc2022';

const SALT = 'MBSalty';
const WATTS = 'AllMyWatts';