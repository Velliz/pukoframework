<?php

namespace pukoframework\plugins;

use pukoframework\pda\DBI;
use pukoframework\Request;

/**
 * Class DataTables.
 *
 * @copyright DV 2016
 * @author Didit Velliz diditvelliz@gmail.com
 */
class DataTables
{

    const GET = 'GET';
    const POST = 'POST';
    const JSON_POST = 'JSON_POST';

    /**
     * @var string ase sql query string
     */
    public $sQuery;
    public $totalColumn;

    /**
     * @var array total columns for selector, order and sorting.
     */
    public $dtColumns = array();

    /**
     * @var string
     */
    private $httpVerb;

    private $draw = 1;
    private $orderByColumnIndex = 0;
    private $orderBy = null;
    private $orderType = null;

    private $start = 0;
    private $length = 1;

    private $where = array();
    private $recordsTotal = 0;
    private $recordsFiltered = 0;

    private $column = null;

    private $response = array(
        'draw' => 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'error' => 'No data from DataTable frontend',
    );

    /**
     * DataTables constructor.
     * @param string $httpVerb
     * @internal param string $query
     */
    public function __construct($httpVerb = 'GET')
    {
        $this->httpVerb = $httpVerb;
    }


    public function SetColumnSpec($columns)
    {
        if (count($columns) > 0) {
            if (!isset($this->totalColumn)) {
                $this->totalColumn = count($columns);
            }
            for ($i = 0; $i < count($columns); $i++) {
                $this->dtColumns[] = $columns[$i];
            }
        } else {
            $this->dtColumns = null;
            $this->dtColumns = $columns;
        }
    }

    public function SetQuery($query)
    {
        $sql = sprintf('SELECT ' . '*' . ' FROM (%s) onions ', $query);
        $this->sQuery = str_replace(';', '', $sql);
    }

