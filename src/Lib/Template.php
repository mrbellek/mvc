<?php
declare(strict_types=1);

namespace MVC\Lib;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\Extension\DebugExtension;

class Template {

    protected $_controller;
    protected $_action;

    protected $twig;

    protected $variables = [];
    private $includes = ['css' => [], 'js' => []];

    public function __construct($controller, $action)
    {
        $this->_controller = $controller;
        $this->_action = $action;

        $twig_loader = new FilesystemLoader(DOCROOT . '/view');
        $this->twig = new Environment($twig_loader, [
            'cache' => (defined('ENV') && ENV == 'prod' ? DOCROOT . '/cache' : false),
            'debug' => !(defined('ENV') && ENV == 'prod'),
        ]);
        $this->twig->addExtension(new DebugExtension());
    }

    public function set($name, $value = false)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->variables[$key] = $value;
            }
        } else {
            $this->variables[$name] = $value;
        }
    }

    public function get($name)
    {
        if (!empty($this->variables[$name])) {
            return $this->variables[$name];
        } else {
            return false;
        }
    }

    /**
     * include external file, either css or javascript
     * @param string $type either 'css' or 'js'
     * @param string $content either a filename (relative path to /public), an url (js only) or inline content
     */
    public function includeExternal(string $type, string $content)
    {
        $this->includes[$type][] = $content;
    }

    //called in template
    private function getCss(): string
    {
        $return = [];
        foreach ($this->includes['css'] as $content) {
            if (is_file(DOCROOT . '/public' . $content)) {
                //content is a file, create <link> tag
                $return[] = '<link rel="stylesheet" href="/public' . $content . '" type="text/css" />';
            } else {
                //content is css rules, create <style> tag
                $return[] = '<style type="text/css">' . $content . '</style>';
            }
        }
        return implode("\n", $return);
    }

    //called in template
    private function getJs(): string
    {
        $return = [];
        foreach ($this->includes['js'] as $content) {
            if (is_file(DOCROOT . '/public' . $content)) {
                //content is a local file, create <script src="./.."> tag
                $return[] = '<script type="text/javascript" src="/public' . $content . '"></script>';
            } elseif (strpos($content, 'http') === 0) {
                //content is a remote file, create <script src=".."> tag
                $return[] = '<script type="text/javascript" src="' . $content . '"></script>';
            } else {
                //content is inline javascript, create <script>..</script> tag
                $return[] = '<script type="text/javascript">' . $content . '</script>';
            }
        }
        return implode("\n", $return);
    }

    //render the page
    public function fetch(): string
    {
        if (!is_file(DOCROOT . '/view/' . $this->_controller . '/' . $this->_action . '.twig')) {
            die('view not found: ' . DOCROOT . "/view/{$this->_controller}/{$this->_action}.twig");
            Controller::redirect(sprintf('/errorpage/error500/%s/%s',
                base64_encode(sprintf('/%s/%s', $this->_controller, $this->_action)),
                base64_encode($_SERVER['HTTP_REFERER'] ?? '')
            ));
        }

        //auto-include css and js for this controller
        if (is_file(DOCROOT . '/public/css/' . $this->_controller . '.css')) {
            $this->includeExternal('css', '/css/' . $this->_controller . '.css');
        }
        if (is_file(DOCROOT . '/public/js/' . $this->_controller . '.js')) {
            $this->includeExternal('js', '/js/' . $this->_controller . '.js');
        }

        $this->variables['included_css'] = $this->getCss();
        $this->variables['included_js'] = $this->getJs();
        return $this->twig->render($this->_controller . '/' . $this->_action . '.twig', $this->variables);
    }

    //display the page
    public function render(): void
    {
        echo $this->fetch();
    }
}