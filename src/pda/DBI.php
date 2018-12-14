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
use Memcached;
use PDO;
use PDOException;
use pukoframework\config\Config;
use pukoframework\Framework;
use pukoframework\log\LoggerInterface;
use pukoframework\log\LogLevel;
use pukoframework\peh\ThrowService;
use pukoframework\plugins\CurlRequest;

/**
 * Class DBI
 * @package pukoframework\pda
 */
class DBI implements LoggerInterface
{

    private static $dbi;

    protected $query;
    protected $queryParams;

    protected $dbType;
    protected $dbName;

    private $username;
    private $password;

    private $host;
    private $port;

    /**
     * @var bool
     */
    private $cache = false;

    private $queryPattern = '#@([0-9]+)#';

    /**
     * @param $connection array
     */
    protected function DBISet($connection)
    {
        $this->dbType = $connection['dbType'];
        $this->host = $connection['host'];
        $this->port = $connection['port'];
        $this->dbName = $connection['dbName'];
        $this->username = $connection['user'];
        $this->password = $connection['pass'];
        $this->cache = $connection['cache'];
    }

    /**
     * DBI constructor.
     * @param $query
     * @throws Exception
     */
    protected function __construct($query)
    {
        $exception_handler = new ThrowService('Database Service Error');
        $exception_handler->setLogger($this);

        set_exception_handler(array($exception_handler, 'ExceptionHandler'));
        set_error_handler(array($exception_handler, 'ErrorHandler'));

        $this->query = $query;
        if (is_object(self::$dbi)) {
            return;
        }

        $this->DBISet(Config::Data('database'));
        $pdoConnection = "$this->dbType:host=$this->host;port=$this->port;dbname=$this->dbName";

        try {
            self::$dbi = new PDO($pdoConnection, $this->username, $this->password);
            self::$dbi->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $ex) {
            throw new Exception("Connection failed: " . $ex->getMessage());
        }
    }

    /**
     * @param $query string
     * @return DBI
     * @throws Exception
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
        $insert_text = "INSERT INTO $this->query";
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
                    if (!$hasBinary) {
                        $blob = file_get_contents($values[$no]);
                    } else {
                        $blob = $values[$no];
                    }
                    $statement->bindValue(':' . $key, $blob, PDO::PARAM_LOB);
                } else {
                    $statement->bindValue(':' . $key, $values[$no]);
                }
            }
            if ($statement->execute()) {
                return self::$dbi->lastInsertId();
            } else {
                return false;
            }
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
        $del_text = "DELETE FROM $this->query WHERE ";
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
                    if (!$hasBinary) $blob = file_get_contents($val);
                    else $blob = $val;
                    $statement->bindValue(':' . $key, $blob, PDO::PARAM_LOB);
                } else $statement->bindValue(':' . $key, $val);
            }
            foreach ($id as $key => $val) {
                if (strpos($key, 'file') !== false) {
                    if (!$hasBinary) {
                        $blob = file_get_contents($val);
                    } else {
                        $blob = $val;
                    }
                    $statement->bindValue(':' . $key, $blob, PDO::PARAM_LOB);
                } else {
                    $statement->bindValue(':' . $key, $val);
                }
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
        if ($this->cache) {
            $cacheConfig = Config::Data('app')['cache'];
            $memcached = new Memcached();
            $memcached->addServer($cacheConfig['host'], $cacheConfig['port']);

            $keys = hash('ripemd160', $this->query);
            $item = $memcached->get($keys);

            if ($item) {
                return $item;
            } else {
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
                    //doing memcached storage
                    $memcached->set($keys, $statement->fetchAll(PDO::FETCH_ASSOC));
                    return $memcached->get($keys);

                } catch (PDOException $ex) {
                    throw new Exception('Database error: ' . $ex->getMessage());
                }
            }
        } else {
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
                return $statement->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $ex) {
                throw new Exception('Database error: ' . $ex->getMessage());
            }
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
            return $result;
        } catch (PDOException $ex) {
            throw new Exception('Database error: ' . $ex->getMessage());
        }
    }

    /**
     * @return mixed|null
     * @throws Exception
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
                return $statement->execute($parameters);
            } else {
                return $statement->execute();
            }
        } catch (PDOException $ex) {
            throw new Exception('Database error: ' . $ex->getMessage());
        }
    }

    /**
     * @param $name
     * @param $arrData
     * @return bool
     * @throws Exception
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
                return $statement->execute($arrData);
            } else {
                return $statement->execute();
            }
        } catch (PDOException $ex) {
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

    public function emergency($message, array $context = array())
    {
        //write custom handle code
    }

    /**
     * @param string $message
     * @param array $context
     * @throws Exception
     */
    public function alert($message, array $context = array())
    {
        $this->notifyError($message, $context);
    }

