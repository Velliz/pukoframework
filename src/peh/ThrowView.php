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
use pukoframework\pte\RenderEngine;
use pukoframework\Response;

class ThrowView extends Exception implements PukoException
{
    /**
     * @var RenderEngine
     */
    private $render;

    public $IsFatalError;
    public $systemHtml;

    public $message;

    /**
     * PukoException constructor.
     *
     * @param string $message
     * @param Response $response
     */
    public function __construct($message = '', Response $response)
    {
        parent::__construct($message, 10122, null);
        $this->message = $message;
        $this->systemHtml = ROOT . '/assets/system/';
        $this->render = new RenderEngine($response);
        $response->useMasterLayout = false;
    }

    /**
     * @param Exception $error
     * @return mixed|void
     */
    public function ExceptionHandler($error)
    {
        $emg['ErrorCount'] = $error;
        $emg['ErrorCode'] = $error->getCode();
        $emg['Message'] = $error->getMessage();
        $emg['File'] = $error->getFile();
        $emg['LineNumber'] = $error->getLine();
        $emg['Stacktrace'] = $error->getTrace();

        foreach ($emg['Stacktrace'] as $key => $val) {
            unset($val['args']);
            $emg['Stacktrace'][$key] = $val;
        }

        echo $this->render->PTEParser($this->systemHtml . '/exception.html', $emg);
        exit;
    }

    /**
     * @param $error
     * @param $message
     * @param $file
     * @param $line
     * @return mixed|void
     */
    public function ErrorHandler($error, $message, $file, $line)
    {
        $emg['ErrorCount'] = $error;
        $emg['ErrorCode'] = $this->getCode();
        $emg['Message'] = $message;
        $emg['File'] = $file;
        $emg['LineNumber'] = $line;
        $emg['Stacktrace'] = $this->getTrace();

        foreach ($emg['Stacktrace'] as $key => $val) {
            unset($val['args']);
            $emg['Stacktrace'][$key] = $val;
        }

        echo $this->render->PTEParser($this->systemHtml . '/error.html', $emg);
        exit;
    }
}
