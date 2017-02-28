<?php
/**
 * pukoframework.
 *
 * MVC PHP Framework for quick and fast PHP Application Development.
 *
 * This content is released under the Apache License Version 2.0, January 2004
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * Copyright (c) 2016, Didit Velliz
 *
 * @author    Didit Velliz
 *
 * @link    https://github.com/velliz/pukoframework
 * @since    Version 1.0.2
 */

namespace pukoframework\peh;

use Exception;
use pukoframework\Lifecycle;
use pukoframework\pte\RenderEngine;

class ThrowService extends Exception
{

    public $IsFatalError;

    /**
     * PukoException constructor.
     *
     * @param string $message
     */
    public function __construct($message = '')
    {
        parent::__construct($message, 10222, null);
    }

    /**
     * @param Exception $error
     *
     * @return mixed
     */
    public function ExceptionHandler($error)
    {
        $emg['Message'] = $error->getMessage();

        header('Author: Puko Framework');
        header('Content-Type: application/json');

        $data = array(
            'time' => microtime(true) - Lifecycle::$start,
            'status' => 'failed',
            'exception' => $emg
        );

        echo json_encode($data);
    }

    /**
     * @param $error
     * @param $message
     * @param $file
     * @param $line
     *
     * @return array
     */
    public function ErrorHandler($error, $message, $file, $line)
    {
        $emg['Error'] = $error;
        $emg['Message'] = $message;

        header('Author: Puko Framework');
        header('Content-Type: application/json');

        $data = array(
            'time' => microtime(true) - Lifecycle::$start,
            'status' => 'failed',
            'exception' => $emg
        );

        echo json_encode($data);
    }
}
