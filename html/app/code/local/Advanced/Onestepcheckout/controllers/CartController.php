<?php

/**
 * Advanced Checkout
 * 
 * NOTICE OF LICENSE 
 * 
 * This source file is subject to the Onestepcheckout.com license that is
 * available through the world-wide-web at this URL:
 * http://www.advancedcheckout.com/license-agreement
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category 	Advanced Checkout
 * @package 	Advanced_Onestepcheckout
 * @copyright 	Copyright (c) 2015 Advanced Checkout (http://www.advancedcheckout.com/)
 * @license 	http://www.advancedcheckout.com/license-agreement
 */

/**
 * Onestepcheckout Block
 * 
 * @category 	Onestepcheckout
 * @package 	Advanced_Onestepcheckout
 * @author  	Onestepcheckout Developer
 */
class Advanced_Onestepcheckout_CartController extends Mage_Core_Controller_Front_Action {

     /**
     * Filter to convert localized values to internal ones
     * @var Zend_Filter_LocalizedToNormalized
     */
    protected $_localFilter = null;
    
    /**
     * Add products in group to shopping cart action
     */
    public function addAction() {
        $message = '';
        $redirectUrl = '';
        $error = false;
        $productData = array();

        $cart = $this->_getCart();
        $params = $this->getRequest()->getParams();
        
        if (!$error) {
            try {
                if (isset($params['qty'])) {
                    $filter = new Zend_Filter_LocalizedToNormalized(
                            array('locale' => Mage::app()->getLocale()->getLocaleCode())
                    );
                    $params['qty'] = $filter->filter($params['qty']);
                }

                $product = $this->_initProduct();
                /**
                 * Check product availability
                 */
                if (!$product) {
                    $message = $this->__('Product is not exists.');
                    $error = true;
                }else{
                        $urlImage = Mage::getModel('catalog/product_media_config')->getMediaUrl( $product->getThumbnail());                           
                        $productData['image'] = $urlImage;
                        $productData['name'] = $product->getName();
                        $productData['price'] = Mage::app()->getStore()->formatPrice($product->getFinalPrice());
                        $productData['sku'] = $product->getSku();
                }
                
                if (!$error) {
                    $cart->addProduct($product, $params);   
                    $cart->save();
                    
                    $this->_getSession()->setCartWasUpdated(true);
                    if (!$cart->getQuote()->getHasError()) {
                        $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));                        
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $error = true;
                $message = $e->getMessage();
                
            } catch (Exception $e) {
                $error = true;
                $message = $this->__('Cannot add the item to shopping cart.');
                
            }
        }
        $result = array();
        $result['error'] = $error;
        $result['message'] = $message;
        $result['cart_summary'] = Mage::helper('checkout/cart')->getItemsQty();

        if(count($productData)){
            $result['product'] = $productData;
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    /**
     * Add wishlist item to shopping cart and remove from wishlist
     *
     * If Product has required options - item removed from wishlist and redirect
     * to product view page with message about needed defined required options
     */
    public function cartAction()
    {
        $message = '';
        $redirectUrl = '';
        $error = false;
        $productData = array();
        
        $itemId = (int) $this->getRequest()->getParam('item');

        /* @var $item Mage_Wishlist_Model_Item */
        $item = Mage::getModel('wishlist/item')->load($itemId);
        if (!$item->getId()) {
            $message = $this->__('Item is not exists.');
            $error = true;
        }
        
        try{
            $wishlist = $this->_getWishlist($item->getWishlistId());
        }catch(Exception $e){
            $message = $e->getMessage();
            $error = true;
        }
       
        if(!$error){
            // Set qty
            $qty = $this->getRequest()->getParam('qty');
            if (is_array($qty)) {
                if (isset($qty[$itemId])) {
                    $qty = $qty[$itemId];
                } else {
                    $qty = 1;
                }
            }
            $qty = $this->_processLocalizedQty($qty);
            if ($qty) {
                $item->setQty($qty);
            }

            /* @var $session Mage_Wishlist_Model_Session */
            $session    = Mage::getSingleton('wishlist/session');
            $cart       = Mage::getSingleton('checkout/cart');

            

            try {
                $options = Mage::getModel('wishlist/item_option')->getCollection()
                        ->addItemFilter(array($itemId));
                $item->setOptions($options->getOptionsByItem($itemId));

                $buyRequest = Mage::helper('catalog/product')->addParamsToBuyRequest(
                    $this->getRequest()->getParams(),
                    array('current_config' => $item->getBuyRequest())
                );

                $item->mergeBuyRequest($buyRequest);
                if ($item->addToCart($cart, true)) {
                    $cart->save()->getQuote()->collectTotals();
                }

                $wishlist->save();
                Mage::helper('wishlist')->calculate();


                $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($item->getProductId());
                $productName = Mage::helper('core')->escapeHtml($product->getName());
                
                $urlImage = Mage::getModel('catalog/product_media_config')->getMediaUrl( $product->getThumbnail());                           
                $productData['image'] = $urlImage;
                $productData['name'] = $product->getName();
                $productData['price'] = Mage::app()->getStore()->formatPrice($product->getFinalPrice());
                $productData['sku'] = $product->getSku();
                        
                $message = $this->__('%s was added to your shopping cart.', $productName);

            } catch (Mage_Core_Exception $e) {
                $error = true;
                if ($e->getCode() == Mage_Wishlist_Model_Item::EXCEPTION_CODE_NOT_SALABLE) {
                    $message = $this->__('This product(s) is currently out of stock');
                } else if ($e->getCode() == Mage_Wishlist_Model_Item::EXCEPTION_CODE_HAS_REQUIRED_OPTIONS) {
                    $message = $e->getMessage();
                    $redirectUrl = Mage::getUrl('wishlist/index/configure/', array('id' => $item->getId()));
                } else {
                    $message = $e->getMessage();
                    $redirectUrl = Mage::getUrl('wishlist/index/configure/', array('id' => $item->getId()));
                }
            } catch (Exception $e) {
                $error = true;                
                $message = $this->__('Cannot add item to shopping cart');
            }

            Mage::helper('wishlist')->calculate();
        }
        $result = array();
        $result['error'] = $error;
        $result['message'] = $message;
        $result['redirect'] = $redirectUrl;
        
        if(count($productData)){
            $result['product'] = $productData;
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    public function updateItemOptionsAction()
    {
        $message = '';
        $redirectUrl = '';
        $error = false;
        $productData = array();

        $cart   = $this->_getCart();
        $id = (int) $this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();

        if (!isset($params['options'])) {
            $params['options'] = array();
        }
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $quoteItem = $cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                $error = true;
                $message = $this->__('Quote item is not found.');
            }

            $item = $cart->updateItem($id, new Varien_Object($params));
            
            $urlImage = Mage::getModel('catalog/product_media_config')->getMediaUrl( $item->getProduct()->getThumbnail());                           
            $productData['image'] = $urlImage;
            $productData['name'] = $item->getProduct()->getName();
            $productData['price'] = Mage::app()->getStore()->formatPrice($item->getProduct()->getFinalPrice());
            $productData['sku'] = $item->getProduct()->getSku();
            if (is_string($item)) {                
                $error = true;
                $message = $item;
            }
            if ($item->getHasError()) {
                $error = true;
                $message = $item->getMessage();
            }


            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('%s was updated in your shopping cart.', Mage::helper('core')->escapeHtml($item->getProduct()->getName()));
                   
                }
                
            }
        } catch (Mage_Core_Exception $e) {
                $error = true;
                $message = $item->getMessage();
        } catch (Exception $e) {            
            $error = true;
            $message = $this->__('Cannot update the item.');
        }
        $result = array();
        $result['error'] = $error;
        $result['message'] = $message;
        $result['redirect'] = $redirectUrl;
        
        if(count($productData)){
            $result['product'] = $productData;
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    public function osclayoutAction(){
        $quote = $this->getOnepage()->getQuote();
        $checkoutPage = 0;
        if (!$quote->hasItems() || $quote->getHasError()) {
            $layout = Mage::getModel('core/layout');
            $html = $layout->createBlock('core/template')->setTemplate('onestepcheckout/popup/emptycart.phtml')->toHtml();      
        }else{
            $layout = Mage::getModel('core/layout');
            $layout->getUpdate()->load('onestepcheckout_index_index');
            $layout->generateXml()->generateBlocks();
            $html = $layout->getBlock('onestepcheckout')->toHtml(); 
            $checkoutPage = 1;
        }
        $result = array();
        $result['html'] = $html;
        $result['checkout_page'] = $checkoutPage;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    
    /**
     * Get one page checkout model
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage() {
        return Mage::getSingleton('checkout/type_onepage');
    }
    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart() {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession() {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current active quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote() {
        return $this->_getCart()->getQuote();
    }

    /**
     * Initialize product instance from request data
     *
     * @return Mage_Catalog_Model_Product || false
     */
    protected function _initProduct() {
        $productId = (int) $this->getRequest()->getParam('product');
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }

    /**
     * Set back redirect url to response
     *
     * @return Mage_Checkout_CartController
     * @throws Mage_Exception
     */
    protected function _goBack() {
        $returnUrl = $this->getRequest()->getParam('return_url');
        if ($returnUrl) {

            if (!$this->_isUrlInternal($returnUrl)) {
                throw new Mage_Exception('External urls redirect to "' . $returnUrl . '" denied!');
            }

            $this->_getSession()->getMessages(true);
            $return = $returnUrl;
        } elseif (!Mage::getStoreConfig('checkout/cart/redirect_to_cart') && !$this->getRequest()->getParam('in_cart') && $backUrl = $this->_getRefererUrl()
        ) {
            $return = $backUrl;
        } else {
            if (($this->getRequest()->getActionName() == 'add') && !$this->getRequest()->getParam('in_cart')) {
                $this->_getSession()->setContinueShoppingUrl($this->_getRefererUrl());
            }
            $return = Mage::getUrl('checkout/cart');
        }
        return $return;
    }
    
    protected function _getWishlist($wishlistId = null)
    {
        $wishlist = Mage::registry('wishlist');
        if ($wishlist) {
            return $wishlist;
        }

        try {
            if (!$wishlistId) {
                $wishlistId = $this->getRequest()->getParam('wishlist_id');
            }
            $customerId = Mage::getSingleton('customer/session')->getCustomerId();
            /* @var Mage_Wishlist_Model_Wishlist $wishlist */
            $wishlist = Mage::getModel('wishlist/wishlist');
            if ($wishlistId) {
                $wishlist->load($wishlistId);
            } else {
                $wishlist->loadByCustomer($customerId, true);
            }

            if (!$wishlist->getId() || $wishlist->getCustomerId() != $customerId) {
                $wishlist = null;
                Mage::throwException(
                    Mage::helper('wishlist')->__("Requested wishlist doesn't exist")
                );
            }

            Mage::register('wishlist', $wishlist);
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('wishlist/session')->addError($e->getMessage());
            return false;
        } catch (Exception $e) {
            Mage::getSingleton('wishlist/session')->addException($e,
                Mage::helper('wishlist')->__('Wishlist could not be created.')
            );
            return false;
        }

        return $wishlist;
    }
    
    /**
     * Processes localized qty (entered by user at frontend) into internal php format
     *
     * @param string $qty
     * @return float|int|null
     */
    protected function _processLocalizedQty($qty)
    {
        if (!$this->_localFilter) {
            $this->_localFilter = new Zend_Filter_LocalizedToNormalized(
                array('locale' => Mage::app()->getLocale()->getLocaleCode())
            );
        }
        $qty = $this->_localFilter->filter((float)$qty);
        if ($qty < 0) {
            $qty = null;
        }
        return $qty;
    }

}
