<?php
/**
 * pukoframework.
 * MVC PHP Framework for quick and fast PHP Application Development.
 * Copyright (c) 2016, Didit Velliz
 *
 * @author Didit Velliz
 * @link https://github.com/velliz/pukoframework
 * @since Version 1.3.4
 */
namespace pukoframework\pda;

use Exception;
use PDO;
use PDOException;
use pukoframework\config\Config;
use pukoframework\log\LogTransforms;

/**
 * Class DBO
 * @package pukoframework\pda
 * @desc help to create PDO object instances.
 */
class DBO
{

    use LogTransforms;

    protected static $dbo;

    protected $dbType;
    protected $dbName;

    private $username;
    private $password;
    private $host;
    private $port;

    private $driver = 'pdo';

    /**
     * @param $database
     * @throws Exception
     */
    public function __construct($database)
    {
        if (is_object(self::$dbo)) {
            return;
        }

        $this->DBOSet(Config::Data('database'), $database);

        $pdoConnection = null;
        //connection from mysql
        if ($this->dbType === 'mysql') {
            $pdoConnection = "$this->dbType:host=$this->host;port=$this->port;charset=utf8mb4";
            if ($this->dbName !== '') {
                $pdoConnection = "$this->dbType:host=$this->host;port=$this->port;dbname=$this->dbName;charset=utf8mb4";
            }
        }
        if ($this->dbType === 'sqlsrv') {
            if ($this->driver === 'odbc') {
                //connection from pdo_odbc
                $pdoConnection = "odbc:Driver={SQL Server};Server=$this->host";
                if ($this->dbName !== '') {
                    $pdoConnection = "odbc:Driver={SQL Server};Server=$this->host;Database=$this->dbName";
                }
            }
            if ($this->driver === 'sqlsrv') {
                //connection from pdo_sqlsrv
                $pdoConnection = "sqlsrv:Server=$this->host,$this->port";
                if ($this->dbName !== '') {
                    $pdoConnection = "sqlsrv:Server=$this->host,$this->port;Database=$this->dbName";
                }
            }
        }

        try {
            self::$dbo = new PDO($pdoConnection, $this->username, $this->password);
            self::$dbo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $ex) {
            self::$dbo = null;

            $this->notify("Database error", $ex->getMessage(), $ex->getTrace());
            throw new Exception("Database error: {$ex->getMessage()}");
        }
    }

    /**
     * @param $connection array
     * @param $database
     */
    protected function DBOSet(array $connection, $database)
    {
        $this->dbType = $connection[$database]['dbType'];
        $this->host = $connection[$database]['host'];
        $this->port = $connection[$database]['port'];

        $this->dbName = $connection[$database]['dbName'];
        $this->username = $connection[$database]['user'];
        $this->password = $connection[$database]['pass'];

        if (isset($connection[$database]['driver'])) {
            $this->driver = $connection[$database]['driver'];
        }
    }

    /**
     * @param $database
     * @return DBO
     * @throws Exception
     * @desc make singleton object database callable as the PDO object instances.
     */
    public static function Init($database = null): DBO
    {
        if ($database === null) {
            $database = 'primary';
        }
        return new DBO($database);
    }

    /**
     * @return PDO|null
     */
    public static function GetPDO(): PDO
    {
        return self::$dbo;
    }

}