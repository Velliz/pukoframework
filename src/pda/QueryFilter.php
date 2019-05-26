<?php

namespace pukoframework\pda;

/**
 * Class QueryFilter
 * @package pukoframework\pda
 * Example usages:
 * TestModel::SearchData(
 *   'key1' => QueryFilter::and($value, QueryFilter::CONTAINS),
 *   'key2' => QueryFilter::or($value, QueryFilter::EQUAL),
 * );
 */
class QueryFilter
{

    const CONTAINS = 0;
    const EQUAL = 1;
    const NOT_EQUAL = 2;
    const INSIDE = 3;
    const NOT_INSIDE = 4;
    const BETWEEN = 5;
    const NOT_BETWEEN = 6;
    const IS_NULL = 7;
    const IS_NOT_NULL = 8;
    const IS_EMPTY = 9;
    const IS_NOT_EMPTY = 10;
    const NOT_CONTAINS = 11;

    /**
     * @param $column
     * @param $value
     * @param int $condition
     * @return string
     */
    public static function and($column, $value, $condition = QueryFilter::EQUAL)
    {
        $clause = 'AND';
        $q = new QueryFilter();
        switch ($condition) {
            case QueryFilter::CONTAINS:
                return $q->_contains($column, $value, $clause);
                break;
            case QueryFilter::NOT_CONTAINS:
                return $q->_notContains($column, $value, $clause);
                break;
            case QueryFilter::EQUAL:
                return $q->_equal($column, $value, $clause);
                break;
            case QueryFilter::NOT_EQUAL:
                return $q->_notEqual($column, $value, $clause);
                break;
            case QueryFilter::INSIDE:
                return $q->_inside($column, $value, $clause);
                break;
            case QueryFilter::NOT_INSIDE:
                return $q->_notInside($column, $value, $clause);
                break;
            case QueryFilter::BETWEEN:
                return $q->_between($column, $value, $clause);
                break;
            case QueryFilter::NOT_BETWEEN:
                return $q->_notBetween($column, $value, $clause);
                break;
            case QueryFilter::IS_NULL:
                return $q->_null($column, $clause);
                break;
            case QueryFilter::IS_NOT_NULL:
                return $q->_notNull($column, $clause);
                break;
            case QueryFilter::IS_EMPTY:
                return $q->_empty($column, $clause);
                break;
            case QueryFilter::IS_NOT_EMPTY:
                return $q->_notEmpty($column, $clause);
                break;
            default:
                return null;
                break;
        }
    }

    /**
     * @param $column
     * @param $value
     * @param int $condition
     * @return null|string
     */
    public static function or($column, $value, $condition = QueryFilter::EQUAL)
    {
        $clause = 'OR';
        $q = new QueryFilter();
        switch ($condition) {
            case QueryFilter::CONTAINS:
                return $q->_contains($column, $value, $clause);
                break;
            case QueryFilter::NOT_CONTAINS:
                return $q->_notContains($column, $value, $clause);
                break;
            case QueryFilter::EQUAL:
                return $q->_equal($column, $value, $clause);
                break;
            case QueryFilter::NOT_EQUAL:
                return $q->_notEqual($column, $value, $clause);
                break;
            case QueryFilter::INSIDE:
                return $q->_inside($column, $value, $clause);
                break;
            case QueryFilter::NOT_INSIDE:
                return $q->_notInside($column, $value, $clause);
                break;
            case QueryFilter::BETWEEN:
                return $q->_between($column, $value, $clause);
                break;
            case QueryFilter::NOT_BETWEEN:
                return $q->_notBetween($column, $value, $clause);
                break;
            case QueryFilter::IS_NULL:
                return $q->_null($column, $clause);
                break;
            case QueryFilter::IS_NOT_NULL:
                return $q->_notNull($column, $clause);
                break;
            case QueryFilter::IS_EMPTY:
                return $q->_empty($column, $clause);
                break;
            case QueryFilter::IS_NOT_EMPTY:
                return $q->_notEmpty($column, $clause);
                break;
            default:
                return null;
                break;
        }
    }

    /**
     * @param $column
     * @param $value
     * @param $clause
     * @return string
     */
    private function _contains($column, $value, $clause)
    {
        return " {$clause} {$column} LIKE '%{$value}%' ";
    }

    /**
     * @param $column
     * @param $value
     * @param $clause
     * @return string
     */
    private function _notContains($column, $value, $clause)
    {
        return " {$clause} {$column} NOT LIKE '%{$value}%' ";
    }

    /**
     * @param $column
     * @param $value
     * @param $clause
     * @return string
     */
    private function _equal($column, $value, $clause)
    {
        return " {$clause} ({$column} = '{$value}') ";
    }

    /**
     * @param $column
     * @param $value
     * @param $clause
     * @return string
     */
    private function _notEqual($column, $value, $clause)
    {
        return " {$clause} ({$column} <> '{$value}') ";
    }

    /**
     * @param $column
     * @param $value
     * @param $clause
     * @return string
     */
    private function _inside($column, $value = array(), $clause)
    {
        $value = implode(',', $value);
        return " {$clause} {$column} IN ({$value}) ";
    }

    /**
     * @param $column
     * @param $value
     * @param $clause
     * @return string
     */
    private function _notInside($column, $value = array(), $clause)
    {
        $value = implode(',', $value);
        return " {$clause} {$column} NOT IN ({$value}) ";
    }

    /**
     * @param $column
     * @param $value
     * @param $clause
     * @return null|string
     */
    private function _between($column, $value = array(), $clause)
    {
        if (count($value) < 2) {
            return null;
        }
        return " {$clause} {$column} BETWEEN {$value[0]} AND {$value[1]} ";
    }

    /**
     * @param $column
     * @param $value
     * @param $clause
     * @return null|string
     */
    private function _notBetween($column, $value = array(), $clause)
    {
        if (count($value) < 2) {
            return null;
        }
        return " {$clause} {$column} NOT BETWEEN {$value[0]} AND {$value[1]} ";
    }

    /**
     * @param $column
     * @param $clause
     * @return string
     */
    private function _null($column, $clause)
    {
        return " {$clause} {$column} IS NULL ";
    }

    /**
     * @param $column
     * @param $clause
     * @return string
     */
    private function _notNull($column, $clause)
    {
        return " {$clause} {$column} IS NOT NULL ";
    }

    /**
     * @param $column
     * @param $clause
     * @return string
     */
    private function _empty($column, $clause)
    {
        return " {$clause} {$column} = '' ";
    }

    /**
     * @param $column
     * @param $clause
     * @return string
     */
    private function _notEmpty($column, $clause)
    {
        return " {$clause} {$column} <> '' ";
    }

}