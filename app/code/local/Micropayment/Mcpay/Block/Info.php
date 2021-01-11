<?php
/**
 * Base payment iformation block
 *
 */
class Micropayment_Mcpay_Block_Info extends Mage_Payment_Block_Info
{

	/**
	 * Retrieve info model
	 *
	 * @return Mage_Payment_Model_Info
	 */
	public function getInfo()
	{
    $info = $this->getData('info');
    if (!($info instanceof Mage_Payment_Model_Info)) {
    	Mage::throwException($this->__('Can not retrieve payment info model object.'));
    }
    return $info;
	}

	/**
	 * Retrieve payment method model
	 *
	 * @return Mage_Payment_Model_Method_Abstract
	 */
	public function getMethod()
	{
    return $this->getInfo()->getMethodInstance();
	}

	/**
	 * Retrieve order model
	 *
	 * @return Mage_Sales_Model_Order
	 */
	public function getOrder()
	{
	  return Mage::registry('sales_order');
	}

}
