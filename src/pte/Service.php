<?php
namespace pukoframework\pte;

use pukoframework\auth\Auth;
use pukoframework\peh\ThrowService;

class Service extends Controller implements Auth
{

    public function __construct()
    {
        $exceptionHandler = new ThrowService();
        set_exception_handler(array($exceptionHandler, 'ExceptionHandler'));
        set_error_handler(array($exceptionHandler, 'ErrorHandler'));
    }

    public function RedirectTo($url, $permanent = false)
    {
        header('Location: ' . $url, true, $permanent ? 301 : 302);
        exit();
    }

    public function Login($username, $password)
    {
    }

    public function Logout()
    {
    }

    public function GetLoginData($id)
    {
    }

    /**
     * @return array|string|bool|null
     */
    public function OnInitialize()
    {
        return null;
    }
}