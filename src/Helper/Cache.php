<?php
namespace MVC\Helper;
use \Memcached;
use \Exception;

class Cache {

	//NB: the 'CLEARCACHE' and 'NOCACHE' constants are defined in index.php
	//one forcefully clears the entire cache, the other just ignores the cache for that request

	static $oMemcached = null;

	//default cache time: 30 minutes
	private $iDefaultExpiration = 1800;

	private function __construct() {}

	public static function getInstance() {

		if (defined('ENV') && ENV == 'prod') {
			if (!self::$oMemcached) {

				try {
					self::$oMemcached = new Memcached;
					self::$oMemcached->addServer('127.0.0.1', 11211);

				} catch(Exception $i) {

					throw new Exception('Kon niet verbinden met memcached: ' . $e->getMessage());
				}
			}

			return self::$oMemcached;

		} else {

			return new MemcacheDummy;
		}
	}

	//store item in cache (objects are automatically serialized
	public function set($sName, $mValue, $iExpires = FALSE) {

		//don't use cache when ?nocache
		if (NOCACHE) {
			return TRUE;
		}

		if (!$iExpires) {
			$iExpires = $this->iDefaultExpiration;
		}

		//store item
		$this->oMemcached->set($sName, $mValue, $iExpires);

		return TRUE;
	}

	//get item from cache if possible
	public function get($sName) {

		//don't use cache when ?nocache
		if (NOCACHE) {
			return FALSE;
		}

		return $this->oMemcached->get($sName);
	}

	//delete item from cache and index
	public function delete($sKey) {

		if (preg_match('/\/.+\/.?/', $sKey)) {

			$aKeys = $this->oMemcached->getAllKeys();
			if ($aKeys && is_array($aKeys)) {

				$aDelete = array();
				foreach ($aKeys as $sName) {
					if (preg_match($sKey, $sName)) {
						$aDelete[] = $sName;
					}
				}
				$this->oMemcached->deleteMulti($aDelete);
			}

		} else {
			$this->oMemcached->delete($sKey);
		}

		return TRUE;
	}

	public function clear() {

		$aKeys = $this->oMemcached->getAllKeys();
		$this->oMemcached->deleteMulti($aKeys);

		return TRUE;
	}
}

//dummy class to use on dev, both because memcached only runs
//on unix server and we don't want caching on dev
class MemcacheDummy {

	public function get() { return array(); }
	public function set() {}
	public function delete() {}
	public function clear() {}
}
