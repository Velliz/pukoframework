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
use pte\CustomRender;
use pte\Pte;
use pukoframework\Response;

/**
 * Class ThrowView
 * @package pukoframework\peh
 */
class ThrowView extends Exception implements PukoException, CustomRender
{
    /**
     * @var Pte
     */
    private $render;

    /**
     * @var string
     */
    public $system_html;

    /**
     * @var string
     */
    public $message;

    var $fn;
    var $param;

    /**
     * PukoException constructor.
     *
     * @param string $message
     * @param Response $response
     */
    public function __construct($message, Response $response)
    {
        parent::__construct($message, PukoException::view);

        $this->system_html = ROOT . '/assets/system/';
        $this->message = $message;

        $response->useMasterLayout = false;
        $this->render = new Pte(false);
    }

    /**
     * @param Exception $error
     * @return mixed|void
     * @throws \pte\exception\PteException
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

        $this->render->SetHtml($this->system_html . '/exception.html');
        $this->render->SetValue($emg);
        echo $this->render->Output($this);
        die();
    }

    /**
     * @param $error
     * @param $message
     * @param $file
     * @param $line
     * @return mixed|void
     * @throws \pte\exception\PteException
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

        $this->render->SetHtml($this->system_html . '/error.html');
        $this->render->SetValue($emg);
        echo $this->render->Output($this);
        die();
    }

    /**
     * @param $fnName
     * @param $paramArray
     */
    public function RegisterFunction($fnName, $paramArray)
    {
        $this->fn = $fnName;
        $this->param = $paramArray;
    }

    /**
     * @return string
     */
    public function Parse()
    {
        if ($this->fn === 'url') {
            return BASE_URL . $this->param;
        }
        return '';
    }
}
