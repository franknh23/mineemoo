<?php
/**
 * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2018 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.sellxed.com/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.sellxed.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 *
 * @category	Customweb
 * @package		Customweb_SaferpayCw
 * 
 */

class Customweb_SaferpayCw_Block_ExternalCheckout_AdditionalInformation extends Mage_Core_Block_Template
{
	private $_context;

	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('customweb/saferpaycw/external_checkout/additional-information.phtml');
	}

	public function isAdditionalInformationRequired()
	{
		return $this->isGenderRequired() || $this->isDateOfBirthRequired();
	}

	public function isGenderRequired()
	{
		return Mage::helper('SaferpayCw/externalCheckout')->isGenderRequired($this->getQuote());
	}

	public function isDateOfBirthRequired()
	{
		return Mage::helper('SaferpayCw/externalCheckout')->isDateOfBirthRequired($this->getQuote());
	}

	/**
	 * @return Customweb_SaferpayCw_Model_ExternalCheckoutContext
	 */
	public function getContext()
	{
		return $this->_context;
	}

	public function setContext(Customweb_SaferpayCw_Model_ExternalCheckoutContext $context)
	{
		$this->_context = $context;
		return $this;
	}

	/**
	 *
	 * @return Mage_Sales_Model_Quote
	 */
	public function getQuote()
	{
		return $this->getContext()->getQuote();
	}
}
