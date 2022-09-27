<?php
declare(strict_types=1);

define('DOCROOT', str_replace('/public', '', getcwd()));
define('NOCACHE', isset($_GET['nocache']) || isset($_GET['NOCACHE']));
define('CLEARCACHE', isset($_GET['clearcache']) || isset($_GET['CLEARCACHE']));

$url = filter_input(INPUT_GET, 'url') ?? '';

require_once(DOCROOT . '/lib/bootstrap.php');