    public function critical($message, array $context = array())
    {
        //write custom handle code
    }

    /**
     * @param string $message
     * @param array $context
     * @throws \Exception
     */
    public function error($message, array $context = array())
    {
        $this->notifyError($message, $context);
    }

    public function warning($message, array $context = array())
    {
        //write custom handle code
    }

    public function notice($message, array $context = array())
    {
        //write custom handle code
    }

    public function info($message, array $context = array())
    {
        //write custom handle code
    }

    public function debug($message, array $context = array())
    {
        //write custom handle code
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @throws \Exception
     */
    public function log($level, $message, array $context = array())
    {
        switch ($level) {
            case LogLevel::ERROR:
                $this->error($message, $context);
                break;
            case LogLevel::ALERT:
                $this->alert($message, $context);
                break;
            case LogLevel::NOTICE:
                $this->notice($message, $context);
                break;
            case LogLevel::INFO:
                $this->info($message, $context);
                break;
            case LogLevel::WARNING:
                $this->warning($message, $context);
                break;
            case LogLevel::CRITICAL:
                $this->critical($message, $context);
                break;
            case LogLevel::DEBUG:
                $this->debug($message, $context);
                break;
            case LogLevel::EMERGENCY:
                $this->emergency($message, $context);
                break;
        }
    }

    /**
     * @param $message
     * @param array $context
     * @return mixed
     * @throws \Exception
     */
    private function notifyError($message, array $context = array())
    {
        foreach (Config::Data('app')['logs'] as $name => $configuration) {
            switch ($name) {
                case 'slack':
                    if (!$configuration['active']) {
                        return true;
                    }
                    $stack = '';
                    foreach ($context['Stacktrace'] as $k => $v) {
                        if ($k === 1) {
                            $stack .= print_r($v, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                        }
                    }
                    $messages = array(
                        'attachments' => array(
                            array(
                                'title' => $configuration['username'],
                                'title_link' => Framework::$factory->getRoot(),
                                'text' => 'An error raised from this part:',
                                'fallback' => sprintf('(%s) %s', $context['ErrorCode'], $message),
                                'pretext' => sprintf('(%s) %s', $context['ErrorCode'], $message),
                                'color' => '#764FA5',
                                'fields' => array(
                                    array(
                                        'title' => $context['File'],
                                        'value' => sprintf('Line number: %s', $context['LineNumber']),
                                        'short' => false
                                    ),
                                    array(
                                        'title' => 'Error Stack',
                                        'value' => $stack,
                                        'short' => false
                                    ),
                                ),
                            )
                        )
                    );
                    return CurlRequest::To($configuration['url'])->Method('POST')
                        ->Receive($messages, CurlRequest::JSON);
                    break;
                case 'hook':
                    if (!$configuration['active']) {
                        return true;
                    }
                    $messages = array(
                        'attachments' => array(
                            array(
                                'title' => $configuration['username'],
                                'title_link' => Framework::$factory->getRoot(),
                                'text' => 'An error raised from this part:',
                                'fallback' => sprintf('(%s) %s', $context['ErrorCode'], $message),
                                'pretext' => sprintf('(%s) %s', $context['ErrorCode'], $message),
                                'color' => '#764FA5',
                                'fields' => array(
                                    array(
                                        'title' => $context['File'],
                                        'value' => sprintf('Line number: %s', $context['LineNumber']),
                                        'short' => false
                                    )
                                ),
                            )
                        )
                    );
                    return CurlRequest::To($configuration['url'])->Method('POST')
                        ->Receive($messages, CurlRequest::JSON);
                    break;
                default:
                    return true;
                    break;

            }
        }
        return true;
    }
}