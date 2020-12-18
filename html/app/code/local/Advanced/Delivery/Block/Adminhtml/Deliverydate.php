<?php
/**
 * Advanced
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the AdvancedCheckout.com license that is
 * available through the world-wide-web at this URL:
 * http://www.AdvancedCheckout.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Advanced
 * @package     Advanced_Delivery
 * @copyright   Copyright (c) 2012 Advanced (http://www.AdvancedCheckout.com/)
 * @license     http://www.AdvancedCheckout.com/license-agreement.html
 */

/**
 * Delivery Adminhtml Block
 * 
 * @category    Advanced
 * @package     Advanced_Delivery
 * @author      Advanced Developer
 */
class Advanced_Delivery_Block_Adminhtml_Deliverydate extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_deliverydate';
        $this->_blockGroup = 'delivery';
        $this->_headerText = Mage::helper('delivery')->__('Manager Delivery Date ');
        parent::__construct();
        $this->_removeButton('add');
    }
}