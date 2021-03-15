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
use Memcached;
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
    protected $queryParams;

    protected $dbType;
    protected $dbName;

    private $username;
    private $password;

    private $host;
    private $port;
    private $driver = 'pdo';

    /**
     * @var bool
     */
    private $cache = false;
    private $cacheExpiry = 60;

    private $queryPattern = '#@([0-9]+)#';

    /**
     * @param $connection array
     * @param $database
     */
    protected function DBISet($connection, $database)
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
        if ($this->dbType === 'mysql') {
            $pdoConnection = "$this->dbType:host=$this->host;port=$this->port";
            if ($this->dbName !== '') {
                $pdoConnection = "$this->dbType:host=$this->host;port=$this->port;dbname=$this->dbName";
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
            $this->notify('Connection failed: ' . $ex->getMessage(), $query, $ex->getTrace());
            throw new Exception("Connection failed: " . $ex->getMessage());
        }
    }

    /**
     * @param int $timeInSeconds
     * @return $this
     */
    public function CacheFor($timeInSeconds = 60)
    {
        $this->cache = true;
        $this->cacheExpiry = $timeInSeconds;
        return $this;
    }


    /**
     * @param $query string
     * @param null $database
     * @return DBI
     * @throws Exception
     */
    public static function Prepare($query = '', $database = null)
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
    public function Save($array = [], $identity = '')
    {
        $keys = $values = [];
        $insert_text = "INSERT INTO $this->query";
        foreach ($array as $k => $v) {
            $keys[] = $k;
            $values[] = $v;
        }
        $key_string = "(";
        foreach ($keys as $key) {
            if ($this->dbType === 'mysql') {
                $key_string = $key_string . "`" . $key . "`, ";
            } else {
                $key_string = $key_string . " " . $key . ", ";
            }
        }
        $key_string = substr($key_string, 0, -2);
        if ($this->dbType === 'sqlsrv') {
            if (strlen($identity) > 0) {
                $insert_text = $insert_text . " " . $key_string . ") OUTPUT INSERTED.{$identity}";
            } else {
                $insert_text = $insert_text . " " . $key_string . ")";
            }
        } else {
            $insert_text = $insert_text . " " . $key_string . ")";
        }
        $insert_text = $insert_text . " VALUES ";
        $value_string = "(";
        foreach ($keys as $key) {
            $value_string = $value_string . ":" . $key . ", ";
        }
        $value_string = substr($value_string, 0, -2);
        $insert_text = $insert_text . $value_string . ");";

        try {
            $lastid = null;
            $statement = self::$dbi->prepare($insert_text);
            foreach ($keys as $no => $key) {
                $statement->bindValue(':' . $key, $values[$no]);
            }
            if ($statement->execute()) {
                if ($this->dbType === 'mysql') {
                    $lastid = self::$dbi->lastInsertId();
                }
                if ($this->dbType === 'sqlsrv') {
                    if (strlen($identity) > 0) {
                        $result = $statement->fetch(PDO::FETCH_ASSOC);
                        $lastid = $result[$identity];
                    }
                }
                self::$dbi = null;
                return $lastid;
            } else {
                self::$dbi = null;
                return false;
            }
        } catch (PDOException $ex) {
            self::$dbi = null;
            $this->notify('Database error: ' . $ex->getMessage(), $insert_text, $ex->getTrace());
            throw new Exception('Database error: ' . $ex->getMessage());
        }
    }

    /**
     * @param $arrWhere
     * @return bool
     * @throws \Exception
     */
    public function Delete($arrWhere)
    {
        $del_text = "DELETE FROM $this->query WHERE ";
        foreach ($arrWhere as $col => $value) {
            if ($this->dbType === 'mysql') {
                $del_text .= "`" . $col . "`" . " = '" . $value . "' AND ";
            } else {
                $del_text .= $col . " = " . $value . " AND ";
            }
        }
        $del_text = substr($del_text, 0, -4);
        try {
            $statement = self::$dbi->prepare($del_text);
            $result = $statement->execute();
            self::$dbi = null;
            return $result;
        } catch (PDOException $ex) {
            self::$dbi = null;
            $this->notify('Database error: ' . $ex->getMessage(), $del_text, $ex->getTrace());
            throw new Exception('Database error: ' . $ex->getMessage());
        }
    }

    /**
     * @param $id
     * @param $array
     * @return bool
     * @throws \Exception
     */
    public function Update($id, $array)
    {
        $update_text = "UPDATE $this->query SET";
        $key_string = "";
        $key_where = " WHERE ";
        foreach ($array as $key => $val) {
            $key_string .= $key . " = :" . $key . ", ";
        }
        $key_string = substr($key_string, 0, -2);
        foreach ($id as $key => $val) {
            $key_where .= $key . " = :" . $key . " AND ";
        }
        $key_where = substr($key_where, 0, -4);
        $update_text .= " " . $key_string . $key_where;
        try {
            $statement = self::$dbi->prepare($update_text);
            foreach ($array as $key => $val) {
                $statement->bindValue(':' . $key, $val);
            }
            foreach ($id as $key => $val) {
                $statement->bindValue(':' . $key, $val);
            }
            $result = $statement->execute();
            self::$dbi = null;
            return $result;
        } catch (PDOException $ex) {
            self::$dbi = null;
            $this->notify('Database error: ' . $ex->getMessage(), $update_text, $ex->getTrace());
            throw new Exception('Database error: ' . $ex->getMessage());
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function GetData()
    {
        $memcached = $keys = null;
        if ($this->cache) {
            $cacheConfig = Config::Data('app')['cache'];
            $memcached = new Memcached();
            $memcached->addServer($cacheConfig['host'], $cacheConfig['port']);

            $keys = hash('ripemd160', $this->query);
            $item = $memcached->get($keys);
            if ($item) {
                return $item;
            }
        }

        $parameters = func_get_args();
        $argCount = count($parameters);
        $this->queryParams = $parameters;
        if ($argCount > 0) {
            $this->query = preg_replace_callback(
                $this->queryPattern,
                array($this, 'queryPrepareSelect'),
                $this->query
            );
        }
        try {
            $statement = self::$dbi->prepare($this->query);
            if ($argCount > 0) {
                $statement->execute($parameters);
            } else {
                $statement->execute();
            }
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            self::$dbi = null;

            if ($this->cache) {
                //doing memcached storage
                $memcached->set($keys, $statement->fetchAll(PDO::FETCH_ASSOC), $this->cacheExpiry);
                return $memcached->get($keys);
            }

            return $result;

        } catch (PDOException $ex) {
            self::$dbi = null;
            $this->notify('Database error: ' . $ex->getMessage(), $this->query, $ex->getTrace());
            throw new Exception('Database error: ' . $ex->getMessage());
        }
    }

    /**
     * @return mixed|null
     * @throws \Exception
     */
    public function FirstRow()
    {
        $parameters = func_get_args();
        $argCount = count($parameters);
        $this->queryParams = $parameters;
        if ($argCount > 0) {
            $this->query = preg_replace_callback($this->queryPattern, array($this, 'queryPrepareSelect'), $this->query);
        }
        try {
            $statement = self::$dbi->prepare($this->query);
            if ($argCount > 0) {
                $statement->execute($parameters);
            } else {
                $statement->execute();
            }
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            isset($result[0]) ? $result = $result[0] : $result = null;
            self::$dbi = null;
            return $result;
        } catch (PDOException $ex) {
            self::$dbi = null;
            $this->notify('Database error: ' . $ex->getMessage(), $this->query, $ex->getTrace());
            throw new Exception('Database error: ' . $ex->getMessage());
        }
    }

    /**
     * @return mixed|null
     * @throws \Exception
     */
    public function Run()
    {
        $parameters = func_get_args();
        $argCount = count($parameters);
        $this->queryParams = $parameters;
        if ($argCount > 0) {
            $this->query = preg_replace_callback($this->queryPattern, array($this, 'queryPrepareSelect'), $this->query);
        }
        try {
            $statement = self::$dbi->prepare($this->query);
            if ($argCount > 0) {
                $result = $statement->execute($parameters);
                self::$dbi = null;
                return $result;
            } else {
                $result = $statement->execute();
                self::$dbi = null;
                return $result;
            }
        } catch (PDOException $ex) {
            self::$dbi = null;
            $this->notify('Database error: ' . $ex->getMessage(), $this->query, $ex->getTrace());
            throw new Exception('Database error: ' . $ex->getMessage());
        }
    }

    /**
     * @param $name
     * @param $arrData
     * @return bool
     * @throws \Exception
     */
    public function Call($name, $arrData)
    {
        $argCount = count($arrData);
        $call = "CALL $name(";
        foreach ($arrData as $col => $value) {
            $call .= "'" . $value . "', ";
        }
        $call_text = substr($call, 0, -2);
        $call_text .= ");";
        try {
            $statement = self::$dbi->prepare($call_text);
            if ($argCount > 0) {
                $result = $statement->execute($arrData);
                self::$dbi = null;
                return $result;
            } else {
                $result = $statement->execute();
                self::$dbi = null;
                return $result;
            }
        } catch (PDOException $ex) {
            self::$dbi = null;
            $this->notify('Database error: ' . $ex->getMessage(), $this->query, $ex->getTrace());
            throw new Exception('Database error: ' . $ex->getMessage());
        }
    }

    /**
     * @return string
     */
    private function queryPrepareSelect()
    {
        return '?';
    }

}
