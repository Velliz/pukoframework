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
 * @author	Didit Velliz
 *
 * @link	https://github.com/velliz/pukoframework
 * @since	Version 1.0.2
 */

namespace pukoframework\peh;

use Exception;
use pukoframework\auth\Session;

class ValueException extends Exception
{
    private $validation = array();

    public function __construct($message = '', $validate = array())
    {
        parent::__construct($message, 10701, null);
        $this->validation = $validate;
    }

    public function Prepare($key, $value)
    {
        $this->validation['#'.$key] = $value;
    }

    public function getValidations()
    {
        $error = $this->validation;

        return $error;
    }

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
