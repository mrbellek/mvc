<?php
declare(strict_types=1);

namespace MVC\Controller;

use MVC\Lib\Controller;

class Account extends Controller
{
    public function index(): void
    {
        if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
            $this->post();
            return;
        }

        $user = $_SESSION['user'];
        $this->set('user', $user);
    }

    private function post(): void
    {
        $isAdmin = filter_input(INPUT_POST, 'is_admin');
        if (!empty($isAdmin)) {
            $roles = ['ROLE_USER', 'ROLE_ADMIN'];
        } else {
            $roles = ['ROLE_USER'];
        }
        $ret = $this->model->update(
            intval($_SESSION['user']['id']),
            filter_input(INPUT_POST, 'email'),
            $roles,
        );

        if ($ret) {
            $this->setDelayedInfo('User data saved.');
        } else {
            $this->setDelayedError('Failed to save user data.');
        }
        $this->redirect('/home');
    }
}