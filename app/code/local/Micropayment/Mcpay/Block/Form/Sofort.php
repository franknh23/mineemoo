<?php
class Micropayment_Mcpay_Block_Form_Sofort extends Micropayment_Mcpay_Block_Form_Default
{
	protected function _construct()
	{
    $pm = Mage::getModel('mcpay/sofort');
    $this->logoHTML = $pm->getFrontendLogo();
    parent::_construct();
	}

}
