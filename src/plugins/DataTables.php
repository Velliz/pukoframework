<?php

namespace pukoframework\plugins;

use Exception;
use pukoframework\pda\DBI;
use pukoframework\Request;

/**
 * Class DataTables.
 * @package pukoframework\plugins
 *
 * @copyright DV 2016 - 2019
 * @author Didit Velliz diditvelliz@gmail.com
 */
class DataTables
{

    const GET = 'GET';
    const POST = 'POST';
    const JSON_POST = 'JSON_POST';

    /**
     * @var string database
     */
    public $database = 'primary';

    /**
     * @var string
     * sql query
     */
    public $query;

    public $raw_query;

    /**
     * @var int
     */
    public $total_columns;

    /**
     * @var array
     */
    private $column_names = [];

    public $http_verb = 'POST';

    private $draw = 1;

    private $order_index = 0;
    private $order_dir = 'asc';

    private $start = 0;
    private $length = 0;

    private $search_terms = null;

    private $search_array = [];

    private $records_total = 0;
    private $records_filtered = 0;

    private $response = [
        'draw' => 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'error' => 'No data',
    ];

    private $db_engine = 'mysql';

    /**
     * DataTables constructor.
     * @param string $verb
     * @internal param string $query
     */
    public function __construct($verb = 'POST')
    {
        //draw pointer
        $this->draw = Request::Post('draw', 0);

        //order pointer
        $this->order_index = $_POST['order'][0]['column'];
        $this->order_dir = $_POST['order'][0]['dir'];

        //page length pointer
        $this->start = $_POST['start'];
        $this->length = $_POST['length'];

        //search terms
        if (!empty($_POST['search']['value'])) {
            $this->search_terms = $_POST['search']['value'];
        }

        // post, put, get etc...
        $this->http_verb = $verb;
    }

    public function SetDBEngine($engine = 'mysql')
    {
        $this->db_engine = $engine;
    }

    /**
     * @param $selected_columns
     * @throws Exception
     */
    public function SetColumnSpec($selected_columns)
    {
        $total = count($selected_columns);
        if ($total === 0) {
            throw new Exception('You must provide column to displayed!');
        }
        for ($i = 0; $i < $total; $i++) {
            $this->column_names[] = $selected_columns[$i];
        }
        $this->total_columns = $total;
    }

    /**
     * @param $query
     * @param string $database
     */
    public function SetQuery($query, $database = 'primary')
    {
        $this->database = $database;

        $this->raw_query = str_replace(';', '', $query);

        $sql = "SELECT ";
        if($this->db_engine === 'sqlsrv') {
            $sql = "SELECT TOP {$this->length} ";
        }
        $sql .= "*";
        $sql .= " FROM ({$query}) ";
        $sql .= "TEMP ";
        $this->query = str_replace(';', '', $sql);
    }

    /**
     * @param callable|null $callback
     * @return array DataTables get ajax requests
     * DataTables POST ajax requests
     * @throws Exception
     */
    public function GetDataTables(callable $callback = null)
    {
        if (empty($_POST)) {
            return $this->response;
        }

        $count_sql = "SELECT ";
        $count_sql .= "COUNT(*) results ";
        $count_sql .= "FROM ({$this->raw_query}) counter ";
        $data = DBI::Prepare($count_sql, $this->database)->FirstRow();
        $this->records_total = intval($data['results']);
        $this->records_filtered = intval($data['results']);

        if ($this->search_terms !== null) {
            for ($i = 0; $i < count($this->column_names); $i++) {
                $this->search_array[] = "{$this->column_names[$i]} LIKE '%{$this->search_terms}%'";
            }
        }

        $search_param = "";
        if (count($this->search_array) > 0) {
            $likes = implode(" OR ", $this->search_array);
            $search_param .= " WHERE {$likes} ";

            $filtered = DBI::Prepare(($this->query . $search_param), $this->database)->GetData();
            $this->records_filtered = count($filtered);
        }

        $order = strtoupper($this->order_dir);
        $search_param .= " ORDER BY {$this->column_names[$this->order_index]} {$order}";
        if($this->db_engine === 'mysql') {
            $search_param .= " LIMIT {$this->start},{$this->length}";
        }
        //todo: sqlsrv implementations

        $data = DBI::Prepare(($this->query . $search_param), $this->database)->GetData();

        $response = [
            'draw' => intval($this->draw),
            'recordsTotal' => $this->records_total,
            'recordsFiltered' => $this->records_filtered,
            'data' => [],
        ];

        if ($callback !== null) {
            $data = $callback($data);
        }

        //make the visible column is only from column specs
        foreach ($data as $a_row) {
            $row = [];
            for ($i = 0; $i < $this->total_columns; $i++) {
                $row[] = $a_row[$this->column_names[$i]];
            }
            $response['data'][] = $row;
        }

        return $response;
    }
}
