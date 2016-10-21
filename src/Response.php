<?php
namespace pukoframework;

use Exception;

class Response
{
    var $responseType;

    /**
     * @param Exception $error
     * @return mixed
     */
    public function ExceptionHandler($error)
    {
        $emg['Exception'] = false;
        $emg['token'] = $_COOKIE['token'];
        $emg['ExceptionMessage'] = $error->getMessage();
        return $emg;
    }
}