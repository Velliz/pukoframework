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
use pukoframework\Framework;
use pukoframework\log\LoggerAwareInterface;
use pukoframework\log\LoggerInterface;
use pukoframework\log\LogLevel;

/**
 * Class ThrowService
 * @package pukoframework\peh
 */
class ThrowService extends Exception
    implements PukoException, LoggerAwareInterface
{

    /**
     * @var string
     */
    var $message;

    /**
     * @var LoggerInterface
     */
    var $logger;

    /**
     * PukoException constructor.
     * @param string $message
     */
    public function __construct($message)
    {
        $this->message = $message;
        parent::__construct($message, PukoException::service);
    }

    /**
     * @param Exception $error
     */
    public function ExceptionHandler($error)
    {
        $emg['ErrorCount'] = $error;
        $emg['ErrorCode'] = PukoException::value;
        $emg['Message'] = $error->getMessage();
        $emg['File'] = $error->getFile();
        $emg['LineNumber'] = $error->getLine();
        $emg['Stacktrace'] = $error->getTrace();

        http_response_code(403);
        header('Author: Puko Framework');
        header('Content-Type: application/json');

        $exception = array(
            'count' => $emg['ErrorCount'],
            'error_code' => $emg['ErrorCode'],
            'message' => $emg['Message'],
            'Message' => $emg['Message'],
            'File' => $emg['File'],
            'LineNumber' => $emg['LineNumber'],
            'Stacktrace' => $emg['Stacktrace'],
        );

        $this->logger->log(LogLevel::ALERT, $error->getMessage(), $emg);

        if (Framework::$factory->getEnvironment() === 'PROD') {
            unset($exception['File']);
            unset($exception['LineNumber']);
            unset($exception['Stacktrace']);
        }

        $data = array(
            'status' => 'error',
            'exception' => $exception
        );

        die(json_encode($data));
    }

    /**
     * @param $error
     * @param $message
     * @param $file
     * @param $line
     */
    public function ErrorHandler($error, $message, $file, $line)
    {
        $emg['ErrorCount'] = $error;
        $emg['ErrorCode'] = $this->getCode();
        $emg['Message'] = $message;
        $emg['File'] = $file;
        $emg['LineNumber'] = $line;
        $emg['Stacktrace'] = $this->getTrace();

        http_response_code(500);
        header('Author: Puko Framework');
        header('Content-Type: application/json');

        $exception = array(
            'count' => $emg['ErrorCount'],
            'error_code' => $emg['ErrorCode'],
            'message' => $emg['Message'],
            'Message' => $emg['Message'],
            'File' => $emg['File'],
            'LineNumber' => $emg['LineNumber'],
            'Stacktrace' => $emg['Stacktrace'],
        );

        $this->logger->log(LogLevel::ERROR, $message, $emg);

        if (Framework::$factory->getEnvironment() === 'PROD') {
            unset($exception['File']);
            unset($exception['LineNumber']);
            unset($exception['Stacktrace']);
        }

        $data = array(
            'status' => 'failed',
            'exception' => $exception
        );

        die(json_encode($data));
    }

    /**
     * @param LoggerInterface $logger
     * @return mixed
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this->logger;
    }
}
