<?php

namespace pukoframework\pda;

use Exception;

/**
 * Class Database
 * @package pukoframework\pda
 */
class Database
{

    /**
     * @param $name
     * @return mixed|null
     * @throws Exception
     */
    public static function createDB($name)
    {
        $result = DBI::Prepare("CREATE DATABASE {$name};")->Run();
        return $result;
    }

    /**
     * @param $name
     * @return mixed|null
     * @throws Exception
     */
    public static function dropDB($name)
    {
        $result = DBI::Prepare("DROP DATABASE {$name};")->Run();
        return $result;
    }

    /**
     * @param $name
     * @param $path
     * @return mixed|null
     * @throws Exception
     */
    public static function backupDB($name, $path)
    {
        $result = DBI::Prepare("BACKUP DATABASE {$name} TO DISK = '{$path}';")->Run();
        return $result;
    }

}