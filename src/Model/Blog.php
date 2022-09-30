<?php
declare(strict_types=1);

namespace MVC\Model;

use MVC\Lib\Model;

class Blog extends Model
{
    public function getAll(): array
    {
        return $this->sql->fquery(
            'SELECT * FROM blog ORDER BY created_on'
        );
    }

    public function get(int $id): ?array
    {
        return $this->sql->fetch_single(
            'SELECT * FROM blog WHERE id = :id LIMIT 1',
            [':id' => $id]
        );
    }

    public function edit(int $id, ?string $title, ?string $body): bool
    {
        return $this->sql->fquery(
            'UPDATE blog
            SET last_modified = NOW(),
                title = :title,
                body = :body
            WHERE id = :id
            LIMIT 1',
            [
                ':id' => $id,
                ':title' => $title,
                ':body' => $body,
            ]
        );
    }

    public function add(?string $title, ?string $body): bool
    {
        $ret = $this->sql->fquery(
            'INSERT INTO blog
            SET created_on = NOW(),
                title = :title,
                body = :body',
            [
                ':title' => $title,
                ':body' => $body,
            ]
        );

        return $ret !== false;
    }

    public function delete(int $id): bool
    {
        return $this->sql->fquery(
            'DELETE FROM blog
            WHERE id = :id
            LIMIT 1',
            [':id' => $id]
        );
    }
}