    /**
     * @param callable|null $callback
     * @return array DataTables get ajax requests*
     * DataTables get ajax requests
     * @throws \Exception
     * @throws \pukoframework\peh\PukoException
     */
    public function GetDataTables(callable $callback = null)
    {
        $data = array();
        switch ($this->httpVerb) {
            case DataTables::GET:
                if (empty($_GET)) {
                    return $this->response;
                }

                $this->draw = $_GET['draw'];
                $this->orderByColumnIndex = $_GET['order'][0]['column'];
                $this->orderBy = $_GET['columns'][$this->orderByColumnIndex]['data'];
                $this->orderType = $_GET['order'][0]['dir'];

                $this->start = $_GET['start'];
                $this->length = $_GET['length'];

                $this->recordsTotal = count(DBI::Prepare($this->sQuery)->GetData());

                if (!empty($_GET['search']['value'])) {
                    for ($i = 0; $i < count($_GET['columns']); $i++) {
                        $this->column = $this->dtColumns[$_GET['columns'][$i]['data']];
                        $this->where[] = "$this->column like '%" . $_GET['search']['value'] . "%'";
                    }
                    $this->where = 'WHERE ' . implode(' OR ', $this->where);
                    $sql = sprintf('%s %s', $this->sQuery, $this->where);
                    $this->recordsFiltered = count(DBI::Prepare($sql)->GetData());
                    $sql = sprintf('%s %s ORDER BY %s %s limit %d,%d ',
                        $this->sQuery,
                        $this->where,
                        $this->dtColumns[$this->orderBy],
                        $this->orderType,
                        $this->start,
                        $this->length
                    );
                    $data = DBI::Prepare($sql)->GetData();
                } else {
                    $sql = sprintf('%s ORDER BY %s %s limit %d,%d ',
                        $this->sQuery,
                        $this->dtColumns[$this->orderBy],
                        $this->orderType,
                        $this->start,
                        $this->length
                    );
                    $data = DBI::Prepare($sql)->GetData();
                    $this->recordsFiltered = $this->recordsTotal;
                }

                break;
            case DataTables::POST:

                if (empty($_POST)) {
                    return $this->response;
                }

                $this->draw = $_POST['draw'];

                $this->orderByColumnIndex = $_POST['order'][0]['column'];
                $this->orderBy = $_POST['columns'][$this->orderByColumnIndex]['data'];
                $this->orderType = $_POST['order'][0]['dir'];

                $this->start = $_POST['start'];
                $this->length = $_POST['length'];

                $this->recordsTotal = count(DBI::Prepare($this->sQuery)->GetData());

                if (!empty($_POST['search']['value'])) {
                    for ($i = 0; $i < count($_POST['columns']); $i++) {
                        $this->column = $this->dtColumns[$_POST['columns'][$i]['data']];
                        $this->where[] = "$this->column like '%" . $_POST['search']['value'] . "%'";
                    }
                    $this->where = 'WHERE ' . implode(' OR ', $this->where);
                    $sql = sprintf('%s %s', $this->sQuery, $this->where);
                    $this->recordsFiltered = count(DBI::Prepare($sql)->GetData());
                    $sql = sprintf('%s %s ORDER BY %s %s limit %d,%d ',
                        $this->sQuery,
                        $this->where,
                        $this->dtColumns[$this->orderBy],
                        $this->orderType,
                        $this->start,
                        $this->length);
                    $data = DBI::Prepare($sql)->GetData();
                } else {
                    $sql = sprintf('%s ORDER BY %s %s limit %d,%d ',
                        $this->sQuery,
                        $this->dtColumns[$this->orderBy],
                        $this->orderType,
                        $this->start,
                        $this->length
                    );
                    $data = DBI::Prepare($sql)->GetData();
                    $this->recordsFiltered = $this->recordsTotal;
                }

                break;
            case DataTables::JSON_POST:

                $param = Request::JsonBody();

                if (empty($param)) {
                    return $this->response;
                }
                $this->draw = $param['draw'];

                $this->orderByColumnIndex = $param['order'][0]['column'];
                $this->orderBy = $param['columns'][$this->orderByColumnIndex]['data'];
                $this->orderType = $param['order'][0]['dir'];

                $this->start = $param['start'];
                $this->length = $param['length'];

                $this->recordsTotal = count(DBI::Prepare($this->sQuery)->GetData());

                if (!empty($param['search']['value'])) {
                    for ($i = 0; $i < count($param['columns']); $i++) {
                        $this->column = $this->dtColumns[$param['columns'][$i]['data']];
                        $this->where[] = "$this->column like '%" . $param['search']['value'] . "%'";
                    }
                    $this->where = 'WHERE ' . implode(' OR ', $this->where);
                    $sql = sprintf('%s %s', $this->sQuery, $this->where);
                    $this->recordsFiltered = count(DBI::Prepare($sql)->GetData());
                    $sql = sprintf('%s %s ORDER BY %s %s limit %d,%d ',
                        $this->sQuery,
                        $this->where,
                        $this->dtColumns[$this->orderBy],
                        $this->orderType,
                        $this->start,
                        $this->length
                    );
                    $data = DBI::Prepare($sql)->GetData();
                } else {
                    $sql = sprintf('%s ORDER BY %s %s limit %d,%d ',
                        $this->sQuery,
                        $this->dtColumns[$this->orderBy],
                        $this->orderType,
                        $this->start,
                        $this->length
                    );
                    $data = DBI::Prepare($sql)->GetData();
                    $this->recordsFiltered = $this->recordsTotal;
                }

                break;
            default:
                break;
        }

        $response = array(
            'draw' => intval($this->draw),
            'recordsTotal' => $this->recordsTotal,
            'recordsFiltered' => $this->recordsFiltered,
            'data' => array(),
        );

        if ($callback !== null) {
            $data = $callback($data);
        }

        $counter = 0;
        foreach ($data as $aRow) {
            $row = array();
            $counter++;
            for ($i = 0; $i < count($this->dtColumns); $i++) {
                $row[] = $aRow[$this->dtColumns[$i]];
            }
            $tData = count($this->dtColumns);
            for ($j = $tData; $j < $this->totalColumn; $j++) {
                $row[] = '-';
            }
            $response['data'][] = $row;
        }

        return $response;
    }
}
