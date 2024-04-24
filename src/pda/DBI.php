<?php
/**
 * pukoframework.
 * MVC PHP Framework for quick and fast PHP Application Development.
 * Copyright (c) 2016, Didit Velliz
 *
 * @author Didit Velliz
 * @link https://github.com/velliz/pukoframework
 * @since Version 0.9.1
 */

namespace pukoframework\pda;

use Exception;
use PDOException;
use PDO;
use pukoframework\config\Config;
use pukoframework\log\LogTransforms;

/**
 * Class DBI
 * @package pukoframework\pda
 */
class DBI
{

    use LogTransforms;

    private static $dbi;
    protected $query;

    protected $dbType;
    protected $dbName;
    private $username;
    private $password;
    private $host;
    private $port;

    private $driver = 'pdo';

    private $queryPattern = '#@([0-9]+)#';

    /**
     * @param $connection array
     * @param $database
     */
    protected function DBISet(array $connection, $database)
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
     * DBI constructor.
     * @param $query
     * @param $database
     * @throws Exception
     */
    protected function __construct($query, $database)
    {
        $this->query = $query;
        if (is_object(self::$dbi)) {
            return;
        }

        $this->DBISet(Config::Data('database'), $database);

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
            self::$dbi = new PDO($pdoConnection, $this->username, $this->password);
            self::$dbi->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $ex) {
            self::$dbi = null;

            $this->notify("Database error: {$ex->getMessage()}", $this->query, $ex->getTrace());
            throw new Exception("Database error: {$ex->getMessage()}");
        }
    }

    /**
     * @param $query string
     * @param null $database
     * @return DBI
     * @throws Exception
     */
    public static function Prepare(string $query = '', $database = null): DBI
    {
        if ($database === null) {
            $database = 'primary';
        }
        return new DBI($query, $database);
    }

    /**
     * @param array $array
     * @param string $identity
     * @return bool|string
     * @throws Exception
     */
    public function Save(array $array = [], string $identity = '')
    {
        $ticks = "";
        if ($this->dbType === 'mysql') {
            $ticks = "`";
        }

        //separate key dan values
        $keys = [];
        $values = [];
        foreach ($array as $k => $v) {
            $keys[] = $k;
            $values[] = $v;
        }

        //build key
        $key_string = "";
        foreach ($keys as $key) {
            $key_string .= "{$ticks}{$key}{$ticks}, ";
        }
        $key_string = substr($key_string, 0, -2);

        //output inserted.? for sql-server
        $output = "";
        if ($this->dbType === 'sqlsrv' && strlen($identity) > 0) {
            $output .= "OUTPUT INSERTED.{$identity}";
        }

        //build value
        $value_string = "";
        foreach ($keys as $key) {
            $value_string .= ":{$key}, ";
        }
        $value_string = substr($value_string, 0, -2);

        $last_id = null;
        $insert_text = "INSERT INTO {$this->query} ({$key_string}) {$output} VALUES ({$value_string});";
        try {
            $statement = self::$dbi->prepare($insert_text);
            foreach ($keys as $no => $key) {
                $statement->bindValue(":{$key}", $values[$no]);
            }

            if ($statement->execute()) {
                if ($this->dbType === 'mysql') {
                    $last_id = self::$dbi->lastInsertId();
                }
                if ($this->dbType === 'sqlsrv') {
                    if (strlen($identity) > 0) {
                        $result = $statement->fetch(PDO::FETCH_ASSOC);
                        $last_id = $result[$identity];
                    }
                }
                self::$dbi = null;
                return $last_id;
            }
            self::$dbi = null;
            return false;
        } catch (PDOException $ex) {
            self::$dbi = null;

            $this->notify("Database error: {$ex->getMessage()}", $this->query, $ex->getTrace());
            throw new Exception("Database error: {$ex->getMessage()}");
        }
    }

    /**
     * @param array $where
     * @return bool
     * @throws Exception
     */
    public function Delete(array $where = [])
    {
        $ticks = "";
        if ($this->dbType === 'mysql') {
            $ticks = "`";
        }

        $key_where = " WHERE ";
        foreach ($where as $key => $value) {
            $key_where .= "{$ticks}{$key}{$ticks} = '{$value}' AND ";
        }
        $key_where = substr($key_where, 0, -4);

        try {
            $statement = self::$dbi->prepare("DELETE FROM {$this->query} {$key_where};");
            $result = $statement->execute();

            self::$dbi = null;

            return $result;
        } catch (PDOException $ex) {
            self::$dbi = null;

            $this->notify("Database error: {$ex->getMessage()}", $this->query, $ex->getTrace());
            throw new Exception("Database error: {$ex->getMessage()}");
        }
    }

    /**
     * @param array $ids
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function Update(array $ids = [], array $data = []): bool
    {
        $key_string = "";
        foreach ($data as $key => $val) {
            $key_string .= "{$key} = :{$key}, ";
        }
        $key_string = substr($key_string, 0, -2);

        $key_where = " WHERE ";
        foreach ($ids as $key => $val) {
            $key_where .= "{$key} = :{$key} AND ";
        }
        $key_where = substr($key_where, 0, -4);

        $update_text = "UPDATE {$this->query} SET {$key_string} {$key_where};";

        try {
            $statement = self::$dbi->prepare($update_text);
            foreach ($data as $key => $val) {
                $statement->bindValue(":{$key}", $val);
            }
            foreach ($ids as $key => $val) {
                $statement->bindValue(":{$key}", $val);
            }
            $result = $statement->execute();

            self::$dbi = null;

            return $result;
        } catch (PDOException $ex) {
            self::$dbi = null;

            $this->notify("Database error: {$ex->getMessage()}", $this->query, $ex->getTrace());
            throw new Exception("Database error: {$ex->getMessage()}");
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function GetData(): array
    {
        $parameters = func_get_args();

        //flatern array parameter
        $flatern = [];
        if (isset($parameters[0])) {
            if (is_array($parameters[0])) {
                foreach ($parameters[0] as $key => $item) {
                    $flatern[$key] = $item;
                }
                $parameters = $flatern;
            }
        }

        $args = count($parameters);
        if ($args > 0) {
            $this->query = preg_replace_callback($this->queryPattern, array($this, '_query_prepare_select'), $this->query);
        }
        try {
            $statement = self::$dbi->prepare($this->query);
            if ($args > 0) {
                $statement->execute($parameters);
            } else {
                $statement->execute();
            }
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);

            self::$dbi = null;

            return $result;
        } catch (PDOException $ex) {
            self::$dbi = null;

            $this->notify("Database error: {$ex->getMessage()}", $this->query, $ex->getTrace());
            throw new Exception("Database error: {$ex->getMessage()}");
        }
    }

    /**
     * @return mixed|null
     * @throws Exception
     */
    public function FirstRow()
    {
        $parameters = func_get_args();

        //flatern array parameter
        $flatern = [];
        if (isset($parameters[0])) {
            if (is_array($parameters[0])) {
                foreach ($parameters[0] as $key => $item) {
                    $flatern[$key] = $item;
                }
                $parameters = $flatern;
            }
        }

        $args = count($parameters);
        if ($args > 0) {
            $this->query = preg_replace_callback($this->queryPattern, array($this, '_query_prepare_select'), $this->query);
        }

        try {
            $statement = self::$dbi->prepare($this->query);
            if ($args > 0) {
                $statement->execute($parameters);
            } else {
                $statement->execute();
            }
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            $result = $result[0] ?? null;

            self::$dbi = null;

            return $result;
        } catch (PDOException $ex) {
            self::$dbi = null;

            $this->notify("Database error: {$ex->getMessage()}", $this->query, $ex->getTrace());
            throw new Exception("Database error: {$ex->getMessage()}");
        }
    }

    /**
     * @return mixed|null
     * @throws Exception
     */
    public function Run()
    {
        $parameters = func_get_args();

        //flatten array parameter
        $flatern = [];
        if (isset($parameters[0])) {
            if (is_array($parameters[0])) {
                foreach ($parameters[0] as $key => $item) {
                    $flatern[$key] = $item;
                }
                $parameters = $flatern;
            }
        }

        $args = count($parameters);
        if ($args > 0) {
            $this->query = preg_replace_callback($this->queryPattern, array($this, '_query_prepare_select'), $this->query);
        }

        try {
            $statement = self::$dbi->prepare($this->query);
            if ($args > 0) {
                $result = $statement->execute($parameters);
            } else {
                $result = $statement->execute();
            }
            self::$dbi = null;

            return $result;
        } catch (PDOException $ex) {
            self::$dbi = null;

            $this->notify("Database error: {$ex->getMessage()}", $this->query, $ex->getTrace());
            throw new Exception("Database error: {$ex->getMessage()}");
        }
    }

    /**
     * @param string $sp_name
     * @param array $payloads
     * @return bool
     * @throws Exception
     */
    public function Call(string $sp_name = '', array $payloads = []): bool
    {
        $attr = "";
        foreach ($payloads as $value) {
            $attr .= "'{$value}', ";
        }
        $attr = substr($attr, 0, -2);

        try {
            $statement = self::$dbi->prepare("CALL {$sp_name}({$attr});");
            if (sizeof($payloads) > 0) {
                $result = $statement->execute($payloads);
            } else {
                $result = $statement->execute();
            }
            self::$dbi = null;

            return $result;
        } catch (PDOException $ex) {
            self::$dbi = null;

            $this->notify("Database error: {$ex->getMessage()}", $this->query, $ex->getTrace());
            throw new Exception("Database error: {$ex->getMessage()}");
        }
    }

    /**
     * @return string
     */
    private function _query_prepare_select(): string
    {
        return '?';
    }

}
