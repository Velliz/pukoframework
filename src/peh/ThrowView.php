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
use pte\exception\PteException as PteExceptionAlias;
use pte\exception\PteException;
use pte\Pte;
use pukoframework\Framework;
use pukoframework\log\LoggerAwareInterface;
use pukoframework\log\LoggerInterface;
use pukoframework\log\LogLevel;
use pukoframework\Response;

/**
 * Class ThrowView
 * @package pukoframework\peh
 */
class ThrowView extends Exception implements
    PukoException, CustomRender, LoggerAwareInterface
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

    /**
     * @var string
     */
    var $fn;

    /**
     * @var string
     */
    var $param;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * PukoException constructor.
     *
     * @param string $message
     * @param Response $response
     */
    public function __construct($message, Response $response)
    {
        parent::__construct($message, PukoException::view);

        $this->system_html = Framework::$factory->getRoot() . '/assets/system/';
        $this->message = $message;

        $response->useMasterLayout = false;
        $this->render = new Pte(false);
    }

    /**
     * @param Exception $error
     * @return mixed|void
     * @throws PteException
     */
    public function ExceptionHandler($error)
    {
        $emg['ErrorCount'] = $error;
        $emg['ErrorCode'] = PukoException::value;
        $emg['Message'] = $error->getMessage();
        $emg['File'] = $error->getFile();
        $emg['LineNumber'] = $error->getLine();
        $emg['Stacktrace'] = $error->getTrace();

        foreach ($emg['Stacktrace'] as $key => $val) {
            unset($val['args']);
            $emg['Stacktrace'][$key] = $val;
        }

        $this->logger->log(LogLevel::ALERT, $error->getMessage(), $emg);

        if (Framework::$factory->getEnvironment() === 'PROD') {
            unset($emg['File']);
            unset($emg['LineNumber']);
            unset($emg['Stacktrace']);
        }

        $this->render->SetHtml($this->system_html . '/exception.html');
        $this->render->SetValue($emg);
        die($this->render->Output($this));
    }

    /**
     * @param $error
     * @param $message
     * @param $file
     * @param $line
     * @return mixed|void
     * @throws PteExceptionAlias
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

        $this->logger->log(LogLevel::ERROR, $message, $emg);

        if (Framework::$factory->getEnvironment() === 'PROD') {
            unset($emg['File']);
            unset($emg['LineNumber']);
            unset($emg['Stacktrace']);
        }

        $this->render->SetHtml($this->system_html . '/error.html');
        $this->render->SetValue($emg);
        die($this->render->Output($this));
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
     * @param null $data
     * @param string $template
     * @param bool $templateBinary
     * @return string
     */
    public function Parse($data = null, $template = '', $templateBinary = false)
    {
        if ($this->fn === 'url') {
            return Framework::$factory->getBase() . $this->param;
        }
        return '';
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
