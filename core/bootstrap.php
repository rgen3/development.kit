<?php

namespace core;

class Bootstrap
{
    private static $instance;
    private $path;

    private function __construct()
    {
        $this->registerDefaultConfigs();
    }

    public static function run()
    {
        try {
            if (is_null(self::$instance))
            {
                self::$instance = new bootstrap();
            }

            self::$instance->autoloadRegister();

            self::$instance->processPath();

            $app = new App(self::$instance->path);
            $app->run();
        }
        catch (\Exception $e)
        {
            echo $e->getMessage();
        }
    }

    private function registerDefaultConfigs()
    {

    }

    private function processPath()
    {
        $this->path = $_SERVER['REDIRECT_URL'];
    }

    private function autoloadRegister()
    {
        spl_autoload_register(__NAMESPACE__ . '\\bootstrap::autoloader');
    }

    private static function autoloader($path)
    {
        $path = strtolower($path);

        $path = str_replace('\\', DIRECTORY_SEPARATOR, $path);

        $path = getcwd() . DIRECTORY_SEPARATOR . $path . '.php';

        if (!file_exists($path))
            throw new \Exception("File not {$path} doesn't exists");

        include_once $path;
    }
}