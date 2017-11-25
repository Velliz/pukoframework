<?php

namespace pukoframework\plugins;

use pukoframework\pda\DBI;

/**
 * Class DataTables.
 *
 * @copyright DV 2016
 * @author Didit Velliz diditvelliz@gmail.com
 */
class DataTables extends DBI
{
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
     * DataTables constructor.
     * @param string $query
     */
    public function __construct($query = "")
    {
        parent::__construct($query);
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
        $this->sQuery = sprintf("SELECT * FROM (%s) tb ", $query);
    }

    /**
     * @param callable|null $callback
     * @return string
     *
     * DataTables get ajax requests
     */
    public function GetDataTables(callable $callback = null)
    {
        if (!empty($_GET)) {
            $draw = $_GET['draw'];

            $orderByColumnIndex = $_GET['order'][0]['column'];
            $orderBy = $_GET['columns'][$orderByColumnIndex]['data'];
            $orderType = $_GET['order'][0]['dir'];

            $start = $_GET['start'];
            $length = $_GET['length'];

            $where = array();

            $recordsTotal = count($this->Prepare($this->sQuery)->GetData());

            if (!empty($_GET['search']['value'])) {
                for ($i = 0; $i < count($_GET['columns']); $i++) {
                    $column = $this->dtColumns[$_GET['columns'][$i]['data']];
                    $where[] = "$column like '%" . $_GET['search']['value'] . "%'";
                }
                $where = 'WHERE ' . implode(' OR ', $where);
                $sql = sprintf('%s %s', $this->sQuery, $where);
                $recordsFiltered = count($this->Prepare($sql)->GetData());
                $sql = sprintf('%s %s ORDER BY %s %s limit %d,%d ', $this->sQuery, $where, $this->dtColumns[$orderBy], $orderType, $start, $length);
                $data = $this->Prepare($sql)->GetData();
            } else {
                $sql = sprintf('%s ORDER BY %s %s limit %d,%d ', $this->sQuery, $this->dtColumns[$orderBy], $orderType, $start, $length);
                $data = $this->Prepare($sql)->GetData();
                $recordsFiltered = $recordsTotal;
            }

            $response = array(
                'draw' => intval($draw),
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
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
        } else {
            $response = array(
                'draw' => 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'error' => 'NO POST Query from DataTable',
            );
        }

        return json_encode($response);
    }
}
