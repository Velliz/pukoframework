<?php
/**
 * pukoframework.
 * MVC PHP Framework for quick and fast PHP Application Development.
 * Copyright (c) 2016, Didit Velliz
 *
 * @author Didit Velliz
 * @link https://github.com/velliz/pukoframework
 * @since Version 1.0.3
 */

namespace pukoframework;

/**
 * Class Response
 * @package pukoframework
 */
class Response
{
    /**
     * @var string
     */
    public $sourceFile;

    /**
     * @var bool
     */
    public $htmlMaster = false;

    /**
     * @var bool
     */
    public $useMasterLayout = true;

    /**
     * @var bool
     */
    public $useHtmlLayout = true;

    /**
     * @var bool
     */
    public $clearOutput = false;

    /**
     * @var bool
     */
    public $displayException = true;

}