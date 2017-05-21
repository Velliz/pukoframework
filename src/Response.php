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


class Response
{
    public $sourceFile;

    public $htmlMaster = false;

    public $useMasterLayout = true;
    public $useHtmlLayout = true;
    public $clearOutput = false;
    public $displayException = true;
}