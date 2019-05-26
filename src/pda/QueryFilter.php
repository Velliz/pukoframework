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

    const CONTAINS = " ? LIKE '%?%' ";

    const EQUAL = " (? = '?') ";

    const NOT_EQUAL = " (? <> '?') ";

    const INSIDE = " ? IN ('?') ";

    const NOT_INSIDE = " ? NOT IN ('?') ";

    const BETWEEN = " ? BETWEEN '?' AND '?' ";

    const NOT_BETWEEN = " ? NOT BETWEEN ?' AND '?' ";

    const IS_NULL = " ? IS NULL ";

    const IS_NOT_NULL = " ? IS NOT NULL ";

    const IS_EMPTY = " (? = '') ";

    const IS_NOT_EMPTY = " (? <> '') ";

    public static function and($value, $condition = QueryFilter::EQUAL)
    {

    }

    public static function or($value, $condition = QueryFilter::EQUAL)
    {

    }



}