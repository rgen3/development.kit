<?php

namespace core;

class Creator
{

    private $project_name;
    private $project_path;
    private $base_path;
    private $default_folders = array(
        'scss'  => array('common.scss'),
        'js'    => array(),
        'css'   => array(),
        'img'   => array(),
    );

    public function __construct($project_name)
    {
        if (empty($project_name))
            throw new \Exception('Project name cannot be empty!');

        $this->project_name = preg_replace("#[^\w-._\d]+#", "", $project_name);

        $this->base_path = Config::get('base_path', 'default');
    }

    public function run()
    {
        $this->check_project();

        $this->create_dir_structure();

        $this->init_git_repo();

        $this->checkout_project();
    }

    protected function check_project()
    {
        $this->project_path = Config::get('base_path', 'default') . Config::get('project_folder', 'default') . DIRECTORY_SEPARATOR . $this->project_name;

        if (is_dir($this->project_path))
            throw new \Exception("Project '{$this->project_name}' has already exists");
    }

    protected function create_dir_structure()
    {
        $this->create_dir($this->project_path);

        foreach ($this->default_folders as $folder => $file_arr)
        {
            $f = $this->project_path . DIRECTORY_SEPARATOR . $folder;
            $this->create_dir($f);

            if (!empty($file_arr))
            {
                foreach($file_arr as $filename)
                {
                    $this->create_file($f . DIRECTORY_SEPARATOR . $filename, false);
                }
            }
        }

        $this->create_base_files();
    }

    protected function create_base_files()
    {
        $this->create_file('config.php');
        $this->create_file('index.html', true, "hi i'm in {$this->project_path}/index.html");
    }

    private function create_dir($path, $mode = '0755', $recursive = false)
    {
        mkdir($path, 0755, $recursive);
    }

    private function create_file($filename, $add_base = true, $content = '', $mode = 'w')
    {
        $filename = ($add_base === true) ? ($this->project_path . DIRECTORY_SEPARATOR . $filename) : $filename;

        $h = fopen($filename, $mode);
        if ($content) {
            fwrite($h, $content);
        }
        fclose($h);
    }

    private function init_git_repo()
    {
        chdir($this->project_path);
        shell_exec("git init");
        shell_exec("git config user.email '<>'");
        shell_exec("git config user.name 'AUTO'");
        shell_exec("git add .");
        shell_exec("git commit -am \"Initial commit\"");

        chdir($this->base_path);
        shell_exec("git submodule add {$this->project_path}");
        shell_exec("git add .gitmodules");
        shell_exec("git config user.email '<>'");
        shell_exec("git config user.name 'AUTO'");
        shell_exec("git commit -m \"Project {$this->project_name} is added\"");
    }

    private function checkout_project()
    {
        Session::set('project', $this->project_name);
        header('Location: /');
    }
}