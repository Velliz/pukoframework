<?php

namespace pukoframework\pda;

interface ModelCallbacks
{
    public function SetQuery($customQuery);

    public function SetTable($tableName);
}

interface Crud
{
    public function Create($data, ModelCallbacks $options);

    public function Read(ModelCallbacks $options);

    public function Update($id, $data, ModelCallbacks $options);

    public function Delete($id, ModelCallbacks $options);
}

/**
 * Class Model.
 */
class Model implements Crud, ModelCallbacks
{
    public $table;
    public $query;

    /**
     * @var DBI
     */
    public $db;

    public function __construct($table)
    {
        $this->db = DBI::Prepare($table);
    }

    public function Create($data, ModelCallbacks $options = null)
    {
        return $this->db->Save($data);
    }

    public function Read(ModelCallbacks $options = null)
    {
        return DBI::Prepare($this->query)->GetData();
    }

    public function Update($id, $data, ModelCallbacks $options = null)
    {
        return DBI::Prepare($this->table)->Update($id, $data);
    }

    public function Delete($data, ModelCallbacks $options = null)
    {
        return DBI::Prepare($this->table)->Delete($data);
    }

    public function SetQuery($customQuery)
    {
        $this->query = $customQuery;
    }

    public function SetTable($tableName)
    {
        $this->table = $tableName;
    }
}
