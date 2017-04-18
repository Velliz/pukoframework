<?php
namespace pukoframework\pda;

interface Crud
{
    public function Create($data, ModelCallbacks $options);

    public function Read(ModelCallbacks $options);

    public function Update($id, $data, ModelCallbacks $options);

    public function Delete($id, ModelCallbacks $options);
}