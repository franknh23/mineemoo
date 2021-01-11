<?php

class Micropayment_Mcpay_Model_System_Config_Source_Debugmode
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'0', 'label'=>Mage::helper('mcpay')->__('No')),
            array('value'=>'1', 'label'=>Mage::helper('mcpay')->__('Yes')),
        );
    }
}
