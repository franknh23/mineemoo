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
class Advanced_Onestepcheckout_Block_Fields_Dob extends Mage_Customer_Block_Widget_Dob {

    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('onestepcheckout/fields/dob.phtml');
    }


    public function isRequired()
    {
        $check = false;
        $storeId = Mage::app()->getStore()->getStoreId();
        $websiteId = Mage::app()->getWebsite()->getId();
        $helper = Mage::helper('onestepcheckout'); 
        $field = $helper->getField($storeId, $websiteId, 'birthday'); 
        
        if($field['required']==1){
            $check = true;
        }
        
        return $check;
    }

}