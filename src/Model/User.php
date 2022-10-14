<?php
declare(strict_types=1);

namespace MVC\Model;

use MVC\Lib\Model;

class User extends Model
{
    public function getAll(): array
    {
        return $this->sql->fquery(
            'SELECT *
            FROM user
            ORDER BY username'
        );
    }

    public function get(int $id): array
    {
        return $this->sql->fetch_single(
            'SELECT *
            FROM user
            WHERE id = :id
            LIMIT 1',
            [':id' => $id]
        );
    }

    public function add(string $username, string $password, ?string $email, bool $enabled, array $roles): bool
    {
        $result = $this->sql->fquery(
            'INSERT INTO user
            SET username = :username,
                passwordhash = :hash,
                email = :email,
                enabled = :enabled,
                roles = :roles,
                registered_date = NOW()',
            [
                ':username' => $username,
                ':hash' => hashPassword($password),
                ':email' => $email,
                ':enabled' => $enabled,
                ':roles' => serialize($roles),
            ]
        );

        return intval($result) > 0;
    }

    public function edit(
        int $id,
        string $username,
        ?string $email,
        string $registrationDate,
        string $lastLogin,
        bool $enabled,
        array $roles
    ): bool {

        return $this->sql->fquery(
            'UPDATE user
            SET username = :username,
                email = :email,
                registered_date = :registered_date,
                last_login = :last_login,
                enabled = :enabled,
                roles = :roles
            WHERE id = :id
            LIMIT 1',
            [
                ':id' => $id,
                ':username' => $username,
                ':email' => $email,
                ':registered_date' => $registrationDate,
                ':last_login' => $lastLogin,
                ':enabled' => $enabled,
                ':roles' => serialize($roles),
            ]
        );
    }

    public function delete(int $id): bool
    {
        return $this->sql->fquery(
            'DELETE FROM user
            WHERE id = :id
            LIMIT 1',
            [':id' => $id]
        );
    }
}