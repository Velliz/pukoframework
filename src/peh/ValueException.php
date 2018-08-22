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
use pukoframework\auth\Session;

/**
 * Class ValueException
 * @package pukoframework\peh
 */
class ValueException extends Exception
{

    /**
     * @var array
     */
    private $validation = array();

    /**
     * ValueException constructor.
     * @param string $message
     * @param array $validate
     */
    public function __construct($message = '', $validate = array())
    {
        parent::__construct($message, PukoException::value);
        $this->validation = $validate;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function Prepare($key, $value)
    {
        $this->validation['#' . $key] = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getValidations()
    {
        return $this->validation;
    }

    /**
     * @param $arrayData
     * @param string $message
     *
     * @throws Exception
     */
    public function Throws($arrayData, $message = '')
    {
        Session::GenerateSecureToken();

        if (count($this->validation) > 0) {
            $this->validation = array_merge($this->validation, $arrayData);

            $this->validation['Exception'] = false;
            $this->validation['ExceptionMessage'] = $message;

            throw new self($message, $this->validation);
        }
    }
}
