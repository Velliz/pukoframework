<?php

namespace pukoframework\pda;

use Exception;

/**
 * Interface ModelContracts
 * @package plugins
 */
interface ModelContracts
{

    /**
     * This method return data available on the database in array structure
     * @return array
     * @throws Exception
     */
    public static function GetData();

    /**
     * This method return one row from database specified by id
     * @param $id
     * @return array|null
     * @throws Exception
     */
    public static function GetById($id);

    /**
     * This method return true if row found or false if not found from database specified by id
     * @param $id
     * @return bool
     * @throws Exception
     */
    public static function IsExists($id);

    /**
     * This method return true if row found or false if not found from database specified by custom selection
     * @param string $column
     * @param string $value
     * @return bool
     * @throws Exception
     */
    public static function IsExistsWhere(string $column, string $value);

    /**
     * This method return count of the data on the database
     * @return int
     * @throws Exception
     */
    public static function GetDataSize();

    /**
     * This method return count of the data on the database with selected conditions
     * @param array $keyword
     * @param int|null $limit
     * @param int|null $offset
     * @return mixed
     * @throws Exception
     */
    public static function GetDataSizeWhere(array $condition = [], int $limit = null, int $offset = null);

    /**
     * This method return last inserted data
     * @return mixed
     * @throws Exception
     */
    public static function GetLastData();

    /**
     * This method return search result data available on the database in array structure
     * @param array $keyword
     * @param int $limit
     * @param int $offset
     * @return mixed
     * @throws Exception
     */
    public static function SearchData(array $keyword = [], int $limit = null, int $offset = null);

    /**
     * This method return search result data available on the database in datatables json format
     * @param array $condition
     * @return mixed
     * @throws Exception
     */
    public static function GetDataTable(array $condition = []);

}
