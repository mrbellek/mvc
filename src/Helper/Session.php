<?php
declare(strict_types=1);

namespace MVC\Helper;

use InvalidArgumentException;

class Session
{
    private function __construct() {
        //static methods only
    }

    public static function init(?string $httpHost): void
    {
        ini_set('session.gc_maxlifetime', strval(7 * 24 * 3600));
        ini_set('session.cookie_lifetime', strval(7 * 24 * 3600));
        session_set_cookie_params(7 * 24 * 3600, '/', '.' . $httpHost);
        session_start();
    }

    public static function set(string $name, mixed $value): void
    {
        $_SESSION[$name] = $value;
    }

    public static function getAll(): mixed
    {
        return $_SESSION;
    }

    public static function get(string $name): mixed
    {
        return $_SESSION[$name] ?? null;
    }

    public static function remove(string $name): void
    {
        if (isset($_SESSION[$name])) {
            unset($_SESSION[$name]);
        }
    }

    public static function login(array $data): bool
    {
        if (empty($data['id']) || empty($data['username'])) {
            throw new InvalidArgumentException('Login data is invalid, cannot login user.');
        }

        self::set('user', [
            'id' => $data['id'],
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'is_admin' => $data['isAdmin'] ?? false,
        ]);

        return true;
    }

    public static function logout(): void
    {
        self::remove('user');
    }
}