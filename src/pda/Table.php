<?php

namespace pukoframework\pda;

use Exception;

/**
 * Class Schema
 * @package pukoframework\pda
 */
class Table
{

    /**
     * @param Model $obj
     * @throws Exception
     */
    public static function create(Model $obj)
    {
        if (!$obj instanceof Model) {
            throw new Exception('parameter must be instance of Model');
        }


    }

    public static function rename()
    {

    }

    public static function drop()
    {

    }

    public static function dropIfExists()
    {

    }

    public static function hasTable()
    {

    }

    public static function hasColumn()
    {

    }

}