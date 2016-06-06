<?php
function setReporting() {
	if (defined('ENV') && ENV == 'dev' || ENV == 'test') {
		//dev
		error_reporting(E_ALL);
		ini_set('display_errors', 'On');
	} else {
		//prod
		error_reporting(E_ALL);
		ini_set('display_errors', 'On');
		//ini_set('display_errors', 'Off');
		ini_set('log_errors', 'On');
		ini_set('error_log', DOCROOT . '/tmp/logs/error.log');
	}
}

function callHook($url, $routing, $default) {
	$urlArray = explode('/', $url);
	//default controller
	$urlArray[0] = (empty($urlArray[0]) ? $default['controller'] : $urlArray[0]);
	//default action
	$urlArray[1] = (empty($urlArray[1]) ? $default['action'] : $urlArray[1]);

	//pretty urls: http://domain.com/controller/action/queryString
	$controller = array_shift($urlArray);
	$action = array_shift($urlArray);
	$queryString = $urlArray;

    //custom routing
    if (isset($routing[$controller . '/' . $action])) {
        $reroute = $routing[$controller . '/' . $action];
        $controller = key($reroute);
        $action = $reroute[$controller];
    }

	$controllerName = $controller;
	$controller = ucwords($controller);
	$model = 'MVC\\Model\\' . $controller;
	$controller = 'MVC\\Controller\\' . $controller;

	session_set_cookie_params(7 * 24 * 3600, '/', '.' . $_SERVER['HTTP_HOST']);
	session_start();
	ini_set('session.gc_maxlifetime', 7 * 24 * 3600);
	ini_set('session.cookie_lifetime', 7 * 24 * 3600);

	require_once(DOCROOT . '/src/Func/include_dir.function.php');
	include_dir(DOCROOT . '/src/Func');

	//NB: we use the Composer autoloader to load our class php files for us (as well as packages)
	require_once(DOCROOT . '/vendor/autoload.php');

	//instancing a class that doesn't exists causes a FATAL, so try/catch doesn't work
	if (!class_exists($controller)) {
		header('Location: /errorpage/error404/' . base64_encode($_SERVER['REQUEST_URI']) . '/' . base64_encode(@$_SERVER['HTTP_REFERER']));
		exit();
	}

	//create controller object
	try {
		$dispatch = new $controller($model, $controllerName, $action);
	} catch(Exception $e) {
		header('Location: /errorpage/error404/' . base64_encode($_SERVER['REQUEST_URI']) . '/' . base64_encode(@$_SERVER['HTTP_REFERER']));
		exit();
	}

	//call action
	if (method_exists($controller, $action)) {
		call_user_func_array(array($dispatch, $action), $queryString);
	} else {
		header('Location: /errorpage/error404/' . base64_encode($_SERVER['REQUEST_URI']) . '/' . base64_encode(@$_SERVER['HTTP_REFERER']));
		exit();
	}
}

define('TIMER_START', microtime(TRUE));
setReporting();
callHook($url, $routing, $default);
