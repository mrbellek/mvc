<?php
declare(strict_types=1);

namespace MVC\Model;

use MVC\Lib\Model;

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

            $roles = @unserialize($user['roles']);
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'roles' => [
                    'is_admin' => in_array('ROLE_SUPER_ADMIN', $roles),
                ],
            ];

            return true;
        }

        return false;
    }
}