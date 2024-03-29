<?php
declare(strict_types=1);

namespace MVC\Lib;

use MVC\Lib\Db;

class Model
{
    protected string $modelStr;
    protected ?Db $sql;

    public function __construct()
    {
        $this->modelStr = get_class($this);
        $this->sql = Db::getInstance();
    }

    public function __destruct()
    {
        $this->sql = null;
        unset($this->sql);
    }

    public function __sleep()
    {
        return ['modelStr'];
    }

    public function __wakeup()
    {
        $this->sql = Db::getInstance();
    }
}