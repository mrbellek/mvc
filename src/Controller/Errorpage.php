<?php
declare(strict_types=1);

namespace MVC\Controller;

use MVC\Lib\Controller;

class Errorpage extends Controller {

    public function error404($url, $referrer = false)
    {
        $this->set('sUrl', base64_decode($url));
        if ($referrer) {
            $referrer = str_replace('http://' . filter_input(INPUT_SERVER, 'HTTP_HOST'), '', base64_decode($referrer));
            $this->set('sReferer', $referrer);
        }
    }

    public function error500($sUrl, $sReferer = false)
    {
        $this->set('sUrl', base64_decode($sUrl));
        if ($sReferer) {
            $sReferer = str_replace('http://' . filter_input(INPUT_SERVER, 'HTTP_HOST'), '', base64_decode($sReferer));
            $this->set('sReferer', $sReferer);
        }
    }

    public function test()
    {
        //test call
    }
}