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
}