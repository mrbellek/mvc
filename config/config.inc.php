<?php
declare(strict_types=1);

$httpHost = filter_input(INPUT_SERVER, 'HTTP_HOST');

//localhost and phpdev.nl: DEV
if (str_contains($httpHost, 'localhost') || str_contains($httpHost, 'phpdev.nl')) {

    define('ENV', 'dev');
    define('DB_HOST', 'localhost');

//phptest.nl: TEST
} elseif (str_contains($httpHost, 'phptest.nl')) {

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