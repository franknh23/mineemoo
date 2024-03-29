<?php

/**
 *  * You are allowed to use this API in your web application.
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
 * @category Customweb
 * @package Customweb_SaferpayCw
 * 
 */
class Customweb_SaferpayCw_Block_Info extends Mage_Payment_Block_Info {

	protected function _construct(){
		parent::_construct();
		
		$controllerName = $this->getRequest()->getControllerName();
		if (Mage::getDesign()->getArea() == 'adminhtml' && strstr($controllerName, 'memo') !== FALSE) {
			$this->setTemplate('customweb/saferpaycw/info_creditmemo.phtml');
		}
		else if (Mage::getDesign()->getArea() == 'adminhtml' &&
				 (strstr($controllerName, 'invoice') !== FALSE || strstr($controllerName, 'editsaferpaycw') !== FALSE)) {
			$this->setTemplate('customweb/saferpaycw/info_invoice.phtml');
		}
		else {
			$this->setTemplate('customweb/saferpaycw/info.phtml');
		}
	}

	public function getOrder(){
		return $this->getInfo()->getOrder();
	}

	public function getTransaction(){
		try {
			return Mage::helper('SaferpayCw')->loadTransactionByOrder($this->getOrder()->getId());
		}
		catch (Exception $e) {
			return null;
		}
	}

	public function getPaymentId(){
		$transaction = $this->getTransaction();
		if ($transaction !== null) {
			return $transaction->getPaymentId();
		}
		else {
			return null;
		}
	}

	public function getPaymentInformation(){
		$transaction = $this->getTransaction();
		if ($transaction !== null) {
			return $transaction->getTransactionObject()->getPaymentInformation();
		}
		else {
			return null;
		}
	}
	
	/**
	 * Render as PDF
	 * @return string
	 */
	public function toPdf()
	{
		$this->setTemplate('customweb/saferpaycw/info_pdf.phtml');
		return $this->toHtml();
	}
}
