<?php
namespace MVC\Lib;
use \PDO;
use \Exception;

class Db {

    private static $instance;
	private $iQueryCount = 0;
	private $lQueryTimer = 0;

    private function __construct() {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_DATABASE;
		try {
			$this->PDO = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
			$this->PDO->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		} catch(Exception $e) {
			if (defined('ENV') && ENV == 'prod') {
				throw new Exception(sprintf('Database connection to %s failed: %s', DB_HOST, $e->getMessage()));
			} else {
				die(sprintf('Database connection to %s failed. Check config file and database server?', DB_HOST));
			}
		}
    }

	//singleton
    public static function getInstance() {
        if(!self::$instance) {
            self::$instance = new Db();
        }
        return self::$instance;
    }

	//function that actually does the query and returns a result
    private function query() {
		$lStart = microtime(TRUE);

		//arguments should be query (with placeholders) followed by an array of key-value pairs
        $args = func_get_args();
        $args = array_shift($args);
		$query = array_shift($args);
		if ($args) {
			$params = array_shift($args);
		} else {
			$params = array();
		}

		$sth = $this->PDO->prepare($query);
		if (!$sth) {
			//prepare failed
			if (defined('ENV') && ENV == 'prod') {
				throw new Exception(sprintf('%s - Prepare failed for query: %s', implode(':', $sth->errorInfo()), $query));
			} else {
				die(var_dump(implode(':', $this->PDO->errorInfo()), $query, $params));
			}
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
			if (defined('ENV') && ENV == 'prod') {
				throw new Exception(sprintf('%s - Execute failed for query: %s', implode(':', $sth->errorInfo()), $query));
			} else {
				die(var_dump(implode(':', $sth->errorInfo()), $query, $params));
			}
        }
		$this->lQueryTimer+= microtime(TRUE) - $lStart;
		$this->iQueryCount++;

        $query = preg_replace('/^\s+/', '', $query);
        if(strpos(strtolower($query), 'select') === 0) {
			//return results for SELECT
            return $sth;
        } elseif(strpos(strtolower($query), 'insert') === 0) {
			//return insert id for INSERT
            $id = $this->PDO->lastInsertId();
            return ($id ? $id : TRUE);
        } else {
			//return TRUE for DELETE, UPDATE
			//NB: don't return affected rows for UPDATE since 0 affected will be interpreted as query failed)
            return TRUE;
        }
    }

	//regular query that returns multiple rows
    public function fquery() {
        $sth = $this->query(func_get_args());
        if(is_object($sth)) {
            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return $sth;
        }
    }

	//query that returns single row
    public function fetch_single() {
        $sth = $this->query(func_get_args());
        if(is_object($sth)) {
            return $sth->fetch(PDO::FETCH_ASSOC);
        } else {
            return $sth;
        }
    }

	//query that returns single value from single row
    public function fetch_value() {
        $sth = $this->query(func_get_args());
        if(is_object($sth)) {
            $ret = $sth->fetch(PDO::FETCH_NUM);
            if($ret) {
                return $ret[0];
            } else {
                return FALSE;
            }
        } else {
            return $sth;
        }
    }

	public function fetch_raw() {
		$sth = $this->query(func_get_args());
		return $sth;
	}

	public function foundRows() {
		return $this->fetch_value('SELECT FOUND_ROWS()');
	}

	public function lastInsertId() {
		return $this->PDO->lastInsertId();
	}

	public function getQueryStats() {
		return array('count' => $this->iQueryCount, 'time' => number_format($this->lQueryTimer, 4));
	}
}
?>
