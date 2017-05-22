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
     * error code for service
     */
    const service = 1011;

    /**
     * error code for view
     */
    const view = 1012;

    /**
     * error code for value
     */
    const value = 1013;

    /**
     * @param $error
     */
    public function ExceptionHandler($error);

    /**
     * @param $error
     * @param $message
     * @param $file
     * @param $line
     */
    public function ErrorHandler($error, $message, $file, $line);
}
