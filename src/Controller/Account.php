<?php
declare(strict_types=1);

namespace MVC\Controller;

use Exception;
use MVC\Lib\Controller;
use MVC\Helper\Session;
use MVC\Exception\InvalidPasswordException;

class Account extends Controller
{
    /**
     * @var \MVC\Model\Account
     */
    protected $model;

    public function index(): void
    {
        if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
            $this->post();
            return;
        }

        $user = Session::get('user');
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
            intval(Session::get('user')['id']),
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

    public function changepassword(): void
    {
        if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
            $this->changepasswordPost();
        }

        $this->set('no_old_password', Session::get('password-reset-active') === true);
    }

    private function changepasswordPost(): void
    {
        $passwordResetActive = Session::get('password-reset-active');
        Session::remove('password-reset-active');

        $userId = intval(Session::get('user')['id']);
        $oldPassword = filter_input(INPUT_POST, 'oldpassword');
        $newPassword = filter_input(INPUT_POST, 'newpassword');
        $newPasswordVerify = filter_input(INPUT_POST, 'newpassword-verify');

        try {
            //don't require the old password when resetting a password
            if (!$passwordResetActive) {
                $this->model->validatePassword($userId, $oldPassword);
            }
            $this->validateNewPassword($newPassword, $newPasswordVerify);

            $this->model->updatePassword($userId, $newPassword);
            $this->setInfo('Password changed succesfully.');

        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return;
        }
    }

    /**
     * @throws InvalidPasswordException
     */
    private function validateNewPassword(string $newPassword, string $newPasswordVerify): void
    {
        if ($newPassword !== $newPasswordVerify) {
            throw new InvalidPasswordException('Passwords do not match.');
        }

        if (strlen($newPassword) < 8) {
            throw new InvalidPasswordException('Password must be at least 8 characters.');
        }

        if (preg_match('/\d/', $newPassword) === 0) {
            throw new InvalidPasswordException('Password must contain at least one number.');
        }

        if (preg_match('/[a-z]/', $newPassword) === 0 || preg_match('/[A-Z]/', $newPassword) === 0) {
            throw new InvalidPasswordException('Password must contain uppercase and lowercase characters.');
        }
    }
}