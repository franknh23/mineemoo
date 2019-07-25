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
class Advanced_Bannerrules_Block_Adminhtml_Bannerrules_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * prepare tab form's information
     *
     * @return Advanced_Bannerrules_Block_Adminhtml_Bannerrules_Edit_Tab_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        if (Mage::getSingleton('adminhtml/session')->getBannerrulesData()) {
            $data = Mage::getSingleton('adminhtml/session')->getBannerrulesData();
            Mage::getSingleton('adminhtml/session')->setBannerrulesData(null);
        } elseif (Mage::registry('bannerrules_data')) {
            $data = Mage::registry('bannerrules_data')->getData();
        }
        $fieldset = $form->addFieldset('bannerrules_form', array(
            'legend' => Mage::helper('bannerrules')->__('Information')
        ));

        $fieldset->addField('title', 'text', array(
            'label' => Mage::helper('bannerrules')->__('Title'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'title',
        ));

        $fieldset->addField('description', 'textarea', array(
            'label' => Mage::helper('bannerrules')->__('Description'),
            'required' => false,
            'name' => 'description',
        ));

        if (Mage::app()->isSingleStoreMode()) {
            $websiteId = Mage::app()->getStore(true)->getWebsiteId();
            $fieldset->addField('website', 'hidden', array(
                'name' => 'website[]',
                'value' => $websiteId
            ));
            $data['website'] = $websiteId;
        } else {
            $field = $fieldset->addField('website', 'multiselect', array(
                'name' => 'website[]',
                'label' => Mage::helper('bannerrules')->__('Websites'),
                'title' => Mage::helper('bannerrules')->__('Websites'),
                'required' => true,
                'values' => Mage::getSingleton('adminhtml/system_store')->getWebsiteValuesForForm()
            ));
            $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
            $field->setRenderer($renderer);
        }
        $customer = Mage::getResourceModel('customer/group_collection')
                ->toOptionArray();
        if (count($customer) > 0) {
            $fieldset->addField('customer_group', 'multiselect', array(
                'label' => Mage::helper('bannerrules')->__('Customer Group'),
                'required' => true,
                'name' => 'customer_group[]',
                'values' => $customer,
            ));
        }
        $fieldset->addField('from_date', 'date', array(
            'label' => Mage::helper('bannerrules')->__('Start Date'),
            'required' => false,
            'name' => 'from_date',
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        ));
        $fieldset->addField('to_date', 'date', array(
            'label' => Mage::helper('bannerrules')->__('Expire Date'),
            'required' => false,
            'name' => 'to_date',
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        ));
        $fieldset->addField('priority', 'text', array(
            'label' => Mage::helper('bannerrules')->__('Priority'),
            'required' => false,
            'name' => 'priority',
        ));

        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('bannerrules')->__('Status'),
            'name' => 'status',
            'values' => Mage::getSingleton('bannerrules/status')->getOptionHash(),
        ));

        $form->setValues($data);
        return parent::_prepareForm();
    }

}
