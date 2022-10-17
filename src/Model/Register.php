<?php
declare(strict_types=1);

namespace MVC\Model;

use Exception;
use MVC\Lib\Model;
use MVC\Helper\Session;

class Register extends Model {

    public function validate(string $username, string $email, string $password, string $passwordVerify) {

        $userSession = Session::get('user');
        if ($this->userExists($username, $email) && $userSession && $userSession['username'] !== $username) {
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

    private function validateEmailSyntax($email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function userExists(string $username, string $email): bool
    {
        $result = $this->sql->fetch_single('
            SELECT *
            FROM user
            WHERE username = :username
            OR email = :email
            LIMIT 1',
            [
                ':username' => $username,
                ':email' => $email
            ]
        );

        return is_countable($result) && count($result) > 0;
    }

    public function registerAndLogin(string $username, string $email, string $password, ?string $admin): bool
    {
        $userId = $this->register($username, $email, $password, !empty($admin));

        if ($userId) {
            return $this->login($username, $password);
        }

        return false;
    }

    private function register(string $username, string $email, string $password, bool $isAdmin = false)
    {
        $hash = hashPassword($password);

        $roles = ['ROLE_USER'];
        if ($isAdmin) {
            $roles[] = 'ROLE_SUPER_ADMIN';
        }

        return $this->sql->fquery('
            INSERT INTO user (username, email, passwordHash, enabled, roles, registered_date)
            VALUES (:username, :email, :password, 1, :roles, NOW())',
            [
                ':username' => $username,
                ':email' => $email,
                ':password' => $hash,
                ':roles' => serialize($roles),
            ]
        );
    }

    private function login(string $username, string $password) {

        return (new \MVC\Model\Login())->login($username, $password);
    }
}