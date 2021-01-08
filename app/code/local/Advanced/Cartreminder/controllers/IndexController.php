<?php
class Advanced_Cartreminder_IndexController extends Mage_Core_Controller_Front_Action
{
    public function recoverAction()
    {    	
        $key = $this->getRequest()->getParam('key');
        $cartreminder = Mage::getModel('cartreminder/reminder')->load($key,'key');
        Mage::getSingleton('checkout/session')->setQuoteId($cartreminder->getQuoteId());
        $redirect = Mage::getStoreConfig('cartreminder/reminder/redirectroute',Mage::app()->getStore()->getStoreId());
        $this->_redirect($redirect);
    }
}