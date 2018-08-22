<?php
/**
 * Created by PhpStorm.
 * User: didit
 * Date: 8/22/2018
 * Time: 10:56 PM
 */

namespace pukoframework\log;

/**
 * Interface LoggerAwareInterface
 * @package pukoframework\log
 */
interface LoggerAwareInterface
{
    /**
     * @param LoggerInterface $logger
     * @return mixed
     */
    public function setLogger(LoggerInterface $logger);

}