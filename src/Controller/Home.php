<?php
namespace MVC\Controller;
use MVC\Lib\Controller;
use \Exception;

class Home extends Controller {

	public function index() {

		$this->set('text', 'It works!');
	}
}
