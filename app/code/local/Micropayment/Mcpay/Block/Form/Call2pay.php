<?php
class Micropayment_Mcpay_Block_Form_Call2pay extends Micropayment_Mcpay_Block_Form_Default
{
	protected function _construct()
	{
    $pm = Mage::getModel('mcpay/call2pay');
    $this->logoHTML = $pm->getFrontendLogo();
    parent::_construct();
	}

}
