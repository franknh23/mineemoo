<?php class Stork_Shipcloud_PakadooController extends Mage_Core_Controller_Front_Action {

	protected function _getSession() {
		return Mage::getSingleton('customer/session');
	}

	public function preDispatch() {
		parent::preDispatch();
		if (!Mage::getSingleton('customer/session')->authenticate($this)) {
                $this->setFlag('', 'no-dispatch', true);
        }
    }

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();

    }

	public function saveAction() {
        $pakadooId = $this->getRequest()->getParam('pakadooId');
		if(is_null($pakadooId) || empty($pakadooId) || trim($pakadooId) == ''){
			$emptyError['error'][] = Mage::helper('shipcloud')->__('You set empty Pakado Id!');
			Mage::getSingleton('core/session')->addError(implode(', ', $emptyError['error']));
			$this->_redirect('customer/pakadoo/');
		}else {
			if(Mage::getSingleton('customer/session')->isLoggedIn()) {
					$customerData = Mage::getSingleton('customer/session')->getCustomer();
					$customerId = $customerData->getId();

          $pakadooCollection = Mage::getModel('shipcloud/pakadoo')->load($customerId,'customer_id');
          $pakadooCollection->setData(array(
						'pakadoo_id' => $pakadooId,
						'customer_id' => $customerId
					))->save();

				$this->_redirect('customer/pakadoo/');
			}else{
				$emptyError['error'][] = Mage::helper('shipcloud')->__('You are Logged Out!');
				Mage::getSingleton('core/session')->addError(implode(', ', $emptyError['error']));
				$this->_redirect('customer/pakadoo/');
			}
		}
    }
}
