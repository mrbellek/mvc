<?php
$httpHost = filter_input(INPUT_SERVER, 'HTTP_HOST');

//localhost and phpdev.nl: DEV
if (strpos($httpHost, 'localhost') !== false || strpos($httpHost, 'phpdev.nl') !== false) {

    define('ENV', 'dev');
    define('DB_HOST', 'localhost');

//phptest.nl: TEST
} elseif (strpos($httpHost, 'phptest.nl') !== false) {

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