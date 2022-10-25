<?php
declare(strict_types=1);

namespace MVC\Controller;

use Exception;
use MVC\Lib\Controller;
use MVC\Helper\Session;
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

                if ($this->validate($username, $email, $password, $passwordVerify)) {
                    if ($this->registerAndLogin($username, $email, $password, $admin)) {

                        $this->setDelayedInfo('Registration successful.');

                        $mailer = new PHPMailer(true);
                        $mailer->Subject = 'Welcome to MVC framework!';
                        $mailer->Body = 'You just registered for MVC framework. Hooray!';
                        $mailer->addAddress($email);
                        $mailer->From = 'admin@' . filter_input(INPUT_SERVER, 'SERVER_NAME');
                        $mailer->send();

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

    public function validate(string $username, string $email, string $password, string $passwordVerify): bool
    {
        if ($this->model->userExists($username, $email)) {
            throw new Exception('This username or email address is already taken.');

        } elseif (!$this->validateEmailSyntax($email)) {
            throw new Exception('Email adress is invalid.');

        } elseif (empty($password) || empty($passwordVerify)) {
            throw new Exception('Password is empty.');

        } elseif ($password !== $passwordVerify) {
            throw new Exception('Passwords aren\'t the same.');
        }

        return true;
    }

    public function registerAndLogin(string $username, string $email, string $password, ?string $admin): bool
    {
        $isAdmin = !empty($admin);
        $userId = $this->model->register($username, $email, $password, $isAdmin);

        if ($userId) {
            return Session::login([
                'id' => $userId,
                'username' => $username,
                'email' => $email,
                'is_admin' => $isAdmin,
            ]);
        }

        return false;
    }

    private function validateEmailSyntax($email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}