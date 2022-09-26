<?php
declare(strict_types=1);

namespace MVC\Controller;

use MVC\Lib\Controller;
use Exception;

class Register extends Controller {

    public function index() {

        if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
            try {
                $username = filter_input(INPUT_POST, 'username');
                $password = filter_input(INPUT_POST, 'password');
                $passwordVerify = filter_input(INPUT_POST, 'passwordVerify');
                $admin = filter_input(INPUT_POST, 'admin');

                if ($this->model->validate($username, $password, $passwordVerify)) {
                    if ($this->model->registerAndLogin($username, $password, $admin)) {

                        $this->setDelayedInfo('Registration successful.');
                        $this->redirect('/');
                    } else {
                        $this->setError('Registration failed.');
                        $this->set('post', [
                            'username' => $username,
                            'password' => $password,
                            'passwordVerify' => $passwordVerify,
                        ]);
                    }
                }
            } catch (Exception $e) {
                $this->setError($e->getMessage());
                $this->set('post', [
                    'username' => $username,
                    'password' => $password,
                    'passwordVerify' => $passwordVerify,
                ]);
            }
        }
    }
}