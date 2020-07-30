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
 * Bannerrules Edit Form Content Tab Block
 * 
 * @category    Stabeaddon
 * @package     Stabeaddon_Bannerrules
 * @author      Stabeaddon Developer
 */
class Advanced_Bannerrules_Block_Adminhtml_Bannerrules_Edit_Tab_Actions extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * prepare tab form's information
     *
     * @return Stableaddon_Rewardpoint_Block_Adminhtml_Rewardpoint_Edit_Tab_Form
     */
    protected function _prepareForm() {
        if (Mage::registry('bannerrules_data')) {
            $model = Mage::registry('bannerrules_data');
        } else {
            $model = Mage::getModel('bannerrules_data/rule');
        }
        if (Mage::getSingleton('adminhtml/session')->getBannerrulesData()) {
            $data = Mage::getSingleton('adminhtml/session')->getBannerrulesData();
            Mage::getSingleton('adminhtml/session')->setBannerrulesData(null);
        } elseif (Mage::registry('bannerrules_data')) {
            $data = Mage::registry('bannerrules_data')->getData();
        }
        $data = $model->getData();
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('bannerrules_');



        $fieldset = $form->addFieldset('rule_form', array(
            'legend' => Mage::helper('bannerrules')->__('Actions')
        ));
        
        $fieldset->addField('show_block', 'multiselect', array(
            'label'        => Mage::helper('bannerrules')->__('Static blocks'),
            'name'        => 'show_block',
            'values'    => Mage::getSingleton('bannerrules/edit_staticblocks')->getOptionHash(),
        ));
        
        $fieldset->addField('position_cart', 'select', array(
            'label'        => Mage::helper('bannerrules')->__('Banner\'s position on Cart page'),
            'name'        => 'position_cart',
            'values'    => Mage::getSingleton('bannerrules/edit_position')->getOptionHash(),
        ));
        
        $fieldset->addField('position_checkout', 'select', array(
            'label'        => Mage::helper('bannerrules')->__('Banner\'s position on Checkout page'),
            'name'        => 'position_checkout',
            'values'    => Mage::getSingleton('bannerrules/edit_position')->getOptionHash(),
        ));
        
        $fieldset->addField('position_oscheckout', 'select', array(
            'label'        => Mage::helper('bannerrules')->__('Banner\'s position on One step checkout page'),
            'name'        => 'position_oscheckout',
            'values'    => Mage::getSingleton('bannerrules/edit_position')->getOptionHash(),
            'after_element_html' => '<p class="nm"><small><a href="http://www.advancedcheckout.com/one-step-checkout-features.html?utm_source=clientpr&utm_medium=product">One Step Checkout</a></small></p>'
        ));
    



        $form->setValues($data);
        $this->setForm($form);
        return parent::_prepareForm();
    }

}