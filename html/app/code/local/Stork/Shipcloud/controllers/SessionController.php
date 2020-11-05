<?php
class Stork_Shipcloud_SessionController extends Mage_Core_Controller_Front_Action {

	public function sessionAction() {
		$pakadooId = $this->getRequest()->getParam('cartPakadooId');
		if(!is_null($pakadooId) && !empty($pakadooId) && trim($pakadooId) != ''){
			Mage::getSingleton('core/session')->setPakadooId($pakadooId);

			if(Mage::getSingleton('customer/session')->isLoggedIn()) {
					$customerData = Mage::getSingleton('customer/session')->getCustomer();
					$customerId = $customerData->getId();

					$pakadooCollection = Mage::getModel('shipcloud/pakadoo')->load($customerId,'customer_id');
					$pakadooCollection->setData(array(
						'pakadoo_id' => $pakadooId,
						'customer_id' => $customerId
					))->save();
			}
		}
		echo Mage::getUrl('checkout/onepage/');
	}
}
