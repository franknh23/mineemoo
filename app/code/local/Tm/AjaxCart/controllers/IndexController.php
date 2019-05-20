<?php 

require_once(Mage::getModuleDir('controllers','Mage_Checkout').DS.'CartController.php');
class Tm_AjaxCart_IndexController extends Mage_Checkout_CartController
{
	public function addAction()
	{
		$cart = $this->_getCart();
		$params = $this->getRequest()->getParams();

		$this->_updateShoppingCart();
	}
}