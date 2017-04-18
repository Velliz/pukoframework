<?php

namespace pukoframework\pda;

class Model implements Crud
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
}
