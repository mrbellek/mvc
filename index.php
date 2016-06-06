<?php
define('DOCROOT', str_replace('/public', '', getcwd()));
define('NOCACHE', (isset($_GET['nocache']) || isset($_GET['NOCACHE']) ? TRUE : FALSE));
define('CLEARCACHE', (isset($_GET['clearcache']) || isset($_GET['CLEARCACHE']) ? TRUE : FALSE));

$url = (!empty($_GET['url']) ? $_GET['url'] : '');

require_once(DOCROOT . '/lib/bootstrap.php');
