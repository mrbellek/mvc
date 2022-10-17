<?php
declare(strict_types=1);

namespace MVC\Controller;

use MVC\Lib\Controller;
use MVC\Helper\Session;
use MVC\Helper\Mailer;

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

    public function forgotpassword()
    {
        if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
            $emailOrUsername = filter_input(INPUT_POST, 'username');
            if (filter_var($emailOrUsername, FILTER_VALIDATE_EMAIL)
                && $user = $this->model->getUserByEmail($emailOrUsername)
            ) {
                //valid email
                $email = $user['email'];
            } elseif ($user = $this->model->getUserByUsername($emailOrUsername)) {
                //valid username, got email
                $email = $user['email'];
            } else {
                $this->setError(sprintf('No user was found with the email or username "%s"', $emailOrUsername));
                return;
            }

            //user not found, or user found but has no email
            if (!$email) {
                $this->setError(sprintf('No user was found with the email or username "%s"', $emailOrUsername));
                return;
            }

            //@TODO: self-expiring links?
            $link = 'http://' . filter_input(INPUT_SERVER, 'SERVER_NAME') . '/login/password-reset/'
                . sha1($email . $user['id'] . $user['username'])
            ;

            $mailer = new Mailer();
            $mailer->send(
                $emailOrUsername,
                'Password reset link',
                'Here\'s your password reset link: ' . $link
            );
        }
    }

    public function passwordreset(string $link)
    {
        //validate link
        $user = $this->model->validateResetLink($link);

        if ($user) {
            $roles = unserialize($user['roles']);
            Session::set('user', [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'is_admin' => in_array('ROLE_ADMIN', $roles),
            ]);
            $this->setDelayedInfo('Password reset link is validate. You can now change your password.');

            //@TODO: update page so old password isn't required
            $this->redirect('/account/change-password');
        } else {
            $this->set('validateOk', false);
        }
    }

    public function logout()
    {
        Session::remove('user');
        $this->redirect('/login');
    }
}