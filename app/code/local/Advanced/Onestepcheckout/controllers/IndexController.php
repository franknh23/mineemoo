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
class Advanced_Onestepcheckout_IndexController extends Mage_Core_Controller_Front_Action {

    /**
     * check valid email
     */
    public function checkemailAction() {
        $email = $this->getRequest()->getParam('email');
        $result = array();

        $result['error'] = false;
        $customer = Mage::getModel('customer/customer');
        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
        $customer->loadByEmail($email);
        if ($customer->getId()) {
            $result['error'] = true;
            $result['error_messages'] = $this->__('Email "%s" has been registered. Please login.', $email);
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * index action
     */
    public function indexAction() {

        $quote = $this->getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->_redirect('checkout/cart');
            return;
        }


        $storeId = Mage::app()->getStore()->getStoreId();


        if (!Mage::getModel('checkout/session')->getQuote()->getBillingAddress()->getData('city')) {
            if (Mage::getStoreConfig('onestepcheckout/default_setting_checkout/default_city', $storeId)) {
                Mage::getModel('checkout/session')->getQuote()->getBillingAddress()->setData('city', Mage::getStoreConfig('onestepcheckout/default_setting_checkout/default_city', $storeId))->save();
            }
        }

        if (!Mage::getModel('checkout/session')->getQuote()->getShippingAddress()->getData('city')) {
            if (Mage::getStoreConfig('onestepcheckout/default_setting_checkout/default_city', $storeId)) {
                Mage::getModel('checkout/session')->getQuote()->getShippingAddress()->setData('city', Mage::getStoreConfig('onestepcheckout/default_setting_checkout/default_city', $storeId))->save();
            }
        }

        if (!Mage::getModel('checkout/session')->getQuote()->getBillingAddress()->getData('postcode')) {
            if (Mage::getStoreConfig('onestepcheckout/default_setting_checkout/default_postcode', $storeId)) {
                Mage::getModel('checkout/session')->getQuote()->getBillingAddress()->setData('postcode', Mage::getStoreConfig('onestepcheckout/default_setting_checkout/default_postcode', $storeId))->save();
            }
        }

        if (!Mage::getModel('checkout/session')->getQuote()->getShippingAddress()->getData('postcode')) {
            if (Mage::getStoreConfig('onestepcheckout/default_setting_checkout/default_postcode', $storeId)) {
                Mage::getModel('checkout/session')->getQuote()->getShippingAddress()->setData('postcode', Mage::getStoreConfig('onestepcheckout/default_setting_checkout/default_postcode', $storeId))->save();
            }
        }

        if (!Mage::getModel('checkout/session')->getQuote()->getBillingAddress()->getData('country_id')) {
            if (Mage::getStoreConfig('onestepcheckout/default_setting_checkout/country_id', $storeId)) {
                Mage::getModel('checkout/session')->getQuote()->getBillingAddress()->setData('country_id', Mage::getStoreConfig('onestepcheckout/default_setting_checkout/country_id', $storeId))->save();
            }
        }

        if (!Mage::getModel('checkout/session')->getQuote()->getShippingAddress()->getData('country_id')) {
            if (Mage::getStoreConfig('onestepcheckout/default_setting_checkout/country_id', $storeId)) {
                Mage::getModel('checkout/session')->getQuote()->getShippingAddress()->setData('country_id', Mage::getStoreConfig('onestepcheckout/default_setting_checkout/country_id', $storeId))->save();
            }
        }
        if (!Mage::getModel('checkout/session')->getQuote()->getBillingAddress()->getData('region')) {
            if (Mage::getStoreConfig('onestepcheckout/default_setting_checkout/region_id', $storeId)) {
                Mage::getModel('checkout/session')->getQuote()->getBillingAddress()->setData('region', Mage::getStoreConfig('onestepcheckout/default_setting_checkout/region_id', $storeId))->save();
            }
        }

        if (!Mage::getModel('checkout/session')->getQuote()->getShippingAddress()->getData('region')) {
            if (Mage::getStoreConfig('onestepcheckout/default_setting_checkout/region_id', $storeId)) {
                Mage::getModel('checkout/session')->getQuote()->getShippingAddress()->setData('region', Mage::getStoreConfig('onestepcheckout/default_setting_checkout/region_id', $storeId))->save();
            }
        }

        $this->loadLayout();
        $head = $this->getLayout()->getBlock('head');
        $head->setTitle(Mage::getStoreConfig('onestepcheckout/general/title', $storeId));
        //select design
        $styleDesign = Mage::getStoreConfig('onestepcheckout/style/design', $storeId);

        switch ($styleDesign) {
            case 'material':
                $head->addItem('js', 'advanced/materialize.min.js');
                $head->addItem('skin_css', 'css/advanced/materialize.css');
                break;
        }
        $this->renderLayout();
    }

    /**
     * save address action
     */
    public function saveAddressAction() {

        $params = $this->getRequest()->getParams();
        $storeId = Mage::app()->getStore()->getStoreId();

        $billingData = $params['billing'];
        if (isset($params['billing:shippingaddress']) && $params['billing:shippingaddress'] == 1) {
            $shippingData = $billingData;
        } else {
            if (!$this->getOnepage()->getQuote()->isVirtual() && Mage::getStoreConfig('onestepcheckout/general/shipping_address', $storeId)) {
                $shippingData = $params['shipping'];
            } else {
                $shippingData = $billingData;
            }
        }

        if (isset($billingData['email'])) {
            $billingData['email'] = trim($billingData['email']);
        }

        if (isset($shippingData['email'])) {
            $shippingData['email'] = trim($shippingData['email']);
        }

        // set customer tax/vat number for further usage
        if (Mage::getStoreConfig('onestepcheckout/vat/enabled', $storeId)) {
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {

                $customer = Mage::getSingleton('customer/session')->getCustomer();
                $taxvat = $customer->getTaxvat();
                $euVat = Mage::helper('onestepcheckout/euvat')->verifyCustomerVat($this->getOnepage()->getQuote(), $taxvat, $billingData['country_id']);

                if ($euVat) {
                    $this->getOnepage()->getQuote()->setCustomerTaxvat($taxvat);
                    $this->getOnepage()->getQuote()->setTaxvat($taxvat);
                    $this->getOnepage()->getQuote()->getBillingAddress()->setTaxvat($taxvat);
                    if ($this->getOnepage()->getQuote()->getBillingAddress()->getBuyWithoutVat() === null) {
                        $this->getOnepage()->getQuote()->getBillingAddress()->setBuyWithoutVat(1);
                    }
                } else {
                    $this->getOnepage()->getQuote()->setCustomerTaxvat('');
                    $this->getOnepage()->getQuote()->setTaxvat('');
                    $this->getOnepage()->getQuote()->getBillingAddress()->setTaxvat('');
                    if ($this->getOnepage()->getQuote()->getBillingAddress()->getBuyWithoutVat()) {
                        $this->getOnepage()->getQuote()->getBillingAddress()->setBuyWithoutVat(null);
                    }
                }
            } else {
                $cookie = Mage::getSingleton('core/cookie');

                if (!empty($billingData['taxvat'])) {
                    $this->getOnepage()->getQuote()->setCustomerTaxvat($billingData['taxvat']);
                    $this->getOnepage()->getQuote()->setTaxvat($billingData['taxvat']);
                    $this->getOnepage()->getQuote()->getBillingAddress()->setTaxvat($billingData['taxvat']);
                    if ($this->getOnepage()->getQuote()->getBillingAddress()->getBuyWithoutVat() === null) {
                        $this->getOnepage()->getQuote()->getBillingAddress()->setBuyWithoutVat(1);
                    }

                    $cookie->set('uc_tax_vat', $billingData['taxvat']);
                } else {
                    $cookie->set('uc_tax_vat', '');
                    $this->getOnepage()->getQuote()->setCustomerTaxvat('');
                    $this->getOnepage()->getQuote()->setTaxvat('');
                    $this->getOnepage()->getQuote()->getBillingAddress()->setTaxvat('');
                    if ($this->getOnepage()->getQuote()->getBillingAddress()->getBuyWithoutVat()) {
                        $this->getOnepage()->getQuote()->getBillingAddress()->setBuyWithoutVat(null);
                    }
                }
            }
        }

        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getModel('customer/customer');
            $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
            $customer->loadByEmail($billingData['email']);
            if ($customer->getId()) {
                $this->getOnepage()->getQuote()->setEmail('');
            }
        }
        $billingAddressId = $this->getRequest()->getPost('billing_address_id', false);
        $shippingAddressId = $this->getRequest()->getPost('shipping_address_id', false);



        if (isset($params['billing:shippingaddress']) && $params['billing:shippingaddress'] == 1) {
            $billingData['use_for_shipping'] = 1;
        } else {
            if (!$this->getOnepage()->getQuote()->isVirtual() && Mage::getStoreConfig('onestepcheckout/general/shipping_address', $storeId)) {

                $billingData['use_for_shipping'] = 0;
            } else {
                $billingData['use_for_shipping'] = 1;
            }
        }
        $this->getOnepage()->saveBilling($billingData, $billingAddressId);
        if (!$billingData['use_for_shipping']) {
            $this->getOnepage()->saveShipping($shippingData, $shippingAddressId);
        } else {
            if (!$this->getOnepage()->getQuote()->isVirtual()) {
                $this->getOnepage()->saveShipping($billingData, $billingAddressId);
            }
        }
        $ajaxAddress = explode(',', Mage::getStoreConfig('onestepcheckout/ajax_update/update_ajax_address', $storeId));

        $reloadShipping = (in_array('shipping_method', $ajaxAddress)) ? 1 : 0;
        $reloadPayment = (in_array('payment_method', $ajaxAddress)) ? 1 : 0;
        $reloadOrderReview = (in_array('order_review', $ajaxAddress)) ? 1 : 0;

        $result = array();

        if ($reloadShipping) {
            $shippingHtml = $this->_getShippingMethodHtml();
            $result['shipping_method'] = $shippingHtml;
        }

        if ($reloadPayment) {
            $paymentHtml = $this->_getPaymentMethodHtml();
            $result['payment_method'] = $paymentHtml;
        }

        if ($reloadOrderReview) {
            $reviewHtml = $this->_getReviewHtml();
            $result['order_review'] = $reviewHtml;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * save shipping method action
     */
    public function saveShippingMethodAction() {

        $data = $this->getRequest()->getParams('shipping_method', '');
        $storeId = Mage::app()->getStore()->getStoreId();
        $shippingMethod = $data['shipping_method'];
        $this->getOnepage()->saveShippingMethod($shippingMethod);

        $result = array();
        $result['error'] = false;

        Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method', array(
            'request' => $this->getRequest(),
            'quote' => $this->getOnepage()->getQuote()));
        $this->getOnepage()->getQuote()->collectTotals();
        $this->getOnepage()->getQuote()->collectTotals()->save();


        $ajaxShipping = explode(',', Mage::getStoreConfig('onestepcheckout/ajax_update/update_ajax_shipping', $storeId));



        $reloadPayment = (in_array('payment_method', $ajaxShipping)) ? 1 : 0;
        $reloadOrderReview = (in_array('order_review', $ajaxShipping)) ? 1 : 0;

        if ($reloadPayment) {
            $paymentHtml = $this->_getPaymentMethodHtml();
            $result['payment_method'] = $paymentHtml;
        }
        if ($reloadOrderReview) {
            $reviewHtml = $this->_getReviewHtml();
            $result['order_review'] = $reviewHtml;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * save payment method action
     */
    public function savePaymentMethodAction() {

        $data = $this->getRequest()->getParams('payment_method', '');
        $storeId = Mage::app()->getStore()->getStoreId();
        $paymentMethod = $this->getRequest()->getPost('payment', array());
        $paymentMethod['method'] = $data['payment_method'];
        $result = array();
        $redirectUrl = '';
        try {
            // set payment to quote
            $result = $this->getOnepage()->savePayment($paymentMethod);
        } catch (Mage_Payment_Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            $result['error'] = $e->getMessage();
        } catch (Exception $e) {
            $result['error'] = $this->__('Unable to set Payment Method.');
        }
        $ajaxPayment = explode(',', Mage::getStoreConfig('onestepcheckout/ajax_update/update_ajax_payment', $storeId));

        $reloadOrderReview = (in_array('order_review', $ajaxPayment)) ? 1 : 0;


        if ($reloadOrderReview) {
            $reviewHtml = $this->_getReviewHtml();
            $result['order_review'] = $reviewHtml;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * update item's qty action
     */
    public function updateItemsAction() {

        $itemsData = $this->getRequest()->getParam('items', '');
        $result = array();
        $cartData = array();
        $result['error'] = false;
        try {

            if (is_array($itemsData)) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                foreach ($itemsData as $index => $data) {
                    if (isset($data)) {
                        $cartData[$index]['qty'] = $filter->filter(trim($data));
                    }
                }
                $cart = $this->_getCart();
                if (!$cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                    $cart->getQuote()->setCustomerId(null);
                }

                $cartData = $cart->suggestItemsQty($cartData);
                $cart->updateItems($cartData)
                        ->save();

                if (!$cart->getQuote()->getItemsCount()) {
                    $result['url'] = Mage::getUrl('checkout/cart', array('_secure' => true));
                }
            }
            Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        } catch (Mage_Core_Exception $e) {
            $result['error'] = true;
            $result['message'] = Mage::helper('core')->escapeHtml($e->getMessage());
        } catch (Exception $e) {
            $result['error'] = true;
            $result['message'] = $this->__('Cannot update shopping cart.');
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * update item's qty action
     */
    public function deleteItemAction() {

        $item_id = $this->getRequest()->getParam('item_id', '');
        $result = array();

        $result['error'] = false;
        if ($item_id) {
            try {
                $this->_getCart()->removeItem($item_id)
                        ->save();
                if (!$this->_getCart()->getQuote()->getItemsCount()) {
                    $result['url'] = Mage::getUrl('checkout/cart', array('_secure' => true));
                }
            } catch (Exception $e) {
                $result['error'] = true;
                $result['message'] = Mage::helper('onestepcheckout')->__('Cannot remove the item.');
            }
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
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
     * Get checkout cart model
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
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession() {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Add welcome message and send new account email.
     * Returns success URL
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param bool $isJustConfirmed
     * @return string
     */
    protected function _welcomeCustomer(Mage_Customer_Model_Customer $customer, $isJustConfirmed = false) {
        $this->_getSession()->addSuccess(
                $this->__('Thank you for registering with %s.', Mage::app()->getStore()->getFrontendName())
        );
        if ($this->_isVatValidationEnabled()) {
            // Show corresponding VAT message to customer
            $configAddressType = Mage::helper('customer/address')->getTaxCalculationAddressType();
            $userPrompt = '';
            switch ($configAddressType) {
                case Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING:
                    $userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you shipping address for proper VAT calculation', Mage::getUrl('customer/address/edit'));
                    break;
                default:
                    $userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you billing address for proper VAT calculation', Mage::getUrl('customer/address/edit'));
            }
            $this->_getSession()->addSuccess($userPrompt);
        }

        $customer->sendNewAccountEmail(
                $isJustConfirmed ? 'confirmed' : 'registered', '', Mage::app()->getStore()->getId()
        );

        $successUrl = Mage::getUrl('*/*/index', array('_secure' => true));
        if ($this->_getSession()->getBeforeAuthUrl()) {
            $successUrl = $this->_getSession()->getBeforeAuthUrl(true);
        }
        return $successUrl;
    }

    /**
     * applyCoupon action
     */
    public function applyCouponAction() {

        $result = array();

        $cart = $this->_getCart();
        $couponCode = (string) $this->getRequest()->getParam('coupon_code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $cart->getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $result['redirect'] = Mage::getUrl('onestepcheckout/', array('_secure' => Mage::app()->getStore()->isCurrentlySecure()));
            $this->getResponse()->setBody(Zend_Json::encode($result));
            return;
        }

        try {
            $codeLength = strlen($couponCode);
            $isCodeLengthValid = $codeLength;

            $cart->getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $cart->getQuote()->setCouponCode($isCodeLengthValid ? $couponCode : '')
                    ->collectTotals()
                    ->save();

            if ($codeLength) {
                if ($isCodeLengthValid && $couponCode == $cart->getQuote()->getCouponCode()) {
                    $result['success'] = $this->__('Coupon code "%s" was applied.', Mage::helper('core')->escapeHtml($couponCode));
                } else {
                    $result['error'] = $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->escapeHtml($couponCode));
                }
            } else {
                $result['success'] = $this->__('Coupon code was canceled.');
            }
        } catch (Mage_Core_Exception $e) {
            $result['error'] = $e->getMessage();
        } catch (Exception $e) {
            $result['error'] = $this->__('Cannot apply the coupon code.');
            Mage::logException($e);
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    /**
     * loginPost action
     */
    public function loginPostAction() {
        $username = $this->getRequest()->getPost('username', false);
        $password = $this->getRequest()->getPost('password', false);

        $session = Mage::getSingleton('customer/session');

        $result = array('success' => false);

        if ($username && $password) {
            try {
                $session->login($username, $password);
            } catch (Exception $e) {
                $result['error'] = $e->getMessage();
            }
            if (!isset($result['error'])) {
                $result['success'] = true;
            }
        } else {
            $result['error'] = $this->__('Please enter a username and password.');
        }

        if (isset($result['success']) && $result['success']) {

            $layout = Mage::getModel('core/layout');
            $layout->getUpdate()->load('onestepcheckout_index_index');
            $layout->generateXml()->generateBlocks();
            $html = $layout->getBlock('onestepcheckout')->toHtml();
            $result['html'] = $html;
        }


        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    /**
     * editItem action
     */
    public function editItemAction() {
        $cart = $this->_getCart();
        $data = $this->getRequest()->getParams();

        $id = (int) $data['item_id'];
        $params = $this->getRequest()->getParams();

        if (!isset($params['options'])) {
            $params['options'] = array();
        }
        $return = array();
        $return['error'] = false;
        $return['success'] = false;
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $quoteItem = $cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                $return['error'] = Mage::throwException($this->__('Quote item is not found.'));
            }

            $item = $cart->updateItem($id, new Varien_Object($params));
            if (is_string($item)) {
                Mage::throwException($item);
            }
            if ($item->getHasError()) {
                Mage::throwException($item->getMessage());
            }

            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            Mage::dispatchEvent('checkout_cart_update_item_complete', array('item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );
            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $return['success'] = true;
                }
            }
        } catch (Mage_Core_Exception $e) {

            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $return['error'] = $message;
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            $return['url'] = $url;
        } catch (Exception $e) {
            $return['error'] = $this->__('Cannot update the item.');
        }

        $this->getResponse()->setBody(Zend_Json::encode($return));
    }

    /**
     * cart cartconfigure action
     */
    public function cartconfigureAction() {

        // Extract item and product to configure
        $id = (int) $this->getRequest()->getParam('item_id');
        $quoteItem = null;
        $cart = $this->_getCart();
        $result = array();
        $result['error'] = false;
        if ($id) {
            $quoteItem = $cart->getQuote()->getItemById($id);
        }
        try {
            $params = new Varien_Object();
            $params->setCategoryId(false);
            $params->setConfigureMode(true);
            $params->setBuyRequest($quoteItem->getBuyRequest());

            Mage::helper('catalog/product_view')->prepareAndRender($quoteItem->getProduct()->getId(), $this, $params);
        } catch (Exception $e) {
            $result['error'] = $this->__('Cannot configure product.');
        }

        if (!$result['error']) {

            if ($quoteItem->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {

                $productId = $quoteItem->getProductId();

                $product = Mage::getModel('catalog/product')->load($productId);

                $addToCart = Mage::app()->getLayout()->createBlock('catalog/product_view')
                        ->setTemplate('catalog/product/view/addtocart.phtml');

                $attrRenderers = Mage::app()->getLayout()->createBlock('core/text_list');
                $after = Mage::app()->getLayout()->createBlock('core/text_list');
                $configurable = Mage::app()->getLayout()->createBlock('catalog/product_view_type_configurable')
                        ->setChild('attr_renderers', $attrRenderers)
                        ->setChild('after', $after)
                        ->setTemplate('onestepcheckout/cart/product/view/type/options/configurable.phtml');


                $options = Mage::app()->getLayout()->createBlock('catalog/product_view_options')
                        ->setTemplate('catalog/product/view/options.phtml');
                $options->addOptionRenderer('text', 'catalog/product_view_options_type_text', 'onestepcheckout/cart/product/view/options/type/text.phtml');
                $options->addOptionRenderer('file', 'catalog/product_view_options_type_file', 'onestepcheckout/cart/product/view/options/type/file.phtml');
                $options->addOptionRenderer('select', 'onestepcheckout/product_view_options_type_select', 'onestepcheckout/cart/product/view/options/type/select.phtml');
                $options->addOptionRenderer('date', 'catalog/product_view_options_type_date', 'onestepcheckout/cart/product/view/options/type/date.phtml');
                $optionsJs = Mage::app()->getLayout()->createBlock('core/template')
                        ->setTemplate('catalog/product/view/options/js.phtml');


                $block = $this->getLayout()->createBlock('catalog/product_view')->setTemplate('onestepcheckout/cart/item/product/view.phtml')
                        ->setChild('addtocart', $addToCart)
                        ->setChild('options_js', $optionsJs)
                        ->setChild('product_options', $options)
                        ->setChild('options_configurable', $configurable)
                        ->setTierPriceTemplate('onestepcheckout/cart/product/view/tierprices.phtml')
                        ->setData('item', $id)
                        ->toHtml();


                $result['html'] = $block;

                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            } elseif ($quoteItem->getProductType() == 'bundle') {

                $productId = $quoteItem->getProductId();

                $product = Mage::getModel('catalog/product')->load($productId);



                $addToCart = Mage::app()->getLayout()->createBlock('catalog/product_view')
                        ->setTemplate('catalog/product/view/addtocart.phtml');


                $options = Mage::app()->getLayout()->createBlock('catalog/product_view_options')
                        ->setTemplate('catalog/product/view/options.phtml');
                $options->addOptionRenderer('text', 'catalog/product_view_options_type_text', 'onestepcheckout/cart/product/view/options/type/text.phtml');
                $options->addOptionRenderer('file', 'catalog/product_view_options_type_file', 'onestepcheckout/cart/product/view/options/type/file.phtml');
                $options->addOptionRenderer('select', 'onestepcheckout/product_view_options_type_select', 'onestepcheckout/cart/product/view/options/type/select.phtml');
                $options->addOptionRenderer('date', 'catalog/product_view_options_type_date', 'onestepcheckout/cart/product/view/options/type/date.phtml');
                $optionsJs = Mage::app()->getLayout()->createBlock('core/template')
                        ->setTemplate('catalog/product/view/options/js.phtml');



                $block = $this->getLayout()->createBlock('catalog/product_view')->setTemplate('onestepcheckout/cart/item/product/view.phtml')
                        ->setChild('addtocart', $addToCart)
                        ->setChild('options_js', $optionsJs)
                        ->setChild('product_options', $options)
                        ->setTierPriceTemplate('onestepcheckout/cart/product/view/tierprices.phtml')
                        ->setData('item', $id)
                        ->toHtml();


                $result['html'] = $block;
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            } elseif ($quoteItem->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL) {

                $productId = $quoteItem->getProductId();

                $product = Mage::getModel('catalog/product')->load($productId);

                $addToCart = Mage::app()->getLayout()->createBlock('catalog/product_view')
                        ->setTemplate('catalog/product/view/addtocart.phtml');

                $virtual = Mage::app()->getLayout()->createBlock('catalog/product_view_type_virtual')
                        ->setTemplate('onestepcheckout/cart/product/view/type/default.phtml');

                $options = Mage::app()->getLayout()->createBlock('catalog/product_view_options')
                        ->setTemplate('catalog/product/view/options.phtml');
                $options->addOptionRenderer('text', 'catalog/product_view_options_type_text', 'onestepcheckout/cart/product/view/options/type/text.phtml');
                $options->addOptionRenderer('file', 'catalog/product_view_options_type_file', 'onestepcheckout/cart/product/view/options/type/file.phtml');
                $options->addOptionRenderer('select', 'onestepcheckout/product_view_options_type_select', 'onestepcheckout/cart/product/view/options/type/select.phtml');
                $options->addOptionRenderer('date', 'catalog/product_view_options_type_date', 'onestepcheckout/cart/product/view/options/type/date.phtml');
                $optionsJs = Mage::app()->getLayout()->createBlock('core/template')
                        ->setTemplate('catalog/product/view/options/js.phtml');


                $block = $this->getLayout()->createBlock('catalog/product_view')->setTemplate('onestepcheckout/cart/item/product/view.phtml')
                        ->setChild('addtocart', $addToCart)
                        ->setChild('product_type_data', $virtual)
                        ->setChild('options_js', $optionsJs)
                        ->setChild('product_options', $options)
                        ->setTierPriceTemplate('onestepcheckout/cart/product/view/tierprices.phtml')
                        ->setData('item', $id)
                        ->toHtml();


                $result['html'] = $block;
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            } elseif ($quoteItem->getProductType() == 'downloadable') {

                $productId = $quoteItem->getProductId();

                $product = Mage::getModel('catalog/product')->load($productId);
                $addToCart = Mage::app()->getLayout()->createBlock('catalog/product_view')
                        ->setTemplate('catalog/product/view/addtocart.phtml');

                $options = Mage::app()->getLayout()->createBlock('catalog/product_view_options')
                        ->setTemplate('catalog/product/view/options.phtml');
                $options->addOptionRenderer('text', 'catalog/product_view_options_type_text', 'onestepcheckout/cart/product/view/options/type/text.phtml');
                $options->addOptionRenderer('file', 'catalog/product_view_options_type_file', 'onestepcheckout/cart/product/view/options/type/file.phtml');
                $options->addOptionRenderer('select', 'onestepcheckout/product_view_options_type_select', 'onestepcheckout/cart/product/view/options/type/select.phtml');
                $options->addOptionRenderer('date', 'catalog/product_view_options_type_date', 'onestepcheckout/cart/product/view/options/type/date.phtml');
                $optionsJs = Mage::app()->getLayout()->createBlock('core/template')
                        ->setTemplate('catalog/product/view/options/js.phtml');

                $link = Mage::app()->getLayout()->createBlock('downloadable/catalog_product_links')
                        ->setTemplate('downloadable/catalog/product/links.phtml');
                $link->insert('product.info.downloadable.options');

                $block = $this->getLayout()->createBlock('catalog/product_view')->setTemplate('onestepcheckout/cart/item/product/view.phtml')
                        ->setChild('addtocart', $addToCart)
                        ->setChild('type_downloadable_options', $link)
                        ->setChild('options_js', $optionsJs)
                        ->setChild('product_options', $options)
                        ->setTierPriceTemplate('onestepcheckout/cart/product/view/tierprices.phtml')
                        ->setData('item', $id)
                        ->toHtml();


                $result['html'] = $block;
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            } else {

                $productId = $quoteItem->getProductId();

                $product = Mage::getModel('catalog/product')->load($productId);
                $addToCart = Mage::app()->getLayout()->createBlock('catalog/product_view')
                        ->setTemplate('catalog/product/view/addtocart.phtml');

                $options = Mage::app()->getLayout()->createBlock('catalog/product_view_options')
                        ->setTemplate('catalog/product/view/options.phtml');
                $options->addOptionRenderer('text', 'catalog/product_view_options_type_text', 'onestepcheckout/cart/product/view/options/type/text.phtml');
                $options->addOptionRenderer('file', 'catalog/product_view_options_type_file', 'onestepcheckout/cart/product/view/options/type/file.phtml');
                $options->addOptionRenderer('select', 'onestepcheckout/product_view_options_type_select', 'onestepcheckout/cart/product/view/options/type/select.phtml');
                $options->addOptionRenderer('date', 'catalog/product_view_options_type_date', 'onestepcheckout/cart/product/view/options/type/date.phtml');
                $optionsJs = Mage::app()->getLayout()->createBlock('core/template')
                        ->setTemplate('catalog/product/view/options/js.phtml');



                $block = $this->getLayout()->createBlock('catalog/product_view')->setTemplate('onestepcheckout/cart/item/product/view.phtml')
                        ->setChild('product_options', $options)
                        ->setChild('addtocart', $addToCart)
                        ->setChild('options_js', $optionsJs)
                        ->setChild('product_options', $options)
                        ->setTierPriceTemplate('onestepcheckout/cart/product/view/tierprices.phtml')
                        ->setData('item', $id)
                        ->toHtml();


                $result['html'] = $block;
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Check can page show for unregistered users
     *
     * @return boolean
     */
    protected function _canShowForUnregisteredUsers() {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    /**
     * save order action
     */
    public function saveOrderAction() {

        $data = $this->getRequest()->getParams();
        $billingData = $data['billing'];

        $billingData['dob'] = date("Y-m-d", strtotime($billingData['year'].'-'.$billingData['month'].'-'.$billingData['day']));

        unset($billingData['year']);
        unset($billingData['month']);
        unset($billingData['day']);
        
        $result = array();

        if (!$this->getOnepage()->getQuote()->isVirtual() && !isset($data['shipping_method'])) {
            $result['error'] = true;
            $result['success'] = false;
            $result['error_messages'] = $this->__('Please select a shipping method');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            return;
        }

        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getModel('customer/customer');
            $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
            $customer->loadByEmail($billingData['email']);
            if ($customer->getId()) {
                $result['error'] = true;
                $result['success'] = false;
                $result['error_messages'] = $this->__('Email "%s" has been registered.', $data['billing']['email']);
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                $this->getOnepage()->getQuote()->setEmail('');
                return;
            }
        }




        $storeId = Mage::app()->getStore()->getStoreId();

        if (Mage::getStoreConfig('onestepcheckout/features/term_condition', Mage::app()->getStore()->getStoreId()) && Mage::getStoreConfig('onestepcheckout/features/type_condition', Mage::app()->getStore()->getStoreId()) == 'system') {
            $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
            if ($requiredAgreements) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                $diff = array_diff($requiredAgreements, $postedAgreements);
                if ($diff) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = $this->__('Please agree to all the terms and conditions before placing the order.');
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    return;
                }
            }
        }
        //newsletter
        if (isset($data['is_subscribed']) && $data['is_subscribed']) {

            $is_subscriber = $data['is_subscribed'];
            if ($is_subscriber) {
                $subscribe_email = '';
                //pull subscriber email from billing data
                if (isset($billingData['email']) && $billingData['email'] != '') {
                    $subscribe_email = $billingData['email'];
                } else if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                    $subscribe_email = Mage::helper('customer')->getCustomer()->getEmail();
                }
                //check if email is already subscribed
                $subscriberModel = Mage::getModel('newsletter/subscriber')->loadByEmail($subscribe_email);
                if (!$subscriberModel->getId()) {
                    Mage::getModel('newsletter/subscriber')->subscribe($subscribe_email);
                } else if ($subscriberModel->getData('subscriber_status') != 1) {
                    $subscriberModel->setData('subscriber_status', 1);
                    try {
                        $subscriberModel->save();
                    } catch (Exception $e) {
                        
                    }
                }
            }
        }

        /*
         * save gift message
         */
        if (isset($data['giftmessage']) && $data['giftmessage']) {
            $giftMessages = $data['giftmessage'];
            $quote = $this->getOnepage()->getQuote();

            if (is_array($giftMessages)) {
                foreach ($giftMessages as $entityId => $message) {

                    $giftMessage = Mage::getModel('giftmessage/message');

                    switch ($message['type']) {
                        case 'quote':
                            $entity = $quote;
                            break;
                        case 'quote_item':
                            $entity = $quote->getItemById($entityId);
                            break;
                        case 'quote_address':
                            $entity = $quote->getAddressById($entityId);
                            break;
                        case 'quote_address_item':
                            $entity = $quote->getAddressById($message['address'])->getItemById($entityId);
                            break;
                        default:
                            $entity = $quote;
                            break;
                    }

                    if ($entity->getGiftMessageId()) {
                        $giftMessage->load($entity->getGiftMessageId());
                    }

                    if (trim($message['message']) == '') {
                        if ($giftMessage->getId()) {
                            try {
                                $giftMessage->delete();
                                $entity->setGiftMessageId(0)
                                        ->save();
                            } catch (Exception $e) {
                                
                            }
                        }
                        continue;
                    }

                    try {
                        $giftMessage->setSender($message['from'])
                                ->setRecipient($message['to'])
                                ->setMessage($message['message'])
                                ->save();

                        $entity->setGiftMessageId($giftMessage->getId())
                                ->save();
                    } catch (Exception $e) {
                        
                    }
                }
            }
        }

        /*
         * save billing address and shipping address
         */

        if (isset($data['billing:shippingaddress']) && $data['billing:shippingaddress'] == 1) {
            $shippingData = $billingData;
        } else {
            if (!$this->getOnepage()->getQuote()->isVirtual() && Mage::getStoreConfig('onestepcheckout/general/shipping_address', $storeId)) {
                $shippingData = $data['shipping'];
            } else {
                $shippingData = $billingData;
            }
        }

        if (isset($billingData['email'])) {
            $billingData['email'] = trim($billingData['email']);
        }

        if (isset($shippingData['email'])) {
            $shippingData['email'] = trim($shippingData['email']);
        }

        $billingAddressId = $this->getRequest()->getPost('billing_address_id', false);
        $shippingAddressId = $this->getRequest()->getPost('shipping_address_id', false);

        if (Mage::getStoreConfig('onestepcheckout/brazil/tax', $storeId)) {
            if (isset($billingData['brazil_tax']) && $billingData['brazil_tax']) {
                $this->getOnepage()->getQuote()->getBillingAddress()->setCustomerBrazilTax($billingData['brazil_tax']);
                $this->getOnepage()->getQuote()->getShippingAddress()->setCustomerBrazilTax($billingData['brazil_tax']);
            }
        }

        /*
         * register customer
         */

        if (!$this->_canShowForUnregisteredUsers()) {
            $method = 'guest';
            if (isset($billingData['createaccount']) && $billingData['createaccount']) {
                $method = 'register';
            }
            $this->getOnepage()->getQuote()->setCustomerId(null);
            $this->getOnepage()->getQuote()->setCustomerFirstname($billingData['firstname']);
            $this->getOnepage()->getQuote()->setCustomerLastname($billingData['lastname']);
            $this->getOnepage()->getQuote()->setCustomerEmail($billingData['email']);
            //$this->getOnepage()->getQuote()->setCustomerId(null);
            $this->getOnepage()->saveCheckoutMethod($method);
        }


        if (isset($data['billing:shippingaddress']) && $data['billing:shippingaddress'] == 1) {
            $billingData['use_for_shipping'] = 1;
        } else {
            if (!$this->getOnepage()->getQuote()->isVirtual() && Mage::getStoreConfig('onestepcheckout/general/shipping_address', $storeId)) {
                $this->getOnepage()->saveShipping($shippingData, $shippingAddressId);
                $billingData['use_for_shipping'] = 0;
            } else {
                $billingData['use_for_shipping'] = 1;
            }
        }

        $this->getOnepage()->saveBilling($billingData, $billingAddressId);



        /*
         * save payment method
         */
        try {

            if ($paymentData = $this->getRequest()->getPost('payment', false)) {
                $this->getOnepage()->savePayment($paymentData);
                $this->getOnepage()->getQuote()->getPayment()->importData($paymentData);
            }


            $redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();

            $result['success'] = true;
            $result['error'] = false;
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                $result['error_messages'] = $message;
            }
            $result['goto_section'] = 'payment';
            $result['update_section'] = array(
                'html' => $this->_getPaymentMethodsHtml()
            );
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $this->__('There was an error processing your order. Please contact us or try again later.');
        }

        if ($redirectUrl && $redirectUrl != '') {
            $result['redirect'] = $redirectUrl;
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        } else {
            try {

                $this->getOnepage()->saveOrder();
                $this->getOnepage()->getQuote()->save();

                $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
                $result['success'] = false;
                $result['error'] = true;
                $result['error_messages'] = $e->getMessage();
            }
            /**
             * when there is redirect to third party, we don't want to save order yet.
             * we will save the order in return action.
             */
            $isSecure = Mage::app()->getStore()->isCurrentlySecure();
            if (isset($redirectUrl)) {
                $result['redirect'] = $redirectUrl;
            } else {
                if (!isset($result['goto_section']))
                    $result['redirect'] = Mage::getUrl('checkout/onepage/success', array('_secure' => $isSecure));
            }
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * Get payment method step html
     *
     * @return string
     */
    protected function _getPaymentMethodsHtml() {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('checkout_onepage_paymentmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

    /**
     * Get review html
     *
     * @return string
     */
    protected function _getReviewHtml() {
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load('onestepcheckout_index_review');
        $layout->generateXml()->generateBlocks();
        return $layout->getBlock('onestepcheckout_review')->toHtml();
    }

    /**
     * Get shipping method html
     *
     * @return string
     */
    protected function _getShippingMethodHtml() {

        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load('onestepcheckout_index_shippingmethod');
        $layout->generateXml()->generateBlocks();
        return $layout->getBlock('onestepcheckout_checkout_shippingmethod')->toHtml();
    }

    /**
     * Get payment method html
     *
     * @return string
     */
    protected function _getPaymentMethodHtml() {

        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load('onestepcheckout_index_paymentmethod');
        $layout->generateXml()->generateBlocks();
        return $layout->getBlock('onestepcheckout_checkout_paymentmethod')->toHtml();
    }

    /**
     * Forgot customer password action
     */
    public function forgotpasswordAction() {
        $email = $this->getRequest()->getPost('email', false);
        $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email);

        if ($customer->getId()) {
            try {
                $newPassword = $customer->generatePassword();
                $customer->changePassword($newPassword, false);
                $customer->sendPasswordReminderEmail();
                $result = array('success' => true);
            } catch (Exception $e) {
                $result = array('success' => false, 'error' => $e->getMessage());
            }
        } else {
            $result = array('success' => false, 'error' => 'Not found!');
        }

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function getreionidAction() {
        $data = $this->getRequest()->getPost();
        $resion = Mage::getModel('directory/region')->getCollection()
                ->addFieldToFilter('country_id', $data['country'])
                ->addFieldToFilter('code', $data['region_id'])
                ->getFirstItem();
        $result = array();
        $result['id'] = $resion->getId();

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    /**
     * check vat action
     */
    public function checkvatAction() {
        $params = $this->getRequest()->getParams();
        $storeId = Mage::app()->getStore()->getStoreId();
        $quote = $this->getOnepage()->getQuote();
        if (Mage::getStoreConfig('onestepcheckout/vat/enabled', $storeId)) {
            $euVat = Mage::helper('onestepcheckout/euvat')->verifyCustomerVat($quote, $params['taxvat'], $params['country']);
        }


        $this->getOnepage()->getQuote()->collectTotals()->save();

        $result['verify_result'] = $euVat;


        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * check brazil zipcode action
     */
    public function brazilzipcodeAction() {
        $zipcode = $this->getRequest()->getParam('zipcode');
        $url = 'http://api.postmon.com.br/v1/cep/' . $zipcode;
        $error = false;
        try {
            $return = file_get_contents($url);
        } catch (Exception $e) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $return = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpcode == '404') {
                $error = true;
            }
        }

        $check = false;
        $result = array();


        if ($error) {
            $message = Mage::helper('onestepcheckout')->__('Nenhum endereço encontrado para o CEP: %s.', $zipcode);
        } else {
            $return = Mage::helper('core')->jsonDecode($return);
            $result['data'] = $return;
            $check = true;
        }

        $result['status'] = $check;
        if (isset($message) && $message)
            $result['message'] = $message;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * check brazil CPNJ action
     */
    public function brazilcpnjAction() {
        $value = $this->getRequest()->getParam('cpnj');
        $valid = true;
        $cnpj = preg_replace('/[^0-9_]/', '', $value);
        for ($x = 0; $x < 10; $x++) {
            if ($cnpj == str_repeat($x, 14)) {
                $valid = false;
            }
        }
        if ($valid) {
            if (strlen($cnpj) != 14) {
                $valid = false;
            } else {
                for ($t = 12; $t < 14; $t ++) {
                    $d = 0;
                    $c = 0;
                    for ($m = $t - 7; $m >= 2; $m --, $c ++) {
                        $d += $cnpj {$c} * $m;
                    }
                    for ($m = 9; $m >= 2; $m --, $c ++) {
                        $d += $cnpj {$c} * $m;
                    }
                    $d = ((10 * $d) % 11) % 10;
                    if ($cnpj {$c} != $d) {
                        $valid = false;
                        break;
                    }
                }
            }
        }
        $result = array();
        $result['valid'] = $valid;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

}
