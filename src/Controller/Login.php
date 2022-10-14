<?php
declare(strict_types=1);

namespace MVC\Controller;

use MVC\Lib\Controller;

class Login extends Controller {

    public function index()
    {
        if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
            $username = filter_input(INPUT_POST, 'username');
            $password = filter_input(INPUT_POST, 'password');

            if ($this->model->login($username, $password)) {
                if (!empty($_SESSION['post_login']) && !str_contains($_SESSION['post_login'], 'http')) {
                    $redirectUrl = $_SESSION['post_login'];
                    unset($_SESSION['post_login']);
                    $this->redirect($redirectUrl);
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
        unset($_SESSION['user']);
        $this->redirect('/login');
    }
}