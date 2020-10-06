<?php

namespace pukoframework\pda;

use Exception;

/**
 * Class Database
 * @package pukoframework\pda
 */
class Schema
{

    /**
     * @param $name
     * @return mixed|null
     * @throws Exception
     */
    public static function createDB($name)
    {
        return DBI::Prepare("CREATE DATABASE IF NOT EXISTS {$name};")->Run();
    }

    /**
     * @param $name
     * @return mixed|null
     * @throws Exception
     */
    public static function dropDB($name)
    {
        return DBI::Prepare("DROP DATABASE {$name};")->Run();
    }

    /**
     * @param $name
     * @param $path
     * @return mixed|null
     * @throws Exception
     */
    public static function backupDB($name, $path)
    {
        return DBI::Prepare("BACKUP DATABASE {$name} TO DISK = '{$path}';")->Run();
    }

}