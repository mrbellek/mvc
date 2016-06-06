<?php
namespace MVC\Lib;
use MVC\Lib\Db;

class Model {

	protected $_model;

	public function __construct() {
		$this->_model = get_class($this);
		$this->SQL = Db::getInstance();
	}

	public function __destruct() {
		$this->SQL = null;
		unset($this->SQL);
	}

	public function __sleep() {
		return array('_model');
	}

	public function __wakeup() {
		$this->SQL = Db::getInstance();
	}
}
