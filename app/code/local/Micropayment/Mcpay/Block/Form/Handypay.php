<?php
class Micropayment_Mcpay_Block_Form_Handypay extends Micropayment_Mcpay_Block_Form_Default
{
	protected function _construct()
	{
    $pm = Mage::getModel('mcpay/handypay');
    $this->logoHTML = $pm->getFrontendLogo();
    parent::_construct();
	}

}
