<?php
declare(strict_types=1);

namespace MVC\Lib;

use MVC\Lib\Template;
use MVC\Lib\Db;
use MVC\Helper\Cache;
use MVC\Helper\Mailer;

class Controller
{
    protected string $modelStr;
    protected string $controllerStr;
    protected string $actionStr;
    protected Template $template;

    /**
     * @var Model
     */
    protected $model;
    protected $cache;

    //page load timer
    private $start;

    //pages that don't require user to be logged in
    private array $openPages = [
        'home',
        'errorpage',
        'login',
        'register',
    ];

    private array $adminPages = [
        'user',
    ];

    //flag to prevent render, for page that just redirect
    protected bool $doNotRender = false;

    public function __construct(string $model, string $controller, string $action)
    {
        $this->controllerStr = $controller;
        $this->actionStr = $action;
        $this->modelStr = $model;

        $this->model = new $model();
        $this->template = new Template($controller, $action);

        //caching is only available in controller because model is the wrong place for that
        $this->cache = Cache::getInstance();
        if (defined('CLEARCACHE')) {
            $this->cache->clear();
        }

        if (!empty($_SESSION['user'])) {
            //set user session var
            $this->set('session', $_SESSION['user']);

            if (empty($_SESSION['user']['is_admin']) && in_array($controller, $this->adminPages)) {
                $this->setDelayedError('You need to be admin to view this page.');
                $this->redirect('/');
            }
        } elseif (!in_array($controller, $this->openPages) && !in_array($controller . '/' . $action, $this->openPages)) {
            //show login screen for restricted pages if user is not logged in
            $this->setDelayedError('You need to login to view this page.');
            $_SESSION['post_login'] = filter_input(INPUT_SERVER, 'REQUEST_URI');
            $this->redirect('/login');
        }

        //set any error/warning/info message set in session
        if (isset($_SESSION['msg'])) {
            $this->set('_message', $_SESSION['msg']);
            unset($_SESSION['msg']);
        }

        //include css
        $this->includeCss('/css/mvc.css');

        //include js (jquery first so we can use it)
        $this->includeJs('https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js');
        $this->includeJs('/js/mvc.js');

        if (!empty(filter_input(INPUT_GET, 'emailtest'))) {
            $oMail = new Mailer();
            $oMail->test($_GET['emailtest']);
        }
    }

    //set template var
    public function set($name, $value): void
    {
        $this->template->set($name, $value);
    }

    public function setInfo($sMessage): void
    {
        $this->setMessage('info', $sMessage);
    }

    public function setError($sMessage): void
    {
        $this->setMessage('danger', $sMessage);
    }

    public function setMessage($sType, $sMessage): void
    {
        $this->template->set('_message', ['type' => $sType, 'message' => $sMessage]);
    }

    public function setDelayedInfo($sMessage): void
    {
        $this->setDelayedMessage('info', $sMessage);
    }

    public function setDelayedError($sMessage): void
    {
        $this->setDelayedMessage('danger', $sMessage);
    }

    public function setDelayedMessage($sType, $sMessage): void
    {
        $_SESSION['msg'] = ['type' => $sType, 'message' => $sMessage];
    }

    //wrapper call for including css
    public function includeCss(string $content): void
    {
        $this->template->includeExternal('css', $content);
    }

    //wrapper call for including js
    public function includeJs($content): void
    {
        $this->template->includeExternal('js', $content);
    }

    //wrapper call for redirects
    public function redirect($url): void
    {
        header('Location: ' . $url);
        $this->doNotRender = true;
        exit();
    }

    //render page
    public function __destruct()
    {
        if (!$this->doNotRender) {
            $this->set('timer_pageload', round(microtime(true) - TIMER_START, 2) . 's');
            $this->set('timer_database', Db::getInstance()->getQueryStats());
            $this->set('session_print', print_r($_SESSION, true));
            $this->template->render();
        }
    }
}