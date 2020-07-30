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
class Advanced_Onestepcheckout_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * get fields label
     *
     * @return String
     */
    public function getFieldsLabel($value) {
        if ($value == 'empty')
            return $this->__('Empty');
        $fields = array(
            'firstname' => $this->__('First Name'),
            'lastname' => $this->__('Last Name'),
            'prefix_name' => $this->__('Prefix Name'),
            'middlename' => $this->__('Middle Name'),
            'suffix' => $this->__('Suffix Name'),
            'email' => $this->__('Email Address'),
            'company' => $this->__('Company'),
            'street' => $this->__('Address'),
            'country' => $this->__('Country'),
            'region' => $this->__('State/Province'),
            'city' => $this->__('City'),
            'postcode' => $this->__('Zip/Postal Code'),
            'telephone' => $this->__('Telephone'),
            'fax' => $this->__('Fax'),
            'birthday' => $this->__('Date of Birth'),
            'gender' => $this->__('Gender'),
            'taxvat' => $this->__('Tax/VAT number'),
        );

        return $fields[$value];
    }

    /**
     * get fields data on backend
     *
     * @return Array
     */
    public function getFieldData($scope = null, $scopeId = 0) {
        $model = Mage::getModel('onestepcheckout/fieldsposition')->getCollection();
        if ($scope) {
            $model->addFieldToFilter('scope', $scope);
        }
        if ($scopeId) {
            $model->addFieldToFilter('scope_id', $scopeId);
        }
        $model->setOrder('position', 'ASC');
        $fields = array();

        foreach ($model as $field) {


            if ($field->getScope() == 'default') {
                $fields['default'][] = $field->getData();
                $fields['use_default']['default'] = $field->getUseDefault();
            }
            if ($field->getScope() == 'websites') {
                $fields['websites'][] = $field->getData();
                $fields['use_default']['websites'] = $field->getUseDefault();
            }
            if ($field->getScope() == 'stores') {
                $fields['stores'][] = $field->getData();
                $fields['use_default']['stores'] = $field->getUseDefault();
            }
        }
        return $fields;
    }

    /**
     * get fields data on frontend
     *
     * @return Array
     */
    public function getFields($storeId, $websiteId) {

        if ($storeId) {
            $_fields = Mage::getModel('onestepcheckout/fieldsposition')->getCollection()
                    ->addFieldToFilter('scope', 'stores')
                    ->addFieldToFilter('scope_id', $storeId)
                    ->addFieldToFilter('remove', array('neq'=>'remove'))
                    ->setOrder('position', 'ASC');
        }


        if (!$_fields->getFirstItem()->getId()) {
            $_fields = Mage::getModel('onestepcheckout/fieldsposition')->getCollection()
                    ->addFieldToFilter('scope', 'websites')
                    ->addFieldToFilter('scope_id', $websiteId)
                    ->addFieldToFilter('remove', array('neq'=>'remove'))
                    ->setOrder('position', 'ASC');
        }

        if (!$_fields->getFirstItem()->getId()) {
            $_fields = Mage::getModel('onestepcheckout/fieldsposition')->getCollection()
                    ->addFieldToFilter('scope', 'default')
                    ->addFieldToFilter('scope_id', 0)
                    ->addFieldToFilter('remove', array('neq'=>'remove'))
                    ->setOrder('position', 'ASC');
        }



        $fields = array();

        foreach ($_fields as $field) {
            $fields[] = $field->getData();
        }

        return $fields;
    }

    /**
     * check show newsletter
     *
     * @return Boolean
     */
    public function showNewsletter() {
        if (!Mage::getStoreConfig('onestepcheckout/features/newsletter', Mage::app()->getStore()->getStoreId()))
            return false;
        if (!Mage::helper('core')->isModuleOutputEnabled('Mage_Newsletter'))
            return false;
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $subscribe_email = Mage::helper('customer')->getCustomer()->getEmail();
            $subscriberModel = Mage::getModel('newsletter/subscriber')->loadByEmail($subscribe_email);
            if ($subscriberModel->getId() && $subscriberModel->getData('subscriber_status') == 1) {
                return false;
            }
        }
        return true;
    }

    /**
     * get field data on frontend
     *
     * @return Array
     */
    public function getField($storeId, $websiteId, $path) {

        if ($storeId) {
            $_fields = Mage::getModel('onestepcheckout/fieldsposition')->getCollection()
                    ->addFieldToFilter('scope', 'stores')
                    ->addFieldToFilter('scope_id', $storeId)
                    ->addFieldToFilter('path', $path)
                    ->setOrder('position', 'ASC');
        }


        if (!$_fields->getFirstItem()->getId()) {
            $_fields = Mage::getModel('onestepcheckout/fieldsposition')->getCollection()
                    ->addFieldToFilter('scope', 'websites')
                    ->addFieldToFilter('scope_id', $websiteId)
                    ->addFieldToFilter('path', $path)
                    ->setOrder('position', 'ASC');
        }

        if (!$_fields->getFirstItem()->getId()) {
            $_fields = Mage::getModel('onestepcheckout/fieldsposition')->getCollection()
                    ->addFieldToFilter('scope', 'default')
                    ->addFieldToFilter('scope_id', 0)
                    ->addFieldToFilter('path', $path)
                    ->setOrder('position', 'ASC');
        }


        return $_fields->getFirstItem();
    }

    public function getDefaultPaymentMethod() {  
        $storeId = Mage::app()->getStore()->getStoreId();
        $value = '';        
        try {
            $value = Mage::getSingleton('checkout/session')->getQuote()->getPayment()->getMethodInstance()->getCode();
        } catch (Exception $e) {
        }
        if(!$value)
            $value = Mage::getStoreConfig('onestepcheckout/default_setting_checkout/default_payment_method',$storeId);

        return $value;
    }

    public function getDefaultShippingMethod() {
        $storeId = Mage::app()->getStore()->getStoreId();
        $value = '';
        try {
            $value = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getShippingMethod();
        } catch (Exception $e) {
        }
        
        
            if(!$value){
                if(Mage::getStoreConfig('onestepcheckout/features/minimum_shipping',$storeId)){
                    $cart = Mage::getSingleton('checkout/cart');
                    $address = $cart->getQuote()->getShippingAddress();
                

                    // Find if our shipping has been included.
                    $rates = $address->collectShippingRates()
                                     ->getGroupedAllShippingRates();
                    $price = 0;
                    $method = '';
                    foreach ($rates as $carrier) {
                        
                        foreach ($carrier as $rate) {                            
                            if((float)$price>=(float)$rate->getPrice()){
                                $price = $rate->getPrice();
                                $method = $rate->getCode();
                            }
                        }
                    }
                    
                    if($method){
                        $value = $method;
                    }
                }else{
                    $value = Mage::getStoreConfig('onestepcheckout/default_setting_checkout/default_shipping_method',$storeId);
                
                }
            }
        return $value;
    }
    
    public function showGiftWrap() {
        return Mage::getStoreConfig('onestepcheckout/features/enable_giftwrap', Mage::app()->getStore()->getStoreId());
    }
    
    
    public function getGiftwrapAmount() {
        $amount = Mage::getStoreConfig('onestepcheckout/features/giftwrap_amount', Mage::app()->getStore()->getStoreId());
        $giftwrapAmount = 0;
        $items = Mage::getSingleton('checkout/cart')->getItems();
        if (Mage::getStoreConfig('onestepcheckout/features/giftwrap_type', Mage::app()->getStore()->getStoreId()) == 1) {
            foreach ($items as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }
                $giftwrapAmount += $amount * ($item->getQty());
            }
        } elseif (count($items) > 0) {
            $giftwrapAmount = $amount;
        }
        return $giftwrapAmount;
    }
    
    public function checkGiftwrapSession() {
        $session = Mage::getSingleton('checkout/session');
        return $session->getData('onestepcheckout_giftwrap');
    }
    
    public function getGiftwrapType() {
        return Mage::getStoreConfig('onestepcheckout/giftwrap/giftwrap_type', Mage::app()->getStore()->getStoreId());
    }

}
