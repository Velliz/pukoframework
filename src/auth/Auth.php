<?php
namespace pukoframework\auth;

interface Auth
{
    public function Login();

    public function Logout();

    public function GetLoginData($id);
}