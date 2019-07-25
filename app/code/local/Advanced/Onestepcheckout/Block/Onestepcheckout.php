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
class Advanced_Onestepcheckout_Block_Onestepcheckout extends Mage_Checkout_Block_Onepage_Abstract
{
	
    /**
     * get country html select
     *
     * @return HTML
     */
    public function getCountryHtmlSelect($type)
    {
        $countryId = $this->getAddress()->getCountryId();
        if (is_null($countryId)) {
            $countryId = Mage::helper('core')->getDefaultCountry();
        }
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'[country_id]')
            ->setId($type.':country_id')
            ->setTitle(Mage::helper('checkout')->__('Country'))
            ->setClass('validate-select')
            ->setValue($countryId)
            ->setOptions($this->getCountryOptions());       

        return $select->getHtml();
    }
    
    /**
     * Brazil Zipcode validation
     *
     * @return boolean
     */
    public function braziZipcodevalid(){
        $storeId = Mage::app()->getStore()->getStoreId();
        return (Mage::getStoreConfig('onestepcheckout/brazil/zipcode',$storeId))?true:false;
    }
    
    /**
     * Brazil Tax validation
     *
     * @return boolean
     */
    public function braziTaxvalid(){
        $storeId = Mage::app()->getStore()->getStoreId();
        return (Mage::getStoreConfig('onestepcheckout/brazil/tax',$storeId))?true:false;
    }
}