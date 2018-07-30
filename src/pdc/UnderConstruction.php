<?php

namespace pukoframework\pdc;

use pte\Pte;
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

            $render = new Pte(false);
            if ($response->useMasterLayout) {
                $render->SetMaster($response->htmlMaster);
            }

            $render->SetValue(array());
            if ($response->useHtmlLayout) {
                $render->SetHtml(sprintf('%s/assets/system/construction.html', ROOT));
                echo $render->Output($this, Pte::VIEW_HTML);
            } else {
                echo $render->Output($this, Pte::VIEW_JSON);
            }
            exit();
        }
        return true;
    }
}