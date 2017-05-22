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

use Exception;

/**
 * Class ThrowService
 * @package pukoframework\peh
 */
class ThrowService extends Exception implements PukoException
{

    /**
     * PukoException constructor.
     * @param string $message
     */
    public function __construct($message)
    {
        parent::__construct($message, PukoException::service);
    }

    /**
     * @param Exception $error
     */
    public function ExceptionHandler($error)
    {
        $emg['Message'] = $error->getMessage();

        header('Author: Puko Framework');
        header('Content-Type: application/json');

        $data = array(
            'time' => microtime(true) - START,
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
     */
    public function ErrorHandler($error, $message, $file, $line)
    {
        $emg['Error'] = $error;
        $emg['Message'] = $message;

        header('Author: Puko Framework');
        header('Content-Type: application/json');

        $data = array(
            'time' => microtime(true) - START,
            'status' => 'failed',
            'exception' => $emg
        );

        echo json_encode($data);
    }
}
