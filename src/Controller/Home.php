<?php
declare(strict_types=1);

namespace MVC\Controller;

use MVC\Lib\Controller;

class Home extends Controller {

    public function index()
    {
        $this->set('text', 'It works!');
    }
}