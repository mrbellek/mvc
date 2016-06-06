<?php
namespace MVC\Controller;
use MVC\Lib\Controller;

class Login extends Controller {

    public function index() {

        if ($_POST) {
            if ($this->model->login($_POST)) {
				if (!empty($_SESSION['post_login']) && strpos($_SESSION['post_login'], 'http') === FALSE) {
					$sRedirectUrl = $_SESSION['post_login'];
					unset($_SESSION['post_login']);
					$this->redirect($sRedirectUrl);
				} else {
					$this->redirect('/');
				}
            } else {
                $this->setError('Emailadres or password invalid.');
                $this->set('post', $_POST);
            }
        }
    }

    public function logout() { 

        unset($_SESSION['user']);
        $this->redirect('/login');
    }
}
