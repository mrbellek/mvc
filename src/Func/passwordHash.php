<?php
declare(strict_types=1);

function hashPassword($password): string
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}