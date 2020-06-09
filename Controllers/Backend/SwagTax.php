<?php

class Shopware_Controllers_Backend_SwagTax extends Shopware_Controllers_Backend_ExtJs
{
    public function saveAction()
    {
        echo "<pre>";
        print_r($this->Request()->getParam('taxMapping'));
        echo "</pre>";
        exit();
    }
}
