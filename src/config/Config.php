<?php

namespace pukoframework\config;

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
     * @return mixed
     */
    public static function Data($name)
    {
        $file_config = sprintf("%s/config/%s.php", ROOT, $name);
        if (!file_exists($file_config)) {
            die(sprintf("Puko Fatal Error (AUTH001) Config file '%s' not found", $name));
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