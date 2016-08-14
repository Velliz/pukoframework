<?php
namespace pukoframework\auth;

interface Auth
{
    public function Login($username, $password);

    public function Logout();

    public function GetLoginData($id);
}