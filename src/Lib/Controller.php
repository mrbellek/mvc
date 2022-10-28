<?php
declare(strict_types=1);

namespace MVC\Lib;

use JetBrains\PhpStorm\NoReturn;
use MVC\Lib\Template;
use MVC\Lib\Db;
use MVC\Helper\Cache;
use MVC\Helper\Mailer;
use MVC\Helper\Session;
use PHPMailer\PHPMailer\Exception as MailerException;

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

        $userSession = Session::get('user');
        if ($userSession) {
            //set user session var
            $this->set('session', $userSession);
        }

        if ($userSession && empty($userSession['is_admin']) && in_array($controller, $this->adminPages)) {
            $this->setDelayedError('You need to be admin to view this page.');
            $this->redirect('/');
        }

        if (!$userSession && !in_array($controller, $this->openPages) && !in_array($controller . '/' . $action, $this->openPages)) {
            //show login screen for restricted pages if user is not logged in
            $this->setDelayedError('You need to login to view this page.');
            Session::set('post_login', filter_input(INPUT_SERVER, 'REQUEST_URI'));
            $this->redirect('/login');
        }

        //set any error/warning/info message set in session
        if ($msgSession = Session::get('msg')) {
            $this->set('_message', $msgSession);
            Session::remove('msg');
        }

        //include css
        $this->includeCss('/css/mvc.css');

        //include js (jquery first so we can use it)
        $this->includeJs('https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js');
        $this->includeJs('/js/mvc.js');

        //allow mailer testing
        if (!empty($emailTest = filter_input(INPUT_GET, 'emailtest'))) {
            try {
                $oMail = new Mailer();
                $oMail->test($emailTest);
                $this->setInfo(sprintf('Test email sent to %s successfully', $emailTest));
            } catch (MailerException $e) {
                $this->setError(sprintf('Test email to %s failed: %s', $emailTest, $e->getMessage()));
            }
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
        Session::set('msg', ['type' => $sType, 'message' => $sMessage]);
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
    #[NoReturn]
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
            $this->set('session_print', print_r(Session::getAll(), true));
            $this->template->render();
        }
    }
}