<?php
declare(strict_types=1);

namespace MVC\Model;

use MVC\Lib\Model;
use MVC\Helper\Session;

class Login extends Model {

    public function login(string $username, string $password) {

        if (empty($username) || empty($password)) {
            return false;
        }

        $user = $this->sql->fetch_single('
            SELECT *
            FROM user
            WHERE username = :username
            AND enabled = 1
            LIMIT 1',
            [':username' => $username]
        );

        if ($user && password_verify($password, $user['passwordhash'])) {

            $this->sql->fquery('
                UPDATE user
                SET last_login = NOW()
                WHERE id = :id
                LIMIT 1',
                [':id' => $user['id']]
            );

            $roles = unserialize($user['roles']);
            Session::set('user', [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'is_admin' => in_array('ROLE_ADMIN', $roles),
            ]);

            return true;
        }

        return false;
    }

    public function getUserByUsername(string $username): ?array
    {
        return $this->sql->fetch_single(
            'SELECT *
            FROM user
            WHERE username = :username
            LIMIT 1',
            [':username' => $username]
        );
    }

    public function getUserByEmail(string $email): ?array
    {
        return $this->sql->fetch_single(
            'SELECT *
            FROM user
            WHERE email = :email
            LIMIT 1',
            [':email' => $email]
        );
    }

    public function validateResetLink(string $checksum): ?array
    {
        $result = $this->sql->fetch_single(
            'SELECT *
            FROM user
            WHERE SHA1(CONCAT(email, id, username)) = :checksum
            LIMIT 1',
            [':checksum' => $checksum]
        );

        return $result !== false ? $result : null;
    }
}