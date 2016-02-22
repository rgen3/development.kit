<?php

namespace core;

class Configuration
{
    protected $login = false;
    protected $session = null;
    protected $path = 'login.html';
    protected $data = array();

    public function __construct()
    {
        $this->session = Session::getInstance();

        if (!$this->check_login())
            $this->login();

        $this->set_path();
    }

    public function run()
    {
        if (!$this->check_login())
            return false;

        if (!empty($_POST))
        {
            if (isset($_POST['project_create']))
            {
                $this->create_project();
            } else {
                $this->select_project();
            }
        }

        $project_path = Config::get('base_path', 'default') . Config::get('project_folder', 'default');
        $projects = array();
        foreach (glob($project_path . DIRECTORY_SEPARATOR . '*') as $folder) {
            $pattern = DIRECTORY_SEPARATOR . '([\w-._\d]+)$';
            if (preg_match("#{$pattern}#", $folder, $matches)) {
                $project = $matches[1];
                if ($project == 'configure')
                    continue;

                Config::load($project);

                try
                {
                    $projects[$project] = Config::get('name', $project);
                }
                catch (\Exception $e)
                {
                    $projects[$project] = $project;
                }
            }
        }

        $this->set_data('projects', $projects);
    }

    protected function create_project()
    {
        $creator = new Creator($_POST['project_name']);
        $creator->run();
    }

    protected function select_project()
    {
        $project_path = Config::get('base_path', 'default') . Config::get('project_folder', 'default') . DIRECTORY_SEPARATOR . $_POST['project'];

        if (is_dir($project_path))
            Session::set('project', $_POST['project']);

        header('Location: /');
    }

    protected function set_data($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function get_data()
    {
        return $this->data;
    }

    public function check_login()
    {
        $password = $this->get_password();

        if (Config::get('password', 'configure') === $password)
            Session::set('login', true);

        return $this->session->get('login');
    }

    protected function login()
    {
        $password = $this->get_password();

        if ($password === Config::get('password', 'configure'))
        {
            Session::set('login', true);
        }
    }

    private function get_password()
    {
        return isset($_POST['password']) ? $_POST['password'] : false;
    }

    public function set_path()
    {
        $this->path = $this->session->get('login') ? 'index.html' : 'login.html';
    }

    public function get_path()
    {
        return $this->path;
    }
}