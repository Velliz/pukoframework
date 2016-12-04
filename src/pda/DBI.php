<?php
/**
 * pukoframework
 *
 * MVC PHP Framework for quick and fast PHP Application Development.
 *
 * This content is released under the Apache License Version 2.0, January 2004
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * Copyright (c) 2016, Didit Velliz
 *
 * @package    puko/framework
 * @author    Didit Velliz
 * @link    https://github.com/velliz/pukoframework
 * @since    Version 0.9.1
 *
 */
namespace pukoframework\pda;

use Exception;
use PDO;
use PDOException;

/**
 * Class DBI
 * @package pukoframework\pda
 */
class DBI
{

    private static $dbi;

    protected $query;
    protected $queryParams;

    protected $db_type;
    protected $db_name;

    private $username;
    private $password;

    private $host;
    private $port;

    private $queryPattern = '#@([0-9]+)#';
    private $connectionPath;

    /**
     * @param $connection array
     */
    protected function DBISet($connection)
    {
        $this->db_type = $connection['dbType'];
        $this->host = $connection['host'];
        $this->port = $connection['port'];
        $this->db_name = $connection['dbName'];
        $this->username = $connection['user'];
        $this->password = $connection['pass'];
    }

    /**
     * DBI constructor.
     * @param $query
     * @throws Exception
     */
    private function __construct($query)
    {
        $this->query = $query;
        if (is_object(self::$dbi)) return;
        $this->connectionPath = ROOT . "/config/database.php";
        if (!file_exists($this->connectionPath))
            die("Puko Error (PDA001) Database configuration file not found or ROOT is not set.");

        $this->DBISet(include "$this->connectionPath");
        $pdoConnection = "$this->db_type:host=$this->host;port=$this->port;dbname=$this->db_name";
        self::$dbi = new PDO($pdoConnection, $this->username, $this->password);

        self::$dbi->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @param $query string
     * @return DBI
     */
    public static function Prepare($query)
    {
        return new DBI($query);
    }

    /**
     * @param $array
     * @param bool $hasBinary
     * @return bool|string
     * @throws Exception
     */
    public function Save($array, $hasBinary = false)
    {
        $keys = $values = array();
        $insert_text = "INSERT INTO `$this->query`";
        foreach ($array as $k => $v) {
            $keys[] = $k;
            $values[] = $v;
        }
        $key_string = "(";
        foreach ($keys as $key) {
            $key_string = $key_string . "`" . $key . "`, ";
        }
        $key_string = substr($key_string, 0, -2);
        $insert_text = $insert_text . " " . $key_string . ")";
        $insert_text = $insert_text . " VALUES ";
        $value_string = "(";
        foreach ($keys as $key) {
            $value_string = $value_string . ":" . $key . ", ";
        }
        $value_string = substr($value_string, 0, -2);
        $insert_text = $insert_text . $value_string . ");";

        try {
            $statement = self::$dbi->prepare($insert_text);
            foreach ($keys as $no => $key) {
                if (strpos($key, 'file') !== false) {
                    if (!$hasBinary) $blob = file_get_contents($values[$no], 'rb');
                    else $blob = $key;
                    $statement->bindValue(':' . $key, $blob, PDO::PARAM_LOB);
                } else $statement->bindValue(':' . $key, $values[$no]);
            }
            if ($statement->execute()) return self::$dbi->lastInsertId();
            else return false;
        } catch (PDOException $ex) {
            throw new Exception('Database error: ' . $ex->getMessage());
        }
    }

    /**
     * @param $arrWhere
     * @return bool
     * @throws Exception
     */
    public function Delete($arrWhere)
    {
        $del_text = "DELETE FROM `$this->query` WHERE ";
        foreach ($arrWhere as $col => $value) {
            $del_text .= "`" . $col . "`" . " = '" . $value . "' AND ";
        }
        $del_text = substr($del_text, 0, -4);
        try {
            $statement = self::$dbi->prepare($del_text);
            return $statement->execute($arrWhere);
        } catch (PDOException $ex) {
            throw new Exception('Database error: ' . $ex->getMessage());
        }
    }

    /**
     * @param $id
     * @param $array
     * @param bool $hasBinary
     * @return bool
     * @throws Exception
     */
    public function Update($id, $array, $hasBinary = false)
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
                if (strpos($key, 'file') !== false) {
                    if (!$hasBinary) $blob = file_get_contents($val, 'rb');
                    else $blob = $val;
                    $statement->bindValue(':' . $key, $blob, PDO::PARAM_LOB);
                } else $statement->bindValue(':' . $key, $val);
            }
            foreach ($id as $key => $val) {
                if (strpos($key, 'file') !== false) {
                    if (!$hasBinary) $blob = file_get_contents($val, 'rb');
                    else $blob = $val;
                    $statement->bindValue(':' . $key, $blob, PDO::PARAM_LOB);
                } else $statement->bindValue(':' . $key, $val);
            }
            return $statement->execute();
        } catch (PDOException $ex) {
            throw new Exception('Database error: ' . $ex->getMessage());
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function GetData()
    {
        $parameters = func_get_args();
        $argCount = count($parameters);
        $this->queryParams = $parameters;
        if ($argCount > 0) $this->query = preg_replace_callback($this->queryPattern, array($this, 'queryParseReplace'), $this->query);
        try {
            $statement = self::$dbi->prepare($this->query);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            throw new Exception('Database error: ' . $ex->getMessage());
        }
    }

    /**
     * @return mixed|null
     * @throws Exception
     */
    public function FirstRow()
    {
        $parameters = func_get_args();
        $argCount = count($parameters);
        $this->queryParams = $parameters;
        if ($argCount > 0) $this->query = preg_replace_callback($this->queryPattern, array($this, 'queryParseReplace'), $this->query);
        try {
            $statement = self::$dbi->prepare($this->query);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            isset($result[0]) ? $result = $result[0] : $result = null;
            return $result;
        } catch (PDOException $ex) {
            throw new Exception('Database error: ' . $ex->getMessage());
        }
    }

    /**
     * @param $key
     * @return string
     */
    private function queryParseReplace($key)
    {
        $aKey = ((int)$key[1] - 1);
        if (isset($this->queryParams[$aKey])) {
            $var = $this->queryParams[$aKey];
            if (is_string($var)) {
                return ("'" . $var . "'");
            } else {
                if (is_bool($var)) {
                    return ($var ? '1' : '0');
                } else {
                    if (is_array($var)) {
                        $s = '';
                        foreach ($var as $item) {
                            if (is_string($item)) {
                                $s .= (",'" . $item . "'");
                            } else {
                                $s .= (',' . $item);
                            }
                        }
                        $s[0] = '(';
                        return ($s . ')');
                    } else {
                        return $var;
                    }
                }
            }
        }
        return '';
    }

    /**
     * @return bool|string
     */
    public static function NOW()
    {
        return date('c');
    }

}