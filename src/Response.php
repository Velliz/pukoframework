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

use pte\PteCache;

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
     * @var PteCache
     */
    public $cacheDriver = null;

    /**
     * @var bool
     */
    public $clearValues = true;

    /**
     * @var bool
     */
    public $clearBlocks = false;

    /**
     * @var bool
     */
    public $clearComments = true;

    /**
     * @var bool
     */
    public $displayException = true;

}