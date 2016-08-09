<?php
namespace pukoframework\pda;

class DBI
{
    private static $dbi;
    private $queryPattern = '#@([0-9]+)#';
    private $queryParams;
    var $query;

    private function __construct($query)
    {
        $this->query = $query;
        if (!is_object(self::$dbi)) {
            $file = ROOT . "/config/database.php";
            if(!file_exists($file)) throw new \Exception("Database configuration file not found.");
            $connection = include $file;
            $dbtype = $connection['dbType'];
            $host = $connection['host'];
            $port = $connection['port'];
            $dbname = $connection['dbName'];
            $username = $connection['user'];
            $password = $connection['pass'];
            self::$dbi = new \PDO("$dbtype:host=$host;port=$port;dbname=$dbname", $username, $password);
            self::$dbi->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
    }

    public static function Prepare($query)
    {
        return new DBI($query);
    }

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

    public function Save($array)
    {
        $insert_text = "INSERT INTO `$this->query`";
        $keys = array();
        $values = array();
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

        $statement = self::$dbi->prepare($insert_text);

        foreach ($keys as $no => $key) {
            if (strpos($key, 'file') !== false) {
                $blob = file_get_contents($values[$no], 'rb');
                $statement->bindParam(':' . $key, $blob, \PDO::PARAM_LOB);
            } else {
                $statement->bindParam(':' . $key, $values[$no]);
            }
        }

        if ($statement->execute()) {
            return self::$dbi->lastInsertId();
        } else {
            return false;
        }

    }

    public function Delete($arrWhere)
    {
        $del_text = "DELETE FROM `$this->query` WHERE ";
        foreach ($arrWhere as $col => $value) {
            $del_text .= "`" . $col . "`" . " = '" . $value . "' AND ";
        }
        $del_text = substr($del_text, 0, -4);

        $statement = self::$dbi->prepare($del_text);
        return $statement->execute($arrWhere);
    }

    public function Update($id, $array)
    {
        $array = array_merge($id, $array);

        $update_text = "UPDATE `$this->query` SET ";

        $key_string = "";
        $key_where = " WHERE ";

        foreach ($array as $key => $val) {
            $key_string = $key_string . "`" . $key . "` = :" . $key . ", ";
        }
        $key_string = substr($key_string, 0, -2);

        foreach ($id as $key => $val) {
            $key_where = $key_where . "`" . $key . "` = :" . $key . " AND ";
        }
        $key_where = substr($key_where, 0, -4);

        $update_text = $update_text . " " . $key_string . $key_where;

        $statement = self::$dbi->prepare($update_text);
        return $statement->execute($array);
    }

    public function GetData()
    {
        $parameters = func_get_args();
        $argCount = count($parameters);
        if ($argCount > 0) {
            $this->queryParams = $parameters;
            $this->query = preg_replace_callback(
                $this->queryPattern, array($this, 'queryParseReplace'), $this->query);
        }
        $statement = self::$dbi->prepare($this->query);
        $statement->execute();
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function FirstRow()
    {
        $parameters = func_get_args();
        $argCount = count($parameters);
        if ($argCount > 0) {
            $this->queryParams = $parameters;
            $this->query = preg_replace_callback(
                $this->queryPattern, array($this, 'queryParseReplace'), $this->query);
        }
        $statement = self::$dbi->prepare($this->query);
        $statement->execute();
        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    public static function NOW()
    {
        return date('c');
    }
}