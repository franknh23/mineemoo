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
 */

class Customweb_SaferpayCw_Model_Source_PostFinanceEFinanceAddressmode
{
	public function toOptionArray()
	{
		$options = array(
			array('value' => 'none', 'label' => Mage::helper('adminhtml')->__("No address is sent")),
			array('value' => 'delivery', 'label' => Mage::helper('adminhtml')->__("Delivery address is sent")),
			array('value' => 'billing', 'label' => Mage::helper('adminhtml')->__("Billing address is sent")),
			array('value' => 'both', 'label' => Mage::helper('adminhtml')->__("Billing and Shipping"))
		);
		return $options;
	}
}
