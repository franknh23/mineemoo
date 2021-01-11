<?php
class Micropayment_Mcpay_Block_Form_Paysafecard extends Micropayment_Mcpay_Block_Form_Default
{
	protected function _construct()
	{
    $pm = Mage::getModel('mcpay/paysafecard');
    $this->logoHTML = $pm->getFrontendLogo();
    parent::_construct();
	}

}
