<?php
declare(strict_types=1);

namespace MVC\Model;

use MVC\Lib\Model;
use MVC\Helper\Session;

class Login extends Model {

    public function getUserByUsername(string $username): ?array
    {
        $user = $this->sql->fetch_single(
            'SELECT *
            FROM user
            WHERE username = :username
            LIMIT 1',
            [':username' => $username]
        );

        return $user !== false ? $user : null;
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

    public function updateUserLastLogin(int $id): void
    {
        $this->sql->fquery(
            'UPDATE user
            SET last_login = NOW()
            WHERE id = :id
            LIMIT 1',
            [':id' => $id]
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