<?php
namespace MVC\Controller;
use MVC\Lib\Controller;

class Errorpage extends Controller {

	public function error404($sUrl, $sReferer = FALSE) {

		$this->set('sUrl', base64_decode($sUrl));
		if ($sReferer) {
			$sReferer = str_replace('http://' . $_SERVER['HTTP_HOST'], '', base64_decode($sReferer));
			$this->set('sReferer', $sReferer);
		}
	}

	public function error500($sUrl, $sReferer = FALSE) {

		$this->set('sUrl', base64_decode($sUrl));
		if ($sReferer) {
			$sReferer = str_replace('http://' . $_SERVER['HTTP_HOST'], '', base64_decode($sReferer));
			$this->set('sReferer', $sReferer);
		}
	}

	public function test() {}
}
