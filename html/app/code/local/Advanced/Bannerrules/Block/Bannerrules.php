<?php
/**
 * Stabeaddon
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Stabeaddon.com license that is
 * available through the world-wide-web at this URL:
 * http://www.stabeaddon.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Stabeaddon
 * @package     Stabeaddon_Bannerrules
 * @copyright   Copyright (c) 2012 Stabeaddon (http://www.stabeaddon.com/)
 * @license     http://www.stabeaddon.com/license-agreement.html
 */

/**
 * Bannerrules Block
 * 
 * @category    Stabeaddon
 * @package     Stabeaddon_Bannerrules
 * @author      Stabeaddon Developer
 */
class Advanced_Bannerrules_Block_Bannerrules extends Mage_Core_Block_Template
{
    /**
     * prepare block's layout
     *
     * @return Advanced_Bannerrules_Block_Bannerrules
     */
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    public function getDisplayBlocks(){
        $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $websiteId = Mage::app()->getWebsite()->getId();
        $date_expr = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
    
        $displayBlocks = Mage::getModel('bannerrules/bannerrules')->getCollection()
                                                ->addFieldToFilter('status',1)
                                                ->addFieldToFilter('customer_group',array('finset'=>array($groupId)))
                                                ->addFieldToFilter('website',array('finset'=>array($websiteId)))
                                                ->addFieldToFilter('from_date', array(array("lteq" => $date_expr), array("null" => true)))
                                                ->addFieldToFilter('to_date', array(array("gteq" => $date_expr), array("null" => true)))
                                                ->setOrder('priority', 'ASC');
        
        return $displayBlocks;
    }
}