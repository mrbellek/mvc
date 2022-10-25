<?php
declare(strict_types=1);

namespace MVC\Model;

use Exception;
use MVC\Lib\Model;

class Register extends Model {

    public function userExists(string $username, string $email): bool
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

    public function register(string $username, string $email, string $password, bool $isAdmin = false)
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