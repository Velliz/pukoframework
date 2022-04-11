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

    const KB = 1024;
    const MB = 1048576;
    const GB = 1073741824;

    /**
     * @var object|null
     */
    protected $files = null;

    /**
     * Files constructor.
     * @param $files
     */
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

    /**
     * @return bool
     */
    public function isError()
    {
        return (int)$this->files['error'] === 0 ? false : true;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->files['size'];
    }

    /**
     * @param float|int $expectations
     * @return bool
     * Default expectation is lower than 10MB
     */
    public function isSizeSmallerThan(float $expectations = 1048576)
    {
        return ($this->getSize() < $expectations);
    }

    /**
     * @return false|string
     */
    public function getFile()
    {
        return file_get_contents($this->getTmpName());
    }

}
