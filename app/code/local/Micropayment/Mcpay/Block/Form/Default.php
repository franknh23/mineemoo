<?php
class Micropayment_Mcpay_Block_Form_Default extends Micropayment_Mcpay_Block_Form
{
  var $logoHTML; // set in paymethod block

	protected function _construct()
	{
    parent::_construct();
    $this->setTemplate('micropayment/form/default.phtml')->setMethodLabelAfterHtml($this->logoHTML);
	}

	/**
	 * Retrieve payment configuration object
	 *
	 * @return Mage_Payment_Model_Config
	 */
	protected function _getConfig()
	{
    return Mage::getSingleton('payment/config');
	}

  function getPoweredBy()
  {
    $pm = Mage::getModel('mcpay/standard');
    return $pm->getPoweredBy();
  }

}
