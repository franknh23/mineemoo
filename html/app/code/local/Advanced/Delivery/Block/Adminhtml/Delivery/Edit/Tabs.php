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
 * Delivery Edit Tabs Block
 * 
 * @category    Advanced
 * @package     Advanced_Delivery
 * @author      Advanced Developer
 */
class Advanced_Delivery_Block_Adminhtml_Delivery_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('delivery_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('delivery')->__('Holiday'));
    }
    
    /**
     * prepare before render block to html
     *
     * @return Advanced_Delivery_Block_Adminhtml_Delivery_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('delivery')->__('Holidays'),
            'title'     => Mage::helper('delivery')->__('Holidays'),
            'content'   => $this->getLayout()
                
                                ->createBlock('delivery/adminhtml_delivery_edit_tab_form')
                                ->toHtml(),
        ));
        return parent::_beforeToHtml();
    }
}