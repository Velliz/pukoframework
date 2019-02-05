<?php
/**
 * pukoframework.
 * MVC PHP Framework for quick and fast PHP Application Development.
 * Copyright (c) 2016, Didit Velliz
 *
 * @author Didit Velliz
 * @link https://github.com/velliz/pukoframework
 * @since Version 1.1.3
 */

namespace pukoframework\config;

use pukoframework\Framework;

/**
 * Class Config
 * @package pukoframework\config
 */
class Config
{

    /**
     * Config constructor.
     */
    private function __construct()
    {
    }

    /**
     * @param $name
     * @param array $default
     * @return mixed
     */
    public static function Data($name, $default = array())
    {
        $file_config = sprintf("%s/config/%s.php", Framework::$factory->getRoot(), $name);
        if (!file_exists($file_config)) {
            return $default;
        }
        return self::Get(include "$file_config");
    }

    /**
     * @param $config
     * @return mixed
     *
     * Escaping dynamic include warning
     */
    private static function Get($config)
    {
        return $config;
    }
}