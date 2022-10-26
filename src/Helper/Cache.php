<?php
declare(strict_types=1);

namespace MVC\Helper;

use Memcached;
use MVC\Exception\MemcacheException;

class Cache
{
    //NB: the 'CLEARCACHE' and 'NOCACHE' constants are defined in index.php
    //one forcefully clears the entire cache, the other just ignores the cache for that request

    private static $memcached = null;

    //default cache time: 30 minutes
    private int $defaultExpiration = 1800;

    private function __construct() {}

    /**
     * @return Memcached|MemcacheDummy|null
     * @throws MemcacheException
     */
    public static function getInstance()
    {
        if (defined('ENV') && ENV == 'prod') {
            if (!self::$memcached) {

                try {
                    self::$memcached = new Memcached();
                    self::$memcached->addServer('127.0.0.1', 11211);

                } catch(Exception $e) {

                    throw new MemcacheException('Kon niet verbinden met memcached: ' . $e->getMessage());
                }
            }

            return self::$memcached;

        } else {

            return new MemcacheDummy();
        }
    }

    //store item in cache (objects are automatically serialized
    public function set($sName, $mValue, $iExpires = false): bool
    {
        //don't use cache when ?nocache
        if (NOCACHE) {
            return true;
        }

        if (!$iExpires) {
            $iExpires = $this->defaultExpiration;
        }

        //store item
        self::$memcached->set($sName, $mValue, $iExpires);

        return true;
    }

    //get item from cache if possible
    public function get($sName)
    {
        //don't use cache when ?nocache
        if (NOCACHE) {
            return false;
        }

        return self::$memcached->get($sName);
    }

    //delete item from cache and index
    public function delete($sKey)
    {
        if (preg_match('/\/.+\/.?/', $sKey)) {

            $aKeys = self::$memcached->getAllKeys();
            if ($aKeys && is_array($aKeys)) {

                $aDelete = array();
                foreach ($aKeys as $sName) {
                    if (preg_match($sKey, $sName)) {
                        $aDelete[] = $sName;
                    }
                }
                self::$memcached->deleteMulti($aDelete);
            }

        } else {
            self::$memcached->delete($sKey);
        }

        return true;
    }

    public function clear()
    {
        $aKeys = self::$memcached->getAllKeys();
        self::$memcached->deleteMulti($aKeys);

        return true;
    }
}

//dummy class to use on dev, both because memcached only runs
//on unix server and we don't want caching on dev
class MemcacheDummy
{
    public function get() { return []; }
    public function set() {}
    public function delete() {}
    public function clear() {}
}