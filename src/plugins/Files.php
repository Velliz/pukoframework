<?php


namespace pukoframework\plugins;

/**
 * Class Files
 * @package pukoframework\plugins
 *
 * @copyright DV 2020
 * @author Didit Velliz diditvelliz@gmail.com
 */
class Files
{

    /**
     * @var $_FILES|null
     */
    protected $files = null;

    public function __construct($files)
    {
        $this->files = $files;
    }

    public function getName()
    {
        return $this->files['name'];
    }

    public function getType()
    {
        return $this->files['type'];
    }

    public function getTmpName()
    {
        return $this->files['tmp_name'];
    }

    public function isError()
    {
        return $this->files['error'];
    }

    public function getSize()
    {
        return $this->files['size'];
    }

}