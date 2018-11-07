<?php
/**
 * pukoframework.
 * MVC PHP Framework for quick and fast PHP Application Development.
 * Copyright (c) 2018, Didit Velliz
 *
 * @author Didit Velliz
 * @link https://github.com/velliz/pukoframework
 * @since Version 1.1.3
 */

namespace pukoframework\pdc;

use pte\Pte;
use pukoframework\Framework;
use pukoframework\Response;

/**
 * Class UnderConstruction
 * @package pukoframework\pdc
 */
class UnderConstruction implements Pdc
{

    var $key;

    var $value;

    /**
     * @param $clause
     * @param $command
     * @param $value
     */
    public function SetCommand($clause, $command, $value = null)
    {
        $this->key = $clause;
        $this->value = $command;
    }

    /**
     * @param Response $response
     * @return mixed
     * @throws \pte\exception\PteException
     */
    public function SetStrategy(Response &$response)
    {
        if ($this->value === 'true') {

            $render = new Pte(false, false);

            $render->SetValue(array());
            if ($response->useHtmlLayout) {
                $render->SetHtml(sprintf('%s/assets/system/construction.html', Framework::$factory->getRoot()));
                echo $render->Output(null, Pte::VIEW_HTML);
            } else {
                echo $render->Output(null, Pte::VIEW_JSON);
            }
            exit();
        }
        return true;
    }
}