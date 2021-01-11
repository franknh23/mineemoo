<?php
class Micropayment_Mcpay_Block_Form_Paydirekt extends Micropayment_Mcpay_Block_Form_Default
{
	protected function _construct()
	{
    $pm = Mage::getModel('mcpay/paydirekt');
    $this->logoHTML = $pm->getFrontendLogo();
    parent::_construct();
	}

}
