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

namespace pukoframework\middleware;

use pukoframework\log\LoggerInterface;
use pukoframework\log\LogLevel;
use pukoframework\log\LogTransforms;

/**
 * Class Controller
 * @package pukoframework\pte
 */
abstract class Controller implements LoggerInterface
{

    use LogTransforms;

    /**
     * @var array
     */
    public $const = array();

    /**
     * @var array
     */
    public $logger = array();

    /**
     * @return array
     */
    abstract public function BeforeInitialize();

    /**
     * @return mixed
     */
    abstract public function AfterInitialize();

    /**
     * @param $url
     * @param bool $permanent
     */
    public function RedirectTo($url, $permanent = false)
    {
        header('Location: ' . $url, true, $permanent ? 301 : 302);
        exit;
    }

    /**
     * @param int $length
     * @return string
     */
    public function GetRandomToken($length = 6)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @return string
     * get system date information
     */
    public function GetServerDateTime()
    {
        return date('c');
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function GetAppConstant($key)
    {
        if (isset($this->const[$key])) {
            return $this->const[$key];
        }
        return null;
    }

    public function emergency($message, array $context = array())
    {
        //write custom handle code
    }

    public function alert($message, array $context = array())
    {
        //write custom handle code
    }

    public function critical($message, array $context = array())
    {
        //write custom handle code
    }

    /**
     * @param string $message
     * @param array $context
     * @throws \Exception
     */
    public function error($message, array $context = array())
    {
        $this->notify($message, $context);
    }

    public function warning($message, array $context = array())
    {
        //write custom handle code
    }

    public function notice($message, array $context = array())
    {
        //write custom handle code
    }

    public function info($message, array $context = array())
    {
        //write custom handle code
    }

    public function debug($message, array $context = array())
    {
        //write custom handle code
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @throws \Exception
     */
    public function log($level, $message, array $context = array())
    {
        switch ($level) {
            case LogLevel::ERROR:
                $this->error($message, $context);
                break;
            case LogLevel::ALERT:
                $this->alert($message, $context);
                break;
            case LogLevel::NOTICE:
                $this->notice($message, $context);
                break;
            case LogLevel::INFO:
                $this->info($message, $context);
                break;
            case LogLevel::WARNING:
                $this->warning($message, $context);
                break;
            case LogLevel::CRITICAL:
                $this->critical($message, $context);
                break;
            case LogLevel::DEBUG:
                $this->debug($message, $context);
                break;
            case LogLevel::EMERGENCY:
                $this->emergency($message, $context);
                break;
        }
    }

}