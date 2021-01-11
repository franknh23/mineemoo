<?php

class Micropayment_Mcpay_Model_System_Config_Source_Refundmode
{
    public function toOptionArray()
    {
        return array(
          array('value'=>'CREDITMEMO', 'label'=>Mage::helper('mcpay')->__('Generate creditmemo for invoice')),
          array('value'=>'DIRECT', 'label'=>Mage::helper('mcpay')->__('Book amounts directly to order'))
        );
    }
}
