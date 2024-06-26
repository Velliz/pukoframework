<?php
/**
 * pukoframework.
 * MVC PHP Framework for quick and fast PHP Application Development.
 * Copyright (c) 2016, Didit Velliz
 *
 * @author Didit Velliz
 * @link https://github.com/velliz/pukoframework
 * @since Version 1.1.3
 */

namespace pukoframework\pda;

use Exception;
use ReflectionClass;
use ReflectionException;

/**
 * Class Model
 * @package pukoframework\pda
 */
class Model
{

    /**
     * @var null
     */
    private $_table = null;

    /**
     * @var null
     */
    private $_primary = null;

    /**
     * @var null
     */
    private $_foreign = null;

    /**
     * @var array
     */
    private $_specs = [];

    /**
     * @var string
     */
    public $_database = null;

    /**
     * Model constructor.
     * @param null $id
     *
     * @param null $database
     * @throws ReflectionException
     * @throws Exception
     */
    public function __construct($id = null, $database = null)
    {
        $this->_database = $database;

        $rc = new ReflectionClass($this);

        $head = $this->ModelParser($rc->getDocComment());
        foreach ($head as $key => $val) {
            if ($val['type'] === 'Table') {
                $this->_table = $val['name'];
            }
            if ($val['type'] === 'PrimaryKey') {
                $this->_primary = $val['name'];
            }
            if ($val['type'] === 'ForeignKey') {
                $this->_foreign = $val['name'];
            }
        }

        foreach (get_object_vars($this) as $key => $val) {
            if (!in_array($key, array('_table', '_specs', '_primary', '_foreign'))) {
                $parse = $this->ModelParser($rc->getProperty($key)->getDocComment());
                $this->_specs[$rc->getProperty($key)->name] = $parse;
            }
        }

        if ($id !== null) {
            $sql = sprintf("SELECT * FROM %s WHERE (%s = @1);", $this->_table, $this->_primary);
            $result = DBI::Prepare($sql, $this->_database)->FirstRow($id);

            if ($result !== null) {
                foreach ($result as $key => $val) {
                    foreach ($this->_specs as $k => $v) {
                        foreach ($v as $y) {
                            if ($y['name'] === $key) {
                                $this->{$k} = $val;
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @throws Exception
     */
    public function save()
    {
        $insert = [];
        foreach ($this->_specs as $key => $val) {
            foreach ($val as $v) {
                $skip = false;
                if (strpos($v['datatype'], 'auto_increment') !== false) {
                    $skip = true;
                }
                if (strpos($v['datatype'], 'identity') !== false) {
                    $skip = true;
                }
                if ($v['type'] === 'Column' && !$skip) {
                    $insert[$v['name']] = $this->{$key};
                }
            }
        }
        $last_id = DBI::Prepare($this->_table, $this->_database)->Save($insert, $this->_primary);
        $this->__construct($last_id, $this->_database);
    }

    /**
     * @throws Exception
     */
    public function modify()
    {
        $insert = [];
        foreach ($this->_specs as $key => $val) {
            foreach ($val as $v) {
                $skip = false;
                if (strpos($v['datatype'], 'auto_increment') !== false) {
                    $skip = true;
                }
                if (strpos($v['datatype'], 'identity') !== false) {
                    $skip = true;
                }
                if ($v['type'] === 'Column' && !$skip) {
                    $insert[$v['name']] = $this->{$key};
                }
            }
        }
        DBI::Prepare($this->_table, $this->_database)->Update([$this->_primary => $this->{$this->_primary}], $insert);
        $this->__construct($this->{$this->_primary}, $this->_database);
    }

    /**
     * @throws Exception
     */
    public function remove()
    {
        DBI::Prepare($this->_table, $this->_database)->Delete([$this->_primary => $this->{$this->_primary}]);
    }

    /**
     * @param $raw_docs
     * returned from controller
     *
     * @return array
     * @throws Exception
     */
    public function ModelParser($raw_docs): array
    {
        $data = array();

        preg_match_all('(#[ a-zA-Z0-9-:.+/_()]+)', $raw_docs, $result, PREG_PATTERN_ORDER);
        if (count($result[0]) > 0) {
            foreach ($result[0] as $key => $value) {

                $preg = explode(' ', $value);

                $clause = str_replace('#', '', $preg[0]);
                $command = $preg[1];

                $value = '';

                foreach ($preg as $k => $v) {
                    switch ($k) {
                        case 0:
                        case 1:
                            break;
                        default:
                            if ($k !== sizeof($preg) - 1) {
                                $value .= $v . ' ';
                            } else {
                                $value .= $v;
                            }
                            break;
                    }
                }
                $data[] = array(
                    'type' => $clause,
                    'name' => $command,
                    'datatype' => $value,
                );
            }
        }

        return $data;
    }

}
