<?php
declare(strict_types=1);

namespace MVC\Model;

use MVC\Lib\Model;
use Exception;

class Register extends Model {

    public function validate(string $username, string $password, string $passwordVerify) {

        if ($this->userExists($username) && !$_SESSION['user']['username'] == $username) {
            throw new Exception('This email address is already taken.');

        } elseif (!validEmailSyntax($username)) {
            throw new Exception('Email adress is invalid.');

        } elseif (empty($password) || empty($passwordVerify)) {
            throw new Exception('Password is empty.');

        } elseif ($password !== $passwordVerify) {
            throw new Exception('Passwords aren\'t the same.');
        }

        return true;
    }

    private function userExists($sUsername) {

        return $this->sql->fetch_single('
            SELECT *
            FROM user
            WHERE username = :username
            OR email = :username
            LIMIT 1',
            [':username' => $sUsername]
        );
    }

    public function registerAndLogin(string $username, string $password, string $admin) {

        $userId = $this->register($username, $password, !empty($admin));

        if ($userId) {
            return $this->login($username, $password);
        }

        return false;
    }

    private function register(string $username, string $password, $bAdmin = false)
    {
        $sUsername = $username;
        $sPassword = $password;

        $sHash = password_hash($sPassword, PASSWORD_BCRYPT, ['cost' => 12]);

        $roles = ['ROLE_USER'];
        if ($bAdmin) {
            $roles[] = 'ROLE_SUPER_ADMIN';
        }

        return $this->sql->fquery('
            INSERT INTO user
                (username, email, enabled, salt, password, locked, expired, roles, credentials_expired)
            VALUES
                (:username, :email, 1, "", :password, 0, 0, :roles, 0)',
            [
                ':username' => $sUsername,
                ':email' => $sUsername,
                ':password' => $sHash,
                ':roles' => serialize($roles),
            ]
        );
    }

    private function login(string $username, string $password) {

        return (new \MVC\Model\Login())->login($username, $password);
    }
}