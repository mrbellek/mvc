<?php
declare(strict_types=1);

function setReporting()
{
    error_reporting(E_ALL);
    if (defined('ENV') && (ENV == 'dev' || ENV == 'test')) {
        //dev
        ini_set('display_errors', 'On');
    } else {
        //prod
        ini_set('display_errors', 'Off');
        ini_set('log_errors', 'On');
    }
}

function callHook(string $url, array $routing, array $default)
{
    $urlArray = explode('/', $url);

    //default controller
    $urlArray[0] = (empty($urlArray[0]) ? $default['controller'] : $urlArray[0]);

    //default action
    $urlArray[1] = (empty($urlArray[1]) ? $default['action'] : $urlArray[1]);

    //pretty urls, e.g.: http://domain.com/controller/action/queryString
    $controller = array_shift($urlArray);
    $action = str_replace(['-', '_'], '', array_shift($urlArray));
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

    $httpHost = filter_input(INPUT_SERVER, 'HTTP_HOST');
    $requestUri = filter_input(INPUT_SERVER, 'REQUEST_URI');
    $httpReferrer = filter_input(INPUT_SERVER, 'HTTP_REFERER');

    ini_set('session.gc_maxlifetime', strval(7 * 24 * 3600));
    ini_set('session.cookie_lifetime', strval(7 * 24 * 3600));
    session_set_cookie_params(7 * 24 * 3600, '/', '.' . $httpHost);
    session_start();

    require_once(DOCROOT . '/src/Func/include_dir.function.php');
    include_dir(DOCROOT . '/src/Func');

    //NB: we use the Composer autoloader to load our class php files for us (as well as packages)
    require_once(DOCROOT . '/vendor/autoload.php');

    //instancing a class that doesn't exist causes a FATAL, so try/catch doesn't work
    if (!class_exists($controller)) {
        if (class_exists('MVC\\Controller\\Errorpage')) {
            redirectNotFound($requestUri, $httpReferrer);
        } else {
            printf('Controller class not found: %s', $controller);
            return;
        }
    }

    //create controller object
    $dispatch = null;
    try {
        $dispatch = new $controller($model, $controllerName, $action);
    } catch (Exception | Error $e) {
        redirectNotFound($requestUri, $httpReferrer);
    }

    //call action
    if (method_exists($controller, $action)) {
        call_user_func_array([$dispatch, $action], $queryString);
    } else {
        redirectNotFound($requestUri, $httpReferrer);
    }
}

function redirectNotFound(string $requestUri, ?string $httpReferrer): void
{
    header(sprintf(
        'Location: /errorpage/error404/%s/%s',
        base64_encode($requestUri),
        base64_encode($httpReferrer ?? '')
    ));
    exit();
}

define('TIMER_START', microtime(true));
setReporting();
callHook($url ?? '', $routing ?? [], $default ?? []);