<?php

class Micropayment_Mcpay_Model_System_Config_Source_Invoicemode
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'AUTOINVOICE', 'label'=>Mage::helper('mcpay')->__('Generate automatic invoice')),
            array('value'=>'NOINVOICE', 'label'=>Mage::helper('mcpay')->__('Generate manual invoice'))
        );
    }
}
