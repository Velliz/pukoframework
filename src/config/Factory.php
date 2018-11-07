<?php
/**
 * pukoframework.
 * MVC PHP Framework for quick and fast PHP Application Development.
 * Copyright (c) 2016, Didit Velliz
 *
 * @author Didit Velliz
 * @link https://github.com/velliz/pukoframework
 * @since Version 1.1.5
 */

namespace pukoframework\config;

/**
 * Class Factory
 * @package pukoframework\config
 */
class Factory
{

    private $base = '';

    private $root = '';

    private $start = '';

    public function __construct($config = array())
    {
        $this->base = $config['base'];
        $this->root = $config['root'];
        $this->start = $config['start'];
    }

    /**
     * @return string
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * @return string
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @return string
     */
    public function getStart()
    {
        return $this->start;
    }


}