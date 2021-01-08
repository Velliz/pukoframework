<?php


namespace pukoframework\plugins;

use Exception;
use pukoframework\pda\DBI;

/**
 * Class Paginations
 * @package pukoframework\plugins
 *
 * @copyright DV 2016 - 2021
 * @author Didit Velliz diditvelliz@gmail.com
 */
class Paginations
{

    public $database = 'primary';

    public $query;

    public $http_verb = 'GET';

    private $response = [
        'page' => 1,
        'length' => 0,
        'total' => 0,
        'anchor' => [],
        'error' => 'No data'
    ];

    private $db_engine = 'mysql';

    private $page = 1;

    private $length = 10;

    public function __construct($verb = 'GET')
    {
        $this->http_verb = $verb;

        //page length pointer
        $this->page = $_GET['page'];
        $this->length = $_GET['length'];
    }

    public function SetDBEngine($engine = 'mysql')
    {
        $this->db_engine = $engine;
    }

    public function SetLength($length = 10)
    {
        $this->length = $length;
    }

    public function SetQuery($query, $database = 'primary')
    {
        $this->database = $database;

        $sql = "SELECT ";
        if ($this->db_engine === 'sqlsrv') {
            $sql = "SELECT TOP {$this->length} ";
        }
        $sql .= "*";
        $sql .= " FROM ({$query}) ";
        $sql .= "TEMP ";

        $this->query = str_replace(';', '', $sql);
    }

    /**
     * @param callable|null $callbacks
     * @return array
     * @throws Exception
     */
    public function GetDataPaginations(callable $callbacks = null)
    {
        if (empty($_GET)) {
            return $this->response;
        }

        $count_sql = "SELECT ";
        $count_sql .= "COUNT(*) results ";
        $count_sql .= "FROM ({$this->query}) counter ";
        $data = DBI::Prepare($count_sql, $this->database)->FirstRow();

        $this->page = ($this->page > 1) ? ($this->page * $this->length) - $this->length : 1;
        $total = intval($data['results']);
        $total_page = floor($total / $this->page);
        $page_list = [];
        for ($i = 1; $i <= $total_page; $i++) {
            $page_list = $i;
        }

        $paginate_param = "";
        if ($this->db_engine === 'mysql') {
            $start = $this->page - 1;
            if ($start < 0) {
                $start = 0;
            }
            $paginate_param .= " LIMIT {$start},{$this->length}";
        }
        //todo: sqlsrv implementations

        $data = DBI::Prepare(($this->query . $paginate_param), $this->database)->GetData();

        $response = [
            'page' => $this->page,
            'length' => $this->length,
            'total' => $total,
            'anchor' => $page_list,
            'data' => $data,
        ];

        if ($callbacks !== null) {
            $response['data'] = $callbacks($data);
        }

        return $response;
    }


}
