<?php

namespace core;

use Leafo\ScssPhp\Compiler;
use Leafo\ScssPhp\Server;

class App
{
    protected $session;
    protected $path;
    protected $project;
    protected $extension;
    protected $data = array();

    public function __construct($path)
    {
        if (isset($_GET['logout']))
            Session::reset();

        $this->path = $path;

        $this->data['project_path'] = Session::getInstance()->get('project');

        if (
            !Session::get('login') ||
            Session::getInstance()->get('project') == 'configure'
        )
        {
            $configure = new Configuration();
            $configure->run();
            $this->path = $configure->get_path();
            $this->data += $configure->get_data();
        }

        $this->setParts();
        $this->setExtension();
        $this->setProject();
    }

    public function run()
    {
        switch($this->extension)
        {
            case 'scss':
            case 'sass':
                modules\Module::load('scssphp', 'scss.inc.php');

                $path = $this->pathToProject();
                $path .= DIRECTORY_SEPARATOR . 'scss';

                $scss = new Compiler();
                $scss->setImportPaths($path);

                header('Content-type: text/css');

                echo $scss->compile('@import "common.scss"');

                break;
            case 'html':
            case 'htm' :
                $path = $this->getPath();

                if (!file_exists($path))
                {
                    echo "File {$path} doesn't exists";
                    return;
                }

                $content = $this->processHTML($path);
                echo $content;
                break;
            default:
                $path = $this->getPath();

                if (!file_exists($path))
                {
                    echo "File {$path} doesn't exists";
                    return;
                }

                $this->setHeaders($path);

                $this->extension === 'php' ? include $path : readfile($path);
                break;
        }
    }

    private function setParts()
    {
        $path = ($this->path === '/' ? 'index.html' : trim($this->path, '/'));
        $this->parts = explode('/', $path);
    }

    private function getPath($folder = false)
    {
        $path_to_project = $this->pathToProject();

        $path = $path_to_project . implode(DIRECTORY_SEPARATOR, $this->parts);

        return $path;
    }

    private function pathToProject()
    {
        $base_path = Config::get('base_path', $this->project);
        $project_folder = Config::get('project_folder', $this->project);

        return $base_path . $project_folder . DIRECTORY_SEPARATOR . Session::get('project') . DIRECTORY_SEPARATOR;
    }

    protected function setExtension()
    {
        $this->extension = 'html';
        if (preg_match('/\.([\w]+)$/', $this->path, $matches))
        {
            $this->extension = $matches[1];
        }
    }

    protected function setProject()
    {
        if (is_null(Session::get('project')))
            Session::set('project', 'configure');

        $this->project = Session::get('project');
    }

    protected function setHeaders($path)
    {
        switch ($this->extension)
        {
            case 'js':
                $mime = "application/javascript";
                break;
            case 'css':
                $mime = "text/css";
            default:
                $mime = mime_content_type($path);
                break;
        }

        if ($mime)
            header("Content-Type: {$mime}");
    }

    protected function processHTML($path)
    {
        extract($this->data);

        ob_start();
        include $path;
        $content = ob_get_clean();

        $cwp = DIRECTORY_SEPARATOR . Config::get('project_folder', $this->project) . DIRECTORY_SEPARATOR . $this->project . DIRECTORY_SEPARATOR;

        $content = str_replace('{{ cwp }}', $cwp, $content);

        return $content;
    }

    public function __get($name)
    {
        return Config::get($name, $this->parts[0]);
    }
}