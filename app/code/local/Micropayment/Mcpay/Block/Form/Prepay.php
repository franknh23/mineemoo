<?php
class Micropayment_Mcpay_Block_Form_Prepay extends Micropayment_Mcpay_Block_Form_Default
{
	protected function _construct()
	{
    $pm = Mage::getModel('mcpay/prepay');
    $this->logoHTML = $pm->getFrontendLogo();
    parent::_construct();
	}

}
