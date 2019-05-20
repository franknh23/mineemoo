<?php

require_once(Mage::getModuleDir('controllers','Mage_Checkout').DS.'CartController.php');

class Tm_AjaxCart_CartController extends Mage_Checkout_CartController
{
    /**
     * Adds product to cart
     */
    public function addAction()
    {
        if (!$this->_validateFormKey()) {
            Mage::throwException('Invalid form key');
            return;
        }
        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }
            $product = $this->_initProduct();

            /**
             * Add product to cart is stock validation passed
             */
            if(!Mage::helper('tm_ajaxcart/data')->validateStock($product, $params)) {
                Mage::throwException("The requested quantity is not available.");
            } else {
                $cart   = $this->_getCart();
                if (!$product) {
                    $this->_goBack();
                    return;
                }
                $cart->addProduct($product, $params);
                $cart->save();
                $this->_getSession()->setCartWasUpdated(true);
            }
            $result['success'] = 1;
        } catch (Exception $e) {
            $result['success'] = 0;
            $result['message'] = $this->__("The requested quantity for " . $product->getName() . " is not available.");
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Updates minicart block content
     */
    public function ajaxUpdateAction()
    {
        if (!$this->_validateFormKey()) {
            Mage::throwException('Invalid form key');
        }
        $params = $this->getRequest()->getParams();

        $id = (int)$params['product'];
        $qty = (int)$params['qty'];
        $cart = $this->_getCart();

        $result = array();

        if($id){
            try{
                // Qty localization filter
                if (isset($qty)) {
                    $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                    );
                    $qty = $filter->filter($qty);
                }

                //Get quote item
                $quoteItem = $cart->getQuote()->getItemById($params['product']);
                if (!$quoteItem) {
                    Mage::throwException($this->__('Quote item is not found.'));
                }

                //Get product SKU
                $sku = $quoteItem->getSku();

                //Get product object (Mage_Catalog_Product_Model)
                $product    = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);

                if(!Mage::helper('tm_ajaxcart/data')->validateStock($product, $qty)){
                    Mage::throwException("The requested quantity is not available.");
                } else {
                    // Remove product from cart if qty = 0
                    if ($qty == 0) {
                        $cart->removeItem($id);
                    } else {
                        $quoteItem->setQty($qty)->save();
                    }

                    $this->_getCart()->save();

                    if (!$quoteItem->getHasError()) {
                        $result['message'] = $this->__('Item was updated successfully.');
                    } else {
                        $result['notice'] = $quoteItem->getMessage();
                    }
                }
                $result['success'] = 1;
            } catch (Exception $e) {
                $result['success'] = 0;
                $result['message'] = $this->__("The requested quantity for " . $product->getName() . " is not available.");
            }
        }

        $this->loadLayout();
        $result['content'] = $this->getLayout()->getBlock('ajaxcart_sidebar')->toHtml();
        $result['sidebar'] = $this->getLayout()->getBlock('cart_sidebar')->toHtml();
        $result['mobile'] = $this->getLayout()->getBlock('minicart_head_mobile')->toHtml();
        $result['subtotal'] = Mage::helper('checkout')->formatPrice($cart->getQuote()->getSubtotal());
        $result['qty'] = $this->_getCart()->getSummaryQty();

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * AJAX product delete action
     */
    public function ajaxDeleteAction()
    {
        if (!$this->_validateFormKey()) {
            Mage::throwException('Invalid form key');
        }
        $id = (int) $this->getRequest()->getParam('id');
        $result = array();
        if ($id) {
            try {
                $this->_getCart()->removeItem($id)->save();

                $result['qty'] = $this->_getCart()->getSummaryQty();

                $this->loadLayout();
                $result['content'] = $this->getLayout()->getBlock('ajaxcart_sidebar')->toHtml();

                $result['success'] = 1;
                $result['message'] = $this->__('Item was removed successfully.');
                Mage::dispatchEvent('ajax_cart_remove_item_success', array('id' => $id));
            } catch (Exception $e) {
                $result['success'] = 0;
                $result['error'] = $this->__('Can not remove the item.');
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function testAction(){
        Zend_Debug::dump($this->getLayout());
    }

}