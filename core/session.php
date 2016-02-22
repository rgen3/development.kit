<?php

namespace core;

class Session
{
    private static $instance;
    public $data;

    private function __construct()
    {
        session_start();
        $this->data = $_SESSION;
    }

    public static function getInstance()
    {
        if (is_null(self::$instance))
            self::$instance = new Session();

        return self::$instance;
    }

    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    final public static function reset()
    {
        array_walk(
            Session::getInstance()->data,
            function($v, $k){Session::del($k);}
        );
        header("Location: /");
    }

    final public function get($key)
    {
        $value = null;
        if (array_key_exists($key, self::getInstance()->data))
            $value = self::getInstance()->data[$key];

        return $value;
    }

    final public static function set($key, $value)
    {
        self::getInstance()->data[$key] = $value;
        self::getInstance()->update();
    }

    final public static function del($key)
    {
        self::getInstance()->__unset($key);
        self::getInstance()->update();
    }

    final public static function update()
    {
        $_SESSION = self::getInstance()->data;
    }
}