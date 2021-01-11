<?php
class Micropayment_Mcpay_Block_Form_Paypal extends Micropayment_Mcpay_Block_Form_Default
{
	protected function _construct()
	{
    $pm = Mage::getModel('mcpay/paypal');
    $this->logoHTML = $pm->getFrontendLogo();
    parent::_construct();
	}

}
