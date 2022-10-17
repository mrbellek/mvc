<?php
declare(strict_types=1);

namespace MVC\Controller;

use MVC\Lib\Controller;
use MVC\Helper\Session;

class Login extends Controller {

    public function index()
    {
        if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
            $username = filter_input(INPUT_POST, 'username');
            $password = filter_input(INPUT_POST, 'password');

            if ($this->model->login($username, $password)) {
                $postLoginUrl = Session::get('post_login');
                Session::remove('post_login');
                if (!empty($postLoginUrl) && !str_contains($postLoginUrl, 'http')) {
                    $this->redirect($postLoginUrl);
                } else {
                    $this->redirect('/');
                }
            } else {
                $this->setError('Username or password invalid.');
                $this->set('post', [
                    'username' => $username,
                    'password' => $password,
                ]);
            }
        }
    }

    public function logout()
    {
        Session::remove('user');
        $this->redirect('/login');
    }
}