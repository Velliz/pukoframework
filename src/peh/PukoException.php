<?php
/**
 * pukoframework.
 * MVC PHP Framework for quick and fast PHP Application Development.
 * Copyright (c) 2016, Didit Velliz
 *
 * @author Didit Velliz
 * @link https://github.com/velliz/pukoframework
 * @since Version 1.0.2
 */

namespace pukoframework\peh;

/**
 * Interface PukoException
 * @package pukoframework\peh
 */
interface PukoException
{

    /**
     * @param $error
     * @return mixed
     */
    public function ExceptionHandler($error);

    /**
     * @param $error
     * @param $message
     * @param $file
     * @param $line
     * @return mixed
     */
    public function ErrorHandler($error, $message, $file, $line);
}
