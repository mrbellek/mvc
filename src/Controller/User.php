<?php
declare(strict_types=1);

namespace MVC\Controller;

use MVC\Lib\Controller;

class User extends Controller
{
    public function index(): void
    {
        $users = $this->model->getAll();
        if (count($users) === 0) {
            $this->setInfo('No users found.');
        }

        foreach ($users as $key => $user) {
            $roles = unserialize($user['roles']);
            if (in_array('ROLE_ADMIN', $roles)) {
                $users[$key]['is_admin'] = true;
            }
        }
        $this->set('users', $users);
    }

    public function edit($id): void
    {
        if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
            $this->editPost($id);
            return;
        }

        $this->set('title', 'Edit user');
        $user = $this->model->get(intval($id));

        //convert roles array into just the relevant 'admin' bit
        $user['is_admin'] = in_array('ROLE_ADMIN', unserialize($user['roles']));
        unset($user['roles']);

        if ($user !== null) {
            $this->set('user', $user);
        } else {
            $this->setError(sprintf('User with id %s was not found.', $id));
        }
    }

    public function add(): void
    {
        if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
            $this->addPost();
            return;
        }

        $this->set('title', 'Add user');
        $this->set('addUser', true);
    }

    private function editPost($id): void
    {
        $username = filter_input(INPUT_POST, 'username');
        $email = filter_input(INPUT_POST, 'email');
        $registrationDate = filter_input(INPUT_POST, 'registered_date');
        $lastLogin = filter_input(INPUT_POST, 'last_login');
        $enabled = !empty(filter_input(INPUT_POST, 'enabled'));

        $roles = ['ROLE_USER'];
        //only consider 'user is admin' checkbox when current user is actually an admin
        if (!empty($_SESSION['user']['is_admin']) && !empty(filter_input(INPUT_POST, 'is_admin'))) {
            $roles = ['ROLE_ADMIN'];
        }


        if ($this->model->edit(intval($id), $username, $email, $registrationDate, $lastLogin, $enabled, $roles) === true) {
            $this->setDelayedInfo('User edited.');
            $this->redirect('/user');
        } else {
            $this->setDelayedError('Failed to edit user.');
            $this->redirect(sprintf('/user/edit/%d', $id));
        }
    }

    private function addPost(): void
    {
        $username = filter_input(INPUT_POST, 'username');
        $password = filter_input(INPUT_POST, 'password');
        $email = filter_input(INPUT_POST, 'email');
        $enabled = !empty(filter_input(INPUT_POST, 'enabled'));

        $roles = ['ROLE_USER'];
        if (!empty($_SESSION['user']['is_admin']) && !empty(filter_input(INPUT_POST, 'is_admin'))) {
            $roles = ['ROLE_ADMIN'];
        }

        if ($this->model->add($username, $password, $email, $enabled, $roles) === true) {
            $this->setDelayedInfo('User added.');
        } else {
            $this->setDelayedError('Failed to add user.');
        }
        $this->redirect('/user');
    }

    public function delete($id)
    {
        if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
            $ret = $this->model->delete(intval($id));
            if ($ret) {
                $this->setDelayedInfo('User deleted.');
            } else {
                $this->setDelayedError('Failed to delete user.');
            }
            $this->redirect('/user');
        }

        $user = $this->model->get(intval($id));
        if ($user) {
            $this->set('username', $user['username']);
        } else {
            $this->setDelayedError('User not found.');
            $this->redirect('/user');
        }
    }
}