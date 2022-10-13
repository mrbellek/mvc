<?php
declare(strict_types=1);

namespace MVC\Lib;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\Extension\DebugExtension;

class Template {

    protected string $controller;
    protected string $action;

    protected Environment $twig;

    protected array $variables = [];
    private array $includes = ['css' => [], 'js' => []];

    public function __construct($controller, $action)
    {
        $this->controller = $controller;
        $this->action = $action;

        $twig_loader = new FilesystemLoader(DOCROOT . '/view');
        $this->twig = new Environment($twig_loader, [
            'cache' => (defined('ENV') && ENV == 'prod' ? DOCROOT . '/cache' : false),
            'debug' => !(defined('ENV') && ENV == 'prod'),
        ]);
        $this->twig->addExtension(new DebugExtension());
    }

    public function set($name, $value = false): void
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->variables[$key] = $value;
            }
        } else {
            $this->variables[$name] = $value;
        }
    }

    /**
     * @return mixed|null
     */
    public function get(string $name)
    {
        return $this->variables[$name] ?? null;
    }

    public function includeCss(string $content): void
    {
        $this->includeExternal('css', $content);
    }

    public function includeJs(string $content): void
    {
        $this->includeExternal('js', $content);
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
                $return[] = sprintf(
                    '<link rel="stylesheet" href="/public%s" type="text/css" />',
                    $content
                );
            } else {
                //content is css rules, create <style> tag
                $return[] = sprintf(
                    '<style>%s</style>',
                    $content
                );
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
                $return[] = sprintf(
                    '<script type="text/javascript" src="/public%s"></script>',
                    $content
                );
            } elseif (str_starts_with($content, 'http')) {
                //content is a remote file, create <script src=".."> tag
                $return[] = sprintf(
                    '<script type="text/javascript" src="%s"></script>',
                    $content
                );
            } else {
                //content is inline javascript, create <script>..</script> tag
                $return[] = sprintf(
                    '<script>%s</script>',
                    $content
                );
            }
        }
        return implode("\n", $return);
    }

    //render the page
    public function fetch(): string
    {
        if (!is_file(DOCROOT . '/view/' . $this->controller . '/' . $this->action . '.twig')) {
            die('view not found: ' . DOCROOT . "/view/{$this->controller}/{$this->action}.twig");
            Controller::redirect(sprintf('/errorpage/error500/%s/%s',
                base64_encode(sprintf('/%s/%s', $this->controller, $this->action)),
                base64_encode($_SERVER['HTTP_REFERER'] ?? '')
            ));
        }

        //auto-include css and js for this controller
        if (is_file(DOCROOT . '/public/css/' . $this->controller . '.css')) {
            $this->includeExternal('css', '/css/' . $this->controller . '.css');
        }
        if (is_file(DOCROOT . '/public/js/' . $this->controller . '.js')) {
            $this->includeExternal('js', '/js/' . $this->controller . '.js');
        }

        $this->variables['included_css'] = $this->getCss();
        $this->variables['included_js'] = $this->getJs();
        return $this->twig->render($this->controller . '/' . $this->action . '.twig', $this->variables);
    }

    //display the page
    public function render(): void
    {
        echo $this->fetch();
    }
}