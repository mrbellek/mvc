<?php
declare(strict_types=1);

namespace MVC\Model;

use MVC\Lib\Model;

class Account extends Model
{
    public function update(int $id, string $email, array $roles): bool
    {
        return $this->sql->fquery(
            'UPDATE user
            SET email = :email,
                roles = :roles
            WHERE id = :id
            LIMIT 1',
            [
                ':id' => $id,
                ':email' => $email,
                ':roles' => serialize($roles),
            ]
        );
    }

    public function getUserPasswordHash(int $id): ?string
    {
        return $this->sql->fetch_value(
            'SELECT passwordHash
            FROM user
            WHERE id = :id
            LIMIT 1',
            [':id' => $id]
        );
    }

    public function updatePassword(int $id, string $password)
    {
        return $this->sql->fquery(
            'UPDATE user
            SET passwordHash = :hash
            WHERE id = :id
            LIMIT 1',
            [
                ':id' => $id,
                ':hash' => hashPassword($password),
            ]
        );
    }
}