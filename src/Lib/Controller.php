<?php
namespace MVC\Lib;
use MVC\Lib\Template;
use MVC\Lib\Db;
use MVC\Helper\Cache;
use MVC\Helper\Mailer;

class Controller {

	protected $_model;
	protected $_controller;
	protected $_action;
	protected $_template;

	//page load timer
	private $start;

	//pages that don't require user to be logged in
	private $openPages = array(
		'home',
		'errorpage',
		'login',
        'register',
	);

	private $adminPages = array(
	);

	//flag to prevent render, for page that just redirect
	protected $doNotRender = FALSE;

	public function __construct($model, $controller, $action) {

		$this->_controller = $controller;
		$this->_action = $action;
		$this->_model = $model;

		$this->model = new $model;
		$this->_template = new Template($controller, $action);

		//caching is only available in controller because model is the wrong place for that
		$this->Cache = Cache::getInstance();
		if (CLEARCACHE) {
			$this->Cache->clear();
		}

		if (isset($_SESSION['user'])) {
			//set user session var
			$this->set('session', $_SESSION['user']);

			if (!empty($_SESSION['user']['roles']) && !$_SESSION['user']['roles']['is_admin'] && in_array($controller, $this->adminPages)) {
				$this->setDelayedError('You need to be admin to view this page.');
				$this->redirect('/');
			}
		} else {
			//show login screen for restricted pages if user is not logged in
			if (empty($_SESSION['user']) && !in_array($controller, $this->openPages) && !in_array($controller . '/' . $action, $this->openPages)) {
				$this->setDelayedError('You need to login to view this page.');
				$_SESSION['post_login'] = $_SERVER['REQUEST_URI'];
				$this->redirect('/login');
			}
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

		if (!empty($_GET['emailtest'])) {
			$oMail = new Mailer();
			$oMail->test($_GET['emailtest']);
		}
	}

	//set template var
	public function set($name, $value) {
		$this->_template->set($name, $value);
	}

	public function setInfo($sMessage) {

		return $this->setMessage('info', $sMessage);
	}

	public function setError($sMessage) {

		return $this->setMessage('danger', $sMessage);
	}

	public function setMessage($sType, $sMessage) {

		return $this->_template->set('_message', array('type' => $sType, 'message' => $sMessage));
	}

	public function setDelayedInfo($sMessage) {

		return $this->setDelayedMessage('info', $sMessage);
	}

	public function setDelayedError($sMessage) {

		return $this->setDelayedMessage('danger', $sMessage);
	}

	public function setDelayedMessage($sType, $sMessage) {

		$_SESSION['msg'] = array('type' => $sType, 'message' => $sMessage);

		return TRUE;
	}

	//wrapper call for including css
	public function includeCss($content) {
		return $this->_template->includeExternal('css', $content);
	}

	//wrapper call for including js
	public function includeJs($content) {
		return $this->_template->includeExternal('js', $content);
	}

	//wrapper call for redirects
	public function redirect($url) {
		header('Location: ' . $url);
		$this->doNotRender = TRUE;
		exit();
	}

	//render page
	public function __destruct() {
		if (!$this->doNotRender) {
			$this->set('timer_pageload', round(microtime(TRUE) - TIMER_START, 2) . 's');
			$this->set('timer_database', Db::getInstance()->getQueryStats());
			$this->set('session_print', print_r($_SESSION, TRUE));
			$this->_template->render();
		}
	}
}
