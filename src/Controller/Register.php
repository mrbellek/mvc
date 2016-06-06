<?php
namespace MVC\Controller;
use MVC\Lib\Controller;
use \Exception;

class Register extends Controller {

    public function index() {

		if ($_POST) {

			try {
				if ($this->model->Validate($_POST)) {
					if ($this->model->RegisterAndLogin($_POST)) {

						$this->setDelayedInfo('Registration successful.');
						$this->redirect('/');
					} else {
						$this->setError('Registration failed.');
						$this->set('post', $_POST);
					}
				}
			} catch (Exception $e) {
				$this->setError($e->getMessage());
				$this->set('post', $_POST);
			}
		}
	}
}
