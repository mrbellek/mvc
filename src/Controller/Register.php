<?php
declare(strict_types=1);

namespace MVC\Controller;

use MVC\Lib\Controller;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Register extends Controller {

    public function index() {

        if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
            try {
                $username = filter_input(INPUT_POST, 'username');
                $email = filter_input(INPUT_POST, 'email');
                $password = filter_input(INPUT_POST, 'password');
                $passwordVerify = filter_input(INPUT_POST, 'passwordVerify');
                $admin = filter_input(INPUT_POST, 'admin');

                if ($this->model->validate($username, $email, $password, $passwordVerify)) {
                    if ($this->model->registerAndLogin($username, $email, $password, $admin)) {

                        $mailer = new PHPMailer(true);
                        $mailer->Subject = 'Welcome to MVC framework!';
                        $mailer->Body = 'You just registered for MVC framework. Hooray!';
                        $mailer->addAddress($email);
                        $mailer->From = 'admin@' . filter_input(INPUT_SERVER, 'SERVER_NAME');
                        $mailer->send();

                        $this->setDelayedInfo('Registration successful.');
                        $this->redirect('/');
                    } else {
                        $this->setError('Registration failed.');
                        $this->set('post', [
                            'username' => $username,
                            'email' => $email,
                            'password' => $password,
                            'passwordVerify' => $passwordVerify,
                        ]);
                    }
                }
            } catch (Exception $e) {
                $this->setError($e->getMessage());
                $this->set('post', [
                    'username' => $username,
                    'email' => $email,
                    'password' => $password,
                    'passwordVerify' => $passwordVerify,
                ]);
            }
        }
    }
}