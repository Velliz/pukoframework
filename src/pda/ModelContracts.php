<?php

namespace pukoframework\pda;

/**
 * Interface ModelContracts
 * @package plugins
 */
interface ModelContracts
{

    /**
     * This method return data available on the database in array structure
     * @return array
     */
    public static function GetData();

    /**
     * This method return one row from database specified by id
     * @param $id
     * @return array|null
     */
    public static function GetById($id);

    /**
     * This method return true if row found or false if not found from database specified by id
     * @param $id
     * @return bool
     */
    public static function IsExists($id);

    /**
     * This method return true if row found or false if not found from database specified by custom selection
     * @param $column
     * @param $value
     * @return bool
     */
    public static function IsExistsWhere($column, $value);

    /**
     * This method return count of the data on the database
     * @return int
     */
    public static function GetDataSize();

    /**
     * This method return count of the data on the database with selected conditions
     * @param array $condition
     * @return mixed
     */
    public static function GetDataSizeWhere($condition = array());

    /**
     * This method return the creator of the data
     * @param $id
     * @return string
     */
    public static function GetCreator($id);

    /**
     * This method return the time created of the data
     * @param $id
     * @return mixed
     */
    public static function GetCreated($id);

    /**
     * This method return last inserted data
     * @return mixed
     */
    public static function GetLastData();

    /**
     * This method return search result data available on the database in array structure
     * @param array $keyword
     * @return mixed
     */
    public static function SearchData($keyword = array());

    /**
     * This method return search result data available on the database in datatables json format
     * @param array $condition
     * @return mixed
     */
    public static function GetDataTable($condition = array());

}