<?php
declare(strict_types=1);

namespace MVC\Lib;

use PDO;
use Exception;

class Db
{
    private static ?Db $instance = null;
    private int $queryCount = 0;
    private float $queryTimer = 0;
    private PDO $pdo;

    private function __construct()
    {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_DATABASE;
        try {
            $this->pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(Exception $e) {
            if (defined('ENV') && ENV == 'prod') {
                throw new Exception(sprintf('Database connection to %s failed: %s', DB_HOST, $e->getMessage()));
            } else {
                printf(
                    'Database connection to %s failed. Check config file and database server?<br>Error message: %s<hr>',
                    DB_HOST,
                    $e->getMessage()
                );
                exit();
            }
        }
    }

    //singleton
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Db();
        }
        return self::$instance;
    }

    //function that actually does the query and returns a result
    private function query()
    {
        $lStart = microtime(true);

        //arguments should be query (with placeholders) followed by an array of key-value pairs
        $args = func_get_args();
        $args = array_shift($args);
        $query = array_shift($args);
        $params = $args ? array_shift($args) : [];

        $sth = $this->pdo->prepare($query);
        if (!$sth) {
            //prepare failed
            $this->handlePrepareError($sth, $query, $params);
        }

        //explicitly bind parameters as int/string so IN() doens't get quoted
        foreach ($params as $key => $value) {
            if (is_numeric($value)) {
                $sth->bindValue($key, $value, PDO::PARAM_INT);
            } elseif (is_bool($value)) {
                $sth->bindValue($key, $value, PDO::PARAM_BOOL);
            } elseif (is_null($value)) {
                $sth->bindValue($key, $value, PDO::PARAM_NULL);
            } else {
                $sth->bindValue($key, $value, PDO::PARAM_STR);
            }
        }

        //query invalid
        if(!$sth->execute()) {
            $this->handleExecuteError($sth, $query, $params);
        }
        $this->queryTimer += microtime(true) - $lStart;
        $this->queryCount++;

        $query = preg_replace('/^\s+/', '', $query);
        if(str_starts_with(strtolower($query), 'select')) {
            //return results for SELECT
            return $sth;

        } elseif(str_starts_with(strtolower($query), 'insert')) {
            //return insert id for INSERT
            $id = $this->pdo->lastInsertId();
            return ($id ?: true);

        } else {
            //return TRUE for DELETE, UPDATE
            //NB: don't return affected rows for UPDATE since 0 affected will be interpreted as query failed)
            return true;
        }
    }

    //regular query that can return multiple rows
    public function fquery(): mixed
    {
        $sth = $this->query(func_get_args());
        if (is_object($sth)) {
            //select query
            return $sth->fetchAll();
        } else {
            //non-select query that can return bool/int
            return $sth;
        }
    }

    //query that returns single row
    public function fetch_single(): mixed
    {
        $sth = $this->query(func_get_args());
        if(is_object($sth)) {
            return $sth->fetch();
        } else {
            return $sth;
        }
    }

    //query that returns single value from single row
    public function fetch_value(): mixed
    {
        $sth = $this->query(func_get_args());
        if (is_object($sth)) {
            $ret = $sth->fetch();
            if ($ret) {
                return reset($ret);
            } else {
                return false;
            }
        } else {
            return $sth;
        }
    }

    public function fetch_raw(): mixed
    {
        return $this->query(func_get_args());
    }

    public function foundRows(): ?int
    {
        return $this->fetch_value('SELECT FOUND_ROWS()');
    }

    public function lastInsertId(): ?string
    {
        return $this->pdo->lastInsertId();
    }

    public function getQueryStats(): array
    {
        return ['count' => $this->queryCount, 'time' => number_format($this->queryTimer, 4)];
    }

    /**
     * @throws Exception
     */
    private function handlePrepareError($sth, $query, $params): void
    {
        if (defined('ENV') && ENV == 'prod') {
            throw new Exception(sprintf('%s - Prepare failed for query: %s', implode(':', $sth->errorInfo()), $query));
        } else {
            die(var_dump(implode(':', $this->pdo->errorInfo()), $query, $params));
        }
    }

    /**
     * @throws Exception
     */
    private function handleExecuteError($sth, $query, $params): void
    {
        if (defined('ENV') && ENV == 'prod') {
            throw new Exception(sprintf('%s - Execute failed for query: %s', implode(':', $sth->errorInfo()), $query));
        } else {
            die(var_dump(implode(':', $sth->errorInfo()), $query, $params));
        }
    }
}