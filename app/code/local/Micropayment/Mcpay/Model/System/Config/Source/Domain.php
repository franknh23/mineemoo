<?php

class Micropayment_Mcpay_Model_System_Config_Source_Domain
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'micropayment.de', 'label'=>Mage::helper('mcpay')->__('.DE')),
            array('value'=>'micropayment.ch', 'label'=>Mage::helper('mcpay')->__('.CH')),
        );
    }
}
