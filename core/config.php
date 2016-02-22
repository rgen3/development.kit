<?php

namespace core;

class Config
{
    private static $configs = array();

    public static function load($name)
    {
        if (!array_key_exists('default', self::$configs))
            self::$configs['default'] = include_once 'config/base.php';

        $path = self::$configs['default']['base_path'] . Config::get('project_folder', 'default') . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'config.php';

        if (!file_exists($path))
            throw new \Exception("File {$path} doesn't exists");

        $configs = include_once $path;

        self::$configs[$name] = $configs;
    }

    public static function get($name, $type)
    {
        if (!array_key_exists($type, self::$configs))
            self::load($type);

        if (!array_key_exists($name, self::$configs[$type]))
        {
            if (array_key_exists($name, self::$configs['default']))
            {
                $result = self::$configs['default'][$name];
            }
            else
            {
                throw new \Exception("Config {$name} not found in {$type}.php");
            }
        }
        else
        {
            $result = self::$configs[$type][$name];
        }

        return $result;
    }

    public static function set($name, $value, $type = 'default')
    {
        self::$configs[$type][$name] = $value;
    }
}